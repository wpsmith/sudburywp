<?php
/**
 * The metabox for editing the contact information for department or committee
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Admin
 */

/**
 * @param $data
 */
function sudbury_dept_contact_info_metabox( $data ) {
	global $wpdb; ?>
	<div class="sudbury-metabox sudbury-contact-metabox">
		
		<label for="sudbury_telephone"> Telephone
			<input type="text" id="sudbury_telephone" name="sudbury_telephone" class="form-input-tip" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_telephone'] ); ?>">
		</label>

		<label for="sudbury_fax"> Fax
			<input type="text" id="sudbury_fax" name="sudbury_fax" class="form-input-tip" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_fax'] ); ?>">
		</label>

		<label for="sudbury_email"> Email
			<input type="email" id="sudbury_email" name="sudbury_email" class="form-input-tip sudbury-email-input" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_email'] ); ?>">
		</label>

		<label for="sudbury_facebook_url"> Facebook Url
			<input type="text" id="sudbury_facebook_url" name="sudbury_facebook_url" class="form-input-tip sudbury-url-input" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_facebook_url'] ); ?>">
		</label>

		<label for="sudbury_twitter_url"> Twitter Url
			<input type="text" id="sudbury_twitter_url" name="sudbury_twitter_url" class="form-input-tip sudbury-url-input" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_twitter_url'] ); ?>">
		</label>

		<label for="sudbury_youtube_url"> Youtube Url
			<input type="text" id="sudbury_youtube_url" name="sudbury_youtube_url" class="form-input-tip sudbury-url-input" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_youtube_url'] ); ?>">
		</label>

		<label for="sudbury_google_plus_url"> Google Plus Url
			<input type="text" id="sudbury_google_plus_url" name="sudbury_google_plus_url" class="form-input-tip sudbury-url-input" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_google_plus_url'] ); ?>">
		</label>
	</div>
<?php

}
