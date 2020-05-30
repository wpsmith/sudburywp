
jQuery(document).ready( function($){
	var settings = [
		// WordPress Custom Background 
		{
			setting: 'background_color',
			callback: function(to){
				theme_change_style('body', 'background-color', to, '!important');
			}
		},
		{
			setting: 'background_image',
			callback: function(to){
				theme_queue_style('body', 'background-image', 'url('+to+')', '!important');
				theme_queue_style('body', 'background-repeat', wp.customize('background_repeat').get(), '!important');
				theme_queue_style('body', 'background-position', 'top '+wp.customize('background_position_x').get(), '!important');
				theme_queue_style('body', 'background-attachment', wp.customize('background_attachment').get(), '!important');
				theme_update_css();
			}
		},
		{
			setting: 'background_repeat',
			callback: function(to){
				theme_change_style('body', 'background-repeat', to, '!important');
			}
		},
		{
			setting: 'background_position_x',
			callback: function(to){
				theme_change_style('body', 'background-position', 'top '+to, '!important');
			}
		},
		{
			setting: 'background_attachment',
			callback: function(to){
				theme_change_style('body', 'background-attachment', to, '!important');
			}
		},
		// Text styling
		{
			setting: theme_prefix+'body_font',
			callback: function(to){
				theme_queue_font_family('a.button, a:link.button, a:visited.button, a:hover.button', to, '');
				theme_queue_font_family('body', to, '');
				theme_queue_font_family('input[type="submit"]', to, '');
				theme_queue_font_family('input[type="submit"].search-dark', to, '');
				theme_queue_font_family('input[type="text"]', to, '');
				theme_queue_font_family('input[type="search"]', to, '');
				theme_queue_font_family('input[type="password"]', to, '');
				theme_queue_font_family('input[type="text"].search-top', to, '');
				theme_queue_font_family('input[type="button"], .button', to, '');
				theme_queue_font_family('select', to, '');
				theme_queue_font_family('textarea', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'headline_font',
			callback: function(to){
				theme_queue_font_family('h1', to, '');
				theme_queue_font_family('h1 a, h1 a:link, h1 a:visited, h1 a:hover', to, '');
				theme_queue_font_family('h2', to, '');
				theme_queue_font_family('h3', to, '');
				theme_queue_font_family('h3#comments-number, h3#reply', to, '');
				theme_queue_font_family('#latest h3', to, '');
				theme_queue_font_family('.pagetitle, #settings-form h3, #profile-content h2', to, '');
				theme_queue_font_family('h4, h4 a, h4 a:link, h4 a:visited, h4 a:hover', to, '');
				theme_queue_font_family('.post h1', to, '');
				theme_queue_font_family('.post h2', to, '');
				theme_queue_font_family('.post h3', to, '');
				theme_queue_font_family('.post h4, #sidebar h4, #sidebar-left h4, #sidebar-small h4', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'h1_colour',
			callback: function(to){
				theme_queue_style('h1', 'color', to, '');
				theme_queue_style('h1 a, h1 a:link, h1 a:visited, h1 a:hover', 'color', to, '');
				theme_queue_style('.post h1', 'color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'h2_colour',
			callback: function(to){
				theme_queue_style('h2', 'color', to, '');
				theme_queue_style('.post h2', 'color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'h3_colour',
			callback: function(to){
				theme_queue_style('h3', 'color', to, '');
				theme_queue_style('h3#comments-number, h3#reply', 'color', to, '');
				theme_queue_style('.pagetitle, #settings-form h3, #profile-content h2', 'color', to, '');
				theme_queue_style('.post h3', 'color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'h4_colour',
			callback: function(to){
				theme_queue_style('h4, h4 a, h4 a:link, h4 a:visited, h4 a:hover', 'color', to, '');
				theme_queue_style('.post h4, #sidebar h4, #sidebar-left h4, #sidebar-small h4', 'color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'latest_header_colour',
			callback: function(to){
				theme_change_style('#latest h3', 'color', to, '');
			}
		},
		{
			setting: theme_prefix+'text_colour',
			callback: function(to){
				theme_change_style('body', 'color', to, '');
			}
		},
		{
			setting: theme_prefix+'latest_text_colour',
			callback: function(to){
				theme_queue_style('.latest-block', 'color', to, '');
				theme_queue_style('.latest-block-end, .latest-block-end .pagetitle, .latest-block .pagetitle', 'color', to, '');
				theme_queue_style('#latest-wrapper .widget-error, #latest-wrapper .widget-error a, #latest-wrapper .widget-error a:link, #latest-wrapper .widget-error a:hover, #latest-wrapper .widget-error a:visited', 'color', to, '');
				theme_queue_style('.site-stats-number', 'color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'blockquote_colour',
			callback: function(to){
				theme_change_style('blockquote', 'color', to, '');
			}
		},
		{
			setting: theme_prefix+'list_colour',
			callback: function(to){
				theme_change_style('.post ol, .post ul, #sidebar ol, #sidebar-left ol, #sidebar ul, #sidebar-left ul, #sidebar-small ol, #sidebar-small ul', 'color', to, '');
			}
		},
		// Link styling
		{
			setting: theme_prefix+'link_colour',
			callback: function(to){
				theme_change_style('a, a:link, a:visited, a:hover', 'color', to, '');
			}
		},
		{
			setting: theme_prefix+'link_hover_colour',
			callback: function(to){
				theme_change_style('a:hover', 'color', to, '');
			}
		},
		{
			setting: theme_prefix+'footer_link_colour',
			callback: function(to){
				theme_queue_style('#footer-links', 'color', to, '');
				theme_queue_style('#footer-links a, #footer-links a:link, #footer-links a:visited, #footer-links a:hover', 'color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'latest_colour',
			callback: function(to){
				theme_change_style('#latest a, #latest a:link, #latest a:visited, #latest a:hover', 'color', to, '');
			}
		},
		// Navigation styling
		{
			setting: theme_prefix+'navigation_link_colour',
			callback: function(to){
				theme_change_style('.sf-menu a, .sf-menu a:visited', 'color', to, '');
			}
		},
		{
			setting: theme_prefix+'navigation_hover_colour',
			callback: function(to){
				theme_queue_style('.sf-menu li:hover, .sf-menu li.current, .sf-menu li.current a:visited, .sf-menu li.current_page_item, .sf-menu li.current_page_item a:visited, .sf-menu li.sfHover, .sf-menu a:focus, .sf-menu a:hover, .sf-menu a:active', 'color', to, '');
				theme_queue_style('.sf-menu .selected a', 'color', to, '');
				theme_queue_style('.navigation-wrapper .selected, .navigation-wrapper .current_page_item', 'color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'navigation_background_hover_colour',
			callback: function(to){
				theme_queue_style('.sf-menu li li', 'background-color', to, '');
				theme_queue_style('.sf-menu li li li', 'background-color', to, '');
				theme_queue_style('.sf-menu li:hover, .sf-menu li.current, .sf-menu li.current a:visited, .sf-menu li.current_page_item, .sf-menu li.current_page_item a:visited, .sf-menu li.sfHover, .sf-menu a:focus, .sf-menu a:hover, .sf-menu a:active', 'background-color', to, '');
				theme_queue_style('.sf-menu .selected a', 'background-color', to, '');
				theme_queue_style('.navigation-wrapper .selected, .navigation-wrapper .current_page_item', 'background-color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'user_link_colour',
			callback: function(to){
				theme_queue_style('#bp-nav li a', 'color', to, '');
				theme_queue_style('#bp-nav li a:link', 'color', to, '');
				theme_queue_style('#bp-nav li a:visited', 'color', to, '');
				theme_queue_style('#options-nav li a', 'color', to, '');
				theme_queue_style('#options-nav li a:link', 'color', to, '');
				theme_queue_style('#options-nav li a:visited', 'color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'user_background_link_colour',
			callback: function(to){
				theme_queue_style('#bp-nav li a', 'background', to, '');
				theme_queue_style('#options-nav li a', 'background', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'user_link_hover_colour',
			callback: function(to){
				theme_queue_style('#bp-nav li a:hover, #bp-nav li.current a, #bp-nav li.current a:link, #bp-nav li.selected a, #bp-nav li.selected a:link, #bp-nav li.current_page_item a:link, #bp-nav li.current_page_item a', 'color', to, '');
				theme_queue_style('#options-nav li a:hover, #options-nav li.current a, #options-nav li.current a:link, #options-nav li.selected a, #options-nav li.selected a:link, #options-nav li.current_page_item a:link, #options-nav li.current_page_item a', 'color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'user_background_hover_colour',
			callback: function(to){
				theme_queue_style('#bp-nav li a:hover, #bp-nav li.current a, #bp-nav li.current a:link, #bp-nav li.selected a, #bp-nav li.selected a:link, #bp-nav li.current_page_item a:link, #bp-nav li.current_page_item a', 'background', to, '');
				theme_queue_style('#options-nav li a:hover, #options-nav li.current a, #options-nav li.current a:link, #options-nav li.selected a, #options-nav li.selected a:link, #options-nav li.current_page_item a:link, #options-nav li.current_page_item a', 'background', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'user_link_border_colour',
			callback: function(to){
				theme_queue_style('#bp-nav li', 'border-bottom', '1px solid ' + to, '');
				theme_queue_style('#options-nav li', 'border-bottom', '1px solid ' + to, '');
				theme_update_css();
			}
		},
		// Layout styling
		{
			setting: theme_prefix+'header_background_image',
			callback: function(to){
				theme_queue_style('#header-wrapper', 'background-image', 'url('+to+')', '');
				theme_queue_style('#header-wrapper', 'background-repeat', wp.customize('ne_buddybusiness_header_image_repeat').get(), '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'header_image_repeat',
			callback: function(to){
				theme_change_style('#header-wrapper', 'background-repeat', to, '');
			}
		},
		{
			setting: theme_prefix+'header_background_colour',
			callback: function(to){
				if ( wp.customize(theme_prefix+'header_background_image').get() )
					theme_change_style('#header-wrapper', 'background-color', to, '');
				else
					theme_change_style('#header-wrapper', 'background', to, '');
			}
		},
		{
			setting: theme_prefix+'navigation_background_image',
			callback: function(to){
				theme_queue_style('.navigation-wrapper', 'background-image', 'url('+to+')', '');
				theme_queue_style('.navigation-wrapper', 'background-repeat', wp.customize('ne_buddybusiness_navigation_image_repeat').get(), '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'navigation_image_repeat',
			callback: function(to){
				theme_change_style('.navigation-wrapper', 'background-repeat', to, '');
			}
		},
		{
			setting: theme_prefix+'navigation_background_colour',
			callback: function(to){
				if ( wp.customize(theme_prefix+'navigation_background_image').get() )
					theme_change_style('.navigation-wrapper', 'background-color', to, '');
				else
					theme_change_style('.navigation-wrapper', 'background', to, '');
			}
		},
		{
			setting: theme_prefix+'featured_colour',
			callback: function(to){
				theme_change_style('#feature-wrapper', 'background-color', to, '');
			}
		},
		{
			setting: theme_prefix+'content_colour',
			callback: function(to){
				theme_change_style('#content-wrapper', 'background-color', to, '');
			}
		},
		{
			setting: theme_prefix+'images_colour',
			callback: function(to){
				theme_queue_style('#feature-image', 'background-color', to, '');
				theme_queue_style('img.avatar', 'background-color', to, '');
				theme_queue_style('.image', 'background-color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'latest_background_image',
			callback: function(to){
				theme_queue_style('#latest-wrapper', 'background-image', 'url('+to+')', '');
				theme_queue_style('#latest-wrapper', 'background-repeat', wp.customize('ne_buddybusiness_latest_image_repeat').get(), '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'latest_image_repeat',
			callback: function(to){
					theme_change_style('#latest-wrapper', 'background-repeat', to, '');
			}
		},
		{
			setting: theme_prefix+'latest_background_colour',
			callback: function(to){
				if ( wp.customize(theme_prefix+'latest_background_image').get() )
					theme_change_style('#latest-wrapper', 'background-color', to, '');
				else
					theme_change_style('#latest-wrapper', 'background', to, '');
			}
		},
		{
			setting: theme_prefix+'latest_border_colour',
			callback: function(to){
				theme_change_style('.latest-block', 'border-right', '1px solid ' + to, '');
			}
		},
		{
			setting: theme_prefix+'footer_background_colour',
			callback: function(to){
				theme_queue_style('body', 'background', to, '');
				theme_queue_style('#footer-wrapper', 'background', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'footer_border_colour',
			callback: function(to){
				theme_change_style('#footer-wrapper', 'border-top', '3px solid ' + to, '');
			}
		},
		{
			setting: theme_prefix+'information_background_colour',
			callback: function(to){
				theme_queue_style('.alert', 'background-color', to, '');
				theme_queue_style('.info', 'background-color', to, '');
				theme_queue_style('.widget-error', 'background-color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'information_border_colour',
			callback: function(to){
				theme_queue_style('.alert', 'border', '1px solid ' + to, '');
				theme_queue_style('.info', 'border', '1px solid ' + to, '');
				theme_queue_style('.widget-error', 'border', '1px solid ' + to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'box_background_colour',
			callback: function(to){
				theme_queue_style('div#item-header h3 span.highlight span, #activity-rss', 'background-color', to, '');
				theme_queue_style('.date', 'background-color', to, '');
				theme_queue_style('#global-forum-topic-list', 'background-color', to, '');
				theme_queue_style('.messages-options', 'background-color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'box_border_colour',
			callback: function(to){
				theme_queue_style('div#item-header h3 span.highlight span, #activity-rss', 'border', '1px solid ' + to, '');
				theme_queue_style('.date', 'border', '1px solid ' + to, '');
				theme_queue_style('#global-forum-topic-list', 'border', '1px solid ' + to, '');
				theme_queue_style('.messages-options', 'border', '1px solid ' + to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'login_background_colour',
			callback: function(to){
				theme_queue_style('#latest-wrapper', 'border-bottom', '1px solid ' + to, '');
				theme_queue_style('#login-wrapper', 'background-color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'login_border_colour',
			callback: function(to){
				theme_change_style('#login-wrapper', 'border-top', '1px solid ' + to, '');
			}
		},
		{
			setting: theme_prefix+'main_border_colour',
			callback: function(to){
				theme_queue_style('#activity-list li, .item-list-tabs li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('#activity-filter-links li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('.content-header-nav li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('.entry', 'border-bottom', '1px solid'+to, '');
				theme_queue_style('#featured-blogs-list li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('#featured-members-list li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('#forum-topic-list li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('#friend-list li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('.generic-button', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('#global-forum-topic-filters li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('#global-forum-topic-list th', 'background-color', to, '');
				theme_queue_style('#global-forum-topic-list table', 'border', '1px solid '+to, '');
				theme_queue_style('.group-forum', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('#groups-list li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('hr', 'background-color', to, '');
				theme_queue_style('hr', 'color', to, '');
				theme_queue_style('h3#comments-number, h3#reply', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('.pagetitle, #settings-form h3, #profile-content h2', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('.horiz-gallery li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('img', 'border', '1px solid '+to, '');
				theme_queue_style('img.avatar', 'border', '1px solid '+to, '');
				theme_queue_style('.image', 'border', '1px solid '+to, '');
				theme_queue_style('input[type="text"]', 'border', '1px solid '+to, '');
				theme_queue_style('input[type="search"]', 'border', '1px solid '+to, '');
				theme_queue_style('input[type="password"]', 'border', '1px solid '+to, '');
				theme_queue_style('#invite-list li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('.left-menu', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('#members-list li', 'border-bottom', '1px solid '+to, '');
				theme_queue_style('#random-members-list li', 'border-bottom', '1px solid '+to, '');;
				theme_queue_style('#request-list li', 'border-bottom', '1px solid '+to, '');;
				theme_queue_style('#signup-box', 'border-left', '1px solid '+to, '');;
				theme_queue_style('#topic-post-list li', 'border-bottom', '1px solid '+to, '');
				theme_update_css();
			}
		},
		// Form styling
		{
			setting: theme_prefix+'button_colour',
			callback: function(to){
				theme_queue_style('input[type="submit"]', 'background-color', to, '');
				theme_queue_style('input[type="submit"]', 'border', '1px solid ' + to, '');
				theme_queue_style('input[type="button"], .button', 'background-color', to, '');
				theme_queue_style('input[type="button"], .button', 'border', '1px solid ' + to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'button_text',
			callback: function(to){
				theme_queue_style('a.button, a:link.button, a:visited.button, a:hover.button', 'color', to, '');
				theme_queue_style('input[type="submit"]', 'color', to, '');
				theme_queue_style('input[type="button"], .button', 'color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'dark_button_colour',
			callback: function(to){
				theme_queue_style('input[type="submit"].search-dark', 'border', '1px solid ' + to, '');
				theme_queue_style('input[type="submit"].search-dark', 'background-color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'form_background_colour',
			callback: function(to){
				theme_queue_style('input[type="text"]', 'background-color', to, '');
				theme_queue_style('input[type="search"]', 'background-color', to, '');
				theme_queue_style('input[type="password"]', 'background-color', to, '');
				theme_queue_style('select', 'background-color', to, '');
				theme_queue_style('select', 'border', '1px solid ' + to, '');
				theme_queue_style('textarea', 'background-color', to, '');
				theme_queue_style('textarea', 'border', '1px solid ' + to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'form_text_colour',
			callback: function(to){
				theme_queue_style('input[type="text"]', 'color', to, '');
				theme_queue_style('input[type="search"]', 'color', to, '');
				theme_queue_style('input[type="password"]', 'color', to, '');
				theme_queue_style('select', 'color', to, '');
				theme_queue_style('textarea', 'color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'label_text_colour',
			callback: function(to){
				theme_change_style('label', 'color', to, '');
			}
		},
		{
			setting: theme_prefix+'search_background_colour',
			callback: function(to){
				theme_queue_style('input[type="text"].search-top', 'background', to, '');
				theme_queue_style('input[type="text"].search-top', 'border', '1px solid ' + to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'search_text_colour',
			callback: function(to){
				theme_change_style('input[type="text"].search-top', 'color', to, '');
			}
		},
		{
			setting: theme_prefix+'block_background_colour',
			callback: function(to){
				theme_change_style('.standard-form', 'background-color', to, '');
			}
		},
		{
			setting: theme_prefix+'block_border_colour',
			callback: function(to){
				theme_change_style('.standard-form', 'border', '1px solid ' + to, '');
			}
		},
		{
			setting: theme_prefix+'comment_background_colour',
			callback: function(to){
				theme_queue_style('ol.commentlist li.even', 'background-color', to, '');
				theme_queue_style('ol.commentlist li.odd', 'background-color', to, '');
				theme_update_css();
			}
		},
		{
			setting: theme_prefix+'comment_border_colour',
			callback: function(to){
				theme_change_style('ol.commentlist li.even, ol.commentlist li.odd, ol.commentlist li.comment div.vcard img.avatar', 'border', '1px solid ' + to, '');
			}
		},
		{
			setting: theme_prefix+'comment_list_border_colour',
			callback: function(to){
				theme_queue_style('ol.commentlist li.comment div.vcard img.avatar', 'border', '1px solid ' + to, '');
				theme_queue_style('ol.commentlist li.even', 'border', '1px solid ' + to, '');
				theme_queue_style('ol.commentlist li.odd', 'border', '1px solid ' + to, '');
				theme_update_css();
			}
		},
		/*{
			setting: theme_prefix+'',
			callback: function(to){
			}
		},*/
		// Site title & tagline
		{
			setting: 'blogname',
			callback: function(to){
				$('#logo h1:not(.square-header) a').text(to);
			}
		},
		{
			setting: 'blogdescription',
			callback: function(to){
				$('#logo .description').text(to);
			}
		}
	];
	theme_bind_customize( settings );
	
} );