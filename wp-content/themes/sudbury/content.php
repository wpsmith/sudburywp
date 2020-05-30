<?php
/**
 * Template part for displaying posts
 *
 * @link    https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WP_Bootstrap_Starter
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'row' ); ?>>
	<?php if ( current_theme_supports( 'post-thumbnails' ) ): ?>
		<div class="post-thumbnail<?php if ( ! has_post_thumbnail() ) : ?> has-icon<?php endif; ?> col-md-3">
			<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
				<?php if ( has_post_thumbnail() ) : ?>
					<span class="helper"></span><?php the_post_thumbnail( 'post-thumbnail' ); ?>
				<?php else: ?>
					<span class="helper"></span><i class="fa fa-newspaper"></i>
				<?php endif; ?>
			</a>

		</div>
	<?php endif; ?>
	<div class="<?php esc_attr_e( current_theme_supports( 'post-thumbnails' ) ? 'col-md-9' : 'col-md-12' ); ?> entry">
		<header class="entry-header ">
			<?php
			if ( is_single() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else : ?>
				<h3 class="entry-title">
					<a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark">
						<?php the_title(); ?>
					</a>
				</h3>
			<?php endif; ?>

			<div class="entry-meta">
				<?php get_template_part( 'parts/meta', get_post_type() ); ?>
			</div><!-- .entry-meta -->

		</header><!-- .entry-header -->
		<div class="entry-content">
			<?php
			if ( is_single() ) :
				the_content();
			else :
				the_excerpt();
			endif;

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wp-bootstrap-starter' ),
				'after'  => '</div>',
			) );
			?>
		</div><!-- .entry-content -->
	</div>
</article><!-- #post-## -->

