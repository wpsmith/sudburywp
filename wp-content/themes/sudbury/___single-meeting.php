<?php
/**
 * The Template for displaying all single posts.
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 * @since      Twenty Eleven 1.0
 */

get_header(); ?>

<?php sudbury_department_tabs(); ?>
<div id="main-col" <?php sudbury_main_col_class(); ?>>
	<?php if ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'content-single', get_post_type() ); ?>

		<?php if ( is_internal() || comments_open() ) :
			comments_template( '', true );
		endif; ?>
	<?php endif; ?>

</div><!-- #main-col -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
