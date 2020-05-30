<?php
/**
 * Adds functionality specific to the meetings Custom Post Type
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Meetings
 */

class Sudbury_Meeting_Manager {


	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * Add meetings removal request admin page
	 */
	function init() {
		// This should not show up in the menus, it should only be registered as a page when necessary
		if ( is_admin() && isset( $_REQUEST['page'] ) && 'meeting-removal-request' == $_REQUEST['page'] ) {
			add_menu_page( 'Meeting Removal Request', 'Delete Meeting', 'delete_posts', 'meeting-removal-request', array(
				&$this,
				'meeting_removal_page'
			), '', 7 );
		}
		add_action( 'pre_get_posts', 'sudbury_meeting_query' );

		add_action( 'save_post', array( &$this, 'save' ), 50 );

	}

	function save( $post_id ) {
		$this->rename( $post_id );
		$this->set_title( $post_id );
	}

	/**
	 * Changes the file name for agendas and minutes
	 *
	 * @param int $post_id The id of the meeting being saved or published
	 */
	function rename( $post_id ) {
		_sudbury_log( "[sudbury_rename_meeting_attachments] Meeting Attachments Function Called" );
		// sudbury_set_meeting_title() was causing double calls to this function
		if ( isset( $GLOBALS['sudbury_set_meeting_title_lock'] ) ) {
			return;
		}

		// We only want to deal with meetings
		if ( 'meeting' != get_post_type( $post_id ) ) {
			return;
		}
		// Get the meeting event information (We need the date in order to rename the file)
		$event = sudbury_get_event_from_post_id( $post_id, get_current_blog_id() );
		// If they didn't publish an event then we can't do anything... quit
		if ( false === $event ) {
			return;
		}
		// Get the attachments instance (see @link{https://wordpress.org/plugins/attachments/})
		$attachments = new Attachments( 'attachments', $post_id );

		// Do we have any attachments for this meeting
		if ( $attachments->exist() ) {
			_sudbury_log( "[sudbury_rename_meeting_attachments] Attachments Exist, Will Loop through them" );
			$attachments_meta = json_decode( get_post_meta( $post_id, 'attachments', true ), true );

			while ( $attachments->get() ) {
				_sudbury_log( "[sudbury_rename_meeting_attachments] Proccessing Attachment " . $attachments->id() );
				// Sanitize the Blogname for use in the new filename
				$committee = preg_replace( "/[^a-zA-Z0-9]/", '', ucwords( get_bloginfo( 'blogname' ) ) );

				// is it an agenda, minutes, or other
				$type            = sudbury_guess_attachment_type( $attachments );
				$types_to_rename = array(
					'agenda',
					'minutes',
					'executive-minutes',
					'other'
				); // We only rename minutes and agendas

				// ensure indefinite
				update_post_meta( $attachments->id(), 'sudbury_end_date', date( 'm/d/Y H:i', 2147483647 ) );
				update_post_meta( $attachments->id(), 'sudbury_end_date_timestamp', 2147483647 );

				if ( in_array( $type, $types_to_rename ) ) {


					// format the date of the meeting to Y_M_d format
					$date_stamp = mysql2date( 'Y_M_d', $event['event_start_date'] );

					// Get the full file path of the meeting minutes or agenda file
					$file = get_attached_file( $attachments->id() );

					$path = pathinfo( $file );

					// Join it all together
					// Known Bug: Need to incorporate a copy extension into this expected file name or else it will try to rename every time if there are duplicate files
					$type_file = str_replace( '-', '_', $type );
					if ( $type_file == 'other' ) {
						$type_file = 'supporting_materials';
					}
					$new_file = $path['dirname'] . "/" . $committee . '_' . $date_stamp . '_' . $type_file . "." . $path['extension'];
					if ( $new_file != $file ) {
						// Making sure that the file name is unique and we don't overwrite something important
						// will rename file.txt -> file_2.txt -> file_3.txt and so on until there is no conflict
						// Respects multiple extensions to archive.tar.gz -> archive_2.tar.gz -> archive_3.tar.gz
						// does a check with the original file to try and address bug noted above
						if ( file_exists( $new_file ) ) {

							$new_file = basename( $new_file );
							$i        = 2;
							$parts    = explode( '.', $new_file );
							$first    = $path['dirname'] . "/" . $parts[0] . '_';
							$ext      = '.' . implode( '.', array_slice( $parts, 1, count( $parts ) ) );
							while ( file_exists( $first . $i . $ext ) && ( $first . $i . $ext ) != $file ) {
								$i ++;
							}
							$new_file = $first . $i . $ext;
						}
						// After all that ensuure that the new file is different
						if ( $new_file != $file ) {
							// Rename the file
							_sudbury_log( "[FILESYSTEM] Be advised that WordPress is renaming file $file to $new_file" );
							rename( $file, $new_file );

							// Update the database with the new file name
							if ( ! update_attached_file( $attachments->id(), $new_file ) ) {
								_sudbury_log( "[ERROR] Failed to update attachment id " . $attachments->id() . "'s filename from $file to $new_file" );
							}
						} else {
							_sudbury_log( "[NOTICE]Skipping File Rename because the new and old filenames are the same: $file" );
						}
					} else {
						_sudbury_log( "File $file does not need renaming" );
					}

					if ( $type != 'other' ) {
						$type_title = ucwords( str_replace( '-', ' ', $type ) );
						$new_title  = ucwords( get_bloginfo( 'blogname' ) ) . ' ' . mysql2date( 'M/d/Y', $event['event_start_date'] ) . ' ' . $type_title;
						// Then Media Attachment's post_title is the same as the title in Attachments... Lets rename the Atachments title for it to

						foreach ( $attachments_meta['attachments'] as $i => $attachment ) {
							if ( $attachment['id'] == $attachments->id() ) {
								$attachments_meta['attachments'][ $i ]['fields']['title'] = $new_title;
								break;
							}
						}

						wp_update_post( array( 'ID' => $attachments->id(), 'post_title' => $new_title ) );

						// States that the file is a minutes or agenda file
						update_post_meta( $attachments->id(), 'sudbury_meeting_document', true );
						// States the file's document type (minutes or agenda)
						update_post_meta( $attachments->id(), 'sudbury_meeting_document_type', $type );
					}
				} else {
					_sudbury_log( "[sudbury_rename_meeting_attachments] Attachment " . $attachments->id() . " is recognized as $type which is not in the list " . implode( ',', $types_to_rename ) );
				}

				// States that it is attached to a meeting and the Post ID that it is attached to
				update_post_meta( $attachments->id(), 'sudbury_attached_meeting', $post_id );
				// States that it is a meeting attachment
				update_post_meta( $attachments->id(), 'sudbury_meeting_attachment', true );
				// States the meeting attachment type
				update_post_meta( $attachments->id(), 'sudbury_meeting_attachment_type', $type );

				$meeting_doc_categories = array();

				$regular_meeting_document = get_term_by( 'slug', 'meeting-documents', 'document_categories' );

				if ( $regular_meeting_document ) {
					$meeting_doc_categories[] = (int) $regular_meeting_document->term_id;
				}


				$type_category = get_term_by( 'slug', 'meeting-' . $type, 'document_categories' );

				if ( $type_category ) {
					$meeting_doc_categories[] = $type_category;
				}

				$meeting_doc_categories = apply_filters( 'sudbury_meeting_doc_categories', $meeting_doc_categories, $attachments->id(), $type );
				wp_set_object_terms( $attachments->id(), $meeting_doc_categories, 'document_categories', true );
			}

			update_post_meta( $post_id, 'attachments', json_encode( $attachments_meta ) );
		} else {
			_sudbury_log( "[sudbury_rename_meeting_attachments] There are no attachments for this meeting" );
		}
	}

	/**
	 * Sets the post_title of a meeting whenever it is updated
	 */
	function set_title( $post_id ) {


		if ( 'meeting' != get_post_type( $post_id ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Check for Lock
		if ( isset( $GLOBALS['sudbury_set_meeting_title_lock'] ) ) {
			// Clear the Lock
			unset( $GLOBALS['sudbury_set_meeting_title_lock'] );

			// get out of here
			return;
		}

		$post      = get_post( $post_id );
		$timestamp = intval( get_post_meta( $post_id, '_event_start_timestamp', true ) );
		$title     = get_bloginfo( 'name' ) . ' Meeting: ' . date( 'l, F j, Y', $timestamp );

		if ( $timestamp && $post->post_title != $title ) {
			$GLOBALS['sudbury_set_meeting_title_lock'] = true;
			wp_update_post( array( 'ID' => $post_id, 'post_title' => $title ) );
		}

		// Update the slug to reflect the title if it changed
		$new_slug = sanitize_title_with_dashes( $title );
		if ( $timestamp && $post->post_name != $new_slug ) {
			$new_slug                                  = wp_unique_post_slug( $new_slug, $post->ID, $post->post_status, $post->post_type, $post->post_parent );
			$GLOBALS['sudbury_set_meeting_title_lock'] = true;
			wp_update_post( array( 'ID' => $post_id, 'post_name' => $new_slug ) );
		}

		// Update the slug to reflect the title if it changed
		$new_date = date( 'Y-m-d H:i:s', $timestamp );
		if ( $timestamp && $post->post_date != $new_date ) {
			$GLOBALS['sudbury_set_meeting_title_lock'] = true;
			wp_update_post( array( 'ID' => $post_id, 'post_date' => $new_date ) );
		}
		// We are forcing all meetings to be under the publish post_status... this is against wordpress core's wishes
		// Enqueued for better solution
		if ( get_post_status( $post_id ) == 'future' ) {
			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'post_status' => 'publish' ), array( 'ID' => $post_id ), '%s' );
		}
	}

	function meeting_removal_page() {
		sudbury_meeting_removal_request_page();
	}
}

new Sudbury_Meeting_Manager();

/**
 * forces a query for meetings to include meetings in the future
 *
 * @param WP_Query $query The query
 */
function sudbury_meeting_query( $query ) {

	if ( $query->get( 'post_type' ) == 'meeting' ) {

		$query->query['post_status'] = array( 'future', 'publish' );

	}
}


/**
 * Guesses whether the attachments is an agenda or minutes
 *
 * PS I hate this function... If there is a better way of automating the determination of minutes vs agendas please lmk
 *
 * @param Attachments $attachments
 *
 * @return string
 */
function sudbury_guess_attachment_type( $attachments ) {

	if ( isset( $_REQUEST['attachment_type'][ $attachments->id() ] ) ) {
		return sanitize_title( $_REQUEST['attachment_type'][ $attachments->id() ] );
	}

	// Well maybe they attached a presentation or something
	return 'other';
}

add_action( 'edit_form_after_title', function () {
	echo '<div id="sudbury-delete-attachment-nonce" class="hidden">' . wp_create_nonce( 'sudbury-delete-attachment' ) . '</div>';
} );


/**
 * Prevents a meeting from being permanently deleted by a non-super admin
 *
 * @param $post_id
 */
function sudbury_delete_meeting( $post_id ) {
	_sudbury_log( '[sudbury_delete_meeting] function called with post id ' . $post_id );
	if ( 'meeting' != get_post_type( $post_id ) ) {
		return;
	}
	// if you aren't a super admin or a cron script halt everything
	if ( is_super_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		// OK We are either emptying trash or super admin... delete it
	} else {

		$user = 'Automated System';
		if ( is_user_logged_in() ) {
			$user = get_current_user();
		}
		wp_mail( 'wordpress@sudbury.ma.us', 'Meeting Deletion Halted!', 'The User "' . $user . '" just attempted to delete the meeting "' . get_the_title( $post_id ) . '" at ' . current_time( 'mysql' ) );

		wp_die( "STOP! Meetings Can Not Be Deleted Without Administrative Approval! This incident has been reported." );
		exit;
	}

	// Ok we are actually going to delete it, lets delete all the attachments

	$attachments = new Attachments( 'attachments', $post_id );

	_sudbury_log( '[sudbury_delete_meeting] Deleting attachments for ' . $post_id );

	if ( $attachments->exist() ) {
		_sudbury_log( "[sudbury_rename_meeting_attachments] Attachments Exist, Will Loop through them" );

		while ( $attachments->get() ) {
			if ( get_post_meta( $attachments->id(), 'sudbury_meeting_attachment', true ) ) {
				if ( false === wp_delete_attachment( $attachments->id(), true ) ) {
					_sudbury_log( "[ERROR] Could not delete attachment " . $attachments->id() );
				} else {
					_sudbury_log( "[SUCCESS] Deleted attachment " . $attachments->id() );
				}
			} else {
				_sudbury_log( "[INFO] Not Deleting Attachment " . $attachments->id() );
			}
		}
	}
	_sudbury_log( '[sudbury_delete_meeting] Deleted attachments for ' . $post_id );

}

add_action( 'before_delete_post', 'sudbury_delete_meeting' );


/* Start of the API */

/**
 * Gets a List of Attachments for the $meeting with the specified $type
 *
 * @param int|WP_Post $meeting The Meeting
 * @param string      $type    The Meeting Attachment type, currently only 'agenda', 'minutes', or 'other'
 * @param bool|int    $blog_id The Blog ID that the meeting and it's attachments reside on.  false for current Blog ID, Default false
 * @param string      $output  The output type, 'posts' for an array of the Attachment Posts, 'attachments' for a clone of the Attachments class, Default: 'posts'
 *
 * @return array An array of attachments for the specified $meeting of the specified $type in the specified $output format
 */
function sudbury_get_meeting_attachments( $meeting, $type = 'all', $blog_id = false, $output = 'posts' ) {
	if ( false === $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	switch_to_blog( $blog_id );

	$meeting     = get_post( $meeting );
	$attachments = new Attachments( 'attachments', $meeting->ID );
	$agendas     = array();
	if ( $attachments->exist() ) {
		while ( $attachments->get() ) {
			if ( $type == 'all' || $type == sudbury_get_meeting_attachment_type( $attachments->id() ) ) {
				switch ( $output ) {
					case 'posts':
						$agendas[] = get_post( $attachments->id() );
						break;
					case 'attachments' :
						$agendas[] = clone $attachments;
						break;
					default :
						_doing_it_wrong( 'sudbury_get_meeting_attachments', 'Invalid Output Type Specified.  Please use \'posts\' for an array of attachment WP_Post objects and \'attachments\' for a clone of the current Attachments class', '3.9.1' );
				}
			}
		}
	}

	restore_current_blog();

	return apply_filters( 'sudbury_get_meeting_attachments', $agendas, $meeting, $type, $blog_id, $output );
}

/**
 * Gets the type of attachment (minutes, agenda, ect) that the given attachment is.
 *
 * @param $attachment_id The ID of the attachment on the current site to return the type of
 *
 * @return string The type of attachment or NULL if there is no associated type
 */
function sudbury_get_meeting_attachment_type( $attachment_id ) {
	return get_post_meta( $attachment_id, 'sudbury_meeting_attachment_type', true );
}

/**
 * Returns an array of Agendas attached to the specified meeting
 *
 * @param int|WP_Post $meeting Post ID or post object
 * @param bool|int    $blog_id The Blog ID that the Meeting resides in, Default: Current Blog ID
 * @param string      $output  The type of output, use 'posts' for an array of attachment WP_Post objects and 'attachments' for a clone of the Attachments class set to the attachment itself
 *
 * @return array An Array of attachments that are agendas
 */
function sudbury_get_meeting_agendas( $meeting, $blog_id = false, $output = 'posts' ) {
	return sudbury_get_meeting_attachments( $meeting, 'agenda', $blog_id, $output );
}

/**
 * Returns an array of Agendas attached to the specified meeting
 *
 * @param int|WP_Post $meeting Post ID or post object
 * @param bool|int    $blog_id The Blog ID that the Meeting resides in, Default: Current Blog ID
 *
 * @return bool Whether there are any agendas associated with the specified meeting
 */
function sudbury_has_meeting_agendas( $meeting, $blog_id = false ) {
	$results = sudbury_get_meeting_agendas( $meeting, $blog_id, 'attachments' );

	return ! empty( $results ); // PHP 5.4 does not support making this function a 1-liner :-(
}

/**
 * Returns an array of minutes attached to the specified meeting
 *
 * @param int|WP_Post $meeting Post ID or post object
 * @param bool|int    $blog_id The Blog ID that the Meeting resides in, Default: Current Blog ID
 * @param string      $output  The type of output, use 'posts' for an array of attachment WP_Post objects and 'attachments' for a clone of the Attachments class set to the attachment itself
 *
 * @return array An Array of attachments that are minutes
 */
function sudbury_get_meeting_minutes( $meeting, $blog_id = false, $output = 'posts' ) {
	return sudbury_get_meeting_attachments( $meeting, 'minutes', $blog_id, $output );
}

/**
 * Returns an array of minutes attached to the specified meeting
 *
 * @param int|WP_Post $meeting Post ID or post object
 * @param bool|int    $blog_id The Blog ID that the Meeting resides in, Default: Current Blog ID
 *
 * @return bool Whether there are any minutes associated with the specified meeting
 */
function sudbury_has_meeting_minutes( $meeting, $blog_id = false ) {
	$results = sudbury_get_meeting_minutes( $meeting, $blog_id, 'attachments' );

	return ! empty( $results ); // PHP 5.4 does not support making this function a 1-liner :-(
}

/**
 * Returns an array of other attached to the specified meeting
 *
 * @param int|WP_Post $meeting Post ID or post object
 * @param bool|int    $blog_id The Blog ID that the Meeting resides in, Default: Current Blog ID
 * @param string      $output  The type of output, use 'posts' for an array of attachment WP_Post objects and 'attachments' for a clone of the Attachments class set to the attachment itself
 *
 * @return array An Array of attachments that are other
 */
function sudbury_get_meeting_others( $meeting, $blog_id = false, $output = 'posts' ) {
	return sudbury_get_meeting_attachments( $meeting, 'other', $blog_id, $output );
}

/**
 * Returns an array of others attached to the specified meeting
 *
 * @param int|WP_Post $meeting Post ID or post object
 * @param bool|int    $blog_id The Blog ID that the Meeting resides in, Default: Current Blog ID
 *
 * @return bool Whether there are any others associated with the specified meeting
 */
function sudbury_has_meeting_others( $meeting, $blog_id = false ) {
	$results = sudbury_get_meeting_others( $meeting, $blog_id, 'attachments' );

	return ! empty( $results ); // PHP 5.4 does not support making this function a 1-liner :-(
}

/**
 * Returns Event records for meetings that are occurring between last sunday and next sunday in the given Location ID,
 * or any sub locations of the given location
 *
 * @param int $location_id The Event Manager ID of the Location
 */
function sudbury_meetings_in_location_this_week( $location_id ) {
	$meeting_rooms = network_query_posts( array(
		'post_type'   => 'location',
		'post_status' => array(
			'publish',
			'private',
			'public-archive',
		),
		'meta_key'    => 'sudbury_parent_location_id',
		'meta_value'  => $location_id,
	) );

	$location_ids = array_map( function ( $location ) {
		return network_get_post_meta( $location, '_location_id', true );
	}, $meeting_rooms );

	$location_ids[] = $location_id;
	global $wpdb;

	$start = date( 'Y-m-d', strtotime( 'last sunday' ) );
	$end   = date( 'Y-m-d', strtotime( 'next sunday' ) );

	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->base_prefix}em_events WHERE location_id IN ( " . implode( ', ', $location_ids ) . ") AND event_start_date > %s AND event_start_date < %s AND event_status = 1 ORDER BY event_start_date, event_start_time", $start, $end ) );

}

/**
 * Returns Meetings Happening today
 *
 * @param string $output The output you would like, either 'meeting' for meeting posts or 'event' for em_event records
 *
 * @return array List of meetings happening today
 */
function sudbury_get_todays_network_meetings( $output = 'posts' ) {
	return sudbury_get_network_meetings_by_time_frame( mktime( 0, 0, 0 ), mktime( 0, 0, 0 ) + 24 * 60 * 60, $output );
}


/**
 * Returns meetings that start within the specified time frame
 *
 * @param int    $start  Timestamp for start
 * @param int    $end    Timestamp for end
 * @param string $output The Output Type
 *
 * @return array List of Events or Meeting Posts
 */
function sudbury_get_network_meetings_by_time_frame( $start, $end, $output = 'meeting' ) {

	$query = array(
		'post_type'      => 'meeting',
		'meta_query'     => array(
			array(
				'key'     => '_event_start_timestamp',
				'value'   => $start,
				'compare' => '>',
			),
			array(
				'key'     => '_event_start_timestamp',
				'value'   => $end,
				'compare' => '<',
			)
		),
		'posts_per_page' => - 1
	);
	$query = apply_filters( 'sudbury_get_network_meetings_by_time_frame_query', $query, $start, $end );

	$meetings = network_query_posts( $query );

	foreach ( $meetings as $index => $meeting ) {
		switch ( $output ) {
			case 'meeting' :
				break;
			case 'event' :
				$meetings[ $index ] = sudbury_get_event_from_post_id( $meeting->ID, $meeting->BLOG_ID, OBJECT );
				break;
			default :
				_doing_it_wrong( 'sudbury_get_network_meetings_by_time_frame', 'Output Type is Invalid.  Please specifiy either \'meeting\' or \'event\' as your output type', '3.9.1' );
		}
	}

	return apply_filters( 'sudbury_get_network_meetings_by_time_frame', $meetings, $start, $end, $output );
}


/**
 * Prints the Meeting Minutes and Agendas to the Meetings CPT Admin Table
 *
 * @param string $column  The Column Currently being Rendered
 * @param int    $post_id The Post ID (Row) being rendered
 */
function sudbury_admin_meetings_table_columns( $column, $post_id ) {

	switch ( $column ) {
		case 'attachments':
			if ( $minutes = sudbury_get_meeting_minutes( $post_id ) ) {
				foreach ( $minutes as $minute ) : ?>
					<a href="<?php echo get_permalink( $minute ); ?>">Minutes</a><br />
				<?php endforeach;
			}
			if ( $agendas = sudbury_get_meeting_agendas( $post_id ) ) {
				foreach ( $agendas as $agenda ) : ?>
					<a href="<?php echo get_permalink( $agenda ); ?>">Agenda</a><br />
				<?php endforeach;
			}
			break;
		case 'location' :
			$location = sudbury_get_location( get_post_meta( $post_id, '_location_id', true ) );
			?>

			<a href="/location/<?php echo esc_attr( $location['location_slug'] ); ?>"><?php echo esc_html( $location['location_name'] ); ?></a>
			<?php
			break;
		case 'notes' :
			echo get_post( $post_id )->post_excerpt;
			break;
		case 'start' :
			echo date( "g:ia", strtotime( get_post_meta( $post_id, '_event_start_time', true ) ) );
			break;
		default :
			break;
	}
}

add_action( 'manage_meeting_posts_custom_column', 'sudbury_admin_meetings_table_columns', 10, 2 );

/**
 * Adds a columns for the Meetings Custom Post Type Admin UI
 *
 * @param array $columns The Existing Columns
 *
 * @return array The New List of Columns
 */
function sudbury_add_meeting_columns( $columns ) {
	return array_merge( $columns,
		array(
			'start'       => __( 'Start Time', 'sudbury' ),
			'attachments' => __( 'Attachments', 'sudbury' ),
			'location'    => __( 'Location', 'sudbury' ),
			'notes'       => __( 'Notes', 'sudbury' )
		) );
}

add_filter( 'manage_meeting_posts_columns', 'sudbury_add_meeting_columns' );


function sudbury_strip_meeting_title( $title ) {
	if ( is_admin() ) {
		return $title;
	}

	if ( ! strpos( $title, ':' ) || ! strpos( $title, 'Meeting' ) ) {
		return $title;
	}

	return substr( $title, 0, strpos( $title, ':' ) );
}

add_filter( 'the_title', 'sudbury_strip_meeting_title' );

function sudbury_rss_post_types( $qv ) {
	if ( isset( $qv['feed'] ) ) {
		$qv['post_type'] = array( 'post', 'meeting', 'attachment' );
	}

	return $qv;
}

add_filter( 'request', 'sudbury_rss_post_types' );


/**
 * @param WP_Post  $post
 * @param bool|int $blog_id
 *
 * @return bool Whether the specified attachment is a meeting document
 */
function sudbury_is_meeting_document( $post, $blog_id = false ) {
	if ( false === $blog_id && isset( $post->BLOG_ID ) ) {
		$blog_id = $post->BLOG_ID;
	}
	switch_to_blog( $blog_id );

	$is_meeting_doc = get_post_meta( $post->ID, 'sudbury_meeting_attachment', true ) || get_post_meta( $post->ID, 'sudbury_meeting_document', true ) || get_post_meta( $post->ID, 'sudbury_doc_cat', true ) == 'Meeting Documents';

	restore_current_blog();

	return (bool) $is_meeting_doc;
}