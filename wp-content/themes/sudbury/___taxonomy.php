<?php
/**
 * Description
 *
 * @author     Eddie Hurtig <hurtige@ccs.neu.edu>
 * @since      1.0
 * @version    1.0
 * @package    ShortLinks
 * @subpackage ShortLinks / Theme
 */

$taxonomy = get_query_var( 'taxonomy' );
$taxonomy = get_taxonomy( $taxonomy );

$term = get_query_var( $taxonomy->name );
$term = get_term_by( 'slug', $term, $taxonomy->name );


get_header(); ?>

	<div id="body" class="news_article">

	<?php sudbury_department_tabs(); ?>
<div id="main-col" <?php sudbury_main_col_class(); ?>>
<?php if ( have_posts() ) : ?>

	<header class="page-header">
		<h1 class="page-title">
			<?php if ( is_day() ) : ?>
				<?php printf( __( 'Daily Archives: %s', 'sudbury' ), '<span>' . esc_html( get_the_date() ) . '</span>' ); ?>
			<?php elseif ( is_month() ) : ?>
				<?php printf( __( 'Monthly Archives: %s', 'sudbury' ), '<span>' . esc_html( get_the_date( _x( 'F Y', 'monthly archives date format', 'sudbury' ) ) ) . '</span>' ); ?>
			<?php
			elseif ( is_year() ) : ?>
				<?php printf( __( 'Yearly Archives: %s', 'sudbury' ), '<span>' . esc_html( get_the_date( _x( 'Y', 'yearly archives date format', 'sudbury' ) ) ) . '</span>' ); ?>
			<?php
			else : ?>
				<?php _e( $taxonomy->labels->singular_name . ' Archives for ' . $term->name, 'sudbury' ); ?>
			<?php endif; ?>
		</h1>
	</header>
	<div class="news-article">


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
	<div class="news-article">
	<article id="post-0" class="post no-results not-found">
		<!-- .entry-header -->

		<div class="entry-content">
			<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'sudbury' ); ?></p>
			<?php get_search_form(); ?>
		</div>
		<!-- .entry-content -->
	</article><!-- #post-0 -->
	<div class="news-article">
<?php endif; ?>

	</div>
	</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>