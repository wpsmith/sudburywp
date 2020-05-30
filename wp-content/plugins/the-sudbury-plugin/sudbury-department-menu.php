<?php
/**
 * Defines a menu that automatically adds pages to itself.  Pages that get automatically added must be classified with
 * a page template starting with the text "Special -" in order to keep regular pages from being added to this very
 * exclusive menu.
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Menus
 */


/**
 * Return the term id for the department tabs menu
 * @return int The term ID of the department tabs menu
 */
function sudbury_get_dept_tabs_menu_id() {
	return get_term_by( 'slug', 'tabs-menu', 'nav_menu' )->term_id;
}

