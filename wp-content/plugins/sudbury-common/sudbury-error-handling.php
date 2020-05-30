<?php
/**
 * Error Handling  functions and protocols
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Debug
 */


/**
 * Halts execution because something went horribly wrong... Plugin conflict, bad update, or something of the like
 * This prevents errors in the database and other horrible things from happening
 */
function sudbury_halt( $msg = 'None Provided' ) {

	$message = 'A Request at ' . current_time( 'mysql' ) . ' caused the WordPress site to halt a request, this is most likely because of an update that was recently installed, code that was recently changed, or a plugin that was recently added. Undo any changes that were recently made and report this message to a developer';
	$message = '\n\n\nAdditional Information: ' . $msg;
	wp_mail( 'wordpress_emergency@sudbury.ma.us', 'WordPress Request Halted!', $message );


	wp_die( 'Site has been halted for the protection of both you and the database running it. Administrators have been notified. If you are a user please check back soon, the site will be back up shortly.<br><pre>' . esc_html( $msg ) . '</pre>', 'Service Halt' );
}

/**
 * Prints a generic message with the Information Systems contact message appended to whatever message string it is given.
 *
 * @param string $message The Message You are showing in addition to the contact info.
 */
function the_sudbury_contact_admin_message( $message = '' ) {
	echo get_sudbury_contact_admin_message( $message );
}

/**
 * Returns a generic message with the Information Systems contact message appended to whatever message string it is given.
 *
 * @param string $message The Message You are showing in addition to the contact info.
 *
 * @return string The Contact Info HTML string
 */
function get_sudbury_contact_admin_message( $message = '' ) {
	$contact_string = 'Please contact Information Systems at <a href="mailto:webmaster@sudbury.ma.us">webmaster@sudbury.ma.us</a> or <a href="tel:9786393306">(978) 369 - 3304</a>';
	if ( $message ) {
		// If the $message is a complete sentence then the $contact message should be cap'd and spaced properly
		if ( '.' == substr( $message, - 1 ) ) {
			$contact_string = '  ' . $contact_string;
		} else {
			$contact_string = ' ' . lcfirst( $contact_string );
		}
	}

	return apply_filters( 'get_sudbury_contact_admin_message', $message . $contact_string, $message );
}

/**
 * A custom handler for any wp_die call.  It will send an email off to wordpresseventlogs@sudbury.ma.us . then it will call the $sudbury_default_wp_die callback to proceed with the normal wp_die screen
 *
 * @param string $message
 * @param string $title
 * @param array  $args
 */
$sudbury_default_wp_die = '_default_wp_die_handler';

function sudbury_die_handler( $message = '--NONE--', $title = '--NONE--', $args = array() ) {
	global $sudbury_default_wp_die;
	if ( isset( $GLOBALS['sudbury_die_lock'] ) ) {
		return;
	}

	if ( is_admin() && ! current_user_can( 'read' ) ) {
		$GLOBALS['sudbury_die_lock'] = true;
		do_action( 'admin_page_access_denied' );
	}

	if ( is_wp_error( $message ) ) {
		$message = $message->get_error_message();
	}

	$body = '<html><head><title>Wordpress Event Report</title></head><body>';
	$body .= '<h2> The is an Event Report from Wordpress </h2>';
	$body .= '<p> Wordpress encountered an error and wp_die was called.</p>';
	$body .= '<p> Wordpress Cancelled the request because of this issue but it is probably running OK right now </p>';
	$body .= '<p> For more information you can check the debug log from WordPress at ' . WP_CONTENT_DIR . '\debug.log </p>';
	$body .= '<hr />';
	$body .= '<h4>Title: ' . $title . '</h4>';
	$body .= '<p><b>Message:</b> ' . $message . '</p>';
	$body .= '<p>Thank You,</p>';
	$body .= __FILE__;
	$body .= '</body></html>';

	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

	@mail( 'wordpress_errors@sudbury.ma.us', '[Wordpress] Event Report - wp_die', $body, $headers );


	sudbury_log( "WordPress Error Report (wp_die)", array( 'echo' => false ) );
	sudbury_log( $message, array( 'echo' => false ) );
	call_user_func( $sudbury_default_wp_die, $message, $title, $args );
}

/**
 * Returns the callback to sudbury_die_handler() but first stores the callback that was going to be called originally to $sudbury_default_wp_die
 *
 * @param $input
 *
 * @return string
 */
function sudbury_die_function( $input ) {
	global $sudbury_default_wp_die;
	$sudbury_default_wp_die = $input;

	return 'sudbury_die_handler';
}

add_filter( 'wp_die_handler', 'sudbury_die_function', 999, 1 );

function sudbury_admin_page_access_denied() {
	if ( is_admin() && ! is_network_admin() && ! current_user_can( 'read' ) ) {
		$blogs = get_blogs_of_user( get_current_user_id() );

		$blog_name = get_bloginfo( 'name' );

		if ( empty( $blogs ) ) {
			wp_die( sprintf( __( 'You attempted to access the "%1$s" dashboard, but you do not currently have privileges on this site. If you believe you should be able to access the "%1$s" dashboard, please contact your network administrator.' ), $blog_name ) );
		}

		$output = '<h3>' . __( 'Welcome to WordPress' ) . '</h3>';
		$output .= '<p>' . __( get_sudbury_contact_admin_message( 'We would like to welcome you to WordPress, the new WebEditor. If you have any questions or comments ' ) );
		$output .= '<h3>' . __( 'Your Sites' ) . '</h3>';
		$output .= '<table>';
		if ( $blogs ) {
			foreach ( $blogs as $blog ) {
				$output .= '<tr>';
				$output .= "<td>{$blog->blogname}</td>";
				$output .= '<td><a href="' . esc_url( get_admin_url( $blog->userblog_id ) ) . '">' . __( 'Visit Dashboard' ) . '</a> | ' .
				           '<a href="' . esc_url( get_home_url( $blog->userblog_id ) ) . '">' . __( 'View Site' ) . '</a></td>';
				$output .= '</tr>';
			}
		} else {

			$output .= '<tr>';
			$output .= '<td colspan="2">No Blogs Found</td>';
			$output .= '</tr>';
		}

		$output .= '</table>';
		wp_die( $output );
	}
}

add_action( 'admin_page_access_denied', 'sudbury_admin_page_access_denied' );
/**
 * Logs the $message using sudbury_log() but does not echo out the message... quiet logging
 *
 * @param string $message
 * @param array  $args
 */
function _sudbury_log( $message = '', $args = array() ) {
	sudbury_log( $message, array_merge( $args, array( 'echo' => false ) ) );
}

/**
 * A wrapper message for whatever logging format we want to use for errors generated by custom code written in-house.
 * This method does not handle logging for generic PHP errors or for any errors from other applications separate from Wordpress
 *
 * @param string $message
 * @param array  $args
 */
function sudbury_log( $message = '', $args = array() ) {
	// Forking all Cron Logs to wp-cron.log and prepending [CRON] [{mysql_thread_id()}]
	$pre_context = '[' . $GLOBALS['REQUEST_ID'] . ']';
	if ( preg_match( '/wp-.*cron.php/', $_SERVER['REQUEST_URI'] ) ) {
		$pre_context .= ' [CRON]';
	}
	$dest = WP_CONTENT_DIR . '/sudbury.log';
	if ( defined( 'WP_LOG_DIR' ) ) {
		$dest = WP_LOG_DIR . '/sudbury.log';
	}
	$defaults = array(
		'type'          => 3,
		'destination'   => $dest,
		'extra_headers' => '',
		'echo'          => true,
		'context'       => false
	);
	$args     = array_merge( $defaults, $args );
	if ( ! is_string( $message ) ) {
		$message = _d( $message );
	}
	global $sudbury_debug_print;
	$lines          = explode( "\n", $message );
	$max_num_digits = floor( log( count( $lines ), 10 ) );
	foreach ( $lines as $line_num => $line ) {
		// Start with the $pre_context
		$line_final = $pre_context;

		// Add the Context if it has one
		if ( $args['context'] ) {
			$line_final .= " [{$args['context']}]";
		}

		// If this is a multiline Log then add the Line Number
		if ( count( $lines ) > 1 ) {
			$line_num   = str_pad( $line_num, $max_num_digits, '0', STR_PAD_LEFT );
			$line_final .= " [LINE: $line_num]";

		}
		$line_final .= " $line";

		// If Not using the builting Logger then we need to add the timestamp and newline ourselves
		if ( $args['type'] == 3 ) {
			$line_final = '[' . date( 'd-M-Y H:i:s e' ) . '] ' . $line_final;
			$line_final .= "\n";
		}

		if ( ! error_log( $line_final, $args['type'], $args['destination'], $args['extra_headers'] ) ) {
			echo "Error logging message '$message' with arguments <pre>" . print_r( $args, true ) . '</pre>';
		}
	}
	if ( $args['echo'] && $sudbury_debug_print ) {
		printf( '<pre>%s</pre>', $message );
	}

}

/**
 * This function logs user information if they perform suspicious actions on our site and then emails the log to a webmaster for review
 *
 * @param string $reason  Required, If you don't provide a reason you are violating privacy policy
 * @param string $message The Message That your code presented to the user I.E. "Are you attempting to hack our site? If so stop now!"
 *                        If you present a message include it when you call this function for legal reasons, be sure to echo the message yourself before calling this function
 *
 * @return bool Whether the log was successful
 */
function sudbury_log_user_info( $reason, $message = '{No Message Provided to User}' ) {
	if ( ! $reason ) {
		return false;
	} // You didn't provide a reason... This is required for justification of user information logging
	$format =
		"====================== USER INFORMATION LOG =======================
		The Following Information was recorded by php for the given reason:

			WARNING: LINKS, IMAGES, AND OTHER CONTENT IN THIS LOG COULD BE MALICIOUS

			[Reason]: %s
			[Message Presented To User]: %s
			[Nice Date Format]: %s
			[Unix Timestamp]: %d
			[IP Address]: %s
			[Requested URL]: %s
			[Full Dump of $ _REQUEST Variable]:
			%s
			[Full Dump of $ _COOKIE Variable]:
			%s
			[Full Dump of $ _SERVER Variable]:
			%s

		============= END USER INFORMATION LOG ==============";

	$log = sprintf( $format, $reason, $message, date( 'F j, Y g:i a' ), time(), $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'], print_r( $_REQUEST, true ), print_r( $_COOKIE, true ), print_r( $_SERVER, true ) );

	$headers = "MIME-Version: 1.0\r\n";

	mail( 'wordpress_logs@sudbury.ma.us', '[WordPress] [USE CAUTION] User Information Log - ' . $reason, $log, $headers );
	sudbury_log( $log, array( 'echo' => false ) );

	return true;

}
