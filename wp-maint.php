<?php
/**
 * Unlock tool for disabling a manually initialed Maintenance window,  See The Sudbury Plugin's Network Admin for more info
 * @author Eddie Hurtig <hurtige@sudbury.ma.us>
 */
if ( isset( $_REQUEST['type'] ) && 'lockout' == $_REQUEST['type'] ) {
	$maint_file = dirname( __FILE__ ) . '/.lockout';
	$mode       = "Lockout";
} else {
	$maint_file = dirname( __FILE__ ) . '/.maintenance';
	$mode       = "Maintenance Mode";
}

if ( file_exists( $maint_file ) ) {
	include $maint_file;
	$user_stop_key = $_REQUEST['stop_key'];
	if ( isset( $stop_key ) && $stop_key === $user_stop_key ) {

		unlink( $maint_file );
		include dirname( __FILE__ ) . '/wp-load.php';

		wp_die( 'Congratulations! ' . $mode . ' has been successfully disabled.  <a href="/wp-admin/network/"> Click Here </a> To return to the Network Admin', 'Maintenance Mode Disabled' );
	} else {
		die( 'Invalid Stop Key' );
	}
} else {
	die( 'Not in ' . $mode );
}