<?php get_header(); ?>

	<div id="content">
		<?php if($bp_existed == 'true') : ?>
			<?php do_action( 'bp_before_404' ) ?>
		<?php endif; ?>
		<div class="page-404">
			<h3 class="pagetitle"><?php _e( 'Page Not Found', TEMPLATE_DOMAIN ) ?></h3>
			<div id="message" class="info">
				<p><?php _e( 'The page you were looking for was not found.', TEMPLATE_DOMAIN ) ?>
			</div>
			<?php if($bp_existed == 'true') : ?>
				<?php do_action( 'bp_404' ) ?>
		<?php endif; ?>
		</div>
		<?php if($bp_existed == 'true') : ?>
			<?php do_action( 'bp_after_404' ) ?>
		<?php endif; ?>
	</div>
	<?php get_sidebar('404'); ?>
<?php get_footer(); ?>