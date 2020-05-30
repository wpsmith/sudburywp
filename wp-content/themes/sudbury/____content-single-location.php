<?php
/**
 * The template for displaying content in the single.php template
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 * @since      Twenty Eleven 1.0
 */
?>
<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
<div class="meta-info">
	<span class="meta-info-updated">Updated on <?php the_modified_date( 'l, F j, Y' ); ?></span>
</div>
<div class="news-article">
	<?php if ( has_post_thumbnail() ) : ?>
		<?php the_post_thumbnail( 'medium' ); ?>
	<?php endif; ?>


	<?php the_content(); ?>

	<?php get_template_part( 'attachments' ); ?>


	<?php sudbury_social_links( array( 'return' => true, 'email' => true ) ); ?>
	<?php edit_post_link( 'Edit Location' ); ?>
</div> <!-- .news-article -->

   
