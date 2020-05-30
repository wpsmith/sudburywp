<?php
/*
Plugin Name: Maintenance Error Reporting
Plugin URI: http://cdn.hurtigtechnologies.com/wordpress/plugins/maint-error
Description: Provides a clean error page to users when wordpress can't establish a Database Connection and also notifies you by email instantly
Version: 0.5
Author: Eddie Hurtig
Author URI: http://hurtigtechnologies.com

Copyright 2013 Eddie Hurtig, Hurtig Technologies (http://hurtigtechnologies.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
if ( ! defined( 'HT_ERROR_REPORT_INTERVAL' ) ) {
	define( 'HT_ERROR_REPORT_INTERVAL', 60 );
} // in seconds

header( 'HTTP/1.1 404 Not Found' );
header( 'Status: 404 Not Found' );
header( 'Retry-After: 60' ); // 1 hour = 3600 seconds

$file = false;
$time = 0;
$data = array();
require_once '../wp-load.php';
_sudbury_log( '[404] "' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '" ' . $_SERVER['REMOTE_ADDR'], array(
	'destination' => WP_CONTENT_DIR . '/404.log'
) );
// This JSON File contains the timestamp of when we last sent out an email to wordpress_emegency@sudbury.ma.us for an error like this
if ( file_exists( __DIR__ . DIRECTORY_SEPARATOR . 'last_error_report.json' ) ) {
	$file = file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . 'last_error_report.json' );
	if ( is_array( $json = json_decode( $file, true ) ) ) {
		$data = $json;
	}
	if ( isset( $data['timestamp-maintenance'] ) && is_numeric( $data['timestamp-maintenance'] ) ) {
		$time = intval( $data['timestamp-maintenance'] );
	}
}
if ( time() - $time > HT_ERROR_REPORT_INTERVAL ) {
	// Write the new Last error report file with the current timestamp
	file_put_contents( __DIR__ . DIRECTORY_SEPARATOR . 'last_error_report.json', json_encode( array_merge( $data, array( 'timestamp-maintenance' => time() ) ) ) );
	// Send an emergency email
	$subject = "Unexpected 404";
	$body    = "Request generated a 404 error\r\n\r\n";
	$body .= "Timestamp: " . date( 'Y-m-d H:i:s' ) . "\r\n\r\n";
	$body .= "A User attempted to get to page: '" . $_SERVER['REQUEST_URI'] . "' and was presented with a 404 page";
	mail( 'wordpress_logs@sudbury.ma.us', $subject, $body, 'From: WordPress <wordpress@sudbury.ma.us>' );
}
// Render the Offline page
$GLOBALS['maintenance_message'] = "File Doesn't exist. We have been notified of this problem.";
$GLOBALS['heading']             = "404: File Not Found";
$GLOBALS['subheading']          = "";
$base_url                       = "";
include_once( "sudbury-offline.php" );
