<?php
/**
 * The Extra Functionality for the FAQs Section of the site
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage FAQs
 */
function sudbury_publish_post( $post_id ) {
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}


	if ( 'faq' == $_POST['post_type'] ) {

		if ( '' == $_POST['post_content'] || '' == $_POST['post_title'] ) {
			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
			sudbury_redirect_error( 'You need to provide BOTH a <b>Question</b> and an <b>Answer</b> before publishing' );
			exit();
		}
	}
}

add_action( 'publish_faq', 'sudbury_publish_post' );

/**
 * Changes the Default "Enter Title Here" Text to "Enter Question Here" When editing an FAQ
 *
 * @param $title The Current "Enter Title Here" PlaceHolder text
 * @param $post  The Post that is being Edited / Created
 *
 * @return string The new "Enter Title Here" Placeholder Text
 */
function sudbury_faqs_enter_title_here( $title, $post ) {
	if ( 'faq' == $post->post_type ) {
		return 'Enter Question Here';
	}

	return $title;
}

add_filter( 'enter_title_here', 'sudbury_faqs_enter_title_here', 10, 2 );