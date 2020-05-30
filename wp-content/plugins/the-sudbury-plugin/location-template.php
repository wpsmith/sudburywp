<?php
/**
 * Template functions for Location Posts (location_the_address() ect.)
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Locations
 */

/**
 * Determines if the Given Location ID (wp_em_locations) is a valid Location in the wp_em_locations table
 *
 * @param int $location_id The Location ID to check
 *
 * @return bool Whether the given $location_id is a valid location
 */
function location_exists( $location_id ) {
	global $wpdb;
	$sql = $wpdb->prepare( "SELECT location_id FROM {$wpdb->base_prefix}em_locations WHERE location_id = %d", $location_id );

	$result = $wpdb->get_results( $sql );


	if ( count( $result ) > 1 ) {
		// just a little bit of error checking, not sure if this case is even possible
		sudbury_log( '[Wordpress Database Error] There is are multiple Locations with ID ' . $location_id . ' Triggered By ' . $sql . ' In is_location() ' . __FILE__ );

		return false;
	} elseif ( count( $result ) == 1 ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Determines if the given post ID is a valid Location Post
 *
 * @param $post_id
 *
 * @return bool
 */
function is_location_post( $post_id ) {
	return 'location' == get_post_type( $post_id );
}

/**
 * The current $global_location object
 */
$global_location = null;
/**
 * The previous post object, if setup_locationdata was called with $loc_post = true
 */
$global_tmppost = null;

if ( ! function_exists( 'setup_locationdata' ) ) : /**
 * Sets up all the location_get|the_field() functions with the given Location... If $loc_post is specified then this will
 * set all the WordPress Template Tags to use $loc_post data too
 *
 * @param              $location
 * @param bool|WP_Post $loc_post
 */ {
	function setup_locationdata( $location, $loc_post = false ) {
		global $global_location;
		$global_location = $location;
		if ( false !== $loc_post ) {
			global $post;
			global $global_tmppost;
			$global_tmppost = $post;
			$post           = $loc_post;
		}
	}
}
endif;

if ( ! function_exists( 'release_locationdata' ) ) : /**
 * Releases the Location and restores the Loop back to it's original state
 */ {
	function release_locationdata() {
		global $global_location;
		global $global_tmppost;
		global $post;

		if ( $global_location ) {
			$global_location = null;
		}

		if ( $global_tmppost ) {
			$post           = $global_tmppost;
			$global_tmppost = null;
		}
	}
}
endif;


if ( ! function_exists( 'location_the_location_id' ) ) : /**
 * Prints the Location's ID
 */ {
	function location_the_location_id() {
		global $global_location;
		echo esc_html( $global_location['location_id'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_location_id' ) ) : /**
 * Prints the Location's Blog ID
 */ {
	function location_get_the_location_id() {
		global $global_location;

		return $global_location['location_id'];
	}
}
endif;

if ( ! function_exists( 'location_the_blog_id' ) ) : /**
 * Prints the Location's Blog ID
 */ {
	function location_the_blog_id() {
		global $global_location;
		echo esc_html( $global_location['blog_id'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_blog_id' ) ) : /**
 * Prints the Location's Blog ID
 */ {
	function location_get_the_blog_id() {
		global $global_location;

		return $global_location['blog_id'];
	}
}
endif;

if ( ! function_exists( 'location_the_post_id' ) ) : /**
 * Prints the Location's Post ID
 */ {
	function location_the_post_id() {
		global $global_location;
		echo esc_html( $global_location['post_id'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_post_id' ) ) : /**
 * Prints the Location's Post ID
 */ {
	function location_get_the_post_id() {
		global $global_location;

		return $global_location['post_id'];
	}
}
endif;


if ( ! function_exists( 'location_the_address' ) ) : /**
 * Prints the Location's Address
 */ {
	function location_the_address() {
		global $global_location;
		echo esc_html( $global_location['location_address'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_address' ) ) : /**
 * Returns the Location's Address
 * @return string The Location's Address
 */ {
	function location_get_the_address() {
		global $global_location;

		return $global_location['location_address'];
	}
}
endif;

if ( ! function_exists( 'location_the_town' ) ) : /**
 * Prints the Location's Town
 */ {
	function location_the_town() {
		global $global_location;
		echo esc_html( $global_location['location_town'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_town' ) ) : /**
 * Return's The Location's Town
 * @return string The Location's Town
 */ {
	function location_get_the_town() {
		global $global_location;

		return $global_location['location_town'];
	}
}
endif;

if ( ! function_exists( 'location_the_state' ) ) : /**
 * Prints the Location's State
 */ {
	function location_the_state() {
		global $global_location;
		echo esc_html( $global_location['location_state'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_state' ) ) : /**
 * Return's the Location's State
 * @return string The Location's State
 */ {
	function location_get_the_state() {
		global $global_location;

		return $global_location['location_state'];
	}
}
endif;

if ( ! function_exists( 'location_the_permalink' ) ) : /**
 * Prints the Location Post's Permalink
 */ {
	function location_the_permalink() {
		global $global_location;
		echo location_get_the_permalink();
	}
}
endif;

if ( ! function_exists( 'location_get_the_permalink' ) ) : /**
 * Returns the Location Post's Permalink
 * @return string the Location Post's Permalink
 */ {
	function location_get_the_permalink() {
		global $global_location;
		switch_to_blog( $global_location['blog_id'] );
		$link = get_permalink( $global_location['post_id'] );
		restore_current_blog();

		return $link;
	}
}
endif;

if ( ! function_exists( 'location_the_country' ) ) : /**
 * Prints the Location's Country Code
 */ {
	function location_the_country() {
		global $global_location;
		echo esc_html( $global_location['location_country'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_country' ) ) : /**
 * Returns the Location's Country Code
 * @return string the Location's Country Code
 */ {
	function location_get_the_country() {
		global $global_location;

		return $global_location['location_country'];
	}
}
endif;

if ( ! function_exists( 'location_the_postcode' ) ) : /**
 * Prints the Location's Postal Code (Zipcode)
 */ {
	function location_the_postcode() {
		global $global_location;
		echo esc_html( $global_location['location_postcode'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_postcode' ) ) : /**
 * Returns the Location's Postal Code (Zipcode)
 * @return string The Location's Postal Code (Zipcode)
 */ {
	function location_get_the_postcode() {
		global $global_location;

		return $global_location['location_postcode'];
	}
}
endif;

if ( ! function_exists( 'location_the_latitude' ) ) : /**
 * Prints the Location's Latitude
 */ {
	function location_the_latitude() {
		global $global_location;
		echo esc_html( $global_location['location_latitude'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_latitude' ) ) : /**
 * Returns the Location's Latitude
 * @return string The Location's Latitude
 */ {
	function location_get_the_latitude() {
		global $global_location;

		return $global_location['location_latitude'];
	}
}
endif;

if ( ! function_exists( 'location_the_longitude' ) ) : /**
 * Prints the Location's Longitude
 */ {
	function location_the_longitude() {
		global $global_location;
		echo esc_html( $global_location['location_longitude'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_longitude' ) ) : /**
 * Returns the Location's Longitude
 * @return string The Location's Longitude
 */ {
	function location_get_the_longitude() {
		global $global_location;

		return $global_location['location_longitude'];
	}
}
endif;


if ( ! function_exists( 'location_the_region' ) ) : /**
 * Prints the Location's Region
 */ {
	function location_the_region() {
		global $global_location;
		echo esc_html( $global_location['location_region'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_region' ) ) : /**
 * Gets the Location's Region
 * @return string The Location's Region
 */ {
	function location_get_the_region() {
		global $global_location;

		return $global_location['location_region'];
	}
}
endif;

if ( ! function_exists( 'location_the_name' ) ) : /**
 * Prints the Location's Name
 */ {
	function location_the_name() {
		global $global_location;
		echo esc_html( $global_location['location_name'] );
	}
}
endif;

if ( ! function_exists( 'location_get_the_name' ) ) : /**
 * Returns the Location's Name
 * @return string The Location's Name
 */ {
	function location_get_the_name() {
		global $global_location;

		return $global_location['location_name'];
	}
}
endif;
