<?php
/**
 * This File Includes all the widgets for the Sudbury Plugin and Theme
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Widgets
 */

require_once( "sudbury-services-widget.php" );
require_once( "sudbury-description-widget.php" );
require_once( "sudbury-dept-head-widget.php" );
require_once( "sudbury-contact-info-widget.php" );
require_once( "spotlight-news-widget.php" );
require_once( "sudbury-related-departments.php" );

/**
 * Registers any widgets that should be globally accessible regardless of theme
 */
function sudbury_register_widgets() {
	register_widget( 'Sudbury_Services' );
	register_widget( 'Sudbury_Description_Paragraph' );
	register_widget( 'Sudbury_Department_head' );
	register_widget( 'Sudbury_Contact_Info' );
	register_widget( 'Spotlight_News_Widget' );
	register_widget( 'Related_Departments_Widget' );
}

add_action( 'widgets_init', 'sudbury_register_widgets', 1 );

function sudbury_event_widget_title( $title ) {
	if ( false !== strpos( strtolower( $title ), 'events' ) ) {
		return $title . ' <a href="/calendar" class="link-black" title="Click for Full Town Calendar"><i class="fa fa-calendar"></i></a>';
	}

	return $title;
}

add_filter( 'widget_title', 'sudbury_event_widget_title' );


function sudbury_event_title_strip( $replace, $event, $full_result ) {

	if ( 'meeting' == $event->post_type ) {
		if ( '#_EVENTNAME' == $full_result ) {
			$replace = explode( ':', $replace )[0];
		} elseif ( '#_EVENTLINK' == $full_result ) {
			$replace = preg_replace( '/Meeting:[^\<\"]*/', 'Meeting', $replace );
		}
	}

	return $replace;
}

add_filter( 'em_event_output_placeholder', 'sudbury_event_title_strip', 10, 3 );