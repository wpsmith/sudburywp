<?php
/**
 * Debug Functions
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Debug
 */

/**
 * Global to keep track of whether to exit on a specific action... can be referenced by other functions
 */
$sudbury_exit_on = false;

/**
 * Exit on a wordpress action call
 *
 * @param string $action   The Action to exit on
 * @param int    $priority The Priority of the action you want to exit on (low for early, high for late)
 */
function exit_on( $action = 'shutdown', $priority = 10 ) {
	global $sudbury_exit_on;
	$sudbury_exit_on = $action;
	add_action( $action, 'exit', $priority, 0 );
	add_filter( 'wp_redirect', '__return_false', 999, 0 );
}

/**
 * Determines if running with in a debug state
 * @return bool Whether we are in debugging environment mode
 */
function is_debug() {
	if ( defined( 'WP_DEBUG' ) ) {
		return ( isset( $_REQUEST['debug'] ) || isset( $_COOKIE['sudbury_developer'] ) ) && is_internal() && WP_DEBUG;
	}

	return false;
}

/**
 * Whether the current request comes from an internal system/user.  This means that they are allowed to view internal
 * content like non-public sites and the internal menu
 *
 * @return bool Whether the current request is authorized for internal content
 */
function is_internal() {
	return apply_filters( 'sudbury_is_internal', is_internal_ip() || ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) );
}

/**
 * Determines if the request came from a Town Computer and should have access to the Internal Website
 *
 * @param string $ip The IP Address to check. Leave blank for the IP of the current request
 *
 * @return bool Whether this request came from an internal computer
 * @return bool Whether this request came from an internal computer
 */
function is_internal_ip( $ip = '' ) {
	if ( '' == $ip && isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = explode( ':', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
	}

	return apply_filters( 'sudbury_is_internal_ip', '10.' == substr( $ip, 0, 3 ) || '127.0.0.1' == $ip );
}

/**
 * The Callback function to set debug mode for this user (sets a cookie)
 */
function debug_mode() {
	if ( isset( $_REQUEST['debug_display'] ) ) {
		if ( 'on' == $_REQUEST['debug_display'] ) {
			setcookie( 'debug_display', 'on', time() + 3600 * 24 * 7 * 365, '/', $_SERVER['HTTP_HOST'] );
			echo "Prepare for Grossness :-/";
		} else {
			setcookie( 'debug_display', 'off', time() - 3600, '/', $_SERVER['HTTP_HOST'] );
			echo "All the grossness has been hidden for you :-)";
		}
	}

	if ( isset( $_REQUEST['set_developer_cookie'] ) ) {
		if ( ! is_super_admin() ) {
			wp_die( 'You must be a super admin to obtain the developer cookie' );
		}
		$domains = array(
			$_SERVER['HTTP_HOST'],
			'ci.sudbury.ma.us',
			'sudburyseniorcenter.org',
			'goodnowlibrary.org'
		);
		if ( 'on' == $_REQUEST['set_developer_cookie'] ) {
			if ( defined( 'SUDBURY_DEVELOPER_COOKIE' ) ) {
				foreach ( $domains as $domain ) {
					setcookie( 'sudbury_developer', SUDBURY_DEVELOPER_COOKIE, time() + YEAR_IN_SECONDS, '/', $domain );
				}
				wp_die( 'A Cookie which enables WP_DEBUG has been set in this browser' );
			} else {
				wp_die( '"SUDBURY_DEVELOPER_COOKIE" has not been defined. Please check your wp-config.php' );
			}
		} else {
			foreach ( $domains as $domain ) {
				setcookie( 'sudbury_developer', 'off', time() - 3600, '/', $domain );
			}
			wp_die( 'The "sudbury_developer" cookie has been removed from this browser' );
		}
	}
}


add_action( 'init', 'debug_mode' );

/**
 * Open up a manual debug printout
 */
function d_start() {
	echo '<b>Starting Debug Printout</b><br><pre>';
}

/**
 * Close off a manual debug printout
 */
function d_end() {
	echo '</pre>';

}

/**
 * One of the factors that determines whether to print debug information
 */
$sudbury_debug_print = true;

/**
 * Temporarily disable Debug Printing
 */
function d_off() {
	global $sudbury_debug_print;
	$sudbury_debug_print = false;
}

/**
 * Temporarily enable Debug Printing
 */
function d_on() {
	global $sudbury_debug_print;
	$sudbury_debug_print = true;
}

/**
 * Returns true if the current server this script is running on is a production server
 * @return bool True if running on a production server, otherwise false
 */
function is_production_server() {
	if ( defined( 'IS_PRODUCTION_SERVER' ) ) {
		return IS_PRODUCTION_SERVER;
	} else {
		return false;
	}
}

/**
 *
 * Takes any expression and prints its evaluation in a way that doesn't look like barf.  So arrays get print_r()'ed
 * with the normal print_r format instead of the HTML compressed format.  NULL is printed as NULL not nothing.  true
 * and false are printed as true and false alike, strings have single quotes wrapped around them to clearly show that
 * they are a sting and to show any whitespace.  and everything is escaped for HTML
 *
 * @param mixed $expression The Expression to print
 * @param bool  $plaintext  Whether to try and force text/plain header... probably not
 * @param bool  $esc_html   Should we escape HTML for you... probably yes
 *
 * @return string|bool The human readable version of $expression or false on failure
 */
function _d( $expression, $plaintext = false ) {
	return sudbury_debug( $expression, $plaintext );
}

/**
 * Shorthand for sudbury_debug(). Prints an expression's evaluation in a human readable and consistent format
 *
 * This is the best function ever... [ as well as its shorthand d() ].  It takes any expression and prints its evaluation
 * in a way that doesn't look like barf.  So arrays get print_r()'ed with the normal print_r format instead of the HTML
 * compressed format.  NULL is printed as NULL not nothing.  true and false are printed as true and false alike, strings
 * have single quotes wrapped around them to clearly show that they are a sting and to show any whitespace.  and everything
 * is escaped for HTML
 *
 * This function prints AND Returns the formatted value... beware might change the return to just pass through $expression
 *
 * @param mixed $expression The Expression to print
 * @param bool  $plaintext  Whether to try and force text/plain header... probably not
 * @param bool  $esc_html   Should we escape HTML for you... probably yes
 *
 * @return string|bool The human readable version of $expression or false on failure
 */
function d( $expression, $plaintext = false ) {
	global $sudbury_debug_print, $sudbury_debug_source_location;
	if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		$sudbury_debug_print = false;
	}

	$formatted = sudbury_debug( $expression, $plaintext );
	if ( ( $sudbury_debug_print || ( isset( $_REQUEST['action'] ) && 'debug_bar_console' == $_REQUEST['action'] ) ) && is_debug() ) {
		if ( function_exists( 'apply_filters' ) && apply_filters( 'sudbury_debug_print_hook_' . $sudbury_debug_source_location, $formatted ) ) {
			$formatted = "<pre>" . esc_html( $formatted ) . "</pre>";
			echo $formatted;
		}
	}

	return $formatted;
}

/**
 * Prints an expression's evaluation in a human readable and consistent format
 *
 * This is the best function ever... [ as well as its shorthand d() ].  It takes any expression and prints its evaluation
 * in a way that doesn't look like barf.  So arrays get print_r()'ed with the normal print_r format instead of the HTML
 * compressed format.  NULL is printed as NULL not nothing.  true and false are printed as true and false alike, strings
 * have single quotes wrapped around them to clearly show that they are a sting and to show any whitespace.  and everything
 * is escaped for HTML
 *
 * @param mixed $expression The Expression to print
 * @param bool  $plaintext  Whether to try and force text/plain header... probably not
 * @param bool  $esc_html   Should we escape HTML for you... probably yes
 *
 * @return string|bool The human readable version of $expression or false on failure
 */
function sudbury_debug( $expression, $plaintext = false ) {
	if ( defined( 'SUDBURY_DISABLE_DEBUG' ) && SUDBURY_DISABLE_DEBUG ) {
		return false;
	}

	$isPlain = ! headers_sent() && $plaintext;
	global $sudbury_debug_print, $sudbury_debug_source_location;

	if ( $isPlain ) {
		header( "Content-Type: text/plain" );
	}

	if ( is_array( $expression ) ) {
		$text = print_r( $expression, true );
	} else {
		$text = var_export( $expression, true );
	}

	do_action( 'sudbury_debug_print_' . substr( $text, 0, 20 ) );

	return $text;
}

/**
 * prints the backtrace for the past $limit function calls
 *
 * @param int   $limit The Max number of calls to print
 * @param array $args  Extra args for the function
 *
 * @return array|bool|mixed|void  ... Don't ask, Its a debugging function... it returns something and prints something else
 */
function b( $limit = 3, $args = array() ) {
	return sudbury_backtrace( $limit, $args );
}

/**
 * The backtrace ID
 */
$backtrace_id = 0;

/**
 * prints the backtrace for the past $limit function calls
 * @bug Needs to compensate for function calls to b() and sudbury_backtrace() and other functions called within sudbury_backtrace()
 *
 * @param int   $limit The Max number of calls to print
 * @param array $args  Extra args for the function
 *
 * @return array|bool|mixed|void ... Don't ask, Its a debugging function... it returns something and prints something else
 */
function sudbury_backtrace( $limit = 3, $args = array() ) {
	if ( defined( 'SUDBURY_DISABLE_DEBUG' ) && SUDBURY_DISABLE_DEBUG ) {
		return false;
	}
	$apply_filters = function_exists( 'apply_filters' );
	global $sudbury_debug_print, $backtrace_id;
	if ( ! $sudbury_debug_print ) {
		return false;
	}

	$backtrace_id ++;
	if ( $apply_filters && apply_filters( 'backtrace_cancellation', false, $limit, $args ) ) {
		return false;
	}
	if ( ! defined( 'DEBUG_BACKTRACE_PROVIDE_OBJECT' ) ) {
		define( 'DEBUG_BACKTRACE_PROVIDE_OBJECT', 0 );
	}
	$defaults = array( 'backtrace_options' => DEBUG_BACKTRACE_PROVIDE_OBJECT );
	$args     = array_merge( $defaults, $args );

	$backtrace = debug_backtrace( $args['backtrace_options'], $limit );


	if ( $apply_filters ) {
		echo apply_filters( 'pre_print_backtrace', '<pre>', $backtrace, $limit, $args );
	} else {
		echo '<pre>';
	}

	if ( $apply_filters ) {
		$backtrace = apply_filters( 'pre_filter_backtrace', $backtrace, $limit, $args );
	}

	foreach ( $backtrace as $index => $stack_frame ) {
		if ( is_array( $stack_frame ) ) {
			$default_printout = print_r( $stack_frame, true );
		} else {
			if ( is_object( $stack_frame ) || is_bool( $stack_frame ) ) {
				$default_printout = var_export( $stack_frame, true );
			} else {
				$default_printout = $stack_frame;
			}
		}
		if ( $apply_filters ) {
			$printout = apply_filters( 'format_backtrace_stack_frame', $default_printout, $stack_frame, $index, $backtrace, $limit, $args );
			sudbury_log( $printout, array( 'echo' => false, 'context' => 'backtrace-' . $backtrace_id ) );
			echo $printout;
		} else {
			sudbury_log( $default_printout, array( 'echo' => false, 'context' => 'backtrace-' . $backtrace_id ) );
			echo $default_printout;
		}
	}

	if ( $apply_filters ) {
		echo apply_filters( 'post_print_backtrace', '</pre>', $backtrace, $limit, $args );
	} else {
		echo '</pre>';
	}

	return $backtrace;
}

/**
 * Shorthand for sudbury_debug_queries
 *
 * @param int   $limit The max number of queries to print
 * @param array $args  Extra args for sudbury_debug_queries()
 *
 * @return bool true on success, otherwise false
 */
function dq( $limit = 1, $args = array() ) {
	return sudbury_debug_queries( $limit, $args );
}

/**
 * Shorthand for sudbury_debug_queries with echoing off
 *
 * @param int   $limit The max number of queries to print
 * @param array $args  Extra args for sudbury_debug_queries()
 *
 * @return bool true on success, otherwise false
 */
function _dq( $limit = 1, $args = array() ) {
	$args['echo'] = false;

	return sudbury_debug_queries( $limit, $args );
}


/**
 * Prints the SQL syntax, time taken, and more for the last $limit number of queries sent through #wpdb
 *
 * @param int   $limit The max number of queries to print
 * @param array $args  Extra args for sudbury_debug_queries()
 *
 * @return string The output or empty string on failure
 */
function sudbury_debug_queries( $limit = 1, $args = array() ) {
	if ( defined( 'SUDBURY_DISABLE_DEBUG' ) && SUDBURY_DISABLE_DEBUG ) {
		return '';
	}

	global $wpdb;
	global $sudbury_debug_print;

//	if ( ! $sudbury_debug_print ) {
//		return '';
//	}

	$defaults = array(
		'before' => "<pre>Last $limit " . _n( "Query", "Queries", $limit, 'sudbury' ) . " from wpdb\n</pre>",
		'after'  => "<pre>Done Listing Queries</pre>",
		'echo'   => true,
		'show'   => 0
	);
	$args     = array_merge( $defaults, $args );
	$out      = array();
	$queries  = array_slice( $wpdb->queries, - $limit );
	foreach ( $queries as $query ) {
		if ( $args['show'] === 'all' ) {
			$formatted = _d( $query );
		} else {
			$formatted = _d( $query[ $args['show'] ] );
		}
		$out[] = $formatted;
	}

	_sudbury_log( implode( "\n", $out ), array( 'context' => 'DQ' ) );

	$out = $args['before'] . implode( "<br>", $out ) . $args['after'];

	if ( $args['echo'] ) {
		echo $out;
	}

	return $out;
}

/**
 * Shorthand for sudbury_debug_exit()
 */
function de() {
	sudbury_debug_exit();
}

/**
 * Forces the script to exit when called unless certain criteria are met... Like being a production server and not in debug more
 * @return bool
 */
function sudbury_debug_exit() {
	if ( ! WP_DEBUG || ( defined( 'SUDBURY_DISABLE_DEBUG' ) && SUDBURY_DISABLE_DEBUG ) ) {
		return false;
	}
	global $sudbury_debug_print;

	if ( ! $sudbury_debug_print ) {
		return false;
	}

	if ( function_exists( 'apply_filters' ) && apply_filters( 'debug_exit_cancellation', false ) ) {
		return false;
	}
	die( ' - ' . random_string( 500 ) );

}

/**
 * The Source of the Debug Buffer... Imagine that is is like output buffering but just for debug information
 *
 * @todo I want to make this work with nested buffers at some point
 */
$sudbury_debug_source_location = '';


/**
 * Shorthand for sudbury_debug_source_location()
 *
 * @param string $source The new location
 */
function d_loc( $source = '' ) {
	sudbury_debug_source_location( $source );
}

/**
 * Changes the Debug Location for debug output buffering
 *
 * @param string $source The new location
 */
function sudbury_debug_source_location( $source = '' ) {
	global $sudbury_debug_source_location;
	$sudbury_debug_source_location = $source;
}

/**
 * @example https://bitbucket.org/sudbury/sudburywp/commits/250486b7baf932364057dfb4ba6af7cf7a6768d4
 *
 * @param callable $lambda A function to execute for every single blog on the network
 *
 * @return array An array of return value from each of the blogs
 */
function foreach_blog( $lambda ) {
	$blogs = wp_get_sites( array( 'limit' => false ) );
	$map   = array();
	foreach ( $blogs as $blog ) {
		switch_to_blog( $blog['blog_id'] );
		$map[ $blog['blog_id'] ] = $lambda( $blog );
		restore_current_blog();
	}

	return $map;
}

/**
 * Allows you to loop through a simple posts query
 * @example foreach_post( function () { wp_delete_post( $post->ID ); } );
 *
 * @example foreach_post( 'faq', function () { echo $post->post_title } );
 * @example foreach_post( array( 'book', 'faq'), function () { echo $post->post_title; } );
 * @example foreach_post( 'book,faq', function () { echo $post->post_title; } );
 *
 * @example foreach_post( false, false, function () { echo $post->post_title; } );
 * @example foreach_post( 'post', array('draft', 'publish'), function () { echo $post->post_title; } );
 * @example foreach_post( 'post', 'draft,publish', function () { echo $post->post_title; } );
 *
 * @example foreach_post( 'post', 'publish', -1, function () { echo $post->post_title; } );
 * @example foreach_post( 'post', 'publish', 5, function () { echo $post->post_title; } );
 * @example foreach_post( 'post', 'publish', 10, function () { echo $post->post_title; } );
 *
 * @example foreach_post( array( 'book', 'faq'), function () { echo $post->post_title; } );
 *
 * @param $lambda
 *
 * @return array An Array of posts that were passed by the foreach loop
 */
function foreach_post( $arg0, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null ) {
	$query  = array( 'posts_per_page' => - 1 );
	$lambda = false;

	if ( isset( $arg0 ) && ! is_callable( $arg0 ) ) { // If the first arg set and not a callable then it must be a post_type arg
		if ( is_string( $arg0 ) ) {
			$query['post_type'] = explode( ',', $arg0 );
		} elseif ( is_array( $arg0 ) ) {
			$query['post_type'] = $arg0;
		} else {
			// nothing
		}
	} else { // Otherwise it is a lambda and we will use the default query
		$lambda = $arg0;
	}


	if ( isset( $arg1 ) && ! is_callable( $arg1 ) && ! $lambda ) { // If the second arg set, not a callable, and the last arg was not a callable then it must be a post_status arg
		if ( is_string( $arg1 ) ) {
			$query['post_status'] = explode( ',', $arg1 );
		} elseif ( is_array( $arg1 ) ) {
			$query['post_status'] = $arg1;
		} else {
			// nothing
		}
	} elseif ( isset( $arg1 ) && is_callable( $arg0 ) ) { // the last arg was a callable then this is an override query array
		$query = array_merge( $query, $arg1 );
	} elseif ( isset( $arg1 ) ) { // This is the callable
		$lambda = $arg1;
	}

	if ( isset( $arg2 ) && ! is_callable( $arg2 ) && ! $lambda ) { // If the third arg set and not a callable and the last arg was not a lambda then this is a post_per_page override
		if ( is_string( $arg2 ) || is_numeric( $arg2 ) ) {
			$query['posts_per_page'] = intval( $arg2 );
		} else {
			// nothing
		}
	} elseif ( isset( $arg2 ) && is_callable( $arg1 ) ) { // the last arg was a callable then this is an override query array
		$query = array_merge( $query, $arg2 );

	} elseif ( isset( $arg2 ) ) { // Nope this is a lambda
		$lambda = $arg2;
	}

	if ( isset( $arg3 ) && is_callable( $arg3 ) ) { // Well is there a fourth arg and was the third arg a callable... in that case then the fourth arg is an override query array
		$lambda = $arg3;
	} elseif ( isset( $arg3 ) && is_callable( $arg2 ) ) {
		$query = array_merge( $query, $arg3 );
	}

	if ( isset( $arg4 ) && is_callable( $arg3 ) ) { // Well is there a fourth arg and was the third arg a callable... in that case then the fourth arg is an override query array
		$query = array_merge( $query, $arg4 );
	}

	$returns = array();
	// get the posts
	$posts = get_posts( $query );;
	// Loop through the results
	foreach ( $posts as $post ) {

		$returns[ $post->ID ] = $lambda( $post );
	}

	return $returns;
}

$yell_counter = 0;

function yell() {
	global $yell_counter;
	$yell_counter ++;
	d( "[YELL] I'm Here! #$yell_counter" );
}

function rewind_yell() {
	global $yell_counter;
	$yell_counter = 0;
}

function set_yell( $count = 0 ) {
	global $yell_counter;
	$yell_counter = $count;
}

/**
 * Hurls a bunch of random text of the specified length at the browser... helpful for stopping a redirect or IE error Pages
 *
 * @param int $chars The number of random characters to display in addition to the Comment wrappers
 */
function hurl( $chars = 1024 ) {
	d( ' === Starting Hurl === ' );
	d( random_string( $chars ) );
	d( ' === End of Hurl === ' );
}
