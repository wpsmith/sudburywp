<?php
/*
Template Name: Full width
*/
?>
<?php get_header(); ?>
		<?php do_action( 'bp_before_blog_home' ) ?>
	<div id="full-width">
			<?php if($bp_existed == 'true') : ?>
				<?php do_action( 'bp_before_blog_page' ) ?>
			<?php endif; ?>
			<div id="blog-page">
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
						<?php if($bp_existed == 'true') : ?>
							<?php do_action( 'bp_after_blog_page' ) ?>
						<?php endif; ?>
			</div>
			</div>
		</div>
	</div>

<?php get_footer(); ?>