<?php

/**
 * Puts an ajax contact search on the page
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Contact_Ajax_Search_Shortcode {

	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	function init() {
		add_shortcode( 'ajax-contact-search', array( &$this, 'shortcode' ) );
	}

	function shortcode() {
		ob_start();

		?>

		<div id="TopContactForm" class="contact cornerButton" style="">
			<b>Contact <img src="/lib/icons/group.png" alt="Staff &amp; Committee Members Contact Search" class="icon"></b>
			<ul style="display: none; ">
				<li><h3>Find Staff and Committee contacts</h3></li>
				<li>Search:
					<input type="text" class="searchStaff ajax" style="width:250px;" placeholder="Search Staff" /> or
					<select id="DeptSelect">
						<option value="">Getting Departments &amp; Committees</option>
					</select>
				</li>
				<li>
					<div id="staffResults" style="float:left;"></div>
					<div id="memberResults" style="margin-left:25px;float:left;"></div>
					<div id="deptResults" style="margin-left:25px;float:left;"></div>
				</li>
			</ul>
		</div>

		<?php
		return ob_get_clean();
	}
}

new Contact_Ajax_Search_Shortcode();