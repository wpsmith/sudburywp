<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

	<head profile="http://gmpg.org/xfn/11">

		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<?php include (get_template_directory() . '/options.php'); ?>
		<title><?php wp_title( '|', true, 'right' ); bloginfo( 'name' ); ?></title>
		<?php if($bp_existed == 'true') : ?>
			<?php do_action( 'bp_head' ) ?>
		<?php endif; ?>
			<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
			<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
			<link rel="icon" href="<?php bloginfo('stylesheet_directory');?>/favicon.ico" type="images/x-icon" />
			<?php wp_head(); ?>
					<script type="text/javascript">
					    jQuery(document).ready(function() {
						   jQuery.noConflict();

						     // Put all your code in your document ready area
						     jQuery(document).ready(function(){
						       // Do jQuery stuff using $
							 	jQuery(function(){
								 jQuery("ul.sf-menu").supersubs({
								            minWidth:    12,   // minimum width of sub-menus in em units
								            maxWidth:    27,   // maximum width of sub-menus in em units
								            extraWidth:  1     // extra width can ensure lines don't sometimes turn over
								                               // due to slight rounding differences and font-family
								        }).superfish();  // call supersubs first, then superfish, so that subs are
								                         // not display:none when measuring. Call before initialising
								                         // containing tabs for same reason.
								});
						    });
					    });
					</script>
		<!--[if lt IE 7]>
		<script src="<?php echo get_template_directory_uri(); ?>/_inc/js/supersleight-min.js" type="text/javascript"></script>
		<![endif]-->
	</head>

	<body <?php body_class() ?>>
			<?php if($bp_existed == 'true') : ?>
				<?php do_action( 'bp_before_header' ) ?>
			<?php endif ?>
		<div id="header-wrapper">
			<div id="header">
				<div id="search-box">

					<?php
						locate_template( array( '/elements/searchform.php' ), true );
?>
				</div>
				<div id="logo">
	<?php
		$logo_on = get_option('ne_buddybusiness_header_image');
		$logo_image = get_option('ne_buddybusiness_header_logo');
		$description_on = get_option('ne_buddybusiness_header_description_on');
		$description = get_option('ne_buddybusiness_header_description');
		$square_logo = get_option('ne_buddybusiness_header_image_square');
		$square_image = get_option('ne_buddybusiness_header_logo_square');
		$site_title = get_option('ne_buddybusiness_header_title');
	?>
	<?php

	if($logo_on == "no" && $square_logo == "yes"){
		?>
		<a href="<?php echo home_url(); ?>" title="<?php _e( 'Home', TEMPLATE_DOMAIN ) ?>"><img src="<?php echo $square_image; ?>" alt="<?php bloginfo('name'); ?>" class="logo-square"></a>
		<h1 class="square-header"><a href="<?php echo home_url(); ?>" title="<?php _e( 'Home', TEMPLATE_DOMAIN ) ?>"><?php echo stripslashes($site_title); ?></a></h1>
	<?php
	}
	else if($logo_on == "yes" && $square_logo == "no"){
		?>
		<a href="<?php echo home_url(); ?>" title="<?php _e( 'Home', TEMPLATE_DOMAIN ) ?>"><img src="<?php echo $logo_image; ?>" alt="<?php bloginfo('name'); ?>"></a>
	<?php
	}
	else{
	?>
		<h1><a href="<?php echo home_url(); ?>" title="<?php _e( 'Home', TEMPLATE_DOMAIN ) ?>"><?php echo stripslashes(bloginfo( 'name' )); ?></a></h1>
	<?php
	}

	?>
			<div class="clear"></div>
	<?php

	if($description_on == "yes"){
		?>
		<h2 class="description"><?php echo stripslashes(bloginfo( 'description' )); ?></h2>
	<?php
	}
	else{
	?>

	<?php
	}

	?>
				</div>
				<div class="clear"></div>
			</div>
		</div>

			<div class="navigation-wrapper">
				<div class="top-navigation">
					<div class="navigation-block">
					<?php
						if ( has_nav_menu('header-nav') ):
							wp_nav_menu( array( 'theme_location' => 'header-nav',
											'container' => false,
											'fallback_cb' => false,
											'menu_class' => 'sf-menu'
											));
					?>
					<?php elseif( $bp_existed == 'true' ) : //check if bp existed ?>
					<?php locate_template( array( '/includes/main-navigation.php' ), true ); ?>
					<?php else: // if not bp detected..let go normal ?>
						<ul class="sf-menu">
							<li<?php if ( is_front_page()) : ?> class="selected"<?php endif; ?>>
								<a href="<?php echo site_url() ?>" title="<?php _e( 'Home', TEMPLATE_DOMAIN ) ?>"><?php _e( 'Home', TEMPLATE_DOMAIN ) ?></a></li>
							<?php
								$feature_navigation = get_option('ne_buddybusiness_feature_navigation');
								$feature_page = get_option('ne_buddybusiness_feature_id');
								$member_navigation = get_option('ne_buddybusiness_community_navigation');
								$member_page = get_option('ne_buddybusiness_community_id');
							?>
							<?php $featured_pageID = wt_get_ID_by_page_name($feature_page); ?>
							<?php $member_pageID = wt_get_ID_by_page_name($member_page); ?>

							<?php
								if ($feature_navigation == "no" && $member_navigation == "no"){ wp_list_pages('exclude='. $featured_pageID . ','. $member_pageID . '&title_li='); }
								else if ($feature_navigation == "no" && $member_navigation == "yes"){ wp_list_pages('exclude='. $featured_pageID . '&title_li='); }
								else if ($feature_navigation == "yes" && $member_navigation == "no"){ wp_list_pages('exclude='. $member_pageID . '&title_li='); }
								else{ wp_list_pages('title_li='); }
							?>
						</ul>
					<?php endif; ?>
						<div class="clear"></div>
					</div>
				</div>
			</div>

			<?php
			if( $bp_existed == 'true' ){
				do_action( 'bp_header' );
				do_action( 'bp_after_header' );
				do_action( 'bp_before_container' );
			}

			if(!is_home()){ ?>
				<div id="content-wrapper">
				<div id="page-wrapper">
			<?php } ?>