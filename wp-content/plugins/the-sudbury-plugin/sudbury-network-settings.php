<?php
/**
 * The Settings Page for the Sudbury Plugin
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Sudbury_Plugin_Settings
 */

/**
 * Registers the Sudbury Plugin Settings Page in the Wordpress Network Admin
 */
function sudbury_register_network_settings_page() {
	if ( current_user_can( 'manage_network' ) ) {
		add_menu_page( 'Sudbury Plugin Settings', 'Sudbury Settings', 'manage_network', 'sudbury-network-settings-page', 'sudbury_network_settings_page', plugins_url( 'images/sudbury-settings.png', __FILE__ ), 12 );
	}
}

add_action( 'network_admin_menu', 'sudbury_register_network_settings_page' );

/**
 * Renders the Sudbury Plugin's Settings Page
 */
function sudbury_network_settings_page() {
	if ( ! current_user_can( 'manage_network' ) ) {
		wp_die( 'You don\'t have permission to access this page' );
	}

	$keys    = array( 'sudbury_recommended_tags', 'sudbury_safe_hosts', 'sudbury_allowedtypes' );
	$options = array();
	foreach ( $keys as $key ) {
		$options[ $key ] = get_site_option( $key );
	}
	?>

	<div class="wrap">
		<h2>Sudbury Plugins Settings</h2>

		<form class="dept-info-form" action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<?php wp_nonce_field( 'sudbury-plugin-settings-nonce' ); ?>
			<input type="hidden" name="action" value="sudbury_save_plugin_options" />

			<?php sudbury_show_error( 'WARNING: The Settings on this page may be confusing.  They are only for Developers and WP Network Administrators' );
			do_action( 'admin_notices' ); ?>

			<?php submit_button( 'Save All Settings' ); ?>

			<div id="settings-wrap">
				<?php do_action( 'sudbury_network_admin_settings_begin' ); ?>

				<div class="sudbury-setting-section">
					<label for="sudbury_recommended_tags"><b>Recommended Tags</b>:
						<br><i>A comma separated list of tags you would like to recommend to your users</i><br>
						<textarea id="sudbury_recommended_tags" name="sudbury_recommended_tags" rows="4" style="width:100%;"><?php echo esc_html( implode( ', ', $options['sudbury_recommended_tags'] ) ); ?></textarea>
					</label>
				</div>

				<div class="sudbury-setting-section">
					<label for="sudbury_allowedtypes"><b>Allowed Types</b>:
						<br><i>A comma separated list of site types</i><br>
						<textarea id="sudbury_allowedtypes" name="sudbury_allowedtypes" rows="4" style="width:100%;"><?php echo esc_html( implode( ', ', $options['sudbury_allowedtypes'] ) ); ?></textarea>
					</label>
				</div>

				<div class="sudbury-setting-section">
					<label for="sudbury_safe_hosts"><b>Safe Hosts</b>:
						<br><i>A comma separated list of hosts that don't require a warning when you click a link to them (all subdomains are marked as safe too)</i><br>
						<textarea id="sudbury_safe_hosts" name="sudbury_safe_hosts" rows="4" style="width:100%;"><?php echo esc_html( implode( ', ', $options['sudbury_safe_hosts'] ) ); ?></textarea>
					</label>
				</div>

				<div class="sudbury-setting-section"><?php

					$post_types       = get_post_types();
					$event_post_types = get_site_option( 'sudbury_linked_events_post_types' );

					?>
					<label for="sudbury_linked_events_post_types"><b>Allow Events to be linked to the following post types</b>: <?php echo 'Currently Active: ' . esc_html( implode( ', ', $event_post_types ) ) . '<br>'; ?>
						<i>use CTRL + click to select multiple</i><br>

						<select id="sudbury_linked_events_post_types" name="sudbury_linked_events_post_types[]" style="height: 150px;" multiple>
							<?php foreach ( $post_types as $post_type ) : ?>
								<option value="<?php echo esc_attr( $post_type ); ?>" <?php selected( in_array( $post_type, $event_post_types ) ); ?>><?php echo esc_html( $post_type ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</div>


				<div class="sudbury-setting-section"><?php
					$post_types_requiring_events = get_site_option( 'sudbury_required_events_post_types' )
					?>
					<label for="sudbury_required_events_post_types"><b>Linked Events are required for these post types</b>: <?php echo 'Currently Active: ' . esc_html( implode( ', ', $post_types_requiring_events ) ) . '<br>'; ?>
						<i>use CTRL + click to select multiple</i><br>

						<select id="sudbury_required_events_post_types" name="sudbury_required_events_post_types[]" multiple>
							<?php foreach ( $event_post_types as $post_type ) : ?>
								<option value="<?php echo esc_attr( $post_type ); ?>" <?php selected( in_array( $post_type, $post_types_requiring_events ) ); ?>><?php echo esc_html( $post_type ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</div>
				<?php
				$sudbury_categories = get_terms( 'category', array( 'hide_empty' => 0 ) );
				foreach ( $sudbury_categories as $index => $category ) {
					$sudbury_categories[ substr( get_category_parents( $category->term_id, false, ' -> ' ), 0, - 4 ) ] = $category;
					unset( $sudbury_categories[ $index ] );
				}
				ksort( $sudbury_categories );
				?>
					<div class="sudbury-settings-section">
						<?php
						$sudbury_demoted_categories = get_site_option( 'sudbury_demoted_categories', array() );
						?>
						<label for="sudbury_demoted_categories">
							<h3>Demoted Categories</h3>
							<p><i>use CTRL + click to select multiple</i></p>
							<select id="sudbury_demoted_categories" name="sudbury_demoted_categories[]" style="height: 150px" multiple>
								<?php foreach ( $sudbury_categories as $category ) : ?>
									<option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( in_array( $category->slug, $sudbury_demoted_categories ) ); ?>><?php echo esc_html( substr( get_category_parents( $category->term_id, false, ' -> ' ), 0, - 4 ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>
					<div class="sudbury-settings-section">
						<?php
						$sudbury_promoted_categories = get_site_option( 'sudbury_promoted_categories', array() );

						?>
						<label for="sudbury_promoted_categories">
							<h3>Promoted Categories</h3>
							<p><i>use CTRL + click to select multiple</i></p>
							<select id="sudbury_promoted_categories" name="sudbury_promoted_categories[]" style="height: 150px" multiple>
								<?php foreach ( $sudbury_categories as $category ) : ?>
									<option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( in_array( $category->slug, $sudbury_promoted_categories ) ); ?>><?php echo esc_html( substr( get_category_parents( $category->term_id, false, ' -> ' ), 0, - 4 ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>
					<div class="clear"></div>
				</div>


				<div class="sudbury-setting-section">
					<?php $add_to_popular = get_site_option( 'sudbury_popular_categories', array() ); ?>
					<label for="sudbury_popular_categories">
						<h3>Popular Categories</h3>
						<p><i>use CTRL + click to select multiple</i></p>
						<select id="sudbury_popular_categories" name="sudbury_popular_categories[]" style="height: 150px" multiple>
							<?php foreach ( $sudbury_categories as $term ) : ?>
								<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( in_array( $term->term_id, $add_to_popular ) ); ?>><?php echo esc_html( substr( get_category_parents( $term->term_id, false, ' -> ' ), 0, - 4 ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>

					<div class="clear"></div>
				</div>


				<div class="sudbury-setting-section">
					<h2>Restrict Category Access By Role</h2>
					<p>Select Categories that should be hidden to users for the specified roles.</p>
					<?php
					$sudbury_restricted_categories = get_site_option( 'sudbury_restricted_categories', array() );
					switch_to_blog( 2 );
					global $wp_roles;

					foreach ( $wp_roles->roles as $role_name => $role ) :
						if ( ! isset( $sudbury_restricted_categories[ $role_name ] ) ) {
							$sudbury_restricted_categories[ $role_name ] = array();
						}
						if ( ! isset( $role['capabilities']['edit_posts'] ) ) {
							continue;
						}
						?>

						<label for="sudbury_restricted_categories">
							<h3><?php echo esc_html( ucfirst( $role_name ) ); ?></h3>
							<p>Currently restricted: <?php echo esc_html( implode( ', ', $sudbury_restricted_categories[ $role_name ] ) ); ?></p>
							<p><i>use CTRL + click to select multiple</i><p>

							<select id="sudbury_restricted_categories" name="sudbury_restricted_categories[<?php echo esc_attr( $role_name ); ?>][]" style="height: 150px" multiple>
								<?php foreach ( $sudbury_categories as $category ) : ?>
									<option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( in_array( $category->slug, $sudbury_restricted_categories[ $role_name ] ) ); ?>><?php echo esc_html( substr( get_category_parents( $category->term_id, false, ' -> ' ), 0, - 4 ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php endforeach;
					restore_current_blog();
					?>
					<div class="clear"></div>
				</div>
			</div>
			<?php submit_button( 'Save All Settings', 'primary' ); ?>
		</form>
		<hr />
		<div class="sudbury-setting-section">
			<h2>Developer Cookie Pickup</h2>

			<p> You<?php if ( ! isset( $_COOKIE['sudbury_developer'] ) ) : ?> DO NOT<?php endif; ?> HAVE the Sudbury Developer Cookie</p>

			<a href="?set_developer_cookie=on" id="developer-cookie-pickup" type="button" class="button button-primary">Pickup Developer Cookie</a> or
			<a href="?set_developer_cookie=off" id="developer-cookie-drop" type="button" class="button button-primary">Drop Developer Cookie</a>

			<p class="developer-cookie-set-console"></p>
		</div>
		<div class="sudbury-setting-section">
			<h2>Debug Display</h2>

			<p> You<?php if ( ! isset( $_COOKIE['debug_display'] ) ) : ?> DO NOT<?php endif; ?> HAVE the Debug Display Cookie</p>

			<p>
				<a href="/?debug_display=on" id="debug-display-cookie-pickup" type="button" class="button button-primary">Pickup Debug Display Cookie</a> or
				<a href="/?debug_display=off" id="debug-display-cookie-drop" type="button" class="button button-primary">Drop Debug Display Cookie </a>
			</p>
		</div>

		<!--	<div class="sudbury-setting-section">-->
		<!--		<label for="sudbury_hashed_passwords"><b>Password Hash</b>:-->
		<!--			<br><i>Enter a list of phrases to hash</i><br>-->
		<!--			<textarea id="sudbury_hashed_passwords" name="sudbury_hashed_passwords" rows="4" style="width:100%;">--><?php //echo esc_html( sudbury_hash_passwords( $_REQUEST['sudbury_hashed_passwords'] ) ); ?><!--</textarea>-->
		<!--		</label>-->
		<!--		<script>-->
		<!--			jQuery(document).ready(function ($) {-->
		<!--				$('#sudbury_hashed_passwords').change(function () {-->
		<!--					var text = $('#sudbury_hashed_passwords').val();-->
		<!---->
		<!--					text = text.replace(/ /g, '');-->
		<!--					var lines = text.split()-->
		<!--				})-->
		<!--			})-->
		<!--		</script>-->
		<!--	</div>-->

		<!--	-->
		<!--	<div class="sudbury-setting-section">-->
		<!--		<h2>Fix Run End Times</h2>-->
		<!---->
		<!--		<form action="--><?php //echo esc_attr( admin_url( 'admin-post.php' ) ); ?><!--" method="post">-->
		<!--			--><?php //wp_nonce_field( 'sudbury_fix_run_end_times' ); ?>
		<!--			<input type="hidden" name="action" value="sudbury_fix_run_end" />-->
		<!--			<input type="submit" class="button-primary" value="Fix Run End Times" />-->
		<!--		</form>-->
		<!---->
		<!--		<div class="clear"></div>-->
		<!--	</div>-->

		<div class="sudbury-setting-section">
			<h2>Filemaker Sync</h2>
			<form action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="fm_sync" />
				<input type="submit" class="button-primary" value="Manual Filemaker Sync" />
			</form>
			<div class="clear"></div>
		</div>

		<div class="sudbury-setting-section">
			<h2>FileSystem Permissions</h2>

			<form action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="permissions" />

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label for="passwd">Password</label></th>
						<td>
							<input id="passwd" class="regular-text" type="password" name="passwd" value="" />
							<p class="description" id="admin-email-desc">
								Unlock Password. See password database.
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="admin_email">Action</label></th>
						<td>
							<input type="submit" name="unlock" class="button-primary sudbury-button-danger" value="Filesystem Unlock" />
							<input type="submit" name="lock" class="button-primary" value="Filesystem Lock" />
							<p class="description" id="admin-email-desc">
								<strong>The filesystem will automatically re-lock itself after 5 minutes.</strong>
							</p>
						</td>
					</tr>
					</tbody>
				</table>
			</form>


			<div class="clear"></div>
		</div>

		<div class="sudbury-setting-section">
			<h2>Maintenance Mode</h2>

			<p>Shutdown the site for a specified amount of time. <b>This locks out everyone, including YOU!</b><br>
				<i>You will be brought to a page where you can disable maintenance mode early,
					<b>do not leave this page!</b></i>
			</p>


			<p>
				<b>Maintenance Mode</b> Disables the entire site and displays a maintenance page. No interaction will happen with the database, plugins, or theme during this time<br>
				<b>Lockout</b> Disables any admin actions from being performed, i.e. Publishing Posts or Logging In, but keeps the front end up. This is good for maintaining uptime<br>
			</p>

			<form action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<?php wp_nonce_field( 'sudbury-enable-maintenance-mode', 'sudbury-enable-maintenance-mode-nonce' ); ?>
				<?php wp_nonce_field( 'sudbury-enable-lockout', 'sudbury-enable-lockout-nonce' ); ?>
				<p><b>1. Duration</b></p>
				<input type="hidden" name="action" value="sudbury_takedown" />
				<input type="radio" name="timestamp" value="60">1 minute<br>
				<input type="radio" name="timestamp" value="300">5 minutes<br>
				<input type="radio" name="timestamp" value="600">10 minutes<br>
				<input type="radio" name="timestamp" value="1800">30 minutes<br>
				<input type="radio" name="timestamp" value="time()">Indefinitely<br>

				<p><b>2. Confirm</b></p>

				<p>

					<label>
						<input type="checkbox" name="sudbury_takedown_confirm">
						<b>STOP!</b> This WILL incur some downtime on some or all components of your site! Check this box indicate that you understand.
					</label>
				</p>

				<p><b>3. Take Down</b></p>

				<p>
					<input type="submit" name="sudbury_enable_maintenance_mode" class="button-primary sudbury-button-danger" value="Enable Maintenance mode" />
					<input type="submit" name="sudbury_enable_lockout" class="button-primary sudbury-button-warning" value="Lockout Users" />
				</p>
			</form>

			<div class="clear"></div>
		</div>


	</div>


	<?php
}

/**
 * Saves the options for the Sudbury Plugin
 */
function sudbury_save_plugin_options() {
	if ( ! current_user_can( 'manage_network' ) ) {
		wp_die( 'You don\'t have permission to perform this action' );
	}

	check_admin_referer( 'sudbury-plugin-settings-nonce' );

	$keys = array( 'sudbury_recommended_tags', 'sudbury_allowedtypes' );

	/* Auto Add Keys from $_POST starting with sudbury_ */

	foreach ( $_POST as $key => $value ) {
		if ( strstartswith( 'sudbury_', $key ) ) {
			$keys[] = $key;
		}
	}

	// Note the difference between options and option in filters:  sudbury_save_plugin_options_keys and 'sudbury_save_plugin_option_' . $key
	$keys = apply_filters( 'sudbury_save_plugin_options_keys', $keys );

	$settings_updated = array();

	foreach ( $keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$val = $_POST[ $key ];
			switch ( $key ) {
				case 'sudbury_recommended_tags':
					$val = explode( ',', $val );
					foreach ( $val as $index => $tag ) {
						$val[ $index ] = trim( $tag );
					}
					break;
				case 'sudbury_safe_hosts':
					$val = explode( ',', $val );
					foreach ( $val as $index => $site ) {
						$val[ $index ] = trim( $site );
					}
					break;
				case 'sudbury_allowedtypes':
					$val = explode( ',', $val );
					foreach ( $val as $index => $tag ) {
						$val[ $index ] = trim( $tag );
					}
					break;
				// Add more as needed
			}

			// Note the difference between options and option in filters:  sudbury_save_plugin_options_keys and 'sudbury_save_plugin_option_' . $key
			_sudbury_log( 'Saving ' . $key . ' as ' . print_r( $val, true ) );
			$updated = update_site_option( $key, apply_filters( 'sudbury_save_plugin_option_' . $key, $val ) );
			if ( $updated ) {
				$settings_updated[] = $key;
			}
		}
	}
	sudbury_redirect_updated( 'Saved Settings.  The Following were changed: ' . implode( ', ', $settings_updated ), false, true );
}

add_action( 'admin_post_sudbury_save_plugin_options', 'sudbury_save_plugin_options' );

/**
 * Useful function to remove junk
 * Update: the worst function ever that deletes content that you didn't think it would
 *
 * @deprecated in a fit of complete frustration with myself... I am removing it from the system
 */
function sudbury_cleanse_hello_world() {
	return;
}

add_action( 'admin_post_sudbury_cleanse_hello_world', 'sudbury_cleanse_hello_world' );


/**
 * Post Exirator has some interesting formats to their expiration date that seemed to conflict with the importer, use this to fix them after an import
 */
function sudbury_fix_run_end() {
	if ( ! current_user_can( 'manage_network' ) ) {
		wp_die( 'You don\'t have permission to perform this action' );
	}

	check_admin_referer( 'sudbury_fix_run_end_times' );


	$blogs = get_blogs( array( 'all' => true ) );

	foreach ( $blogs as $blog ) {
		switch_to_blog( $blog['id'] );

		$posts = get_posts();
		foreach ( $posts as $post ) {
			if ( $expire = get_post_meta( $post->ID, '_expiration-date', true ) ) {
				if ( is_object( $expire ) ) {
					update_post_meta( $post->ID, '_expiration-date-backup', $expire );
					update_post_meta( $post->ID, '_expiration-date', $expire->getTimestamp() );
					_sudbury_log( 'Updated Post ' . $post->ID . ' on site ' . $blog['title'] );
				}
			}
		}
		restore_current_blog();
	}
	sudbury_show_updated( 'Fixed all the run end times in the network' );
}

add_action( 'admin_post_sudbury_fix_run_end', 'sudbury_fix_run_end' );

/**
 * Enable Maintenance mode from the Network Admin
 */
function sudbury_enable_maintenance_mode() {
	// Gotta be the best of the best for this one
	if ( ! is_super_admin() || ! current_user_can( 'manage_network' ) ) {
		wp_die( 'Cheating Detected... Leave!' );
		die();
	}

	check_admin_referer( 'sudbury-enable-maintenance-mode', 'sudbury-enable-maintenance-mode-nonce' );

	if ( ! isset( $_REQUEST['timestamp'] ) ) {
		sudbury_redirect_error( 'You didn\'t specify a number of minutes you will be down for' );

		return;
	}
	$time = $_REQUEST['timestamp'];

	if ( ( ! is_numeric( $time ) || $time <= 0 ) && 'time()' !== $time ) {
		return;
	}

	if ( is_numeric( $time ) ) {
		$time = time() + $time - 600;
	}
	$stop_key             = wp_hash( random_string( rand( 50, 100 ) ) );
	$cancel_file_required = isset( $_REQUEST['no-cancel-file-ok'] );
	$cancel_file_exists   = file_exists( ABSPATH . '/wp-maint.php' );
	if ( $cancel_file_required && ! $cancel_file_exists ) {
		wp_die( 'The file <code>' . ABSPATH . '/wp-maint.php</code> does not exist.  This file is responsible for allowing you to end maintenance mode early with the click of a link.  Please install it or <a href="&no-cancel-file-ok">proceed without this functionality</a>' );
	} else if ( file_put_contents( ABSPATH . '/.maintenance', '<?php $upgrading = ' . $time . '; $stop_key = \'' . $stop_key . '\' ?>' ) ) {

		$time_msg = 'Maintenance mode has been activated ';

		if ( is_numeric( $time ) ) {
			$time_msg .= 'Until ' . date( 'F j, Y g:i:s a', $time + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) + 600 );
		} else {
			$time_msg .= 'Indefinitely';
		}
		$footer = '<p>You can always end maintenance mode by deleting the <code>.maintenance</code> file in the website\'s root directory: <code>' . ABSPATH . '</code></p>';
		if ( $cancel_file_exists ) {
			$stop_link = '<a href="' . site_url( 'wp-maint.php?stop_key=' . urlencode( $stop_key ) ) . '"> End Maintenance Period </a>';

			$message = $time_msg . '. <p>Use this link to automatically end the maintenance period. ' . $stop_link . '</p>' . $footer;
		} else {
			$message = $time_msg . $footer;
		}
		wp_die( $message, 'Maintenance Mode Enabled!' );
	} else {
		wp_die( 'Failed to write <code>.maintenance</code> file in the website\'s root directory <code>' . ABSPATH . '</code>', 'Maintenance Mode Failed!' );
	}
}

add_action( 'admin_post_sudbury_enable_maintenance_mode', 'sudbury_enable_maintenance_mode' );


/**
 * Enable use lockout from admin
 */
function sudbury_enable_lockout() {
	// Gotta be the best of the best for this one
	if ( ! is_super_admin() || ! current_user_can( 'manage_network' ) ) {
		wp_die( 'Cheating Detected... Leave!' );
		die();
	}

	check_admin_referer( 'sudbury-enable-lockout', 'sudbury-enable-lockout-nonce' );

	if ( ! isset( $_REQUEST['timestamp'] ) ) {
		sudbury_redirect_error( 'You didn\'t specify a number of minutes to loxckout for' );

		return;
	}
	$time = $_REQUEST['timestamp'];

	if ( ( ! is_numeric( $time ) || $time <= 0 ) && 'time()' !== $time ) {
		return;
	}

	if ( is_numeric( $time ) ) {
		$time = time() + $time - 600;
	}
	$stop_key             = wp_hash( random_string( rand( 50, 100 ) ) );
	$cancel_file_required = isset( $_REQUEST['no-cancel-file-ok'] );
	$cancel_file_exists   = file_exists( ABSPATH . '/wp-maint.php' );
	if ( $cancel_file_required && ! $cancel_file_exists ) {
		wp_die( 'The file <code>' . ABSPATH . '/wp-maint.php</code> does not exist.  This file is responsible for allowing you to end the lockout early with the click of a link.  Please install it or <a href="&no-cancel-file-ok">proceed without this functionality</a>' );
	} else if ( file_put_contents( ABSPATH . '/.lockout', '<?php $upgrading = ' . $time . '; $stop_key = \'' . $stop_key . '\' ?>' ) ) {

		$time_msg = 'Lockout is currently in effect ';

		if ( is_numeric( $time ) ) {
			$time_msg .= 'Until ' . date( 'F j, Y g:i:s a', $time + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) + 600 );
		} else {
			$time_msg .= 'Indefinitely';
		}
		$footer = '<p>You can always this lockout mode by deleting the <code>.lockout</code> file in the website\'s root directory: <code>' . ABSPATH . '</code></p>';
		if ( $cancel_file_exists ) {
			$stop_link = '<a href="' . site_url( 'wp-maint.php?stop_key=' . urlencode( $stop_key ) . '&type=lockout' ) . '"> End Lockout </a>';

			$message = $time_msg . '. <p>Use this link to automatically end the lockout. ' . $stop_link . '</p>' . $footer;
		} else {
			$message = $time_msg . $footer;
		}
		wp_die( $message, 'Lockout Enabled!' );
	} else {
		wp_die( 'Failed to write <code>.lockout</code> file in the website\'s root directory <code>' . ABSPATH . '</code>', 'Lockout Failed!' );
	}
}


function sudbury_takedown() {
	if ( ! isset( $_REQUEST['sudbury_takedown_confirm'] ) || $_REQUEST['sudbury_takedown_confirm'] != 'on' ) {
		sudbury_redirect_error( 'You did not confirm that you understand the consequences of a takedown.' );

		return;
	}
	if ( isset( $_REQUEST['sudbury_enable_lockout'] ) ) {
		sudbury_enable_lockout();
	} elseif ( isset( $_REQUEST['sudbury_enable_maintenance_mode'] ) ) {
		sudbury_enable_maintenance_mode();
	}
}

add_action( 'admin_post_sudbury_takedown', 'sudbury_takedown' );


function sudbury_handle_lockout() {
	if ( sudbury_is_lockout() ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json_error( 'Admin System Locked Out' );
		} else {
			$end = sudbury_lockout_end();
			if ( - 1 == $end ) {
				$end = "Please check back shortly.  Administrators are performing maintenance.";
			} else {
				$end = "Please check back in " . ceil( $end / 60 ) . " Minutes";
			}

			wp_die( 'Admin System Locked Out. ' . $end );
		}
		die();
	}
}

add_action( 'authenticate', 'sudbury_handle_lockout' );
add_action( 'admin_init', 'sudbury_handle_lockout' );

/**
 * Deterimines if User Logins are locked out
 * @return bool
 */
function sudbury_is_lockout() {
	if ( file_exists( ABSPATH . '/.lockout' ) ) {
		include ABSPATH . '/.lockout';
		if ( isset( $upgrading ) && $upgrading + 600 > time() ) {
			return true;
		} else {
			unlink( ABSPATH . '/.lockout' );
		}
	}

	return false;
}

function sudbury_lockout_end() {
	if ( file_exists( ABSPATH . '/.lockout' ) ) {
		include ABSPATH . '/.lockout';
		if ( isset( $upgrading ) ) {
			if ( $upgrading == time() ) {
				return - 1;
			} else {
				return $upgrading + 600 - time();
			}

		} else {
			return false;
		}
	}

	return false;
}


function sudbury_lockout_login_message( $message ) {
	if ( sudbury_is_lockout() ) {
		return $message . '<p class="error">System is Locked Out!</p">';
	} else {
		return $message;
	}
}

add_filter( 'login_message', 'sudbury_lockout_login_message' );
