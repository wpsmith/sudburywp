<?php
/**
 * The main template file.
 *
 * @package    Sudbury
 * @subpackage Sudbury_Theme
 */
if ( $post && 'page' === $post->post_type ) {
	if ( 'default' != $template = get_post_meta( $post->ID, '_wp_page_template', true ) ) {
		include( $template );
	} else {
		get_template_part( 'page' );
	}
} else {
	get_template_part( 'home' );
}
