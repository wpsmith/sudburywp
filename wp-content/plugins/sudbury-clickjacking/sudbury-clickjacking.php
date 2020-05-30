<?php
/*
Plugin Name: Sudbury ClickJacking Plugin
Plugin URI: http://sudbury.ma.us/
Description: Prevents Malicious ClickJacking Attacks
Version: 1.0
Author: Eddie Hurtig
Author URI: http://hurtigtechnologies.com
*/

/**
 * Adds the CSS and JS to prevent Frame Based ClickJacking
 */
function sudbury_clickjacking_head() { ?>
	<style id="sud-antiClickjack">body {
			display: none !important;
		}</style>
	<script type="text/javascript">
		if (self === top) {
			var antiClickjack = document.getElementById("sud-antiClickjack");
			antiClickjack.parentNode.removeChild(antiClickjack);
		} else {
			top.location = self.location;
		}
	</script>

<?php }

add_action( 'wp_head', 'sudbury_clickjacking_head' );