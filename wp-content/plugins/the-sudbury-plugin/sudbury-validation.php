<?php
/**
 * Settings Registration and Validation for the sudbury settings API... We don't use the Settings API so some of this code is irrelevant
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Sudbury_Plugin_Settings
 */

/**
 * Returns the Default Options For the Sudbury Plugin Features
 * @todo This filter is messed up... it shouldn'y filter for the option name, it should filter for a value
 *
 * @param $option The Option name from filter: sudbury_option_default
 *
 * @return mixed The Default option, if not found then returns null
 */
function sudbury_option_defaults( $option ) {
	$options = array(
		// Contact Info
		'sudbury_address'                    => '',
		'sudbury_location_id'                => '',
		'sudbury_email'                      => '',
		'sudbury_fax'                        => '',
		'sudbury_office_hours'               => '',
		'sudbury_telephone'                  => '',
		// Legacy Formatting
		'sudbury_banner_color'               => '',
		'sudbury_banner_note'                => '',
		// Core
		'sudbury_board_membership_key'       => '',
		'sudbury_description_paragraph'      => '',
		'sudbury_types'                      => array(),
		'sudbury_archived_message'           => '',
		'sudbury_counterparts'               => array(),
		'sudbury_children'                   => array(),
		'sudbury_relationship_meta'			 => array(),
		'sudbury_redirect_url'               => '',
		//'sudbury_parent'					 => No Default

		// Legacy
		'sudbury_department_folder'          => '',
		// Events
		'sudbury_default_event_start_time'   => '20:00',
		'sudbury_default_event_duration'     => 120,
		'sudbury_default_event_days_between' => 7,
		'sudbury_default_event_location'     => 1,
		// Social
		'facebook_url'                       => '',
		'twitter_url'                        => '',
		'youtube_url'                        => '',
		'google_plus_url'                    => '',
	);

	if ( isset( $options[ $option ] ) ) {
		return $options[ $option ];
	}

	return null;
}

add_filter( 'sudbury_option_default', 'sudbury_option_defaults' );

/**
 * Registers all the Settings used by the sudbury plugin:
 * @called: during 'admin_init' by sudbury_admin_init()
 */
function sudbury_settings_init() {


	// Contact
	register_setting(
		'sudbury_options',
		'sudbury_address'
	);
	add_filter( 'pre_update_option_sudbury_address', 'sudbury_validation_html', 10, 3 );

	register_setting(
		'sudbury_options',
		'sudbury_email'
	);
	add_filter( 'pre_update_option_sudbury_email', 'sudbury_validation_email', 2 );

	register_setting(
		'sudbury_options',
		'sudbury_fax'
	);
	add_filter( 'pre_update_option_sudbury_fax', 'sudbury_validation_telephone', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_location_id'
	);
	add_filter( 'pre_update_option_sudbury_location_id', 'sudbury_validation_is_location', 2 );

	register_setting(
		'sudbury_options',
		'sudbury_office_hours'
	);
	add_filter( 'pre_update_option_sudbury_office_hours', 'sudbury_validation_html', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_telephone'
	);
	add_filter( 'pre_update_option_sudbury_telephone', 'sudbury_validation_telephone', 10, 2 );

	// Legacy Formatting

	register_setting(
		'sudbury_options',
		'sudbury_banner_color'
	);
	add_filter( 'pre_update_option_sudbury_banner_color', 'sudbury_validation_css_color_code', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_banner_note'
	);
	add_filter( 'pre_update_option_sudbury_banner_note', 'sudbury_validation_text', 10, 2 );


	// Core
	register_setting(
		'sudbury_options',
		'sudbury_board_membership_key'
	);
	add_filter( 'pre_update_option_sudbury_board_membership_key', 'sanitize_text_field', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_types'
	);
	add_filter( 'pre_update_option_sudbury_types', 'sudbury_validation_types', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_sudbury_description_paragraph'
	);
	add_filter( 'pre_update_option_sudbury_description_paragraph', 'sudbury_validation_html', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_parent'
	);
	add_filter( 'pre_update_option_sudbury_parent', 'sudbury_validation_cancel_and_update_parent', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_children'
	);
	add_filter( 'pre_update_option_sudbury_children', 'sudbury_validation_cancel_and_update_children', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_relationship_meta'
	);
	add_filter( 'pre_update_option_sudbury_relationship_meta', 'sudbury_validation_cancel_and_update_relationship_meta', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_counterparts'
	);
	add_filter( 'pre_update_option_sudbury_counterparts', 'sudbury_validation_cancel_and_update_counterparts', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_archived_message'
	);
	add_filter( 'pre_update_option_sudbury_archived_message', 'sudbury_validation_html', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_redirect_url'
	);
	add_filter( 'pre_update_option_sudbury_redirect_url', 'sanitize_url', 10, 2 );

	// Events
	register_setting(
		'sudbury_options',
		'sudbury_default_event_location'
	);
	add_filter( 'pre_update_option_sudbury_default_event_location', 'sudbury_validation_is_location', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_default_event_days_between'
	);
	add_filter( 'pre_update_option_sudbury_default_event_days_between', 'sudbury_validation_num', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_default_event_start_time'
	);
	add_filter( 'pre_update_option_sudbury_default_event_start_time', 'sudbury_validation_time', 10, 2 );

	register_setting(
		'sudbury_options',
		'sudbury_default_event_duration'
	);
	add_filter( 'pre_update_option_sudbury_default_event_duration', 'sudbury_validation_num', 10, 2 );

	// Social
	register_setting(
		'sudbury_options',
		'sudbury_facebook_url'
	);
	add_filter( 'pre_update_option_sudbury_facebook_url', 'esc_url' );

	register_setting(
		'sudbury_options',
		'sudbury_twitter_url'
	);
	add_filter( 'pre_update_option_sudbury_twitter_url', 'esc_url' );

	register_setting(
		'sudbury_options',
		'sudbury_youtube_url'
	);
	add_filter( 'pre_update_option_sudbury_youtube_url', 'esc_url' );

	register_setting(
		'sudbury_options',
		'sudbury_google_plus_url'
	);
	add_filter( 'pre_update_option_sudbury_google_plus_url', 'esc_url' );

}


/**
 * No Validation occurs
 *
 * @param      $newval
 * @param bool $oldval
 *
 * @return mixed
 */
function sudbury_validation_none( $newval, $oldval = false ) { //Eddie, why Is $oldval listed as an argument if it isn't used? - Matt
	return $newval;
}

/**
 * Strips any character that is not a letter, number, space, or \ / $ - _
 *
 * @param      $newval
 * @param bool $oldval
 *
 * @return mixed
 */
function sudbury_validation_simple_key( $newval, $oldval = false ) { //Eddie, why Is $oldval listed as an argument if it isn't used? - Matt
	return preg_replace( '/[^a-z0-9_-]+/i', '', $newval );
}

/**
 * I Like this one: It checks to see if $newval is a valid Friendly CSS Color Name like 'black'.  If so it returns the Hex representation of that color. If not a color name it validates that it is a hexadecimal color code.  if not it checks if it is an RGB(a) sequence.  If none are true it returns $oldval
 *
 * @param $newval
 * @param $oldval
 *
 * @return string
 */
function sudbury_validation_css_color_code( $newval, $oldval = false ) {
	global $css_color_names;

	if ( isset( $css_color_names[ strtolower( $newval ) ] ) ) {
		return $css_color_names[ strtolower( $newval ) ];
	} else {
		if ( ! strstartswith( '#', $newval ) && ctype_xdigit( $newval ) && strlen( $newval ) <= 8 ) {
			return '#' . $newval;
		} else {
			if ( strstartswith( '#', $newval ) && ctype_xdigit( substr( $newval, 1 ) ) && strlen( $newval ) <= 9 ) {
				return $newval;
			} else {
				if ( preg_match( '/^\s*rgb(a)?\s*\((\s*[0-9]{0,3}(\.[0-9]+)?\s*(,)?){3,4}\s*\)\s*$/i', $newval ) ) { // Regex to validate rgb(a) in almost any format
					return $newval;
				}
			}
		}
	}

	return $oldval;
}

/**
 * Removes any character that is not a letter, number, space, or \ / $ - _
 *
 * @param $newval
 * @param $oldval
 *
 * @return mixed
 */
function sudbury_validation_network_path( $newval, $oldval = false ) {
//Eddie, why is it necessary to list $oldval as an param? -Matt
//Matt, it was easy to copy and paste and if someone wanted to change code later and needed $oldval they don't have to dig up the add_filter call
	return preg_replace( '/[^a-z0-9$\\\ _\/ -]+/i', '', $newval );
}

/**
 * No proper Implementation at this moment
 *
 * @param $newval
 * @param $oldval
 *
 * @return mixed
 */
function sudbury_validation_html( $newval, $oldval = false ) {
	// Squash those evil tags, then autop them :/
	$return = wp_kses_post( stripslashes( $newval ) );

	return $return;
}

/**
 * Will check if the $newval is an array and only contains the allowed types
 *
 * @param $newval
 * @param $oldval
 *
 * @return mixed
 */
function sudbury_validation_types( $newval, $oldval = false ) {
	if ( ! is_array( $newval ) ) {
		sudbury_log( "option: sudbury_types is not an Array!" );

		return $oldval;
	}

	$allowed = get_site_option( 'sudbury_allowedtypes', array() );
	foreach ( $newval as $val ) {
		if ( ! in_array( $val, $allowed ) ) {
			sudbury_log( "option: sudbury_types contains " . $val . " which is not allowed by sudbury_allowedtypes which is/are " . implode( ',', $allowed ) );

			return $oldval;
		}
	}


	return $newval;
}

/**
 * Runs a sanitize_text_field on the new value
 *
 * @param      $newval
 * @param bool $oldval
 *
 * @return string
 */
function sudbury_validation_text( $newval, $oldval = false ) {
	return sanitize_text_field( $newval );
}


/**
 * Performs a sanitize_text_field but preserves newlines as litteral newlines (\n)  Call wpautop() to convert to html breaks when rendering in theme
 *
 * @param      $newval
 * @param bool $oldval
 *
 * @return string
 */
function sudbury_validation_textarea( $newval, $oldval = false ) {
	$newline = '=+=NEWLINE=+=';
	$newval  = str_replace( "\n", $newline, $newval );
	$newval  = sanitize_text_field( $newval );
	$newval  = str_replace( $newline, "\n", $newval );

	return $newval;
}


/**
 * @param      $newval
 * @param bool $oldval
 *
 * @return bool
 */
function sudbury_validation_email( $newval, $oldval = false ) {
	if ( is_email( $newval ) || '' == $newval ) {
		return $newval;
	}

	return $oldval;
}

/**
 * @param      $newval
 * @param bool $oldval
 *
 * @return string
 */
function sudbury_validation_telephone( $newval, $oldval = false ) {
	if ( '' != $newval ) {
		return sudbury_format_phone( sudbury_sanitize_int( $newval ) . '' );
	}

	return '';
}

/**
 * @param      $newval
 * @param bool $oldval
 *
 * @return bool|DateTime|null|string
 */
function sudbury_validation_date( $newval, $oldval = false, $format = 'd/M/Y H:i:s' ) {
    sudbury_log("Validate date: '$newval' against '$oldval' with format '$format'");
	if ( null == $newval ) {
		return $oldval;
	}

	$time = trim( $newval );
    $parsed = date_create_from_format( $format, $time );
    if ( $parsed === false ) {
       sudbury_log("Validation Failed for $newval, returning $oldval");
       return $oldval;

    }
	sudbury_log($parsed->format('Y-m-d'));
	return $parsed->format('Y-m-d');
}

/**
 * Validates date for format 07/22/2013 16:03 to 00:00:00
 *
 * @param      $newval
 * @param bool $oldval
 *
 * @return bool|DateTime|null|string
 */
function sudbury_validation_time( $newval, $oldval = false, $format = 'd/M/Y H:i:s' ) {
	if ( null == $newval ) {
		return $oldval;
	}

	$time = trim( $newval );
    $parsed = date_create_from_format( $format, $time );
    if ( $parsed === false ) {
       sudbury_log("Validation Failed for $newval, returning $oldval");
       return $oldval;

    }
	sudbury_log($parsed->format('H:i:s'));
	return $parsed->format('H:i:s');
}


function sudbury_validation_num( $newval, $oldval = false ) {
	if ( is_numeric( $newval ) ) {
		return $newval;
	}

	return $oldval;
}


function sudbury_validation_is_location( $newval, $oldval = false ) {
	/* TODO: Would like to make location_exists($id) function */
	if ( is_numeric( $newval ) && ! is_null( sudbury_get_location( $newval ) ) ) {
		return $newval;
	}

	return $oldval;
}


function sudbury_validation_cancel_and_update_parent( $newval, $oldval = false ) {
	return $newval;
}

function sudbury_validation_cancel_and_update_children( $newval, $oldval = false ) {
	return $newval;
}

function sudbury_validation_cancel_and_update_relationship_meta( $newval, $oldval = false ) {
	if (is_array($newval)) {
		foreach($newval as $blog_id => $relations) {

			if (!is_int($blog_id)) {
				return $oldval;
			}
			if ($blog_id <= 0) {
				unset($newval[$blog_id]);
				continue;
			}
			foreach ($relations as $relation => $meta) {
				if (!in_array($relation, array('parent', 'child', 'counterpart'))) {
					return $oldval;
				}
				if (!is_array($meta)) {
					return $oldval;
				}
			}

		}
		// Successfully Validated as a good Relationship Meta Object
		_sudbury_log('Successfully Validated Relationship Meta Object');
		return $newval;
	} else {
		return $oldval;
	}
}

function sudbury_validation_cancel_and_update_counterparts( $newval, $oldval = false ) {
	return $newval;
}

function sudbury_validate_and_launch_site_restructuring( $newval, $oldval = false ) {
	if ( sudbury_blog_exists( $newval ) ) {
		wp_schedule_single_event( time(), 'sudbury_restructure_sites' );

		return $newval;
	}

	return $oldval;
}
