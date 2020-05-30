<?php
/**
 * The metabox for editing some extra settings for a department or committee
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Admin
 */

/**
 * @param $data
 */
function sudbury_more_dept_settings_metabox( $data ) {
	global $wpdb;
	?>
	<div class="sudbury-metabox sudbury-more-dept-settings-metabox">
		<div class="sudbury-settings-container">
			<section id="sudbury_tweaks">

				<label for="sudbury_redirect_url">
					<b>Remote Department</b>
					<input type="text" id="sudbury_redirect_url" name="sudbury_redirect_url" class="form-input-tip" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_redirect_url'] ); ?>" style="width:80%">
				</label>

				<label for="sudbury_default_event_start_time">
					<b>Default Start Time of Events (00:00:00 in 24 hour format)</b>
					<input type="text" id="sudbury_default_event_start_time" name="sudbury_default_event_start_time" class="form-input-tip" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_default_event_start_time'] ); ?>">
				</label>

				<label for="sudbury_default_event_duration"> <b>Default Duration of Events (number of minutes)</b>
					<input type="text" id="sudbury_default_event_duration" name="sudbury_default_event_duration" class="form-input-tip" autocomplete="on" value="<?php echo esc_attr( ( $data['sudbury_default_event_duration'] / 60 ) ); ?>">
				</label>

				<label for="sudbury_default_event_days_between"> <b>Default Time between Events (days)</b>
					<input type="text" id="sudbury_default_event_days_between" name="sudbury_default_event_days_between" class="form-input-tip" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_default_event_days_between'] ); ?>">
				</label>

				<label for="sudbury_default_event_location"> <b>Default Event Location</b><br>
					<?php
					// I Cannot $wpdb->prepare this because there are no variables and thus no chance of SQL injection
					$locations = $wpdb->get_results( "SELECT * FROM `{$wpdb->base_prefix}em_locations` ORDER BY location_name LIMIT 500", ARRAY_A );
					if ( empty( $locations ) ) {
						echo 'No Locations were found';
					} else {

						?>
						<select name="sudbury_default_event_location" id="sudbury_default_event_location">
							<option>Select a Default Event Location</option>
							<?php foreach ( $locations as $location ) : ?>
								<option value="<?php echo esc_attr( $location['location_id'] ); ?>" <?php selected( $location['location_id'], $data['sudbury_default_event_location'] ); ?>> <?php echo esc_html( $location['location_name'] ); ?> </option>
							<?php endforeach; ?>
						</select>
					<?php } ?>
				</label>

				<label for="sudbury_board_membership_key"> <b>Board Membership Key</b>
					<input type="text" id="sudbury_board_membership_key" name="sudbury_board_membership_key" class="form-input-tip" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_board_membership_key'] ); ?>" style="width:90%">
				</label>
			</section>
			
		</div><!-- /.sudbury-settings-container -->
	</div><!-- /.sudbury-metabox -->
<?php
}