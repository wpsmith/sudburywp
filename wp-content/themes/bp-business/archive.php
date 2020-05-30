<?php get_header(); ?>

			<?php if($bp_existed == 'true') : ?>
				<?php do_action( 'bp_before_archive' ) ?>
		<?php endif; ?>
		<div class="content-page" id="blog-archives">

			<?php if ( have_posts() ) : ?>
		<?php locate_template( array( '/elements/blog-headers.php' ), true ); ?>

				<?php while (have_posts()) : the_post(); ?>

									<?php do_action( 'bp_before_blog_post' ) ?>

									<div class="post" id="post-<?php the_ID(); ?>">

										<h3 class="pagetitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', TEMPLATE_DOMAIN ) ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>

												<?php if( $bp_existed == 'true' ) { //check if bp existed ?>
										<p class="date"><?php the_time('M j Y') ?><?php _e( 'in', TEMPLATE_DOMAIN ) ?> <?php the_category(', ') ?> <?php printf( __( 'by %s', TEMPLATE_DOMAIN ), bp_core_get_userlink($post->post_author) ) ?><span class="tags"><?php the_tags( __( 'Tags: ', TEMPLATE_DOMAIN ), ', ', '<br />'); ?></span> <span class="comments"><?php comments_popup_link( __( 'No Comments &#187;', TEMPLATE_DOMAIN ), __( '1 Comment &#187;', TEMPLATE_DOMAIN ), __( '% Comments &#187;', TEMPLATE_DOMAIN ) ); ?></span></p>
												<?php } else { // if not bp detected..let go normal ?>
											<p class="date"><?php the_time('M j Y') ?><?php _e( 'in', TEMPLATE_DOMAIN ) ?> <?php the_category(', ') ?> <?php the_author_link(); ?><span class="tags"><?php the_tags( __( 'Tags: ', TEMPLATE_DOMAIN ), ', ', '<br />'); ?></span> <span class="comments"><?php comments_popup_link( __( 'No Comments &#187;', TEMPLATE_DOMAIN ), __( '1 Comment &#187;', TEMPLATE_DOMAIN ), __( '% Comments &#187;', TEMPLATE_DOMAIN ) ); ?></span></p>
												<?php } ?>

										<div class="entry">
													<?php do_action( 'bp_blog_post' ) ?>

																<a href="<?php the_permalink() ?>">
																	<?php if(function_exists('the_post_thumbnail')) { ?><?php if(get_the_post_thumbnail() != "") { ?><div class="alignleft">
																	<?php the_post_thumbnail(); ?></div><?php } } ?>
																</a>
											<?php the_excerpt(); ?>
												<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', TEMPLATE_DOMAIN ) ?> <?php the_title_attribute(); ?>" class="button"><?php _e( 'Read more', TEMPLATE_DOMAIN ) ?></a>
											<div class="clear"></div>
										</div>
									</div>

									<?php do_action( 'bp_after_blog_post' ) ?>

						<?php endwhile; ?>

						<?php locate_template( array( '/elements/pagination.php' ), true ); ?>

					<?php else : ?>

								<?php locate_template( array( '/elements/messages.php' ), true ); ?>

					<?php endif; ?>

		</div>

		<?php if($bp_existed == 'true') : ?>
			<?php do_action( 'bp_after_archive' ) ?>
		<?php endif; ?>
		<?php get_sidebar(); ?>

		<?php get_footer(); ?>