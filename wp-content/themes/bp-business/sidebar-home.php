<?php if ( is_active_sidebar( 'home-sidebar' ) ) : ?>
		<?php dynamic_sidebar( 'home-sidebar' ); ?>
	<?php else : ?>
			<div class="widget-error">
				<?php _e( 'Please log in and add widgets to this column.', TEMPLATE_DOMAIN ) ?> <a href="<?php echo get_option('siteurl') ?>/wp-admin/widgets.php?s=&amp;show=&amp;sidebar=home-sidebar"><?php _e( 'Add Widgets', TEMPLATE_DOMAIN ) ?></a>
			</div>
<?php endif; ?>