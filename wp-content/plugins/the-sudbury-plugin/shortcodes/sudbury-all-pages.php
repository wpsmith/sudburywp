<?php

/**
 * Shortcode that displays a list of all custom pages
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class All_Pages_Shortcode {
	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	function init() {
		add_shortcode( 'all-pages', array( &$this, 'shortcode' ) );
	}

	function shortcode( $atts, $content = null ) {

		$defaults = array( 'echo' => false );

		$args = array_merge( $defaults, $atts );

		return wp_list_pages( $args );
	}
}

$sudbury_all_pages_shortcode = new All_Pages_Shortcode();
