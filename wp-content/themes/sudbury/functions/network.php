<?php

if ( ! function_exists( 'get_all_sites' ) ) {

	function get_all_sites() {

		global $wpdb;
		$multisite = array();
		// Query all blogs from multi-site install
		$blogs = $wpdb->get_results( "SELECT blog_id,domain,path FROM wp_blogs WHERE blog_id not = '1' ORDER BY path" );

		// Get primary blog
		$blogname     = $wpdb->get_row( "SELECT option_value FROM wp_options WHERE option_name='blogname' " );
		$multisite[1] = $blogname->option_value;

		// For each blog search for blog name in respective options table
		foreach ( $blogs as $blog ) {
			// Get rest of the sites
			$blogname = $wpdb->get_results( $wpdb->prepare( "SELECT option_value FROM `{$wpdb->prefix}%d_options` WHERE option_name='blogname' ", $blog->blog_id ) );
			foreach ( $blogname as $name ) {
				$multisite[$blog->blog_id] = $name->option_value;
			}
		}

		return $multisite;
	}
}

function get_submenu( $sites, $output, $item, $depth, $args ) {
	if ( $sites ) {
		$output .= '<ul class="sub-menu"><li><ul>';
		foreach ( $sites as $id => $site ) {
			$output .= esc_html( get_submenu_item( $site, $id, $output, $item, $depth, $args ) );
		}
		$output .= '</ul></li></ul>';
	}

	return $output;
}

function get_submenu_item( $site, $id, $output, $item, $depth, $args, $sublink = false ) {
	$output .= $args->before . '<li class="' . ( $sublink ? 'sublink' : '' ) . '"><a href="' . esc_url( $site['url'] ) . '" title="' . esc_attr( $site['title'] ) . '">';
	$output .= $args->link_before . apply_filters( 'the_title', $site['title'], $id ) . $args->link_after . '</a></li>';
	$output .= $args->after;
	foreach ( $site['children'] as $id => $child ) {
		$output .= get_submenu_item( $child, $id, $output, $item, $depth, $args, true );
	}

	return $output;
}