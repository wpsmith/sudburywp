<?php
/**
 * Resolves old WebEditor Documents to their new Wordpress Locations
 *
 * This script is given 1 piece of information in the global scope: The WebEditor ID found in $id.
 *
 * To sent the user to a post set the global $destination to a WP_Post object or a Network_Post Object... We will figure the URL
 *
 * To send the user to a custom url set $destination to a (string) url use a root-relative ( /.... ) or an absolute url
 *
 * @author         Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package        Sudbury Back Compat
 * @subpackage     Resolver API
 */


$destination = network_query_posts( array_merge( $base_query_args, array(
	'post_type'   => 'attachment',
	'meta_key'    => 'sudbury_imported_webeditor_image_id',
	'meta_value'  => $id,
	'post_status' => 'any'
) ) );

if ( $destination ) {
	$destination = wp_get_attachment_url( $destination[0]->ID );
}


