<?php
/**
 * The Template for displaying all single posts.
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 * @since      Twenty Eleven 1.0
 */

get_header();
the_post();
?>


<div id="cont">
	<div class="container-fluid container-fluid-xl">
		<div class="row">
			<?php sudbury_department_tabs(); ?>
			<main id="main-col" <?php sudbury_main_col_class(); ?>>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<?php if ( has_title() ) : ?>
						<h1>

							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							<?php sudbury_edit_post_link(); ?>
						</h1>
					<?php endif; ?>
					<div class="meta-info">
						<?php get_template_part( 'parts/meta', get_post_type() ); ?>
					</div>

					<?php if ( sudbury_is_post_archived() ) : ?>
						<p class="archived-message"><?php sudbury_the_archived_post_message(); ?></p>
					<?php endif; ?>

					<?php get_template_part( 'content-single', get_post_type() ); ?>

					<?php get_template_part( 'parts/event', get_post_type() ); ?>

					<?php get_template_part( 'parts/attachments', get_post_type() ); ?>

					<?php get_template_part( 'parts/categories', get_post_type() ); ?>

					<?php sudbury_social_links( array( 'return' => true, 'email' => true ) ); ?>

					<?php if ( is_internal() || comments_open() ) : ?>
						<?php comments_template( '', true ); ?>
					<?php endif; ?>

					<?php wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wp-bootstrap-starter' ),
						'after'  => '</div>',
					) );
					?>
				</article>
			</main>
			<?php if ( has_sidebar() ) : ?>
				<?php get_sidebar(); ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php get_footer(); ?>


