<?php
/**
 * Plugin Name: Global Media for Multisite
 * Description: Creates a global media library for a multisite network that is accessible to all sites
 * Plugin URI: http://hurtigtechnologies.com
 * Author: Eddie Hurtig
 * Author URI: http://hurtigtechnologies.com
 * Version: 1.0.0
 */
define( 'WPGM_BLOGID', 101 );

if ( is_multisite() ) {

	// defines the constant "WPGM_BLOGID"
	// using a function in order to hide the variables used from global scope
	function wpgm_init() {

		if ( ! defined( 'WPGM_BLOGID' ) && function_exists( 'get_site_option' ) ) {
			$blog_id = get_site_option( 'wpgm_global_blog_id', false );
			if ( ! $blog_id ) {
				$blog_id = 1;
			}
			define( 'WPGM_BLOGID', $blog_id );
		}

		if ( ! defined( 'WPGM_PREVENT_ATTACHMENT_DELETION' ) ) {
			define( 'WPGM_PREVENT_ATTACHMENT_DELETION', true );
		}

	}

	wpgm_init();

	require_once( 'wpgm-functions.php' );

	require_once( 'wpgm-admin.php' );

} else {
	if ( is_admin() ) {
		echo 'Error: Wordpress Global Media requires a multisite install to work properly';
	}
}

