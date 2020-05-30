<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name: Bring Back the Get Shortlink Button
 * Plugin URI:  https://wordpress.org/plugins/bring-back-the-get-shortlink-button/
 * Description: This plugin brings back the Get Shortlink button, which is hidden by default since WordPress 4.4.
 * Author:      Thorsten Frommen
 * Author URI:  http://tfrommen.de
 * Version:     1.1.0
 * Text Domain: bring-back-the-get-shortlink-button
 * License:     GPLv3
 */

if ( ! function_exists( 'add_filter' ) ) {
	return;
}

add_filter( 'get_shortlink', function ( $shortlink ) {

	return $shortlink;
} );
