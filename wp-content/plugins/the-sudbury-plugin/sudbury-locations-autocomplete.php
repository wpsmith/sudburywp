<?php
/**
 * Autocompletes Location data from choosing a parent location which we define here
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Locations
 */

function sudbury_enqueue_location_autocomplete() {
	add_meta_box( 'sudbury-location-autocomplete', 'Within an Existing Building', 'sudbury_location_autocomplete_metabox', 'location', 'normal', 'high' );
}

add_action( 'add_meta_boxes', 'sudbury_enqueue_location_autocomplete' );

function sudbury_location_autocomplete_metabox( $post ) {
	global $wpdb;
	wp_nonce_field( 'sudbury_parent_location_id', 'save_parent_location' );

	$current_parent = get_post_meta( $post->ID, 'sudbury_parent_location_id', true );

	// I Cannot $wpdb->prepare this because there are no variables and thus no chance of SQL injection
	$locations = $wpdb->get_results( "SELECT * FROM `{$wpdb->base_prefix}em_locations` LIMIT 500", ARRAY_A );

	if ( empty( $locations ) ) {
		echo 'No Locations were found';
	} else {

		?>
		<select name="sudbury_parent_location_id" id="sudbury_parent_location_id" data-autocomplete-url="<?php echo esc_attr( admin_url( 'admin-ajax.php?action=sudbury_location_autocomplete' ) ); ?>">
			<option value="none">--None--</option>
			<?php foreach ( $locations as $location ) : ?>
				<option value="<?php echo esc_attr( $location['location_id'] ); ?>"
				        data-name="<?php echo esc_attr( $location['location_name'] ); ?>"
				        data-address="<?php echo esc_attr( $location['location_address'] ); ?>"
				        data-town="<?php echo esc_attr( $location['location_town'] ); ?>"
				        data-state="<?php echo esc_attr( $location['location_state'] ); ?>"
				        data-zip="<?php echo esc_attr( $location['location_postcode'] ); ?>"
					<?php selected( $location['location_id'], $current_parent ); ?>> <?php echo esc_html( $location['location_name'] ); ?> </option>
			<?php endforeach; ?>
		</select>
	<?php
	}
}

function sudbury_get_location_ajax() {
	if ( ! isset( $_REQUEST['location_id'] ) || ! is_numeric( $_REQUEST['location_id'] ) ) {
		return;
	}

	die( json_encode( sudbury_get_location( intval( $_REQUEST['location_id'] ) ) ) );
}

add_action( 'wp_ajax_sudbury_location_autocomplete', 'sudbury_get_location_ajax' );

function save_sudbury_parent_location_id( $id, $post ) {
	if ( 'location' == $post->post_type ) {
		if ( isset( $_POST['sudbury_parent_location_id'] ) ) {
			check_admin_referer( 'sudbury_parent_location_id', 'save_parent_location' );
			$location_parent = $_POST['sudbury_parent_location_id'];

			// if not a valid number, or location does not exist then quit (exception is that the parent is none)
			if ( 'none' != $location_parent && ( ! is_numeric( $location_parent ) || count( sudbury_get_location( $location_parent ) ) == 0 ) ) {
				return;
			}

			// if set to --None-- then remove the meta field
			if ( 'none' == $location_parent ) {
				delete_post_meta( $id, 'sudbury_parent_location_id' );
			} else {
				update_post_meta( $id, 'sudbury_parent_location_id', intval( $location_parent ) );
			}
		}

		if ( isset( $_POST['sudbury_location_phone'] ) ) {
			check_admin_referer( 'sudbury_location_phone', 'sudbury_location_phone_nonce' );
			$phone = $_POST['sudbury_location_phone'];

			update_post_meta( $id, 'sudbury_location_phone', sudbury_validation_telephone( $phone, get_post_meta( $id, 'sudbury_location_phone', true ) ) );
		}
	}
}

add_action( 'save_post', 'save_sudbury_parent_location_id', 10, 2 );
