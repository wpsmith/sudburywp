<?php
/*
Template Name: Special - Links List Page
*/
inject_content( function ( $content ) {
	global $post;
	$posts       = get_posts( array(
		'post_type'      => 'link',
		'posts_per_page' => - 1,
		'orderby'        => 'post_date',
	) );
	$total_links = 0; ?>


	<?php if ( ! empty( $posts ) ) :
		$all_terms = array();
		ob_start();
		foreach ( $posts as $post ) : setup_postdata( $post );
			$total_links ++;
			$terms = get_the_category_list( ',', 'single' );
			if ( $terms ) {
				$terms     = explode( ',', $terms );
				$all_terms = array_unique( array_merge( $all_terms, $terms ) );
			}
			?>
			<?php get_template_part( 'content', 'link' ); ?>
			<?php wp_reset_postdata(); ?>
		<?php endforeach; ?>
		<?php
		$list_of_links = ob_get_clean();
		if ( count( $all_terms ) > 0 ) {
			?>
			<h3>Link Categories</h3>
			<hr>
			<?php
			sort( $all_terms );

			$cols = array_chunk( $all_terms, ceil( count( $all_terms ) / 3 ) );
			foreach ( $cols as $col ) :
				?>

				<div class="categories-list">
					<ul>
						<?php foreach ( $col as $term ) : ?>
							<li><?php echo $term; ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
			<div class="clear"></div>
			<p>&nbsp;</p>
		<?php } ?>
		<h3>Links</h3>
		<hr>
		<?php echo $list_of_links; ?>


		<p class="post-count links-count"> <?php bloginfo( 'blogname' ); ?> has <?php echo esc_html( $total_links ); ?> <?php echo _n( 'Link', 'Links', $total_links ); ?> </p>

	<?php else : ?>
		<p class="no-documents"> <?php echo apply_filters( 'sudbury_no_posts_label', get_post_type_object( 'link' )->labels->not_found ); ?> </p>

	<?php endif; ?>

<?php } );

get_template_part( 'page' );