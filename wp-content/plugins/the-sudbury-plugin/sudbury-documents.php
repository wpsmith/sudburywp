<?php
/**
 * Handles features that might not have a wordpress implementation.  Displays interfaces of other
 * applications within a frame of the wordpress interface and handles authentication for
 * the external application via wordpress auth
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Extra_Features
 */
/**
 * registers documents with an mime type of application/* in the media library with the title 'Documents'.  Also allows all documents to be filtered by their mimetype using '?post_mime_type=application'
 *
 * @param $post_mime_types
 *
 * @return array
 */
function sudbury_documents_mime_type( $post_mime_types ) {
	if ( ! isset( $post_mime_types['document'] ) ) {
		$post_mime_types['application'] = array(
			__( 'Document' ),
			__( 'Manage Document' ),
			_n_noop( 'Documents <span class="count">(%s)</span>', 'Documents <span class="count">(%s)</span>' )
		);
	}

	return $post_mime_types;
}

add_filter( 'post_mime_types', 'sudbury_documents_mime_type' );

/**
 * Adds the default post status of attachments (inherit) to the post_status query var if in a wp-link-ajax search context
 *
 * @param $wp_query
 */
function sudbury_add_media_link_search_fix( $wp_query ) {
	if ( did_action( 'wp_ajax_wp-link-ajax' ) ) {

		if ( ! is_array( $wp_query->query_vars['post_status'] ) ) {
			$wp_query->query_vars['post_status'] = array( $wp_query->query_vars['post_status'] );
		}

		if ( ! in_array( 'inherit', $wp_query->query_vars['post_status'] ) ) {
			$wp_query->query_vars['post_status'][] = 'inherit';
		}
	}
}

add_action( 'pre_get_posts', 'sudbury_add_media_link_search_fix' );


/**
 * Adds the Documents Link in the sidebar of the Wordpress Admin
 */
function sudbury_documents_link_admin_menu() {
	add_menu_page( 'Documents Redirect Page', 'Documents', 'upload_files', 'sudbury-documents-redirect', 'sudbury_redirect_registry', 'dashicons-analytics', 14 );
}

add_action( 'admin_menu', 'sudbury_documents_link_admin_menu' );


function add_attachment_fields_to_edit( $form_fields, $post ) {
	$run_start           = get_post_meta( $post->ID, 'sudbury_start_date', true );
	$run_end             = get_post_meta( $post->ID, 'sudbury_end_date', true );
	$run_end_timestamp   = mysql2date( 'U', $run_end );
	$run_start_timestamp = mysql2date( 'U', $run_start );

	$indefinite = ( 2147483647 - 3600 < $run_end_timestamp );

	if ( ! $run_end || $indefinite ) {
		$run_end_timestamp = $run_start_timestamp + 60 * 60 * 24 * 30 * 6; // Run Start + 6 months
		if ( current_time( 'timestamp' ) > $run_end_timestamp ) {
			$run_end_timestamp = current_time( 'timestamp' ) + 60 * 60 * 24 * 30 * 6;
		}
	}
	$form_fields["sudbury_close_on_submit"] = array(
		'label' => 'Referred',
		'type'  => 'hidden',
		'value' => ( isset( $_REQUEST['referred'] ) ? 'true' : 'false' ),
	);

	$form_fields["sudbury_start_date"]         = array(
		'label' => __( "Run Start", 'sudbury' ),
		'input' => 'text',
		'value' => $run_start,
		'helps' => 'When this document Shows Up in the Document List',
		'tr'    => '<tr class="compat-field-sudbury_end_date"><th valign="top" scope="row" class="label"><label for="attachments-25-sudbury_end_date"><span class="alignleft">Run Start</span><br class="clear"></label></th>
                    <td class="field">' . sudbury_get_datetime_editor( '', $run_start_timestamp, 'attachments[' . $post->ID . '][sudbury_start_date]' ) . '<p class="help">When this document should start being listed in the Documents List</p></td>
                </tr>'
	);
	$form_fields["sudbury_run_end_indefinite"] = array(
		'label' => 'Indefinite',
		'input' => 'html',
		'html'  => '<input type="checkbox" name="sudbury_indefinite_run_end" class="toggle-disabled check-toggle toggle-inverted" data-toggle="#sudbury_run_end_container" ' . checked( $indefinite, true, false ) . '>This document runs indefinitely',
		'helps' => ''
	);

	$form_fields["sudbury_end_date"] = array(
		'label' => __( "Run End", 'sudbury' ),
		'input' => 'text',
		'value' => $run_end,
		'helps' => 'When this document is hidden from the documents list',
		'tr'    => '<tr class="compat-field-sudbury_end_date" ' . ( $indefinite ? 'style="display:none;"' : '' ) . ' id="sudbury_run_end_container"><th valign="top" scope="row" class="label"><label for="attachments-25-sudbury_end_date"><span class="alignleft">Run End</span><br class="clear"></label></th>
                    <td class="field">' . sudbury_get_datetime_editor( '', $run_end_timestamp, 'attachments[' . $post->ID . '][sudbury_end_date]' ) . '<p class="help">When this document should stop being listed in the Documents List</p></td>
                </tr>'
	);

	return $form_fields;
}

add_filter( "attachment_fields_to_edit", "add_attachment_fields_to_edit", 99, 2 );

/**
 * @param array $post
 * @param array $attachment
 *
 * @return array
 */
function add_attachment_fields_to_save( $post, $attachment ) {
	if ( isset( $attachment['sudbury_start_date'] ) ) {
		update_post_meta( $post['ID'], 'sudbury_start_date', $attachment['sudbury_start_date'] );
		update_post_meta( $post['ID'], 'sudbury_start_date_timestamp', mysql2date( 'U', $attachment['sudbury_start_date'] ) );
		_sudbury_log( $post );
		if ( ! get_post_meta( $post['ID'], '_sudbury_created_date', true ) ) {
			update_post_meta( $post['ID'], '_sudbury_created_date', get_post( $post['ID'] )->post_date );
		}

		wp_update_post( array(
			'ID'        => $post['ID'],
			'post_date' => mysql2date( 'Y-m-d H:i:s', $attachment['sudbury_start_date'] )
		) );

		if ( isset( $attachment['sudbury_end_date'] ) && ! isset( $_REQUEST['sudbury_indefinite_run_end'] ) ) {
			update_post_meta( $post['ID'], 'sudbury_end_date', $attachment['sudbury_end_date'] );
			update_post_meta( $post['ID'], 'sudbury_end_date_timestamp', mysql2date( 'U', $attachment['sudbury_end_date'] ) );
		} else {
			_sudbury_log( $post );

			update_post_meta( $post['ID'], 'sudbury_end_date', date( 'm/d/Y H:i', 2147483647 ) );
			update_post_meta( $post['ID'], 'sudbury_end_date_timestamp', 2147483647 );
		}
	}

	if ( isset( $attachment['sudbury_close_on_submit'] ) && $attachment['sudbury_close_on_submit'] == 'true' ) {
		# Remove the 302 location and show a page that will inform them to close the tab.
		header_remove( 'Location' );
		include_once dirname( __FILE__ ) . '/sudbury-close-tab.php';
		flush();
	}

	return $post;
}

add_filter( 'attachment_fields_to_save', 'add_attachment_fields_to_save', 99, 2 );

/**
 * Sets the Default Run start and Run End Dates
 *
 * @param int $post_id The ID of the Post
 */
function sudbury_add_attachment( $post_id ) {
	_sudbury_log( "New Attachment $post_id on " . get_current_blog_id() . " initialized with 6 months (158 days) ahead end date" );
	update_post_meta( $post_id, 'sudbury_start_date', date( 'm/d/Y H:i', current_time( 'timestamp' ) ) );
	update_post_meta( $post_id, 'sudbury_start_date_timestamp', current_time( 'timestamp' ) );

	update_post_meta( $post_id, 'sudbury_end_date', date( 'm/d/Y H:i', current_time( 'timestamp' ) + 60 * 60 * 24 * 158 ) );
	update_post_meta( $post_id, 'sudbury_end_date_timestamp', current_time( 'timestamp' ) + 60 * 60 * 24 * 158 );
}

add_action( 'add_attachment', 'sudbury_add_attachment' );


/* Framework Functions */

/**
 * Determines if there are any documents for the department or committee
 *
 * @param bool|int $blog_id The ID of the site to check for documents.  False for current blog
 *
 * @return bool True if there is at least one document otherwise false
 */
function sudbury_has_documents( $blog_id = false ) {
	if ( ! $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	switch_to_blog( $blog_id );

	$posts = get_posts( array(
		'post_type'      => 'attachment',
		'post_mime_type' => 'application',
		'posts_per_page' => 1
	) );

	restore_current_blog();

	return ! empty( $posts );
}
