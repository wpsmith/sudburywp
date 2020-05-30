<?php
/**
 * The Backwards Compatibility URL Resolution API. Resolves the Old URLs to the new URLS... Must use URL Rewriting to
 * send requests to this core script with the type parameter set to anything in $known_types below and the id parameter
 * set to the WebEditor ID of the content
 *
 * @author         Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package        Sudbury Back Compat
 * @subpackage     Resolver API
 */

$redirect_type = 302;
$known_types   = array(
	'news'     => 'news',
	'doc'      => 'documents',
	'docs'     => 'documents',
	'faq'      => 'faqs',
	'message'  => 'message',
	'dept'     => 'blog',
	'page'     => 'page',
	'location' => 'location',
	'image'    => 'images',

);

$validate = array(
	'key'   => array( 'message', 'page', 'location' ),
	'num'   => array( 'news', 'doc', 'faq', 'images' ),
	'title' => array( 'dept' ),
);

$base_query_args = array(
	'no_found_rows' => 1,
	'update_post_term_cache' => false,
	'update_post_meta_cache' => false,
	'cache_results' => false
);

// Load up Wordpress if not already loaded
if ( ! defined( 'ABSPATH' ) ) {
	require_once( '../../../../wp-load.php' );
}
if ( is_internal() ) {
	$form = '<style>
			input[type=checkbox]:focus, input[type=email]:focus, input[type=number]:focus, input[type=password]:focus, input[type=radio]:focus, input[type=search]:focus, input[type=tel]:focus, input[type=text]:focus, input[type=url]:focus, select:focus, textarea:focus {
				border-color: #5b9dd9;
				-webkit-box-shadow: 0 0 2px rgba(30,140,190,.8);
				box-shadow: 0 0 2px rgba(30,140,190,.8);
			}

			input[type="text"], select {
				border: 1px solid #ddd;
				-webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
				box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
				background-color: #fff;
				color: #333;
				-webkit-transition: .05s border-color ease-in-out;
				transition: .05s border-color ease-in-out;
				font-size: 100%;
				font-size: 27px;

			}
			input[type="text"] {
				padding: 3px 8px;
				font-size: 1.7em;
				line-height: 100%;
				height: 1em;
				outline: 0;
				margin: 0;
				background-color: #fff;

			}
			input[type="submit"] {
			display: inline-block;
text-decoration: none;
font-size: 13px;
line-height: 26px;
height: 28px;
margin: 0;
padding: 0 10px 1px;
cursor: pointer;
border-width: 1px;
border-style: solid;
-webkit-appearance: none;
-webkit-border-radius: 3px;
border-radius: 3px;
white-space: nowrap;
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
box-sizing: border-box;
			background: #2ea2cc;
border-color: #0074a2;
-webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,.5),0 1px 0 rgba(0,0,0,.15);
box-shadow: inset 0 1px 0 rgba(120,200,230,.5),0 1px 0 rgba(0,0,0,.15);
color: #fff;
text-decoration: none;
height: 30px;
line-height: 28px;
padding: 0 12px 2px;
text-align: center;
			}
			</style>';
	$form .= '<form action="/backcompat.php" method="GET">';
	$form .= '<input type="text" name="id" placeholder="WebEditor ID" autofocus="autofocus"/>';
	$form .= '<select name="type">';
	foreach ( array_keys( $known_types ) as $type ) {
		$form .= '<option value="' . $type . '">' . $type . '</option>';
	}
	$form .= '</select><br><br>';
	$form .= '<input type="submit" class="btn btn-primary" placeholder="WebEditor ID" />';
	$form .= '</form>';
} else {
	$form = '';
}

// Need to specify a news_id from the Legacy Websitor.  This news ID Must have been Present at Cut Over https://bitbucket.org/sudbury/sudburywp/wiki/Cut%20Over%20Date
if ( ! isset( $_REQUEST['id'] ) ) {

	wp_die( 'Sorry but we can\'t resolve this request to a page because: (Tech Note): The "id" query var is not set and thus the Legacy Webeditor to Wordpress resolver can\'t work it\'s magic :-(<br><br>' . $form );
}


if ( ! isset( $_REQUEST['type'] ) ) {
	wp_die( 'Sorry but the type of resolution for the WebEditor to WordPress was never specified' );
}

// Variables
$id          = $_REQUEST['id'];
$type        = $_REQUEST['type'];
$destination = false; // Resolvers will set this

// Verify id is a number
if ( in_array( $type, $validate['num'] ) && ! is_numeric( $id ) ) {
	wp_die( 'Sorry but we can\'t resolve this request because: (Tech Note): The "id" query var is not numeric and thus the Webeditor to Wordpress Resolver can\'t work it\'s magic' );
}

if ( in_array( $type, $validate['key'] ) ) {
	$id = sanitize_key( $id );
	if ( ! $id ) {
		wp_die( 'Sorry id is empty after sanitizing' );
	}
}


if ( in_array( $type, $validate['key'] ) ) {
	$id = sanitize_title( $id );
	if ( ! $id ) {
		wp_die( 'Sorry id is empty after sanitizing' );
	}
}

// Pull in the appropriate resolver
if ( isset( $known_types[ $type ] ) && file_exists( __DIR__ . '/resolver-' . $known_types[ $type ] . '.php' ) ) {
	include( 'resolver-' . $known_types[ $type ] . '.php' );
} else {
	wp_die( 'Sorry but there is no resolver for ' . $type );
}

if ( is_array( $destination ) && ! empty( $destination ) ) {
	$destination = $destination[0];
}

if ( is_string( $destination ) ) {
	wp_redirect( $destination, $redirect_type );
	exit();
}

if ( is_object( $destination ) && isset( $destination->BLOG_ID ) ) {
	switch_to_blog( $destination->BLOG_ID );
	wp_redirect( get_post_permalink( $destination->ID ), $redirect_type );
	exit;
}

status_header( 404 );
wp_die( 'Sorry but we can\'t resolve this request to a page because there is no ' . ucfirst( $type ) . ' with a legacy WebEditor ID of: <code>' . $id . '</code> Please try <a href="/search">searching our site</a>' . $form );
