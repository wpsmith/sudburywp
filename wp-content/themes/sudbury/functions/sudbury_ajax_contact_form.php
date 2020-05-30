<?php

class Sudbury_AJAX_Contact_Form {
	private $hook;

	function __construct() {
		add_action( 'wp_enqueue_scripts', array( &$this, "enqueue_scripts" ) );
		add_action( 'admin_ajax_nopriv_query_search_contacts', array( &$this, 'search' ) );
	}

	function enqueue_scripts() {
	}


	function form( $args = array() ) {
		$html = '<form class="sub-menu sudbury-ajax sudbury-ajax-contact-search-form" action="' . esc_attr( admin_url( "admin-ajax.php?action=search-contact" ) ) . '">

                    <input type="search" class="sudbury-ajax-search-input" name="sudbury_search_term" placeholder="Search for an employee" />
                    <input type="submit" class="sudbury-ajax-search-submit" value="search" />

                 </form>
                 <div class="results-container">
                    <div class="staff-results"></div>
                    <div class="member-results"></div>
                 </div>';

		return $html;
	}

	function search() {
		$personnel = get_site_option( 'sudbury_fm_all_personnel' );
	}
}