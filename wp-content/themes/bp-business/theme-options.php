a.button, a:link.button, a:visited.button, a:hover.button{
	color: <?php if($ne_buddybusiness_button_text == ""){ ?><?php echo "#fff"; } else { ?><?php echo $ne_buddybusiness_button_text; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_body_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_body_font; ?><?php } ?>;
}

a, a:link, a:visited, a:hover{
	color: <?php if($ne_buddybusiness_link_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_link_colour; ?><?php } ?>;
}

a:hover{
	color: <?php if($ne_buddybusiness_link_hover_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_link_colour; ?><?php } ?>;
}

#activity-list li, .item-list-tabs li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

div#item-header h3 span.highlight span, #activity-rss{
	border: 1px solid <?php if($ne_buddybusiness_box_border_colour == ""){ ?><?php echo "#EBEBEB"; } else { ?><?php echo $ne_buddybusiness_box_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_box_background_colour == ""){ ?><?php echo "#F8F8F8"; } else { ?><?php echo $ne_buddybusiness_box_background_colour; ?><?php } ?>;
}

#activity-filter-links li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

.alert{
	border: 1px solid <?php if($ne_buddybusiness_information_border_colour == ""){ ?><?php echo "#aaaaaa"; } else { ?><?php echo $ne_buddybusiness_information_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_information_background_colour == ""){ ?><?php echo "#eeeeee"; } else { ?><?php echo $ne_buddybusiness_information_background_colour; ?><?php } ?>;
}

body{
	color: <?php if($ne_buddybusiness_text_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_text_colour; ?><?php } ?>;
	background: <?php if($ne_buddybusiness_footer_background_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_footer_background_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_body_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_body_font; ?><?php } ?>;
}

#bp-nav li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_user_link_border_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_user_link_border_colour; ?><?php } ?>;
}

#bp-nav li a{
	color: <?php if($ne_buddybusiness_user_link_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_user_link_colour; ?><?php } ?>;
	background: <?php if($ne_buddybusiness_user_background_link_colour == ""){ ?><?php echo "#DDDDDD"; } else { ?><?php echo $ne_buddybusiness_user_background_link_colour; ?><?php } ?>
	url('<?php bloginfo('template_directory'); ?>/_inc/images/foward.png') no-repeat;
	background-position: 8px;
}

#bp-nav li a:link {
	color: <?php if($ne_buddybusiness_user_link_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_user_link_colour; ?><?php } ?>;
}

#bp-nav li a:visited {
	color: <?php if($ne_buddybusiness_user_link_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_user_link_colour; ?><?php } ?>;
}

#bp-nav li a:hover, #bp-nav li.current a, #bp-nav li.current a:link, #bp-nav li.selected a, #bp-nav li.selected a:link, #bp-nav li.current_page_item a:link, #bp-nav li.current_page_item a{
	color: <?php if($ne_buddybusiness_user_link_hover_colour == ""){ ?><?php echo "#eeeeee"; } else { ?><?php echo $ne_buddybusiness_user_link_hover_colour; ?><?php } ?>;
	background: <?php if($ne_buddybusiness_user_background_hover_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_user_background_hover_colour; ?><?php } ?>
	url('<?php bloginfo('template_directory'); ?>/_inc/images/foward.png') no-repeat;
	background-position: 8px;
}

.content-header-nav li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

#content-wrapper{
	background-color: <?php if($ne_buddybusiness_content_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_content_colour; ?><?php } ?>;
}

.date{
	border: 1px solid <?php if($ne_buddybusiness_box_border_colour == ""){ ?><?php echo "#EBEBEB"; } else { ?><?php echo $ne_buddybusiness_box_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_box_background_colour == ""){ ?><?php echo "#F8F8F8"; } else { ?><?php echo $ne_buddybusiness_box_background_colour; ?><?php } ?>;
}

.entry{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

#featured-blogs-list li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}
#feature-wrapper{background-color: <?php if($ne_buddybusiness_featured_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_featured_colour; ?><?php } ?>;}
#feature-image{
	background-color: <?php if($ne_buddybusiness_images_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_images_colour; ?><?php } ?>;
}

#featured-members-list li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

#footer-links{
	color: <?php if($ne_buddybusiness_footer_link_colour == ""){ ?><?php echo "#dddddd"; } else { ?><?php echo $ne_buddybusiness_footer_link_colour; ?><?php } ?>;
}

#forum-topic-list li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

#footer-links a, #footer-links a:link, #footer-links a:visited, #footer-links a:hover{
	color: <?php if($ne_buddybusiness_footer_link_colour == ""){ ?><?php echo "#dddddd"; } else { ?><?php echo $ne_buddybusiness_footer_link_colour; ?><?php } ?>;
}

#footer-wrapper{
	background: <?php if($ne_buddybusiness_footer_background_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_footer_background_colour; ?><?php } ?>;
	border-top: 3px solid <?php if($ne_buddybusiness_footer_border_colour == ""){ ?><?php echo "#333333"; } else { ?><?php echo $ne_buddybusiness_footer_border_colour; ?><?php } ?>;
}

#friend-list li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

.generic-button{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

#global-forum-topic-filters li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

#global-forum-topic-list{
	border: 1px solid <?php if($ne_buddybusiness_box_border_colour == ""){ ?><?php echo "#EBEBEB"; } else { ?><?php echo $ne_buddybusiness_box_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_box_background_colour == ""){ ?><?php echo "#F8F8F8"; } else { ?><?php echo $ne_buddybusiness_box_background_colour; ?><?php } ?>;
}

#global-forum-topic-list th{
	background-color: <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

#global-forum-topic-list table{
	border: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

.group-forum{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

#groups-list li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

#groups-list .generic-button, #members-list .generic-button{
	border: none;
}

h1{
	font-size: 40px;
	color: <?php if($ne_buddybusiness_h1_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_h1_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
	text-shadow: 2px 2px 0 #111111;
}

h1 a, h1 a:link, h1 a:visited, h1 a:hover{
	color: <?php if($ne_buddybusiness_h1_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_h1_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
}

h2{
	color: <?php if($ne_buddybusiness_h2_colour == ""){ ?><?php echo "#cccccc"; } else { ?><?php echo $ne_buddybusiness_h2_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
}

hr{
	background-color: <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
	color: <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

h3{
	color: <?php if($ne_buddybusiness_h3_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_h3_colour; ?><?php } ?>;
	font-size: 18px;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
}

h3#comments-number, h3#reply{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
	color: <?php if($ne_buddybusiness_h3_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_h3_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
}

#latest h3{
	color: <?php if($ne_buddybusiness_latest_header_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_latest_header_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
}

.pagetitle, #settings-form h3, #profile-content h2{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
	color: <?php if($ne_buddybusiness_h3_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_h3_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
}


h4, h4 a, h4 a:link, h4 a:visited, h4 a:hover{
	color: <?php if($ne_buddybusiness_h4_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_h4_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
}

#header-wrapper{
	<?php if(($ne_buddybusiness_header_background_image == "")&&($ne_buddybusiness_header_background_colour != "")) { ?>
	background: <?php echo $ne_buddybusiness_header_background_colour; ?>
	<?php } ?>

	<?php if(($ne_buddybusiness_header_background_image != "")&&($ne_buddybusiness_header_background_colour != "")) { ?>
	background: <?php echo $ne_buddybusiness_header_background_colour; ?> url(<?php echo $ne_buddybusiness_header_background_image; ?>) <?php echo $ne_buddybusiness_header_image_repeat; ?>
	<?php } ?>

	<?php if(($ne_buddybusiness_header_background_image != "")&&($ne_buddybusiness_header_background_colour == "")) { ?>
	background: #5A5A5A url(<?php echo $ne_buddybusiness_header_background_image; ?>) <?php echo $ne_buddybusiness_header_image_repeat; ?>
	<?php } ?>

	<?php if(($ne_buddybusiness_header_background_image == "")&&($ne_buddybusiness_header_background_colour == "")) { ?>
	background: #5A5A5A url('<?php bloginfo('template_directory'); ?>/_inc/images/header_background.png') repeat-x
	<?php } ?>
	;
}

.horiz-gallery li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

img{
	border: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

img.avatar{
	border: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_images_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_images_colour; ?><?php } ?>;
}

.image{
	background-color: <?php if($ne_buddybusiness_images_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_images_colour; ?><?php } ?>;
	border: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

.info{
	border: 1px solid <?php if($ne_buddybusiness_information_border_colour == ""){ ?><?php echo "#aaaaaa"; } else { ?><?php echo $ne_buddybusiness_information_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_information_background_colour == ""){ ?><?php echo "#eeeeee"; } else { ?><?php echo $ne_buddybusiness_information_background_colour; ?><?php } ?>;
}

input[type="submit"]{
	border: 1px solid <?php if($ne_buddybusiness_button_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_button_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_button_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_button_colour; ?><?php } ?>;
	color: <?php if($ne_buddybusiness_button_text == ""){ ?><?php echo "#FFFFFF"; } else { ?><?php echo $ne_buddybusiness_button_text; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_body_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_body_font; ?><?php } ?>;
}

input[type="submit"].search-dark{
	border: 1px solid <?php if($ne_buddybusiness_dark_button_colour == ""){ ?><?php echo "#496D79"; } else { ?><?php echo $ne_buddybusiness_dark_button_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_dark_button_colour == ""){ ?><?php echo "#496D79"; } else { ?><?php echo $ne_buddybusiness_dark_button_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_body_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_body_font; ?><?php } ?>;
}

input[type="text"]{
	border: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#cccccc"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_form_background_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_form_background_colour; ?><?php } ?>;
	color: <?php if($ne_buddybusiness_form_text_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_form_text_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_body_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_body_font; ?><?php } ?>;
}

input[type="search"]{
	border: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#cccccc"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_form_background_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_form_background_colour; ?><?php } ?>;
	color: <?php if($ne_buddybusiness_form_text_colour == ""){ ?><?php echo "#FFFFFF"; } else { ?><?php echo $ne_buddybusiness_form_text_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_body_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_body_font; ?><?php } ?>;
}

input[type="password"]{
	border: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#cccccc"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_form_background_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_form_background_colour; ?><?php } ?>;
	color: <?php if($ne_buddybusiness_form_text_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_form_text_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_body_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_body_font; ?><?php } ?>;
}

input[type="text"].search-top{
	border: 1px solid <?php if($ne_buddybusiness_search_background_colour == ""){ ?><?php echo "#333333"; } else { ?><?php echo $ne_buddybusiness_search_background_colour; ?><?php } ?>;
	background: <?php if($ne_buddybusiness_search_background_colour == ""){ ?><?php echo "#444444"; } else { ?><?php echo $ne_buddybusiness_search_background_colour; ?><?php } ?> url('<?php bloginfo('template_directory'); ?>/_inc/images/search.png') no-repeat right;
	color: <?php if($ne_buddybusiness_search_text_colour == ""){ ?><?php echo "#DDDDDD"; } else { ?><?php echo $ne_buddybusiness_search_text_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_body_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_body_font; ?><?php } ?>;
}

input[type="button"], .button{
	border: 1px solid <?php if($ne_buddybusiness_button_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_button_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_button_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_button_colour; ?><?php } ?>;
	color: <?php if($ne_buddybusiness_button_text == ""){ ?><?php echo "#FFFFFF"; } else { ?><?php echo $ne_buddybusiness_button_text; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_body_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_body_font; ?><?php } ?>;
}

#invite-list li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

label{
	color: <?php if($ne_buddybusiness_label_text_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_label_text_colour; ?><?php } ?>;
}

#latest a, #latest a:link, #latest a:visited, #latest a:hover{
	color: <?php if($ne_buddybusiness_latest_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_latest_colour; ?><?php } ?>;
}

.latest-block{
	border-right: 1px solid <?php if($ne_buddybusiness_latest_border_colour == ""){ ?><?php echo "#88ccdd"; } else { ?><?php echo $ne_buddybusiness_latest_border_colour; ?><?php } ?>;
	color: <?php if($ne_buddybusiness_latest_text_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_latest_text_colour; ?><?php } ?>;
}

.latest-block-end, .latest-block-end .pagetitle, .latest-block .pagetitle{
	color: <?php if($ne_buddybusiness_latest_text_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_latest_text_colour; ?><?php } ?>;
}

#latest-wrapper{
	<?php if(($ne_buddybusiness_latest_background_image == "")&&($ne_buddybusiness_latest_background_colour != "")) { ?>
	background: <?php echo $ne_buddybusiness_latest_background_colour; ?>
	<?php } ?>

	<?php if(($ne_buddybusiness_latest_background_image != "")&&($ne_buddybusiness_latest_background_colour != "")) { ?>
	background: <?php echo $ne_buddybusiness_latest_background_colour; ?> url(<?php echo $ne_buddybusiness_latest_background_image; ?>) <?php echo $ne_buddybusiness_latest_image_repeat; ?>
	<?php } ?>

	<?php if(($ne_buddybusiness_latest_background_image != "")&&($ne_buddybusiness_latest_background_colour == "")) { ?>
	background: #488ca1 url(<?php echo $ne_buddybusiness_latest_background_image; ?>) <?php echo $ne_buddybusiness_latest_image_repeat; ?>
	<?php } ?>

	<?php if(($ne_buddybusiness_latest_background_image == "")&&($ne_buddybusiness_latest_background_colour == "")) { ?>
	background: #488ca1 url(<?php bloginfo('template_directory'); ?>/_inc/styles/blue_images/blue_feature_background.png) repeat-x
	<?php } ?>
	;

	border-bottom: 1px solid <?php if($ne_buddybusiness_login_background_colour == ""){ ?><?php echo "#eeeeee"; } else { ?><?php echo $ne_buddybusiness_login_background_colour; ?><?php } ?>;
}

#latest-wrapper .widget-error, #latest-wrapper .widget-error a, #latest-wrapper .widget-error a:link, #latest-wrapper .widget-error a:hover, #latest-wrapper .widget-error a:visited{
	color: <?php if($ne_buddybusiness_latest_text_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_latest_text_colour; ?><?php } ?>;
}

.left-menu{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

#login-wrapper{
	border-top: 1px solid <?php if($ne_buddybusiness_login_border_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_login_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_login_background_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_login_background_colour; ?><?php } ?>;
}

#members-list li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

.messages-options{
	border: 1px solid <?php if($ne_buddybusiness_box_border_colour == ""){ ?><?php echo "#EBEBEB"; } else { ?><?php echo $ne_buddybusiness_box_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_box_background_colour == ""){ ?><?php echo "#F8F8F8"; } else { ?><?php echo $ne_buddybusiness_box_background_colour; ?><?php } ?>;
}

.sf-menu a, .sf-menu a:visited  { 	color: <?php if($ne_buddybusiness_navigation_link_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_navigation_link_colour; ?><?php } ?>; 	cursor: pointer;}

.sf-menu li li { 	background-color: <?php if($ne_buddybusiness_navigation_background_hover_colour == ""){ ?><?php echo "#333333"; } else { ?><?php echo $ne_buddybusiness_navigation_background_hover_colour; ?><?php } ?>;	cursor: pointer;}

.sf-menu li li li {		background-color: <?php if($ne_buddybusiness_navigation_background_hover_colour == ""){ ?><?php echo "#333333"; } else { ?><?php echo $ne_buddybusiness_navigation_background_hover_colour; ?><?php } ?>; 	cursor: pointer;}

.sf-menu li:hover, .sf-menu li.current, .sf-menu li.current a:visited, .sf-menu li.current_page_item, .sf-menu li.current_page_item a:visited, .sf-menu li.sfHover,
.sf-menu a:focus, .sf-menu a:hover, .sf-menu a:active {
	color: <?php if($ne_buddybusiness_navigation_hover_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_navigation_hover_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_navigation_background_hover_colour == ""){ ?><?php echo "#333333"; } else { ?><?php echo $ne_buddybusiness_navigation_background_hover_colour; ?><?php } ?>;
	cursor: pointer;
}

.sf-menu .selected a{
	color: <?php if($ne_buddybusiness_navigation_hover_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_navigation_hover_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_navigation_background_hover_colour == ""){ ?><?php echo "#333333"; } else { ?><?php echo $ne_buddybusiness_navigation_background_hover_colour; ?><?php } ?>;
	cursor: pointer;
}

.navigation-wrapper .selected, .navigation-wrapper .current_page_item{
	color: <?php if($ne_buddybusiness_navigation_hover_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_navigation_hover_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_navigation_background_hover_colour == ""){ ?><?php echo "#333333"; } else { ?><?php echo $ne_buddybusiness_navigation_background_hover_colour; ?><?php } ?>;
}

.navigation-wrapper{
	<?php if(($ne_buddybusiness_navigation_background_image == "")&&($ne_buddybusiness_navigation_background_colour != "")) { ?>
	background: <?php echo $ne_buddybusiness_navigation_background_colour; ?>
	<?php } ?>

	<?php if(($ne_buddybusiness_navigation_background_image != "")&&($ne_buddybusiness_navigation_background_colour != "")) { ?>
	background: <?php echo $ne_buddybusiness_navigation_background_colour; ?> url(<?php echo $ne_buddybusiness_navigation_background_image; ?>) <?php echo $ne_buddybusiness_navigation_image_repeat; ?>
	<?php } ?>

	<?php if(($ne_buddybusiness_navigation_background_image != "")&&($ne_buddybusiness_navigation_background_colour == "")) { ?>
	background: #5A5A5A url(<?php echo $ne_buddybusiness_navigation_background_image; ?>) <?php echo $ne_buddybusiness_navigation_image_repeat; ?>
	<?php } ?>

	<?php if(($ne_buddybusiness_navigation_background_image == "")&&($ne_buddybusiness_navigation_background_colour == "")) { ?>
	background: #5A5A5A url(<?php bloginfo('template_directory'); ?>/_inc/images/navigation_background.png) repeat-x
	<?php } ?>
	;
}

ol.commentlist li.comment div.vcard img.avatar { border:1px solid <?php if($ne_buddybusiness_comment_border_colour == ""){ ?><?php echo "#EBEBEB"; } else { ?><?php echo $ne_buddybusiness_comment_border_colour; ?><?php } ?>;
}

ol.commentlist li.comment ul.children li.depth-2 { border-left:5px solid <?php if($ne_buddybusiness_comment_list_border_colour == ""){ ?><?php echo "#dddddd"; } else { ?><?php echo $ne_buddybusiness_comment_list_border_colour; ?><?php } ?>; margin:0 0 20px 20px; }

ol.commentlist li.comment ul.children li.depth-3 { border-left:5px solid <?php if($ne_buddybusiness_comment_list_border_colour == ""){ ?><?php echo "#dddddd"; } else { ?><?php echo $ne_buddybusiness_comment_list_border_colour; ?><?php } ?>; margin:0 0 20px 20px; }

ol.commentlist li.comment ul.children li.depth-4 { border-left:5px solid <?php if($ne_buddybusiness_comment_list_border_colour == ""){ ?><?php echo "#dddddd"; } else { ?><?php echo $ne_buddybusiness_comment_list_border_colour; ?><?php } ?>; margin:0 0 20px 20px; }

ol.commentlist li.even {
	border: 1px solid <?php if($ne_buddybusiness_comment_border_colour == ""){ ?><?php echo "#EBEBEB"; } else { ?><?php echo $ne_buddybusiness_comment_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_comment_background_colour == ""){ ?><?php echo "#F8F8F8"; } else { ?><?php echo $ne_buddybusiness_comment_background_colour; ?><?php } ?>;
}

ol.commentlist li.odd {
	border: 1px solid <?php if($ne_buddybusiness_comment_border_colour == ""){ ?><?php echo "#EBEBEB"; } else { ?><?php echo $ne_buddybusiness_comment_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_comment_background_colour == ""){ ?><?php echo "#F8F8F8"; } else { ?><?php echo $ne_buddybusiness_comment_background_colour; ?><?php } ?>;
}

ol.commentlist li.parent { border-left:5px solid <?php if($ne_buddybusiness_comment_list_border_colour == ""){ ?><?php echo "#dddddd"; } else { ?><?php echo $ne_buddybusiness_comment_list_border_colour; ?><?php } ?>; }


#options-nav li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_user_link_border_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_user_link_border_colour; ?><?php } ?>;
}

#options-nav li a{
	color: <?php if($ne_buddybusiness_user_link_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_user_link_colour; ?><?php } ?>;
	background: <?php if($ne_buddybusiness_user_background_link_colour == ""){ ?><?php echo "#DDDDDD"; } else { ?><?php echo $ne_buddybusiness_user_background_link_colour; ?><?php } ?>
	url('<?php bloginfo('template_directory'); ?>_/inc/images/foward.png') no-repeat;
	background-position: 8px;
}

#options-nav li a:link {
	color: <?php if($ne_buddybusiness_user_link_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_user_link_colour; ?><?php } ?>;
}

#options-nav li a:visited {
	color: <?php if($ne_buddybusiness_user_link_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_user_link_colour; ?><?php } ?>;
}

#options-nav li a:hover, #options-nav li.current a, #options-nav li.current a:link, #options-nav li.selected a, #options-nav li.selected a:link, #options-nav li.current_page_item a:link, #options-nav li.current_page_item a{
	color: <?php if($ne_buddybusiness_user_link_hover_colour == ""){ ?><?php echo "#eeeeee"; } else { ?><?php echo $ne_buddybusiness_user_link_hover_colour; ?><?php } ?>;
	background: <?php if($ne_buddybusiness_user_background_hover_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_user_background_hover_colour; ?><?php } ?>
	url('<?php bloginfo('template_directory'); ?>/_inc/images/foward.png') no-repeat;
	background-position: 8px;
}

blockquote{
	background: url('<?php bloginfo('template_directory'); ?>/_inc/styles/blue_images/blue_quote.png') no-repeat;
	color: <?php if($ne_buddybusiness_blockquote_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_blockquote_colour; ?><?php } ?>;
    padding-left: 30px;
}

.post h1{
	color: <?php if($ne_buddybusiness_h1_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_h1_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
}

.post h2{
	color: <?php if($ne_buddybusiness_h2_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_h2_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
}

.post h3{
	color: <?php if($ne_buddybusiness_h3_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_h3_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
}

.post h4, #sidebar h4, #sidebar-left h4, #sidebar-small h4{
	color: <?php if($ne_buddybusiness_h4_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_h4_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_headline_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_headline_font; ?><?php } ?>;
}

.post ol, .post ul, #sidebar ol, #sidebar-left ol, #sidebar ul, #sidebar-left ul, #sidebar-small ol, #sidebar-small ul{
	color: <?php if($ne_buddybusiness_list_colour == ""){ ?><?php echo "#6699aa"; } else { ?><?php echo $ne_buddybusiness_list_colour; ?><?php } ?>;
}

#random-members-list li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

#request-list li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

select{
	border: 1px solid <?php if($ne_buddybusiness_form_background_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_form_background_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_form_background_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_form_background_colour; ?><?php } ?>;
	color: <?php if($ne_buddybusiness_form_text_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_form_text_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_body_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_body_font; ?><?php } ?>;
}

#signup-box{
	border-left: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

.site-stats-number{
	color: <?php if($ne_buddybusiness_latest_text_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_latest_text_colour; ?><?php } ?>;
	background: url('<?php bloginfo('template_directory'); ?>/_inc/images/options.png') no-repeat;
	background-position: 0px;
}

.standard-form{
	border: 1px solid <?php if($ne_buddybusiness_block_border_colour == ""){ ?><?php echo "#EBEBEB"; } else { ?><?php echo $ne_buddybusiness_block_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_block_background_colour == ""){ ?><?php echo "#F8F8F8"; } else { ?><?php echo $ne_buddybusiness_block_background_colour; ?><?php } ?>;
}

textarea{
	border: 1px solid <?php if($ne_buddybusiness_form_background_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_form_background_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_form_background_colour == ""){ ?><?php echo "#ffffff"; } else { ?><?php echo $ne_buddybusiness_form_background_colour; ?><?php } ?>;
	color: <?php if($ne_buddybusiness_form_text_colour == ""){ ?><?php echo "#666666"; } else { ?><?php echo $ne_buddybusiness_form_text_colour; ?><?php } ?>;
	font-family: <?php if($ne_buddybusiness_body_font == ""){ ?><?php echo "Georgia, Sans-serif"; } else { ?><?php echo $ne_buddybusiness_body_font; ?><?php } ?>;
}

table.forum tr.sticky td.td-title {
	background-image: url( <?php bloginfo('template_directory'); ?>/_inc/images/options.png );
	background-position: 10px 30%;
	background-repeat: no-repeat;
}

table.forum tr.closed td.td-title {
	background-image: url( <?php bloginfo('template_directory'); ?>/_inc/images/closed_topic.png );
	background-position: 10px 30%;
	background-repeat: no-repeat;
}

#topic-post-list li{
	border-bottom: 1px solid <?php if($ne_buddybusiness_main_border_colour == ""){ ?><?php echo "#EEEEEE"; } else { ?><?php echo $ne_buddybusiness_main_border_colour; ?><?php } ?>;
}

.widget-error{
	border: 1px solid <?php if($ne_buddybusiness_information_border_colour == ""){ ?><?php echo "#aaaaaa"; } else { ?><?php echo $ne_buddybusiness_information_border_colour; ?><?php } ?>;
	background-color: <?php if($ne_buddybusiness_information_background_colour == ""){ ?><?php echo "#eeeeee"; } else { ?><?php echo $ne_buddybusiness_information_background_colour; ?><?php } ?>;
}
