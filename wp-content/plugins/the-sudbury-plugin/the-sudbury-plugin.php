<?php
/*
Plugin Name: The Sudbury Plugin
Plugin URI: http://sudbury.ma.us/
Description: Makes changes to Wordpress designed for the Town of Sudbury such as changing the Login Logo, Footer Branding, Help Content and so much more
Version: 1.0
Author: Eddie Hurtig - See contact info in code comments
Author URI: http://hurtigtechnologies.com
Network: True
*/

/**
 * The Root Plugin File for The Subury Plugin
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Sudbury_Plugin
 */

/**
 * Some general notes about this plugin:
 *
 * This was developed by Eddie Hurtig, Sudbury's Web Developer from 2012 - 2015,
 *
 * This plugin is contains functionality that is supposed to be implemented NETWORK WIDE regardless of the site's theme.
 *
 * You will find all the branding, error handling, frameworks, and more in this plugin because they need to be accessible to every theme
 *
 * This plugin is included in the private bitbucket repository at https://bitbucket.org/sudbury/sudburywp (ensure you are logged in and a member of the sudbury team)
 *
 * If you need to contact me you can find my contact info below.  I will be at Northeastern University until 2018:
 *
 * Emails
 *  1: hurtige@sudbury.ma.us
 *  2: eddie@hurtigtechnologies.com - I run a web design business on the side so this should be checked no matter what
 *  3: eh1776@verizon.net - My antiquated but always present email address that should alway forward to whatever my current primary is
 *  4: hurtig.e@husky.neu.edu - This will route to me until 2018 hopefully
 *
 * Phone
 *  (978) 505 - 5610 - If it is an emergency please call me, leave a message if I don't pick up because I might be in class
 *
 */

// Autoloader Allows specific modules to be disabled for sites like the library

// Array of $filename => boolean load or not
$sudbury_load_files = get_option( 'sudbury_load', array() );

$sudbury_default_load = array(
	'jquery-ui/jquery-ui-theme.php',
	'sudbury-post-indexer-patches.php',
	'sudbury-alerts.php',
	'sudbury-framework.php',
	'sudbury-filemaker-sync.php',
	'sudbury-includes.php',
	'sudbury-validation.php',
	'sudbury-settings-api.php',
	'sudbury-broadcast-info.php',
	'sudbury-notices-api.php',
	'widgets/sudbury-widgets.php',
	'sudbury-dept-info-admin.php',
	'sudbury-extra.php',
	'sudbury-sticky-posts.php',
	'meta-boxes/sudbury-attach-event-metabox.php',
	'sudbury-events-extensions.php',
	'sudbury-paging/sudbury-paging.php',
	'sudbury-network-settings.php',
	'sudbury-links.php',
	'sudbury-locations-autocomplete.php',
	'sudbury-alerts-emailer.php',
	'sudbury-network-user-manager.php',
	'sudbury-cleanup.php',

	// depends on sudbury-common.php
	'sudbury-dept-head-message.php', // depends on sudbury-common.php
	'sudbury-extra-features.php',  // depends on sudbury-common.php
	'sudbury-documents.php', // depends on sudbury-common.php
	'sudbury-s3.php', // depends on sudbury-common.php
	'sudbury-add-site.php', // depends on sudbury-common.php
	'sudbury-news-articles.php', // depends on sudbury-common.php
	'sudbury-faqs.php',
	'sudbury-department-menu.php',
	'sudbury-meetings.php',
	'sudbury-meeting-removal.php',
	'sudbury-elections.php',
	'sudbury-upcoming-meetings.php',
	'sudbury-user-settings.php',
	'shortcodes/sudbury-shortcodes.php',
	'meta-boxes/class-sudbury_post_options.php',
	'class-sudbury_mover.php',
	'sudbury-nav-menu-metaboxes.php',
	'sudbury-legacy-menu-metabox.php',
	'location-template.php',
	'sudbury-api.php',
	'sudbury-migrate.php'
);

require_once WP_PLUGIN_DIR . '/sudbury-common/sudbury-common.php';

foreach ( $sudbury_default_load as $order => $file ) {
	if ( isset( $sudbury_load_files[ $file ] ) && ! $sudbury_load_files[ $file ] ) {
		continue;
	} else {
		include_once plugin_dir_path( __FILE__ ) . $file;
	}
}

/**
 * Runs before anything else.
 *
 * Tasks:
 *    - Disable the media rename plugin if the current user is not a super admin
 */
function sudbury_plugins_loaded() {
	// Added the media rename plugin but we don't want that functionality for anyone except super admins
	if ( ! is_super_admin() ) {
		remove_action( 'plugins_loaded', 'media_rename_init' );
	}
	// Events manager thinks that all users should be subscribers to the main blog.  I profoundly disagree
	remove_action( 'admin_init', 'em_admin_init' );
	remove_filter( 'media_row_actions', 'add_media_action' );
	remove_action( 'wp_footer', 'orbisius_whitelist_IP_for_limit_login_attempts_add_plugin_credits', 1000 );
	if ( ! is_super_admin() ) {
		global $sudbury_debug_print;
		$sudbury_debug_print = false;
	}

	if ( ! is_debug() ) {
		remove_action( 'admin_bar_init', array( &$GLOBALS['debug_bar'], 'init' ) );
	}
	remove_filter( 'load-plugins.php', 'wp_update_plugins' );
	remove_filter( 'admin_init', '_maybe_update_core' );
	remove_filter( 'admin_init', '_maybe_update_plugins' );
	remove_filter( 'admin_init', '_maybe_update_themes' );
	remove_action( 'init', 'wp_version_check' );
}

add_action( 'plugins_loaded', 'sudbury_plugins_loaded', 1 );

/**
 * The primary init function for the-sudbury-plugin.  It registers a custom post status, adds the custom editor style for all themes, and anything else
 *
 * Tasks
 *  - Registers the public-archive post status
 *  - Blocks Users from accessing sites that are not public with a wp_die()
 *    - Removes wpautop (See note in comment)
 */
function sudbury_init() {
	register_post_status( 'public-archive', array(
		'label'                     => 'Public Archives',
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Public Archive <span class="count">(%s)</span>', 'Public Archive <span class="count">(%s)</span>' ),
	) );

}

add_action( 'init', 'sudbury_init' );


function sudbury_tag_row_hide_delete( $actions ) {
	unset( $actions['delete'] );

	return $actions;
}

add_filter( "tag_row_actions", 'sudbury_tag_row_hide_delete', 10, 2 );

/**
 * Adds the "Public Archive" Text to the end of the title of a post to indicate that it is in the public archive in
 * the Admin Post List Table
 *
 * @param array   $states An Array of states to list for the $post
 * @param WP_Post $post   The Post to list states for
 *
 * @return array The New Array of States to Display
 */
function sudbury_display_post_states( $states, $post ) {
	if ( 'public-archive' == $post->post_status ) {
		$states[] = 'Public Archive';
	}

	if ( sudbury_is_guest_post( $post->ID ) ) {
		$states[] = 'Guest Post';
	}

	return $states;
}

add_filter( 'display_post_states', 'sudbury_display_post_states', 10, 2 );

/**
 * Default Admin Init Function
 */
function sudbury_admin_init() {
	sudbury_settings_init();

	if ( is_super_admin() ) {
		add_action( 'show_user_profile', 'sudbury_add_user_field' );
		add_action( 'edit_user_profile', 'sudbury_add_user_field' );
	}

	add_editor_style( '../../plugins/the-sudbury-plugin/editor-style.css' );
}

add_action( 'admin_init', 'sudbury_admin_init' );

/**
 * Saves user's query string when they visit a Post List Table so that what they click the cancel button it will bring them back to it
 *
 * @param $current_screen
 */
function sudbury_track_user_in_admin( $current_screen ) {
	if ( 'edit' == $current_screen->base ) {
		update_user_meta( get_current_user_id(), "list_table_prefs_{$current_screen->post_type}", $_SERVER['QUERY_STRING'] );
	}
}

add_action( 'current_screen', 'sudbury_track_user_in_admin' );

/**
 * Custom JS for Admin
 */
function sudbury_admin_js() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-slider' );
	wp_enqueue_script( 'jquery-ui-timepicker-addon' );


	wp_register_script( 'jquery-cookie', get_template_directory_uri() . '/js/jquery.cookie-1.4.1.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'jquery-cookie' );

	wp_enqueue_script( 'sudbury-admin-js', plugins_url( '/the-sudbury-plugin/sudbury-admin.js' ), array(), SUDBURY_VERSION );
}

add_action( 'admin_enqueue_scripts', 'sudbury_admin_js', 0, 10001 );

/**
 * Custom CSS for Admin
 */
function sudbury_admin_css() {
	wp_enqueue_style( 'jquery-ui' );
	wp_enqueue_style( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-slider' );
	wp_enqueue_style( 'jquery-ui-timepicker-addon' );


	wp_register_style( 'sudbury-admin-css', plugins_url( 'admin-style.css', __FILE__ ) );
	wp_enqueue_style( 'sudbury-admin-css' );
}

add_action( 'admin_enqueue_scripts', 'sudbury_admin_css' );


function sudbury_everywhere_css() {
	wp_register_style( 'sudbury-everywhere-css', plugins_url( 'everywhere.css', __FILE__ ) );
	wp_enqueue_style( 'sudbury-everywhere-css' );
}

add_action( 'wp_enqueue_scripts', 'sudbury_everywhere_css' );
add_action( 'admin_enqueue_scripts', 'sudbury_everywhere_css' );

function sudbury_enqueue_plugin_scripts() {
	wp_register_script( 'sudbury_plugin_js', plugins_url( 'sudbury-plugin.js', __FILE__ ), array( 'jquery' ), SUDBURY_VERSION );

	wp_enqueue_script( 'sudbury_plugin_js' );

	wp_localize_script( 'sudbury_plugin_js', 'wpApiSettings', array(
		'root'  => esc_url_raw( rest_url() ),
		'nonce' => wp_create_nonce( 'wp_rest' )
	) );

}

add_action( 'wp_enqueue_scripts', 'sudbury_enqueue_plugin_scripts' );

/**
 * Fixes IE 10 because it no longer supports the <!-- IF IE Tags and if there is text before the <html> tag it gets thrown into IE7 Standards
 * Sends header for Admin and Login pages only
 */
function sudbury_xua() {
	if ( ( is_admin() || is_login_page() ) && ! headers_sent() ) {
		header( 'X-UA-Compatible: IE=edge' );
	}
}

add_action( 'muplugins_loaded', 'sudbury_xua', 999 );
add_action( 'login_head', 'sudbury_xua', 999 );

/**
 * Adds/Removes capabilities for WordPress users
 */
function sudbury_caps() {
	// gets the author role
	$role = get_role( 'editor' );

	// This only works, because it accesses the class instance.
	// would allow the author to edit others' posts for current theme only
	$role->add_cap( 'edit_theme_options' );
}

add_action( 'admin_init', 'sudbury_caps' );

/**
 * Returns a continue reading link to the current post that should be appended to the end of an excerpt
 * @return string
 */
function sudbury_continue_reading_link() {
	global $post;
	if ( isset( $post->BLOG_ID ) && $post->BLOG_ID != get_current_blog_id() ) {
		$html = ' <a href="' . esc_url( network_get_permalink() ) . '">';
	} else {
		$html = ' <a href="' . esc_url( get_permalink() ) . '">';
	}

	if ( $post->post_type != 'link' ) {
		return $html . __( 'More <span class="meta-nav">&rarr;</span>', 'sudbury' ) . '</a>';
	} else {
		return $html;
	}
}

/**
 * @param string $more The more text
 *
 * @return string
 */
function sudbury_auto_excerpt_more( $more ) {
	return ' &hellip;' . sudbury_continue_reading_link();
}

add_filter( 'excerpt_more', 'sudbury_auto_excerpt_more' );

/**
 * Adds the Continue Reading Link onto the end of the Excerpt
 *
 * @param string $output The already calculated excerpt
 *
 * @return string The New Excerpt
 */
function sudbury_custom_excerpt_more( $output ) {
	if ( has_excerpt() && ! is_attachment() ) {
		$output .= sudbury_continue_reading_link();
	}

	return preg_replace( "#(^(&nbsp;|\s)+|(&nbsp;|\s)+$)#", "", $output );
}

add_filter( 'get_the_excerpt', 'sudbury_custom_excerpt_more' );

/**
 * Filter function to change the max length of an excerpt to 40 characters across all sites
 *
 * @param int $length The Existing Length of the Excerpt
 *
 * @return int The New Excerpt Length
 */
function sudbury_excerpt_length( $length ) {
	return 35;
}

add_filter( 'excerpt_length', 'sudbury_excerpt_length' );

/**
 * This function modifies the action row for posts and adds the private and public-archive links to make a post private
 * or public
 *
 * @param array   $actions The Actions that are already registered
 * @param WP_Post $post    The Post in the row
 *
 * @return array The new List of actions for the actions row
 */
function sudbury_register_archive_row_links( $actions, $post ) {
	// Custom Post Status
	if ( 'post' == $post->post_type ) {
		if ( 'public-archive' != $post->post_status ) {
			$move_to_public_archive_url = esc_url( admin_url() ) . 'admin.php?action=sudbury_make_status_change&post_status=public-archive&post_id=' . $post->ID;
			$move_to_public_archive_url .= '&return_url=' . esc_url( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
			$move_to_public_archive_url = wp_nonce_url( $move_to_public_archive_url, 'sudbury_make_status_change_public-archive', 'sudbury_make_status_change_nonce' );


			$actions['public_archive'] = '<a href="' . $move_to_public_archive_url . '" title="' . esc_attr( 'Archive (public)' ) . '">Archive (public)</a>';
		}

		if ( 'private' != $post->post_status && is_super_admin() ) {
			$move_to_private_archive_url = esc_url( admin_url() ) . 'admin.php?action=sudbury_make_status_change&post_status=private&post_id=' . $post->ID;

			$move_to_private_archive_url .= '&return_url=' . esc_url( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
			$move_to_private_archive_url = wp_nonce_url( $move_to_private_archive_url, 'sudbury_make_status_change_private', 'sudbury_make_status_change_nonce' );

			// Use Private Post status:  (Allocated a slug, visible to admins - maybe Internal if we implement that, and hidden from public)
			$actions['private_archive'] = '<a href="' . $move_to_private_archive_url . '" title="' . esc_attr( 'Move this to the Private Archive where it will not be publicly accessible' ) . '" class="submitdelete">Hide</a>';
		}
	}

	if ( isset( $actions['schedule'] ) ) {
		unset( $actions['schedule'] );
	}

	return $actions;
}

add_filter( 'post_row_actions', 'sudbury_register_archive_row_links', 20, 2 );

/**
 * Adds a copy-link link to the post action row for the WordPress Admin Post Table Lists which opens a prompt with the post's shortlink
 *
 * @param array   $actions The List of Actions to take
 * @param WP_Post $post    The post
 *
 * @return array The New List of Actions to take
 */
function sudbury_generic_row_links( $actions, $post ) {

	if ( sudbury_is_guest_post( $post->ID ) ) {
		$poster = sudbury_sharing_get_root_post( $post );

		$keep    = array( 'trash', 'delete', 'restore', 'untrash' );
		$actions = array_intersect_key( $actions, array_flip( $keep ) );

		$actions['posted_by'] = '<span style="color:#000;">Posted By </span><a href="' . get_site_url( $poster['BLOG_ID'], 'wp-admin/post.php?action=edit&post=' . $poster['ID'] ) . '"> ' . get_blog_option( $poster['BLOG_ID'], 'blogname' ) . '</a>';

		return $actions;
	}

	unset( $actions['clone'] );

	$url           = add_query_arg( array( 'p' => $post->ID ), sudbury_get_the_site_url() );
	$prompt_script = 'prompt("Shortlink for ' . $post->post_title . '", "' . $url . '");';

	$actions['copy_link'] = '<a href="#" onclick="' . esc_attr( $prompt_script ) . '" title="' . esc_attr( 'Copy ShortLink' ) . '">Shortlink</a>';


	return $actions;
}

add_filter( 'post_row_actions', 'sudbury_generic_row_links', 50, 2 );
add_filter( 'page_row_actions', 'sudbury_generic_row_links', 50, 2 );
add_filter( 'media_row_actions', 'sudbury_generic_row_links', 50, 2 );


function sudbury_guest_posted_post_class( $classes, $class, $post_id ) {
	if ( is_admin() && sudbury_is_guest_post( $post_id ) ) {
		$classes[] = 'guest-posted';
	}

	return $classes;
}

add_filter( 'post_class', 'sudbury_guest_posted_post_class', 10, 3 );
/**
 * Adds a direct link to the file for the WordPress Admin Media Table List
 *
 * @param array   $actions The List of Actions to take
 * @param WP_Post $post    The attachment
 *
 * @return array The New List of Actions to take
 */
function sudbury_media_direct_links( $actions, $post ) {
	$actions['file_url'] = '<a href="' . wp_get_attachment_url( $post->ID ) . '">File</a>';

	return $actions;
}

add_filter( 'media_row_actions', 'sudbury_media_direct_links', 10, 2 );

/**
 * Changes the status of a post when the row action link is clicked for a post
 *
 * Do not confuse with sudbury_check_status_post() which works on save_post and publish_post actions
 */
function sudbury_make_status_change() {

	$redirect = $_REQUEST['return_url'];

	if ( current_user_can( 'publish_posts' ) ) {
		if ( isset( $_REQUEST['post_id'] ) && isset( $_REQUEST['post_status'] ) ) {
			$id = intval( $_REQUEST['post_id'] );
			if ( 0 == $id ) {
				sudbury_redirect_error( "Invalid post ID, Make sure that you have saved that post", $redirect );
			}

			$status = esc_html( $_REQUEST['post_status'] );

			check_admin_referer( 'sudbury_make_status_change_' . $status, 'sudbury_make_status_change_nonce' );

			wp_update_post( array(
				'ID'          => $id,
				'post_status' => $status
			) );

			sudbury_redirect_updated( "Updated the status of the post", $redirect );
		} else {
			sudbury_redirect_error( 'Failed to change status because the arguments post_id and post_status were not supplied', $redirect );
		}
	} else {
		sudbury_redirect_error( 'Failed to change status because you do not have the correct privileges', $redirect );
	}

}

add_action( 'admin_action_sudbury_make_status_change', 'sudbury_make_status_change' );

function add_custom_mime_types( $mimes ) {
	return array_merge( $mimes, array(
		'mht' => 'multipart/related',
	) );
}

add_filter( 'upload_mimes', 'add_custom_mime_types' );

if ( ! defined( 'ATTACHMENTS_DEFAULT_INSTANCE' ) ) {
	define( 'ATTACHMENTS_DEFAULT_INSTANCE', false );
}

/**
 * Registers the default settings for the attachments plugin
 *
 * We Register the post, page, and meeting post types and make the meta box position go to the very bottom of the edit page
 *
 * @param $attachments_class
 */
function sudbury_attachments_plugin_registration( $attachments_class ) {
	$attachments_class->register( 'attachments', array(
			'post_type' => array( 'post', 'page', 'meeting' ),
			'position'  => 'advanced',
			'priority'  => 'low'
		)
	);

}

add_action( 'attachments_register', 'sudbury_attachments_plugin_registration' );

/**
 * Keeps track of the Update history for the sudbury_check_status_post() Function so that it doesn't infinite
 * loop when it calls wp_update_post() again
 */
$sudbury_update_history = array();

/**
 * If a user changes the Status of a Post to be public-archive update it on save
 *
 * @param int $post_id The ID of the Post being Saved or Published
 */
function sudbury_check_status_post( $post_id ) {
	global $sudbury_update_history;
	if ( ! wp_is_post_revision( $post_id ) && ! in_array( $post_id, $sudbury_update_history ) ) {
		$sudbury_update_history[] = $post_id;
		if ( current_user_can( 'publish_posts' ) ) {
			if ( isset( $_REQUEST['post_status'] ) && 'public-archive' == $_REQUEST['post_status'] && 'public-archive' != get_post_status( $post_id ) ) {
				if ( check_admin_referer( 'sudbury-status-change', 'sudbury_update_status_' . $post_id ) ) {
					wp_update_post( array(
						'ID'          => $post_id,
						'post_status' => 'public-archive'
					) );
				} else {
					wp_die( 'Nonce Verification Failed for status transfer to Public Archive' );
				}
			}
		} else {
			/* User Doesn't have enough privileges */
		}
	}

	$post = get_post( $post_id );
	if ( isset( $_REQUEST['post_ID'] ) && isset( $_REQUEST['post_status'] ) && isset( $_REQUEST['original_post_status'] ) && 'public-archive' === $_REQUEST['original_post_status'] && 'public-archive' === $_REQUEST['post_status'] && $post_id == $_REQUEST['post_ID'] ) {
		if ( ! isset( $GLOBALS['lock_save_post_sudbury_check_status_post'] ) ) {
			// $GLOBALS['lock_save_post_sudbury_check_status_post'] = true;
			// wp_transition_post_status( 'publish', 'public-archive', $post );
			// wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
		}
	}
}

add_action( 'save_post', 'sudbury_check_status_post', 1 );
add_action( 'publish_post', 'sudbury_check_status_post' );

/**
 * Adds a nonce to the WordPress Post Edit Page in case they decide to change the status of the post throguh the submitbox
 */
function sudbury_make_status_change_nonce() {
	global $post;
	wp_nonce_field( 'sudbury-status-change', 'sudbury_update_status_' . $post->ID );

}

add_action( 'post_submitbox_misc_actions', 'sudbury_make_status_change_nonce' );


function sudbury_post_submitbox_start( $post ) {
	// The FB Post Link
	if ( $post->post_status == 'publish' || true ) {
		?>
		<div id="fb-action">
			<a href="#" class="post_to_facebook" data-url="<?php echo esc_attr( wp_get_shortlink( $post->id ) ); ?>">Post to FaceBook</a>
		</div>
		<?php
	}
}

add_action( 'post_submitbox_start', 'sudbury_post_submitbox_start' );

/**
 * Gets the Label when there are no Documents Found
 *
 * @deprecated This isn't a really smart filter to have... Remove if there's time
 *
 * @param string $default The Default Message
 *
 * @return string The New No Documents Found Message
 */
function sudbury_no_documents_text_filter( $default ) {
	return $default . ' for this ' . sudbury_get_site_type();
}

add_filter( 'sudbury_no_posts_label', 'sudbury_no_documents_text_filter' );

/**
 * Changes the Admin Menu labels for the default post type 'post' from Post -> News Article
 */
function change_post_menu_label() {
	global $menu;
	global $submenu;
	if ( current_user_can( 'edit_posts' ) ) {
		$menu[5][0]                 = 'News Articles';
		$submenu['edit.php'][5][0]  = 'News Articles';
		$submenu['edit.php'][10][0] = 'Add News Articles';
		if ( isset( $submenu['edit.php'][16] ) && current_user_can( 'manage_categories' ) ) {
			$submenu['edit.php'][16][0] = 'Tags';
			$submenu['edit.php'][16][1] = 'manage_categories';
			$submenu['edit.php'][16][2] = 'edit-tags.php?taxonomy=post_tag';
		}
	}
	// Override Gravity forms icon to a dashicon
	if ( isset( $menu['16.9'] ) ) {
		$menu['16.9'][6] = 'dashicons-clipboard';
	}
}

add_action( 'admin_menu', 'change_post_menu_label', 20 );

/**
 * Changes the Post Type labels for the default Post Type 'post' from Post -> News Article
 */
function change_post_object_label() {
	global $wp_post_types;
	if ( current_user_can( 'edit_posts' ) ) {
		$labels                     = &$wp_post_types['post']->labels;
		$labels->name               = 'News Articles';
		$labels->singular_name      = 'News Article';
		$labels->add_new            = 'Add News Article';
		$labels->add_new_item       = 'Add News Article';
		$labels->edit_item          = 'Edit News Article';
		$labels->new_item           = 'News Article';
		$labels->view_item          = 'View News Article';
		$labels->search_items       = 'Search News Articles';
		$labels->not_found          = 'No News Articles found';
		$labels->not_found_in_trash = 'No News Articles found in Trash';
	}
}

add_action( 'init', 'change_post_object_label' );

/**
 * Removes the My Sites menu item to replace it with another better version for network admins
 */

function reorder_admin_bar_items() {
	remove_action( 'admin_bar_menu', 'wp_admin_bar_my_account_menu', 10 );
	remove_action( 'admin_bar_menu', 'wp_admin_bar_my_sites_menu', 20 );
	remove_action( 'admin_bar_menu', 'wp_admin_bar_edit_menu', 30 );
	remove_action( 'admin_bar_menu', 'wp_admin_bar_shortlink_menu', 80 );
	remove_action( 'admin_bar_menu', 'wp_admin_bar_updates_menu', 70 );

	if ( ! is_network_admin() && ! is_user_admin() ) {
		remove_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 40 );
		remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 50 );
		remove_action( 'admin_bar_menu', 'wp_admin_bar_appearance_menu', 60 );
	}


	add_action( 'admin_bar_menu', 'wp_admin_bar_my_account_menu', 100 );
	add_action( 'admin_bar_menu', 'wp_admin_bar_my_sites_menu', 10 );
	add_action( 'admin_bar_menu', 'wp_admin_bar_edit_menu', 40 );
	add_action( 'admin_bar_menu', 'wp_admin_bar_shortlink_menu', 20 );
	add_action( 'admin_bar_menu', 'wp_admin_bar_updates_menu', 50 );

	if ( ! is_network_admin() && ! is_user_admin() ) {
		add_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 80 );
		add_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 170 );
		add_action( 'admin_bar_menu', 'wp_admin_bar_appearance_menu', 40 );
	}
}

add_action( 'admin_bar_menu', 'reorder_admin_bar_items', 20 );

/**
 * This function forces all blogs to be shown on the My Sites Page for Super Admins.  This makes site switching easier
 *
 * @param array $blogs An array of blog objects belonging to the user.
 *
 * @return array A list of the user's blogs . An empty array if the user doesn't exist or belongs to no blogs.
 */
function sudbury_all_sites_on_my_sites_for_super_admins( $blogs ) {
	if ( function_exists( 'get_current_screen' ) ) {
		// Must Be My-Sites Screen or the global 'override_all_blogs_please' is set
		//  AND a super admin (of course)
		if ( ( ( is_object( get_current_screen() ) && ( 'my-sites' == get_current_screen()->base ) ) || ( isset( $GLOBALS['override_all_blogs_please'] ) && $GLOBALS['override_all_blogs_please'] ) ) && is_super_admin() ) {
			$blogs = array();
			foreach_blog( function ( $blog ) use ( &$blogs ) {
				$blog = get_blog_details( $blog['blog_id'] );

				$blogs[] = (object) array(
					'userblog_id' => $blog->blog_id,
					'blogname'    => $blog->blogname,
					'domain'      => $blog->domain,
					'path'        => $blog->path,
					'site_id'     => $blog->site_id,
					'siteurl'     => $blog->siteurl,
					'archived'    => $blog->archived,
					'spam'        => $blog->spam,
					'deleted'     => $blog->deleted,
				);
			} );

			return $blogs;
		}
	}

	return $blogs;
}

add_filter( 'get_blogs_of_user', 'sudbury_all_sites_on_my_sites_for_super_admins' );

/**
 * Adds (echos) a field to the My Site's Page that enables Users to search for a site in realtime and then hit enter to go to
 * the dashboard of that site
 */
function my_sites_quick_go() {
	?>

	<table class="form-table">
		<tr>
			<th scope="row"><?php _e( 'Quick Select' ); ?></th>
			<td>
				<input type="text" tabindex="0" id="my_sites_quick_go"
					   placeholder="Start Typing To Narrow Results, Hit Enter to Go" autofocus="autofocus"
					   style="width:375px;padding-top:6px;" />
			</td>
		</tr>
	</table>

	<?php
}

add_action( 'myblogs_allblogs_options', 'my_sites_quick_go' );

/**
 * Adds extra links to the Admin Bar
 *
 * @param $admin_bar The admin bar object
 */
function sudbury_my_sites_custom_menu( $admin_bar ) {
	global $current_screen;
	global $post;

	$report_environment = "The following information has been provided by the application server.\n";
	$report_environment .= "\nIS_PRODUCTION_SERVER = " . var_export( IS_PRODUCTION_SERVER, true );
	$report_environment .= "\nHTTP_HOST = " . var_export( $_SERVER['HTTP_HOST'], true );
	$report_environment .= "\nIS_USER_LOGGED_IN = " . var_export( is_user_logged_in(), true );
	$report_environment .= "\nSUDBURY_VERSION = " . var_export( SUDBURY_VERSION, true );
	$report_environment .= "\nTIMESTAMP = " . var_export( ( new DateTime( 'now', new DateTimeZone( 'America/New_York' ) ) )->format( 'Y-m-d H:i:s T' ), true );
	$report_environment .= "\nSYSTEM_STATUS = " . var_export( sudbury_get_status(), true );
	$report_environment .= "\nREQUEST_URI = " . var_export( $_SERVER['REQUEST_URI'], true );

	$report_summary = '';

	$report_environment = urlencode( $report_environment );
	$report_environment = str_ireplace( '%0A', '%250A', $report_environment );


	add_filter( 'clean_url', 'double_encoded_nl_to_encoded_nl' );
	function double_encoded_nl_to_encoded_nl( $url ) {
		return str_ireplace( '%250A', '%0A', $url );
	}

	// Support Group
	$admin_bar->add_menu( array(
		'id'    => 'sudbury-report-menu',
		'title' => 'Report a Bug',
		'href'  => "https://jira.sudbury.ma.us/secure/CreateIssueDetails!init.jspa?pid=10007&issuetype=1&priority=5&summary=" . $report_summary . "&environment=" . $report_environment,
		'meta'  => array(
			'title' => __( 'Report Bugs' ),
		),
	) );

	// Support Group
	$admin_bar->add_menu( array(
		'id'    => 'sudbury-support-menu',
		'title' => 'Contact Support',
		'href'  => admin_url( 'index.php' ),
		'meta'  => array(
			'title' => __( 'Support' ),
		),
	) );

	// Report Issues (Bitbucket)
	$admin_bar->add_menu( array(
			'parent' => 'sudbury-support-menu',
			'id'     => 'sudbury-support-report-issue',
			'title'  => __( 'Report an Issue' ),
			'href'   => "https://bitbucket.org/sudbury/sudburywp/issues?status=new&status=open"
		)
	);


	// Contact Webmaster
	$admin_bar->add_menu( array(
			'parent' => 'sudbury-support-external',
			'id'     => 'sudbury-support-menu-webmaster',
			'title'  => __( 'Contact Webmaster - Mark' ),
			'href'   => "mailto:webmaster@sudbury.ma.us"
		)
	);

	// Contact Developer
	$admin_bar->add_menu( array(
			'parent' => 'sudbury-support-external',
			'id'     => 'sudbury-support-menu-dev',
			'title'  => __( 'Contact Developer - Eddie' ),
			'href'   => "mailto:hurtige@sudbury.ma.us"
		)
	);

	// Infosys phone number
	$admin_bar->add_menu( array(
			'parent' => 'sudbury-support-external',
			'id'     => 'sudbury-unssupport-menu-call',
			'title'  => __( 'Or Call InfoSys @ x3307' ),
			'href'   => false
		)
	);

	// Support Menu
	$admin_bar->add_group( array(
			'parent' => 'sudbury-support-menu',
			'id'     => 'sudbury-support-external',
			'meta'   => array( 'class' => 'ab-sub-secondary' )
		)
	);

	// Brings you to the dashboard
	$admin_bar->add_menu( array(
		'id'    => 'sudbury-main-menu',
		'title' => 'Main Menu',
		'href'  => admin_url( 'index.php' ),
		'meta'  => array(
			'title' => __( 'Main Menu' ),
		),
	) );

	$admin_bar->add_menu( array(
		'id'    => 'sudbury-help',
		'title' => 'Help',
		'href'  => '#sudbury-help',
		'meta'  => array(
			'title' => __( 'Help' ),
			'class' => 'sudbury-help',
		),
	) );


	// Cancel button if editing a post
	if ( $current_screen && 'post' == $current_screen->base && $post ) {
		$referer       = wp_get_referer();
		$referer_parts = parse_url( $referer );

		$file = basename( $referer_parts['path'] );
		if ( in_array( $file, array( 'post.php', 'post-new.php' ) ) ) {
			$list_page_prefs = get_user_meta( get_current_user_id(), "list_table_prefs_{$post->post_type}", true );
			$referer         = admin_url( 'edit.php?' . $list_page_prefs );
		}

		$admin_bar->add_menu( array(
			'id'    => 'sudbury-cancel-link',
			'title' => 'List - Cancel',
			'href'  => $referer,
		) );
	}
}


add_action( 'admin_bar_menu', 'sudbury_my_sites_custom_menu', 999 );


/**
 * Replaces the default wordpress welcome panel with our own welcome panel which is questionably better
 */
function sudbury_welcome_panel() {
	?>
	<style type="text/css">
		div.welcome-panel-content {
			display: none;
		}
	</style>

	<div class="custom-welcome-panel-content">
		<h3><?php _e( 'Welcome to Wordpress (The New WebEditor)' ); ?></h3>

		<p class="about-description"><?php _e( 'Wordpress has replaced the WebEditor as the primary method of editing the town website' ); ?></p>

		<div class="welcome-panel-column-container">
			<div class="welcome-panel-column">
				<h4><?php _e( "Let's Get Started" ); ?></h4>

				<?php $type = sudbury_get_site_type(); ?>
				<a class="button button-primary button-hero load-customize hide-if-no-customize"
				   href="<?php echo admin_url( 'admin.php?page=sudbury-dept-info-options-page' ); ?>"><?php _e( 'Edit Contact Info' ); ?></a>

				<p class="hide-if-no-customize"><?php printf( __( 'or, <a href="%s">edit your %s\'s settings</a>' ), admin_url( 'options-general.php' ), esc_html( $type ) ); ?></p>
			</div>
			<div class="welcome-panel-column">
				<h4><?php _e( 'Next Steps' ); ?></h4>
				<ul>
					<?php if ( 'page' == get_option( 'show_on_front' ) && ! get_option( 'page_for_posts' ) ) : ?>
						<li><?php printf( '<a href="%s" class="welcome-icon welcome-edit-page load-customize">' . __( 'Edit your front page' ) . '</a>', get_edit_post_link( get_option( 'page_on_front' ) ) ); ?></li>
						<li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page load-customize">' . __( 'Add custom pages' ) . '</a>', admin_url( 'post-new.php?post_type=page' ) ); ?></li>
					<?php elseif ( 'page' == get_option( 'show_on_front' ) ) : ?>
						<li><?php printf( '<a href="%s" class="welcome-icon welcome-edit-page load-customize">' . __( 'Edit your Front Page' ) . '</a>', get_edit_post_link( get_option( 'page_on_front' ) ) ); ?></li>
						<li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page load-customize">' . __( 'Add Custom Pages' ) . '</a>', admin_url( 'post-new.php?post_type=page' ) ); ?></li>
						<li><?php printf( '<a href="%s" class="welcome-icon welcome-write-blog load-customize">' . __( 'Add a News Article' ) . '</a>', admin_url( 'post-new.php' ) ); ?></li>
					<?php
					else : ?>
						<li><?php printf( '<a href="%s" class="welcome-icon welcome-write-blog load-customize">' . __( 'Write a News Article' ) . '</a>', admin_url( 'post-new.php' ) ); ?></li>
						<li><?php printf( '<a href="%s" class="welcome-icon welcome-add-page load-customize">' . __( 'Create an FAQ' ) . '</a>', admin_url( 'post-new.php?post_type=faq' ) ); ?></li>
					<?php endif; ?>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-view-site load-customize">' . __( 'View your ' . esc_html( $type ) . "'s Site" ) . '</a>', home_url( '/' ) ); ?></li>
				</ul>
			</div>
			<div class="welcome-panel-column welcome-panel-last">
				<h4><?php _e( 'More Actions' ); ?></h4>
				<ul>
					<li><?php printf( '<div class="welcome-icon welcome-widgets-menus">' . __( 'Manage <a href="%1$s">Sidebar</a> or <a href="%2$s">Links Menu</a>' ) . '</div>', admin_url( 'widgets.php' ), sudbury_get_links_menu_url() ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-comments">' . __( 'Turn comments on or off' ) . '</a>', admin_url( 'options-discussion.php' ) ); ?></li>
					<li><?php printf( '<a href="%s" class="welcome-icon welcome-learn-more">' . __( 'Learn more about getting started' ) . '</a>', __( 'http://codex.wordpress.org/First_Steps_With_WordPress' ) ); ?></li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}

add_action( 'welcome_panel', 'sudbury_welcome_panel' );

/**
 * This is the hook where emails should get protected by converting
 * <a href="mailto:someone@sudbury.ma.us">someone@sudbury.ma.us</a>
 *      TO
 * <a href="mailto:webmaster@sudbury.ma.us" data-domain="sudbury.ma.us" data-salt="ac" data-to="acsomeoneac">webmaster@sudbury.ma.us</a>
 *
 * Note this isn't meant to be human proof... in fact as a human you should be able to look at a few scrambled outputs and EASILY be able to figure out the
 * real emails, This is primarily meant to stop email harvesters
 *
 * TODO: Implement something for General Emails that are not in a link... i.e. <p> someone@sudbury.ma.us </p>
 *
 * @param string $input The input html containing email links
 *
 * @return mixed The html with the email links scrambled
 */
function sudbury_protect_emails( $input ) {
	// Generate random string 100 to 200 chars long
	preg_match_all( '/href="mailto:([^@]+)@([^\"]+)\"[^\>]*>(.*?)\<\/a\>?/', $input, $matches );
	if ( ! empty( $matches[0] ) ) {
		$random_string = random_string( rand( 100, 200 ) );
		foreach ( $matches[0] as $i => $match ) {

			$to     = $matches[1][ $i ];
			$domain = $matches[2][ $i ];
			$text   = $matches[3][ $i ];
			$new    = 'data-scrambled data-to="' . esc_attr( $random_string . $to . $random_string ) . '" data-salt="' . esc_attr( $random_string ) . '" data-domain="' . esc_attr( $domain ) . '" href="mailto:webmaster@sudbury.ma.us" data-text="' . esc_attr( $random_string . $text . $random_string ) . '">webmaster@sudbury.ma.us</a>';
			$input  = str_replace( $match, $new, $input );
		}
	}

	return $input;
}

add_filter( 'the_excerpt', 'sudbury_protect_emails' );
add_filter( 'the_content', 'sudbury_protect_emails' );

/**
 * Changes the logo in the Admin bar to the Sudbury town logo
 */
function custom_admin_logo() {
	echo "<style type=\"text/css\">\n";
	if ( is_admin_bar_showing() ) {
		echo '#wp-admin-bar-wp-logo > .ab-item .ab-icon {
            background-image: url(' . plugins_url( 'images/admin-bar-logo.png', __FILE__ ) . ') !important;
            background-position: 0 !important;
            background-size: 20px 20px;
        }';
	}
	global $post;
	if ( $post && 'image' !== substr( $post->post_mime_type, 0, 5 ) ) {
		echo 'label[for="attachment_caption"], #attachment_caption { display:none; }';
	}
	echo "\n</style>";
}

add_action( 'admin_head', 'custom_admin_logo' );
add_action( 'wp_head', 'custom_admin_logo' );

/**
 * Disables Signup with a much cleaner look than trying to cramp it into the theme
 */
function sudbury_nicer_signup_disabled() {
	wp_die( 'Sorry, Site Registration is disabled. If you believe you are receiving this message in error please contact ' . get_sudbury_contact_admin_message() );
}

add_action( 'signup_header', 'sudbury_nicer_signup_disabled' );

/**
 * Define default terms for custom taxonomies
 *
 * @author    Michael Fields    http://wordpress.mfields.org/
 * @author    Eddie Hurtig <hurtige@ccs.neu.edu>
 * @props     John P. Bloch    http://www.johnpbloch.com/
 *
 * @since     2010-09-13
 * @alter     2014-05-12
 *
 * @license   GPLv2
 */
function mfields_set_default_object_terms( $post_id, $post ) {

	if ( 'publish' === $post->post_status ) {
		// Notice: make sure that these slugs exist for every site.  If you are getting errors check on that...
		$defaults = array(
			'faq_categories'      => array( 'uncategorized' ),
			'document_categories' => array( 'uncategorized' ),
			'link_categories'     => array( 'uncategorized' ),
			'service_categories'  => array( 'uncategorized' ),
		);

		$taxonomies = get_object_taxonomies( $post->post_type );

		foreach ( (array) $taxonomies as $taxonomy ) {
			$terms = wp_get_post_terms( $post_id, $taxonomy );
			if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
				wp_set_object_terms( $post_id, $defaults[ $taxonomy ], $taxonomy );
			}
		}
	}

}

add_action( 'save_post', 'mfields_set_default_object_terms', 100, 2 );


// Known Issue with Wordpress https://core.trac.wordpress.org/ticket/15928
/**
 * Fix SSL on Post Thumbnail URLs
 *
 * @param string $url     The Post Thumbnail URL
 * @param int    $post_id The Post id of the attachment
 *
 * @return int The corrected thumbnail URL
 */
function ssl_post_thumbnail_urls( $url, $post_id ) {

	//Correct protocol for https connections
	list( $protocol, $uri ) = explode( '://', $url, 2 );
	if ( is_ssl() ) {

		if ( 'http' == $protocol ) {
			$protocol = 'https';
		}
	} else {
		if ( 'https' == $protocol ) {
			$protocol = 'http';
		}
	}

	return add_query_arg( array( 'version' => md5( get_post( $post_id )->post_modified ) ), $protocol . '://' . $uri );
}

add_filter( 'wp_get_attachment_url', 'ssl_post_thumbnail_urls', 10, 2 );

function sudbury_add_user_field( $user ) {
	?>
	<table class="form-table">
		<tr>
			<th>
				<label for="sudbury_usermeta"><?php _e( 'User Meta' ); ?></label>
			</th>
			<td>
				<pre class="sudbury-well"><?php print_r( array_map( 'current', get_user_meta( $user->ID ) ) ); ?></pre>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Forces user settings that make workflow easier... like 'cats' should always be 'pop' and 'editor' should always be 'tinymce'
 *
 * @param string $settings A query String formatted list of settings (key=value&key2=value2)
 *
 * @return string Modified User settings that we would like to force for all users
 */
function sudbury_user_settings( $settings ) {

	// Parse the query string format of user-settings to an assoc array
	parse_str( $settings, $settings_array );

	// Force user to always see most used categories by default
	$settings_array['cats'] = 'pop';

	// Force user to always use visual editor by default
	$settings_array['editor'] = 'tinymce';

	// Rebuild the settings array into a query string format
	return http_build_query( $settings_array );
}

add_filter( 'get_user_option_user-settings', 'sudbury_user_settings' );


// Callback function to insert 'styleselect' into the $buttons array
function my_mce_buttons_2( $buttons ) {
	array_unshift( $buttons, 'styleselect' );

	return $buttons;
}

// Register our callback to the appropriate filter
add_filter( 'mce_buttons_2', 'my_mce_buttons_2' );


// Callback function to filter the MCE settings
function my_mce_before_init_insert_formats( $init_array ) {
	// Define the style_formats array
	$style_formats = array(
		// Each array child is a format with it's own settings
		array(
			'title'   => 'padding 10px',
			'block'   => 'span',
			'classes' => 'marginB10',
			'wrapper' => false,

		),
		array(
			'title'   => 'padding 5px',
			'block'   => 'span',
			'classes' => 'marginB5',
			'wrapper' => false,
		),
		array(
			'title'   => '.ltr⇢',
			'block'   => 'blockquote',
			'classes' => 'ltr',
			'wrapper' => true,
		),
	);
	// Insert the array, JSON ENCODED, into 'style_formats'
	$init_array['style_formats'] = json_encode( $style_formats );

	return $init_array;

}

// Attach callback to 'tiny_mce_before_init'
add_filter( 'tiny_mce_before_init', 'my_mce_before_init_insert_formats', 9999 );

/**
 * Fixes a screw up in the WP Rewrite Engine that doesn't take into account the current blog permalink structure... so if
 * you do a switch_to_blog() it will still use the original blog's permastruct. This is really only a problem for us
 * with the blog 1 /blog forced permastruct which we replace if the original blog was blog 1
 *
 * @param string $permalink The potentially broken permalink
 *
 * @return string The happy permalink
 */
function sudbury_fix_cores_permalink_screw_up( $permalink ) {
	if ( ms_is_switched() && 1 == $GLOBALS['_wp_switched_stack'][0] ) {
		return str_replace( '/blog', '', $permalink );
	}

	return $permalink;
}

add_filter( 'post_type_link', 'sudbury_fix_cores_permalink_screw_up' );

function sudbury_tinymce_settings( $existing ) {
	$json             = '{
        "file": {
            "title": "File",
            "items": "print"
        },
        "edit": {
            "title": "Edit",
            "items": "undo redo | cut copy paste pastetext | selectall | searchreplace"
        },
        "insert": {
            "title": "Insert",
            "items": "link hr wp_more wp_page anchor | insertdatetime"
        },
        "view": {
            "title": "View",
            "items": "visualchars visualblocks visualaid | fullscreen"
        },
        "format": {
            "title": "Format",
            "items": "bold italic underline strikethrough superscript subscript | formats | removeformat"
        },
        "table": {
            "title": "Table",
            "items": "inserttable tableprops deletetable | cell row column"
        },
        "tools": {
            "title": "Tools",
            "items": "spellchecker code"
        }
    }';
	$existing['menu'] = $json;

	return $existing;

	return array_merge( $existing, $settings );
}

add_filter( 'tiny_mce_before_init', 'sudbury_tinymce_settings' );

add_action( 'publish_future_post', 'my_test_future_post', 1 );
add_action( 'publish_future_post', 'my_test_future_post', 1000 );

function my_test_future_post( $post_id ) {
	sudbury_log( "FUTURE POSTED $post_id" );
}

add_action( 'admin_init', 'posts_order_wpse_91866' );

function posts_order_wpse_91866() {
	add_post_type_support( 'post', 'page-attributes' );
}

function sudbury_log_term_created( $term_id, $tax_id ) {
	global $blog_id;
	_sudbury_log( 'Created Term $term_id for tax $tax_id on blog $blog_id' );
}

add_action( 'created_term', 'sudbury_log_term_created' );

$last_tag            = exec( 'git describe --tags --abbrev=0' );
$major_minor_version = implode( '.', array_slice( explode( '.', $last_tag ), 0, 2 ) );
$patch_version       = exec( "git rev-list $last_tag..HEAD --count" );
define( 'SUDBURY_VERSION', "$major_minor_version.$patch_version" );
