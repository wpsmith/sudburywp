<?php
/**
 * Links the category Checkbox for flash-news to the sticky posts setting
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Sticky
 */


/**
 * This filter links the flash-news (Sticky) category to the post's Sticky Option
 *
 * @param $value
 *
 * @return array|mixed|void
 */
function sudbury_save_sticky_post( $value, $old ) {
	_sudbury_log( 'Old Sticky Posts: ' . implode( ', ', $old ) );
	// Use the category Flash News for Stickies not the Checkbox in the publish box because that is too hidden
	if ( ! doing_action( 'save_post' ) ) {
		_sudbury_log( 'New Sticky Posts: ' . implode( ', ', $value ) );

		_sudbury_log( '[info] Sticky Post System Called outside of a save_post action tree, lets say ignore to their updates, shall we?  Returning Old List of Stickies' );

		return $old;
	}


	if ( is_null( get_post() ) ) {
		_sudbury_log( '[info] Sticky Post System Quit because there was no post for get_the_ID()' );

		return $value;
	}
	$id        = get_the_ID();
	$in_sticky = in_array( get_the_ID(), $value );

	if ( ! has_category( 'flash-news' ) && $in_sticky ) {
		unset( $value[ array_search( $id, $value ) ] );
		_sudbury_log( '[info] Removed Sticky from post ' . $id . ' On ' . get_current_blog_id() );

	} elseif ( has_category( 'flash-news' ) && ! $in_sticky ) {
		$value[] = $id;
		_sudbury_log( '[info] Added Sticky from post ' . $id . ' On ' . get_current_blog_id() );
	}
	_sudbury_log( 'New Sticky Posts: ' . implode( ', ', $value ) );

	return $value;
}

add_filter( 'pre_update_option_sticky_posts', 'sudbury_save_sticky_post', 9999, 2 );

/**
 * Force an update of sticky posts on save_post
 */
add_action( 'save_post', function () {
	update_option( 'sticky_posts', get_option( 'sticky_posts' ) );
}, 999 );