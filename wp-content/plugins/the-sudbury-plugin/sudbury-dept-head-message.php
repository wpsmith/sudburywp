<?php
/**
 * Handles the Department Head Message Administration
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Head_Message
 */

require_once 'meta-boxes/from-department-head.php';

/**
 * Redirect the user to the department head editor for the custom page marked with the department head attribute
 */
function sudbury_redirect_to_department_head_page() {
	if ( isset( $_REQUEST['page'] ) && 'sudbury-dept-head-options-page' == $_REQUEST['page'] ) {
		$pages = get_posts( array(
			'post_type'   => 'page',
			'post_status' => array(
				'publish',
				'future',
				'private',
				'new',
				'draft',
				'auto-draft',
				'pending'
			),
			'meta_key'    => 'sudbury_department_head_message'
		) );

		if ( empty( $pages ) ) {
			$page_id = wp_insert_post( array(
				'post_type'   => 'page',
				'post_status' => 'draft',
				'post_title'  => 'From the *Title*, *Name*'
			) );
			if ( ! is_int( $page_id ) || is_wp_error( $page_id ) ) {
				wp_die( 'Cannot send you to the Department Head Message Page, Adding the Message Page Failed' );

				// Should die but if not then just return... not that big of a problem
				return;
			}
			add_post_meta( $page_id, 'sudbury_department_head_message', true );
		} elseif ( 1 === count( $pages ) ) {
			$page_id = $pages[0]->ID;
		} else {
			$post_titles = array_map( function ( $post ) {
				return '"' . $post->post_title . '" ID:' . $post->ID . ' Post Status: ' . $post->post_status;
			}, $pages );
			wp_die( 'Multiple Department Head Posts! Please trash one of these pages by clicking their trash links on <a href="' . admin_url( 'edit.php?post_type=page' ) . '">this list</a> <ul><li>' . implode( '</li><li>', $post_titles ) . '</li></ul>' );
		}

		wp_redirect( admin_url( 'post.php?post=' . $page_id . '&action=edit' ) );
		exit;
	}
}

add_action( 'admin_init', 'sudbury_redirect_to_department_head_page' );

/**
 * Add the "From Dept Head" menu item to the sidebar
 */
function sudbury_dept_head_message_menu() {
	global $sudbury_dept_head_message_page_hook;
	if ( is_department() ) {
		$sudbury_dept_head_message_page_hook = add_menu_page( 'Sudbury Department Info', 'From Dept Head', 'edit_pages', 'sudbury-dept-head-options-page', 'sudbury_dept_head_options_page', 'dashicons-testimonial', '10.5673' );
	}
	if ( is_committee() ) {
		$sudbury_dept_head_message_page_hook = add_menu_page( 'Sudbury Department Info', 'From Chairperson', 'edit_pages', 'sudbury-dept-head-options-page', 'sudbury_dept_head_options_page', 'dashicons-testimonial', '10.5673' );
	}
}

add_action( 'admin_menu', 'sudbury_dept_head_message_menu' );

/**
 * This function blocks the publication of a department head message if the title hasn't been filled out properly.
 * Invalid publications are returned to the draft status and the user is redirected with an error message.
 *
 * @param WP_Post $post The post being published
 */
function sudbury_dept_head_message_publish( $post ) {
	// Check to make sure that this $post is a Department Head Message Page
	if ( 'page' == $post->post_type && get_post_meta( $post->ID, 'sudbury_department_head_message', true ) ) {
		// If they didn't replace the name and title placeholders in the title
		if ( false !== strpos( $post->post_title, '*' ) ) {
			// Kill the publish
			wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'draft' ) );
			wp_transition_post_status( 'draft', 'publish', $post );
			// show error message to user
			sudbury_redirect_error( '<b>Publish Cancelled:</b> Please Complete the Title Field with Your Name and Position', false, false );
		}
	}
}

add_action( 'draft_to_publish', 'sudbury_dept_head_message_publish', 20 );
add_action( 'auto-draft_to_publish', 'sudbury_dept_head_message_publish', 20 );
add_action( 'new_to_publish', 'sudbury_dept_head_message_publish', 20 );
add_action( 'draft_to_future', 'sudbury_dept_head_message_publish', 20 );
add_action( 'auto-draft_to_future', 'sudbury_dept_head_message_publish', 20 );
add_action( 'new_to_future', 'sudbury_dept_head_message_publish', 20 );