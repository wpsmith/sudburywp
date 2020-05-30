<?php get_header(); ?>

		<?php do_action( 'bp_before_attachment' ) ?>

	<div id="full-width">
		<div class="page" id="attachments-page">

			<h3 class="pagetitle"><?php _e( 'Blog', TEMPLATE_DOMAIN ) ?></h3>

				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

					<?php do_action( 'bp_before_blog_post' ) ?>

					<?php $attachment_link = wp_get_attachment_link($post->ID, array(450, 800), true); ?>

					<div class="post" id="post-<?php the_ID(); ?>">

						<h3 class="pagetitle"><a href="<?php echo get_permalink($post->post_parent); ?>" rev="attachment"><?php echo get_the_title($post->post_parent); ?></a> &raquo; <a href="<?php echo get_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', TEMPLATE_DOMAIN ) ?> <?php the_title(); ?>"><?php the_title(); ?></a></h3>

						<div class="entry">
							<p class="attachment"><?php echo $attachment_link; ?><br /><?php echo basename($post->guid); ?></p>

							<?php the_content( __('<p class="serif">Read the rest of this entry &raquo;</p>', TEMPLATE_DOMAIN ) ); ?>

							<?php wp_link_pages( array( 'before' => __( '<p><strong>Pages:</strong> ', TEMPLATE_DOMAIN ), 'after' => '</p>', 'next_or_number' => 'number')); ?>
						</div>

					</div>

					<?php do_action( 'bp_after_blog_post' ) ?>

				<?php comments_template(); ?>

				<?php endwhile; else: ?>

					<p><?php _e( 'Sorry, no attachments matched your criteria.', TEMPLATE_DOMAIN ) ?></p>

				<?php endif; ?>
		</div>

		</div>
		<?php get_footer(); ?>