<div id="sidebar">
		<?php if ( is_active_sidebar( '3colright-sidebar' ) ) : ?>
				<?php dynamic_sidebar( '3colright-sidebar' ); ?>
			<?php else : ?>
					<div class="widget-error">
						<?php _e( 'Please log in and add widgets to this column.', TEMPLATE_DOMAIN ) ?> <a href="<?php echo get_option('siteurl') ?>/wp-admin/widgets.php?s=&amp;show=&amp;sidebar=3colright-sidebar"><?php _e( 'Add Widgets', TEMPLATE_DOMAIN ) ?></a>
					</div>
		<?php endif; ?>
</div>
