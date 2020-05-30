<?php
/**
 * Resolves old WebEditor FAQs to their new Wordpress Locations
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
	'meta_query'  => array(
		array(
			'key'     => 'sudbury_imported_webeditor_id',
			'value'   => $id,
			'compare' => '=',
		),

	),
	'post_type'   => 'faq',
	'post_status' => 'any',
) ) );



