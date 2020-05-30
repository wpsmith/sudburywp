</div>
<hr /><footer role="contentinfo" id="footer">  
  <div id="inner-footer" class="clearfix container padding-top-bottom">
  	<?php $mywiki_options = get_option( 'faster_theme_options' ); ?>
	<div id="widget-footer" class="clearfix row">
    	<div class="col-md-4">
		  <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer1') ) : ?>
          <?php endif; ?>
         </div>
         <div class="col-md-4">
		  <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer2') ) : ?>
        <?php endif; ?>
		</div>
        <div class="col-md-4">
		  <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer3') ) : ?>
        <?php endif; ?>
		</div>
    </div>
        <nav class="footer-menu-nav">
     	<ul class="footer-nav nav navbar-nav">
        	<?php if((!empty($mywiki_options['fburl'])) || (!empty($mywiki_options['twitter'])) || (!empty($mywiki_options['googleplus'])) || (!empty($mywiki_options['linkedin']))) { ?>
        	<li><a><?php _e('Follow Us :', 'mywiki'); ?></a></li>
            <?php } if(!empty($mywiki_options['fburl'])) { ?>
        	<li class="facebook_icon socia_icon"><a href="<?php echo esc_url($mywiki_options['fburl']); ?>" target="_blank"></a></li>
            <?php } if(!empty($mywiki_options['twitter'])) { ?>
            <li class="twitter_icon socia_icon"><a href="<?php echo esc_url($mywiki_options['twitter']); ?>" target="_blank"></a></li>
            <?php } if(!empty($mywiki_options['googleplus'])) { ?>
            <li class="google_icon socia_icon"><a href="<?php echo esc_url($mywiki_options['googleplus']); ?>" target="_blank"></a></li>
            <?php } if(!empty($mywiki_options['linkedin'])) { ?>
            <li class="linkedin_icon socia_icon"><a href="<?php echo esc_url($mywiki_options['linkedin']); ?>" target="_blank"></a></li>
            <?php } ?>
        </ul>
    </nav>
    <p class="attribution">
	<?php if(!empty($mywiki_options['footertext']))
    echo esc_attr($mywiki_options['footertext']).' ';
    printf( __( 'Powered by %1$s.', 'mywiki' ),'<a href="http://fasterthemes.com/wordpress-themes/mywiki" target="_blank">Wiki WordPress Theme</a>' ); ?>
</footer>
    </p>
  </div>
  <!-- end #inner-footer -->
<!-- end footer -->
<!-- end #maincont .container --> 
<?php wp_footer(); // js scripts are inserted using this function ?>
</body>
</html>