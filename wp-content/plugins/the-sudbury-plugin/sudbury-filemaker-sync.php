<?php
/**
 * Adds functionality specific to the meetings Custom Post Type
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Cron
 */

define( 'SUDBURY_FM_CACHE_TIMEOUT', 24 * 60 * 60  );
if ( isset( $_REQUEST['fmdebug']  ) && function_exists( 'is_internal'  ) && is_internal()  ) {
	echo 'The next FileMaker sync is scheduled for ' . date( 'm/d/Y \a\t h:i:j a', wp_next_scheduled( 'sudbury_cron_jobs_filemakersync'  )  );

}

function sudbury_filemaker_sync_manual() {
		if ( is_super_admin()  ) {
		sudbury_log( 'Access Granted'  );
		sudbury_filemaker_cron_job();

		} else {

		sudbury_log_user_info( 'Security Access Violation', 'Access Token Incorrect... Your IP Address has ben Logged... repeated attempts to access this page will be dealt with swiftly'  );
		wp_die( 'Access Token Incorrect... Your IP Address has ben Logged... repeated attempts to access this page will be dealt with swiftly'  );

		}

}

add_action( 'admin_post_fm_sync', 'sudbury_filemaker_sync_manual'  );

use airmoi\FileMaker\FileMaker;
use airmoi\FileMaker\FileMakerException;
use airmoi\FileMaker\FileMakerValidationException;

/**
 * @return bool
 */
function sudbury_filemaker_cron_job() {
		if ( ! defined( 'SUDBURY_DOING_LOGGING'  )  ) {
		//define( 'SUDBURY_DOING_LOGGING', true  );

		}

	sudbury_log( 'FILEMAKER: Cron Job started'  );
	$constants = array(
		'SUDBURY_VERSION',
		'FILEMAKER_HOST',
		'FILEMAKER_USER',
		'FILEMAKER_PASS'

					);
	foreach ( $constants as $constant  ) {
			if ( ! defined( $constant  )  ) {
			wp_die( "Failed to run filemaker sync because the constant '{$constant}' is not defined"  );

			}

	}
	require_once( plugin_dir_path( __FILE__  ) . 'Filemaker/autoloader.php'  );
	require_once( plugin_dir_path( __FILE__  ) . 'Filemaker/FileMaker.php'  );
	sudbury_log( 'FILEMAKER: Filemaker libraries loaded'  );
	sudbury_log( 'FILEMAKER: Connecting to ' . FILEMAKER_HOST  );
	$fms = new FileMaker( 'personnel records', FILEMAKER_HOST, FILEMAKER_USER, FILEMAKER_PASS  );
	// requesting all records from filemaker in the format of 'WebLayoutPublic'... I Don't care about find criterion, seriously, because its weird and confusing and not implemented correctly in FileMaker... We are just going to sort it all out in the PHP Script here
	$freq = $fms->newFindCommand( 'WebLayoutPublic'  );

	// note to future dev: add filters here:
	//      $freq->addFindCriterion('Web Department Name Long', $term);
	//      $freq->addSortRule('Web Order', 1, FILEMAKER_SORT_ASCEND);

	sudbury_log( 'FILEMAKER: filemaker query is compiled... executing'  );
	try {
		$impl_data = $freq->execute();

	} catch (FileMakerException $ex) {
		sudbury_log( "FILEMAKER: Error! Execution failed"  );
		sudbury_log( $ex->getMessage()  );
		return false;

	}
	sudbury_log( 'FILEMAKER: filemaker query completed'  );

	$records = $impl_data->getRecords();
	if ( ! isset( $records  ) || empty( $records  )  ) {
		sudbury_log( 'FILEMAKER: query returned empty set'  );

		return false;

	}
	sudbury_log( 'FILEMAKER: query is not erroneous'  );
	$personnel = array();


	foreach ( $records as $record  ) {
		// could use a compound find for this
		$status = $record->getField( 'Status Full Title' );

		sudbury_log( "Status for Record {$record->getField( 'Full Name'  )} is {$status}." );

		if ( $status != "Active" && $status != "Leave of Absence"  ) {
			continue;
		}

		$depts = $record->getField( 'Web Department Name Short'  );
		$depts = explode( ',', $depts );
		foreach ( $depts as $dept  ) {
			$dept                  = sanitize_key( $dept  );
			$person                = array();
			$person['first_name']  = $record->getField( 'First Name'  );
			$person['last_name']   = $record->getField( 'Last Name'  );
			$person['full_name']   = $record->getField( 'Full Name'  );
			$person['phone']       = $record->getField( 'Phone \ Extension Full'  );
			$person['title']       = $record->getField( 'Title'  );
			$person['building']    = $record->getField( 'Building Name'  );
			$person['status']      = $record->getField( 'Status'  ); // YES!! it is redundant
			$person['status_full'] = $record->getField( 'Status Full Title'  ); // YES!! it is redundant again
			$person['order']       = $record->getField( 'Web Order'  );
			$person['email']       = $record->getField( 'Web Email'  );
			$person['keywords']    = $record->getField( 'Web Keywords'  );
			$person['dept']        = $dept; // YES!! Stop criticizing the redundancy... I know you are

			if ( ! isset( $personnel[ $dept  ]  )  ) {
				$personnel[ $dept  ] = array();

			}
			$personnel[ $dept  ][] = $person;
		}

	}
	echo '<pre>' . var_export($personnel, true) . '</pre>';
	sudbury_log( 'FILEMAKER: setting options'  );
	$wp_personnel = array();
	// AH and now you see the logic behind the weird setup of $personnel
	$ids = array();
	foreach ( $personnel as $dept => $staff  ) {

		// Sort the arrays by Web Order for Nice use in the future
			for ( $i = 0; $i < count( $staff  ); $i ++  ) {
					uasort( $staff, function ( $a, $b  ) {
									if ( $a['order'] == $b['order']  ) {
					return strcmp( $a['last_name'], $b['last_name']  );

									}

				return ( $a['order'] < $b['order']  ) ? - 1 : 1;

									}  );


			}

		$id                  = sudbury_get_id_from_legacy_shortname( $dept  );
		$wp_personnel[ $id  ] = $staff;
		if ( $id  ) {
			$ids[] = $id;
			switch_to_blog( $id  );

			sudbury_log( "Setting Personnel for blog $id and dept $dept"  );

			update_option( 'sudbury_fm_personnel', $staff  );

			restore_current_blog();

		} else {
			sudbury_log( "There is no blog with slug: '$dept'... skipping"  );

		}

	}

	$_sites = get_sites( array( 'limit' => null, 'number' => 0, 'count' => false ) );
	// Compat with wp_get_sites
	$sites = array_map( function ( $_site ) { return get_site( $_site )->to_array(); }, $_sites );

	// Delete records for sites that no longer have staff
	// Todo: un-scheme-ify this
	array_map( function ( $id  ) {
			switch_to_blog( $id  );
			sudbury_log( 'Deleting All Personnel Records for ' . $id  );
			delete_option( 'sudbury_fm_personnel'  );
			restore_current_blog();
	
			}, array_diff( array_map( function ( $s  ) {
							return $s['blog_id']; 
						}, $sites  ), 
		$ids  )  );

	update_site_option( 'sudbury_all_fm_personnel', $wp_personnel  );

	sudbury_log( 'FILEMAKER: successfully completed sync with filemaker for Personnel Records... Starting Board Members'  );

	/**********************************************************************
	 * ENTERING THE BOARD MEMBERSHIP SECTION OF THE FILEMAKER SYNC SCRIPT *
	 **********************************************************************/

	$sites = get_sites( array( 'limit' => null, 'number' => 0, 'count' => false  )  );
	foreach ( $sites as $_site  ) {
		$site = get_site( $_site )->to_array();
		switch_to_blog( $site['blog_id']  );
		if ( ! $membership_key = get_option( 'sudbury_board_membership_key'  )  ) {
        	update_option( 'sudbury_board_membership', array() );
			sudbury_log( get_bloginfo( 'name'  ) . ' Is Not a Commitee or is lacking a board membership key'  );
			restore_current_blog();

			continue;

		}

		$fmsb = new FileMaker( 'Boards', FILEMAKER_HOST, FILEMAKER_USER, FILEMAKER_PASS  );
		// new FileMaker object for Boards

        $freqb = $fmsb->newFindCommand('WebLayout');
        $freqb->addFindCriterion("Board Name", $membership_key);

        sudbury_log( 'Getting members for ' . $membership_key );

        ini_set( "default_socket_timeout", 6  ); // not sure if these are still needed
        sudbury_log( 'FILEMAKER: filemaker query is compiled... executing'  );
		try {
            $impl_data = $freqb->execute();

		} catch (FileMakerException $ex) {
            sudbury_log( "FILEMAKER: Error! Execution failed"  );
            sudbury_log( $ex->getMessage()  );
        	update_option( 'sudbury_board_membership', array()  );

          continue;
            //certain committees don't have any entries in the FM database, which throws a no records found error. Removed return false so that the sync doesn't stop when ti encounters one of those

		}
        sudbury_log( 'FILEMAKER: filemaker query completed'  );

        $records = $impl_data->getRecords();
		if ( ! isset( $records  ) || empty( $records  )  ) {
            sudbury_log( 'FILEMAKER: query returned empty set'  );

            return false;

		}
        sudbury_log( 'FILEMAKER: query is not erroneous'  );
        $members = array();

		foreach ( $records as $record  ) {
				$members[] = array(
                "name"     => $record->getField('Name Formal'),
                "position" => $record->getField('Status'),
                "address"  => $record->getField('Str#\Street\ no sudbury'),
                "term"     => $record->getField('Term'),
                "end_date" => $record->getField('Term Expiration'),
                "appointed_by" => $record->getField('Appointed by')
								);

		}

        update_option( 'sudbury_board_membership', $members  );
        sudbury_log( "Setting Members for blog {$site['path']}"  );

		restore_current_blog();


	}
	sudbury_log( "Getting ALL Members"  );

	$master_members = array();

    ini_set( "default_socket_timeout", 6  );
    $freqball = $fmsb->newFindCommand('WebLayout');
    $freqball->addFindCriterion("Board Name", "*");

    sudbury_log( 'FILEMAKER: filemaker query is compiled... executing'  );
	try {
        $impl_data = $freqball->execute();

	} catch (FileMakerException $ex) {
        sudbury_log( "FILEMAKER: Error! Execution failed"  );
        sudbury_log( "Message: " . $ex->getMessage()  );
        return false;

	}
    sudbury_log( 'FILEMAKER: filemaker query completed'  );

    $records = $impl_data->getRecords();
	if ( ! isset( $records  ) || empty( $records  )  ) {
        sudbury_log( 'FILEMAKER: query returned empty set'  );

        return false;

	}
    sudbury_log( 'FILEMAKER: query is not erroneous'  );

	foreach ( $records as $record  ) {
			$member = array(
				"Name Formal"            => $record->getField('Name Formal'),
				"Status"                 => $record->getField('Status'),
				"Board Name"             => $record->getField('Board Name'),
				"Last Name"              => $record->getField('Last Name'),
				"First Appointment Year" => $record->getField('First Appointment Year'),
				"Term"                   => $record->getField('Term'),
				"Term Expiration"        => $record->getField('Term Expiration'),
				"Appointed by"           => $record->getField('Appointed by'),
				"Vacancy"                => $record->getField('Vacancy'),
				"Last Name First"        => $record->getField('Last Name First'),
				"Search Web Contact"     => $record->getField('Web Email'),
				"Web Keywords"           => $record->getField('Web Keywords')

						   );


			$info = sudbury_get_blog_info_by_option( 'sudbury_board_membership_key', $member['Board Name']  );

			if ( $info  ) {
				$board                                = $info->blogname;
				$member['site']                       = $info;
				$member['site']->board_membership_key = $member['Board Name'];

			} else {
				$board = $member['Board Name'];

			}

			if ( ! isset( $master_members[ $board  ]  )  ) {
				$master_members[ $board  ] = array();

			}

			$master_members[ $board  ][] = $member;

			sudbury_log( "Setting Member " . $member['Name Formal']  );


	}
    //echo '<pre>' . var_export($master_members, true) . '</pre>';
	ksort( $master_members  );
	update_site_option( 'sudbury_all_board_membership', $master_members  );


	sudbury_log( 'FILEMAKER: successfully completed cron job... set options for known blogs'  );
	// wp_mail( 'webmaster@sudbury.ma.us', '[WordPress] FileMaker Sync Completed', 'WordPress just synced with FileMaker at ' . current_time( 'mysql'  ) . '.  If this was an automated sync expect another sync in about 12 hours.  Manual FileMaker sync jobs do not affect the regular schedule.'  );

	return true;
}

add_action( 'sudbury_cron_jobs_filemakersync', 'sudbury_filemaker_cron_job'  );

/**
 * Registers the Cron Job
 */
function sudbury_cron_activation() {
	wp_schedule_event( time(), 'twicedaily', 'sudbury_cron_jobs_filemakersync'  );
	wp_schedule_event( time(), 'always', 'sudbury_cron_logger'  );

}

register_activation_hook( dirname( __FILE__  ) . '\\the-sudbury-plugin.php', 'sudbury_cron_activation'  );

/**
 * Unregisters the Cron Job
 */
function sudbury_cron_deactivation() {
	wp_clear_scheduled_hook( 'sudbury_cron_jobs_filemakersync'  );
	wp_clear_scheduled_hook( 'sudbury_cron_logger'  );

}

register_deactivation_hook( dirname( __FILE__  ) . '\\the-sudbury-plugin.php', 'sudbury_cron_deactivation'  );


/* General Cron Debugging Tools */


function cron_add_always( $schedules  ) {
	// Adds a cron schedule that will always execute when cron is called
		$schedules['always'] = array(
		'interval' => 1,
		'display'  => __( 'Always'  )

						);

	return $schedules;

}

add_filter( 'cron_schedules', 'cron_add_always'  );

add_action( 'sudbury_cron_logger', 'sudbury_cron_logger'  );
define( 'SUDBURY_DISABLE_CRON_LOG', true  );
// This is a read only mockup of the wp-cron.php file to figure out the mess that is going on with wp-cron
function sudbury_cron_logger() {

		if ( defined( 'SUDBURY_DISABLE_CRON_LOG'  ) && SUDBURY_DISABLE_CRON_LOG  ) {
		return;

		}

	// this function will call sudbury_log Directly so d() and _d() shouldn't double log them
		if ( ! defined( 'SUDBURY_DOING_LOGGING'  )  ) {
		define( 'SUDBURY_DOING_LOGGING', true  );

		}

	global $crons, $gmt_time, $doing_wp_cron;
	sudbury_log( '=============== Sudbury Cron Log Start: ' . current_time( 'mysql'  ) . ' ==============='  );
	sudbury_log( 'Registered Crons'  );
	sudbury_log( _d( $crons  )  );
	foreach ( $crons as $timestamp => $cronhooks  ) {
			if ( $timestamp > $gmt_time  ) {
			sudbury_log( 'Breaking out of Crons at ' . _d( $cronhooks  )  );
			break;

			}

			foreach ( $cronhooks as $hook => $keys  ) {

					foreach ( $keys as $k => $v  ) {

				$schedule = $v['schedule'];

				if ( $schedule != false  ) {
					sudbury_log( 'Action "' . $hook . '" will be  rescheduled'  );

				}

				sudbury_log( 'Action: ' . $hook . ' will be called with args ' . substr( _d( $v['args']  ), 0, 100  ) . '[...]'  );

				// If the hook ran too long and another cron process stole the lock, quit.
				if ( _get_cron_lock() != $doing_wp_cron  ) {
					sudbury_log( 'Cron Job Ran Too Long and another Process Has Taken Over... Normally only happens if you are running multiple Web Servers hosting the same Wordpress Database'  );

				}

					}

			}

	}
	sudbury_log( '=============== Sudbury Cron Log End: ' . current_time( 'mysql'  ) . ' ==============='  );

}

