<?php
/**
 * WP Global Media Functions File
 */


/**
 * Returns the Blog ID that houses all the Global Media Items
 *
 * @note Applies Filter 'wpgm_get_global_blog_id' with the value of the constant 'WPGM_BLOGID'
 *
 * @return int The ID of the global media blog
 */
function wpgm_get_global_blog_id() {
	return apply_filters( 'wpgm_get_global_blog_id', WPGM_BLOGID );
}

/**
 * Move an attachment to the Global Media Library
 *
 * @param int  $source_attachment_id
 * @param bool $source_blog_id
 *
 * @return bool True on success, false on failure
 */
function wpgm_make_global( $source_attachment_id, $source_blog_id = false ) {
	if ( false === $source_blog_id ) {
		$source_blog_id = get_current_blog_id();
	}

	$target_blog = wpgm_get_global_blog_id();

	// Don't do it, you'll just hurt yourself... even if you REALLY REALLY want to
	if ( $source_blog_id == $target_blog ) {
		return false;
	}

	// Switch to the Source Blog
	switch_to_blog( $source_blog_id );

	$attachment_post = get_post( $source_attachment_id, ARRAY_A );

	if ( ! $attachment_post || wp_is_post_revision( $source_attachment_id ) || 'attachment' != $attachment_post['post_type'] ) {
		restore_current_blog();

		return false;
	}


	$meta = get_post_meta( $source_attachment_id );

	// Get the Old Filename, the file will stay in place!
	$file_path = get_attached_file( $source_attachment_id );

	$file_url = wp_get_attachment_url( $source_attachment_id );

	/* Copy Attachment Post to $target_blog */

	// Switch to the Target Blog
	switch_to_blog( $target_blog );

	// Copy the array to $new_post
	$new_attachment_post = $attachment_post;

	unset( $new_attachment_post['ID'] );

	$global_attachment_id = wp_insert_post( $new_attachment_post );

	// Copy Attachment Meta
	foreach ( $meta as $key => $values ) {
		foreach ( $values as $value ) {
			add_post_meta( $global_attachment_id, $key, maybe_unserialize( $value ) );
		}
	}

	update_post_meta( $global_attachment_id, 'wpgm_original_post_id', $source_attachment_id );
	update_post_meta( $global_attachment_id, 'wpgm_original_blog_id', $source_blog_id );

	// Used to point to where the file is located
	update_post_meta( $global_attachment_id, 'wpgm_full_file_path', wp_slash( $file_path ) );
	update_post_meta( $global_attachment_id, 'wpgm_full_file_url', wp_slash( $file_url ) );

	// Restore to source blog
	restore_current_blog();

	wpgm_delete_attachment_keep_files( $source_attachment_id );

	// Restore to the current blog
	restore_current_blog();


	return $global_attachment_id;
}


/**
 * @param int  $global_attachment_id
 * @param bool $restore_to_blog_id
 *
 * @return bool|int|WP_Error
 */
function wpgm_return_post( $global_attachment_id, $restore_to_blog_id = false ) {

	// You can use this capability later
	if ( ! current_user_can( 'wpgm_return_global_posts' ) && ! is_super_admin() ) {
		return false;
	}

	$global_blog = wpgm_get_global_blog_id();

	// Don't do it, you'll just hurt yourself
	if ( $restore_to_blog_id === $global_blog ) {
		return false;
	}

	switch_to_blog( $global_blog );

	$global_attachment = get_post( $global_attachment_id, ARRAY_A );

	if ( ! $global_attachment || wp_is_post_revision( $global_attachment_id ) || 'attachment' != $global_attachment['post_type'] ) {

		restore_current_blog();

		return false;
	}

	$meta = get_post_meta( $global_attachment_id );

	if ( ! isset( $meta['wpgm_original_post_id'][0] ) || ! isset( $meta['wpgm_original_blog_id'][0] ) ) {
		restore_current_blog();

		return false;
	}

	if ( false === $restore_to_blog_id ) {
		$restore_to_blog_id = $meta['wpgm_original_blog_id'][0];
	} // The original Blog's ID

	unset( $meta['wpgm_original_blog_id'] );
	// keeping wpgm_original_post_id in metadata

	switch_to_blog( $restore_to_blog_id );

	$new_attachment = $global_attachment;
	unset( $new_attachment['ID'] );
	$new_post_id = wp_insert_post( $new_attachment );

	foreach ( $meta as $key => $values ) {
		if ( in_array( $key, array( 'wpgm_full_file_path', 'wpgm_full_file_url' ) ) ) {
			foreach ( $values as $value ) {
				add_post_meta( $new_post_id, $key, maybe_unserialize( $value ) );
			}
		}
	}

	// Restore to source blog
	restore_current_blog();

	// Delete the attachment from the database but not the server itself
	wpgm_delete_attachment_keep_files( $global_attachment_id );

	// Restore to the current blog
	restore_current_blog();

	return $new_post_id;
}

/**
 * Deletes the specified Attachment From the WordPress database but keeps the files.  It also trolls WordPress a bit :-)
 *
 * @param int $attachment_id The attachment ID to delete
 *
 * @return mixed False on failure. Post data on success.
 */
function wpgm_delete_attachment_keep_files( $attachment_id ) {
	// We are going to totally troll WordPress :-)
	add_filter( 'wp_delete_file', '__return_gibberish' );

	// Run the regular delete attachment function... just we will troll it whenever it tries to delete a file
	$return = wp_delete_attachment( $attachment_id, true );

	// OK We've had our fun, wordpress didn't delete any files. let's unhook :-)
	remove_filter( 'wp_delete_file', '__remove_giberish' );

	return $return;
}

/**
 * Returns gibberish String... not cryptographically secure though
 *
 * @param string $salt A salt to use in the gibberish making
 *
 * @return string Gibberish of length 32
 */
function __return_gibberish( $salt = AUTH_SALT ) {
	return md5( ( rand() * microtime() ) . $salt );
}

/**
 * Ensures that a filename is safe and if it isn't then it appends _(2), _(3), ect on to the end of the file
 *
 * @param $file
 * @param $search
 * @param $replace
 *
 * @return bool|string
 */
function wpgm_new_filename( $file, $search, $replace ) {

	$indx = strpos( $file, strval( $search ) );

	if ( false === $indx ) {
		return false;
	}

	$new = substr( $file, 0, $indx ) .
	       $replace .
	       substr( $file, $indx + strlen( strval( $search ) ) );

	$i = 1;
	while ( file_exists( $new ) ) {
		$indx = strrpos( $new, '.' );
		$i ++;
		if ( $i > strlen( $new ) ) {
			$new = $new . "_(" . $i = 2 . ")";
			continue;
		}

		$new = substr( $new, 0, $indx ) . "_($i)" . substr( $new, $indx );
	}

	return $new;
}

/**
 * @param int $postid         THe Attachment ID on the Global Blog
 * @param int $global_blog_id THe Blog ID of the global media library
 *
 * @return bool True if there is a global media item with the given ID
 */
function wpgm_is_global( $postid, $global_blog_id = false ) {
	if ( false === $global_blog_id ) {
		$global_blog_id = wpgm_get_global_blog_id();
	}

	if ( $global_blog_id != wpgm_get_global_blog_id() ) {
		return false;
	}

	switch_to_blog( $global_blog_id );

	$is_global = ( '' !== get_post_meta( $postid, 'wpgm_original_blog_id', true ) );

	restore_current_blog();

	return $is_global;
}

/**
 * Gets the attachment url for a global media attachment
 *
 * @param string $src           The Url where WordPress thinks that the attachment is located
 * @param int    $attachment_id The ID of the attachment
 *
 * @return string The url to the global media attachment file (The original Uploaded Location)
 */
function wpgm_attachment_url( $src, $attachment_id ) {
	if ( get_current_blog_id() != wpgm_get_global_blog_id() ) {
		return $src;
	}

	if ( $url = get_post_meta( $attachment_id, 'wpgm_full_file_url', true ) ) {
		return $url;
	} else {
		return $src;
	}
}

add_filter( 'wp_get_attachment_url', 'wpgm_attachment_url', 50, 2 );

/**
 * Gets the attachment file path for a global media attachment
 *
 * @param string $path          The Path where WordPress thinks that the attachment is located
 * @param int    $attachment_id The ID of the attachment
 *
 * @return string The path to the global media attachment file (The original Uploaded Location)
 */
function wpgm_attachment_path( $path, $attachment_id ) {
	if ( get_current_blog_id() != wpgm_get_global_blog_id() ) {
		return $path;
	}

	if ( $url = get_post_meta( $attachment_id, 'wpgm_full_file_path', true ) ) {
		return $url;
	} else {
		return $path;
	}
}

add_filter( 'get_attached_file', 'wpgm_attachment_path', 50, 2 );

/**
 * Determines if the current request is sending a global attachment to the editor
 *
 * Assumes running in a send-attachment-to-editor request
 *
 * @return bool Whether this request is sending a global attachment to the editor
 */
function wpgm_sending_global_attachment() {
	return ( isset( $_POST['attachment']['id'] ) && wpgm_is_global( $_POST['attachment']['id'] ) );
}