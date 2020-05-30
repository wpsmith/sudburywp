<?php
/**
 * THe Paging Application Backend for WordPress
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Paging
 */

function sudbury_register_paging_metabox() {
	add_meta_box( 'sudbury-paging-metabox', 'Phone / Pager Settings', 'sudbury_paging_metabox', 'paging_item', 'normal', 'core' );
	add_meta_box( 'sudbury-service-providers-metabox', 'Service Provider Info', 'sudbury_service_provider_metabox', 'service_provider', 'normal', 'core' );
}

add_action( 'admin_init', 'sudbury_register_paging_metabox', 0, 1 );

function sudbury_paging_admin_menu() {
	if ( current_user_can( 'manage_network' ) ) {
		/* moving the service providers menu down to a submenu */
		add_submenu_page( 'edit.php?post_type=paging_item', 'Service Providers', 'Service Providers', 'edit_posts', 'sudbury-paging-manage-service-providers', 'sudbury_paging_manage_service_providers' );

		if ( isset( $_REQUEST['page'] ) && 'sudbury-paging-manage-service-providers' == $_REQUEST['page'] ) {
			if ( current_user_can( 'manage_network' ) ) {
				wp_redirect( get_site_url( 1, '/wp-admin/edit.php?post_type=service_provider' ) );
				exit;
			} else {
				wp_die( 'You do not have Permission to view this.  Sorry.' . get_sudbury_contact_admin_message() );
			}
		}
	}
	remove_menu_page( 'edit.php?post_type=service_provider' );
}

add_action( 'admin_menu', 'sudbury_paging_admin_menu' );

/**
 * The metabox for all the paging post meta to the phone or pager
 */
function sudbury_paging_metabox() {
	global $post;

	// array of service providers, each one containing 'name', 'email', and 'maxchars' values
	$service_providers = get_site_option( 'sudbury_service_providers' );

	$meta = get_post_meta( $post->ID );

	// Sometimes the post isn't fully created yet so we need to check that before iterating over $meta
	if ( $post && is_array( $meta ) ) {
		// merge everything down and remove any duplicates.  We now have a nice array of key => value pairs
		foreach ( $meta as $key => $value ) {
			if ( is_array( $value ) ) {
				$meta[ $key ] = $value[0];
			}
		}
	}


	// defaults
	$meta = array_merge( array(
		'_phone_assigned_to_first_name' => '',
		'_phone_assigned_to_last_name'  => '',
		'_phone_number'                 => '',
		'_phone_type'                   => '',
		'_phone_service_provider'       => 'verizon'
	), $meta );


	?>
	<div class="form-field form-required sudbury-metabox">

		<table class="form-table">

			<tbody>
			<tr>
				<th>
					<label for="_phone_assigned_to_first_name">Assigned To (First Name)*</label>
				</th>
				<td>
					<input type="text" class="sudbury-field sudbury-text" name="_phone_assigned_to_first_name" id="_phone_assigned_to_first_name" value="<?php echo esc_attr( $meta['_phone_assigned_to_first_name'] ); ?>">
					<br><span class="description"></span>
				</td>
			</tr>
			<tr>
				<th>
					<label for="_phone_assigned_to_last_name">Assigned To (Last Name)*</label>
				</th>
				<td>
					<input type="text" class="sudbury-field sudbury-text" name="_phone_assigned_to_last_name" id="_phone_assigned_to_last_name" value="<?php echo esc_attr( $meta['_phone_assigned_to_last_name'] ); ?>">
					<br><span class="description"></span>
				</td>
			</tr>
			<tr>
				<th>
					<label for="_phone_number">Phone Number</label>
				</th>
				<td>
					<input type="text" class="sudbury-field sudbury-text" name="_phone_number" id="_phone_number" value="<?php echo esc_attr( $meta['_phone_number'] ); ?>">
					<br><span class="description"></span>
				</td>
			</tr>

			<tr>
				<th>
					<label for="_phone_type">Type of Device</label>
				</th>
				<td>
					<input type="text" class="sudbury-field sudbury-text" name="_phone_type" id="_phone_type" value="<?php echo esc_attr( $meta['_phone_type'] ); ?>">
					<br><span class="description">Optional: the type of device this is: i.e. pager</span>
				</td>
			</tr>

			<tr>
				<th>
					<label for="_phone_service_provider">Service Provider</label>
				</th>
				<td>
					<select class="sudbury-field sudbury-selectbox" name="_phone_service_provider" id="_phone_service_provider">
						<?php foreach ( $service_providers as $service_provider ) : $service_provider = $service_provider['_sp_name']; ?>

							<option <?php selected( $meta['_phone_service_provider'], $service_provider ); ?> value="<?php echo esc_attr( $service_provider ); ?>"><?php echo esc_html( $service_provider ); ?> </option>
						<?php endforeach; ?>
					</select>
					<br><span class="description"></span>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
<?php
}


function sudbury_save_paging_item( $id, $post ) {

	// Has a lock been set?
	if ( isset( $GLOBALS['sudbury_save_paging_item_lock'] ) ) {
		// yup there is a lock, Respect the Lock
		return;
	}

	// Verify the post type is a paging_item
	if ( 'paging_item' == $post->post_type ) {

		// If the post variable for the first name is not set then lets run away because this probably isn't for us
		if ( ! isset( $_POST['_phone_assigned_to_first_name'] ) ) {
			return;
		}

		// get the first and last names and sanitize them
		$first_name = sanitize_text_field( $_POST['_phone_assigned_to_first_name'] );
		$last_name  = sanitize_text_field( $_POST['_phone_assigned_to_last_name'] );

		// Add or update the first and last names
		update_post_meta( $id, '_phone_assigned_to_first_name', $first_name );
		update_post_meta( $id, '_phone_assigned_to_last_name', $last_name );

		// Get the phone number and formatt it in a nice format using our script
		$number = sudbury_validation_telephone( $_POST['_phone_number'] );
		update_post_meta( $id, '_phone_number', $number );

		// Get the optional phone type. default is ''
		$type = sanitize_text_field( $_POST['_phone_type'] );
		update_post_meta( $id, '_phone_type', $type );

		// Update the Service provider slug
		update_post_meta( $id, '_phone_service_provider', sanitize_text_field( $_POST['_phone_service_provider'] ) );

		// Set the lock so that we don't infinite loop when we update the title of this post
		$GLOBALS['sudbury_save_paging_item_lock'] = true;

		// Update the Title of this Paging Item to reflect the new values provided
		$update               = array( 'ID' => $id );
		$new_title            = "{$first_name} {$last_name} - {$number}";
		$update['post_title'] = $new_title;

		if ( 0 === strpos( $post->post_name, 'auto-draft' ) ) {
			$update['post_name'] = sanitize_key( $new_title );
		}

		wp_update_post( $update );

		unset( $GLOBALS['sudbury_save_paging_item_lock'] );
	}
}

add_action( 'save_post', 'sudbury_save_paging_item', 10, 2 );

function sudbury_service_provider_metabox() {
	global $post;

	$adding = false;
	$meta   = get_post_meta( $post->ID );
	if ( ! isset( $meta['_sp_name'] ) ) {
		$adding = true;
		$meta   = array(
			'_sp_name'                 => '',
			'_sp_email_address_format' => '',
			'_sp_max_chars'            => '140',
		);
	} else {
		//fixing the multiple Key issue
		foreach ( $meta as $key => $arr ) {
			$meta[ $key ] = $arr[0];
		}
	}
	?>
	<div class="form-field form-required sudbury-metabox">
		<input type="hidden" name="service_provider_form" value="1" />
		<table class="form-table">

			<tbody>
			<tr>
				<th>
					<label for="_sp_name">Provider Name (cannot be changed)</label>
				</th>
				<td>
					<input type="text" class="sudbury-field sudbury-text" name="_sp_name" id="_sp_name" value="<?php echo esc_attr( $meta['_sp_name'] ); ?>" <?php disabled( ! $adding ) ?>>
					<br><span class="description"></span>
				</td>
			</tr>
			<tr>
				<th>
					<label for="_sp_email_address_format">The email address format. </label>
				</th>
				<td>
					<br><span class="description"> Use %s to replace the number: <br>Example: <code>%s@vtext.com</code> would convert to <code>9786393304@vtext.com</code> </span>

					<input type="text" class="sudbury-field sudbury-text" name="_sp_email_address_format" id="_sp_email_address_format" value="<?php echo esc_attr( $meta['_sp_email_address_format'] ); ?>">
				</td>
			</tr>
			<tr>
				<th>
					<label for="_sp_max_chars">Max Number of Characters</label>
				</th>
				<td>
					<input type="number" class="sudbury-field sudbury-text" name="_sp_max_chars" id="_sp_max_chars" value="<?php echo esc_attr( $meta['_sp_max_chars'] ); ?>">
					<br><span class="description"></span>
				</td>
			</tr>

			</tbody>
		</table>
	</div>
<?php
}

$SUDBURY_DONE_UPDATE_SERVICE_PROVIDER = false;

function sudbury_save_service_provider( $id, $post ) {
	global $SUDBURY_DONE_UPDATE_SERVICE_PROVIDER;
	if ( 'service_provider' == $post->post_type && isset( $_POST['service_provider_form'] ) && ! $SUDBURY_DONE_UPDATE_SERVICE_PROVIDER ) {
		$post = get_post( $id );


		$meta = get_post_meta( $post->ID );

		// if the input is disabled the browser won't post it's value... shame
		if ( isset( $_POST['_sp_name'] ) ) {
			$sp_name = sanitize_text_field( $_POST['_sp_name'] );
		} else {
			$sp_name = $meta['_sp_name'][0];
		}

		//Update the Site Setting
		$service_providers = get_site_option( 'sudbury_service_providers' );
		$found             = false;
		if ( is_array( $service_providers ) ) {
			foreach ( $service_providers as $indx => $sp ) {
				if ( $sp['_sp_name'] == $meta['_sp_name'][0] ) {
					$found                      = true;
					$service_providers[ $indx ] = array(
						'_sp_name'                 => sanitize_title( $sp_name ),
						'_sp_email_address_format' => sanitize_text_field( $_POST['_sp_email_address_format'] ),
						'_sp_max_chars'            => sanitize_text_field( $_POST['_sp_max_chars'] )
					);
				}
			}
		}
		if ( ! $found ) { // then it needs to be added
			$service_providers[] = array(
				'_sp_name'                 => sanitize_title( $sp_name ),
				'_sp_email_address_format' => sanitize_text_field( $_POST['_sp_email_address_format'] ),
				'_sp_max_chars'            => sanitize_text_field( $_POST['_sp_max_chars'] )
			);
		}
		//update the site option
		update_site_option( 'sudbury_service_providers', $service_providers );

		// update the meta

		// I don't actually restrict changing the service provider name so that if you NEED to you can open up dev-tools and mess with the HTML and then submit the form
		update_post_meta( $id, '_sp_name', sanitize_title( $sp_name ) );
		update_post_meta( $id, '_sp_email_address_format', sanitize_text_field( $_POST['_sp_email_address_format'] ) );
		update_post_meta( $id, '_sp_max_chars', sanitize_text_field( $_POST['_sp_max_chars'] ) );


		if ( $post->post_title != sanitize_title( $sp_name ) && ! wp_is_post_revision( $id ) ) {
			$post->post_title = $sp_name;
			wp_update_post( $post );
			$SUDBURY_DONE_UPDATE_SERVICE_PROVIDER = true;
		}


	} else {
		$SUDBURY_DONE_UPDATE_SERVICE_PROVIDER = false;
	}
}

add_action( 'save_post', 'sudbury_save_service_provider', 10, 2 );

/**
 * Deletes the Global sudbury_service_providers site Option when a service provider is deleted
 *
 * @param $post_id
 */
function sudbury_trash_service_provider( $post_id ) {

	if ( ! $post = get_post( $post_id ) ) {
		return;
	}

	if ( 'service_provider' != $post->post_type ) {
		return;
	}

	$meta = get_post_meta( $post->ID );

	if ( ! isset( $meta['_sp_name'] ) ) {
		wp_delete_post( $post_id ); //It's not useful so delete it
		sudbury_redirect_error( "The service provider was Corrupt and has been deleted instead of trashed", admin_url( 'admin.php?page=sudbury-paging-manage-service-providers' ), true );

		return;
	}

	$service_providers = get_site_option( 'sudbury_service_providers' );

	if ( is_array( $service_providers ) ) {
		foreach ( $service_providers as $indx => $sp ) {
			if ( $sp['_sp_name'] == $meta['_sp_name'] ) {
				unset( $service_providers[ $indx ] );
				update_site_option( 'sudbury_service_providers', $service_providers );

				return;
			}
		}
	}

	sudbury_redirect_error( "The service provider was not found in the site option... Just an FYI", admin_url( 'admin.php?page=sudbury-paging-manage-service-providers' ), true );
}

add_action( 'wp_trash_post', 'sudbury_trash_service_provider' );

/**
 * Deals with the
 *
 * @param $post_id
 */
function sudbury_restore_service_provider( $post_id ) {
	if ( ! $post = get_post( $post_id ) ) {
		return;
	}

	if ( 'service_provider' != $post->post_type ) {
		return;
	}

	$meta = get_post_meta( $post->ID );

	if ( ! isset( $meta['_sp_name'] ) ) {
		wp_delete_post( $post_id ); //It's not useful so delete it
		sudbury_redirect_error( "The service provider was Corrupt and has been deleted", admin_url( 'admin.php?page=sudbury-paging-manage-service-providers' ), true );

		return;
	}

	$service_providers = get_site_option( 'sudbury_service_providers' );

	if ( is_array( $service_providers ) ) {
		foreach ( $service_providers as $sp ) {
			if ( $sp['_sp_name'] == $meta['_sp_name'] ) {
				sudbury_redirect_error( "A Service Provider named '{$sp['_sp_name']}' is already active", admin_url( 'admin.php?page=sudbury-paging-manage-service-providers' ), true );
				wp_trash_post( $post_id ); //Send it back to the trash
				return;
			}
		}
	}

	$service_providers[] = array(
		'_sp_name'                 => sanitize_title( $meta['_sp_name'] ),
		'_sp_email_address_format' => sanitize_text_field( $_POST['_sp_email_address_format'] ),
		'_sp_max_chars'            => sanitize_text_field( $_POST['_sp_max_chars'] )
	);


	update_site_option( 'sudbury_service_providers', $service_providers );
}

add_action( 'untrash_post', 'sudbury_restore_service_provider' );

/**
 * Stub Method for service providers
 */
function sudbury_paging_manage_service_providers() {
	//THis is a stub method for the fake menu page above
}
