<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 * @since      Twenty Eleven 1.0
 */
get_header(); ?>

<div id="cont">
	<div class="container-fluid container-fluid-xl">
		<main class="row">
			<?php sudbury_department_tabs(); ?>
			<main id="main-col" <?php sudbury_main_col_class(); ?>>
				<?php if ( have_posts() ) : ?>

					<header class="page-header">
						<h1 class="page-title">
							<?php if ( is_day() ) : ?>
								<?php printf( __( 'Daily Archives %s', 'sudbury' ), '<span>' . esc_html( get_the_date() ) . '</span>' ); ?>
							<?php elseif ( is_month() ) : ?>
								<?php printf( __( 'Monthly Archives %s', 'sudbury' ), '<span>' . esc_html( get_the_date( _x( 'F Y', 'monthly archives date format', 'sudbury' ) ) ) . '</span>' ); ?>
							<?php
							elseif ( is_year() ) : ?>
								<?php printf( __( 'Yearly Archives %s', 'sudbury' ), '<span>' . esc_html( get_the_date( _x( 'Y', 'yearly archives date format', 'sudbury' ) ) ) . '</span>' ); ?>
							<?php
							else : ?>
								<?php _e( 'Archives', 'sudbury' ); ?>
							<?php endif; ?>
						</h1>
					</header>
					<div class="articles">


						<?php /* Start the Loop */ ?>
						<?php while ( have_posts() ) : the_post(); ?>

							<?php
							/* Include the Post-Format-specific template for the content.
							 * If you want to overload this in a child theme then include a file
							 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
							 */
							get_template_part( 'content', get_post_type() );
							?>

						<?php endwhile; ?>

						<?php sudbury_content_nav( 'nav-below' ); ?>
					</div>
				<?php else : ?>
					<div class="articles">
						<div id="post-0" class="post no-results not-found">

							<!-- .entry-header -->

							<div class="entry-content">
								<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'sudbury' ); ?></p>
								<?php get_search_form(); ?>
							</div>
							<!-- .entry-content -->
						</div><!-- #post-0 -->
					</div>
				<?php endif; ?>
			</main>
			<?php get_sidebar(); ?>
	</div>
</div>
</div>
<?php get_footer(); ?>



