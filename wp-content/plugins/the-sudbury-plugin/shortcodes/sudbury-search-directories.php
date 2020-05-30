<?php

/**
 * A shortcode to generate a telephone directory of all departments
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Search_Directories {

	function __construct() {

		add_action( 'init', array( &$this, 'init' ) );
	}


	function init() {
		add_shortcode( 'search-directories', array( &$this, 'shortcode' ) );
	}


	function shortcode( $atts = array(), $content = null ) {
		$defaults = array();

		$atts = shortcode_atts( $defaults, $atts );

		ob_start();
		// End Setup
		?>
		<form class="contact-search">
			<div class="input-group mb-3">
				<input id="term" name="term" type="text" class="form-control" placeholder="Search term" aria-label="Search term" aria-describedby="basic-addon2">
				<div class="input-group-append">
					<input class="btn btn-primary" type="submit" value="search" />
				</div>
			</div>

		</form>

		<div class="tablecap">
			<div class="cap">Locations</div>
			<table class="result-table" id="location-results">
				<tr>
					<td>Please enter a search term...</td>
				</tr>
			</table>
			<div class="foot"></div>
		</div>

		<div class="tablecap">
			<div class="cap">Departments</div>
			<table class="result-table" id="department-results">
				<tr>
					<td>Please enter a search term...</td>
				</tr>
			</table>
			<div class="foot"></div>
		</div>

		<div class="tablecap">
			<div class="cap">Committees</div>
			<table class="result-table" id="committee-results">
				<tr>
					<td>Please enter a search term...</td>
				</tr>
			</table>
			<div class="foot"></div>
		</div>


		<div class="tablecap">
			<div class="cap">Town Staff</div>
			<table class="result-table" id="staff-results">
				<tr>
					<td>Please enter a search term...</td>
				</tr>
			</table>
			<div class="foot"></div>
		</div>


		<div class="tablecap">
			<div class="cap">Board Members</div>
			<table class="result-table" id="member-results">
				<tr>
					<td>Please enter a search term...</td>
				</tr>
			</table>
			<div class="foot"></div>
		</div>

		<?php

		$html = ob_get_clean();


		return $html;
	}
}

new Sudbury_Search_Directories();

