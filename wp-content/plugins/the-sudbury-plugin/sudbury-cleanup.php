<?php
/**
 * Cleans up the WordPress User Interface for our install.
 *
 * This file contains a lot of misc functionality
 *
 * @author       Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package      Sudbury
 * @subpackage   Sudbury_Plugin
 */


/**
 * Forces Metaboxes to be hidden
 *
 * @param array     $hidden The already hidden metabox slugs
 * @param WP_Screen $screen The current screen that the metaboxes will be shown on
 *
 * @return array An array of the new hidden metabox slugs
 */
function sudbury_hide_meta_boxes( $hidden, $screen ) {
	global $post;
	$default_show = array( 'postexcerpt' );
	foreach ( $default_show as $show ) {
		if ( ( $key = array_search( $show, $hidden ) ) !== false ) {
			unset( $hidden[ $key ] );
		}
	}

	if ( 'post' == $screen->base ) {
		$hidden = array_merge( $hidden, array(
			'discussion-settings',
			'sudbury-post-events-debug',
			'formatdiv',
			'slugdiv',
			'tagsdiv-post_tag',
			'trackbacksdiv',
			'commentstatusdiv',
			'commentsdiv',
			'authordiv',
			'revisionsdiv',
			'wdsi_message_override',
			'postcustom',
			'soliloquy_upgrade',
			'copier-meta-box',
			'wdfb_opengraph_editor',
			'wdfb_facebook_publishing',
			'em-event-meta',
			'sharing_meta',
		) );
		
		if ( $post ) {
			if ( 'location' == get_post_type( $post->ID ) ) {
				$hidden[] = 'postexcerpt';
			}
		}
	} elseif ( 'dashboard' == $screen->base ) {
		$hidden = array_merge( $hidden, array(
			'dashboard_recent_comments',
			'dashboard_incoming_links',
			'dashboard_recent_drafts',
			'dashboard_primary',
			'dashboard_secondary',
			'bruteprotect_dashboard_widget',
		) );
	}

	if ( $post && ! in_array( $post->post_type, array( 'post', 'page', 'faq' ) ) ) {
		$hidden[] = 'expirationdatediv';
	}


	// A little bit of a hack but we are using this filter as an opportunity to re-register the custom Links metabox to High Priority
	if ( 'nav-menus' == $screen->base ) {
		add_meta_box( 'add-custom-links', __( 'Links' ), 'wp_nav_menu_item_link_meta_box', 'nav-menus', 'side', 'high' );
	}

	return $hidden;
}

// Potentially use default_hidden_meta_boxes filter
add_filter( 'hidden_meta_boxes', 'sudbury_hide_meta_boxes', 10, 2 );

/**
 * Removes the nagging out-of-date browser notifications on the wordpress dashboard
 */
// WE MAKE WORDPRESS ANGRY!!!!!
function remove_browse_happy() {
	global $wp_meta_boxes;
	unset( $wp_meta_boxes['dashboard']['normal']['high']['dashboard_browser_nag'] );
	unset( $wp_meta_boxes['dashboard-network']['normal']['high']['dashboard_browser_nag'] );
}

add_action( 'wp_dashboard_setup', 'remove_browse_happy' );
add_action( 'wp_network_dashboard_setup', 'remove_browse_happy' );


/**
 * Ok Story Time... This function is a filter that normally is for the __('text'), _e('text'), ect functions.    I am
 * being a bit unconventional and using it to change some of the Wordpress Admin UI here in the cleanup script.
 * If you want to change something core to wordpress's labeling and there is obviously no other way to do it...
 * i.e. post type labels, taxonomy labels, ect.  then try to find where wordpress spits out the text that you want to
 * change.
 *
 * For example: Wordpress's name for the 'Teaser' is 'Excerpt' and it calls _e('Excerpt') in one of it's
 * wp-admin scripts... well that _e() function runs it's return value by this function here before printing the
 * 'Excerpt' text to the web client.  So all we have to do is check if the $text is 'Excerpt' and then return
 * 'Teaser' instead... I would recommend checking the $domain if possible and that the $translated is also == to $text
 * as well
 *
 * @param string $translated what the __(...), _e(...), ect. function was able to translate the $text to
 * @param string $text       The original text that was asked to be translated
 * @param string $domain     The text domain (optional) - Like 'sudbury' for translations preformed by the sudbury plugin
 *
 * @return string The new translation
 */
function sudbury_text_translations( $translated, $text, $domain ) {
	global $post;

	if ( 'Choose from the most used tags' == $text && is_array( $tags = get_site_option( 'sudbury_recommended_tags' ) ) ) {
		return 'Choose from the most used tags<br><br><b>Recommended Tags</b><ul><li class="add_tag_suggestion"><span class="add_category_icon"></span>'
		       . implode( '</li><li class="add_tag_suggestion"><span class="add_category_icon"></span>', $tags )
		       . '</li></ul><div class="clear"></div>';
//		return 'Choose from the most used tags</a><br><br><b>Recommended Tags</b><ul><li class="add_tag_suggestion"><span class="add_category_icon"></span>'
//		       . implode( '</li><li class="add_tag_suggestion"><span class="add_category_icon"></span>', $tags )
//		       . '</li></ul><div class="clear"></div><a href="#">';
	} elseif ( 'Excerpt' == $text ) {
		if ( isset( $post ) && is_object( $post ) ) {
			if ( 'meeting' == $post->post_type ) {
				return 'Notes';
			} elseif ( 'post' == $post->post_type ) {
				return 'Teaser';
			} else {
				return 'Teaser (Excerpt)';
			}
		}
	} elseif ( 'Publish <b>immediately</b>' == $text && 'Publish <b>immediately</b>' == $translated ) {
		return 'Schedule For: <b>now</b>';
	} elseif ( 'Excerpts are optional hand-crafted summaries of your content that can be used in your theme. <a href="http://codex.wordpress.org/Excerpt" target="_blank">Learn more about manual excerpts.</a>' == $text ) {
		if ( isset( $post ) && is_object( $post ) ) {
			$post_type_label        = strtolower( get_post_type_object( $post->post_type )->labels->singular_name );
			$post_type_label_plural = strtolower( get_post_type_object( $post->post_type )->labels->name );
		} else {
			$post_type_label = $post_type_label_plural = 'content';

		}

		return 'The teaser is a shortened version of your ' . $post_type_label . ' to show in lists of ' . $post_type_label_plural . '. It will automatically be generated if you don\'t specify a teaser';
	} elseif ( 'Open link in a new window/tab' == $text ) {
		return $translated . '.</label> <br>' . __( 'Email Addresses follow the URL format' ) . ': <code id="mailto-example" data-source="#mailto-example" data-target="#url-field" class="sudbury_set_remote_value_html">mailto:example@sudbury.ma.us</code><label>';
	}

	return $translated;
}

add_filter( 'gettext', 'sudbury_text_translations', 10, 3 );


/**
 * Removes unnecessary admin menus for users
 */
function sudbury_remove_admin_menus( $context ) {
	if ( ! is_committee() ) {
		remove_menu_page( 'edit.php?post_type=meeting' );
	}

	if ( get_current_blog_id() != 1 ) {
		remove_submenu_page( 'edit.php?post_type=event', 'locations' );
	}

	if ( ! is_super_admin() ) {
		remove_menu_page( 'sudbury-move-post' );
		remove_menu_page( 'tools.php' );
	}

	if ( ! get_comments( array( 'number' => 1 ) ) ) {
		remove_menu_page( 'edit-comments.php' );
	}

	remove_submenu_page( 'edit.php?post_type=event', 'events-manager-bookings' );
	remove_submenu_page( 'users.php', 'wpmu_ldap_adduser.functions.php' );

	/* moe changed this. original: if ( ! current_user_can( 'see_slider_menus' ) ) { */
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		remove_menu_page( 'edit.php?post_type=soliloquy' );
	}

	if ( get_current_blog_id() != 1 ) {
		remove_submenu_page( 'upload.php', 'edit-tags.php?taxonomy=document_categories&post_type=attachment' );
		remove_submenu_page( 'edit.php?post_type=faq', 'edit-tags.php?taxonomy=faq_categories&post_type=faq' );
		remove_submenu_page( 'edit.php?post_type=link', 'edit-tags.php?taxonomy=link_categories&post_type=link' );
	}
}

add_filter( 'admin_menu', 'sudbury_remove_admin_menus', 999 );

/**
 * Handles promoted, demoted, and restricted categories for the categories list box for post editing
 *
 * @param array $args    The args for the Category Checklist Walker
 * @param int   $post_id The Post ID for the Category Checklist
 *
 * @return array New args for the categories box
 */
function sudbury_cleanup_categories_box_args( $args, $post_id ) {
	if ( ! class_exists( 'Sudbury_Category_Checklist_Walker' ) && class_exists( 'Walker_Category_Checklist' ) ) {
		class Sudbury_Category_Checklist_Walker extends Walker_Category_Checklist {
			function walk( $elements, $max_depth ) {
				global $current_user;
				$args = array_slice( func_get_args(), 2 );

				$depreciated = get_site_option( 'sudbury_demoted_categories', array() );
				$found       = array( 'promoted' => array(), 'depreciated' => array() );
				$promoted    = get_site_option( 'sudbury_promoted_categories', array() );
				$restricted  = get_site_option( 'sudbury_restricted_categories', array() );
				if ( ! isset( $restricted[ $current_user->role ] ) ) {
					$restricted[ $current_user->role ] = array();
				}

				for ( $i = 0; $i < count( $elements ); $i ++ ) {
					if ( isset( $elements[ $i ] ) && $elements[ $i ] != null ) {
						$element = $elements[ $i ];

						if ( in_array( $element->slug, $depreciated ) || in_array( $element->name, $depreciated ) ) {
							$found['depreciated'][] = $element;
							unset( $elements[ $i ] );
							$elements = array_values( $elements );
							$i --;
							// Also Unset all the Children
							$this->depreciate_children( $elements, $element, $found );
						}
						if ( in_array( $element->slug, $promoted ) || in_array( $element->name, $promoted ) ) {
							$found['promoted'][] = $element;
							unset( $elements[ $i ] );
							$elements = array_values( $elements );
							$i --;
							// Also Unset all the Children
							$this->depreciate_children( $elements, $element, $found );
						}
						if ( ( in_array( $element->slug, $restricted[ $current_user->role ] ) || in_array( $element->name, $restricted[ $current_user->role ] ) ) && ! is_super_admin() ) {
							unset( $elements[ $i ] );
							$elements = array_values( $elements );
							$i --;
							// Also Unset all the Children
							$this->delete_children( $elements, $element );
						}
					}
				}
				$elements = array_merge( $found['promoted'], $elements, $found['depreciated'] );


				extract( $args );

				return parent::walk( $elements, $max_depth, $args[0] );
			} // end walk()

			function depreciate_children( &$elements, $parent, &$found ) {
				for ( $i = 0; $i < count( $elements ); $i ++ ) {
					if ( $elements[ $i ]->parent == $parent->term_id ) {
						$element                = clone $elements[ $i ];
						$found['depreciated'][] = $element;
						unset( $elements[ $i ] );
						$elements = array_values( $elements );
						$i --;

						$this->depreciate_children( $elements, $element, $found );
					}
				}
			}

			function promote_children( &$elements, $parent, &$found ) {
				for ( $i = 0; $i < count( $elements ); $i ++ ) {
					if ( $elements[ $i ]->parent == $parent->term_id ) {
						$element             = clone $elements[ $i ];
						$found['promoted'][] = $element;
						unset( $elements[ $i ] );
						$elements = array_values( $elements );
						$i --;

						$this->promote_children( $elements, $element, $found );
					}
				}
			}

			function delete_children( &$elements, $parent ) {
				for ( $i = 0; $i < count( $elements ); $i ++ ) {
					if ( $elements[ $i ]->parent == $parent->term_id ) {
						$element = clone $elements[ $i ];
						unset( $elements[ $i ] );
						$elements = array_values( $elements );
						$i --;

						$this->delete_children( $elements, $element );
					}
				}
			}
		} // end Sudbury_Category_Checklist_Walker
	}

	if ( 'post' == get_post_type( $post_id ) && class_exists( 'Walker_Category_Checklist' ) ) {
		$args['walker'] = new Sudbury_Category_Checklist_Walker;
	}

	return $args;
}

add_filter( 'wp_terms_checklist_args', 'sudbury_cleanup_categories_box_args', 10, 2 );

/**
 * Modifies a Term Query to manipulate the list of categories in admin category selector
 *
 * @param array $args       The Existing Args
 * @param array $taxonomies The Taxonomies to apply the query on
 *
 * @return array The New Args
 */
function sudbury_popular_categories( $args, $taxonomies ) {
	if ( isset( $GLOBALS['sudbury_popular_categories_lock'] ) ) {
		return $args;
	}
	$GLOBALS['sudbury_popular_categories_lock'] = 1;
	if ( ! function_exists( 'get_current_screen' ) ) {
		return $args;
	}

	$args['hide_empty'] = false;

	if ( array( 'category' ) == $taxonomies && get_current_screen() && 'post' == get_current_screen()->base && 'count' == $args['orderby'] ) {
		$args['include'] = array_merge( $args['include'], get_site_option( 'sudbury_popular_categories', array() ) );
		// This is a temporary fix to get rid of unwanted categories...
		// TODO: Make this not so janky
		$args['exclude'] = array_merge( $args['exclude'], range( 1, 1000, 1 ) );
		$args['number']  = '';

	}

//	$spotlight_news_id = get_site_option( 'sudbury_spotlight_news_term', 327 );
//	if ( empty( $args['child_of'] ) ) {
	// This hunk of code is notorious for infinite loops
//		if ( array( 'category' ) == $taxonomies && ! current_user_can( 'create_spotlight_news' ) && $spotlight_news_id != $args['child_of'] ) {
	// $args['exclude_tree'][] = $spotlight_news_id;
//		}

//		$network_alerts_id = get_site_option( 'sudbury_network_alerts_term', 7 );
//		if ( array( 'category' ) == $taxonomies && ! ( current_user_can( 'create_network_alerts' ) || current_user_can_for_blog( 1, 'create_network_alerts' ) ) && $network_alerts_id != $args['child_of'] ) {
	// $args['exclude_tree'][] = $network_alerts_id;
//		}
//	}

	unset( $GLOBALS['sudbury_popular_categories_lock'] );

	return $args;
}

add_filter( 'get_terms_args', 'sudbury_popular_categories', 10, 2 );
/**
 * Removes the Query panel in the Debug Bar because it slows me down
 *
 * @param array $panels The current list of panels in the Debug Bar
 *
 * @return mixed The New list of panels for the Admin Bar
 */
function sudbury_remove_query_panel( $panels ) {
	unset( $panels[1] );

	return $panels;
}

/**
 * Removing all Debug Bar Panels if not in Debug Mode
 */
if ( ! WP_DEBUG ) {
	add_filter( 'debug_bar_panels', 'sudbury_remove_query_panel' );
}

/**
 * Remove Events Manager Hello To User Nag
 */
add_filter( 'option_dbem_hello_to_user', '__return_false' );

/**
 * Removes useless widgets
 */
function sudbury_unregister_widgets() {
	if ( false === get_option( 'sudbury_unregister_widgets', true ) && ! is_super_admin() ) {
		return;
	}

	unregister_widget( 'WP_Widget_Pages' );

	unregister_widget( 'WP_Widget_Calendar' );

	//unregister_widget('WP_Widget_Archives');

	unregister_widget( 'WP_Widget_Meta' );

	//unregister_widget('WP_Widget_Search');

	//unregister_widget('WP_Widget_Text');

	unregister_widget( 'WP_Widget_Categories' );

	//unregister_widget('WP_Widget_Recent_Posts');

	unregister_widget( 'WP_Widget_Recent_Comments' );

	unregister_widget( 'WP_Widget_RSS' );

	unregister_widget( 'WP_Widget_Tag_Cloud' );

	//register_widget('WP_Nav_Menu_Widget');

}

add_action( 'widgets_init', 'sudbury_unregister_widgets' );

/**
 * Fixes WP Fullcalendar post content that autoloads over AJAX when you hover over an event
 *
 * @param string $content Existing Content
 *
 * @return string New Content
 */
function sudbury_wpfc_content( $content ) {
	$event = sudbury_get_event( $_REQUEST['event_id'] );
	if ( $event['event_all_day'] ) {
		$content = "<pre>{$event['event_name']}\n All Day Event\n Click for full details</pre>";
	} else {
		$start   = date( 'h:i A', strtotime( $event['event_start_time'] ) );
		$end     = date( 'h:i A', strtotime( $event['event_end_time'] ) );
		$content = "<pre>{$event['event_name']}\n {$start} - {$end}\n Click for full details</pre>";
	}

	return $content;
}

add_filter( 'wpfc_qtip_content', 'sudbury_wpfc_content' );
