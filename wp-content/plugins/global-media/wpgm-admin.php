<?php
/**
 * Adds admin interfaces for the plugin
 */

/**
 * Fields in media library list page
 *
 * @param array  $form_fields The Current Form Fields
 * @param object $post        The current attachment post
 *
 * @return array new form fields
 */
function wpgm_add_attachment_fields_to_edit( $form_fields, $post ) {
        if ( ! is_super_admin() ) {
            return;
        }

        if ( ! wpgm_is_global( $post->ID, get_current_blog_id() ) ) {
		$form_fields["wpgm_make_global_button"] = array(
			'label' => 'Global Media',
			'input' => 'html',
			'html'  => '<input type="checkbox" name="wpgm_make_global_button">Move to Global Media Library When I Click \'Update\'',
			'helps' => 'This will move this media item to a Global Media Library which allows all users access to the item. You cannot get this back after it has been made global, but you can still use it in your content.'
		);
	} else {
		$form_fields["wpgm_make_global_button"] = array(
			'label' => 'Global Media',
			'input' => 'html',
			'html'  => '<input type="checkbox" class="setting" name="wpgm_return_post_button">',
			'helps' => 'This will put the media item back in it\'s original blog/site'
		);
	}

	return $form_fields;
}

add_filter( "attachment_fields_to_edit", "wpgm_add_attachment_fields_to_edit", null, 2 );

/**
 * return posts or make posts global
 *
 * @param array|bool $post
 *
 * @return array
 */
function wpgm_add_attachment_fields_to_save( $post = false ) {

	if ( isset( $_POST['wpgm_make_global_button'] ) && $_POST['wpgm_make_global_button'] = 'on' ) {
		wpgm_make_global( intval( $_POST['ID'] ) );
		if ( function_exists( 'sudbury_redirect_updated' ) ) {
			sudbury_redirect_updated( 'The Media Item has been put in the Global Media Library', admin_url( 'upload.php' ) );
		} else {
			wp_redirect( admin_url( 'upload.php' ) );
			exit;
		}
	}

	if ( isset( $_POST['wpgm_return_post_button'] ) ) {
		wpgm_return_post( intval( $_POST['id'] ) );
		if ( function_exists( 'sudbury_redirect_updated' ) ) {
			sudbury_redirect_updated( 'The Media Item has been returned to it\'s original site', admin_url( 'upload.php' ) );
		} else {
			wp_redirect( admin_url( 'upload.php' ) );
			exit;
		}
	}

	return $post;
}

// Fire late in order to let other saving things do their magic
add_filter( 'update_post_meta', 'wpgm_add_attachment_fields_to_save', 100, 1 );

//add_filter( 'wp_ajax_save-attachment-compat', 'wpgm_add_attachment_fields_to_save', 0, 1 );

/**
 * This overrides the receiving end of backbone's wp.media.query
 * Switches the current blog to the global media wrapper
 */
function wpgm_media_query_call() {
	if ( isset( $_POST['query']['global_library'] ) ) {
		switch_to_blog( wpgm_get_global_blog_id() );
	}
}

add_action( 'wp_ajax_query-attachments', 'wpgm_media_query_call', 0 );

/**
 * Resets the current blog after WordPress has listed out all the attachments for the global library
 */
function wpgm_media_query_call_restore() {
	if ( isset( $_POST['query']['global_library'] ) ) {
		restore_current_blog();
	}
}

add_action( 'wp_ajax_query-attachments', 'wpgm_media_query_call_restore', 2 );

/**
 * Wordpress posts back to get the HTML to plop in the editor and it will fail if it is not on the global media blog
 * @return bool
 */
function wpgm_send_attachment_to_editor() {
	check_ajax_referer( 'media-send-to-editor', 'nonce' );
	// Only switch to global blog if the attachment is a global attachment
	if ( wpgm_sending_global_attachment() ) {
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error();
		}

		if ( wpgm_is_global( $_POST['attachment']['id'], get_current_blog_id() ) ) {
			wp_send_json_error( 'There is an ID Conflict with a media item on your site and the global media library.  Please have the network administrator recreate the global media attachment' );

			return false;
		}
		switch_to_blog( wpgm_get_global_blog_id() );
	}

}

add_action( 'wp_ajax_send-attachment-to-editor', 'wpgm_send_attachment_to_editor', 0 );

/**
 * Allows a user to upload files to the global media library ONLY if we are sending an attachment to the global library
 */
function wpgm_user_cap_override( $allcaps, $cap ) {
	if ( wpgm_sending_global_attachment() && get_current_blog_id() == wpgm_get_global_blog_id() && 'upload_files' == $cap ) {
		restore_current_blog();
		if ( current_user_can( 'upload_files' ) ) {
			$allcaps['upload_files'] = true;
		}
		switch_to_blog( wpgm_get_global_blog_id() );
	}

	return $allcaps;
}

add_filter( 'user_has_cap', 'wpgm_user_cap_override', 10, 2 );

/**
 * Handles the restore_current_blog() after wordpress has determined the correct HTML to send to the Editor
 */
function wpgm_send_attachment_to_editor_restore() {
	if ( wpgm_sending_global_attachment() ) {
		restore_current_blog();
	}
}

add_action( 'wp_ajax_send-attachment-to-editor', 'wpgm_send_attachment_to_editor_restore', 2 );


/**
 * registers and enqueue scripts for global media library modal
 */
function wpgm_scripts() {
	global $post;

	if ( $post && 'meeting' != $post->post_type ) {
		wp_register_script( 'wpgm-admin', plugins_url( 'wpgm-admin.js', __FILE__ ), array(
			'media-views',
			'media-models',
			'backbone'
		), '1.0.0', true );
		wp_enqueue_script( 'wpgm-admin' );
	}
}

add_action( 'admin_enqueue_scripts', 'wpgm_scripts' );

