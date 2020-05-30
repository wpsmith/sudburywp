<?php

/**
 * Links the legacy Election Application into the WordPress URL Structure.
 *
 * @author Eddie Hurtig <hurtige@sudbury.ma.us>
 *
 */
class Sudbury_Elections {
	public $election_id;

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'make_election_endpoint' ) );
		add_action( 'template_redirect', array( &$this, 'template_redirect' ) );
	}

	/**
	 * Register /elections and /election
	 */
	function make_election_endpoint() {
		add_rewrite_endpoint( 'election', EP_ROOT );
		add_rewrite_endpoint( 'elections', EP_ROOT );
	}

	/**
	 * Show the election stuff if the request was for elections
	 */
	function template_redirect() {
		global $wp_query;
		if ( isset( $wp_query->query['election'] ) ) {
			$wp_query->query['elections'] = $wp_query->query['election'];
		}
		// if this is not a request for json or a singular object then bail
		if ( ! isset( $wp_query->query['elections'] ) && ! isset( $wp_query->query['election'] ) ) {
			return;
		}
		if ( is_numeric( $wp_query->query['elections'] ) ) {
			$id = trim( $wp_query->query['elections'], '/' );
			$this->render_single( $id );
		} else {
			$this->render_archive();
		}
		exit;

	}

	/**
	 * Render Single Election
	 */
	function render_single( $id ) {
		$this->election_id = (int)$id;

		get_template_part( 'single', 'election' );
	}

	/**
	 * Render Election List
	 */
	function render_archive() {
		get_template_part( 'archive', 'election' );
	}
}

/**
 * Global Sudbury_Elections Object
 */
$sudbury_elections = new Sudbury_Elections();
