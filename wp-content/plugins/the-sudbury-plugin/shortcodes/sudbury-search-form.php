<?php

/**
 * Simple shortcode to show the site's search form in a post
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Search_Form_ShortCode {

	/**
	 * Hook the init action
	*/
	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * Register the 'search-form' shortcode
	 */
	function init() {
		add_shortcode( 'search-form', array( &$this, 'shortcode' ) );
	}

	/**
	 * A Simple shortcode that returns the site's search form
	 */
	function shortcode() {
		return get_search_form();
	}


}

new Sudbury_Search_Form_ShortCode();