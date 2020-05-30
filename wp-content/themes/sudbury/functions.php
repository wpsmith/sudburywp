<?php
// moe defining Senior Center blog id - used in this file for WP Events Manager
// and WP Full Calendar filters/actions - to separate senior center events
// from the rest of the network's events.
define( 'SUDB_SENIORCENTER_SITE_ID', '381' );

if ( ! isset( $content_width ) ) {
	$content_width = 584;
}

include 'functions/framework.php';
require_once 'functions/sudbury_ajax_contact_form.php';
require_once 'functions/sudbury_nav_walker.php';
require_once 'functions/wp_bootstrap_navwalker.php';
require_once 'widgets/sudbury-featured-action.php';
require_once 'widgets/sudbury-internal-menu.php';

foreach ( glob( __DIR__ . '/shortcodes/*.php' ) as $filename ) {
	require_once $filename;
}

/**
 * Registers any widgets that should be globally accessible regardless of theme
 */
function sudbury_register_theme_widgets() {
	register_widget( 'Sudbury_Featured_Actions' );
	register_widget( 'Sudbury_Internal_Menu' );
}

add_action( 'widgets_init', 'sudbury_register_theme_widgets', 1 );

add_action( 'after_setup_theme', 'sudbury_setup' );

add_filter( 'use_default_gallery_style', '__return_false' );


if ( ! function_exists( 'is_login_page' ) ) {
	/**
	 * Determines if the current page is a login or sign up page
	 * @return bool whether the current page is the login or sign-up page
	 */
	function is_login_page() {
		if ( isset( $GLOBALS['pagenow'] ) ) {
			return in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) );
		}
	}
}

if ( ! function_exists( 'sudbury_setup' ) ):

	function sudbury_setup() {
		if ( ! is_admin() && ! is_login_page() && ! defined( 'SUDBURY_VERSION' ) ) {
			$GLOBALS['maintenance_message'] = 'The Sudbury Plugin is not installed/activated. To fix this Login at /wp-admin/network/plugins.php and enable the sudbury plugin network wide';

			include( ABSPATH . 'wp-content/maintenance.php' );
			exit();
		}

		add_editor_style();

		//require get_template_directory() . '/inc/theme-options.php';

		// FEEDS
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );

		// NAV MENUS
		// Hide the primary nav menu from subsite admin screens
		//if ( ! is_admin() || is_main_site() ) {
		register_nav_menu( 'primary', __( 'Primary Menu', 'sudbury' ) );
		//}

		register_nav_menu( 'secondary', __( 'Secondary Menu', 'sudbury' ) );

//		register_nav_menu( 'internal', __( 'Internal Menu', 'sudbury' ) );

		register_nav_menu( 'links', __( 'Links Menu', 'sudbury' ) );

		register_nav_menu( 'tabs', __( 'Tabs Menu', 'sudbury' ) );

		//if ( is_main_site() ) {
		register_nav_menu( 'all-links', __( 'Content Draw In', 'sudbury' ) );
		//}
		// Add support for a variety of post formats
//		add_theme_support( 'post-formats', array( 'gallery', 'status', 'quote', 'image', 'video', 'audio' ) );

		//$theme_options = sudbury_get_theme_options();


		// This theme uses Featured Images (also known as post thumbnails) for per-post/per-page Custom Header images
		// TODO: Add an option in the admin UI to turn this on and off for subsites.
		add_theme_support( 'post-thumbnails' );

		// Add support for custom headers.
		$custom_header_support = array(
			// The default header text color.
			'default-text-color'     => '000',
			// The height and width of our custom header.
			'width'                  => apply_filters( 'header_image_width', 2560 ),
			'height'                 => apply_filters( 'header_image_height', 860 ),
			// Support flexible height.
			'flex-height'            => true,
			// Random image rotation by default.
			'random-default'         => false,
			// Callback for styling the header.
			'wp-head-callback'       => 'sudbury_header_style',
			// Callback for styling the header preview in the admin.
			'admin-head-callback'    => 'sudbury_admin_header_style',
			// Callback used to display the header preview in the admin.
			'admin-preview-callback' => 'sudbury_admin_header_image',
			// default header image
			'default-image'          => false
		);

		add_theme_support( 'custom-header', $custom_header_support );

		$custom_background_support = array(
			'default-color'    => 'fff',
			'default-repeat'   => 'no-repeat',
			// Callback for styling the header.
			'wp-head-callback' => 'sudbury_background_style',
		);
		add_theme_support( 'custom-background', $custom_background_support );
		function sudbury_background_style() {
			?>
            <style type="text/css" id="custom-background-css">
                #header {
                    background-image: url('<?php echo get_background_image(); ?> ');
                    background-color: <?php echo get_background_color(); ?>;
                    background-repeat: <?php echo get_theme_mod( 'background_repeat', 'repeat' ); ?>;
                    background-position: <?php echo get_theme_mod( 'background_position_x', 'left' ); ?>;
                    background-attachment: <?php echo get_theme_mod( 'background_attachment', 'scroll' ); ?>;
                }
            </style>
			<?php
		}

		// Used for large feature (header) images.
		add_image_size( 'large-feature', $custom_header_support['width'], $custom_header_support['height'], true );
		// Used for featured posts if a large-feature doesn't exist.
		add_image_size( 'small-feature', 500, 300 );
	}
endif; // sudbury_setup

function sudbury_custom_site_icon_size( $sizes ) {
	$sizes[] = 90;

	return $sizes;
}

add_filter( 'site_icon_image_sizes', 'sudbury_custom_site_icon_size' );

// Plug functions from sudbury-framework if they are missing
if ( ! function_exists( 'sudbury_is_site_archived' ) ) {
	function sudbury_is_site_archived() {
		_deprecated_function( __FUNCTION__, '0.0', 'sudbury_is_site_archived() from the-sudbury-plugin' );

		return false;
	}
}

/**
 * Modify the queries performed to render post listings on blog 1 (main site) to include content from the entire
 * network.
 *
 * @see https://developer.wordpress.org/reference/hooks/the_posts/
 *
 * @description
 * This filter hooks 'the_posts' which is called after the WP_Query executes it's query and has results.  In the case
 * of the main site, the "main query"
 *
 *
 *
 * @param $posts array The existing posts that were found on blog 1
 * @param $query WP_Query The query to consider modifying
 *
 * @return array
 */
function sudbury_network_home_query( $posts, $query ) {
	if ( is_main_query() and ( is_front_page() || is_feed() ) ) {
		if ( is_feed() ) {
			sudbury_log( $query, array( 'html' => false ) );
		}
		if ( get_current_blog_id() == 1 ) {
			$posts = network_query_posts( array(
				// No public archive on home or in feeds
				'post_status'    => array( 'publish' ),
				// front page: posts, feeds: post, meeting, attachment
				'post_type'      => $query->query_vars['post_type'],
				// Network posts to show on network home require this category
				'category_name'  => get_site_option( 'sudbury_network_home_category_name', 'Front Page News' ),
				// Copy the pagination query vars :fire:
				'paged'          => $query->query_vars['paged'],
				'posts_per_page' => $query->query_vars['posts_per_page'],
				'meta_key'       => '_post-expiration-enabled',
				'meta_value'     => 1,
			) );

			// Note: Because this list is filtered, the max_num_pages and found posts will be invalid...
			$posts_filtered = array_filter( $posts, function ( $post ) {
				return ! sudbury_is_guest_post( $post->ID, $post->BLOG_ID );
			} );

			// Clear out empty indexes
			$posts_filtered = array_values( $posts_filtered );

			$num_filtered = count( $posts ) - count( $posts_filtered );

			$query->max_num_pages = $GLOBALS['network_query']->max_num_pages;
			$query->found_posts   = $GLOBALS['network_query']->found_posts - $num_filtered;

			$posts = $posts_filtered;

		}
		remove_action( 'the_posts', 'sudbury_network_home_query' );
	}

	return $posts;
}

add_action( 'the_posts', 'sudbury_network_home_query', 10, 2 );

function sudbury_town_meeting_home_query( $posts, $query ) {
	if ( is_main_query() and ( is_front_page() || is_feed() ) ) {
		if ( is_feed() ) {
			sudbury_log( $query, array( 'html' => false ) );
		}
		if ( get_current_blog_id() == 358 ) {
			$posts = network_query_posts( array(
				// No public archive on home or in feeds
				'post_status'    => array( 'publish' ),
				// front page: posts, feeds: post, meeting, attachment
				'post_type'      => $query->query_vars['post_type'],
				// Network posts to show on network home require this category
				'category_name'  => get_site_option( 'sudbury_network_town_meeting_category_name', 'Town Meeting News' ),
				// Copy the pagination query vars :fire:
				'paged'          => $query->query_vars['paged'],
				'posts_per_page' => $query->query_vars['posts_per_page'],
				'meta_key'       => '_post-expiration-enabled',
				'meta_value'     => 1,
			) );

			// Note: Because this list is filtered, the max_num_pages and found posts will be invalid...
			$posts_filtered = array_filter( $posts, function ( $post ) {
				return ! sudbury_is_guest_post( $post->ID, $post->BLOG_ID );
			} );

			// Clear out empty indexes
			$posts_filtered = array_values( $posts_filtered );

			$num_filtered = count( $posts ) - count( $posts_filtered );

			$query->max_num_pages = $GLOBALS['network_query']->max_num_pages;
			$query->found_posts   = $GLOBALS['network_query']->found_posts - $num_filtered;

			$posts = $posts_filtered;

		}
		remove_action( 'the_posts', 'sudbury_town_meeting_home_query' );
	}

	return $posts;
}

add_action( 'the_posts', 'sudbury_town_meeting_home_query', 10, 2 );

function sudbury_ms_permalink( $url, $post ) {
	if ( isset( $post->BLOG_ID ) && $post->BLOG_ID != get_current_blog_id() ) {
		return get_blog_permalink( $post->BLOG_ID, $post->ID );
	}

	return $url;
}

add_filter( 'post_type_link', 'sudbury_ms_permalink', 10, 2 );
add_filter( 'post_link', 'sudbury_ms_permalink', 10, 2 );


if ( ! function_exists( 'sudbury_header_style' ) ) :
	function sudbury_header_style() {
		//$text_color = get_header_textcolor();
	}
endif; // sudbury_header_style


function sudbury_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Main Sidebar', 'sudbury' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => "</aside>",
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Showcase Area', 'sudbury' ),
		'id'            => 'showcase_widget_area',
		'description'   => __( 'The sidebar for the optional Showcase Area above the News Feed on the homepage', 'sudbury' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => "</aside>",
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Tasks Area', 'sudbury' ),
		'id'            => 'tasks',
		'description'   => __( 'The Area directly below the header image on the front page', 'sudbury' ),
		'before_widget' => '<div class="col-md-3 text-center">',
		'after_widget'  => "</div>",
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Left', 'sudbury' ),
		'id'            => 'sidebar-3',
		'description'   => __( 'An optional widget area for your site footer', 'sudbury' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => "</aside>",
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Right', 'sudbury' ),
		'id'            => 'sidebar-4',
		'description'   => __( 'An optional widget area for your site footer', 'sudbury' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => "</aside>",
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}

add_action( 'widgets_init', 'sudbury_widgets_init' );

if ( ! function_exists( 'sudbury_content_nav' ) ) :

	function sudbury_content_nav( $html_id ) {
		global $wp_query;

		if ( $wp_query->max_num_pages > 1 ) : ?>
            <nav id="<?php echo esc_attr( $html_id ); ?>">

                <div
                        class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'sudbury' ) ); ?></div>
                <div
                        class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'sudbury' ) ); ?></div>
                <div class="clear"></div>
            </nav><!-- #nav-above -->
		<?php endif;
	}
endif; // sudbury_content_nav

function sudbury_url_grabber() {
	if ( ! preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"]/is', get_the_content(), $matches ) ) {
		return false;
	}

	return esc_url_raw( $matches[1] );
}


// COMMENTS


if ( ! function_exists( 'sudbury_comment' ) ) :

	function sudbury_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
				?>
                <li class="post pingback">
                <p><?php _e( 'Pingback:', 'sudbury' ); ?><?php comment_author_link(); ?><?php edit_comment_link( __( 'Edit', 'sudbury' ), '<span class="edit-link">', '</span>' ); ?></p>
				<?php
				break;
			default :
				?>
                <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
                <article id="comment-<?php comment_ID(); ?>" class="comment">
                    <footer class="comment-meta">
                        <div class="comment-author vcard">
							<?php
							$avatar_size = 68;
							if ( '0' != $comment->comment_parent ) {
								$avatar_size = 39;
							}

							echo get_avatar( $comment, $avatar_size );

							/* translators: 1: comment author, 2: date and time */
							printf( __( '%1$s on %2$s <span class="says">said:</span>', 'sudbury' ),
								sprintf( '<span class="fn">%s</span>', get_comment_author_link() ),
								sprintf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
									esc_url( get_comment_link( $comment->comment_ID ) ),
									get_comment_time( 'c' ),
									/* translators: 1: date, 2: time */
									sprintf( __( '%1$s at %2$s', 'sudbury' ), get_comment_date(), get_comment_time() )
								)
							);
							?>

							<?php edit_comment_link( __( 'Edit', 'sudbury' ), '<span class="edit-link">', '</span>' ); ?>
                        </div>
                        <!-- .comment-author .vcard -->

						<?php if ( '0' == $comment->comment_approved ) : ?>
                            <em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'sudbury' ); ?></em>
                            <br/>
						<?php endif; ?>

                    </footer>


                    <div class="comment-content"><?php comment_text(); ?></div>

                    <div class="reply">
						<?php comment_reply_link( array_merge( $args, array(
							'reply_text' => __( 'Reply <span>&darr;</span>', 'sudbury' ),
							'depth'      => $depth,
							'max_depth'  => $args['max_depth']
						) ) ); ?>
                    </div>
                    <!-- .reply -->
                </article>
                <!-- #comment-## -->

				<?php
				break;
		endswitch;
	}
endif; // ends check for sudbury_comment()


if ( ! function_exists( 'sudbury_posted_on' ) ) :
	function sudbury_posted_on() {
		printf( __( '<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a><span class="by-author"> <span class="sep"> by </span> <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'sudbury' ),
			esc_url( get_permalink() ),
			esc_attr( get_the_time() ),
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() ),
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_attr( sprintf( __( 'View all posts by %s', 'sudbury' ), get_the_author() ) ),
			get_the_author()
		);
	}
endif;


$menus_to_reverse = array(); //array("internal", "secondary", "primary");

function sudbury_menu_reverse( $menu, $args ) {
	global $menus_to_reverse;
	if ( in_array( $args->theme_location, $menus_to_reverse ) ) {
		return array_reverse( $menu );
	}

	return $menu;
}

// Reverse The Menu Order
add_filter( 'wp_nav_menu_objects', 'sudbury_menu_reverse', 10, 2 );

/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 *
 * @return string The filtered title.
 */
function sudbury_wp_title( $title, $sep ) {
	if ( is_feed() ) {
		return $title;
	}

	global $page, $paged;

	// Add the blog name
	$title .= get_bloginfo( 'name', 'display' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title .= " $sep $site_description";
	}

	// Add a page number if necessary:
	if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
		$title .= " $sep " . sprintf( __( 'Page %s', '_s' ), max( $paged, $page ) );
	}

	return $title;
}

add_filter( 'wp_title', 'sudbury_wp_title', 10, 2 );

/**
 * - Department Homepage - Only Redirect if Not Going to a single post on site
 * - If Custom URL then Redirect to that
 * - If On Single Post and Single Post has redirect url enabled and has redirect url then redirect
 * - If going to Single Post that is a guest posted copy... go to original copy
 * - Else - Ignore and return null
 *
 * @param string $url
 */
function sudbury_link_redirect( $url = '' ) {
	if ( ! is_string( $url ) ) {
		$url = '';
	}
	if ( headers_sent() ) {
		return;
	}
	$warning_override = false;

	if ( is_admin() ) {
		return;
	}

	if ( '' == $url && get_option( 'sudbury_redirect_url', false ) ) {
		// Department Redirects (a site that redirects to another website... I.E. Water District)

		$url = get_option( 'sudbury_redirect_url' );
	}

	if ( ! $url && ! is_singular() ) {
		return;
	}

	if ( $post = get_post() ) {
		if ( sudbury_is_guest_post( $post->ID ) && function_exists( 'sudbury_sharing_get_root_post' ) ) {
			$root = sudbury_sharing_get_root_post( $post->ID );
			get_blog_permalink( $root['BLOG_ID'], $root['ID'] );
		}
	}


	if ( is_singular() ) {
		if ( $post = get_post() ) {
			if ( sudbury_is_guest_post( $post->ID ) && function_exists( 'sudbury_sharing_get_root_post' ) ) {
				$root = sudbury_sharing_get_root_post( $post->ID );
				$url  = get_blog_permalink( $root['BLOG_ID'], $root['ID'] );
			}
		}
		// If the current post specified a redirect url
		$enabled = get_post_meta( get_the_ID(), 'sudbury_redirect_enabled', true ) || 'link' === get_post_type();
		if ( $enabled ) {
			$url              = get_post_meta( get_the_ID(), 'sudbury_redirect_url', true );
			$warning_override = get_post_meta( get_the_ID(), 'sudbury_redirect_warning', true );
		}
	}

	// If we have a url to redirect to then redirect and exit
	if ( $url ) {
		_sudbury_log( "About to Redirect" );
		_sudbury_log( $url );
		$host = parse_url( $url, PHP_URL_HOST );
		if ( $host != null ) {
			if ( $warning_override || is_safe_host( $host ) ) {
				wp_redirect( $url, 302 );
			} else {
				sudbury_set_redirect_url( $url );
				get_template_part( 'redirect', get_post_type() );
			}
		} else {
			// Might be an invalid link so we are not going to exit... probably just a relative url
			wp_redirect( $url, 302 );
		}
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		exit;
	}
}

/* Do not echo data before 'wp' or else redirect will fail because headers are already sent */
add_action( 'wp', 'sudbury_link_redirect' );

/**
 * Handler for a generic redirect from query string.  set the query var 'go' to the url you want and the redirect system will
 * take care of the rest (safe hosts, alternate links, ect.)
 */
function sudbury_go() {
	if ( isset( $_GET['go'] ) ) {
		sudbury_link_redirect( $_GET['go'] );
	}
}

add_action( 'init', 'sudbury_go' );

/**
 * An Array of hard coded safe hosts
 */


/**
 * Determines if a host is known to us and can be trusted for automatic redirects without user consent
 *
 * @param string $host The Host to redirect to
 *
 * @return bool True if it is a safe host, otherwise false
 */
function is_safe_host( $host ) {
	$current_domain = parse_url( get_option( 'siteurl' ) )['host'];
	$safe_hosts     = get_site_option( 'sudbury_safe_hosts', array( $current_domain ) );
	foreach ( $safe_hosts as $safe_host ) {
		// if $host ends with the safe host domain then it is safe >> Makes all subdomains safe as well
		if ( strendswith( trim( $safe_host ), trim( $host ) ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Template Tag for getting the url of the current redirection (used for user consent)
 * @return mixed
 */
function sudbury_get_redirect_url() {
	if ( isset( $GLOBALS['sudbury_redirect_url'] ) ) {
		return $GLOBALS['sudbury_redirect_url'];
	}

	return get_post_meta( get_the_ID(), 'sudbury_redirect_url', true );
}

/**
 * Sets the Redirect URL for this request
 *
 * @param string $url The URL
 */
function sudbury_set_redirect_url( $url ) {
	$GLOBALS['sudbury_redirect_url'] = $url;
}

/* Adds a meta box where you can add links to blogs in the network.  Only available on site 1 */

/**
 * Modifies the RSS Feeds
 *
 * @param $for_comments
 */
function sudbury_feeds_rss2( $for_comments ) {
	$network_rss_template = get_template_directory() . '/feeds/feed-network-rss2.php';
	$blog_rss_template    = get_template_directory() . '/feeds/feed-blog-rss2.php';

	if ( get_current_blog_id() == 1 && file_exists( $network_rss_template ) ) {
		load_template( $network_rss_template );
	} elseif ( file_exists( $blog_rss_template ) ) {
		load_template( $blog_rss_template );
	} else {
		do_feed_rss2( $for_comments ); // Default
	}
}

//remove_all_actions( 'do_feed_rss2' );
//add_action( 'do_feed_rss2', 'sudbury_feeds_rss2', 10, 1 );


if ( ! function_exists( 'sudbury_enqueue_theme_scripts' ) ) {
	/**
	 * Enqueues the Theme's scripts and styles
	 */
	function sudbury_enqueue_theme_scripts() {

		wp_register_script( 'jquery', get_template_directory_uri() . '/js/jquery-3.3.1.min.js', array( 'jquery' ), SUDBURY_VERSION, true );
		wp_register_script( 'bootstrap', get_template_directory_uri() . '/bootstrap-4.1.1/dist/js/bootstrap.min.js', array( 'jquery' ), SUDBURY_VERSION, true );
		wp_register_script( 'jquery-cookie', get_template_directory_uri() . '/js/jquery.cookie.js', array( 'jquery' ), SUDBURY_VERSION, true );
		wp_register_script( 'sudbury_main_js', get_template_directory_uri() . '/js/main.js', array( 'jquery' ), SUDBURY_VERSION, true );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );

		wp_enqueue_script( 'jquery-slim' );
		wp_enqueue_script( 'bootstrap' );
		wp_enqueue_script( 'jquery-cookie' );
		wp_enqueue_script( 'sudbury_main_js' );

		wp_localize_script( 'sudbury_main_js', 'wpApiSettings', array(
			'root'  => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' )
		) );

		// Conditional Tags for scripts don't seem to work right now, it's only for styles so we will manually print the script
		add_action( 'wp_head', function () {
			echo '<style>';
			if ( is_user_logged_in() ) {
				echo '.show_if_logged_in {opacity:1}';
				echo '.hide_if_logged_in {opacity:0}';
			} else {
				echo '.show_if_logged_out {opacity:1}';
				echo '.hide_if_logged_out {opacity:0}';
			}
			echo '</style>';
			echo '<!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->';
		} );

//		wp_register_style( 'font-awesome', get_template_directory_uri() . '/font-awesome/css/fontawesome-all.min.css', array(), SUDBURY_VERSION );
//		wp_enqueue_style( 'font-awesome' );

		wp_register_style( 'sudbury_bootstrap', get_template_directory_uri() . '/bootstrap-4.1.1/dist/css/bootstrap.min.css', array(), SUDBURY_VERSION );
//		wp_register_style( 'sudbury_reset', get_template_directory_uri() . '/css/reset.css', array(), SUDBURY_VERSION );
		wp_register_style( 'sudbury_style', get_stylesheet_uri(), array(), SUDBURY_VERSION );
		wp_register_style( 'sudbury_legacy', get_template_directory_uri() . '/legacy.css', array(), SUDBURY_VERSION );
//		wp_register_style( 'sudbury_print', get_template_directory_uri() . '/css/print.css', array(), SUDBURY_VERSION, 'print' );

		wp_enqueue_style( 'sudbury_bootstrap' );
//		wp_enqueue_style( 'sudbury_reset' );
		wp_enqueue_style( 'sudbury_style' );
		wp_enqueue_style( 'sudbury_legacy' );
//		wp_enqueue_style( 'sudbury_print' );
	}

	add_action( 'wp_enqueue_scripts', 'sudbury_enqueue_theme_scripts' );
}

if ( ! function_exists( 'sudbury_department_tabs' ) ) {

	/**
	 * @param array $args Extra args to pass to wp_nav_menu
	 */
	function sudbury_department_tabs( $args = array() ) {
//		if ( ! has_nav_menu( 'tabs' ) ) {
//			return;
//		}
//
//		$args = wp_parse_args( $args, array(
//			'theme_location' => 'tabs',
//			'container_id'   => 'main-col-menu',
//			'menu_id'        => 'dept-nav',
//			'menu_class'     => 'bar'
//		) );
//
//
//		wp_nav_menu( $args );
	}
}

class Sudbury_Customizer {
	/**
	 * Register Customizer Settings
	 *
	 * @param WP_Customize_Manager $wp_customize The WP_Customize_Manager Object
	 */
	function register( $wp_customize ) {

		$wp_customize->add_section( 'sudbury_custom_css', array(
			'title'    => __( 'Custom CSS', 'sudbury' ),
			'priority' => 120,
		) );

		//  =============================
		//  = Text Input				=
		//  =============================
		$wp_customize->add_setting( 'custom_css', array(
			'default'    => '',
			'capability' => 'edit_theme_options',
			'type'       => 'theme_mod',
			'transport'  => 'postMessage',
		) );
		$wp_customize->add_control( new Sudbury_Customize_Textarea_Control( $wp_customize, 'sudbury_custom_css', array(
			'label'    => __( 'Custom CSS', 'sudbury' ),
			'section'  => 'sudbury_custom_css',
			'settings' => 'custom_css',
		) ) );

		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
		$wp_customize->get_setting( 'background_color' )->transport = 'postMessage';

	}

	function live_preview() {
		wp_enqueue_script(
			'sudbury-customizer', // Give the script a unique ID
			get_template_directory_uri() . '/js/sudbury-customizer.js', // Define the path to the JS file
			array( 'jquery', 'customize-preview' ), // Define dependencies
			SUDBURY_VERSION, // Define a version (optional)
			true // Specify whether to put in footer (leave this true)
		);
	}

	/**
	 * Generates the CSS from the Customizer Setting
	 */
	function custom_css() {
		$css = get_theme_mod( 'custom_css' );
		if ( $css || get_theme_mod( 'background_color' ) ) {

			?>
            <style type="text/css">
                <?php echo $css; ?>
                #header {
                    background-color: <?php echo '#' . get_theme_mod('background_color'); ?>;
                }
            </style>
			<?php
		}
	}
}

$sudbury_customizer = new Sudbury_Customizer();
add_action( 'wp_head', array( $sudbury_customizer, 'custom_css' ) );
add_action( 'customize_register', array( $sudbury_customizer, 'register' ) );
add_action( 'customize_preview_init', array( $sudbury_customizer, 'live_preview' ) );

/**
 * Registers the Textarea Control
 */
function sudbury_customize_register_textarea() {
	class Sudbury_Customize_Textarea_Control extends WP_Customize_Control {
		public $type = 'textarea';

		/**
		 * Renders the control
		 */
		public function render_content() {
			?>
            <label>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
                <textarea rows="20"
                          style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
            </label>
			<?php
		}
	}
}

add_action( 'customize_register', 'sudbury_customize_register_textarea', 1 );


/**
 * Returns an icon for the given site-to-site relationship type. Or if given two Blog IDs then the icon will be generated
 * for the relationship status of the first blog as compared to the second one.
 *
 * @param string|int $this_or_type Either the blog to compare against or one of 'home', 'parent', 'counterpart', 'child'
 * @param bool|int $other The other Blog to compare to.
 *
 * @example sudbury_get_relationship_icon( $current_blog_id ) prints the home icon
 * @example sudbury_get_relationship_icon( $current_blog_id, $counterpart_blog_id ) prints the counterparts icon
 * @example sudbury_get_relationship_icon( $parent_blog_id, $child_blog_id ) prints the parent Icon
 * @example sudbury_get_relationship_icon( $child_blog_id, $parent_blog_id ) prints the child Icon
 * @example sudbury_get_relationship_icon( 'home' ) prints the home Icon
 * @example sudbury_get_relationship_icon( 'parent' ) prints the parent Icon
 * @example sudbury_get_relationship_icon( 'child' ) prints the child Icon
 * @example sudbury_get_relationship_icon( 'counterpart' ) prints the counterpart Icon
 *
 */
function sudbury_the_relationship_icon( $this_or_type, $other = false ) {
	echo sudbury_get_relationship_icon_html( $this_or_type, $other );
}

/**
 * Returns an icon for the given site-to-site relationship type. Or if given two Blog IDs then the icon will be generated
 * for the relationship status of the first blog as compared to the second one.
 *
 * @param string|int $this_or_type Either the blog to compare against or one of 'home', 'parent', 'counterpart', 'child'
 * @param bool|int $other The other Blog to compare to.
 *
 * @return string The HTML of an IMG Element
 * @example sudbury_get_relationship_icon( $current_blog_id ) returns the home icon
 * @example sudbury_get_relationship_icon( $current_blog_id, $counterpart_blog_id ) returns the counterparts icon
 * @example sudbury_get_relationship_icon( $parent_blog_id, $child_blog_id ) returns the parent Icon
 * @example sudbury_get_relationship_icon( $child_blog_id, $parent_blog_id ) returns the child Icon
 * @example sudbury_get_relationship_icon( 'home' ) returns the home Icon
 * @example sudbury_get_relationship_icon( 'parent' ) returns the parent Icon
 * @example sudbury_get_relationship_icon( 'child' ) returns the child Icon
 * @example sudbury_get_relationship_icon( 'counterpart' ) returns the counterpart Icon
 *
 */
function sudbury_get_relationship_icon_html( $this_or_type, $other = false ) {
	if ( is_int( $this_or_type ) ) {
		if ( ! $other || $this_or_type == $other ) {
			$this_or_type = 'home';
		} elseif ( sudbury_get_site_parent( $this_or_type ) == $other ) {
			$this_or_type = 'child';
		} elseif ( in_array( $other, sudbury_get_site_children( $this_or_type ) ) ) {
			$this_or_type = 'parent';
		} elseif ( in_array( $other, sudbury_get_site_counterparts( $this_or_type ) ) ) {
			$this_or_type = 'counterpart';
		}
	}
	$icons = array(
		'home'        => 'house',
		'parent'      => 'arrow_join',
		'counterpart' => 'arrow_refresh',
		'child'       => 'arrow_divide_flip',
	);
	if ( ! isset( $icons[ $this_or_type ] ) ) {
		return "<!-- No Icon for type: $this_or_type -->";
	}
	?>
    <img src="<?php echo get_template_directory_uri(); ?>/icons/<?php echo $icons[ $this_or_type ]; ?>.png"
         alt="<?php ucfirst( "$this_or_type Site" ); ?>"/>
	<?php
}


if ( ! ( $show_expired = ( isset( $_REQUEST['include-archived'] ) || sudbury_is_site_archived() ) ) ) {
	$mod_query['post_status'] = 'publish';
}

/**
 * Returns the message alerting a user to the fact that the given post is archived
 *
 * @param int|string|WP_Post $post The Post
 * @param string|int $blog_id
 *
 * @return string HTML for the archived message
 */
function sudbury_get_archived_post_message( $post = 0, $blog_id = '' ) {
	if ( ! sudbury_is_post_archived( $post, $blog_id ) ) {
		$message = '';
	} else {

		$post    = get_post( $post );
		$post_id = $post->ID;
		$message = '<i class="fa fa-archive fa-3x fa-pull-left"></i>This ' . get_post_type_object( get_post_type( $post_id ) )->labels->singular_name . ' has been archived and it\'s content might be outdated.  If you are looking for recent content please check this <a href="' . get_site_url( $blog_id ) . '">' . sudbury_get_site_type( $blog_id ) . '\'s Homepage</a>';
	}

	return apply_filters( 'sudbury_get_archived_post_message', $message, $post, $blog_id );
}

/**
 * Echos the message alerting a user to the fact that the given post is archived
 *
 * @param int|string|WP_Post $post The Post
 * @param string|int $blog_id
 *
 */
function sudbury_the_archived_post_message( $post = 0, $blog_id = '' ) {
	echo sudbury_get_archived_post_message( $post, $blog_id );
}

// Only pull items that have a run end for the main Loop... wqe will take care of items that don't have an expiration in a secondary loop below

/**
 * Due to an issue with the query_posts function where the order of sticky posts is lost when a category is queried, we must manually filter the query for department homepages
 */
function sudbury_homepage_query( $query ) {
	if ( isset( $GLOBALS['main_query_done'] ) ) {
		return $query;
	}
	if ( $query->is_home && ( ! isset( $GLOBALS['main_query_done'] ) || ! $GLOBALS['main_query_done'] ) ) {
		if ( ! isset( $_REQUEST['include-archived'] ) && ! sudbury_is_site_archived() ) {
			$query->set( 'post_status', 'publish' );
		}

		$query->set( 'meta_key', '_post-expiration-enabled' );
		$query->set( 'meta_value', 1 );
		$query->set( 'posts_per_page', 10 );
		$query->set( 'category_name', 'general' );
	}
	$GLOBALS['main_query_done'] = true;

	return $query;
}

add_filter( 'pre_get_posts', 'sudbury_homepage_query' );


require_once( "functions/network.php" );


// SHARING UI FUNCTIONS

require_once( "functions/social.php" );

/*** MOE's additions ***/
/* This enables php code to be added to a widget text box
   From: http://www.emanueleferonato.com/2011/04/11/executing-php-inside-a-wordpress-widget-without-any-plugin/
    */
add_filter( 'widget_text', 'execute_php', 100 );
function execute_php( $html ) {
	if ( strpos( $html, "<" . "?php" ) !== false ) {
		ob_start();
		eval( "?" . ">" . $html );
		$html = ob_get_contents();
		ob_end_clean();
	}

	return $html;
}

/* Do not display Senior Center (blog_id) on Town events in sidebar
   the sidebar for Town Events uses a shortcode which calls this filter  */
function sudb_em_events_output_events( $events ) {
	$senior_blog  = SUDB_SENIORCENTER_SITE_ID;
	$current_blog = get_current_blog_id();
	if ( $current_blog == $senior_blog ) {
		return ( $events );
	}
	$events_to_return = array();
	if ( $events ) {
		foreach ( $events as $event ) {
			if ( $event->blog_id != $senior_blog ) {
				$events_to_return[] = $event;
			}
		}

		return $events_to_return;
	} else {
		return $events;
	}
}

add_filter( 'em_events_output_events', 'sudb_em_events_output_events' );
/* on sudbury event widget, use category to determine the blog
   the sidebar Senior Center Events calls the_widget with a category of SUDB_SENIORCENTER_SITE_ID
   which uses this code. I force the category input to check as blog id. */
function sudb_em_widget_events_get_args( $instance ) {
	if ( get_current_blog_id() == '1' ) {
		$instance['blog']     = $instance['category'];
		$instance['category'] = null;
	}

	return $instance;
}

add_filter( 'em_widget_events_get_args', 'sudb_em_widget_events_get_args' );

/**
 * Replaces the normal WPFC ajax and uses the EM query system to provide event specific results.
 * replaces wpfc_em_ajax in the full calendar em_wpfc.php file with this one which checks
 * to see if the blog id is for the senior center (SUDB_SENIORCENTER_SITE_ID), and does not show events from that blog-id
 */
remove_action( 'wp_ajax_WP_FullCalendar', 'wpfc_em_ajax' );
remove_action( 'wp_ajax_nopriv_WP_FullCalendar', 'wpfc_em_ajax' );
add_action( 'wp_ajax_WP_FullCalendar', 'sudb_wpfc_em_ajax' );
add_action( 'wp_ajax_nopriv_WP_FullCalendar', 'sudb_wpfc_em_ajax' );

function sudb_wpfc_em_ajax() {
	$_REQUEST['month'] = false; //no need for these two, they are the original month and year requested
	$_REQUEST['year']  = false;

	//get the year and month to show, which would be the month/year between start and end request params
	$scope_start             = strtotime( substr( $_REQUEST['start'], 0, 10 ) );
	$scope_end               = strtotime( substr( $_REQUEST['end'], 0, 10 ) );
	$scope_middle            = $scope_start + ( $scope_end - $scope_start ) / 2;
	$month                   = date( 'n', $scope_middle );
	$year                    = date( 'Y', $scope_middle );
	$args                    = array(
		'month'   => $month,
		'year'    => $year,
		'owner'   => false,
		'status'  => 1,
		'orderby' => 'event_start_time, event_name'
	); //since wpfc handles date sorting we only care about time and name ordering here
	$args['number_of_weeks'] = 6; //WPFC always has 6 weeks
	$limit                   = $args['limit'] = get_option( 'wpfc_limit', 3 );
	$args['long_events']     = ( isset( $_REQUEST['long_events'] ) && $_REQUEST['long_events'] == 0 ) ? 0 : 1; //long events are enabled, unless explicitly told not to in the shortcode

	//do some corrections for EM query
	if ( isset( $_REQUEST[ EM_TAXONOMY_CATEGORY ] ) || empty( $_REQUEST['category'] ) ) {
		$_REQUEST['category'] = ! empty( $_REQUEST[ EM_TAXONOMY_CATEGORY ] ) ? $_REQUEST[ EM_TAXONOMY_CATEGORY ] : false;
	}
	$_REQUEST['tag'] = ! empty( $_REQUEST[ EM_TAXONOMY_TAG ] ) ? $_REQUEST[ EM_TAXONOMY_TAG ] : false;
	$args            = apply_filters( 'sudb_wpfc_fullcalendar_args', array_merge( $_REQUEST, $args ) );
	$calendar_array  = EM_Calendar::get( $args );

	$parentArray = $events = $event_ids = $event_date_counts = $event_dates_more = $event_day_counts = array();

	//get day link template
	global $wp_rewrite;
	if ( get_option( "dbem_events_page" ) > 0 ) {
		$event_page_link = get_permalink( get_option( "dbem_events_page" ) ); //PAGE URI OF EM
		if ( $wp_rewrite->using_permalinks() ) {
			$event_page_link = trailingslashit( $event_page_link );
		}
	} else {
		if ( $wp_rewrite->using_permalinks() ) {
			$event_page_link = trailingslashit( home_url() ) . EM_POST_TYPE_EVENT_SLUG . '/'; //don't use EM_URI here, since ajax calls this before EM_URI is defined.
		} else {
			$event_page_link = home_url() . '?post_type=' . EM_POST_TYPE_EVENT; //don't use EM_URI here, since ajax calls this before EM_URI is defined.
		}
	}
	if ( $wp_rewrite->using_permalinks() && ! defined( 'EM_DISABLE_PERMALINKS' ) ) {
		$event_page_link .= "%s/";
	} else {
		$joiner          = ( stristr( $event_page_link, "?" ) ) ? "&" : "?";
		$event_page_link .= $joiner . "calendar_day=%s";
	}
	foreach ( $calendar_array['cells'] as $date => $cell_data ) {
		if ( empty( $event_day_counts[ $date ] ) ) {
			$event_day_counts[ $date ] = 0;
		}
		/* @var $EM_Event EM_Event */
		$orig_color = get_option( 'dbem_category_default_color' );
		foreach ( $cell_data['events'] as $EM_Event ) {
			/* moe added this check to remove senior events from town calendar */
			$senior_blog = SUDB_SENIORCENTER_SITE_ID;
			if ( $EM_Event->blog_id != $senior_blog ) {

				$color     = $borderColor = $orig_color;
				$textColor = '#fff';
				if ( ! empty ( $EM_Event->get_categories()->categories ) ) {
					foreach ( $EM_Event->get_categories()->categories as $EM_Category ) {
						/* @var $EM_Category EM_Category */
						if ( $EM_Category->get_color() != '' ) {
							$color = $borderColor = $EM_Category->get_color();
							if ( preg_match( "/#fff(fff)?/i", $color ) ) {
								$textColor   = '#777';
								$borderColor = '#ccc';
							}
							break;
						}
					}
				}
				if ( ! in_array( $EM_Event->event_id, $event_ids ) ) {
					//count events for all days this event may span
					if ( $EM_Event->event_start_date != $EM_Event->event_end_date ) {
						for ( $i = $EM_Event->start; $i <= $EM_Event->end; $i = $i + 86400 ) {
							$idate = date( 'Y-m-d', $i );
							empty( $event_day_counts[ $idate ] ) ? $event_day_counts[ $idate ] = 1 : $event_day_counts[ $idate ] ++;
						}
					} else {
						$event_day_counts[ $date ] ++;
					}
					if ( $event_day_counts[ $date ] <= $limit ) {
						$title       = $EM_Event->output( get_option( 'dbem_emfc_full_calendar_event_format', '#_EVENTNAME' ), 'raw' );
						$event_array = array(
							"title"       => $title,
							"color"       => $color,
							'textColor'   => $textColor,
							'borderColor' => $borderColor,
							"start"       => date( 'Y-m-d\TH:i:s', $EM_Event->start ),
							"end"         => date( 'Y-m-d\TH:i:s', $EM_Event->end ),
							"url"         => $EM_Event->get_permalink(),
							'post_id'     => $EM_Event->post_id,
							'event_id'    => $EM_Event->event_id,
							'allDay'      => $EM_Event->event_all_day == true
						);
						if ( $args['long_events'] == 0 ) {
							$event_array['end'] = $event_array['start'];
						} //if long events aren't wanted, make the end date same as start so it shows this way on the calendar
						$events[]    = apply_filters( 'sudb_wpfc_events_event', $event_array, $EM_Event );
						$event_ids[] = $EM_Event->event_id;
					}
				}
			}
			if ( $cell_data['events_count'] > $limit ) {
				$event_dates_more[ $date ] = 1;
				$day_ending                = $date . "T23:59:59";
				$events[]                  = apply_filters( 'sudb_wpfc_events_more', array(
					"title"    => get_option( 'wpfc_limit_txt', 'more ...' ),
					"color"    => get_option( 'wpfc_limit_color', '#fbbe30' ),
					"start"    => $day_ending,
					"url"      => str_replace( '%s', $date, $event_page_link ),
					'post_id'  => 0,
					'event_id' => 0,
					'allDay'   => true
				), $date );
			}
		}
	}

	echo EM_Object::json_encode( apply_filters( 'sudb_wpfc_events', $events ) );
	die();
}

/* remove styles files from tabby responsive tabs */
remove_action( 'wp_print_styles', 'cc_tabby_css', 30 );

function sudbury_edit_post_link( $text = '', $post_id = 0 ) {
	if ( $text ) {
		$text = ' ' . $text;
	} else {
		$text = ' Edit';
	}
	edit_post_link( '<i class="fa fa-edit"></i>' . $text, '', '', $post_id );
}

function sudbury_get_main_col_classes( $addl = '', $layout = null ) {
	if ( $layout === null ) {
		$layout = sudbury_get_post_layout();
	}

	return 'main layout-' . esc_attr( $layout ) . ' ' . ( $layout == 'normal' ? 'col-md-8' : 'col-md-12' ) . " $addl";
}

function sudbury_main_col_class( $addl = '', $layout = null ) {
	echo "class=\"" . esc_attr( sudbury_get_main_col_classes( $addl, $layout ) ) . "\"";
}


function has_title( $post = 0 ) {
	$layout     = 'normal';
	$show_title = true;

	$post = get_post( $post );

	if ( is_singular() ) {
		if ( $post->post_type == 'page' ) {
			$template = basename( get_page_template() );


			switch ( $template ) {
				case 'page-fullwidth.php':
					$layout = 'full';
					break;
				case 'page-fullwidth-notitle.php':
					$layout     = 'full';
					$show_title = false;
					break;
			}
		}
	}

	return $show_title;

}

function has_sidebar() {
	return sudbury_get_post_layout() == 'normal';
}

$GLOBALS['inject_content'] = array();

function inject_content( $callback ) {
	$GLOBALS['inject_content'][] = $callback;
}

function inject_content_handler( $content ) {
	if ( ! isset( $GLOBALS['inject_content'] ) ) {
		$GLOBALS['inject_content'] = array();
	}

	if ( $GLOBALS['injecting_content'] ) {
		return $content;
	}

	if ( ! empty( $GLOBALS['inject_content'] ) ) {
		$content = '';
	}

	$callbacks                    = $GLOBALS['inject_content'];
	$GLOBALS['injecting_content'] = true;

	foreach ( $callbacks as $callback ) {
		ob_start();
		$callback( $content );
		$content .= ob_get_clean();
	}
	$GLOBALS['injecting_content'] = false;


	return $content;
}

add_filter( 'the_content', 'inject_content_handler', 10, 1 );


$GLOBALS['sudbury_loops'] = array();
// Used to skip switching the blog when the_post is called on a wp_reset_postdata.
// The post could be reset to a network / shared post which would leave the request
// in a different blog for the next section.
// TODO: Investigate whether we can safely remove / are improperly using wp_reset_postdata
//       Namely in home.php for the General News query.
$GLOBALS['sudbury_skip_the_post'] = 0;

function sudbury_the_post( $post ) {
	global $wp_query;

	if ( is_admin() ) {
		return;
	}
	if ( $GLOBALS['sudbury_skip_the_post'] > 0 ) {
		d( "Skipping the_post handler" );
		$GLOBALS['sudbury_skip_the_post'] --;

		//sudbury_loop_end( $wp_query );
		return;
	}

	// Fix Featured Images when looping over a network query
	if ( isset( $post->BLOG_ID ) && is_main_query() ) {
		$GLOBALS['sudbury_loops'][] = get_current_blog_id();
		switch_to_blog( $post->BLOG_ID );
		d( "Switch -> {$post->BLOG_ID} (Network Post)" );
	} else if ( sudbury_is_guest_post( $post ) ) {
		$root = sudbury_sharing_get_root_post( $post );
		if ( $root ) {
			$GLOBALS['sudbury_loops'][] = get_current_blog_id();
			d( "Switch -> {$root['BLOG_ID']} (Shared Post)" );
			switch_to_blog( $root['BLOG_ID'] );
		}
	} else {
		d( 'No Action - Restoring' );
		sudbury_restore_blogs();
	}
}

add_action( 'the_post', 'sudbury_the_post', 10, 1 );

function sudbury_loop_start( $query ) {
	global $wp_query;
	$GLOBALS['sudbury_skip_the_post'] = 0;
	d( 'Loop Start -> ' . get_current_blog_id() );
	$GLOBALS['main_post'] = $wp_query->post;
	$GLOBALS['main_blog'] = get_current_blog_id();
}

add_action( 'loop_start', 'sudbury_loop_start', 10, 1 );

if ( ! function_exists( 'sudbury_restore_blogs' ) ) {
	function sudbury_restore_blogs() {
		foreach ( array_reverse( $GLOBALS['sudbury_loops'] ) as $id ) {
			d( "Reset -> $id" );
			restore_current_blog();

			if ( get_current_blog_id() != $id ) {
				sudbury_log( 'Invalid Loop rollback: Expected ' . $id . ' got ' . get_current_blog_id() );
			}
		};
		$GLOBALS['sudbury_loops'] = array();
	}
}

function sudbury_loop_end( $query ) {
	sudbury_restore_blogs();
	$GLOBALS['sudbury_skip_the_post'] = 1;
}

add_action( 'loop_end', 'sudbury_loop_end', 10, 1 );
