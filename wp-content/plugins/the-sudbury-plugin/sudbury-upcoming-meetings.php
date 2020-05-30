<?php

/**
 * http://sudbury.ma.us/committees/upcoming_meetings.asp?location=townhall&place=Town Hall
 * Replicates some legacy functionality that rendered Meetings for a specific location in a printable HTML page with some
 * High Quality clipart and what not
 *
 * Links the legacy Election Application into the WordPress URL Structure.
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Meetings
 */
class Sudbury_Upcoming_Meetings {
	var $main_location;

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'make_endpoint' ) );
		add_action( 'template_redirect', array( &$this, 'template_redirect' ) );
	}

	/**
	 * Register /elections and /election
	 */
	function make_endpoint() {
		add_rewrite_endpoint( 'upcoming-meetings', EP_ROOT );
	}

	/**
	 * Show the election stuff if the request was for elections
	 */
	function template_redirect() {
		global $wp_query;

		// if this is not a request for json or a singular object then bail
		if ( ! isset( $wp_query->query['upcoming-meetings'] ) ) {
			return;
		}

		$location = $wp_query->query['upcoming-meetings'];

		$this->events = sudbury_meetings_in_location_this_week( $location );


		$this->render_single( $location );

		exit;

	}

	/**
	 * Render Single Election
	 */
	function render_single( $id ) {
		$this->main_location = sudbury_get_location( $id );
		if ( ! $this->main_location ) {
			include get_404_template();
			die();
		}



		get_template_part( 'upcoming', 'meetings' );
	}
}

/**
 * Global Sudbury_Elections Object
 */
$upcoming = new Sudbury_Upcoming_Meetings();
