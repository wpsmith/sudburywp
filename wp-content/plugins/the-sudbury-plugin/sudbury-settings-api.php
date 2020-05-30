<?php
/**
 * The Settings API for the Sudbury Plugin
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Settings
 */

/**
 * Gets all the options with keys in $keys from the database and returns an associative array with the keys and the retrieved values
 *
 * @param array $keys     The List of keys to get
 * @param mixed $defaults The List of Defaults
 *
 * @return array The Array of $keys mapped to values
 */
function sudbury_get_options( $keys = array(), $defaults = array() ) {
	$options = array();

	foreach ( $keys as $index => $key ) {
		if ( ! is_array( $defaults ) ) {
			$options[ $key ] = get_option( $key, $defaults );
		} // defaults is not an array and constant for each key (ie a stirng)
		else {
			if ( isset( $defaults[ $index ] ) ) {
				$options[ $key ] = get_option( $key, $defaults[ $index ] );
			} // A default for the index was specified
			else {
				$options[ $key ] = get_option( $key );
			}
		} // use wordpress defaults
	}

	return $options;
}

/**
 * Updates all the specified settings in the assoc array $data. Returns an array with the same keys and a boolean value indicating whether the option was updated
 *
 * @param array $data          A single dimensional associative array containing the key value pairs to update
 * @param bool  $single_return If set to true the function will return the key of the first option that was not updated or TRUE (NOT FALSE) if all options were updated.
 * @param bool  $allow_add     If set to true then the setting will be added if it doesn't exist in the options database
 *
 * @return bool|string|array     If $single return is set to true then the function will return the key of the first option that was not updated or TRUE (NOT FALSE) if
 *                               all options were updated.  Otherwise it will return a list of all the options that were updated
 */
function sudbury_update_options( $data = array(), $single_return = false, $allow_add = false ) {
	foreach ( $data as $key => $value ) {
		if ( $allow_add || ! is_a( $option = get_option( $key, new WP_Error() ), 'WP_Error' ) ) {
			$data[ $key ] = update_option( $key, $value );
		} else {
			$data[ $key ] = false;
		}
	}

	if ( $single_return ) {
		$first_failure = array_search( false, $data );

		// Single Return Specified, Return True on success or the first failure if there was an error
		return ( ( false === $first_failure ) ? true : $first_failure );
	} else {
		return $data;
	}
}

/**
 * Adds all the specified settings in the assoc array $data. Returns an array with the same keys and a boolean value indicating whether the option was added
 *
 * @param array $data          The List of options to add
 * @param bool  $single_return If set to true the function will return the key of the first option that was not updated or TRUE (NOT FALSE) if all options were updated.
 *
 * @return array An Array with the same keys as $data and a value of true if the insert succeeded, and false if it failed
 */
function sudbury_add_options( $data = array(), $single_return = false ) {
	foreach ( $data as $key => $value ) {
		$data[ $key ] = add_option( $key, $value );
	}

	if ( $single_return ) {
		$first_failure = array_search( false, $data );

		return ( ( false === $first_failure ) ? true : $first_failure );
	} else {
		return $data;
	}
}

/**
 *  Function that automatically scans for sudbury_ keys in $_POST.  Calls the filter sudbury_plugin_options_bulk_validate and for any data validation then updates the entire batch
 *
 * @param int|bool $blog_id The id of the blog to update the settings on
 * @param bool     $die     Whether to die after updating the settings
 *
 * @return array An array with the status message, list of updated options, and results from the updates
 */
function sudbury_update_settings( $blog_id = false, $die = false ) {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( get_sudbury_contact_admin_message( 'Access is denied: You cannot modify this department\'s info.' ) );
	}
	if ( false !== $blog_id ) {
		switch_to_blog( $blog_id );
	}

	$error = '';
	$data  = array();

	foreach ( $_POST as $key => $value ) {
		if ( strstartswith( 'sudbury_', $key ) ) {

			// First round of filtering: We pass and retrieve an array for a specific reason:  So that filter functions can modify both the key and value
			$result = apply_filters( 'sudbury_update_settings', array( 'key' => $key, 'value' => $value ) );
			if ( false === $result ) {
				$error .= '{ Key = ' . $key . ', Value =  ' . var_export( $value, true );
				continue;
			} elseif ( false === $result['key'] ) {
				$error .= $result['value']; // Passes back a custom error message if $result['key'] is false
				continue;
			} else {
				$key   = $result['key']; // Ok we are good, lets extract to $key and $value
				$value = $result['value'];
			}


			// Second Round of Filtering: This allows for filter functions to subscribe to just events for the setting they are concerned about and allow them to be excluded
			$result = apply_filters( 'sudbury_update_settings_' . $key, array( 'key' => $key, 'value' => $value ) );
			if ( false === $result ) {
				$error = '{ Key = ' . $key . ', Value =  ' . var_export( $value, true );
				continue;
			} else {
				if ( false === $result['key'] ) {
					$error = $result['value'];
					continue;
				} else {
					$key   = $result['key']; // Ok we are good, lets extract to $key and $value
					$value = $result['value'];
				}
			}

			// We have filtered now lets populate the $data array
			$data[ $key ] = $value;
		}
	}
	// Last Chance Filter
	// Allows complete modification of the $data array before submission.  (allows for updates like the legacy sudbury_types option)
	$data    = apply_filters( 'sudbury_plugin_options_bulk_validate', $data );
	$results = array();

	if ( false === $data ) {
		$error = "Update was canceled by a filter function in the bulk validation";
	} else {
		// Do the Update of all the options
		$results = sudbury_update_options( $data, false, true );
	}

	if ( false !== $blog_id ) {
		restore_current_blog();
	}
	$updated = array();
	// Flush All Caches
	if( class_exists('W3_Plugin_TotalCacheAdmin') ) {
		$plugin_totalcacheadmin = & w3_instance('W3_Plugin_TotalCacheAdmin');

		$plugin_totalcacheadmin->flush_all();
	}

	if ( $error ) {
		$message = 'A validation Error Occurred: ' . $error . 'Validation Error';
		if ( $die ) {
			wp_die( $message );
		}
	} else {
		foreach ( $results as $key => $result ) {
			if ( $result ) {
				$updated[] = $key;
			}
		}
		if ( ! empty( $updated ) ) {
			$message = "<b>Settings Updated!</b>" . ( WP_DEBUG || is_super_admin() ? '&nbsp;&rarr;' . implode( ', ', $updated ) : '' );
		} else {
			$message = "<b>No Settings Were Changed</b>" . ( is_super_admin() ? ' &rarr;Maybe you need to verify settings for this site in the <a href="' . network_admin_url( 'admin.php?page=network-sync' ) . '"> Network Sync Section </a>' : '' );
		}
	}

	return array( 'message' => $message, 'updated' => $updated, 'result' => $results );
}



