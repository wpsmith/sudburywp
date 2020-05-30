<?php
/**
 * Resolves old WebEditor FAQs to their new Wordpress Locations
 *
 * This script is given 1 piece of information in the global scope: The WebEditor ID found in $bid.
 *
 * To sent the user to a post set the global $destination to a WP_Post object or a Network_Post Object... We will figure the URL
 *
 * To send the user to a custom url set $destination to a (string) url use a root-relative ( /.... ) or an absolute url
 *
 * @author         Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package        Sudbury Back Compat
 * @subpackage     Resolver API
 */


$path = apply_filters( 'sudbury_resolve_to_wordpress_name', $id );

$bid = sudbury_get_blog_info_by_option( 'siteurl', 'https://sudbury.ma.us/' . $path )->blog_id;
if ( ! $bid ) {
	$bid = sudbury_get_blog_info_by_option( 'siteurl', 'https://beta.sudbury.ma.us/' . $path )->blog_id;
}


if ( false === $bid ) {
	$destination = false;

	return;
}
switch_to_blog( intval( $bid ) );
$pages = get_posts( array(
	'post_type'   => 'page',
	'post_status' => array(
		'publish',
		'future',
		'private',
		'new',
		'draft',
		'auto-draft',
		'pending'
	),
	'meta_key'    => 'sudbury_department_head_message'
) );

if ( ! $pages ) {
	$destination = site_url();
} else {
	$destination = get_post_permalink( $page_id = $pages[0]->ID );
}

restore_current_blog();
