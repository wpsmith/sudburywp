<?php
/**
 * shortcode to automatically change seasonal meteor slideshow according to the current season
 *
 * @author     Moe Finigan <moe@moegood.com>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Seasonal_Photos {


	function __construct() {

		add_action( 'init', array( &$this, 'init' ) );
	}


	function init() {
		add_shortcode( 'sud_seasonal_photos', array( &$this, 'shortcode' ) );

	}


	function shortcode(  ) {

	// get today's date
	$today = new DateTime();
	//echo 'Today is: ' . $today->format('m-d-Y') . '<br />';

	// get the season dates
	$spring = new DateTime('March 21');
	$summer = new DateTime('June 21');
	$fall = new DateTime('September 22');
	$winter = new DateTime('December 21');

	if ( function_exists( 'meteor_slideshow' ) ) { 

		switch(true) {
    		case $today >= $spring && $today < $summer:
        		//echo 'It\'s Spring!';
				meteor_slideshow( "sudbury-spring-sidebar", "random: 1" );
        		break;

    		case $today >= $summer && $today < $fall:
        		//echo 'It\'s Summer!';
				meteor_slideshow( "sudbury-summer-sidebar", "random: 1" );
        		break;

    		case $today >= $fall && $today < $winter:
        		//echo 'It\'s Fall!';
				meteor_slideshow( "sudbury-fall-sidebar", "random: 1" );
        		break;

    		default:
        		//echo 'It must be Winter!';
				meteor_slideshow( "sudbury-winter-sidebar", "random: 1" );
		}
	}
	}
}
new Sudbury_Seasonal_Photos();
