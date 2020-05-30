<?php


if ( ! function_exists( 'sudbury_restore_blogs' ) ) {
	function sudbury_restore_blogs() {
		foreach ( array_reverse( $GLOBALS['sudbury_loops'] ) as $id ) {
			d( "Reset -> $id" );
			restore_current_blog();

			if ( get_current_blog_id() != $id ) {
				sudbury_log( 'Invalid Loop rollback: Expected ' . $id . ' got ' . get_current_blog_id() );
			}
		};
		$GLOBALS['sudbury_loops'] = array();
	}
}