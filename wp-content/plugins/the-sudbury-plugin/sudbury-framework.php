<?php
/**
 * The Sudbury Framework
 *
 * The Sudbury Framework Provides a set of functionality that the rest of the Sudbury Plugin and other plugins use
 * for better presentation and operation of the Town's WordPress Network
 *
 * This file provides most of the Framework's Helper Functions such as is_committee() and is_department(), as well as the
 * logical functions behind them like sudbury_has_type()
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Sudbury_Plugin
 */

/**
 * Adds Editor CSS because Wordpress Includes doesn't support adding via a plugin
 *
 * @param array $args methods for adding the css default is enqueued internal css
 *
 * @return string The Editor CSS
 */
function sudbury_add_editor_css( $args = array() ) {
	$defaults = array( 'mode' => 'internal', 'enqueue' => true );
	$args     = wp_parse_args( $args, $defaults );

	if ( 'internal' == $args['mode'] ) {
		return sprintf( '<style type="text/css">%s</style>', file_get_contents( __DIR__ . DIRECTORY_SPER . 'editor-style.css' ) );
	} else {
		if ( 'external' == $args['mode'] ) {
			$url = plugins_url( '/editor-style.css' );
			if ( $args['enqueue'] ) {
				wp_enqueue_style( 'sudbury_admin_editor_style', $url );
			}

			return $url;
		}
	}

	return '';
}

/**
 * takes a delimited & padded string and converts it to an array, with the legacy sudbury_types
 *
 * @param string $compound  The Compounded field
 * @param string $delimiter The Delimiter to use
 *
 * @deprecated Aug. 2014
 * @return array The value of the compound field
 */
function sudbury_from_compound_field( $compound, $delimiter = '|' ) {
	return explode( $delimiter, trim( $compound, $delimiter ) );
}

/**
 * @param array  $array     The Array to convert to a compound field (a field delimited in the middle and on the ends)
 * @param string $delimiter The Delimiter to use
 *
 * @deprecated Aug. 2014
 * @return string The Compound value
 */
function sudbury_to_compound_field( $array, $delimiter = '|' ) {
	return $delimiter . implode( $delimiter, $array ) . $delimiter;
}


/**
 * Compiles and Validates the sudbury_types option and any other really complex sudbury options
 *
 * @param array $data
 *
 * @return array The Newly Validated Data
 */
function sudbury_dept_info_options_bulk_validate( $data ) {
	$types = array();
	foreach ( $data as $key => $value ) {
		if ( 0 === strpos( $key, 'sudbury_types_' ) ) {
			if ( 'on' == $value ) {
				$types[] = substr( $key, strlen( 'sudbury_types_' ) );
			}
			unset( $data[ $key ] );
		}
	}
	$data['sudbury_types'] = $types;

	return $data;
}

add_filter( 'sudbury_plugin_options_bulk_validate', 'sudbury_dept_info_options_bulk_validate' );


/**
 * Prints the HTML for a Datetime Editor
 *
 * @param string $stamp     The Text Label for the field
 * @param int    $timestamp The Timestamp
 * @param string $name      The html name of the field
 * @param string $format    The Date format to use
 * @param array  $args      Extra Args
 */
function sudbury_datetime_editor( $stamp, $timestamp, $name = 'sudbury_timestamp', $format = 'm/d/Y H:i:s', $args = array() ) {
	echo sudbury_get_datetime_editor( $stamp, $timestamp, $name, $format, $args );
}

/**
 * Returns the HTML for a Datetime Editor
 *
 * @param string $stamp     The Text Label for the field
 * @param int    $timestamp The Timestamp
 * @param string $name      The html name of the field
 * @param string $format    The Date format to use
 * @param array  $args      Extra Args
 *
 * @return string The HTML for a Datetime Editor
 */
function sudbury_get_datetime_editor( $stamp, $timestamp, $name = 'sudbury_timestamp', $format = 'm/d/Y H:i:s', $args = array() ) {
	$defaults = array(
		'disabled' => false,
	);

	$js_format = "D M d Y H:i:s O";
	$args      = wp_parse_args( $args, $defaults );

	if ( ! $timestamp ) {
		$timestamp = date( $format );
	}

	if ( is_numeric( $timestamp ) ) {
		$timestamp = date( $format, $timestamp );
	}
	$html = sprintf( '<div class="sudbury_datetimepicker_container" id="%s_container">', $name );

	// If they didn't provide a stamp template set it to an empty string
	if ( ! $stamp ) {
		$stamp = '';
	}

	// If there is no placeholder then add it on to the end
	if ( false === strpos( $stamp, '%s' ) ) {
		$stamp .= '%s';
	}

	// They provided more than just a placeholder... They want a label to go along too so lets generate the HTML for that
	if ( '%s' != $stamp ) {
		$stamp = '<div class="sudbury_timepicker_stamp">' . str_replace( '%s', '</div>%s', $stamp );;
	}

	$id    = random_string( 12 );
	$field = '<div class="sudbury_timepicker_field"><input type="text" name="' . $name . '" id="' . $id . '" class="sudbury_datetimepicker"   data-datepicker-format="' . $format . '" value="' . $timestamp . '" ' . disabled( $args['disabled'], true, false ) . ' /></div>';

	$html .= sprintf( $stamp, $field );


	$plus_week_link  = "<a href=\"#\" data-number=\"1\" data-target=\"#$id\" class=\"sudbury_datepicker_add_week\">+ Week</a>";
	$minus_week_link = "<a href=\"#\" data-number=\"-1\" data-target=\"#$id\" class=\"sudbury_datepicker_add_week\">- Week</a>";
	$now_link        = "<a href=\"#\" data-date=\"" . date( $js_format ) . "\" data-target=\"#$id\" class=\"sudbury_datepicker_set_date\">Today</a>";

	//$html .= '<div class="sudbury_timepicker_more">' . $plus_week_link . " | " . $minus_week_link . " | " . $now_link . '</div>';

	$html .= '<div class="clear"></div>';
	$html .= '</div>';

	return $html;
}

if ( ! function_exists( 'strstartswith' ) ) {
	/**
	 * Determines if $haystack starts with $needle
	 *
	 * @param string $needle   The String to check if $haystack starts with
	 * @param string $haystack The string to check if it starts with $needle
	 *
	 * @return bool Whether the $haystack starts with $needle
	 */
	function strstartswith( $needle, $haystack ) {
		return ! strncmp( $haystack, $needle, strlen( $needle ) );
	}
}

if ( ! function_exists( 'strendswith' ) ) {
	/**
	 * Determines if $haystack ends with $needle
	 *
	 * @param string $needle   The String to check if $haystack ends with
	 * @param string $haystack The string to check if it ends with $needle
	 *
	 * @return bool Whether the $haystack ends with $needle
	 */
	function strendswith( $needle, $haystack ) {
		return $needle === substr( $haystack, - strlen( $needle ) );
	}
}

if ( ! function_exists( 'random_letter' ) ) {
	/**
	 * Returns a random lowercase letter a-z
	 * @return string The random letter
	 */
	function random_letter() {
		$letters = 'abcdefghijklmnopqrstuvwxyz';
		$i       = rand( 0, strlen( $letters ) - 1 );

		return $letters[ $i ];
	}
}

if ( ! function_exists( 'random_string' ) ) {
	/**
	 * Returns a random lowercase string of length $l
	 *
	 * @param int $l The length of the string you want
	 *
	 * @return string The random letter
	 */
	function random_string( $l = 1 ) {
		$str = '';
		for ( $i = 0; $i < $l; $i ++ ) {
			$str .= random_letter();
		}

		return $str;

	}
}
function sudbury_start_flushable_request() {
	ignore_user_abort( true );
	ob_end_clean(); //Discard anything echoed to this point
	header( "Connection: close\r\n" );
	header( "Content-Encoding: none\r\n" );
	ob_start(); // Start recording
}

function sudbury_end_flushable_request() {
	$size = ob_get_length();
	header( "Content-Length: $size" );
	ob_end_flush();     // Strange behaviour, will not work
	flush();            // Unless both are called !
	ob_end_clean();     // Flush anything that might have been there
}

/**
 * Ends the request but allows post processing to happen without showing down the browser
 */
function sudbury_flush_request() {
	sudbury_start_flushable_request();
	sudbury_end_flushable_request();
}


/**
 * Sends a truly non-blocking HTTP request to the $url because WordPress HTTP API isn't capable of doing what it
 * claims with 'blocking' set to false because it still blocks... quite badly too
 *
 * @param string $url The URL
 */
function sudbury_non_blocking_http( $url ) {

	$parts = parse_url( $url );

	$fp = fsockopen( $parts['host'],
		isset( $parts['port'] ) ? $parts['port'] : 80,
		$errno, $errstr, 1 );

	$out = "GET " . $parts['path'] . '?' . $parts['query'] . " HTTP/1.1\r\n";
	$out .= "Host: " . $parts['host'] . "\r\n";
	$out .= "Content-Length: 0\r\n";
	$out .= "Connection: Close\r\n\r\n";

	fwrite( $fp, $out );
	fclose( $fp );
}


/**
 * returns the Wordpress Blog ID given the Legacy Shortname of a Department
 * or committee.  (FOr use with Filemaker and the Import Scripts)
 *
 * @param $name The legacy shortname
 *
 * @return int The BLog ID from the Legacy Slug
 */
function sudbury_get_id_from_legacy_shortname( $name ) {
	$name = apply_filters( 'sudbury_resolve_to_wordpress_name', $name ); //for legacy Webeditor short_names

	return get_id_from_blogname( $name );
}

/**
 * Attempts to format a given phone number into a standardized format
 * the phone number can be given in very cryptic formats and this will try to reparse
 * it into a better format
 *
 * @param string $number     User Input Phone Number in any garbled formatt '(97asdfasd8/645/9866)' becomes '(978) 645 - 9866'
 * @param string $format_str A Special Format String... use the default please... I barely understand the formatting language I created for this function
 *
 * @return string A Nice and pretty phone number
 */
function sudbury_format_phone( $number, $format_str = '[+# > {1}] (###) ### - #### x####' ) {
	$version = 'fun'; // 'fun' || 'cool' (fun is always better)

	if ( 'cool' == $version ) {
		return vsprintf( "+1 (%d) %d-%d x%d", sscanf( $number, "%3d%3d%4d%4d" ) );
	} elseif ( 'fun' == $version ) {
		$num_index = 0;
		$formatted = '';
		$format    = str_split( $format_str );
		for ( $i = 0; $i < count( $format ); $i ++ ) {
			$char = $format[ $i ];
			if ( '[' == $char ) {
				$end_if        = strpos( $format_str, ']', $i );
				$components_if = explode( '>', trim( substr( $format_str, $i, $end_if ), '[]' ) );
				$conditions    = explode( ',', trim( $components_if[1], "\t\n\r\0\x0B {}" ) );
				foreach ( $conditions as $condition ) {
					if ( 0 === strpos( $number, $condition, $num_index ) ) {
						foreach ( str_split( $components_if[0] ) as $char ) {
							if ( '#' == $char ) {
								$formatted .= $number[ $num_index ++ ];
							} else {
								$formatted .= $char;
							}
						}
						break;
					}
				}
				$i = $end_if;

			} else {
				if ( '#' == $char ) {
					$formatted .= $number[ $num_index ++ ];
					if ( $num_index == strlen( $number ) ) {
						break;
					}
				} else {
					$formatted .= $char;
				}
			}
		}

		return trim( $formatted );
	}
}

/**
 * Sanitizes a string representing a float.
 * Note: Does not add a decimal place if not given
 *
 * @param mixed $input User Input
 *
 * @return float A nice pretty float
 */
function sudbury_sanitize_float( $input ) {
	// Take care of the obvious cases
	if ( is_float( $input ) ) {
		return $input;
	}
	if ( is_int( $input ) ) {
		return $input + 0.0;
	}

	// Replace anything that isnt a number or a .
	$input = preg_replace( '/[^0-9\.]/', '', $input );

	// If multiple periods then we probs aren't dealing with a valid float... return 0.0
	if ( ( $count = substr_count( $input, '.' ) ) > 1 ) {
		return 0.0;
	} else {
		return floatval( $input );
	}
}

/**
 * Sanitizes a string representing an int.  A lot better than intval
 *
 * @param mixed $input Garbled gross user input that should be an int
 *
 * @return int A nice pretty int
 */
function sudbury_sanitize_int( $input ) {
	$float = sudbury_sanitize_float( $input );

	return floor( $float ); // Floor it!
}

/**
 * A fully foolproof absint that will take input like '50typo', '50 ', 50, '50.125' and give you 50
 *
 * @param mixed $input The User Input
 *
 * @return int A nice pretty int
 */
function sudbury_sanitize_absint( $input ) {
	return absint( sudbury_sanitize_int( $input ) );
}

/**
 * Returns the appropriate type of the site based on it's sudbury_types option.  NOTE: If it is both a Department AND a Committee it will prefer Department
 *
 * @param string|int $blog_id The Blog ID, empty string for current blog
 *
 * @return string The current Site's most prominent type attribute
 */
function sudbury_get_site_type( $blog_id = '' ) {
	if ( ! $blog_id ) {
		$blog_id = get_current_blog_id();
	}
	switch_to_blog( $blog_id );

	$types = get_option( 'sudbury_types' );
	if ( is_array( $types ) && ! empty( $types ) ) {
		if ( in_array( 'department', $types ) ) { // prefer Department if both department and committee in $types selected
			$return = 'Department';
		} elseif ( in_array( 'committee', $types ) ) {
			$return = 'Committee';
		} else { // If for some reason there is another site type... return the first element in $types... this is rare
			$return = ucfirst( $types[0] );
		}
	} else {
		$return = 'Site';
	}
	restore_current_blog();

	return $return;

}


/**
 * Echos the appropriate type of the site based on it's sudbury_types option.
 *
 * @param string|int $blog_id The Blog ID, empty string for current blog
 */
function sudbury_the_site_type( $blog_id = '' ) {
	echo esc_html( sudbury_get_site_type( $blog_id ) );
}


/**
 * Gets the shortname of the Site
 *
 * @param bool|int $blog_id The ID of the blog you want the slug of.  false for current blog
 *
 * @return string The Short Name of the Site ie the <name> in http://sudbury.ma.us/<name>/wp-admin/
 */
function sudbury_get_blog_slug( $blog_id = false ) {
	if ( false === $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	return trim( get_blog_details( $blog_id )->path, '/' );
}

/**
 * Cancels posting to twitter if the post is a guest post or it is not published
 *
 * @param string  $status The Tweet Status
 * @param WP_Post $post   The Post
 *
 * @return bool|string false if the tweet should be cancelled otherwise the status
 */
function sudbury_tweet_post( $status, $post ) {
	if ( sudbury_is_guest_post( $post->ID ) || $post->post_status != 'publish' ) {
		return false;
	}

	return $status;
}

add_action( 'tweet_post', 'sudbury_tweet_post', 10, 2 );



function sudbury_get_term_paths( $terms ) {
	foreach ( $terms as $i => $term ) {
		foreach ( $terms as $j => $other_term ) {
			if ( $term->parent && $term->parent == $other_term->term_id ) {
				unset( $terms[ $j ] );
			}
		}
	}

	return array_map( function ( $term ) {
		return sudbury_get_term_path( $term->parent, $term->taxonomy, false, ' - ', false ) . $term->name;
	}, $terms );

}

/**
 * @param        $id
 * @param        $tax
 * @param bool   $link
 * @param string $separator
 * @param bool   $nicename
 * @param array  $visited
 *
 * @return mixed|null|string|WP_Error
 */
function sudbury_get_term_path( $id, $tax, $link = false, $separator = '/', $nicename = false, $visited = array() ) {
	$chain  = '';
	$parent = get_term( $id, $tax );
	if ( null === $parent ) {
		return '';
	}
	if ( is_wp_error( $parent ) ) {
		return '';
	}
	if ( $nicename ) {
		$name = $parent->slug;
	} else {
		$name = $parent->name;
	}

	if ( $parent->parent && ( $parent->parent != $parent->term_id ) && ! in_array( $parent->parent, $visited ) ) {
		$visited[] = $parent->parent;
		$chain     .= sudbury_get_term_path( $parent->parent, $tax, $link, $separator, $nicename, $visited );
	}

	if ( $link ) {
		$chain .= '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '">' . $name . '</a>' . $separator;
	} else {
		$chain .= $name . $separator;
	}

	return $chain;
}

/**
 * Returns true if the current blog (or blog with $id) is categorized as a department
 *
 * @param bool|int $id The ID of the Blog. default: false for current blog
 *
 * @return bool Whether the Blog is a Department
 */
function is_department( $id = false ) {
	return sudbury_has_type( 'department', $id );
}

/**
 * Returns true if the current blog (or blog with $id) is categorized as a committee
 *
 * @param bool|int $id The ID of the Blog. default: false for current blog
 *
 * @return bool Whether the Blog is a Committee
 */
function is_committee( $id = false ) {
	return sudbury_has_type( 'committee', $id );
}

/**
 * Returns true if the current blog (or blog with $id) is categorized as a utility site
 *
 * @param bool|int $id The ID of the Blog. default: false for current blog
 *
 * @return bool Whether the Blog is a Utility site
 */
function is_utility( $id = false ) {
	return sudbury_has_type( 'utility', $id );
}

/**
 * Determines whether the specified blog or post was imported or not
 *
 * @param bool|int $blog_id The ID of the blog you want to check, or the ID of the blog hosting the post default: current Blog
 * @param bool|int $post_id The ID of the Post you want to check. default: false to check if blog was imported
 *
 * @return bool Whether the Content was imported or not
 */
function is_imported( $blog_id = false, $post_id = false ) {
	if ( false === $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	switch_to_blog( $blog_id );

	// Yes this can be compressed... but look at the wordpress coding standards :-)
	if ( false === $post_id ) {
		if ( get_option( 'x-imported-by', false ) ) {
			$imported = true;
		} else {
			$imported = false;
		}
	} else {
		if ( get_post_meta( $post_id, 'x-imported-by', true ) ) {
			$imported = true;
		} else {
			$imported = false;
		}
	}

	restore_current_blog();

	return $imported;

}

$GLOBALS['sudbury_layout'] = 'normal';

/**
 * Echos the Post Layout
 *
 * @param bool $id The ID of the Post
 *
 * @return string The Post Layout
 */
function sudbury_get_post_layout( $id = false ) {
	if ( ! is_home() && ! is_front_page() ) {
		if ( ! $id && $post = get_post() ) {
			$id = $post->ID;
		}

		if ( $id ) {
			$layout = get_post_meta( $id, 'sudbury_layout', true );
			if ( $layout ) {
				return $layout;
			}
		}
	}

	$layout = $GLOBALS['sudbury_layout'];

	$post = get_post( $id );

	if ( is_singular() ) {
		if ( $post->post_type == 'page' ) {
			$template = basename( get_page_template() );


			switch ( $template ) {
				case 'page-fullwidth.php':
					$layout = 'full';
					break;
				case 'page-fullwidth-notitle.php':
					$layout = 'full';
					break;
			}
		}
	}

	return $layout;
}

/**
 * Echos the Post Layout
 *
 * @param bool $id The ID of the Post
 */
function sudbury_the_post_layout( $id = false ) {
       echo esc_attr( sudbury_get_post_layout( $id ) );
}

/**
 * echos the current Site's name for the current post
 * (required because network queries don't change $blog_id)
 *
 * @param bool|int $the_blog_id The ID of the Blog default: the current blog
 *
 * @return string The name of the blog
 */
function sudbury_the_site_name( $the_blog_id = false ) {
	echo esc_html( sudbury_get_the_site_name( $the_blog_id ) );
}

/**
 * returns the current Site's name for the current post
 * (required because network queries don't change $blog_id)
 *
 * @param bool|int $the_blog_id The ID of the Blog default: the current blog
 *
 * @return string
 */
function sudbury_get_the_site_name( $the_blog_id = false ) {
	global $post;
	global $blog_id;

	if ( $the_blog_id ) {
		$id = $the_blog_id;
	} elseif ( isset( $post->BLOG_ID ) ) {
		$id = $post->BLOG_ID;
	} else {
		$id = $blog_id;
	}

	$details = get_blog_details( $id, true );

	return apply_filters( 'sudbury_get_the_site_name', $details->blogname, $id, $details );
}

/**
 * echos the current Site's url for the current post
 * (required because network queries don't change $blog_id)
 *
 * @param bool|int $the_blog_id The ID of the Blog default: the current blog
 *
 */
function sudbury_the_site_url( $the_blog_id = false ) {
	echo esc_attr( sudbury_get_the_site_url( $the_blog_id ) );
}

/**
 * returns the current Site's url for the current post
 * (required because network queries don't change $blog_id)
 *
 * @param int|bool $the_blog_id The ID of the blog, or false for current blog
 *
 * @return string The homepage URL of the given blog ID
 */
function sudbury_get_the_site_url( $the_blog_id = false ) {
	global $post;
	global $blog_id;

	if ( is_int( $the_blog_id ) || is_numeric( $the_blog_id ) ) {
		$id = $the_blog_id;
	} elseif ( isset( $post->BLOG_ID ) ) {
		$id = $post->BLOG_ID;
	} else {
		$id = $blog_id;
	}

	$details = get_blog_details( $id, true );

	return apply_filters( 'sudbury_get_the_site_url', esc_url( 'https://' . $details->domain . $details->path ), $id, $details );
}

/**
 * Gets the blog details based on an option name / value.  This is an extremely slow function so use only for Cron Jobs
 *
 * @param string $name  Option Name
 * @param mixed  $value Option Value
 *
 * @return bool|object The full blog details of the first blog found or false if not found
 */
function sudbury_get_blog_info_by_option( $name, $value ) {
	$blogs = wp_get_sites( array( 'limit' => false ) );
	if ( is_wp_error( $value ) ) {
		$default = false;
	} else {
		$default = new WP_Error();
	}

	foreach ( $blogs as $blog ) {
		switch_to_blog( $blog['blog_id'] );
		if ( $value === get_option( $name, $default ) ) {
			restore_current_blog();

			return get_blog_details( $blog['blog_id'], true );
		}
		restore_current_blog();
	}

	return false;
}

/**
 * Determines if the given site has been marked as archived
 *
 * @param int|bool $id
 *
 * @return bool
 */
function sudbury_is_site_archived( $id = false ) {
	if ( false === $id ) {
		$id = get_current_blog_id();
	}

	return ( 1 == get_blog_details( $id, false )->archived );
}

/**
 * Determines if the given post is archived... which normally means it's information is outdated and the reader should be informed as such
 *
 * @param int|string|WP_Post $post The Post
 * @param string|int         $blog_id
 *
 * @return bool
 */
function sudbury_is_post_archived( $post = 0, $blog_id = '' ) {
	if ( ! $blog_id ) {
		$blog_id = get_current_blog_id();
	}
	switch_to_blog( $blog_id );
	$post    = get_post( $post );
	$post_id = $post->ID;
	if ( 'attachment' == get_post_type( $post_id ) ) {
		$timestamp = get_post_meta( $post_id, 'sudbury_end_date_timestamp', true );
		$archived  = $timestamp ? $timestamp < time() : false;
	} else {
		$status         = get_post_status( $post_id );
		$archived_stati = array( 'public-archive', 'private' );
		$archived       = in_array( $status, $archived_stati );
	}
	restore_current_blog();


	return apply_filters( 'is_post_archived', $archived, $post, $blog_id );
}

/**
 * Gets the date a post was / will be archived
 *
 * @param string             $format  The Format of the Date
 * @param int|string|WP_Post $post    The Post
 * @param string|int         $blog_id THe BLog ID
 *
 * @return string HTML for the archived message
 */
function sudbury_get_post_archive_date( $format = 'n/j/Y', $post = 0, $blog_id = '' ) {
	if ( ! $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	switch_to_blog( $blog_id );

	$post    = get_post( $post );
	$post_id = $post->ID;

	if ( 'attachment' == get_post_type( $post_id ) ) {
		$timestamp = get_post_meta( $post_id, 'sudbury_end_date_timestamp', true );
		$enabled   = (bool) $timestamp;
	} else {
		$enabled   = get_post_meta( $post_id, '_post-expiration-enabled', true );
		$timestamp = get_post_meta( $post_id, '_post-expiration-timestamp', true );
	}
	restore_current_blog();

	if ( $enabled ) {
		$date = date( $format, $timestamp );
	} else {
		$date = '';
	}

	return apply_filters( 'sudbury_get_post_archive_date', $date, $format, $post, $blog_id );
}

/**
 * Determines if the given post is archived... which normally means it's information is outdated and the reader should be informed as such
 *
 * @param string             $format The Format of the Date
 * @param int|string|WP_Post $post   The Post
 * @param string|int         $blog_id
 *
 */
function sudbury_the_post_archive_date( $format = 'n/j/Y', $post = 0, $blog_id = '' ) {
	echo sudbury_get_post_archive_date( $format, $post, $blog_id );
}


/**
 * Gets the date the blog was archived, returns false if not archived
 *
 * @param string $format
 * @param string $blog_id
 *
 * @return bool|string
 */
function sudbury_get_the_archived_date( $format = 'l, F j, Y', $blog_id = '' ) {
	if ( ! $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	switch_to_blog( $blog_id );
	$timestamp = get_option( 'archived_date', false );
	restore_current_blog();

	if ( false === $timestamp ) {
		return false;
	} // Yeah it's weird but it's the one of the only logical ways... read onwards

	return date( $format, $timestamp );
}

/**
 * Echos the date the blog was archived, returns false if not archived
 *
 * @param string $format
 * @param string $blog_id
 */
function sudbury_the_archived_date( $format = 'l, F j, Y', $blog_id = '' ) {
	echo esc_html( sudbury_get_the_archived_date( $format, $blog_id ) );
}

/**
 * Strips the 'Private: ' prefix off of the post title of a private post.
 *
 * @param string $title The Post Title
 *
 * @return string THe new title with 'Private: ' removed if it prefixed the title
 */
function sudbury_remove_private_heading_from_title( $title ) {
	if ( strstartswith( 'Private: ', $title ) ) {
		return str_replace( 'Private: ', '', $title );
	}

	return $title;
}

add_filter( 'the_title', 'sudbury_remove_private_heading_from_title' );


/**
 * Gets the help url for the sudbury web help system
 *
 * @param string      $slug   The help page slug that you want to link to
 * @param null|string $scheme Optional. Scheme to give the site url context. Currently 'http', 'https', 'login', 'login_post', 'admin', or 'relative'.
 *
 * @return string The base usl to the Sudbury Help Section (With Trailing Slash)
 */
function sudbury_help_url( $slug = '', $scheme = null ) {
	if ( defined( 'SUDBURY_KB_BLOG_ID' ) ) {
		$kb_blog_id = SUDBURY_KB_BLOG_ID;
	} else {
		$kb_blog_id = get_blog_details( array( 'domain' => $_SERVER['HTTP_HOST'], 'path' => '/kb/' ) );

		if ( $kb_blog_id ) {
			$kb_blog_id = $kb_blog_id->blog_id;
		} else {
			$kb_blog_id = 1;
		}
	}
	switch_to_blog( $kb_blog_id );
	$posts = get_posts( array( 'name' => $slug, 'post_type' => 'any' ) );

	$url = '/kb/';
	if ( ! empty( $posts ) ) {
		$post = $posts[0];
		$url  = get_post_permalink( $post->ID );
	}

	$url = apply_filters( 'sudbury_help_url', $url, $slug, $scheme );

	restore_current_blog();

	return $url;
}

/**
 * Get the post permalink for a post on a given blog
 *
 * @param $post_id
 * @param $blog_id
 *
 * @return string|WP_Error
 */
function sudbury_get_post_permalink( $post_id, $blog_id ) {
	switch_to_blog( (int) $blog_id );
	$ret = get_post_permalink( $post_id );

	restore_current_blog();

	return $ret;
}


/**
 * Echos the help url
 *
 * @param string $slug
 * @param null   $scheme
 */
function sudbury_the_help_url( $slug = '', $scheme = null ) {
	echo esc_url( sudbury_help_url( $slug, $scheme ) );
}

/**
 * Forces the given Absolute URL to be HTTPS if it is HTTP
 *
 * @param string $url An Absolute URL starting
 *
 * @return string An HTTPS Absolute URL
 */
function sudbury_force_https_url( $url ) {
	if ( strstartswith( 'http:', $url ) ) {
		return 'https' . substr( $url, 4 );
	}

	return $url;
}

/**
 * This is a simple redirect registry for wordpress.  I'm not sure if wordpress
 * already has one but this was really easy to implement (and filter)
 *
 */
function sudbury_redirect_registry() {
	global $post;
	$reg = get_site_option( 'sudbury_redirect_registry' );

	if ( false === $reg ) {
		sudbury_log( 'Redirect Registry not found' );

		return;
	}

	$rule = false;
	if ( isset( $_REQUEST['page'] ) && isset( $reg['admin_page'][ $_REQUEST['page'] ] ) ) {

		$recorded_rule = $reg['admin_page'][ $_REQUEST['page'] ];

		// cancel redirect by returning false
		// modify redirect url by returning string
		// modify full rule by returning full array
		$rule = apply_filters( 'redirect_url_' . $_REQUEST['page'], $recorded_rule );
		if ( is_string( $rule ) ) {
			$tmp          = $rule;
			$rule         = $recorded_rule;
			$rule['dest'] = $tmp;
		}

	} elseif ( isset( $reg['direct'][ $_SERVER['REQUEST_URI'] ] ) ) {
		$rule = $reg[ $_SERVER['REQUEST_URI'] ];
	} elseif ( isset( $_REQUEST['p'] ) && isset( $reg['post'][ get_current_blog_id() . '-' . $_REQUEST['p'] ] ) ) {
		$rule = $reg['post'][ get_current_blog_id() . '-' . $_REQUEST['p'] ];
	} elseif ( is_object( $post ) && $reg['post'][ get_current_blog_id() . '-' . $post->ID ] ) {
		$rule = $reg['post'][ get_current_blog_id() . '-' . $post->ID ];
	}

	if ( $rule ) {
		/* Can do a lot more with options here */
		wp_redirect( $rule['dest'], $rule['code'] );
		exit;
	}

}

add_action( 'wp_loaded', 'sudbury_redirect_registry' );


/**
 * Determines if there is a redirect route of $type from $source.  Supports admin page redirects by the admin page slug
 *
 * @param string $type   The Type of Redirect, either 'direct' or 'admin_page'
 * @param string $source The Source (Depends on Type)
 *
 * @return bool
 */
function sudbury_has_redirect( $type, $source ) {
	$registry = get_site_option( 'sudbury_redirect_registry' );

	return isset( $registry[ $type ][ $source ] );
}

/**
 * Registers a new redirect route from $source to $dest.  Supports admin page redirects too by the admin page slug
 *
 * @param string     $type   The type of Redirect ('admin_page', 'direct', 'post')
 * @param string|int $source The Source Data (depends on $type)
 * @param string     $dest   The Destination URL
 * @param int        $code   The HTTP Status COde
 * @param bool       $force  Whether to force the registration and overwrite an existing redirect
 *
 * @return bool True on success, false on failure
 */
function sudbury_register_redirect( $type, $source, $dest, $code = 302, $force = false ) {
	$registry = get_site_option( 'sudbury_redirect_registry' );

	if ( isset( $registry[ $type ][ $source ] ) && ! $force ) {
		return false;
	}
	if ( ! in_array( $type, array( 'direct', 'admin_page', 'post' ) ) ) {
		_doing_it_wrong( 'sudbury_register_redirect', 'You specified an invalid type of redirect, please use admin_page or direct', '1.0' );

		return false;
	}

	if ( 'post' == $type && is_int( $source ) ) {
		$source = get_current_blog_id() . '-' . $source;
	}

	if ( ! is_string( $source ) ) {
		_doing_it_wrong( 'sudbury_register_redirect', 'You specified an invalid redirect source, please specify a string', '1.0' );

		return false;
	}

	if ( ! is_string( $dest ) ) {
		_doing_it_wrong( 'sudbury_register_redirect', 'You specified an invalid redirect dest, please specify a string', '1.0' );

		return false;
	}

	if ( ! is_numeric( $code ) && 0 <= $code && $code <= 505 ) {
		_doing_it_wrong( 'sudbury_register_redirect', 'You specified an invalid redirect code, please specify a number between 0 and 505', '1.0' );

		return false;
	}

	$registry[ $type ][ $source ] = array( 'dest' => $dest, 'code' => $code );

	return update_site_option( 'sudbury_redirect_registry', $registry );
}


/**
 * Registers a new redirect route from $source to $dest.  Supports admin page redirects too by the admin page slug
 *
 * @param     $type
 * @param     $source
 *
 * @return bool
 */
function sudbury_unregister_redirect( $type, $source ) {
	$registry = get_site_option( 'sudbury_redirect_registry' );

	if ( ! isset( $registry[ $type ][ $source ] ) ) {
		return false;
	}

	if ( ! in_array( $type, array( 'direct', 'admin_page', 'post' ) ) ) {
		_doing_it_wrong( 'sudbury_unregister_redirect', 'You specified an invalid type of redirect, please use admin_page or direct', '1.0' );

		return false;
	}

	if ( ! is_string( $source ) ) {
		_doing_it_wrong( 'sudbury_register_redirect', 'You specified an invalid redirect source, please specify a string', '1.0' );

		return false;
	}

	unset( $registry[ $type ][ $source ] );

	return update_site_option( 'sudbury_redirect_registry', $registry );
}

/**
 * Determines if a blog with the given ID exists
 *
 * @param $blog_id
 *
 * @return bool
 */
function sudbury_blog_exists( $blog_id ) {
	return ( false !== get_blog_details( $blog_id ) );
}

/* Registry for keeping track of Parent, Child and Counterpart Relationships between sites. */

/**
 * Master de-corruption function.  Calling this should fix any broken pointers in the relationships
 */
function sudbury_restructure_sites() {

	$blog_list = get_blogs( array( 'all' => true ) );
	$blogs     = array(); // The real array of blogs, fully loaded and indexed by blog ID instead of by get_blogs order... Note there will be gaps in the indexes
	foreach ( $blog_list as $index => $blog ) {
		switch_to_blog( $blog['id'] );
		$blogs[ $blog['id'] ]                 = $blog;
		$blogs[ $blog['id'] ]['children']     = get_option( 'sudbury_children', array() );
		$blogs[ $blog['id'] ]['counterparts'] = get_option( 'sudbury_counterparts', array() );
		$blogs[ $blog['id'] ]['parent']       = get_option( 'sudbury_parent', 0 );
		restore_current_blog();
	}

	// Ensure that for each blog that has a parent listed, it's parent site also references it as a child
	foreach ( $blogs as $id => $blog ) {
		if ( $blog['parent'] ) { // If there is a parent set check the parent site to see if it lists this site as a child
			if ( ! isset( $blogs[ $blog['parent'] ] ) ) {
				$blogs[ $id ]['parent'] = 0;
			} elseif ( ! in_array( $id, $blogs[ $blog['parent'] ]['children'] ) ) {
				$blogs[ $blog['parent'] ]['children'][] = $id;
			}
		}
	}

	// Ensure that for each blog that has children listed, it's child site also references it as a parent
	foreach ( $blogs as $id => $blog ) {
		if ( ! empty( $blog['children'] ) ) { // Check If there is a child set so that we can safely loop through them
			foreach ( $blog['children'] as $index => $child_site ) {
				if ( $child_site == $id ) {
					unset( $blogs[ $id ]['children'][ $index ] );
				} elseif ( ! isset( $blogs[ $child_site ] ) ) { // delete the reference
					unset( $blogs[ $id ]['children'][ $index ] );
				} elseif ( 0 == $blogs[ $child_site ]['parent'] ) { // The Child Site did not have a parent set
					$blogs[ $child_site ]['parent'] = $id;
				} elseif ( $blogs[ $child_site ]['parent'] != $id ) { // The Child Site was already Linked up to another site so delete this reference
					unset( $blogs[ $id ]['children'][ $index ] );
				}
			}
		}
	}

	// Ensure that all counterparts look to each other.  In order to effectively delete a counterpart relationship you MUST delete both entries or else they will be restored by this script
	foreach ( $blogs as $id => $blog ) {
		if ( ! empty( $blog['counterparts'] ) ) { // Check If there is a child set so that we can safely loop through them
			foreach ( $blog['counterparts'] as $index => $counterpart ) {
				if ( ! isset( $blog[ $counterpart ] ) ) {
					unset( $blogs[ $id ]['counterparts'][ $index ] );
				} elseif ( ! in_array( $id, $blogs[ $counterpart ]['counterparts'] ) ) {
					$blogs[ $counterpart ]['counterparts'][ $counterpart ] = $id;
				}
			}
		}
	}


	// Update the system with the new settings
	foreach ( $blogs as $blog_id => $details ) {
		switch_to_blog( $blog_id );
		update_option( 'sudbury_children', $details['children'] );
		update_option( 'sudbury_counterparts', $details['counterparts'] );
		update_option( 'sudbury_parent', $details['parent'] );
		restore_current_blog();
	}

	return $blogs;
}

add_action( 'sudbury_restructure_sites', 'sudbury_restructure_sites' );

/**
 * Deletes a counterpart relationship between 2 sites
 *
 * @param        $other_blog
 * @param string $this_blog
 *
 * @return bool
 */
function sudbury_delete_counterpart( $other_blog, $this_blog = '' ) {
	if ( '' == $this_blog ) {
		$this_blog = get_current_blog_id();
	}
	// Disallow using blog ID of 0 and make sure that the blogs are different
	if ( 0 == $other_blog || 0 == $this_blog || $this_blog == $other_blog ) {
		return false;
	}

	switch_to_blog( $this_blog );
	$this_counterparts = get_option( 'sudbury_counterparts', array() );
	restore_current_blog();

	switch_to_blog( $other_blog );
	$other_counterparts = get_option( 'sudbury_counterparts', array() );
	restore_current_blog();

	if ( false !== ( $other_index_of_this = array_search( $this_blog, $other_counterparts ) ) ) {
		unset( $other_counterparts[ $other_index_of_this ] );
	}

	if ( false !== ( $this_index_of_other = array_search( $other_blog, $this_counterparts ) ) ) {
		unset( $this_counterparts[ $this_index_of_other ] );
	}

	switch_to_blog( $this_blog );
	update_option( 'sudbury_counterparts', $this_counterparts );
	restore_current_blog();

	switch_to_blog( $other_blog );
	update_option( 'sudbury_counterparts', $other_counterparts );
	restore_current_blog();

	return true;
}


/**
 * Deletes all counterpart relationships for all sites
 *
 * @param $blog_id
 *
 * @return bool
 */
function sudbury_delete_all_counterparts( $blog_id = '' ) {
	if ( '' == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	// Don't use 0 as a blog ID... it's unacceptable
	if ( 0 == $blog_id ) {
		return false;
	}

	switch_to_blog( $blog_id );
	$counterparts = get_option( 'sudbury_counterparts', array() );
	restore_current_blog();

	foreach ( $counterparts as $counterpart ) {
		sudbury_delete_counterpart( $counterpart, $blog_id );
	}

	return true;

}

/**
 * Deletes a parent-child relationship between the two given blogs
 * updates the child's parent to the parent blog's parent
 *
 * @param $parent_blog
 * @param $child_blog
 *
 * @return bool
 */
function sudbury_delete_child( $parent_blog, $child_blog ) { // This is actually redundant because you could just call delete_parent($child_blog, $new_parent))
	// Stop using 0 as a blog ID
	if ( 0 == $parent_blog || 0 == $child_blog ) {
		return false;
	}
	// No do not delete yourself as a child
	if ( $parent_blog == $child_blog ) {
		return false;
	}

	switch_to_blog( $parent_blog );
	$parent_blog_children = get_option( 'sudbury_children', array() );
	restore_current_blog();

	if ( false !== ( $index = array_search( $child_blog, $parent_blog_children ) ) ) {
		unset( $parent_blog_children[ $index ] );

		switch_to_blog( $parent_blog );
		update_option( 'sudbury_children', $parent_blog_children );
		restore_current_blog();

		switch_to_blog( $child_blog );
		update_option( 'sudbury_parent', 0 );
		restore_current_blog();

		return true;
	}

	return false;
}

/**
 * Deletes all the child relationships for the given blog
 * updates the children's parents to the given blog's parent blog
 *
 * @param $blog_id
 *
 * @return bool
 */
function sudbury_delete_all_children( $blog_id ) {

	if ( 0 == $blog_id ) {
		return false;
	}

	switch_to_blog( $blog_id );
	$children = get_option( 'sudbury_children', array() );
	restore_current_blog();

	foreach ( $children as $child ) {
		sudbury_delete_child( $blog_id, $child );
	}

	return true;
}

/**
 * Removes the parent-child relationship given the child blog id
 *
 * @param $blog_id
 *
 * @return bool
 */
function sudbury_delete_parent( $blog_id = '' ) {
	if ( '' == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $blog_id ) {
		return false;
	}

	switch_to_blog( $blog_id );
	$parent_blog_id = get_option( 'sudbury_parent', 0 );
	restore_current_blog();

	switch_to_blog( $parent_blog_id );
	$parent_blog_children = get_option( 'sudbury_children', array() );

	restore_current_blog();

	if ( false !== ( $index = array_search( $blog_id, $parent_blog_children ) ) ) {
		unset( $parent_blog_children[ $index ] );

		switch_to_blog( $parent_blog_id );
		update_option( 'sudbury_children', $parent_blog_children );
		restore_current_blog();

		switch_to_blog( $blog_id );
		update_option( 'sudbury_parent', 0 );
		restore_current_blog();

		return true;
	}

	return false;
}

// When a blog is deleted we need to prevent orphans
add_action( 'delete_blog', 'sudbury_delete_all_counterparts' );

add_action( 'delete_blog', 'sudbury_delete_all_children' );

add_action( 'delete_blog', 'sudbury_delete_parent' );

/**
 * Changes the parent of the given $blog_id to the $new_parent
 *
 * @param        $new_parent
 * @param string $blog_id
 *
 * @return bool
 */
function sudbury_update_parent( $new_parent, $blog_id = '' ) {
	// Use current Blog if blog wasn't specified
	if ( '' == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( 0 == $new_parent || 0 == $blog_id ) {
		return false;
	}

	// Check to make sure that the proposed parent blog and the proposed child blog exist
	if ( ! sudbury_blog_exists( $new_parent ) || ! sudbury_blog_exists( $blog_id ) ) {
		return false;
	}

	// Do not draw that relationship... pain will incur
	if ( $new_parent == $blog_id ) {
		return false;
	}

	// Delete the current Relationship if any
	sudbury_delete_parent( $blog_id );

	// Switch to the proposed child blog
	switch_to_blog( $blog_id );

	// Set the Parent BLog ID to the proposed parent blog id
	update_option( 'sudbury_parent', $new_parent );

	// Go back to the current blog... whatever that was
	restore_current_blog();

	// Switch the the proposed parent blog and update it's list of children to include the proposed child blog
	switch_to_blog( $new_parent );

	// get the list of children
	$children = get_option( 'sudbury_children', array() );

	if ( ! in_array( $blog_id, $children ) ) {

		// Add the proposed child blog to the list of children
		$children[] = $blog_id;
		// A quick Sanity Check, making sure that the parent blog doesn't list itself as a child of itself
		if ( false !== ( $key = array_search( $new_parent, $children ) ) ) {
			// Yeah if you did that then you are wrong...
			unset( $children[ $key ] );
		}
		// set the new list of children
		update_option( 'sudbury_children', $children );
	}

	// Go back to whatever blog we were on
	restore_current_blog();

	// hooray!
	return true;
}

/**
 * Updates the given $blog_id's counterpart list to the given $counterparts
 *
 * @param        $counterparts
 * @param string $blog_id
 *
 * @return array|bool
 */
function sudbury_update_counterparts( $counterparts, $blog_id = '' ) {
	if ( '' == $blog_id ) {
		$blog_id = get_current_blog_id();
	}
	if ( null === $counterparts ) {
		$counterparts = array();
	}

	$counterparts = array_unique( $counterparts ); // remove any duplicates
	if ( ! sudbury_blog_exists( $blog_id ) ) {
		return false;
	}

	sudbury_delete_all_counterparts( $blog_id );

	foreach ( $counterparts as $index => $counterpart ) {
		if ( ! sudbury_blog_exists( $counterpart ) ) {
			unset( $counterparts[ $index ] );
			continue;
		}
		switch_to_blog( $counterpart );
		$other_counterparts = get_option( 'sudbury_counterparts', array() );
		if ( ! in_array( $blog_id, $other_counterparts ) ) {
			$other_counterparts[] = $blog_id;
			update_option( 'sudbury_counterparts', $other_counterparts );
		}
		restore_current_blog();
	}

	switch_to_blog( $blog_id );
	update_option( 'sudbury_counterparts', $counterparts );
	restore_current_blog();

	return $counterparts;
}


function sudbury_add_counterpart( $counterpart, $blog_id = '' ) {
	if ( '' == $blog_id ) {
		$blog_id = get_current_blog_id();
	}
	if ( is_int( $counterpart ) && $counterpart > 0 ) {
		return false;
	}

	if ( ! sudbury_blog_exists( $blog_id ) ) {
		return false;
	}

	switch_to_blog( $blog_id );

	$counterparts = get_option( 'sudbury_counterparts', array() );

	if ( ! in_array( $counterpart, $counterparts ) ) {
		$counterparts[] = $counterpart;
		$success        = sudbury_update_counterparts( $counterparts, $blog_id );
	} else {
		$success = false;
	}

	restore_current_blog();

	return $success;
}

/**
 * Creates all the relationships for a given blog using the $_POST data
 *
 * @param $blog_id
 */
function sudbury_create_relationships( $blog_id ) {
	switch_to_blog( $blog_id );
	// If they Don't already exist add the 3 options
	add_option( 'sudbury_parent', 0 );
	add_option( 'sudbury_counterparts', array() );
	add_option( 'sudbury_children', array() );


	if ( isset( $_POST['sudbury_parent'] ) ) {
		$parent = intval( $_POST['sudbury_parent'] );
		sudbury_update_parent( $parent );
	}

	if ( isset( $_POST['sudbury_counterparts'] ) ) {
		$counterparts = $_POST['sudbury_counterparts'];

		if ( ! is_array( $counterparts ) ) {
			$counterparts = array( intval( $counterparts ) );
		}

		sudbury_update_counterparts( $counterparts );
	}
	restore_current_blog();
}

add_action( 'wpmu_new_blog', 'sudbury_create_relationships' );

/**
 * Returns the Parent Blog ID of the specified blog or the Parent of the current blog if $blog_id is omitted
 *
 * @param bool|int $blog_id The ID of the blog to get the parent of
 *
 * @return int THe ID of the parent Site
 */
function sudbury_get_site_parent( $blog_id = false ) {
	if ( false == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	return (int) get_blog_option( $blog_id, 'sudbury_parent', false );
}

/**
 * Returns the Counterpart Blog IDs of the specified blog or the Counterparts of the current blog if $blog_id is omitted
 *
 * @param bool|int $blog_id The ID of the Blog to get the counterparts of
 *
 * @return array An Array of Blog IDs corresponding to the counterparts
 */
function sudbury_get_site_counterparts( $blog_id = false ) {
	if ( false == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	return get_blog_option( $blog_id, 'sudbury_counterparts', array() );
}

/**
 * Returns the Child Blog IDs of the specified blog or the Children of the current blog if $blog_id is omitted
 *
 * @param bool|int $blog_id The ID of the blog to get the parent of
 *
 * @return array An Array of Blog IDs corresponding to the children blogs
 */
function sudbury_get_site_children( $blog_id = false ) {
	if ( false == $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	return get_blog_option( $blog_id, 'sudbury_children', array() );
}

/**
 * Returns a List of all the related Blogs in order of Parent, Children, then counterparts
 *
 * @param bool|int $blog_id The ID of the blog to get the related blogs of
 *
 * @return array An array of all related blogs ordered by parent, then children, then counterparts
 */
function sudbury_get_site_related( $blog_id = false ) {
	$return = array();
	if ( $parent = sudbury_get_site_parent( $blog_id ) ) {
		$return[] = $parent;
	}

	$return = array_merge( $return, sudbury_get_site_children( $blog_id ), sudbury_get_site_counterparts( $blog_id ) );

	return array_unique( $return );
}

/**
 * Echos the hierarchical representation (path) to the given $blog_id
 *  Example: Department of Public Works >> Engineering Department
 *
 * Note: This is not intended to be used for URLs, just UI components
 *
 * @param mixed $blog_id
 * @param array $args
 *
 * @return string
 */
function sudbury_the_relationship_path( $blog_id = false, $args = array() ) {
	$defaults = array( 'sep' => ' / ', 'echo' => true, 'links' => true );

	$args = wp_parse_args( $args, $defaults );

	$args['getall'] = true; // regardless of what it was before, must be true

	if ( isset( $blog_id->BLOG_ID ) ) {
		$blog_id = $blog_id->BLOG_ID;
	} else if ( false === $blog_id || is_object( $blog_id ) ) {
		$blog_id = get_current_blog_id();
	}

	$blogs = sudbury_get_the_relationship_path( $blog_id, $args );

	foreach ( $blogs as $index => $blog ) {
		if ( $args['links'] ) {
			$blogs[ $index ] = sprintf( '<a href="%s" class="site-link">%s</a>', $blog->path, $blog->blogname );
		} else {
			$blogs[ $index ] = $blog->blogname;
		}
	}
	$formatted = implode( $args['sep'], array_reverse( $blogs ) );

	if ( $args['echo'] ) {
		echo $formatted;
	}

	return $formatted;
}

/**
 * Gets a list of the current and all parent blogs
 *
 * @param       $id
 * @param array $args
 *
 * @return mixed
 * @recursive
 */
function sudbury_get_the_relationship_path( $id, $args = array() ) {
	$defaults = array( 'getall' => true, 'blogs' => array() );
	$args     = wp_parse_args( $args, $defaults );
	switch_to_blog( $id );
	$parent = get_option( 'sudbury_parent', 0 );
	restore_current_blog();
	$args['blogs'][] = get_blog_details( $id, $args['getall'] );

	if ( 0 == $parent ) {
		return $args['blogs'];
	} else {
		return sudbury_get_the_relationship_path( $parent, $args );
	}
}

/**
 * Gets the metadata for the relationship from the $current_blog to the $other_blog
 *
 * @param bool|int    $other_blog   The other blog
 * @param bool|int    $current_blog The current Blog (Defaults to the Current Blog ID)
 * @param bool|string $relation     The Relation to show... IE if $current_blog and $other_blog are both counterparts and in a
 *                                  parent/child relationship then use this to select the relationship to get the meta for. Will Defaukt to
 *                                  parent, then child, then counterpart if false
 *
 * @return array An associative array of the meta, empty array by default
 */
function sudbury_get_relationship_meta( $other_blog = false, $current_blog = false, $relation = false ) {
	if ( false !== $current_blog ) {
		switch_to_blog( $current_blog );
	}
	$meta = get_option( 'sudbury_relationship_meta', array() );
	if ( false === $other_blog ) {
		$return = $meta;
	} else {
		$return = array();
		if ( isset( $meta[ $other_blog ] ) && ! empty( $meta[ $other_blog ] ) ) {
			if ( false === $relation ) {
				$types = array( 'parent', 'child', 'counterpart' );
				foreach ( $types as $type ) {
					if ( isset( $meta[ $other_blog ][ $type ] ) ) {
						$return = $meta[ $other_blog ][ $type ];
						break;
					}
				}
			} else {
				$return = $meta[ $other_blog ][ $relation ];
			}
		}
	}
	restore_current_blog();

	return $return;
}

/**
 * sorts the given list of sites alphabetically first into a hierarchical tree joined together by the 'children' array of each level
 *
 * @param       $sites
 * @param array $args
 *
 * @return mixed
 * @bugs Known issue: only works with a depth of 1, but that is OK because we will not have departments with more than 1 in depth
 */
function sort_sites( $sites, $args = array() ) {
	$defaults = array( 'sort' => 'normal' );
	$args     = wp_parse_args( $args, $defaults );


	// foreach site
	for ( $i = 0; $i < count( $sites ); $i ++ ) {

		// swap until you get to a point where the current element is in an a, B, c formation
		for ( $k = $i; $k > 1 && strcasecmp( $sites[ $k ]['title'], $sites[ $k - 1 ]['title'] ) < 0; $k -- ) {
			$temp            = $sites[ $k ];
			$sites[ $k ]     = $sites[ $k - 1 ];
			$sites[ $k - 1 ] = $temp;
		}

		if ( 'hierarchical' == $args['sort'] && ! empty( $sites[ $i ]['children'] ) ) {

			foreach ( $sites[ $i ]['children'] as $cindx => $id ) {
				$indx                              = index_of_site_id( $sites, $id );
				$sites[ $i ]['children'][ $cindx ] = $sites[ $indx ];
				unset( $sites[ $indx ] );
			}
			$sites[ $i ]['children'] = sort_sites( $sites[ $i ]['children'] );
		}
	}

	return $sites;
}

/**
 * Sorts the given list of sites by title
 *
 * @param $sites
 *
 * @return mixed
 */
function sort_sites_by_title( $sites ) {
	uasort( $sites, 'sort_sites_by_title_comparator' );

	return $sites;
}

/**
 * Helper function for sort_sites_by_title that acts a a comparator of 2 wordpress
 * site detail arrays $a and $b and compares them by title
 *
 * @param $a
 * @param $b
 *
 * @return int
 */
function sort_sites_by_title_comparator( $a, $b ) {
	return strcmp( $a['title'], $b['title'] );
}

/**
 * returns the index of the site with the given $id in the given $sites array
 *
 * @param $sites
 * @param $id
 *
 * @return bool|int|string
 */
function index_of_site_id( $sites, $id ) {
	foreach ( $sites as $index => $site ) {
		if ( $site['id'] == $id ) {
			return $index;
		}
	}

	return false;
}

/**
 * Gets a list of guest posts for the given $post_id.  It will return everything, including the Root Post
 * and the Post ID requested regardless of whether the $post_id is a guest post or a root post
 *
 * @param int $post_id Th ID of the post on the current blog to look up guest posts for, does not have to be a root post
 *
 * @return array Key-Value hash of blog_id => post_id for each guest/root post
 */
function sudbury_get_guest_posts( $post_id ) {
	$guests = get_post_meta( $post_id, '_itgMultiPost', true );

	return $guests;
}

/**
 * Determines if the Post is either a guest or root post
 *
 * @param int $post_id the Id of the post on the current blog to check
 *
 * @return bool true if this post is in the multi-post system otherwise false
 */
function sudbury_is_multi_post( $post_id, $blog_id = false ) {
	return sudbury_sharing_is_shared( $post_id, $blog_id );
}

/**
 * Determines if the current post is a root post
 *
 * @param int $post_id The ID of the post to check
 *
 * @return bool Whether the post is a root post in the multi=-post system or not
 */
function sudbury_is_root_post( $post_id, $blog_id = false ) {
	return sudbury_sharing_is_root_post( $post_id, $blog_id );
}

/**
 * Determines if the given post is a guest post
 *
 * @param int $post_id The ID of the post
 *
 * @return bool Whether the post is a guest post or not
 */
function sudbury_is_guest_post( $post_id, $blog_id = false ) {
	return sudbury_sharing_is_guest_post( $post_id, $blog_id );
}

/**
 * Gets a list of blogs/sites from the multisite wp_blogs table
 *
 * @param array $args
 *
 * @return array|mixed
 */
function get_blogs( $args = array() ) {
	global $wpdb;
	global $table_prefix;

	if ( ! is_multisite() ) {
		_doing_it_wrong( "get_blogs", "This is not a multisite install!", "The Sudbury Plugin Beta" );

		return array();
	}

	$defaults = array( 'all' => false, 'sort' => 'normal', 'type' => '', 'exclude' => array(), 'filters' => array() );


	$args = wp_parse_args( $args, $defaults );
	//get blog list

	if ( ! is_array( $args['exclude'] ) ) {
		$args['exclude'] = array( $args['exclude'] );
	}
	if ( 'normal' == $args['sort'] ) {
		$orderby = 'blog_id';
	} elseif ( 'shortname' == $args['sort'] ) {
		$orderby = 'path';
	}


	$blogs = $wpdb->get_col( $wpdb->prepare( "SELECT `blog_id` FROM `{$wpdb->blogs}` WHERE " . ( $args['all'] ? ' 1=1 ' : "  public = '1' AND archived = '0' AND mature = '0' AND spam = '0'" ) . "ORDER BY %s DESC", $orderby ) );
	$sites = array();

	//make sure there are blogs
	if ( $blogs ) {

		foreach ( $blogs as $blog ) {

			// If the blog_id is excluded skip
			if ( in_array( $blog, $args['exclude'] ) ) {
				continue;
			}

			switch_to_blog( $blog );

			$info          = get_blog_details( $blog, true );
			$site['id']    = $blog;
			$site['url']   = $info->siteurl;
			$site['title'] = $info->blogname;
			$site['types'] = get_option( 'sudbury_types' );

			$skip = false;
			foreach ( $args['filters'] as $filter ) {
				if ( ! preg_match( $filter[1], $site[ $filter[0] ] ) ) {
					$skip = true;
					break;
				}
			}
			if ( $skip ) {
				continue;
			}

			if ( $args['type'] && ! in_array( $args['type'], $site['types'] ) ) {
				restore_current_blog();
				continue;
			}

			$site['children']     = get_option( 'sudbury_children' );
			$site['parent']       = get_option( 'sudbury_parent' );
			$site['counterparts'] = get_option( 'sudbury_counterparts' );

			$site = apply_filters( 'sudbury_get_blog_menu_options', $site );

			restore_current_blog();
			$sites[] = $site;
			// Get all the information about this blog and add it to the array
		}

		if ( 'hierarchical' == $args['sort'] ) {
			$sites = sort_sites( $sites, $args );
		} elseif ( 'longname' == $args['sort'] ) {
			$sites = sort_sites_by_title( $sites );
		}
	}

	return $sites;

}

/**
 * Gets all the news articles published to the Network
 *
 * @param array $args
 *
 * @return array
 */
function sudbury_get_all_news( $args = array() ) {

	$posts = network_query_posts( array( 'post_type' => 'post' ) );

	return $posts;


}

/**
 * Renders an On/Off Switch with the given state and name
 *
 * @param bool        $checked Whether it should be checked
 * @param string      $name    The HTML Name of the checkbox
 * @param string|bool $id      The HTML ID of the checkbox (Default is the $name and some gibberish)
 */
function sudbury_on_off_switch( $checked, $name, $id = false ) {
	$id = $id ? $id : "{$name}_" . random_string( 6 );
	?>
	<div class="onoffswitch">
		<input type="checkbox" name="<?php echo $name; ?>" class="onoffswitch-checkbox"
			   id="<?php echo $id; ?>" <?php checked( $checked ); ?>>
		<label class="onoffswitch-label" for="<?php echo $id; ?>">
			<span class="onoffswitch-inner"></span>
			<span class="onoffswitch-switch"></span>
		</label>
	</div>
<?php }


/**
 * Gets the status of the Sudbury System
 *
 * @param array $stati An array of existing Stati
 *
 * @return string
 */
function sudbury_get_status( $stati = array() ) {

	if ( has_alerts() ) {
		$stati[] = 'High Alert';
	}

	if ( ! is_production_server() ) {
		$stati[] = 'Development';
	}

	if ( is_debug() ) {
		$stati[] = 'Debug';
	}

	if ( empty( $stati ) ) {
		$stati[] = 'Operational';
	}

	if ( is_user_logged_in() ) {
		$stati[] = 'Logged In';
	}

	if ( is_internal() ) {
		$stati[] = "Internal";
	}

	if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
		$stati[] = 'Uncached';
	}

	return implode( ', ', $stati );
}


/**
 * Executes the given callable suppressing any output that it produces... I feel like there should be a WordPress func for this
 *
 * Note this suppresses ALL output, not just error output like @
 *
 * @param $callable
 */
function muzzle( $callable ) {
	ob_start();
	$callable();
	ob_clean();
}


/**
 * Appends the SUDBURY_VERSION into the query string with the given query arg name
 *
 * @param string $url   The URL to modify
 * @param string $param The Query Arg field to push it into
 *
 * @return string The New URL
 */
function sudbury_framework_url( $url, $param = 'v' ) {
	$url = add_query_arg( array( $param => SUDBURY_VERSION ), $url );

	return $url;
}
