<?php
/**
 * Team Member page temaplte.
 *
 * @package    AllInPaint
 * @author    WP Smith
 * @since    0.1.0
 */

namespace WPS\WP\Plugins\CoreClasses;

/**
 * Add `team-member` body class.
 *
 * @param array $classes The existing body classes.
 *
 * @return array $classes The modified body classes.
 * @since  0.1.0
 * @access public
 */
add_filter( 'body_class', function ( $classes ) {
	$classes[] = 'team-member';

	return $classes;
} );

/**
 * Remove entry info.
 *
 * Callback defined in Genesis.
 *
 * @see genesis_post_info
 */
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );

/**
 * Remove entry footer.
 *
 * Callback defined in Genesis.
 *
 * @see genesis_entry_footer_markup_open
 * @see genesis_entry_footer_markup_close
 * @see genesis_post_meta
 */
remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_open', 5 );
remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_close', 15 );
remove_action( 'genesis_entry_footer', 'genesis_post_meta' );

/**
 * Reposition entry header.
 *
 * Callback defined in Genesis.
 *
 * @see genesis_entry_header_markup_open
 * @see genesis_entry_header_markup_close
 * @see genesis_do_post_title
 */
remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 );
remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );
remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
add_action( 'genesis_entry_content', 'genesis_entry_header_markup_open', 5 );
add_action( 'genesis_entry_content', 'genesis_entry_header_markup_close', 9 );
add_action( 'genesis_entry_content', 'genesis_do_post_title', 6 );

/**
 * Reposition genesis singular image.
 *
 * Callback defined in Genesis.
 *
 * @see genesis_do_singular_image
 */
remove_action( 'genesis_entry_content', 'genesis_do_singular_image', 8 );
add_action( 'genesis_before_entry_content', 'genesis_do_singular_image', 8 );

/**
 * Output back to team page.
 *
 * @return void
 * @since 0.1.0
 */
add_action( 'genesis_before_entry', function () {
	$team_page = get_page_by_path( 'our-team' );
	?>
    <div class="back-team">
        <a href="<?php echo get_permalink( $team_page->ID ); ?>"><?php esc_html_e( 'Back to Team', 'allinpaint' ); ?></a>
    </div>
	<?php
} );

/**
 * Output team member terms.
 *
 * Team member title is a custom category.
 *
 * @return void
 * @since 0.1.0
 */
add_action( 'genesis_entry_content', function () {
	$terms = get_the_terms( get_the_ID(), 'team_category' );

	if ( is_wp_error( $terms ) ) {
		return '';
	}

	if ( empty( $terms ) ) {
		return '';
	}

	foreach ( $terms as $term ) {
		printf( '<span class="entry-term">%s</span>',
			esc_html( $term->name )
		);
	}

}, 7 );

/**
 * Output team member social.
 *
 * @return void
 * @since 0.1.0
 */
add_action( 'genesis_entry_content', function () {
	$facebook  = get_field( 'field_team_facebook' );
	$twitter   = get_field( 'field_team_twitter' );
	$instagram = get_field( 'field_team_instagram' );
	$linkedin  = get_field( 'field_team_linkedin' );

	if ( $facebook || $twiiter || $instagram || $linkedin ) : ?>

        <ul class="team-social">

			<?php if ( $facebook ) : ?>
                <li class="team-social__item team-social__item--facebook">
                    <a href="<?php echo esc_url( $facebook ); ?>" class="team-social__link" title="<?php esc_attr_e( 'Facebook', 'allinpaint' ); ?>">
                        <span class="screen-reader-text"><?php esc_html_e( 'Facebook', 'allinpaint' ); ?></span>
                        <img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/facebook-logo.svg' ?>"/>
                    </a>
                </li>
			<?php endif; ?>

			<?php if ( $twitter ) : ?>
                <li class="team-social__item team-social__item--twitter">
                    <a href="<?php echo esc_url( $twitter ); ?>" class="team-social__link" title="<?php esc_attr_e( 'Twitter', 'allinpaint' ); ?>">
                        <span class="screen-reader-text"><?php esc_html_e( 'Twitter', 'allinpaint' ); ?></span>
                        <img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/twitter-logo.svg' ?>"/>
                    </a>
                </li>
			<?php endif; ?>

			<?php if ( $instagram ) : ?>
                <li class="team-social__item team-social__item--instagram">
                    <a href="<?php echo esc_url( $instagram ); ?>" class="team-social__link" title="<?php esc_attr_e( 'Instagram', 'allinpaint' ); ?>">
                        <span class="screen-reader-text"><?php esc_html_e( 'Instagram', 'allinpaint' ); ?></span>
                        <img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/instagram-logo.svg' ?>"/>
                    </a>
                </li>
			<?php endif; ?>

			<?php if ( $linkedin ) : ?>
                <li class="team-social__item team-social__item--linkedin">
                    <a href="<?php echo esc_url( $linkedin ); ?>" class="team-social__link" title="<?php esc_attr_e( 'LinkedIn', 'allinpaint' ); ?>">
                        <span class="screen-reader-text"><?php esc_html_e( 'LinkedIn', 'allinpaint' ); ?></span>
                        <img src="<?php echo get_stylesheet_directory_uri() . '/assets/images/linkedin-logo.svg' ?>"/>
                    </a>
                </li>
			<?php endif; ?>

        </ul>

	<?php endif;

}, 7 );

/**
 * Output team member about.
 *
 * @return void
 * @since 0.1.0
 */
add_action( 'genesis_entry_content', function () {
	$about = get_field( 'field_team_about' );

	if ( $about ) {
		printf( '<div class="team-about"><h3 class="team-about__heading">%s</h3>%s</div>',
			esc_html__( 'About', 'allinpaint' ),
			$about
		);
	}

}, 8 );

genesis();