<?php
/*
Template Name: Left sidebar
*/
?>
<?php get_header(); ?>
		<div class="content-page-left" id="blog-latest">

					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

						<h3 class="pagetitle"><?php the_title(); ?></h3>

						<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

							<div class="entry">

								<?php the_content( __( '<p class="serif">Read the rest of this page &raquo;</p>', TEMPLATE_DOMAIN ) ); ?>

								<?php wp_link_pages( array( 'before' => __( '<p><strong>Pages:</strong> ', TEMPLATE_DOMAIN ), 'after' => '</p>', 'next_or_number' => 'number')); ?>
								<?php edit_post_link( __( 'Edit this entry.', TEMPLATE_DOMAIN ), '<p>', '</p>'); ?>
	<div class="clear"></div>
							</div>

						</div>

					<?php endwhile; ?>
							<?php locate_template( array( '/elements/pagination.php' ), true ); ?>
					<?php else: ?>

						<?php locate_template( array( '/elements/messages.php' ), true ); ?>

					<?php endif; ?>

		</div>

	<?php get_sidebar('left'); ?>
				<div class="clear">
		</div>
	</div>



<?php get_footer(); ?>