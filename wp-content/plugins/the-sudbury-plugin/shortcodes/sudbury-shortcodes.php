<?php
/**
 * This file imports all the shortcodes and adds any extra shortcode functionality.\
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Sudbury_Plugin
 */

// Shortcode Loader.  Loads all Files in this directory.  To disable a file from loading either:
// 1. Change the file ext to '.php.exclude' or
// 2. Return false in the 'sudbury_load_file' filter
foreach ( glob( plugin_dir_path( __FILE__ ) . '*.php' ) as $file ) {
	if ( ! strendswith( '/shortcodes.php', $file ) && apply_filters( 'sudbury_load_file', true, $file ) ) {
		require_once $file;
	}
}

