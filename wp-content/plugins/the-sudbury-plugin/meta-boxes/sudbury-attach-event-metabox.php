<?php
/**
 * Metabox that displays the UI for creating and managing an event to be published with this post
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Admin
 */

/**
 * Events are linked by their Location ID (Not their Location Post's ID)
 *
 * I could be convinced that this is some awful code...
 *
 */
function sudbury_attach_event_metabox() {
	global $wpdb;

	// The ID of the Post we are allowing a user to attach an event to
	$post_id = get_the_ID();
	// The post meta for this post
	$meta = get_post_meta( $post_id );

	// The Event ID (for wp_em_events) that this post is already attached to... or false
	$event_id = isset( $meta['_event_id'][0] ) ? $meta['_event_id'][0] : false;

	// Is this post attached to an event
	$has_event = $event_id != false;

	// Time in seconds: The Default Duration of an Event (useful for meetings)
	$default_duration = get_option( 'sudbury_default_event_duration', 2 * 60 * 60 );
	// The Default Start Time for an event (Expressed in HH:MM format) (useful for meetings)
	$default_start_time = get_option( 'sudbury_default_event_start_time', '20:00' );
	// The Default Number of days between Events
	$default_days_between = get_option( 'sudbury_default_event_days_between', 28 );
	// The Default Location for Events (Like a meeting room that they always use)
	$default_location = intval( get_option( 'sudbury_default_event_location', 0 ) );

	// Checking to see if an event has already been attached or if this is a new event
	if ( $has_event ) {
		// This event is not a new event... Lets load up the stored start date and time from post meta
		$event_start_date = strtotime( $meta['_event_start_date'][0] );
		$event_start_time = strtotime( $meta['_event_start_time'][0] ) - mktime( 0, 0, 0 );

	} else {
		// This is a new Event
		// Get the Post ID of the Last Event that was created by this Department or Committee
		$last_event_pid = $wpdb->get_results( $wpdb->prepare( "SELECT `post_id` FROM `{$wpdb->base_prefix}em_events` WHERE `blog_id` = %d ORDER BY `event_start_time` DESC LIMIT 1", get_current_blog_id() ) );

		// Check if that post is a valid Event Post
		if ( ! empty( $last_event_pid ) && array_key_exists( '_event_id', $last_event_meta = get_post_meta( $last_event_pid = current( $last_event_pid )->post_id ) ) ) {
			// The Last Event is valid... Lets get it's start date and set our start date to be that date + our committee's Default Days Between
			$event_start_date = strtotime( $last_event_meta['_event_start_date'][0] ) + $default_days_between * 24 * 60 * 60;
			// And lets set the time to the Default time for this committee
			$event_start_time = strtotime( $default_start_time ) - $event_start_date;
		} else {
			// No It wasn't valid so let's generate a start time with our own logic... that is at the next occurence of this committees default start time...
			// so something like 7:00 tonight
			$event_start_date = mktime( 0, 0, 0 );
			$event_start_time = strtotime( $default_start_time ) - mktime( 0, 0, 0 );
		}
	}

	// PLEASE READ:
	// Note that we store our dates and times as numbers... these are unix timestamps that are (UTC time? GMT Time? Local Time?) need to confirm that
	// The Date is the number of seconds to the start of the date represented... (Timezone???)
	// The Time is the number of seconds that that time represents, so 12:01 AM is 60,  1:00 AM is 3600,  and 1 PM is 46800
	// The Max Value for the Time is 11:59 PM or 86340


	// If we already have an Event End Date then use that, If not then Lets just use the Start Date that we determined above
	if ( isset( $meta['_event_end_date'] ) ) {
		// We already have an end date defined... get it from meta
		$event_end_date = strtotime( $meta['_event_end_date'][0] );
	} else {
		// use the start date as the end date (assuming that most events are single day events)
		$event_end_date = $event_start_date;
	}


	// Use the defined end time or if not set in meta use start time + default duration (seconds)
	if ( isset( $meta['_event_end_time'] ) ) {
		// We already defined an end time
		$event_end_time = strtotime( $meta['_event_end_time'][0] ) - mktime( 0, 0, 0 );
	} else {
		// Lets generate an end time based on the start time and the default duration
		$event_end_time = $default_duration + $event_start_time;
	}

	// The Location ID (wp_em_locations) of the Event... If new event then use the Default Location.  If 0 then leave as 0
	if ( $has_event || ( isset( $meta['_location_id'] ) && location_exists( $meta['_location_id'] ) ) ) {
		$location_id = $meta['_location_id'][0];
	} else {
		$location_id = $default_location;
	}

	// The Full Timestamp for the Start Time
	$event_start_datetime = $event_start_date + $event_start_time;
	// The Full Timestamp for the End Time
	$event_end_datetime = $event_end_date + $event_end_time;
	// is a Event is required for this post? true or false
	$event_required = in_array( get_post_type( $post_id ), get_site_option( 'sudbury_required_events_post_types' ) );

	if ( 'meeting' == get_post_type( $post_id ) ) {
		$stamp = 'Meeting';
	} else {
		$stamp = 'Event';
	}


	?>
	<div class="sudbury-metabox">

		<label for="is_event">
			<input id="_is_event" name="_is_event" class="check-toggle" <?php echo( $event_required ? 'style="display:none;"' : '' ); ?> type="checkbox" <?php checked( $has_event || $event_required, true ); ?> data-toggle="#event_details"> Publish an Event
		</label>

		<div id="event_details" class="<?php if ( ! $has_event && ! $event_required ) : ?>hide-if-js<?php endif; ?>">
			<?php do_action( 'sudbury_before_linked_event_meta_box', $post_id ); ?>

			<hr>

			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row">Event Details</th>
					<td>
						<fieldset class="event_post_event_datetime">
							<legend class="screen-reader-text"><span>Date and Time</span></legend>
							<div class="sudbury_post_event_dates">
								<?php sudbury_datetime_editor( $stamp . ' Start ', $event_start_datetime, '_sudbury_event_start_datetime' ); ?>
								<?php sudbury_datetime_editor( $stamp . ' End ', $event_end_datetime, '_sudbury_event_end_datetime' ); ?>
							</div>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="_sudbury_location_id">Location</label></th>
					<td>
						<?php
						// This was old code
						//$locations = $wpdb->get_results("SELECT `location_id`, `location_address`, `location_name` FROM `{$wpdb->base_prefix}em_locations` LIMIT 500", ARRAY_A);
						//$locations = $wpdb->get_results("SELECT `location_id`, `location_address`, `location_name` FROM `{$wpdb->base_prefix}em_locations` LIMIT 500", ARRAY_A);

						// Args for the Locations Query
						$args = array(
							'posts_per_page' => - 1,
							'orderby'        => 'title',
							'order'          => 'ASC',
							'post_type'      => 'location',
						);

						// If this is a meeting post then only show Locations that are categorized as meeting locations
						if ( 'meeting' == get_post_type() ) {
							$args['location_categories'] = 'meeting-location';
						}

						// Get the Locations from the Post Indexer Index (wp_network_posts)
						$locations = network_query_posts( $args );

						// Refine the List of Locations that were returned
						foreach ( $locations as $index => $location ) {
							// Get the Location ID for wp_em_locations from the Location Post
							if ( $id = network_get_post_meta( $location, '_location_id', true ) ) {
								// The Location Post's ID
								$locations[ $index ]->location_post_id = $location->ID;
								// The Blog that the Location is defined on... Should always be blog 1 but we are thinking ahead
								$locations[ $index ]->blog_id = $location->BLOG_ID;
								// The ID of the Location in wp_em_locations
								$locations[ $index ]->location_id = $id;
								// The Location's Name
								$locations[ $index ]->location_name = $location->post_title;
								// The Location's Address
								$locations[ $index ]->location_address = get_post_meta( $location, '_location_address', true );
							}
						}

						if ( empty( $locations ) ) {
							// If there were no locations found show this error message
							echo 'No Locations were found';
						} else {
							// Otherwise show a dropdown (<select>) with all the Locations.  The <select> uses the Location ID (wp_em_locations)
							// As it's Value for each of it's <options>
							?>

							<?php
							// Location names can be optional
							if ( ! $event_required ) : ?>
								(<i>optional</i>)
							<?php endif; ?>
							<select name="_sudbury_location_id" id="_sudbury_location_id">
								<option value="tbd">Select a Location</option>
								<?php foreach ( $locations as $location ) : ?>
									<?php if ( isset( $location->location_id ) ) : ?>
										<option value="<?php echo esc_attr( $location->location_id ); ?>" data-name="<?php echo esc_attr( $location->location_name ); ?>" data-address="<?php echo esc_attr( $location->location_address ); ?>" <?php selected( $location->location_id == $location_id ); ?>> <?php echo esc_html( $location->location_name ); ?> </option>
									<?php else: ?>
										<!-- Invalid Location <?php var_dump( $location ); ?> -->
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						<?php } ?>
					</td>
				</tr>
				<?php do_action( 'sudbury_after_linked_event_meta_box', $post_id ); ?>
				</tbody>
			</table>


			<?php
			// Add this field if there is already an event.
			if ( $event_id ) {
				?>
				<input type="hidden" name="_had_event" value="<?php echo esc_attr( $event_id ); ?>" />
			<?php } ?>
		</div>
	</div>
	<?php
}

/**
 * Adds the attach an event metabox to the Admin Post Editor for specific post types
 */
function sudbury_create_post_event_metabox() {
	$post_types = get_site_option( 'sudbury_linked_events_post_types', array() );
	foreach ( $post_types as $post_type ) {
		add_meta_box( 'sudbury-post-events', 'Linked Event', 'sudbury_attach_event_metabox', $post_type );
	}
}

add_action( 'add_meta_boxes', 'sudbury_create_post_event_metabox' );
