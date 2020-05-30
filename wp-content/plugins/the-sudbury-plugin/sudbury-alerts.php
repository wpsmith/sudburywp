<?php
/**
 * Alerts API for the Town Website.  By Default this API pulls alerts from the network index categorizes as an alert and
 * then caches them ready for pickup from the theme using the get_alerts() function.  A filter is in place to add your own
 * alerts to the System.  See the Weather Alerts plugin which interfaces with that filter
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage ALerts
 */

// Set the default time to cache alerts if not already set
if ( ! defined( 'SUDBURY_ALERT_CACHE_TIME' ) ) {
	define( 'SUDBURY_ALERT_CACHE_TIME', 20 );
}

/**
 * Determines if there are Any Alerts in the System to Show
 * @return bool true if there are alerts, otherwise false
 */
function has_alerts() {
	$alerts = get_alerts();

	return is_array( $alerts ) && ! empty( $alerts['site-only'] ) || ! empty( $sudbury_alerts['network-wide'] );
}

/**
 * Gets the List of Alerts In the System
 *
 * @param bool $reload Force the Function to skip the cache and check the database directly then reload the cached var
 *
 * @return array An Array with the list of site-only Alert Posts, network-wide Alert Posts, and a list of all Alerts
 */
function get_alerts( $reload = false ) {

	// Get the Alerts from the cache if possible and a force reload is not specified
	if ( ! $reload && $alerts = wp_cache_get( 'alerts', 'sudbury' ) ) {

		return $alerts;
	}

	$sudbury_alerts = array();
	// Looking only at the current site for a post that is a site-only alert
	$sudbury_alerts['site-only'] = get_posts(
		array(
			'post_status'    => 'publish',
			'tax_query'      =>
				array(
					array(
						'taxonomy' => 'category',
						'field'    => 'slug',
						'terms'    => 'alert'
					)
				),
			'posts_per_page' => - 1
		)
	);
	//Looking for any post made by any department in the network with the category 'Network Alert'
	$sudbury_alerts['network-wide'] = network_query_posts(
		array(
			'post_status'    => 'publish',
			'tax_query'      =>
				array(
					array(
						'taxonomy' => 'category',
						'field'    => 'slug',
						'terms'    => 'network-alert'
					)
				),
			'posts_per_page' => - 1
		)
	);

	$sudbury_alerts['all'] = array_merge( $sudbury_alerts['site-only'], $sudbury_alerts['network-wide'] );


	$sudbury_alerts = apply_filters( 'sudbury_alerts', $sudbury_alerts );

	// Cache it for a brief period of time (default 20 seconds)
	wp_cache_set( 'alerts', $sudbury_alerts, 'sudbury', SUDBURY_ALERT_CACHE_TIME );

	// Get a list of the previous alerts and compare to the new list of alerts top check what's changed
	$prev_alerts = get_site_option( 'prev_network_alerts', array() );

	// Check for Subtractions
	foreach ( $prev_alerts as $prev_alert ) {
		if ( ! in_array( $prev_alert, $sudbury_alerts ) ) {
			_sudbury_log( '[Alerts] Alert Canceled! Sending Notification to Listeners! ' );
			_sudbury_log( $prev_alert );
			do_action( 'alert_down', $prev_alert );
		}
	}

	// Check for Additions
	foreach ( $sudbury_alerts as $alert ) {
		if ( ! in_array( $alert, $prev_alerts ) ) {
			_sudbury_log( '[Alerts] New Alert Detected! Sending Notification to Listeners! ' );
			_sudbury_log( $alert );
			do_action( 'alert_up', $alert );
		}
	}

	// Set the previous Alerts option to the current alert
	update_site_option( 'prev_network_alerts', $sudbury_alerts );

	return $sudbury_alerts;
}


$alert_levels = array( 'success', 'ok', 'info', 'warning', 'danger', 'red', 'critical' );

/**
 * Determines the Alert level of an alert
 *
 * @param string $string The category of the alert i.e. network-alert-emergency or just alert-emergency
 *
 * @return int The Alert Level:  0 to count($alert_levels) - 1
 */
function determine_alert_level( $string ) { //finds which of 5 strings is present in the argument and returns corresponding integer
	global $alert_levels;
	for ( $i = count( $alert_levels ) - 1; $i >= 0; $i -- ) {
		if ( false !== strpos( $string, $alert_levels[ $i ] ) ) { //if locating the substring did not fail. Cannot have ==true b/c strpos returns int or false
			return $i;
		}
	}

	return 0;
}

/**
 * Determines the type of alert for a post given the list of categories assigned to a post
 *
 * @author Mathew Goff goffm@sudbury.ma.us
 * @author Eddie Hurtig hurtige@sudbury.ma.us
 *
 * @param array $categories A list of Categories
 *
 * @return array|bool An array with details or false if not an alert
 */
function sudbury_parse_alert_type( $categories ) {
	global $alert_levels;
	// Start at -1 for both a network alert and a regular alert
	$highest_network_alert = - 1;
	$highest_alert         = - 1;

	// Loop through the categories
	foreach ( $categories as $category ) {
		// Check if this category is a network alert category
		if ( strstartswith( 'network-alert-', $category->slug ) ) {
			// Determine the alert level (number 0 - 4)
			$alert_level = determine_alert_level( $category->slug );
			// if this is the highest found so far then bump up the $highest_network_alert
			if ( $alert_level > $highest_network_alert ) {
				$highest_network_alert = $alert_level;
			}
		} elseif ( strstartswith( 'alert-', $category->slug ) ) { // Check if this category is a regular alert category
			// Determine the alert level (number 0 - 4)
			$alert_level = determine_alert_level( $category->slug );
			// if this is the highest found so far then bump up the $highest_alert
			if ( $alert_level > $highest_alert ) {
				$highest_alert = $alert_level;
			}
		}
	}

	// Return the info for dealing with this alert... or false if you shouldn't deal with it
	if ( $highest_network_alert > - 1 ) {
		return array(
			'type'        => 'network-alert',
			'alert-class' => "alert-" . $alert_levels[ $highest_network_alert ]
		);
	} elseif ( $highest_alert > - 1 ) {
		return array( 'type' => 'alert', 'alert-class' => "alert-" . $alert_levels[ $highest_alert ] );
	} else {
		return false;
	}

}


/**
 * Adds the restrictions to stop users from applying spotlight news categories who aren't allowed to
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage News_Articles
 */
class Alert_Capability_Manager {

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * WordPress Init. Registers required actions
	 */
	function init() {
		if ( is_admin() && current_user_can( 'promote_users' ) ) {
			add_action( 'show_user_profile', array( &$this, 'add_user_field' ) );
			add_action( 'edit_user_profile', array( &$this, 'add_user_field' ) );
			add_action( 'profile_update', array( &$this, 'save' ), 10, 2 );
		}

	}

	function add_user_field( $user ) {
		?>
		<table class="form-table">
			<?php wp_nonce_field( 'sudbury-update-network-alert-cap', 'sudbury-update-network-alert-cap-nonce' ); ?>
			<tr>
				<th>
					<label for="sudbury_network_alert_cap"><?php _e( 'Network Alerts' ); ?></label>
				</th>
				<td>
					<?php if ( ! $overriden = ( is_super_admin( $user->ID ) || ( $this->user_can_for_blog( $user, 1, 'create_network_alerts' ) && get_current_blog_id() != 1 ) ) ) : ?>
						<input type="hidden" name="sudbury_network_alert_cap_process" value="1">
					<?php endif; ?>
					<input type="checkbox" name="sudbury_network_alert_cap" id="sudbury_network_alert_cap" <?php checked( user_can( $user, 'create_network_alerts' ) || $this->user_can_for_blog( $user, 1, 'create_network_alerts' ) ); ?> <?php disabled( $overriden ); ?> class="checkbox" />
					<span class="description"><?php _e( 'Create Network Alerts Capability', 'sudbury' ); ?></span>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Saves the Capability
	 *
	 * @param int   $user_id  User ID
	 * @param array $old_data The Old Data
	 */
	function save( $user_id, $old_data ) {

		if ( ! current_user_can( 'promote_users' ) ) {
			return;
		}

		check_admin_referer( 'sudbury-update-network-alert-cap', 'sudbury-update-network-alert-cap-nonce' );
		$user = new WP_User( $user_id );
		if ( isset( $_REQUEST['sudbury_network_alert_cap_process'] ) ) {
			if ( $user->has_cap( 'create_network_alerts' ) && ! isset( $_REQUEST['sudbury_network_alert_cap'] ) ) {
				$user->remove_cap( 'create_network_alerts' );
			} elseif ( ! $user->has_cap( 'create_network_alerts' ) && isset( $_REQUEST['sudbury_network_alert_cap'] ) ) {
				$user->add_cap( 'create_network_alerts' );
			}
		}
	}

	/**
	 * Can user perform action on specified blog
	 *
	 * @param WP_User $user       The User
	 * @param int     $blog_id    Blog ID
	 * @param string  $capability The Cap
	 *
	 * @return bool
	 */
	function user_can_for_blog( $user, $blog_id, $capability ) {


		// Create new object to avoid stomping the $user.
		$the_user = new WP_User( $user->ID );

		if ( empty( $the_user ) ) {
			return false;
		}

		// Set the blog id. @todo add blog id arg to WP_User constructor?
		$the_user->for_site( $blog_id );

		$args = array_slice( func_get_args(), 2 );
		$args = array_merge( array( $capability ), $args );

		return call_user_func_array( array( &$the_user, 'has_cap' ), $args );
	}
}

new Alert_Capability_Manager();
