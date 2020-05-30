<?php
class Sudbury_Shared_Posts_Extra {

	function __construct() {
		add_filter( 'map_meta_cap', array(&$this, 'map_meta_cap'), 10, 4 );
	}

	/**
	 * Disable editing of guest posts... entirely!
	 */
	function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( isset( $args[0] ) ) {
			$post_id = $args[0];

			if ( 'edit_post' == $cap ) {
				if ( sudbury_sharing_is_guest_post( $post_id ) ) {
					$caps[] = 'do_not_allow';
				}
			}
		}
		/* Return the capabilities */
		return $caps;
	}
}


new Sudbury_Shared_Posts_Extra();
