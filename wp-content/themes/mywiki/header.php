<?php
$mywiki_options = get_option( 'faster_theme_options' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if(!empty($mywiki_options['favicon'])) { ?>
<link rel="shortcut icon" href="<?php echo esc_url($mywiki_options['favicon']);?>">
<?php } ?>
<!--[if lt IE 9]>
			<script src="<?php echo get_template_directory_uri(); ?>/js/html5shiv.js"></script>
<![endif]-->
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div id="wrap">
<header role="banner">
  <div id="inner-header" class="clearfix">
    <div class="navbar navbar-default top-bg">
      <div class="container" id="navbarcont">
        <div class="nav-container col-md-9">
          <nav role="navigation">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
              <a class="navbar-brand logo" id="logo" title="<?php echo get_bloginfo('description'); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php if(!empty($mywiki_options['logo'])) { echo '<img src='.esc_url($mywiki_options['logo']).'  height="101" width="250" alt="logo" />'; } else { echo'<p><span class="header-text">'.bloginfo("name").'</span></p>'; } ?></a>
            </div>
            <!-- end .navbar-header -->
          </nav>
        </div>
        <div class="navbar-collapse collapse top-menu">
          <?php
			$mywiki_defaults = array(
					'theme_location'  => 'primary',
					'container'       => 'div',
					'container_class' => '',
					'container_id'    => '',
					'menu_class'      => '',
					'menu_id'         => '',
					'echo'            => true,
					'fallback_cb'     => 'wp_page_menu',
					'before'          => '',
					'after'           => '',
					'link_before'     => '',
					'link_after'      => '',
					'items_wrap'      => '<ul id="menu" class="nav navbar-nav navbar-right mywiki-header-menu">%3$s</ul>',
					'depth'           => 0,
					'walker'          => ''
					);
			wp_nav_menu( $mywiki_defaults ); ?>
        </div>
        <!-- end .nav-container -->
      </div>
      <!-- end #navcont -->
    </div>
    <!-- end .navbar --> 
  </div>
  <!-- end #inner-header --> 
</header>
<!-- end header -->
<div class="searchwrap col-md-3">
  <div class="search-main container" id="search-main">
    <form class="navbar-form navbar-right form-inline asholder" role="search" method="get" id="searchformtop" action="<?php echo site_url(); ?>">
      <div class="input-group" id="suggest">
        <input name="s" id="s" type="text" onKeyUp="suggest(this.value);" onBlur="fill();" class="search-query form-control pull-right" autocomplete="off" placeholder="<?php _e('Have a Question? Write here and press enter','mywiki') ?>" data-provide="typeahead" data-items="4" data-source=''>
        <div class="suggestionsbox" id="suggestions" style="display: none;"> <img src="<?php echo get_template_directory_uri().'/img/arrow1.png'; ?>" height="18" width="27" class="upArrow" alt="upArrow" />
          <div class="suggestionlist" id="suggestionslist"></div>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="container margin-top" id="maincnot">