<?php
/**
 * Exposes functionality to interface with the Events Manager Plugin
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Events
 */

/**
 * This function will take a post and attempt to extract as much information that would be relevant to an event as possible
 *
 * @param $post_id
 *
 * @return array
 */
function sudbury_extract_event_args_from_post( $post_id ) {

	$post = get_post( $post_id );
	$meta = get_post_meta( $post_id );
	_sudbury_log( "Pulling info from $post_id for saving to events table" );
	_sudbury_log( $post );
	_sudbury_log( $meta );
	$args = array(
		'event_owner'         => $post->post_author,
		'event_start_date'    => ( isset( $meta['_event_start_date'] ) ? $meta['_event_start_date'][0] : $post->post_date ),
		'event_start_time'    => ( isset( $meta['_event_start_time'] ) ? $meta['_event_start_time'][0] : $post->post_date ),
		'event_end_date'      => ( isset( $meta['_event_end_date'] ) ? $meta['_event_end_date'][0] : $post->post_date ),
		'event_end_time'      => ( isset( $meta['_event_end_time'] ) ? $meta['_event_end_time'][0] : $post->post_date ),
		'event_rsvp'          => ( isset( $meta['_event_rsvp'] ) ? $meta['_event_rsvp'][0] : 0 ),
		'event_rsvp_date'     => ( ! empty( $meta['_event_rsvp_date'][0] ) ? $meta['_event_rsvp_date'][0] : null ),
		'event_rsvp_time'     => ( isset( $meta['_event_rsvp_time'] ) ? $meta['_event_rsvp_time'][0] : '00:00:00' ),
		'event_spaces'        => ( isset( $meta['_event_spaces'] ) ? intval( $meta['_event_spaces'][0] ) : 0 ),
		'event_status'        => ( ( 'publish' == $post->post_status ) || ( 'future' == $post->post_status && $post->post_type == 'meeting' ) ? 1 : - 1 ),
		'event_private'       => ( isset( $meta['_event_private'] ) ? $meta['_event_private'][0] : 0 ),
		'recurrence_id'       => ( ! empty( $meta['_recurrence_id'][0] ) ? $meta['_recurrence_id'][0] : null ),
		'recurrence'          => ( isset( $meta['_recurrence'] ) ? $meta['_recurrence'][0] : 0 ),
		'recurrence_interval' => ( ! empty( $meta['_recurrence_interval'][0] ) ? $meta['_recurrence_interval'][0] : null ),
		'recurrence_freq'     => ( ! empty( $meta['_recurrence_freq'][0] ) ? $meta['_recurrence_freq'][0] : null ),
		'recurrence_days'     => ( isset( $meta['_recurrence_days'] ) ? $meta['_recurrence_days'][0] : 0 ),
		'recurrence_byday'    => ( ! empty( $meta['_recurrence_byday'][0] ) ? $meta['_recurrence_byday'][0] : null ),
		'recurrence_byweekno' => ( ! empty( $meta['_recurrence_byweekno'][0] ) ? $meta['_recurrence_byweekno'][0] : null ),
		'event_all_day'       => 0,
		'post_content'        => $post->post_content,
		'event_slug'          => $post->post_name . '-event',
		'event_name'          => $post->post_title,
		'event_date_created'  => date( 'Y-m-d H:i:s' ),
		'event_date_modified' => date( 'Y-m-d H:i:s' ),
		'blog_id'             => get_current_blog_id(),
		'location_id'         => ( isset( $meta['_location_id'] ) ? $meta['_location_id'][0] : get_option( 'sudbury_default_event_location' ) ),
		'post_id'             => $post_id
		// The post that is linked to the Event (The $post_id)  This is a bit redundant
	);
	_sudbury_log( "Args: " );
	_sudbury_log( $args );
	$data = $_POST;
	$data = apply_filters( 'sudbury_event_args_extract', $data );
	_sudbury_log( "sudbury_extract_event_args_from_post Input Data:" );
	_sudbury_log( $data );
	foreach ( $data as $key => $value ) {
		// Overwrites both $key and $value
		if ( strstartswith( '_sudbury_', $key ) ) {
			$key = preg_replace( '/_sudbury_/', '', $key, 1 );

			// Do not create new keys that might not be a column
			/* Validation Stuff for NULL values */
			if ( 'event_rsvp_date' == $key && '' == $value ) {
				continue;
			}

			_sudbury_log( "\t\tSetting args[$key] = $value" );

			$args[ $key ] = $value;
		}
	}

	//filter NULLS because $wpdb is dumb: @link{https://core.trac.wordpress.org/ticket/15158}
	foreach ( $args as $key => $value ) {
		if ( is_null( $value ) ) {
			unset( $args[ $key ] );
			_sudbury_log( "Unset args[$key] because it was NULL" );
		}
	}

	return $args;
}

/**
 * Attaches an event to the post with the given ID
 *
 * @param            $post_id
 * @param bool|array $event_data
 *
 * @return bool
 */
function sudbury_add_event( $post_id, $event_data = false ) {
	global $wpdb;

	if ( ! sudbury_handle_events( $post_id ) ) {
		return false;
	}
	// If an event id post meta exists
	if ( $event_id = get_post_meta( $post_id, '_event_id', true ) ) {
		// ensure that there is an event really there
		if ( false !== sudbury_get_event_from_post_id( $post_id, get_current_blog_id() ) ) {
			_sudbury_log( '[error] Canceled Event Add because the post ' . $post_id . ' Already Has one' );

			return false;

		}

	}

	if ( false === $event_data ) {
		_sudbury_log( 'No Event Data provided to sudbury_add_event()... Extracting event data from post meta' );
		$event_data = sudbury_extract_event_args_from_post( $post_id );
	}

	// yes this can be condensed
	$result = $wpdb->insert( $wpdb->base_prefix . 'em_events', array(
		'event_id'            => $event_data['event_id'],
		'post_id'             => ( isset( $event_data['post_id'] ) ? $event_data['post_id'] : $post_id ),
		'event_slug'          => $event_data['event_slug'],
		'event_owner'         => $event_data['event_owner'],
		'event_status'        => $event_data['event_status'],
		'event_name'          => $event_data['event_name'],
		'event_start_time'    => $event_data['event_start_time'],
		'event_end_time'      => $event_data['event_end_time'],
		'event_all_day'       => $event_data['event_all_day'],
		'event_start_date'    => $event_data['event_start_date'],
		'event_end_date'      => $event_data['event_end_date'],
		'post_content'        => $event_data['post_content'],
		'event_rsvp'          => $event_data['event_rsvp'],
		'event_rsvp_date'     => $event_data['event_rsvp_date'],
		'event_rsvp_time'     => $event_data['event_rsvp_time'],
		'event_spaces'        => $event_data['event_spaces'],
		'event_private'       => $event_data['event_private'],
		'location_id'         => $event_data['location_id'],
		'recurrence_id'       => $event_data['recurrence_id'],
		'event_category_id'   => $event_data['event_category_id'],
		'event_attributes'    => $event_data['event_attributes'],
		'event_date_created'  => $event_data['event_date_created'],
		'event_date_modified' => $event_data['event_date_modified'],
		'recurrence'          => $event_data['recurrence'],
		'recurrence_interval' => $event_data['recurrence_interval'],
		'recurrence_freq'     => $event_data['recurrence_freq'],
		'recurrence_byday'    => $event_data['recurrence_byday'],
		'recurrence_byweekno' => $event_data['recurrence_byweekno'],
		'recurrence_days'     => $event_data['recurrence_days'],
		'blog_id'             => $event_data['blog_id'],
		'group_id'            => $event_data['group_id'],
	), array(
		'%d',
		'%d',
		'%s',
		'%d',
		'%d',
		'%s',
		'%s',
		'%s',
		'%d',
		'%s',
		'%s',
		'%s',
		'%d',
		'%s',
		'%s',
		'%d',
		'%d',
		'%d',
		'%d',
		'%d',
		'%s',
		'%s',
		'%s',
		'%d',
		'%d',
		'%s',
		'%s',
		'%d',
		'%d',
		'%d',
		'%d',
	) );

	if ( false == $result ) {
		_sudbury_log( 'Could Not Insert Event in sudbury_add_event() Database Error' );

		return false;
	}

	_sudbury_log( '[Success] Added Event ' . $wpdb->insert_id . ' to database and linked to post ' . $post_id );
	// Now we link the event record from wp_em_events to the $post_id
	update_post_meta( $post_id, '_event_id', $wpdb->insert_id );

	return $wpdb->insert_id;
}

/**
 * Pulls all the information form the em_events table into the given post's postmeta
 *
 * @param int $post_id  The Post Id to pull meta into
 * @param int $event_id The Event ID to get meta from
 */
function update_event_post_meta( $post_id, $event_id ) {

	if ( ! sudbury_handle_events( $post_id ) ) {
		return;
	}

	// Gets the Event from wp_em_events Table
	$event = sudbury_get_event( $event_id, ARRAY_A );

	update_post_meta( $post_id, "_event_id", $event_id );
	update_post_meta( $post_id, "_event_post_id", $event['post_id'] );
	$fields = array(
		"event_start_time",
		"event_end_time",
		"event_all_day",
		"event_start_date",
		"event_end_date",
		"event_rsvp",
		"event_rsvp_date",
		"event_rsvp_time",
		"event_spaces",
		"location_id",
		"recurrence_id",
		"event_status",
		"event_private",
		"event_date_created",
		"event_date_modified",
		"blog_id",
		"group_id",
		"recurrence",
		"recurrence_interval",
		"recurrence_freq",
		"recurrence_days",
		"recurrence_byday",
		"recurrence_byweekno"
	);

	foreach ( $event as $k => $v ) {
		if ( in_array( $k, $fields ) ) {
			update_post_meta( $post_id, "_$k", $v );
		}
	}

	// Calculate Event Start and End Date timestamps
	$start = mysql2date( 'U', $event['event_start_date'] . ' ' . $event['event_start_time'] );
	$end   = mysql2date( 'U', $event['event_end_date'] . ' ' . $event['event_end_time'] );

	update_post_meta( $post_id, '_event_start_timestamp', $start );
	update_post_meta( $post_id, '_event_end_timestamp', $end );
}

/**
 * This function will re-sync a post that has been manually linked to an event upon the post's save.
 * This is needed for when a news article is linked to an event and the post_content, event_start_time, ect is updated.
 *
 * @param int     $post_id Current Post ID being Saved
 * @param WP_Post $post    The Current Post Object
 */
function sudbury_post_to_event_sync( $post_id, $post ) {
	global $wpdb;
	if ( ! sudbury_handle_events( $post_id ) ) {
		return;
	}
	_sudbury_log( "[sudbury_post_to_event_sync] Function was called for " . $post_id );
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! in_array( $post->post_type, get_site_option( 'sudbury_linked_events_post_types' ) ) ) {
		return;
	}

	if ( ! isset( $_REQUEST['post_type'] ) ) {
		_sudbury_log( "[sudbury_post_to_event_sync] Not coming from the admin Edit Page... quit" );

		return;
	}

	$event        = sudbury_get_event_from_post_id( $post_id, get_current_blog_id(), OBJECT );
	$event_exists = ( false !== $event );
	_sudbury_log( "[sudbury_post_to_event_sync] " . ( $event_exists ? "Post $post_id already has an event" : "Post $post_id does not have an event" ) );
	/**
	 * Flow Diagram
	 *      IF checkbox '_is_event' is selected (== 'on') then run (UPDATE | INSERT)
	 *           IF $event !== false then run UPDATE
	 *           ELSE run INSERT
	 *      ELSE IF checkbox '_is_event' is NOT selected (!isset()) and $meta['_event_id'] exists then run DELETE
	 */

	if ( isset( $_REQUEST['_is_event'] ) && 'on' == $_REQUEST['_is_event'] ) {
		_sudbury_log( "[sudbury_post_to_event_sync] An Event is attached to post $post_id" );
		$update = array();

		if ( $event_exists ) { // New version is to be kept
			_sudbury_log( "[sudbury_post_to_event_sync] Will Update existing Event with EventID {$event->event_id}" );
			$update = sudbury_extract_event_args_from_post( $post_id );
			_sudbury_log( "[sudbury_post_to_event_sync] Will Update Event with the Following Info" );
			_sudbury_log( $update );

			if ( ! empty( $update ) ) {
				$wpdb->update( $wpdb->base_prefix . 'em_events', $update, array( 'event_id' => $event->event_id ) );
				_sudbury_log( "[sudbury_post_to_event_sync] Updated Event with EventID {$event->event_id}" );
			} else {
				_sudbury_log( "[sudbury_post_to_event_sync] Update Array is empty... skipping update" );
			}

			_sudbury_log( "[sudbury_post_to_event_sync] Will Now update the postmeta for Post $post_id" );
			update_event_post_meta( $post_id, $event->event_id );
		} else {
			$insert = sudbury_extract_event_args_from_post( $post_id );
			_sudbury_log( "[sudbury_post_to_event_sync] Will Insert a new Event into the wp_em_events table" );
			if ( ! empty( $insert ) ) {
				$result = $wpdb->insert( $wpdb->base_prefix . 'em_events', $insert );
				_sudbury_log( "[sudbury_post_to_event_sync] Inserted Into wp_em_events using wpdb" );
			} else {
				_sudbury_log( "[sudbury_post_to_event_sync] Insert was empty... This is not normal" );
			}

			$id = $wpdb->insert_id;
			_sudbury_log( "[sudbury_post_to_event_sync] New event has EventID $id" );

			_sudbury_log( "[sudbury_post_to_event_sync] Will Add postmeta for new Event #{$id} to Post $post_id" );
			update_event_post_meta( $post_id, $id );
		}
	} else {
		if ( $event_exists ) { // and no longer has event linked
			_sudbury_log( "[sudbury_post_to_event_sync] Deleting Event from post $post_id" );

			// The _event_id could have been deleted by save_post so lets make sure that an em_event is not orphaned by deleting it
			sudbury_delete_event_from_post( $post_id );
		}
	}
}

add_action( 'save_post', 'sudbury_post_to_event_sync', 10, 2 );

/**
 * @param int $post_id The post that has an Event attached to
 */
function sudbury_delete_event_from_post( $post_id ) {
	if ( ! sudbury_events_process_post_type( $post_id ) || sudbury_is_guest_post( $post_id ) ) {
		return;
	}
	_sudbury_log( 'Deleting events from post ' . $post_id );


	global $EM_Notices;
	$EM_Event = em_get_event( $post_id, 'post_id' );

	if ( is_null( $EM_Event->event_id ) ) {
		return;
	}

	$EM_Event->delete_meta();

	if ( is_object( $EM_Notices ) ) {
		$EM_Notices->remove_all(); //no validation/notices needed
	}
	_sudbury_delete_event_post_meta( $post_id );
	$guest_posts = sudbury_get_guest_posts( $post_id );
	foreach ( $guest_posts as $blog => $guest_post ) {
		if ( $blog == get_current_blog_id() || $guest_post == $post_id ) {
			continue;
		}
		switch_to_blog( $blog );
		_sudbury_delete_event_post_meta( $guest_post );
		restore_current_blog();
	}
}

add_action( 'before_delete_post', 'sudbury_delete_event_from_post', 1 );

/**
 * Deletes the _event_id post meta field but stores the old value into the _event_id_history post
 * meta list (multi-key) post meta
 *
 * @param int post_id The ID of the post to remove the event meta from
 *
 * @return bool True on successful deletion, false on failure (normally the post didn't have an _event_id)
 */
function _sudbury_delete_event_post_meta( $post_id ) {

	if ( ! sudbury_events_process_post_type( $post_id ) || sudbury_is_guest_post( $post_id ) ) {
		return;
	}

	if ( $event_id = get_post_meta( $post_id, '_event_id', true ) ) {
		delete_post_meta( $post_id, '_event_id' );
		add_post_meta( $post_id, '_event_id_history', $event_id );

		return true;
	} else {
		return false;
	}
}

/**
 * Trashes an event on meeting trash
 *
 * @param $post_id
 */
function sudbury_trash_event_from_post( $post_id ) {

	if ( ! sudbury_events_process_post_type( $post_id ) || sudbury_is_guest_post( $post_id ) ) {
		return;
	}

	$event = em_get_event( $post_id, 'post_id' );
	$event->delete( false );
}

add_action( 'trashed_post', 'sudbury_trash_event_from_post' );

/**
 * Gets an event from the wp_em_events table
 *
 * @param int    $event_id The Event ID From wp_em_events
 * @param string $output   How to output the event (OBJECT, ARRAY_N, or ARRAY_A)
 *
 * @return array|bool The Event with the Given $event_id or false if it doesn't exist
 */
function sudbury_get_event( $event_id, $output = ARRAY_A ) {
	global $wpdb;
	$events = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->base_prefix}em_events` WHERE `event_id` = %d", $event_id ), $output );
	if ( count( $events ) > 1 ) {
		wp_die( "<h2>Database Error</h2><p> There are multiple events registered to event_id '$event_id'.  Database corruption is amiss. Please contact a Support Engineer ->> Eddie Hurtig for Help ", "Database Error" );
	} else {
		if ( count( $events ) == 0 ) {
			return false;
		}
	}

	return $events[0];
}

/**
 * @param int    $post_id The ID of the Post that is linked to the Event
 * @param int    $blog_id The Blog ID of the Post that is Linked to the event
 * @param string $output  How to output the event (OBJECT, ARRAY_N, or ARRAY_A)
 *
 * @return array|bool  The Event that is linked to the specified post
 */
function sudbury_get_event_from_post_id( $post_id, $blog_id, $output = ARRAY_A ) {
	global $wpdb;
	$events = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->base_prefix}em_events` WHERE `post_id` = %d AND `blog_id` = %d", $post_id, $blog_id ), $output );
	if ( count( $events ) > 1 ) {
		$real_event = get_post_meta( $post_id, '_event_id', true );

		$html  = "<h2>Database Error</h2><p> There are multiple events registered to post '$post_id' of blog '$blog_id'.<ul>";
		$fixed = array();
		foreach ( $events as $event ) {
			if ( $output == ARRAY_A ) {
				$html     .= "<li>Event ID: {$event['event_id']} Title: {$event['event_name']} - {$event['event_start_date']} to {$event['event_end_date']}</li>";
				$event_id = $event['event_id'];
			} else {
				$html     .= "<li>Event ID: {$event->event_id} Title: {$event->event_name} - {$event->event_start_date} to {$event->event_end_date}</li>";
				$event_id = $event->event_id;
			}
			if ( $event_id && $event_id != $real_event ) {
				$fixed[ $event_id ] = $wpdb->delete( $wpdb->base_prefix . 'em_events', array( 'event_id' => $event_id ), array( '%d' ) );
			}
		}
		$html .= '</ul><p>Automatically Attempted to fix the problems: Results below</p><ul>';

		if ( ! empty( $fixed ) ) {
			foreach ( $fixed as $event_id => $deleted ) {
				if ( 1 === $deleted ) {
					$html .= '<li>Automatic Fixer Deleted Event ID ' . $event_id . '</li>';
				} elseif ( false === $deleted ) {
					$html .= '<li>Automatic Fixer Wanted to Delete Event ID ' . $event_id . ' But failed to do so</li>';
				} elseif ( false === $deleted ) {
					$html .= '<li>Automatic Fixer Somehow Deleted multiple events with the Unique event ID of ' . $event_id . ' (Event IDs should be unique)</li>';
				}
			}
		}

		$html .= '</ul>';

		$html .= 'If in doubt I refer you to ' . get_sudbury_contact_admin_message();

		wp_die( $html, "Database Error" );
	} elseif ( count( $events ) == 0 ) {
		return false;
	}

	return $events[0];
}

/**
 * @param int    $location_id The wp_em_locations Location ID (location_id)
 * @param string $output      How to output the location (OBJECT, ARRAY_N, or ARRAY_A)
 *
 * @return mixed The Location with the location_id of $id
 */
function sudbury_get_location( $location_id, $output = ARRAY_A ) {
	global $wpdb;

	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->base_prefix}em_locations` WHERE `location_id` = %d", $location_id ), $output );
}

function sudbury_get_raw_locations( $output ) {
	global $wpdb;
	$locations = $wpdb->get_results( "SELECT * FROM `{$wpdb->base_prefix}em_locations` ORDER BY location_name", $output );

	foreach ( $locations as $location ) {
		if ( ARRAY_A === $output ) {
			$post_id = $location['post_id'];
			$blog_id = $location['blog_id'];
		} elseif ( OBJECT === $output ) {
			$post_id = $location->post_id;
			$blog_id = $location->blog_id;
		} else {
			continue;
		}
		switch_to_blog( $blog_id );

		if ( is_null( get_post( $post_id ) ) ) {
			$result = $wpdb->delete( $wpdb->base_prefix . 'em_locations', array(
				'blog_id' => $blog_id,
				'post_id' => $post_id
			), array( '%d', '%d' ) );

			_sudbury_log( "[Database Error] [ERROR] Found a Location in em_locations that does not have a valid post attached to it... so we are deleting it. It has Post ID {$post_id} which is supposed to be on blog {$blog_id}" );

			if ( false === $result ) {
				_sudbury_log( "[Database Error] [ERROR] Failed to Delete Location from em_locations" );
			} elseif ( 1 === $result ) {
				_sudbury_log( "[Database Error] [SUCCESS] Fixed the corrupted Location" );
			} else {
				_sudbury_log( "[Database Error] [WARNING] DELETED MULTIPLE LOCATIONS" );
			}
		}

		restore_current_blog();
	}

	return $locations;
}

/**
 * Gets the post associated with the location id specified.  Will also retrieve meta information if requested
 *
 * @param                   $location_id                       The ID of the location
 * @param bool|string|array $meta                              will
 *
 * @return null|WP_Post
 */
function sudbury_get_location_post( $location_id, $meta = true, $query_args = array() ) {
	switch_to_blog( 1 );
	$query_args = array_merge( array(
		'post_type'  => 'location',
		'meta_key'   => '_location_id',
		'meta_value' => $location_id
	), $query_args );

	$post = get_posts( $query_args );

	if ( empty( $post ) ) {
		restore_current_blog();

		return null;
	}

	$post          = $post[0];
	$post_id       = $post->ID;
	$post->BLOG_ID = get_current_blog_id();

	if ( $meta ) {

		if ( $meta === true ) {
			$post->meta = get_post_meta( $post_id );
		} else {
			if ( is_string( $meta ) ) {
				$meta = array( $meta );
			}

			foreach ( $meta as $metakey ) {
				$post->meta[ $metakey ] = get_post_meta( $post_id, $metakey, true );
			}
		}
	}
	restore_current_blog();

	return $post;
}


/**
 * @param int      $post_id The ID of the Post that is linked to the Event
 * @param bool|int $blog_id The Blog ID of the Post that is Linked to the event. false for current blog id
 * @param string   $output  How to output the event (OBJECT, ARRAY_N, or ARRAY_A)
 *
 * @return array|bool  The Event that is linked to the specified post
 */
function sudbury_get_location_from_post_id( $post_id, $blog_id = false, $output = ARRAY_A ) {
	global $wpdb;
	if ( false == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$events = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->base_prefix}em_locations` WHERE `post_id` = %d AND `blog_id` = %d", $post_id, $blog_id ), $output );
	if ( count( $events ) > 1 ) {
		wp_die( " <h2>Database Error </h2>
<p> There are multiple events registered to post '$post_id' of blog '$blog_id' . Database corruption is amiss . Please contact a Support Engineer ->> Eddie Hurtig for Help</p>", "Database {
					Error}" );
	} elseif ( count( $events ) == 0 ) {
		return false;
	}

	return $events[0];
}

/**
 * Determines whether the given post should be proccessed by the sudbury event extensions (or if it should be ignored and let Event Manager handle it)
 *
 * @param int|WP_Post $post The Post ID or Post Object
 *
 * @return bool True if it should be handled by us
 */
function sudbury_handle_events( $post ) {
	$post = get_post( $post );
	$ids  = array();
	if ( isset( $_REQUEST['post'] ) ) {
		if ( is_array( $_REQUEST['post'] ) ) {
			$ids = $_REQUEST['post'];
		} else {
			$ids[] = $_REQUEST['post'];
		}
	}

	if ( isset( $_POST['post_ID'] ) ) {
		$ids[] = $_POST['post_ID'];
	}


	if ( ! in_array( $post->ID, $ids ) ) {
		// Trying to determine whether this save_post action has been called by admin postback or by wp_insert_post by multipost
		return false;
	}

	if ( sudbury_is_guest_post( $post->ID ) ) {
		return false;
	}

	return sudbury_events_process_post_type( $post );
}

function sudbury_events_process_post_type( $post ) {
	$post = get_post( $post );

	return in_array( $post->post_type, get_site_option( 'sudbury_linked_events_post_types', array() ) );
}

/**
 * Pushes warning notices to the user regarding the event they created
 */
function sudbury_event_notices() {
	// Post Save Validation (non-critical warning/advisory type things)
	if ( 'post' != get_current_screen()->base ) {
		return;
	}
	$post = get_post();
	if ( ! $post ) {
		return;
	}

	$event = sudbury_get_event_from_post_id( $post->ID, get_current_blog_id() );
	if ( ! isset( $event['event_start_date'] ) || ! isset( $event['event_end_date'] ) ) {
		return;
	}

	if ( $event['event_start_date'] != $event['event_end_date'] ) : ?>
		<div class="error">
			<p><?php _e( 'Warning! The Start and End dates of this event are different!', 'sudbury' ); ?></p>
		</div>
	<?php endif;

	$start = mysql2date( 'U', $event['event_start_date'] . ' ' . $event['event_start_time'] );
	$end   = mysql2date( 'U', $event['event_end_date'] . ' ' . $event['event_end_time'] );

	if ( $start > $end ) : ?>
		<div class="error">
			<p><?php _e( 'Error! The <strong>Start</strong> date is set later than the <strong>End</strong> date!', 'sudbury' ); ?></p>
		</div>
	<?php endif;
}

add_action( 'admin_notices', 'sudbury_event_notices' );

function sudbury_duplicate_post_blacklist( $meta_blacklist ) {
	$meta_blacklist[] = '_event_*'; // don't copy any post meta

	return $meta_blacklist;
}

add_filter( 'duplicate_post_blacklist_filter', 'sudbury_duplicate_post_blacklist', 10, 1 );

function get_buildings( $query = array() ) {
	$defaults = array(
		'post_type'           => 'location',
		'posts_per_page'      => - 1,
		'orderby'             => 'title',
		'order'               => 'ASC',
		'location_categories' => 'town-building'
	);

	$query = array_merge( $defaults, $query );

	return get_posts( $query );
}

function sudbury_event_is_happening( $post_id ) {
	$start_date = get_post_meta( $post_id, '_event_start_date', true );
	$start_time = get_post_meta( $post_id, '_event_start_time', true );
	$end_date   = get_post_meta( $post_id, '_event_end_date', true );
	$end_time   = get_post_meta( $post_id, '_event_end_time', true );

	$start_timestamp = mysql2date( 'U', $start_date . ' ' . $start_time );
	$end_timestamp   = mysql2date( 'U', $end_date . ' ' . $end_time );
	$now_timestamp   = mysql2date( 'U', current_time( 'mysql' ) );

	return ( $start_timestamp < $now_timestamp && $now_timestamp < $end_timestamp );
}

function sudbury_event_is_today( $post_id ) {
	$start_date = get_post_meta( $post_id, '_event_start_date', true );

	$start_timestamp = mysql2date( 'U', $start_date );
	$now_timestamp   = mysql2date( 'U', current_time( 'Y-m-d' ) );

	return ( $start_timestamp == $now_timestamp );
}

function sudbury_event_is_over( $post_id ) {
	$end_date = get_post_meta( $post_id, '_event_end_date', true );
	$end_time = get_post_meta( $post_id, '_event_end_time', true );

	$end_timestamp = mysql2date( 'U', $end_date . ' ' . $end_time );
	$now_timestamp = mysql2date( 'U', current_time( 'mysql' ) );

	return ( $end_timestamp < $now_timestamp );
}

function sudbury_has_event( $post_id ) {
	return (bool) get_post_meta( $post_id, '_event_id', true );
}