<?php
/**
 * The default template for displaying content
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 * @since      Twenty Eleven 1.0
 */
?>

<div class="tablecap">
	<div class="cap">
		<a href="<?php the_permalink(); ?>" class="coltitle"><?php the_title(); ?></a>
		<span class="pull-right"><?php sudbury_edit_post_link( 'Edit' ); ?></span>
	</div>
	<table cellspacing="0">
		<tbody>
		<tr>
			<td>
				<div class="tablep faq-answer">
					<?php the_content(); ?>
				</div>
			</td>
		</tr>
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

