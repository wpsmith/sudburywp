<?php
/**
 * Handles features that might not have a wordpress implementation.  Displays interfaces of other
 * applications within a frame of the wordpress interface and handles authentication for
 * the external application via wordpress auth
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Extra_Features
 */

function sudbury_add_extra_feature_menus() {
	if ( is_super_admin() ) {
		global $sudbury_;
		add_menu_page( "FileMaker Status", "FileMaker Status", 'manage_network', 'sudbury-fm-status', 'sudbury_fm_status_page', 'dashicons-hammer', 78 );
		if ( isset( $_REQUEST['page'] ) && 'sudbury-edit-feature-page' == $_REQUEST['page'] ) {
			add_screen_option( 'layout_columns', array( 'max' => 1 ) );
		}
	}
}

function sudbury_extra_features_source( $feature ) {
	$username     = wp_get_current_user()->user_login;
	$time_rounded = round( time() / 5 ) * 5;
	$ip           = $_SERVER['LOCAL_ADDR'];
	$md5          = md5( $username . LEGACY_SECRET_HASH_SALT . $time_rounded . $ip );

	return sprintf( $feature['source'], sudbury_get_blog_slug(), sudbury_get_the_site_name(), sudbury_get_site_type(), get_option( 'sudbury_board_membership_key', '' ), apply_filters( 'sudbury_resolve_to_webeditor_name', sudbury_get_blog_slug() ), apply_filters( 'sudbury_resolve_to_webeditor_long_name', sudbury_get_the_site_name() ), $username, $md5 );

}

function sudbury_fm_status_page() {
	$feature_slug = substr( $_REQUEST['page'], - ( strlen( $_REQUEST['page'] ) - strlen( 'sudbury_extra_feature_' ) ) );
	$options      = get_site_option( 'sudbury_extra_features' );
	$feature      = $options['features'][ $feature_slug ];

	if ( ! current_user_can( $feature['capability'] ) || ( @is_array( $feature['allowed-users'] ) && ! empty( $feature['allowed-users'] ) && ! in_array( get_current_user_id(), $feature['allowed-users'] ) ) ) {
		wp_die( "You Are Not Authorized" );
	}

	if ( ! isset( $feature['source'] ) ) {
		wp_die( "Source not Set for this Feature.  The source must be the url of the application that feature is representing" );
	}


	$source = sudbury_extra_features_source( $feature );
	if ( isset( $feature['new-window'] ) && true === $feature['new_window'] ) {
		?>
		<META http-equiv="refresh" content="1;URL=<?php echo esc_attr( $feature['source'] ); ?>" target="_blank">
	<?php
	}


	?>
	<div class="clear"></div>
	</div>
	</div>
	<iframe allowtransparency src="<?php echo esc_url( $source ); ?>" id="source-frame" style="position: absolute; top:0; bottom: 41px; left:160px; right:0; width:100%; height: 100%;" onerror="alert('error')">
		Frame could not be loaded... <?php the_sudbury_contact_admin_message(); ?>
	</iframe>
	<div id="iframe-error" style="text-align: center; display:none;">
		<h2 style="margin:0;">Problems Loading Frame? <a href="/kb/blocked-admin-frames/" target="_blank">Help</a> |
			<a href="<?php echo esc_url( $source ); ?>" target="_blank">Open In New Tab</a></h2>

		<p>Trying to Load
			<a href="<?php echo esc_url( $source ); ?>" target="_blank"><?php echo esc_html( esc_url( $source ) ); ?></a> but it seems like your browser is stopping you
		</p>
	</div>
	<style>
		#wpfooter {
			background: #FFFFFF;
			margin-right: 0;
			margin-left: 160px;
			padding-right: 20px;
			padding-left: 20px;
			-webkit-box-shadow: 0px -1px 1px rgba(50, 50, 50, 0.2);
			-moz-box-shadow: 0px -1px 1px rgba(50, 50, 50, 0.2);
			box-shadow: 0px -1px 1px rgba(50, 50, 50, 0.2);
			position: fixed;
			bottom: 0;
		}
	</style>

	<script>
		(function ($) {
			var iframeError = setTimeout(function () {
				$('#source-frame').remove();
				$("#iframe-error").show();
			}, 2000);
			$('#source-frame').on('load', (function (a, b, c, d) {
				clearTimeout(iframeError);
			}));

		})(jQuery);

	</script>
	<div style="display:none;">
	<div style="display:none;">

<?php
}

add_action( 'admin_menu', 'sudbury_add_extra_feature_menus' );

function sudbury_feature_icon_url( $name ) {
	//if it is a URL
	if ( strstartswith( 'http', $name ) || strstartswith( '/', $name ) ) {
		return $name;
	}

	if ( strstartswith( 'dashicon', $name ) ) {
		return $name;
	}

	return plugins_url( "/the-sudbury-plugin/images/feature-$name.png" );
}


function sudbury_extra_feature_admin() {
	$enabled_features = get_option( 'sudbury_enabled_features', array() );

	$options = get_site_option( 'sudbury_extra_features' );
	if ( false === $options ) {
		wp_die( "Site Option: sudbury_extra_features does not exist" );
	}
	$features = $options['features'];

	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div>
		<h2>Department Features for <?php bloginfo( 'name' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=sudbury-edit-feature' ) ); ?>" class="add-new-h2">Add New</a>
		</h2>
		<br>

		<div class="updated">
			<p>
				<b>Tip: </b> Check out this <?php sudbury_the_site_type(); ?>'s
				<a href="<?php echo admin_url( 'plugins.php' ); ?>">Plugins Page</a> to enable installed plugins. Want to add more functionality? Check out the
				<a href="<?php echo network_admin_url( 'plugins.php' ); ?>">Network Admin Plugins Manager</a> where you can manage ALL installed plugins and install more!
			</p>
		</div>
		<br>

		<table class="wp-list-table widefat fixed features" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column"></th>
				<th scope="col" id="title" class="manage-column column-title" style="">Name</th>
				<th scope="col" id="author" class="manage-column" style="">Enabled</th>
				<th scope="col" id="author" class="manage-column" style="">Capability Required</th>
			</tr>
			</thead>

			<tfoot>
			<tr>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column"></th>
				<th scope="col" id="title" class="manage-column column-title" style="">Name</th>
				<th scope="col" id="author" class="manage-column" style="">Enabled</th>
				<th scope="col" id="author" class="manage-column" style="">Capability Required</th>
			</tr>
			</tfoot>

			<tbody id="the-list">
			<?php foreach ( $features as $slug => $feature ) :
				$enabled         = sudbury_is_feature_enabled( $slug, $feature );
				$script_path     = esc_url( admin_url( 'admin-post.php', __FILE__ ) );
				$this_page       = urlencode( admin_url( 'admin.php?page=sudbury-extra-feature-admin' ) );
				$network_enabled = $feature['network-enabled'];
				?>
				<tr class="feature type-post status-publish format-standard hentry category-front-page-news  iedit author-self" data-feature="<?php echo esc_attr( $slug ); ?>" valign="top">
					<th scope="row" class="check-column">
						<label class="screen-reader-text" for="cb-select-3706"></label>
						<input id="cb-select-3706" type="checkbox" name="post[]" value="3706" <?php checked( $enabled || $network_enabled ); ?> disabled>
					</th>
					<td class="post-title page-title column-title">
						<strong><a class="row-title" href="<?php echo esc_url( admin_url( 'admin.php?page=sudbury-edit-feature&feature=' . urlencode( $slug ) ) ); ?>" title=""><?php echo esc_html( $feature['page-title'] ); ?></a></strong>

						<div class="row-actions">
							<span class="edit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=sudbury-edit-feature&feature=' . urlencode( $slug ) ) ); ?>" title="Edit this item">Edit</a> | </span>
							<span class="delete"><a class="submitdelete" title="Delete this Item" href="<?php echo wp_nonce_url( $script_path . '?action=sudbury_feature_edit&type=delete&feature=' . urlencode( $slug ) . '&redirect_url=' . $this_page, 'sudbury-feature-edit' ); ?>">Delete</a> | </span>
							<?php if ($feature['network-enabled']) { ?>
								<span class="enable-disable">
                                <a href="#" style="color:#8f8f8f;" title="Feature is Network Enabled" rel="permalink">Feature is Network Enabled</a>
                            </span>
							<?php
							} else {
							?>
							<span class="enable-disable">
		<a href="<?php echo wp_nonce_url( $script_path . '?action=sudbury_feature_edit&type=' . ( $enabled ? 'disable' : 'enable' ) . '&feature=' . urlencode( $slug ) . '&blog=' . get_current_blog_id() . '&redirect_url=' . $this_page, 'sudbury-feature-edit' ); ?>" title="Enable or Disable this feature for this department" rel="permalink">
			<?php echo( $enabled ? 'Disable' : 'Enable' ); ?>
		</a>
			</span>
						</div>
					</td>
					<?php } ?>
					<td class="author column-author">
						<?php
						if ( $network_enabled ) {
							echo 'Feature is enabled Network Wide uncheck setting on <a href="' . esc_url( admin_url( 'admin.php?page=sudbury-edit-feature&feature=' . urlencode( $slug ) ) ) . '">edit page</a> to disable';
						} else {
							if ( $enabled ) {
								echo 'Enabled for this site';
							} else {
								echo 'Disabled for this site';
							}
						} ?>
					</td>
					<td class="categories column-categories"><?php echo esc_html( $feature['capability'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

	</div>

<?php

}

function sudbury_edit_feature_page() {

	global $sudbury_edit_feature_page_hook;


	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );


	add_meta_box( 'sudbury-feature-metabox-1', 'Feature Settings', 'sudbury_feature_edit_metabox', $sudbury_edit_feature_page_hook, 'normal', 'core' );

	?>

	<script type="text/javascript" src="<?php echo admin_url( '/js/post.js' ); ?>"></script>

	<div class="wrap">
		<h2>Edit Feature
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=sudbury-edit-feature' ) ); ?>" class="add-new-h2">Add New</a>
		</h2>

		<form class="dept-info-form" action="admin-post.php?action=sudbury_feature_edit" method="post">
			<?php wp_nonce_field( 'sudbury-feature-edit' ); ?>
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			<input type="hidden" name="action" value="sudbury_feature_edit" />
			<input type="hidden" name="type" value="full_edit_or_add" />

			<?php submit_button( 'Save All Settings' ); ?>
			<div id="dashboard-widgets-wrap">
				<div id="dashboard-widgets" class="metabox-holder columns-1">
					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( $sudbury_edit_feature_page_hook, 'normal', null ); ?>
					</div>

				</div>
				<div class="clear"></div>

			</div>
			<?php submit_button( 'Save All Settings', 'primary' ); ?>
		</form>
	</div>
	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready(function ($) {
			// open postboxes that should be closed
			$('.if-js-closed').removeClass('if-js-closed').addClass('open');
			// postboxes setup
			stop_navigation = false;
			$('input').change(function () {
				if (!stop_navigation) {
					console.log("Will attempt to halt navigation away from this page");
					stop_navigation = true;
				}

			})
			$('input[type="submit"]').click(function () {
				stop_navigation = false;
			})
			window.onbeforeunload = function () {
				if (stop_navigation)
					return "Are you sure you want to navigate away?";
			}

			console.log("Loaded Navigation Halt Script");
		});
		//]]>
	</script>

<?php

}


function sudbury_feature_edit_layout_columns( $columns, $screen ) {
	global $sudbury_edit_feature_page_hook;
	if ( $screen == $sudbury_edit_feature_page_hook ) {
		$columns[ $sudbury_edit_feature_page_hook ] = 1;
	}

	return $columns;
}

add_filter( 'screen_layout_columns', 'sudbury_feature_edit_layout_columns', 999, 2 );
function sudbury_feature_edit_metabox( $data ) {
	$slug = '';
	$mode = 'add';

	if ( ! isset( $_REQUEST['feature'] ) ) {
		// default feature
		$feature = array(
			'page-title'      => '',
			'menu-title'      => '',
			'menu-position'   => 41,
			'capability'      => 'edit_posts',
			'icon'            => '/wp-content/plugins/the-sudbury-plugin/images/feature-test.png',
			'allowed-users'   => true,
			'source'          => '',
			'network-enabled' => false,

		);
	} else {
		$slug = $_REQUEST['feature'];

		$options = get_site_option( 'sudbury_extra_features' );

		if ( false === $options ) {
			wp_die( "Site Option: sudbury_extra_features does not exist" );
		}
		$mode = 'edit';
		if ( ! isset( $options['features'][ $slug ] ) ) {
			wp_die( "Feature $slug does not exist" );
		}
		$feature = $options['features'][ $slug ];
	}

	?>



	<div class="inside">
		<div class="form-wrap">
			<div class="form-field form-required ct-form-field">
				<input type="hidden" name="mode" value="<?php echo esc_attr( $mode ); ?>">
				<table class="form-table edit-dept-feature">

					<tbody>
					<tr>
						<th>
							<label for="feature_page-title">Name</label>
						</th>
						<td>
							<input type="text" class="ct-field ct-text" name="feature_page-title" id="feature_page-title" value="<?php echo esc_attr( $feature['page-title'] ); ?>"><br>
							<span class="description">The name of the feature.  This is used to identify the feature to you the user.</span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="feature_slug">Slug</label>
						</th>
						<td>
							<input type="text" class="ct-field ct-text" name="feature_slug" id="feature_slug" value="<?php echo esc_attr( $slug ); ?>"><br>
							<span class="description">A Unique name for the feature.  Used by wordpress to identify the feature.  Should ONLY contain Letters and Numbers</span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="feature_menu-title">Menu Title</label>
						</th>
						<td>
							<input type="text" class="ct-field ct-text" name="feature_menu-title" id="feature_menu-title" value="<?php echo esc_attr( $feature['menu-title'] ); ?>"><br>
							<span class="description">This is used in the menu item that will be registered to access this feature</span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="feature_menu-position">Menu Position</label>
						</th>
						<td>
							<input type="text" class="ct-field ct-text" name="feature_menu-position" id="feature_menu-position" value="<?php echo esc_attr( $feature['menu-position'] ); ?>"><br>
							<span class="description">Choose carefully, if you pick a number that is already in use it will overwrite the exisiting menu item<br>5 - below Posts; 10 - below Media; 20 - below Pages; 60 - below first separator; 100 - below second separator</span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="feature_source">Source URL</label>
						</th>
						<td>
							<input type="text" class="ct-field ct-text" name="feature_source" id="feature_source" value="<?php echo esc_attr( $feature['source'] ); ?>"><br>
							<span class="description">This is the fully qualified URL or Root-relative url of the application you want to access.  for example for paging it is <code>http://svrinweb/paging</code><br>
								<ul>
									<li>Use <code>%1$s</code> for the wp short name of the current blog</li>
									<li>Use <code>%2$s</code> for the wp long name of the current blog</li>
									<li>Use <code>%3$s</code> for the site type department or committees</li>
									<li>Use <code>%4$s</code> for the site's board membership key</li>
									<li>Use <code>%5$s</code> for the webeditor short name of the current blog</li>
									<li>Use <code>%6$s</code> for the webeditor long name of the current blog</li>
									<li>Use <code>%7$s</code> for the current user's username i.e. 'hurtige'</li>
									<li>Use <code>%8$s</code> for the MD5 HashCode
										<code>md5(USERNAME + LEGACY_SECRET_HASH_SALT + ROUNDED_5_SECONDS + WP_IP)</code> i.e. 'hurtige'
									</li>
								</ul>
							</span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="feature_icon">Icon URL</label>
						</th>
						<td>
							<input type="text" class="ct-field ct-text" name="feature_icon" id="feature_icon" value="<?php echo esc_attr( $feature['icon'] ); ?>"><br>
							<span class="description">This is the fully qualified URL or Root-relative url of the icon you want to use in the menu</span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="feature_capability">Capability</label>
						</th>
						<td>
							<input type="text" class="ct-field ct-text" name="feature_capability" id="feature_capability" value="<?php echo esc_attr( $feature['capability'] ); ?>"><br>
							<span class="description">This is the minimium access level that is required to access this feature.  See <a href="http://codex.wordpress.org/Roles_and_Capabilities">http://codex.wordpress.org/Roles_and_Capabilities</a> for more info.</span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="feature_network_enabled">Network Enable</label>
						</th>
						<td>
							<label for="feature_network_enabled">
								<input type="checkbox" class="" style="width:15px;" name="feature_network-enabled" id="feature_network_enabled" <?php checked( $feature['network-enabled'] ); ?>>
								Force Enable this feature for all current and future Sites</label>
						</td>
					</tr>
					<tr>
						<th>
							<label for="feature_all_departments">Enabled on All Departments</label>
						</th>
						<td>
							<label for="feature_all_departments">
								<input type="checkbox" class="" style="width:15px;" name="feature_all-departments" id="feature_all_departments" <?php checked( $feature['all-departments'] ); ?>>
								Force Enable this feature for all departments</label>
						</td>
					</tr>
					<tr>
						<th>
							<label for="feature_all_committees">Enabled on All Committees</label>
						</th>
						<td>
							<label for="feature_all_committees">
								<input type="checkbox" class="" style="width:15px;" name="feature_all-committees" id="feature_all_committees" <?php checked( $feature['all-committees'] ); ?>>
								Force Enable this feature for all committees</label>
						</td>
					</tr>
					<tr>
						<th>
							<label for="feature_allowed_users">Allowed Users</label>
						</th>
						<td>
							<span class="description">Only Allow these Users to access this feature ... selecting none will allow all current and future users to access the feature </span><br />
							<?php
	$users = get_users( array( 'blog_id' => - 1 ) );


	?>
							<select multiple id="feature_allowed-users" name="feature_allowed-users[]" style="width:50%;height: 200px">
								<?php


	foreach ( $users as $user ) : ?>
		<option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( in_array( $user->ID, ( is_array( $feature['allowed-users'] ) ? $feature['allowed-users'] : array() ) ) ); ?> ><?php echo esc_html( $user->user_nicename ); ?></option>
	<?php endforeach; ?>

							</select>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

<?php

}

function sudbury_feature_edit() {
	if ( isset( $_REQUEST['type'] ) ) {
		if ( ! is_super_admin() ) {
			wp_die( "You are not a super admin so you can't edit this" );
		}

		check_admin_referer( 'sudbury-feature-edit' );

		if ( 'delete' == $_REQUEST['type'] ) {
			$feature_slug = $_REQUEST['feature'];
			if ( isset( $_REQUEST['confirmed'] ) ) {
				$features = get_site_option( 'sudbury_extra_features' );
				if ( isset( $features['features'][ $feature_slug ] ) ) {
					unset( $features['features'][ $feature_slug ] );
					update_site_option( 'sudbury_extra_features', $features );
					// blog options will update themselves later
					echo 'Done';
					sudbury_redirect_updated( "Deleted Feature", $_REQUEST['redirect_url'] );

				} else {
					wp_die( "Feature ($feature_slug) not found", "Not Found", 404 );
				}
			} else {
				wp_die( 'Do you really want to delete this feature (' . esc_html( $feature_slug ) . ') for <b>ALL</b> sites? <a onclick="history.go(-1);">Go Back</a> or <a class="delete" href="' . wp_nonce_url( admin_url( 'admin-post.php?action=sudbury_feature_edit&type=delete&confirmed&feature=' . $feature_slug . '&redirect_url=' . urlencode( $_REQUEST['redirect_url'] ) ), 'sudbury-feature-edit' ) . '">Yes Delete It</a>', 'Confirm Deletion', 200 );
			}
		} elseif ( 'enable' == $_REQUEST['type'] ) {
			$feature_slug = $_REQUEST['feature'];
			$blog         = $_REQUEST['blog'];

			switch_to_blog( $blog );

			$enabled_features = get_option( 'sudbury_enabled_features', array() );

			if ( ! in_array( $feature_slug, $enabled_features ) ) {
				$enabled_features[] = $feature_slug;
			}

			update_option( 'sudbury_enabled_features', $enabled_features );

			restore_current_blog();

			sudbury_redirect_updated( "Enabled Feature", $_REQUEST['redirect_url'] );
		} elseif ( 'disable' == $_REQUEST['type'] ) {
			$feature_slug = $_REQUEST['feature'];
			$blog         = $_REQUEST['blog'];

			switch_to_blog( $blog );

			$enabled_features = get_option( 'sudbury_enabled_features' );

			if ( false !== ( $indx = array_search( $feature_slug, $enabled_features ) ) ) {
				unset( $enabled_features[ $indx ] );
			}

			update_option( 'sudbury_enabled_features', $enabled_features );

			restore_current_blog();
			sudbury_redirect_updated( "Disabled Feature", $_REQUEST['redirect_url'] );

		} elseif ( 'full_edit_or_add' == $_REQUEST['type'] ) {
			$mode     = $_REQUEST['mode'];
			$defaults = array(
				'page-title'      => '',
				'menu-title'      => '',
				'menu-position'   => 41,
				'source'          => '',
				'icon'            => '',
				'capability'      => '',
				'network-enabled' => false,
				'allowed-users'   => true,
				'all-departments' => false,
				'all-committees'  => false,
			);

			$new_feature = array();
			if ( isset( $_POST['feature_page-title'] ) ) {
				$new_feature['page-title'] = $_POST['feature_page-title'];
			}

			if ( isset( $_POST['feature_menu-title'] ) ) {
				$new_feature['menu-title'] = $_POST['feature_menu-title'];
			}

			if ( isset( $_POST['feature_menu-position'] ) ) {
				$new_feature['menu-position'] = intval( $_POST['feature_menu-position'] );
			}

			if ( isset( $_POST['feature_source'] ) ) {
				$new_feature['source'] = $_POST['feature_source'];
			}

			if ( isset( $_POST['feature_icon'] ) ) {
				$new_feature['icon'] = $_POST['feature_icon'];
			}

			if ( isset( $_POST['feature_capability'] ) ) {
				$new_feature['capability'] = $_POST['feature_capability'];
			}

			if ( isset( $_POST['feature_network-enabled'] ) ) {
				$new_feature['network-enabled'] = ( 'on' == $_POST['feature_network-enabled'] );
			}

			if ( isset( $_POST['feature_allowed-users'] ) ) {
				$new_feature['allowed-users'] = $_POST['feature_allowed-users'];
			}

			if ( isset( $_POST['feature_all-departments'] ) ) {
				$new_feature['all-departments'] = $_POST['feature_all-departments'];
			}


			$new_feature['all-departments'] = isset( $_POST['feature_all-departments'] );
			$new_feature['all-committees']  = isset( $_POST['feature_all-committees'] );


			$new_feature = array_merge( $defaults, $new_feature );

			if ( isset( $_POST['feature_slug'] ) && '' != $_POST['feature_slug'] ) {
				$slug = sanitize_key( $_POST['feature_slug'] );
			} else {
				if ( '' != $new_feature['page-title'] ) {
					$slug = sanitize_key( $new_feature['page-title'] );
				} else {
					wp_die( 'No Page Title or Slug: Please click the back button and Provide a Page Title and a Slug' );
				}
			}

			$options = get_site_option( 'sudbury_extra_features' );

			if ( 'edit' == $mode ) {
				$options['features'][ $slug ] = $new_feature;
			} else {
				if ( ! isset( $options['features'][ $slug ] ) && 'add' == $mode ) {
					$options['features'][ $slug ] = $new_feature;
				} else {
					wp_die( 'A feature with slug ' . $slug . ' Already Exists.  Please click thee back button in your browser and edit the slug' );
				}
			}

			update_site_option( 'sudbury_extra_features', $options );

			sudbury_redirect_updated( ( $mode = 'add' ? 'Created ' : 'Updated ' ) . 'Feature "' . $slug . '"', add_query_arg( 'feature', $slug, $_REQUEST['_wp_http_referer'] ) );
		}
	}
}

add_action( 'admin_post_sudbury_feature_edit', 'sudbury_feature_edit' );


function sudbury_get_active_features( $args = array() ) {
	$defaults = array( 'source_req' => false );
	$args     = wp_parse_args( $args, $defaults );

	$options = get_site_option( 'sudbury_extra_features' );
	$return  = array();
	if ( $options['features'] ) {
		// doing a quick check to make sure our blog setting is up to date with the available features


		foreach ( $options['features'] as $slug => $feature ) {
			if ( sudbury_is_feature_enabled( $slug, $feature ) ) {
				if ( isset( $feature['allowed-users'] ) && is_array( $feature['allowed-users'] ) && ! empty( $feature['allowed-users'] ) && ! in_array( get_current_user_id(), $feature['allowed-users'] ) ) {
					continue;
				}

				if ( $args['source_req'] && empty( $feature['source'] ) ) {
					continue;
				}

				$return[ $slug ] = $feature;
			}
		}
	}

	return $return;
}

function sudbury_is_feature_enabled( $slug, $feature ) {
	$enabled_features = get_option( 'sudbury_enabled_features' );

	$enabled = ( isset( $feature['network-enabled'] ) && true === $feature['network-enabled'] ) || $enabled_features && in_array( $slug, $enabled_features );

	return apply_filters( 'sudbury_is_feature_enabled', $enabled, $slug, $feature );
}


/**
 * @param $enabled
 * @param $slug
 *
 * @return bool
 */
function sudbury_email_list_override( $enabled, $slug, $feature ) {

	if ( in_array( $slug, get_option( 'sudbury_force_disable_features', array() ) ) ) {
		return false;
	}


	if ( isset( $feature['all-departments'] ) && $feature['all-departments'] && is_department() ) {
		return true;
	}

	if ( isset( $feature['all-committees'] ) && $feature['all-committees'] && is_committee() ) {
		return true;
	}

	return $enabled;
}

add_filter( 'sudbury_is_feature_enabled', 'sudbury_email_list_override', 10, 3 );
