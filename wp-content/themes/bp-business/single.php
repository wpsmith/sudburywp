<?php get_header(); ?>
<div id="content-wrapper">
	<div id="content">
			<?php if($bp_existed == 'true') : ?>
				<?php do_action( 'bp_before_blog_single_post' ) ?>
		<?php endif; ?>
		<div class="content-page" id="blog-single">
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<?php locate_template( array( '/elements/blog-headers.php' ), true ); ?>
				<div class="post" id="post-<?php the_ID(); ?>">

					<?php do_action( 'bp_before_blog_post' ) ?>

					<h3 class="pagetitle"><?php the_title(); ?></h3>
							<?php if( $bp_existed == 'true' ) { //check if bp existed ?>
					<p class="date"><?php the_time('M j Y') ?><?php _e( 'in', TEMPLATE_DOMAIN ) ?> <?php the_category(', ') ?> <?php printf( __( 'by %s', TEMPLATE_DOMAIN ), bp_core_get_userlink($post->post_author) ) ?><span class="tags"><?php the_tags( __( 'Tags: ', TEMPLATE_DOMAIN ), ', ', '<br />'); ?></span> <span class="comments"><?php comments_popup_link( __( 'No Comments &#187;', TEMPLATE_DOMAIN ), __( '1 Comment &#187;', TEMPLATE_DOMAIN ), __( '% Comments &#187;', TEMPLATE_DOMAIN ) ); ?></span></p>
							<?php } else { // if not bp detected..let go normal ?>
						<p class="date"><?php the_time() ?><?php _e( 'in', TEMPLATE_DOMAIN ) ?> <?php the_category(', ') ?> <?php the_author_link(); ?><span class="tags"><?php the_tags( __( 'Tags: ', TEMPLATE_DOMAIN ), ', ', '<br />'); ?></span> <span class="comments"><?php comments_popup_link( __( 'No Comments &#187;', TEMPLATE_DOMAIN ), __( '1 Comment &#187;', TEMPLATE_DOMAIN ), __( '% Comments &#187;', TEMPLATE_DOMAIN ) ); ?></span></p>
							<?php } ?>
					<div class="entry">

						<?php the_content( __( '<p class="serif">Read the rest of this entry &raquo;</p>', TEMPLATE_DOMAIN ) ); ?>

						<?php wp_link_pages(array('before' => __( '<p><strong>Pages:</strong> ', TEMPLATE_DOMAIN ), 'after' => '</p>', 'next_or_number' => 'number')); ?>
		<div class="clear"></div>
					</div>

				</div>

			<?php comments_template(); ?>

				<?php endwhile;?>

				<?php locate_template( array( '/elements/pagination.php' ), true ); ?>

				<?php else: ?>

				<?php locate_template( array( '/elements/messages.php' ), true ); ?>

				<?php endif; ?>

		</div>
		</div>
		<?php if($bp_existed == 'true') : ?>
			<?php do_action( 'bp_after_blog_single_post' ) ?>
		<?php endif; ?>
	<?php get_sidebar(); ?>
<?php get_footer(); ?>