<?php
/**
 * The default template for displaying content
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 * @since      Twenty Eleven 1.0
 */
?>

<div class="tablecap link-<?php the_ID(); ?>">
	<h4><span class="faq-drop-cap"></span>
		<a href="<?php the_permalink(); ?>" class="coltitle"><?php the_title(); ?></a>
		<span class="pull-right"><?php sudbury_edit_post_link( 'Edit' ); ?></span>
	</h4>
	<table cellspacing="0">
		<tbody>
		<?php if ( ( $content = get_post()->post_excerpt ) ) : //|| ( $content = get_the_content() ) ) : ?>
			<tr>
				<td>
					<div class="tablep faq-answer">
						<?php echo $content; ?>
					</div>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( $cats = get_the_category_list( ', ' ) ) : ?>
			<tr class="faq-categories">
				<td>
					<div class="tablep faq-answer">
						<b>Categories:</b>
						<?php the_category( ', ' ); ?>
					</div>
				</td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>
	<div class="foot"></div>
</div>

