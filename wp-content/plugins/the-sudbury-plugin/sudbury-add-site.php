<?php
/**
 * Adds Functionality to the Add Site Page to Choose Specific Options when you are creating a site
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Multisite
 */

/**
 * Run Filemaker Sync after a new blog is added
 */
add_action( 'wpmu_new_blog', function ( $blog_id ) {
	ob_start();

	ob_get_clean();

}, 10, 1 );

add_action( 'wpmu_new_blog', 'sudbury_cleanse_hello_world' );

/**
 * Force HTTPS Site URLS
 */
add_filter( 'pre_update_option_siteurl', 'sudbury_force_https_url' );
add_filter( 'pre_update_option_home', 'sudbury_force_https_url' );
