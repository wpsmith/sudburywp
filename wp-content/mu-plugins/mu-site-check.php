<?php
/**
 * Plugin Name: Prevent Archived/Deleted blogs warning in Multisite
 * Plugin Url: https://wpsmith.net
 * Version: 1.0
 * Author: Travis Smith <t@wpsmith.net>
 * Author URI: https://wpsmith.net
 */

add_filter(
	'ms_site_check',
	function () {
		// Super admins should be able to see it
		if ( current_user_can( 'manage_network' ) ) {
			return;
		}

		$blog = get_blog_details();
		if ( '1' == $blog->deleted || '2' == $blog->deleted || '1' == $blog->archived || '1' == $blog->spam ) {
			wp_die( get_sudbury_contact_admin_message( 'This Site has been Deactivated. <br><br>If you believe this is an error' ) );
//			wp_redirect( network_site_url() );
//			die();
		}
	}
);