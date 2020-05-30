<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 */

sudbury_log( "Falling Back to index.php for themeing, this is unexpected and should probably be corrected by creating a template that best fits the requested content which is {$_SERVER['REQUEST_URI']} in wp-content/themes/sudbury/index.php" );
get_template_part( 'archive' );