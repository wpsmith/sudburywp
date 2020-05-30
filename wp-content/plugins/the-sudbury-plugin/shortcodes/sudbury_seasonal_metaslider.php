<?php
/**
 * shortcode to automatically change seasonal meteor slideshow according to the current season
 *
 * @author     Moe Finigan <moe@moegood.com>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Seasonal_MetaSlider {


	function __construct() {

		add_action( 'init', array( &$this, 'init' ) );
	}


	function init() {
		add_shortcode( 'sud_seasonal_metaslider', array( &$this, 'shortcode' ) );

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


		switch(true) {
    		case $today >= $spring && $today < $summer:
        		//echo 'It\'s Spring!';
				echo do_shortcode("[metaslider id=5764]");
        		break;

    		case $today >= $summer && $today < $fall:
        		//echo 'It\'s Summer!';
				echo do_shortcode("[metaslider id=5775]");
        		break;

    		case $today >= $fall && $today < $winter:
        		//echo 'It\'s Fall!';
				echo do_shortcode("[metaslider id=5752]");
        		break;

    		default:
        		//echo 'It must be Winter!';
				echo do_shortcode("[metaslider id=5785]");
		}
	}
}
new Sudbury_Seasonal_MetaSlider();
