<?php
/**
 * Renders the Attachments box for displaying attached files for a news article or meeting
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Sudbury / Theme
 */

$attachments = new Attachments( 'attachments' ); /* pass the instance name */
?>

<?php if ( $attachments->exist() ) : ?>
	<h2>Attachments</h2>
	<?php
	$i = 0;
	while ( $attachments->get() ) : $i ++; ?>
		<?php global $post; ?>
		<?php $post = get_post( $attachments->id() ); ?>
		<?php setup_postdata( $post ); ?>

		<div class="attachments-attachment row">
			<div class="col-md-1">
				<a class="attachment_thumbnail" href="<?php echo esc_url( $attachments->url() ); ?>"><?php echo $attachments->image( 'thumbnail' ); ?></a>
			</div>
			<div class="col-md-11">
				<a class="attachment_title" href="<?php echo esc_url( $attachments->url() ); ?>"><?php echo $post->post_title; ?></a>
				<div class="meta-info">
					<?php get_template_part( 'parts/meta', get_post_type() ); ?> |
					<a href="<?php echo get_post_permalink( $attachments->id() ); ?>">Details</a>
				</div>
				<p class="attachment_caption"><?php echo $post->post_excerpt; ?></p>
			</div>
		</div>
	<?php endwhile; ?>
	<?php wp_reset_postdata(); ?>

<?php elseif ( get_post_type() == 'meeting' ) : ?>
	<h2>Attachments</h2>

	<p><i>Meeting minutes and agendas will be posted to this page as they become available.</i></p>

<?php endif; ?>