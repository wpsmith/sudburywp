<?php

/**
 * Shortcode to list out links to all the committees' Meeting Pages
 * 
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class All_Meetings_Shortcode {

	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	function init() {
		add_shortcode( 'all-meetings', array( &$this, 'meetings_shortcode' ) );
	}

	function meetings_shortcode( $atts, $content ) {
		if ( ! $atts ) {
			$atts = array();
		}
		$atts = array_merge( array( 'cols' => 2 ), $atts );

		$html       = '<h2>Please Select a Committee</h2>';
		$committees = get_blogs( array( 'type' => 'committee' ) );
		$cols       = array_chunk( $committees, ceil( count( $committees ) / $atts['cols'] ) );
		foreach ( $cols as $committee_col ) {
			$html .= '<ul style="float:left;width:' . ( 100 / count( $cols ) ) . '%">';
			foreach ( $committee_col as $committee ) {
				if ( $committee['title'] == 'Committee Template' ) {
					continue;
				}
				$html .= sprintf( '<li style="margin:3px; line-height: 1.3"><a href="%s">%s</a></li>', $committee['url'] . '/meetings/?show_all_meetings', $committee['title'] );
			}
			$html .= '</ul>';
		}
		$html .= '<div style="clear:both;"></div>';

		return $html;
	}
}

new All_Meetings_Shortcode();