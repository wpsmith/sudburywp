<?php
/**
 * Adds functionality specific to the meetings Custom Post Type
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Cron
 */

define( 'SUDBURY_FM_CACHE_TIMEOUT', 24 * 60 * 60 );
if ( isset( $_REQUEST['fmdebug'] ) && function_exists( 'is_internal' ) && is_internal() ) {
	echo 'The next FileMaker sync is scheduled for ' . date( 'm/d/Y \a\t h:i:j a', wp_next_scheduled( 'sudbury_cron_jobs_filemakersync' ) );
}

if ( isset( $_REQUEST['do_fm_sync'] ) ) {
	if ( isset( $_REQUEST['fm_sync_access_token'] ) && '7F1C1F8C7D3594B769AA57C6B6E4D' == $_REQUEST['fm_sync_access_token'] ) {
		sudbury_log( 'Access Granted' );
		sudbury_filemaker_cron_job();
	} else {

		sudbury_log_user_info( 'Security Access Violation', 'Access Token Incorrect... Your IP Address has ben Logged... repeated attempts to access this page will be dealt with swiftly' );
		die( 'Access Token Incorrect... Your IP Address has ben Logged... repeated attempts to access this page will be dealt with swiftly' );
	}
}

add_action( 'admin_post_fm_sync', 'sudbury_filemaker_cron_job' );
/**
 * @return bool
 */
function sudbury_filemaker_cron_job() {
	if ( ! defined( 'SUDBURY_DOING_LOGGING' ) ) {
		//define( 'SUDBURY_DOING_LOGGING', true );
	}

	sudbury_log( 'FILEMAKER: Cron Job started' );

	if ( ! defined( 'SUDBURY_FRAMEWORK_VERSION' ) ) {
		wp_die( 'FILEMAKER: Failed to run filemaker sync because the sudbury framework is not loaded' );
	}

	require_once( plugin_dir_path( __FILE__ ) . 'Filemaker/FileMaker.php' );
	sudbury_log( 'FILEMAKER: Filemaker libraries loaded' );

	$fms = new FileMaker( 'personnel records' );

	// requesting all records from filemaker in the format of 'WebLayoutPublic'... I Don't care about find criterion, seriously, because its weird and confusing and not implemented correctly in FileMaker... We are just going to sort it all out in the PHP Script here
	$freq = $fms->newFindCommand( 'WebLayoutPublic' );

	// note to future dev: add filters here:
	//      $freq->addFindCriterion('Web Department Name Long', $term);
	//      $freq->addSortRule('Web Order', 1, FILEMAKER_SORT_ASCEND);

	sudbury_log( 'FILEMAKER: filemaker query is compiled... executing' );

	$impl_data = $freq->execute();

	sudbury_log( 'FILEMAKER: filemaker query completed' );
	if ( $fms->isError( $impl_data ) ) {
		sudbury_log( "FILEMAKER: Error! Execution failed: " . print_r( $impl_data, true ) );

		return false;
	}
	$records = $impl_data->getRecords();
	if ( ! isset( $records ) || empty( $records ) ) {
		sudbury_log( 'FILEMAKER: query returned empty set' );

		return false;
	}
	sudbury_log( 'FILEMAKER: query is not erroneous' );
	$personnel = array();


	foreach ( $records as $record ) {
		// could use a compound find for this
		sudbury_log( "Status for Record {$record->getField( 'Full Name' )} is {$record->getField( "StatusEnter" )}" );
		if ( $record->getField( "StatusEnter" ) != "A" && $record->getField( "StatusEnter" ) != "L" ) {
			continue;
		}

		$depts = $record->getField( 'Web Department Name Short' );
		$depts = explode( ',', $depts );
		foreach ( $depts as $dept ) {
			$dept                  = sanitize_key( $dept );
			$person                = array();
			$person['first_name']  = $record->getField( 'First Name' );
			$person['last_name']   = $record->getField( 'Last Name' );
			$person['full_name']   = $record->getField( 'Full Name' );
			$person['phone']       = $record->getField( 'Phone \ Extension Full' );
			$person['title']       = $record->getField( 'Title' );
			$person['building']    = $record->getField( 'Building Name' );
			$person['status']      = $record->getField( 'Status' ); // YES!! it is redundant
			$person['status_full'] = $record->getField( 'Status Full Title' ); // YES!! it is redundant again
			$person['order']       = $record->getField( 'Web Order' );
			$person['email']       = $record->getField( 'Web Email' );
			$person['keywords']    = $record->getField( 'Web Keywords' );
			$person['dept']        = $dept; // YES!! Stop criticizing the redundancy... I know you are

			if ( ! isset( $personnel[ $dept ] ) ) {
				$personnel[ $dept ] = array();
			}

			$personnel[ $dept ][] = $person;
		}
	}

	sudbury_log( 'FILEMAKER: setting options' );
	$wp_personnel = array();
	// AH and now you see the logic behind the weird setup of $personnel
	$ids = array();
	foreach ( $personnel as $dept => $staff ) {

		// Sort the arrays by Web Order for Nice use in the future
		for ( $i = 0; $i < count( $staff ); $i ++ ) {
			uasort( $staff, function ( $a, $b ) {
				if ( $a['order'] == $b['order'] ) {
					return strcmp( $a['last_name'], $b['last_name'] );
				}

				return ( $a['order'] < $b['order'] ) ? - 1 : 1;
			} );

		}

		$id                  = sudbury_get_id_from_legacy_shortname( $dept );
		$wp_personnel[ $id ] = $staff;
		if ( $id ) {
			$ids[] = $id;
			switch_to_blog( $id );

			sudbury_log( "Setting Personnel for blog $id and dept $dept" );

			update_option( 'sudbury_fm_personnel', $staff );

			restore_current_blog();
		} else {
			sudbury_log( "There is no blog with slug: '$dept'... skipping" );
		}
	}

	$sites = wp_get_sites( array( 'limit' => false ) );

	array_map( function ( $id ) {
		switch_to_blog( $id );
		sudbury_log( 'Deleting All Personnel Records for ' . $id );
		delete_option( 'sudbury_fm_personnel' );
		restore_current_blog();
	}, array_diff( array_map( function ( $s ) {
		return $s['blog_id'];
	}, $sites ), $ids ) );

	update_site_option( 'sudbury_all_fm_personnel', $wp_personnel );

	sudbury_log( 'FILEMAKER: successfully completed sync with filemaker for Personnel Records... Starting Board Members' );

	/**********************************************************************
	 * ENTERING THE BOARD MEMBERSHIP SECTION OF THE FILEMAKER SYNC SCRIPT *
	 **********************************************************************/


	$sites = wp_get_sites( array( 'limit' => false ) );
	foreach ( $sites as $site ) {
		switch_to_blog( $site['blog_id'] );
		// I could see an argument to remove the ! is_committee() check but for now it is staying
		if ( ! is_committee() || ! $membership_key = get_option( 'sudbury_board_membership_key' ) ) {
			sudbury_log( get_bloginfo( 'name' ) . ' Is Not a Commitee or is lacking a board membership key' );
			restore_current_blog();

			continue;
		}
		$url = "http://filemaker.sudbury.ma.us/fmi/xsl/Statistics/BoardMembership.xsl?Board%20Name=" . urlencode( $membership_key );
		sudbury_log( 'Getting members for ' . $url );

		ini_set( "default_socket_timeout", 6 );
		$filemakerxml = file_get_contents( $url );
		if ( $filemakerxml ) {
			$members = array();
			$xml     = new SimpleXMLElement( $filemakerxml );
			foreach ( $xml->RESULTSET->ROW as $row ) {
				$members[] = array(
					"name"     => (string) $row->COL[0]->DATA,
					"position" => (string) $row->COL[1]->DATA,
					"address"  => (string) $row->COL[2]->DATA,
					"term"     => (string) $row->COL[6]->DATA,
					"end_date" => (string) $row->COL[7]->DATA,
					"appointed_by" => (string) $row->COL[8]->DATA
				);
			}

			update_option( 'sudbury_board_membership', $members );
			sudbury_log( "Setting Members for blog {$site['path']}" );
		} else {
			sudbury_log( $filemakerxml );
			sudbury_log( 'Is Not A Valid Response From FileMaker' );
		}
		restore_current_blog();

	}
	sudbury_log( "Getting ALl Members" );

	$master_members = array();

	$url = "http://filemaker.sudbury.ma.us/fmi/xsl/Statistics/BoardMembership.xsl";
	ini_set( "default_socket_timeout", 6 );
	$filemakerxml = @file_get_contents( $url );
	if ( $filemakerxml ) {
		$members = array();
		$xml     = new SimpleXMLElement( $filemakerxml );
		foreach ( $xml->RESULTSET->ROW as $row ) {
			$member = array(
				"Name Formal"            => (string) $row->COL[0]->DATA,
				"Status"                 => (string) $row->COL[1]->DATA,
				"Board Name"             => (string) $row->COL[3]->DATA,
				"Last Name"              => (string) $row->COL[4]->DATA,
				"First Appointment Year" => (string) $row->COL[5]->DATA,
				"Term"                   => (string) $row->COL[6]->DATA,
				"Term Expiration"        => (string) $row->COL[7]->DATA,
				"Appointed by"           => (string) $row->COL[8]->DATA,
				"Vacancy"                => (string) $row->COL[9]->DATA,
				"Last Name First"        => (string) $row->COL[10]->DATA,
				"Search Web Contact"     => (string) $row->COL[12]->DATA,
				"Web Keywords"           => (string) $row->COL[13]->DATA,
			);


			$info = sudbury_get_blog_info_by_option( 'sudbury_board_membership_key', $member['Board Name'] );

			if ( $info ) {
				$board                                = $info->blogname;
				$member['site']                       = $info;
				$member['site']->board_membership_key = $member['Board Name'];
			} else {
				$board = $member['Board Name'];
			}

			if ( ! isset( $master_members[ $board ] ) ) {
				$master_members[ $board ] = array();
			}

			$master_members[ $board ][] = $member;

			sudbury_log( "Setting Member " . $member['Name Formal'] );

		}
	}
	ksort( $master_members );
	update_site_option( 'sudbury_all_board_membership', $master_members );


	sudbury_log( 'FILEMAKER: successfully completed cron job... set options for known blogs' );
	wp_mail( 'webmaster@sudbury.ma.us', '[WordPress] FileMaker Sync Completed', 'WordPress Just Synced with FileMaker as ' . current_time( 'mysql' ) . '.  If this was an automated sync expect another sync in about 12 hours.  Manual FileMaker sync jobs do not affect the regular schedule.' );

	return true;
}

add_action( 'sudbury_cron_jobs_filemakersync', 'sudbury_filemaker_cron_job' );

/**
 * Registers the Cron Job
 */
function sudbury_cron_activation() {
	wp_schedule_event( time(), 'twicedaily', 'sudbury_cron_jobs_filemakersync' );
	wp_schedule_event( time(), 'always', 'sudbury_cron_logger' );
}

register_activation_hook( dirname( __FILE__ ) . '\\the-sudbury-plugin.php', 'sudbury_cron_activation' );

/**
 * Unregisters the Cron Job
 */
function sudbury_cron_deactivation() {
	wp_clear_scheduled_hook( 'sudbury_cron_jobs_filemakersync' );
	wp_clear_scheduled_hook( 'sudbury_cron_logger' );
}

register_deactivation_hook( dirname( __FILE__ ) . '\\the-sudbury-plugin.php', 'sudbury_cron_deactivation' );


/* General Cron Debugging Tools */


function cron_add_always( $schedules ) {
	// Adds a cron schedule that will always execute when cron is called
	$schedules['always'] = array(
		'interval' => 1,
		'display'  => __( 'Always' )
	);

	return $schedules;
}

add_filter( 'cron_schedules', 'cron_add_always' );

add_action( 'sudbury_cron_logger', 'sudbury_cron_logger' );
define( 'SUDBURY_DISABLE_CRON_LOG', true );
// This is a read only mockup of the wp-cron.php file to figure out the mess that is going on with wp-cron
function sudbury_cron_logger() {

	if ( defined( 'SUDBURY_DISABLE_CRON_LOG' ) && SUDBURY_DISABLE_CRON_LOG ) {
		return;
	}

	// this function will call sudbury_log Directly so d() and _d() shouldn't double log them
	if ( ! defined( 'SUDBURY_DOING_LOGGING' ) ) {
		define( 'SUDBURY_DOING_LOGGING', true );
	}

	global $crons, $gmt_time, $doing_wp_cron;
	sudbury_log( '=============== Sudbury Cron Log Start: ' . current_time( 'mysql' ) . ' ===============' );
	sudbury_log( 'Registered Crons' );
	sudbury_log( _d( $crons ) );
	foreach ( $crons as $timestamp => $cronhooks ) {
		if ( $timestamp > $gmt_time ) {
			sudbury_log( 'Breaking out of Crons at ' . _d( $cronhooks ) );
			break;
		}

		foreach ( $cronhooks as $hook => $keys ) {

			foreach ( $keys as $k => $v ) {

				$schedule = $v['schedule'];

				if ( $schedule != false ) {
					sudbury_log( 'Action "' . $hook . '" will be  rescheduled' );
				}

				sudbury_log( 'Action: ' . $hook . ' will be called with args ' . substr( _d( $v['args'] ), 0, 100 ) . '[...]' );

				// If the hook ran too long and another cron process stole the lock, quit.
				if ( _get_cron_lock() != $doing_wp_cron ) {
					sudbury_log( 'Cron Job Ran Too Long and another Process Has Taken Over... Normally only happens if you are running multiple Web Servers hosting the same Wordpress Database' );
				}
			}
		}
	}
	sudbury_log( '=============== Sudbury Cron Log End: ' . current_time( 'mysql' ) . ' ===============' );
}

