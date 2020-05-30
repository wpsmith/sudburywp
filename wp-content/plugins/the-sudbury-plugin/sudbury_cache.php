<?php
/**
 * Created by PhpStorm.
 * User: hurtige
 * Date: 12/15/2014
 * Time: 3:34 PM
 */

/**
 * Flushes the cached versions of the department and network homepages when a post is added
 *
 * @param $post_id
 */
function sudbury_flush_front_pages( $post_id ) {
	if ( has_term( 'front-page-news', 'category', $post_id ) ) {
		if ( ! w3tc_pgcache_flush_url( 'https://sudbury.ma.us/' ) ) {
			_sudbury_log( '[ERROR] Failed to clear cached version of `https://sudbury.ma.us`' );
		} else {
			_sudbury_log( '[SUCCESS] Cleared cached version of `https://sudbury.ma.us`' );
		}
	}

	if ( ! w3tc_pgcache_flush_url( $url = site_url() ) ) {
		_sudbury_log( "Failed to clear cached version of `$url``" );
	} else {
		_sudbury_log( "[SUCCESS] Cleared cached version of `$url``" );
	}
}

add_action( 'save_post', 'sudbury_flush_front_pages' );