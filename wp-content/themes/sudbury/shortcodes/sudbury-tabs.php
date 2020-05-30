<?php

/**
 * A shortcodes to render tabs
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Tabs {

	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}


	function init() {
		add_shortcode( 'tabs', array( &$this, 'tabs' ) );
		add_shortcode( 'tab', array( &$this, 'tab' ) );
		add_shortcode( 'panels', array( &$this, 'panels' ) );
		add_shortcode( 'panel', array( &$this, 'panel' ) );
	}


	function tabs( $atts = array(), $content = null ) {
		$defaults = array(
			'class' => 'tabs',
		);

		$atts = shortcode_atts( $defaults, $atts );

		$out = "<ul class=\"{$atts['class']}\">";
		$out .= do_shortcode( $content );
		$out .= "</ul>";
		$out .= "<div class=\"clear\">&nbsp;</div>";

		return $out;
	}

	function tab( $atts = array(), $content = null ) {
		$defaults = array(
			'class' => 'tab',
			'name'  => '',
			'slug'  => ''
		);

		$atts = shortcode_atts( $defaults, $atts );

		if ( empty( $atts['slug'] ) ) {
			$atts['slug'] = sanitize_key( $atts['name'] );
		}

		$out = "<li><a class=\"{$atts['class']}\" href=\"#{$atts['slug']}-panel\">";

		$out .= $atts['name'];
		$out .= "</a></li>";

		return $out;
	}

	function panels( $atts = array(), $content = null ) {
		$defaults = array(
			'class' => 'tab-panels'
		);

		$atts = shortcode_atts( $defaults, $atts );

		$out = "<div class=\"{$atts['class']}\">";
		$out .= do_shortcode( $content );
		$out .= "</div>";

		return $out;
	}

	function panel( $atts = array(), $content = null ) {
		$defaults = array(
			'class' => 'panel',
			'name'  => '',
			'slug'  => ''
		);

		$atts = shortcode_atts( $defaults, $atts );

		if ( ! $atts['slug'] ) {
			$atts['slug'] = sanitize_key( $atts['name'] );
		}

		$out = "<div id=\"{$atts['slug']}-panel\" class=\"{$atts['class']}\">";
		$out .= do_shortcode( $content );
		$out .= "</div>";

		return $out;
	}
}

new Sudbury_Tabs();
