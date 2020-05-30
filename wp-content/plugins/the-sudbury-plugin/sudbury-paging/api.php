<?php
/**
 * The Api for the Sudbury Paging System
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Paging
 */


define( 'DOING_AJAX', true );
require_once '../../../../wp-load.php';

$blog_id = $_REQUEST['blog_id'];
$post_id = $_REQUEST['post_id'];

if ( $phone = network_get_post( $blog_id, $post_id ) ) {
	wp_verify_nonce( 'paging_send_message', 'paging_system' );

	if ( ! is_internal() ) {
		wp_send_json( array( 'error' => 'You aren\'t within the town network... this is an internal application only.' ) );
	}
	$service_providers = get_site_option( 'sudbury_service_providers', array() );

	$provider = $_REQUEST['service_provider'];

	$valid = false;
	foreach ( $service_providers as $service_provider ) {
		if ( $service_provider['_sp_name'] == $provider ) {
			$valid    = true;
			$provider = $service_provider;
			break;
		}
	}

	if ( ! $valid ) {
		wp_send_json( array( 'error' => 'There is no service provider registered with name: ' . $provider ) );
	}
	$message = $_REQUEST['message'];
	$number  = network_get_post_meta( $phone, '_phone_number', true, $blog_id );
	$number  = preg_replace( '/[^0-9]/', '', $number );
	$address = sprintf( $provider['_sp_email_address_format'], $number );

	if ( $provider['_sp_max_chars'] < strlen( $message ) ) {
		wp_send_json( array( 'error' => "Too Many Characters! Max is {$provider['_sp_max_chars']}: " . $provider ) );
	} elseif ( 0 === strlen( $message ) ) {
		wp_send_json( array( 'error' => "Empty Message: Please type a message" ) );
	}

	if ( is_user_logged_in() ) {
		$from = wp_get_current_user()->user_email;
	} else {
		$from = 'pager@sudbury . ma . us'; // Not a Real Email
	}
	if ( wp_mail( $address, 'Sudbury Paging System', $message, array( 'From: ' . $from ) ) ) {
		wp_send_json( array( 'message' => 'Your Message was sent! to ' . $address ) );
	} else {
		wp_send_json( array( 'error' => "Internal Mailing Error. Check Email Server Connection" ) );
	}


} else {
	wp_send_json( array( 'error' => 'That Phone Does not Exist in the Database ... We can\'t send to any phone that isn\'t in the database' ) );
}
