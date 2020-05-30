	<div id="pageheader" class="titleclass">
		<div class="container">
			<?php get_template_part('templates/page', 'header'); ?>
		</div><!--container-->
	</div><!--titleclass-->
	
    <div id="content" class="container">
   		<div class="row">
      <div class="main <?php echo esc_attr( kadence_main_class() ); ?>" role="main">
<div class="alert">
  <p><?php _e('Sorry, but the page you were trying to view does not exist.', 'virtue'); ?></p>
</div>

<p><?php _e('We have recently launched our new website and it looks like you may have found a bad link. Our apologies.', 'virtue'); ?></p>

<p><?php _e('Please use the menu above to navigate to the page you were looking for, OR use the search box here to search for what you need:', 'virtue'); ?></p>
<?php get_search_form(); ?>
<p>&nbsp;</p>
<p><?php _e('Thank you for your patience.', 'virtue'); ?></p>

</div>