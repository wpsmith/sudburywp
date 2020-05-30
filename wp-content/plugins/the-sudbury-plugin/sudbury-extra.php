<?php
/**
 * A Bunch of Misc Stuff mostly relating to Legacy Compatibility
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Sudbury_Plugin
 */

/**
 * if given a WebEditor representation of a Department, will return a Wordpress
 * representation of it's type (Department, Committee, Informational, ect.)
 * otherwise returns the given WebEditor representation of the Department
 *
 * @param $args
 *
 * @return array
 */
function sudbury_types_filter( $args ) {
	if ( isset( $args['dept'] ) ) {
		$types = array();
		if ( $args['dept']['is_committee'] ) {
			$types[] = 'committee';
		}
		if ( $args['dept']['is_department'] ) {
			$types[] = 'department';
		}

		return $types;
	}

	return $args;
}

add_filter( 'sudbury_update_settings_sudbury_types', 'sudbury_types_filter', 999, 1 );

/**
 * Will parse the given Department into an array of types
 * (Department, Committee, Informational) if looking for
 * the default value for the sudbury_types option, else will
 * return the $to_option argument which is equivalent to the
 * function saying "I Don't know what the default value should be"
 *
 * @param $to_option
 * @param $from_option
 * @param $dept
 *
 * @return array
 */
function sudbury_types_default_option( $to_option, $from_option, $dept ) {
	if ( 'sudbury_types' == $to_option ) {
		return sudbury_types_filter( array( 'dept' => $dept ) );
	}

	return $to_option;
}

add_filter( 'sudbury_option_default', 'sudbury_types_default_option', 10, 3 );

/**
 * Resolves the WebEditor Option names to the more standardized Wordpress Option Names
 *
 * @param $webeditor_option
 *
 * @return mixed
 */
function sudbury_resolve_to_wordpress_option( $webeditor_option ) {
	$resolver = array(
		'from_dept_head_text'  => 'sudbury_dept_head_message',
		'BoardMembershipKey'   => 'sudbury_board_membership_key',
		'from_dept_head_start' => 'sudbury_dept_head_message_start',
		'from_dept_head_end'   => 'sudbury_dept_head_message_end'
	);

	return ( isset( $resolver[ $webeditor_option ] ) ? $resolver[ $webeditor_option ] : $webeditor_option );
}

add_filter( 'sudbury_resolve_to_wordpress_option', 'sudbury_resolve_to_wordpress_option', 999, 1 );

/**
 * Resolves the Wordpress Option names to the legacy WebEditor Option Names
 *
 * @param $wp_option
 *
 * @return mixed
 */
function sudbury_resolve_to_webeditor_option( $wp_option ) {
	$resolver = array(
		'sudbury_dept_head_message'       => 'from_dept_head_text',
		'sudbury_board_membership_key'    => 'BoardMembershipKey',
		'sudbury_dept_head_message_start' => 'from_dept_head_start',
		'sudbury_dept_head_message_end'   => 'from_dept_head_end'
	);

	return ( isset( $resolver[ $wp_option ] ) ? $resolver[ $wp_option ] : $wp_option );
}

add_filter( 'sudbury_resolve_to_webeditor_option', 'sudbury_resolve_to_webeditor_option', 999, 1 );
/**
 * @param $wp_name
 *
 * @return mixed
 */
function sudbury_resolve_to_webeditor_name( $wp_name ) {
	$resolver = array(
		'transferstation' => 'Transfer Station',
		'spsfacilities'   => 'SPS Facilities',
		''                => 'default'
	);

	return ( isset( $resolver[ $wp_name ] ) ? $resolver[ $wp_name ] : $wp_name );
}

add_filter( 'sudbury_resolve_to_webeditor_name', 'sudbury_resolve_to_webeditor_name', 999, 1 );
/**
 * @param $webeditor_name
 *
 * @return mixed
 */
function sudbury_resolve_to_wordpress_name( $webeditor_name ) {
	$resolver = array(
		'Transfer Station' => 'transferstation',
		'SPS Facilities'   => 'spsfacilities',
		'default'          => '',
	);

	return ( isset( $resolver[ $webeditor_name ] ) ? $resolver[ $webeditor_name ] : $webeditor_name );
}

add_filter( 'sudbury_resolve_to_wordpress_name', 'sudbury_resolve_to_wordpress_name', 999, 1 );

/**
 * @param $wp_name
 *
 * @return mixed
 */
function sudbury_resolve_to_webeditor_long_name( $wp_name ) {
	$resolver = array(
		'transferstation' => 'Transfer Station',
		''                => 'default',
	);

	return ( isset( $resolver[ $wp_name ] ) ? $resolver[ $wp_name ] : $wp_name );
}

add_filter( 'sudbury_resolve_to_webeditor_long_name', 'sudbury_resolve_to_webeditor_long_name', 999, 1 );
/**
 * @param $webeditor_name
 *
 * @return mixed
 */
function sudbury_resolve_to_wordpress_long_name( $webeditor_name ) {
	$resolver = array( /* 'webeditor_nane' => 'wp_name', */ );

	return ( isset( $resolver[ $webeditor_name ] ) ? $resolver[ $webeditor_name ] : $webeditor_name );
}

add_filter( 'sudbury_resolve_to_wordpress_long_name', 'sudbury_resolve_to_wordpress_long_name', 999, 1 );

function sudbury_extract_event_datetimes( $data ) {
	if ( isset( $data['_sudbury_event_start_datetime'] ) ) {
		$data['_sudbury_event_start_date'] = sudbury_validation_date( $data['_sudbury_event_start_datetime'], null, 'm/d/Y H:i:s' );
		$data['_sudbury_event_start_time'] = sudbury_validation_time( $data['_sudbury_event_start_datetime'], null, 'm/d/Y H:i:s' );
		unset( $data['_sudbury_event_start_datetime'] );
	}

	if ( isset( $data['_sudbury_event_end_datetime'] ) ) {
		$data['_sudbury_event_end_date'] = sudbury_validation_date( $data['_sudbury_event_end_datetime'], null, 'm/d/Y H:i:s' ); // Converts compound mm/dd/yyyy hh:mm:ss to yyyy-mm-dd (MySQL)
		$data['_sudbury_event_end_time'] = sudbury_validation_time( $data['_sudbury_event_end_datetime'], null, 'm/d/Y H:i:s' ); // Converts compound mm/dd/yyyy hh:mm:ss to hh:mm:ss
		unset( $data['_sudbury_event_end_datetime'] );
	}

	return $data;
}

add_filter( 'sudbury_event_args_extract', 'sudbury_extract_event_datetimes', 10, 1 );
$event_columns          = array();
$event_postmeta_columns = array();

function sudbury_multi_post_meta_key_copy_prevent_event_copy( $value, $key ) {
	global $wpdb, $event_columns, $event_postmeta_columns;
	if ( empty( $event_columns ) ) {
		$event_array   = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->base_prefix . 'em_events LIMIT 1', ARRAY_A );
		$event_columns = array_keys( $event_array );
	}
	if ( empty( $event_meta_columns ) ) {
		load_event_postmeta_columns();
	}
	if ( in_array( $key, $event_postmeta_columns ) ) {
		return false;
	}

	return $value;
}

function load_event_postmeta_columns( $manual_event_columns = array() ) {
	global $event_columns, $event_postmeta_columns;

	if ( empty( $event_columns ) ) {
		if ( empty( $manual_event_columns ) ) {
			return false;
		} else {
			$event_columns = $manual_event_columns;

		}
	}
	$event_postmeta_columns = array(); // clear the existing version
	foreach ( $event_columns as $key ) {
		$event_postmeta_columns[] = "_$key";
	}

	return true;
}

add_filter( 'multi-post-meta-key-copy', 'sudbury_multi_post_meta_key_copy_prevent_event_copy', 10, 2 );

function sudbury_default_event_duration_update_settings_filter( $setting ) {
	$setting['value'] = $setting['value'] * 60;

	return $setting;
}

add_filter( 'sudbury_update_settings_sudbury_default_event_duration', 'sudbury_default_event_duration_update_settings_filter' );


function sudbury_unregister_widgets_default( $option_name ) {
	if ( 'sudbury_unregister_widgets' == $option_name ) {
		return true;
	}

	return $option_name;
}

add_filter( 'sudbury_default_option', 'sudbury_unregister_widgets_default' );

/**
 * Allows you to change whether the current request should be shown internal content (Internal Menu, Internal Website, ect)
 *
 * @param bool $allow Whether the Sudbury Framework thinks they should be allowed
 *
 * @return bool What you think about them being allowed
 */
function sudbury_override_is_internal( $allow ) {
	$ip = $_SERVER['REMOTE_ADDR'];

	// replace the x's with ip addresses
	$deny_list  = array( 'x.x.x.x', 'x.x.x.x', 'x.x.x.x' );
	$allow_list = array( 'x.x.x.x', 'x.x.x.x', 'x.x.x.x' );

	if ( in_array( $ip, $deny_list ) ) {
		return false;
	}

	if ( in_array( $ip, $allow_list ) ) {
		return true;
	}


	return $allow;
}
// Uncomment this line to enable the above filter
//add_filter( 'sudbury_is_internal', 'sudbury_override_is_internal' );

/**
 * Allows Non-Breaking spaces in the editor
 * @see https://core.trac.wordpress.org/ticket/23778
 */ 
function sudbury_allow_nbsp_in_tinymce( $mceInit ) {
	$mceInit['entities'] = '160,nbsp,38,amp,60,lt,62,gt';	
	$mceInit['entity_encoding'] = 'named';	
	return $mceInit;
}
add_filter( 'tiny_mce_before_init', 'sudbury_allow_nbsp_in_tinymce' );