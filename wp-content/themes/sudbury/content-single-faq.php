<?php
/**
 * The template for displaying content in the single-faq.php template
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 * @since      Twenty Eleven 1.0
 */
?>

<div class="meta-info">

	<span class="meta-info-published">Published on <a href="<?php echo esc_url( get_month_link( get_the_date( 'Y' ), get_the_date( 'm' ) ) ); ?>"><?php the_date( 'l, n/j/Y g:i a' ); ?></a> | by <a href="<?php sudbury_the_site_url(); ?>"><?php sudbury_the_site_name(); ?></a></span>
	<?php if ( get_the_modified_date( 'U' ) > ( intval( get_the_date( 'U' ) ) + 3600 ) )  : ?>
		<span class="meta-info-updated"> | Updated on <a href="<?php echo esc_url( get_month_link( get_the_modified_date( 'Y' ), get_the_modified_date( 'm' ) ) ); ?>"><?php the_modified_date( 'l, n/j/Y g:i a' ); ?></a></span>
	<?php endif; ?>
</div>


<?php if ( has_post_thumbnail() ) : ?>
	<?php the_post_thumbnail( 'medium' ); ?>
<?php endif; ?>

<?php if ( sudbury_is_post_archived() ) : ?>
	<p class="archived-message"><?php sudbury_the_archived_post_message(); ?></p>
<?php endif; ?>

<div class="tablecap documents-table">
	<h4><?php the_title(); ?></h4>
	<table cellspacing="0" style="display: table;">
		<thead>
		<tr>
			<th>Answer</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td style="vertical-align: top;">
				<?php the_content(); ?>
			</td>
		</tr>
		</tbody>
	</table>
	<div class="foot"></div>
</div>
