<?php if ( is_singular() && get_post_type() == 'location' ) : ?>
	<?php get_template_part( 'sidebar', 'location' ); ?>
<?php else : ?>
	<div id="right-col" class="sidebar layout-<?php sudbury_the_post_layout() ?> col-md-4">
		<div id="right-col-details">
			<?php if ( ! dynamic_sidebar( 'sidebar-1' ) ) : ?>
				<p>No Sidebar Defined for sidebar-1</p>
			<?php endif; // end sidebar widget area ?>
		</div>
	</div>
<?php endif; ?>

