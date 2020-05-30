<?php
/*
Plugin Name: DB Error Reporting
Plugin URI: http://cdn.hurtigtechnologies.com/wordpress/plugins/db-error
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

header( 'HTTP/1.1 503 Service Temporarily Unavailable' );
header( 'Status: 503 Service Temporarily Unavailable' );
header( 'Retry-After: 60' ); // 1 hour = 3600 seconds

$file = false;
$time = 0;
$error_report_file = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'last_error_report.json';
if ( file_exists( $error_report_file ) ) {
	$file = file_get_contents( $error_report_file );
	if ( is_numeric( $file ) ) {
		$time = intval( $file );
	}
}

if ( time() - $time > HT_ERROR_REPORT_INTERVAL ) {
	$errors = '';
	if ( file_exists( $error_report_file ) ) {
		$errors = json_decode( file_get_contents( __DIR__ . DIRECTORY_SEPARATOR . 'last_error_report.json' ), true );
	}
	if ( ! is_array( $errors ) ) {
		$errors = array();
	}

	file_put_contents( $error_report_file, json_encode( array_merge( $errors, array( 'db-error-timestamp' => time() ) ) ) );

	$subject = "Database Error";
	$body    = "There is a problem with the database!\r\n\r\n";
	$body .= "Timestamp: " . date( 'Y-m-d H:i:s' ) . "\r\n\r\n";
	$body .= "A User attempted to get to page: '" . $_SERVER['REQUEST_URI'] . "' and was presented with a temporarily offline page\r\n\r\n";
	$body .= "Attempting to connect to database: '" . DB_NAME . "' via '" . DB_USER . "@" . DB_HOST . "'... Check password in wp-config.php\r\n\r\n";
	$body .= "Please check the MySQL Host indicated above and potentially restart the MySQL server on that host by running 'Restart-Service MySQL56' in an elevated PowerShell Command Line\r\n\r\n";
	$body .= "Good Luck!\r\n Eddie Hurtig's DB-Error Script for WordPress \r\n\r\n PS: In case of serious problems call me at (978) 505 - 5610";
	mail( 'wordpress_emergency@sudbury.ma.us', $subject, $body, 'From: WordPress <wordpress@sudbury.ma.us>' );

	mail( "wordpress_emergency@sudbury.ma.us", "Database Error", "There is a problem with the database!\r\n\r\nTimestamp: " . date( 'Y-m-d H:i:s' ) . "\r\n\r\nAttempting to connect to database: '" . DB_NAME . "' via '" . DB_USER . "@" . DB_HOST . "'... Check password in wp-config.php", "From: Wordpress Error Reporting" );
}
error_log( '[emergency] Database Error' );
$GLOBALS['maintenance_message'] = 'MySQL Database is currently unavailable';
$base_url                       = "";
include_once( "sudbury-offline.php" );
