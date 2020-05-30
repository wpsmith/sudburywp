<?php

/**
 * Interfaces with the alerts API to send an email when a new alert is detected
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Alerts
 */
class Sudbury_Alerts_Emailer {
	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * Alert Emailer Init
	 */
	function init() {
		add_action( 'network_admin_menu', array( &$this, 'network_admin_menu' ) );
		add_action( 'admin_post_sudbury_alerts_emailer_save', array( &$this, 'save' ) );
		add_action( 'alert_up', array( &$this, 'alert_up' ) );
	}

	/**
	 * Sends an Alert Email to subscribed emails. By Default it will email emergency@sudbury.ma.us which is a distribution group on svrexch10 than goes to the Police, Fire, and Other Town officials
	 *
	 * CAUTION: This Function Alerts every Police Officer, Fireman, and Emergency Responder! DO NOT MESS WITH IT! The same warning goes for every line of code in the Alerts subpackage
	 *
	 * @param array|object|string $alert The Alert Being sent... As of this point it can be an alert array (see header.php) or a Network_Post from Post Indexer
	 */
	function alert_up( $alert ) {
		_sudbury_log( '[ALERTS] Alert Up Notification Received from The Sudbury Alerts System' );

		// DEVELOPERS: I Really want to abstract the short and long stuff but this is working and very flexible right now
		$to_short             = get_site_option( 'sudbury_alert_emails_to_short' );
		$to_long              = get_site_option( 'sudbury_alert_emails_to_long' );
		$subject_short        = get_site_option( 'sudbury_alert_emails_subject_short', 'Sudbury Alerts System' );
		$subject_long         = get_site_option( 'sudbury_alert_emails_subject_long', 'Sudbury MA Alerts System' );
		$message_header_short = sprintf( get_site_option( 'sudbury_alert_emails_header_short', '' ), date( 'D, M dS \a\t h:i:s A', current_time( 'timestamp' ) ) );
		$message_header_long  = sprintf( get_site_option( 'sudbury_alert_emails_header_long', "The Sudbury Alerts System received an alert activation with the following message on %s:<br /><br />>" ), date( 'D, M dS \a\t h:i:s A', current_time( 'timestamp' ) ) );

		$message_short_len = 135 - strlen( $subject_short ) - strlen( $message_header_short );

		// It's a Network Post then
		if ( is_object( $alert ) && isset( $alert->BLOG_ID ) ) {
			switch_to_blog( $alert->BLOG_ID );
			$cats = wp_get_object_terms( $alert->ID, 'category' );

			$type = sudbury_parse_alert_type( $cats );

			$cat_slugs_to_email_for = get_site_option( 'sudbury_alert_email_categories', array(
				'network-alert-critical',
				'network-alert-red'
			) );
			restore_current_blog();

			if ( ! in_array( $type['alert-class'], $cat_slugs_to_email_for ) ) {
				return; // This Post isn't important enough to send out alerts to the Police, Fire, ect.
			}
			$message_long = $alert->post_title . ': ' . $alert->post_content;

			$message_short = substr( $alert->post_title, 0, $message_short_len );
		} elseif ( is_array( $alert ) ) {
			if ( is_array( $alert ) && ! $alert['title'] ) {
				foreach ( $alert as $single_alert ) {
					$this->alert_up( $single_alert );
				}
			}

			if ( ! isset( $alert['title'] ) ) {
				return;
			}
			$message_long = $alert['title'] . ' ' . $alert['url'];

			$message_short = substr( $alert['title'], 0, $message_short_len );
		} elseif ( is_string( $alert ) ) {
			$message_long  = $alert;
			$message_short = substr( $alert, 0, $message_short_len );
		} else {
			_sudbury_log( '[ALERTS] I don\'t know what kind of alert this is so I can\'t Send Emergency Alerts for it.  Must be a Network Post, Array with the title key, or a string' );
			_sudbury_log( $alert );

			return;
		}

		if ( $to_short ) {
			wp_mail( $to_short, $subject_short, $message_header_short . $message_short );
		}
		if ( $to_long ) {
			wp_mail( $to_long, $subject_long, $message_header_long . $message_long, 'Content-Type: text/html; charset="UTF-8"' );
		}

	}

	/**
	 * Registers the Alert Email Settings page in th network admin menu
	 */
	function network_admin_menu() {
		add_menu_page( 'Alert Email Settings', 'Alert Emails', 'manage_network', 'alert-email-settings', array(
			&$this,
			'form'
		), 'dashicons-email-alt' );
	}

	/**
	 * Renders the Settings page for the Emailer Settings
	 */
	function form() {
		$to_short             = get_site_option( 'sudbury_alert_emails_to_short', array() );
		$to_long              = get_site_option( 'sudbury_alert_emails_to_long', array() );
		$subject_short        = get_site_option( 'sudbury_alert_emails_subject_short' );
		$subject_long         = get_site_option( 'sudbury_alert_emails_subject_long' );
		$message_header_short = get_site_option( 'sudbury_alert_emails_header_short' );
		$message_header_long  = get_site_option( 'sudbury_alert_emails_header_long' );
		$categories_available = get_terms( 'category', array( 'hide_empty' => 0 ) );
		foreach ( $categories_available as $index => $category ) {
			if ( strstartswith( 'network-alert', $category->slug ) ) {
				$categories_available[ substr( get_category_parents( $category->term_id, false, ' -> ' ), 0, - 4 ) ] = $category;
			}
			unset( $categories_available[ $index ] );
		}
		ksort( $categories_available );

		$categories_selected = get_site_option( 'sudbury_alert_email_categories' )

		?>
		<style>
			input[type="text"], select, textarea {
				width: 70%;
			}
		</style>
		<h2> Alert Emailer Settings</h2>
		<hr>
		<?php do_action( 'admin_notices' ); ?>
		<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
			<?php wp_nonce_field( 'save_sudbury_alerts_emailer_settings', 'sudbury_alerts_emailer_nonce' ); ?>
			<?php wp_referer_field(); ?>
			<input type="hidden" name="action" value="sudbury_alerts_emailer_save">

			<h3 class="title">General</h3>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row">
						<label for="sudbury_alert_email_categories">Only Send with these Alert Categories</label>
					</th>
					<td>
						<i>use CTRL + click to select multiple</i><br>
						<select id="sudbury_alert_email_categories" name="sudbury_alert_email_categories[]" style="height: 150px" multiple>
							<?php foreach ( $categories_available as $category ) : ?>
								<option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( in_array( $category->slug, $categories_selected ) ); ?>><?php echo esc_html( substr( get_category_parents( $category->term_id, false, ' -> ' ), 0, - 4 ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				</tbody>
			</table>
			<h3>Short Message Settings</h3>

			<p>Short Messages are limited to 140 characters (1 Text Message). The Subject and header count towards that 140 character so make them short or empty because the message title will be truncated more if your subject or header is long</p>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row"><label for="sudbury_alert_emails_to_short">Short Version Recipients</label><br>
						<i>(1 email per line)</i>
					</th>
					<td>
						<textarea name="sudbury_alert_emails_to_short" id="sudbury_alert_emails_to_short" cols="45" rows="8"><?php echo esc_textarea( str_replace( ',', "\n", $to_short ) ); ?></textarea><br>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sudbury_alert_emails_subject_short">Short Message Subject</label></th>
					<td>
						<input type="text" name="sudbury_alert_emails_subject_short" id="sudbury_alert_emails_subject_short" value="<?php echo esc_attr( $subject_short ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sudbury_alert_emails_header_short">Short Message Header</label></th>
					<td>
						<input type="text" name="sudbury_alert_emails_header_short" id="sudbury_alert_emails_header_short" value="<?php echo esc_attr( $message_header_short ); ?>" />
					</td>
				</tr>
				</tbody>
			</table>

			<h3>Long Message Settings</h3>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row"><label for="sudbury_alert_emails_to_long">Long Version Recipients</label><br>
						<i>(1 email per line)</i>
					</th>
					<td>
						<textarea name="sudbury_alert_emails_to_long" id="sudbury_alert_emails_to_long" cols="45" rows="8"><?php echo esc_textarea( str_replace( ',', "\n", $to_long ) ); ?></textarea><br>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sudbury_alert_emails_subject_long">Long Message Subject</label></th>
					<td>
						<input type="text" name="sudbury_alert_emails_subject_long" id="sudbury_alert_emails_subject_long" value="<?php echo esc_attr( $subject_long ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sudbury_alert_emails_header_long">Long Message Header</label></th>
					<td>
						<textarea name="sudbury_alert_emails_header_long" id="sudbury_alert_emails_header_long" rows="4" cols="45"><?php echo esc_textarea( $message_header_long ); ?></textarea>
					</td>
				</tr>
				</tbody>
			</table>


			<?php submit_button( "Save Changes" ); ?>
		</form>
	<?php
	}

	/**
	 * Saves the ALert Emailer Settings
	 */
	function save() {
		check_admin_referer( 'save_sudbury_alerts_emailer_settings', 'sudbury_alerts_emailer_nonce' );

		if ( is_multisite() ) {
			if ( ! current_user_can( 'manage_network' ) ) {
				sudbury_redirect_error( 'Access Denied you need to have the <b>manage_network</b> Capability to update options on a multisite network' );
			}
		}

		if ( isset( $_POST['sudbury_alert_email_categories'] ) && is_array( $_POST['sudbury_alert_email_categories'] ) ) {
			$cats = array_map( 'sanitize_key', $_POST['sudbury_alert_email_categories'] );
			update_site_option( 'sudbury_alert_email_categories', $cats );
		}

		if ( isset( $_POST['sudbury_alert_emails_to_short'] ) ) {
			$to_short = implode( ',', array_map( 'trim', explode( "\n", sudbury_validation_textarea( $_POST['sudbury_alert_emails_to_short'] ) ) ) );
			update_site_option( 'sudbury_alert_emails_to_short', $to_short );
		}
		
		if ( isset( $_POST['sudbury_alert_emails_subject_short'] ) ) {
			$subject_short = sanitize_text_field( $_POST['sudbury_alert_emails_subject_short'] );
			update_site_option( 'sudbury_alert_emails_subject_short', $subject_short );
		}

		if ( isset( $_POST['sudbury_alert_emails_header_short'] ) ) {
			$message_header_short = sanitize_text_field( $_POST['sudbury_alert_emails_header_short'] );
			update_site_option( 'sudbury_alert_emails_header_short', $message_header_short );
		}

		if ( isset( $_POST['sudbury_alert_emails_to_long'] ) ) {
			// Explode the textarea into an array (each line is an email), run each email through the trim function, then implode them all down on commas
			$to_long = implode( ',', array_map( 'trim', explode( "\n", sudbury_validation_textarea( $_POST['sudbury_alert_emails_to_long'] ) ) ) );
			update_site_option( 'sudbury_alert_emails_to_long', $to_long );
		}

		if ( isset( $_POST['sudbury_alert_emails_subject_long'] ) ) {
			$subject_long = sanitize_text_field( $_POST['sudbury_alert_emails_subject_long'] );

			update_site_option( 'sudbury_alert_emails_subject_long', $subject_long );
		}

		if ( isset( $_POST['sudbury_alert_emails_header_long'] ) ) {
			$message_header_long = sudbury_validation_textarea( $_POST['sudbury_alert_emails_header_long'] );
			update_site_option( 'sudbury_alert_emails_header_long', $message_header_long );
		}

		sudbury_redirect_updated( 'Settings Saved' );
	}
}

new Sudbury_Alerts_Emailer();