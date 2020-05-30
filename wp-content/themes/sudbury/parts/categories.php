<?php foreach ( get_object_taxonomies( get_post_type() ) as $tax ) : ?>
	<?php if ( has_term( '', $tax, get_the_ID() ) ) : ?>
		<div class="categories">
			<h3><?php echo esc_html( get_taxonomy( $tax )->labels->name ); ?></h3>

			<p><?php the_category( ', ', 'multiple' ); ?></p>
		</div>
	<?php endif; ?>
<?php endforeach; ?>