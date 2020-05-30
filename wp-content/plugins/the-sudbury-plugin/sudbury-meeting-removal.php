<?php

/**
 * Stops a meeting from being trashed if there hasn't been a reason specified
 *
 * @param int $post_id The ID of the meeting
 */
function sudbury_halt_trash_meeting( $post_id ) {
	_sudbury_log( '[sudbury_halt_trash_meeting] function called with post id ' . $post_id );

	if ( 'meeting' != get_post_type( $post_id ) ) {
		return;
	}

	// If a reason for post trashing has been specified then trashing is OK
	if ( get_post_meta( $post_id, 'sudbury_delete_reason', true ) ) {
		return;
	}

	_sudbury_log( '[sudbury_halt_trash_meeting]Redirecting to Explanation Page ' . $post_id );

	// Redirect to the removal request page
	wp_redirect( admin_url( 'admin.php?page=meeting-removal-request&post=' . $post_id, 302 ) );
	exit;
}

add_action( 'wp_trash_post', 'sudbury_halt_trash_meeting' );

/**
 * Notifies a webmaster when a meeting is unpublished from the website
 */
function sudbury_handle_meeting_remove_request() {
	check_admin_referer( 'trash_meeting_request', 'sudbury_meetings_nonce' );
	if ( ! isset( $_REQUEST['post'] ) || ! is_numeric( $_REQUEST['post'] ) ) {
		sudbury_redirect_error( 'Post ID Not Specified' );
	}
	$post_id = $_REQUEST['post'];

	$post = get_post( $post_id );

	if ( ! current_user_can( 'delete_posts' ) || ! current_user_can( 'delete_post', $post_id ) ) {
		sudbury_redirect_error( 'Access Denied, You can\'t trash this meeting' );
	}
	if ( 'meeting' != $post->post_type ) {
		sudbury_redirect_error( 'Not a Meeting, sorry' );
	}

	if ( ! isset( $_REQUEST['delete-reason'] ) ) {
		sudbury_redirect_error( 'A Reason for deletion was not passed to the script' );
	}

	$reason = sanitize_text_field( $_REQUEST['delete-reason'] );


	if ( ! in_array( $reason, sudbury_get_meeting_delete_reasons() ) ) {
		sudbury_redirect_error( 'A valid reason for deletion was not passed to the script' );
	}
	if ( 'Other' == $reason ) // get the deletion reason (and sanitize it with sudbury_validation_textarea())
	{
		$reason = sudbury_validation_textarea( $_REQUEST['delete-reason-other'] );

		// if they didn't provide a solid reason (shortest I can think of is "Snow Storm") then ask for more info

		if ( strlen( $reason ) < 10 ) {
			sudbury_redirect_error( 'Please be More specific', add_query_arg( array(
				'delete-reason-other' => $reason,
				'delete-reason'       => 'Other'
			), wp_get_referer() ) );
		}
	}

	sudbury_redirect_updated( "Meeting is being moved to the trash", admin_url( 'edit.php?post_type=meeting' ), true );

	sudbury_flush_request(); // End the current Request but continue the processing

	_sudbury_log( '[POST_PROCESSING] Processing meeting trash request' );

	// Get the user that is requesting this removal
	$user = wp_get_current_user();
	// Record the Reason
	add_post_meta( $post_id, 'sudbury_delete_reason', $reason );
	add_post_meta( $post_id, 'sudbury_delete_requestor', $user->user_login );

	wp_trash_post( $post_id );


	$body = 'The meeting "' . get_the_title( $post ) . '" was moved to the trash by ' . $user->user_login . ' at ' . current_time( 'mysql' );
	$body .= '<br><br>Reason: ' . $reason;
	$body .= '<br><br>Edit Link: <a href="' . admin_url( 'edit.php?post_type=meeting&post_status=trash' ) . '">Click Here</a><br><br>';
	$body .= '<i>Please note that this meeting will be permanently deleted in 30 days if it is not restored from the trash</i>';
	wp_mail( 'webmaster@sudbury.ma.us', 'Meeting Was Trashed', $body, "Content-type: text/html" );
	_sudbury_log( '[POST_PROCESSING] Completed Post Processing of meeting trash request' );

	die();
}


add_action( 'admin_post_sudbury-trash-meeting', 'sudbury_handle_meeting_remove_request', 10, 3 );

/**
 * @return array gets the acceptable reasons for removing a meeting
 */
function sudbury_get_meeting_delete_reasons() {
	$reasons = array(
		'Cancelled - Conflict with meeting space',
		'Cancelled - Weather Event',
		'Could not achieve quorum',
		'Draft / Unneeded',
		'Other'
	);

	return apply_filters( 'sudbury_delete_meeting_reasons', $reasons );
}

/**
 * renders the HTML for the meeting removal request page
 */
function sudbury_meeting_removal_request_page() {
	?>
	<h2 class="page-title">Meeting Removal Request</h2>
	<?php if ( ! isset( $_REQUEST['post'] ) ) : ?>
		<div id="message" class="error"><p><b>Error</b> No Meeting id was specified</p></div>
	<?php
	else:
		$post_id = $_REQUEST['post'];
		$post    = get_post( $post_id ); ?>
		<form action="admin-post.php" method="post">
			<?php wp_nonce_field( 'trash_meeting_request', 'sudbury_meetings_nonce' ); ?>
			<?php wp_referer_field(); ?>
			<input type="hidden" name="action" value="sudbury-trash-meeting" />
			<input type="hidden" name="post" value="<?php echo esc_attr( $post_id ); ?>" />

			<h3>Remove Meeting: <?php echo esc_html( $post->post_title ); ?></h3>

			<?php foreach ( sudbury_get_meeting_delete_reasons() as $reason ) : ?>
				<input type="radio" name="delete-reason" value="<?php echo esc_attr( $reason ); ?>" <?php checked( isset( $_REQUEST['delete-reason'] ) && $reason == $_REQUEST['delete-reason'] ); ?>><?php echo esc_html( $reason ); ?>
				<br>
			<?php endforeach; ?>
			<label for="delete-reason-other">
				<b>Other</b> If you selected "other" please provide a reason? Example:
				<code>Snow Storm</code><br />
				<textarea rows="6" cols="150" id="delete-reason-other" name="delete-reason-other"><?php if ( isset( $_REQUEST['delete-reason-other'] ) ) {
						echo esc_textarea( $_REQUEST['delete-reason-other'] );
					} ?></textarea>
			</label>
			<?php submit_button( 'Trash Meeting', 'delete' ); ?>
		</form>
	<?php endif; ?>
<?php
}
