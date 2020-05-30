<?php
/*
Template Name: Special - FAQs List Page
*/

inject_content( function ( $content ) {
	global $post;
	$args = array(
		'post_type'      => 'faq',
		'posts_per_page' => - 1,
		'orderby'        => 'post_date',
	);

	if ( $show_expired = ( isset( $_REQUEST['include-archived'] ) || is_archived( get_current_blog_id() ) ) ) {
		$args['post_status'] = array( 'publish', 'public-archive' );
	} else {
		$args['post_status'] = array( 'publish' );
	}

	$posts = get_posts( $args );

	$total_faqs = 0; ?>


	<?php if ( ! empty( $posts ) ) :
		$all_terms = array();
		ob_start();
		foreach ( $posts as $post ) : setup_postdata( $post );
			$total_faqs ++;
			$terms = get_the_category_list( ',', 'single' );
			if ( $terms ) {
				$terms     = explode( ',', $terms );
				$all_terms = array_unique( array_merge( $all_terms, $terms ) );
			}

			?>
			<?php get_template_part( 'content', 'faq' ); ?>
			<?php wp_reset_postdata(); ?>
		<?php endforeach;

		$list_of_faqs = ob_get_clean();
		if ( count( $all_terms ) > 0 ) {
			?>
			<h3>FAQ Categories <b><span class="section-header-links">
		<?php if ( ! $show_expired ) { ?>
			<a href="?include_archived">Include Archived Items</a>
		<?php } else { ?>
			<a href="?">Showing Archived Documents</a>
		<?php } ?></span></b>

			</h3>
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
		<h3>FAQs</h3>
		<hr>
		<?php echo $list_of_faqs; ?>


		<p class="post-count faqs-count"> <?php bloginfo( 'blogname' ); ?> has <?php echo esc_html( $total_faqs ); ?> <?php echo _n( 'FAQ', 'FAQs', $total_faqs ); ?> </p>

	<?php else : ?>
		<p class="no-documents"> <?php echo apply_filters( 'sudbury_no_posts_label', get_post_type_object( 'faq' )->labels->not_found ); ?> </p>

	<?php endif; ?>

	<?php
} );

get_template_part( 'page' );
