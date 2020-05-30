<?php

/**
 * Simple shortcode to show the site's search form in a post
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Iframe_ShortCode {

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
		add_shortcode( 'iframe', array( &$this, 'shortcode' ) );
	}

	/**
	 * A Simple shortcode that returns the site's search form
	 */
	function shortcode($atts) {
		$atts = array_merge($atts, ['','','']);

		return "<iframe src=\"{$atts[0]}\" width=\"$atts[1]\" height=\"$atts[2]\"></iframe>";
	}


}

new Sudbury_Iframe_ShortCode();
