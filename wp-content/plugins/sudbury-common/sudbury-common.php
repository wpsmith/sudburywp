<?php
/*
Plugin Name: Sudbury Common
Plugin URI: http://sudbury.ma.us/
Description: Common Changes to WordPress that are needed on all sites regardless of theme
Version: 1.0
Author: Eddie Hurtig
Author URI: http://hurtigtechnologies.com
Network: True
*/

require_once 'sudbury-debug.php';
require_once 'sudbury-error-handling.php';
require_once 'global-categories.php';

/**
 * The primary init function for the-sudbury-plugin.  It registers a custom post status, adds the custom editor style for all themes, and anything else
 *
 * Tasks
 *  - Registers the public-archive post status
 *  - Blocks Users from accessing sites that are not public with a wp_die()
 *    - Removes wpautop (See note in comment)
 */
function sudbury_common_init() {
	// If the user is trying to access the front end
	if ( ! is_admin() && ! is_login_page() ) {
		// Lets get the deatils for the blog they are trying to access
		$details = get_blog_details( get_current_blog_id(), false );
		// If the blog they are trying to access is not public and they are not internal then die
		if ( ! $details->public && ! is_internal() ) {
			wp_die( 'This site is for Internal Use Only.', 'Internal Site' );
			exit; // To Be Sure
		}
	}
}

add_action( 'init', 'sudbury_common_init' );

/**
 *  Changes the Wordpress logo to the Sudbury MA logo
 */
function sudbury_login_logo() {
	?>
    <style type="text/css">
        body.login div#login h1 a {
            background-image: url(<?php echo plugins_url('/images/login.png', __FILE__); ?>);
            padding-bottom: 30px;
            background-size: 283px 80px;
            width: auto;
        }
    </style>
	<?php
}

add_action( 'login_enqueue_scripts', 'sudbury_login_logo' );

/**
 *  Replaces the default Wordpress Admin footer with a branded line with a legal notice and copyright info
 */
function sudbury_footer_admin() {
	echo "Copyright " . date( 'Y' ) . ' <a href="http://sudbury.ma.us">Town of Sudbury</a> | Unauthorized Access to this system is strictly prohibited. | Maintained by the <a href="https://sudbury.ma.us/infosys/">Information Systems</a> department';
}

add_filter( 'admin_footer_text', 'sudbury_footer_admin' );

/**
 * Filter Function to determine the max upload size for wordpress depending on whether the user is an Admin or standard user
 *
 * @param $limit The Existing Limit (bytes)
 *
 * @return int The New Limit (bytes)
 */
function sudbury_upload_size_limit( $limit ) {
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();

		/*
		 * The upload_size_limit User Meta Field is stored in KB so we need to multiply by 1024 to convert to bytes
		 */
		if ( ( $user_limit = get_user_meta( $user_id, 'upload_size_limit', true ) ) && is_numeric( $user_limit ) && $user_limit > 0 ) {
			$limit = floor( $user_limit * 1024 );
		} else { // Going to default functionality
			if ( is_super_admin() ) {
				$limit = max( 1073741824, $limit ); // 1 GB (calculate #GB * 1024 * 1024 * 1024)
			} else {
				$limit = max( 5242880, $limit ); // 5 MB (calculate #MB * 1024 * 1024)
			}
		}
	}

	$php_max_upload = wp_convert_hr_to_bytes( @ini_get( 'upload_max_filesize' ) );
	$php_max_post   = wp_convert_hr_to_bytes( @ini_get( 'post_max_size' ) );

	return min( $limit, $php_max_upload, $php_max_post );
}

add_filter( 'upload_size_limit', 'sudbury_upload_size_limit', 999 );

/**
 * Adds the Upload Size Limit Field to a User Profile Page if the current user is a super admin
 *
 * @param $profile_fields The Existing Profile Fields
 *
 * @return mixed The new Profile Fields
 */
function sudbury_add_upload_size_limit_field( $profile_fields ) {
	if ( is_super_admin() ) {
		$profile_fields['upload_size_limit'] = 'Upload Size Limit (KB)';
	}

	return $profile_fields;
}

add_filter( 'user_contactmethods', 'sudbury_add_upload_size_limit_field' );

/**
 * Yell at the user if the they sent a $_POST['upload_size_limit'] but are not a super admin
 *
 * @param $user_id The ID of the User that is currently being edited
 */
function sudbury_save_upload_size_limit_field( $user_id ) {
	if ( ! is_super_admin() ) {
		wp_die( 'You aren\'t allowed to edit Max Upload Sizes! That\'s a super admin feature' );
		exit();
	}
}

add_action( 'personal_options_update', 'sudbury_save_upload_size_limit_field', 1 );
add_action( 'edit_user_profile_update', 'sudbury_save_upload_size_limit_field', 1 );


define( 'ATTACHMENTS_DEFAULT_INSTANCE', false );

/**
 * Allows Archived Sites to be shown
 *
 * @param bool $skip Skip the Check
 *
 * @return bool Whether to Skip the check or not
 */
function sudbury_skip_multisite_check( $skip ) {
	if ( get_blog_details()->archived ) {
		return true;
	}

	return $skip;
}

add_filter( 'ms_site_check', 'sudbury_skip_multisite_check' );


function sudbury_is_indexable( $indexable, $post, $blog_id ) {
	_sudbury_log( $indexable . ' Indexing Post ' . $post['ID'] . ' ' . $post['post_title'] . ' on ' . $blog_id );

	return $indexable;
}

add_filter( 'postindexer_is_post_indexable', 'sudbury_is_indexable', 10, 3 );

function sudbury_index_archived_sites( $indexing, $blog_id ) {
	if ( get_blog_status( $blog_id, 'archived' ) ) {
		return 'yes';
	}

	return $indexing;
}

add_filter( 'postindexer_is_blog_indexable', 'sudbury_index_archived_sites', 10, 2 );

/**
 * Logs when a post is indexed
 *
 * @param array $post The post
 */
function sudbury_log_indexed_post( $post ) {
	_sudbury_log( ' Indexed Post ' . $post['ID'] . ' ' . $post['post_title'] . ' MIGHT BE ON ' . (int) get_current_blog_id() );
}

add_action( 'postindexer_index_post', 'sudbury_log_indexed_post' );

if ( ! function_exists( 'is_login_page' ) ) {
	/**
	 * Determines if the current page is a login or sign up page
	 * @return bool whether the current page is the login or sign-up page
	 */
	function is_login_page() {
		if ( isset( $GLOBALS['pagenow'] ) ) {
			return in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) );
		}

		return false;
	}
}

add_filter( 'emr_enable_replace_and_search', '__return_false' );

// disable Gutenberg network wide
add_filter( 'use_block_editor_for_post', '__return_false', 10 );

if ( defined( "GOOGLE_MAPS_API_KEY" ) && ! function_exists( 'sudbury_google_maps_key' ) ) {
	function sudbury_google_maps_key( $value, $option = '' ) {
		return GOOGLE_MAPS_API_KEY;
	}

	add_filter( 'option_dbem_google_maps_browser_key', 'sudbury_google_maps_key' );
}


if ( ! function_exists( 'sudbury_has_type' ) ) {
	/**
	 * Returns true if the current blog (or blog with $id) has the given $type in it's sudbury_types option
	 *
	 * @param string $type THe Site Type to check (lowercase)
	 * @param bool|int $id The ID of the Blog. default: false for current blog
	 *
	 * @return bool Whether the Blog has the specified Type
	 */
	function sudbury_has_type( $type, $id = false ) {
		if ( $id ) {
			$types = get_blog_option( $id, 'sudbury_types', array() );
		} else {
			$types = get_option( 'sudbury_types', array() );
		}

		return in_array( $type, $types );
	}
}