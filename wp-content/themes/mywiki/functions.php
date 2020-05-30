<?php 
add_action( 'wp_enqueue_scripts', 'mywiki_theme_setup' );
function mywiki_theme_setup(){
wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css', array(), '3.0.1', 'all' );
wp_enqueue_style('style', get_stylesheet_uri());
wp_enqueue_script( 'bootstrap',  get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), '3.0.1');
wp_enqueue_script( 'ajaxsearch',  get_template_directory_uri() . '/js/ajaxsearch.js', array(), '1.0.0');
wp_enqueue_script( 'general',  get_template_directory_uri() . '/js/general.js');
wp_localize_script( 'general', 'my_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
if ( is_singular() ) wp_enqueue_script( 'comment-reply' );
}
/* mywiki theme starts */
if ( ! function_exists( 'mywiki_setup' ) ) :
function mywiki_setup() {
	/* content width */
	global $content_width;
	if ( ! isset( $content_width ) ) {
		$content_width = 900;
	}
	/*
	 * Make mywiki theme available for translation.
	 *
	 */
	load_theme_textdomain( 'mywiki', get_template_directory() . '/languages' );
	// This theme styles the visual editor to resemble the theme style.
	add_editor_style( 'css/editor-style.css' );
	// Add RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );
  add_theme_support( 'title-tag' );
	/*
	 * Enable support for Post Formats.
	 */
	// This theme allows users to set a custom background.
	add_theme_support( 'custom-background', apply_filters( 'mywiki_custom_background_args', array(
		'default-color' => '048eb0',
	) ) );
	// Add support for featured content.
	add_theme_support( 'featured-content', array(
		'featured_content_filter' => 'mywiki_get_featured_posts',
		'max_posts' => 6,
	) );
	// This theme uses its own gallery styles.
	add_filter( 'use_default_gallery_style', '__return_false' );
}
endif; // mywiki_setup
// Implement Custom Header features.
require get_template_directory() . '/function/custom-header.php';

add_action( 'after_setup_theme', 'mywiki_setup' );
/*Title*/
function mywiki_wp_title( $title, $sep ) {
  global $paged, $page;
  if ( is_feed() ) { return $title; } // end if
  // Add the site name.
  $title .= get_bloginfo( 'name' );
  // Add the site description for the home/front page.
  $site_description = get_bloginfo( 'description', 'display' );
  if ( $site_description && ( is_home() || is_front_page() ) ) {
    $title = "$title $sep $site_description";
  } // end if
  // Add a page number if necessary.
  if ( $paged >= 2 || $page >= 2 ) {
    $title = sprintf( __( 'Page %s', 'mywiki' ), max( $paged, $page ) ) . " $sep $title";
  } // end if
  return $title;
} // end mywiki_wp_title
add_filter( 'wp_title', 'mywiki_wp_title', 10, 2 );
if ( ! function_exists( 'mywiki_entry_meta' ) ) :
/**
 * Set up post entry meta.
 *
 * Meta information for current post: categories, tags, permalink, author, and date.
 **/
function mywiki_entry_meta() {
	$mywiki_category_list = get_the_category_list(', ');
	$mywiki_tag_list = get_the_tag_list('',', ');
	$mywiki_date = sprintf( '<a href="%1$s" title="%2$s" ><time datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);
	$mywiki_author = sprintf( '<span><a href="%1$s" title="%2$s" >%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'mywiki' ), get_the_author() ) ),
		get_the_author()
	);
	if ( $mywiki_tag_list ) {
		$mywiki_utility_text = __( 'Posted %3$s by %4$s & filed under %1$s Comments: '.get_comments_number(), 'mywiki' );
	} elseif ( $mywiki_category_list ) {
		$mywiki_utility_text = __( 'Posted %3$s by %4$s & filed under %1$s Comments: '.get_comments_number(), 'mywiki' );
	} else {
		$mywiki_utility_text = __(__( 'Posted %3$s by %4$s Comments:','mywiki'), get_comments_number());
	}
	printf(
		$mywiki_utility_text,
		$mywiki_category_list,
		$mywiki_tag_list,
		$mywiki_date,
		$mywiki_author
	);
}
endif;
/**
 * Add default menu style if menu is not set from the backend.
 */
function mywiki_add_menuclass ($page_markup) {
preg_match('/^<div class=\"([a-z0-9-_]+)\">/i', $page_markup, $mywiki_matches);
$mywiki_toreplace = array('<div class="navbar-collapse collapse top-gutter">', '</div>');
$mywiki_replace = array('<div class="navbar-collapse collapse top-gutter">', '</div>');
$mywiki_new_markup = str_replace($mywiki_toreplace,$mywiki_replace, $page_markup);
$mywiki_new_markup= preg_replace('/<ul/', '<ul class="nav navbar-nav navbar-right mywiki-header-menu"', $mywiki_new_markup);
return $mywiki_new_markup; } //}
add_filter('wp_page_menu', 'mywiki_add_menuclass');
register_nav_menus(
		array(
			'primary' => __( 'The Main Menu', 'mywiki' ),  // main nav in header
			'footer-links' => __( 'Footer Links', 'mywiki' ) // secondary nav in footer
		)
	);
function mywiki_category_widget_function($mywiki_args) {
   extract($mywiki_args);
   echo $before_widget;
  echo $before_title . '<p class="wid-category"><span>'.__('Categories','mywiki').'</span></p>' . $after_title;
   echo $after_widget;
   // print some HTML for the widget to display here
  $mywiki_cat = array(
			'child_of'                 => 0,
			'parent'                   => '',
			'orderby'                  => 'name',
			'order'                    => 'ASC',
			'hide_empty'               => 0,
			'hierarchical'             => 1,
                        'exclude'                  => '',			
			'include'                  => '',
			'number'                   => '',
			'taxonomy'                 => 'category',
			'pad_counts'               => false
			 );
	 
	 $mywiki_cat = get_categories( $mywiki_cat );
	 echo "<div class='wid-cat-container'><ul>";
	 foreach ($mywiki_cat as $mywiki_categories) {
		 ?>
<li><a href="<?php echo get_category_link( $mywiki_categories->term_id );?>" class="wid-cat-title"><?php echo $mywiki_categories->name ; ?>
</a></li>
<?php }
echo "</ul></div>";
}
wp_register_sidebar_widget(
    __('Category Widget','mywiki'),        // your unique widget id
    __('Category Widget','mywiki'),          // widget name
    'mywiki_category_widget_function',  // callback function
    array(                  // options
        'description' => __('Category Widget Shows Category','mywiki')
    )	
);
add_action( 'widgets_init', 'mywiki_popular_load_widgets' );
function mywiki_popular_load_widgets() {
register_widget( 'mywiki_popular_widget' );
register_widget( 'mywiki_recentpost_widget' );
}
/** Define the Widget as an extension of WP_Widget **/
class mywiki_popular_widget extends WP_Widget {
function __construct() {
/* Widget settings. */
$mywiki_widget_ops = array( 'classname' => 'widget_popular', 'description' => __('Displays most popular posts by comment count','mywiki'));
 
/* Widget control settings. */
$mywiki_control_ops = array( 'id_base' => 'popular-widget' );
 
/* Create the widget. */
parent::__construct( 'popular-widget', __('Popular Posts','mywiki'), $mywiki_widget_ops, $mywiki_control_ops );
}
 
// Limit to last 30 days
function filter_where( $where = '' ) {
// posts in the last 30 days
$where .= " AND post_date > '" . date('Y-m-d', strtotime('-' . $instance['days'] .' days')) . "'";
return $where;
}
function widget( $args, $instance ) {
extract( $args );
echo $before_widget;
if( !empty( $instance['title'] ) ) echo $before_title .'<p class="wid-category"><span>'.$instance['title'].'</span></p>' . $after_title;
$loop_args = array(
'posts_per_page' => (int) $instance['count'],
'orderby' => 'comment_count'
);
if( 0 == $instance['days'] ) {
$loop = new WP_Query( $loop_args );
}else{
add_filter( 'posts_where', array( $this, 'filter_where' ) );
$loop = new WP_Query( $loop_args );
remove_filter( 'posts_where', array( $this, 'filter_where' ) );
}echo "<div class='wid-cat-container'><ul>";
if( $loop->have_posts() ): while( $loop->have_posts() ): $loop->the_post(); global $post;
?><li>
<a href="<?php echo get_permalink();?>" class="wid-cat-title wid-popular-post">
  <?php the_title() ;?>
</a></li>
<?php endwhile; endif; wp_reset_query();
echo "</ul></div>";
echo $after_widget;
}
function update( $new_instance, $old_instance ) {
$instance = $old_instance;
/* Strip tags (if needed) and update the widget settings. */
$mywiki_instance['title'] = esc_attr( $new_instance['title'] );
$mywiki_instance['count'] = (int) $new_instance['count'];
$mywiki_instance['days'] = (int) $new_instance['days'];
return $instance;
}
function form( $instance ) {
/* Set up some default widget settings. */
$mywiki_defaults = array( 'title' => '', 'count' => 5, 'days' => 30 );
$instance = wp_parse_args( (array) $instance, $mywiki_defaults ); ?>
<p>
  <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'mywiki') ?>:</label>
  <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
</p>
<p>
  <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e('Number of Posts', 'mywiki') ?>:</label>
  <input id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" size="3" value="<?php echo $instance['count']; ?>" />
</p>
<p>
  <label for="<?php echo $this->get_field_id( 'days' ); ?>"><?php _e('Posted in the past X days', 'mywiki') ?>:</label>
  <input id="<?php echo $this->get_field_id( 'days' ); ?>" name="<?php echo $this->get_field_name( 'days' ); ?>" size="3" value="<?php echo $instance['days']; ?>" />
</p>
<p class="description"><?php _e('Use 0 for no time limit.', 'mywiki') ?></p>
<?php
}
}
class mywiki_recentpost_widget extends WP_Widget {
function __construct() {
/* Widget settings. */
$mywiki_widget_ops = array( 'classname' => 'widget_recentpost', 'description' => __('Displays most recent posts by post count','mywiki') );
 
/* Widget control settings. */
$mywiki_control_ops = array( 'id_base' => 'recent-widget' );
 
/* Create the widget. */
parent::__construct( 'recent-widget', __('Recent Posts','mywiki'), $mywiki_widget_ops, $mywiki_control_ops );
}
function widget( $args, $instance ) {
extract( $args );
echo $before_widget;
if( !empty( $instance['title'] ) ) echo $before_title .'<p class="wid-category"><span>'.$instance['title'].'</span></p>' . $after_title;
$mywiki_loop_args = array(
'posts_per_page' => (int) $instance['count'],
'orderby' => 'DESC'
);
$mywiki_loop = new WP_Query( $mywiki_loop_args );
echo "<div class='wid-cat-container'><ul>";
if( $mywiki_loop->have_posts() ): while( $mywiki_loop->have_posts() ): $mywiki_loop->the_post(); global $post;
?><li>
<a href="<?php echo get_permalink();?>" class="wid-cat-title wid-popular-post"><?php the_title() ;?></a></li>
<?php endwhile; endif; wp_reset_query();
echo "</ul></div>";
echo $after_widget;
}
function update( $new_instance, $old_instance ) {
$mywiki_instance = $old_instance;
/* Strip tags (if needed) and update the widget settings. */
$mywiki_instance['title'] = esc_attr( $new_instance['title'] );
$mywiki_instance['count'] = (int) $new_instance['count'];
return $mywiki_instance;
}
function form( $instance ) {
/* Set up some default widget settings. */
$mywiki_defaults = array( 'title' => '', 'count' => 5, 'days' => 30 );
$instance = wp_parse_args( (array) $instance, $mywiki_defaults ); ?>
<p>
  <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'mywiki') ?>:</label>
  <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
</p>
<p>
  <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e('Number of Posts', 'mywiki') ?>:</label>
  <input id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" size="3" value="<?php echo $instance['count']; ?>" />
</p>
<?php
} 
}
    register_sidebar(array(
    	'id' => 'sidebar1',
    	'name' => __('Main Sidebar','mywiki'),
    	'description' => __('Used on every page.','mywiki'),
    	'before_widget' => '<div id="%1$s" class="widget %2$s">',
    	'after_widget' => '</div>',
    	'before_title' => '<h4 class="widgettitle">',
    	'after_title' => '</h4>',
    ));
	    register_sidebar(array(
    	'id' => 'footer1',
    	'name' => __('Footer Content Area 1','mywiki'),
    	'description' => __('Used on Footer.','mywiki'),
    	'before_widget' => '<div id="%1$s" class="widget %2$s">',
    	'after_widget' => '</div>',
    	'before_title' => '<h4 class="widgettitle">',
    	'after_title' => '</h4>',
    ));
	    register_sidebar(array(
    	'id' => 'footer2',
    	'name' => __('Footer Content Area 2','mywiki'),
    	'description' => __('Used on Footer.','mywiki'),
    	'before_widget' => '<div id="%1$s" class="widget %2$s">',
    	'after_widget' => '</div>',
    	'before_title' => '<h4 class="widgettitle">',
    	'after_title' => '</h4>',
    ));
	    register_sidebar(array(
    	'id' => 'footer3',
    	'name' => __('Footer Content Area 3','mywiki'),
    	'description' => __('Used on Footer.','mywiki'),
    	'before_widget' => '<div id="%1$s" class="widget %2$s">',
    	'after_widget' => '</div>',
    	'before_title' => '<h4 class="widgettitle">',
    	'after_title' => '</h4>',
    ));
if ( function_exists( 'add_theme_support' ) ) {
		add_theme_support( 'post-thumbnails' );
    set_post_thumbnail_size( 150, 150 ); // default Post Thumbnail dimensions
}
if ( function_exists( 'add_image_size' ) ) { 
		add_image_size( 'category-thumb', 300, 9999 ); //300 pixels wide (and unlimited height)
		add_image_size( 'homepage-thumb', 220, 180, true ); //(cropped)
}
add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form' ) );
function mywiki_custom_breadcrumbs() {
  $mywiki_showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
  $mywiki_delimiter = '&raquo;'; // delimiter between crumbs
  $mywiki_home = __('Home','mywiki'); // text for the 'Home' link
  $mywiki_showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
  $mywiki_before = '<span class="current">'; // tag before the current crumb
  $mywiki_after = '</span>'; // tag after the current crumb
  global $post;
  $mywiki_homeLink = esc_url( home_url( '/' ) );
  if (is_home() || is_front_page()) {
    if ($mywiki_showOnHome == 1) echo '<div id="crumbs" class="mywiki_breadcrumbs"><a href="' . $mywiki_homeLink . '">' . $mywiki_home . '</a></div>';
  } else {
    echo '<div id="crumbs" class="mywiki_breadcrumbs"><a href="' . $mywiki_homeLink . '">' . $mywiki_home . '</a> ' . $mywiki_delimiter . ' ';
    if ( is_category() ) {
      $mywiki_thisCat = get_category(get_query_var('cat'), false);
      if ($mywiki_thisCat->parent != 0) echo get_category_parents($mywiki_thisCat->parent, TRUE, ' ' . $mywiki_delimiter . ' ');
      echo $mywiki_before . _e('Archive by category ','mywiki').' : '.single_cat_title('', false).$mywiki_after;
    } elseif ( is_search() ) {
      echo $mywiki_before . _e('Search results for ','mywiki') . get_search_query() . '"' . $mywiki_after;
    } elseif ( is_day() ) {
      echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $mywiki_delimiter . ' ';
      echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $mywiki_delimiter . ' ';
      echo $mywiki_before . get_the_time('d') . $mywiki_after;
    } elseif ( is_month() ) {
      echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $mywiki_delimiter . ' ';
      echo $mywiki_before . get_the_time('F') . $mywiki_after;
    } elseif ( is_year() ) {
      echo $mywiki_before . get_the_time('Y') . $mywiki_after;
    } elseif ( is_single() && !is_attachment() ) {
      if ( get_post_type() != 'post' ) {
        $mywiki_post_type = get_post_type_object(get_post_type());
        $mywiki_slug = $mywiki_post_type->rewrite;
        echo '<a href="' . $mywiki_homeLink . '/' . $mywiki_slug['slug'] . '/">' . $mywiki_post_type->labels->singular_name . '</a>';
        if ($mywiki_showCurrent == 1) echo ' ' . $mywiki_delimiter . ' ' . $mywiki_before . get_the_title() . $mywiki_after;
      } else {
        $mywiki_cat = get_the_category(); $mywiki_cat = $mywiki_cat[0];
        $mywiki_cats = get_category_parents($mywiki_cat, TRUE, ' ' . $mywiki_delimiter . ' ');
        if ($mywiki_showCurrent == 0) $mywiki_cats = preg_replace("#^(.+)\s$mywiki_delimiter\s$#", "$1", $mywiki_cats);
        echo $mywiki_cats;
        if ($mywiki_showCurrent == 1) echo $mywiki_before . get_the_title() . $mywiki_after;
      }
    } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
      $mywiki_post_type = get_post_type_object(get_post_type());
      echo $mywiki_before . $mywiki_post_type->labels->singular_name . $mywiki_after;
    } elseif ( is_attachment() ) {
      $mywiki_parent = get_post($post->post_parent);
      $mywiki_cat = get_the_category($mywiki_parent->ID); $mywiki_cat = $mywiki_cat[0];
      echo get_category_parents($mywiki_cat, TRUE, ' ' . $mywiki_delimiter . ' ');
      echo '<a href="' . get_permalink($mywiki_parent) . '">' . $mywiki_parent->post_title . '</a>';
      if ($mywiki_showCurrent == 1) echo ' ' . $mywiki_delimiter . ' ' . $mywiki_before . get_the_title() . $mywiki_after;
    } elseif ( is_page() && !$post->post_parent ) {
      if ($mywiki_showCurrent == 1) echo $mywiki_before . get_the_title() . $mywiki_after;
    } elseif ( is_page() && $post->post_parent ) {
      $mywiki_parent_id  = $post->post_parent;
      $mywiki_breadcrumbs = array();
      while ($mywiki_parent_id) {
        $mywiki_page = get_page($mywiki_parent_id);
        $mywiki_breadcrumbs[] = '<a href="' . get_permalink($mywiki_page->ID) . '">' . get_the_title($mywiki_page->ID) . '</a>';
        $mywiki_parent_id  = $mywiki_page->post_parent;
      }
      $mywiki_breadcrumbs = array_reverse($mywiki_breadcrumbs);
      for ($mywiki_i = 0; $mywiki_i < count($mywiki_breadcrumbs); $mywiki_i++) {
        echo $mywiki_breadcrumbs[$mywiki_i];
        if ($mywiki_i != count($mywiki_breadcrumbs)-1) echo ' ' . $mywiki_delimiter . ' ';
      }
      if ($mywiki_showCurrent == 1) echo ' ' . $mywiki_delimiter . ' ' . $mywiki_before . get_the_title() . $mywiki_after;
    } elseif ( is_tag() ) {
      echo $mywiki_before ._e('Posts tagged ','mywiki') . single_tag_title('', false) . '"' . $mywiki_after;
    } elseif ( is_author() ) {
      global $author;
      $mywiki_userdata = get_userdata($author);
      echo $mywiki_before . _e('Articles posted by ','mywiki'). $mywiki_userdata->display_name . $mywiki_after;
  
    } elseif ( is_404() ) {
      echo $mywiki_before . _e('Error 404 ','mywiki'). $mywiki_after;
    }
    if ( get_query_var('paged') ) {
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
      echo'paged'. ' ' . get_query_var('paged');
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
    }
    echo '</div>';
  }
} // end qt_custom_breadcrumbs()

/* ===========================================================
	T H E M E  O P T I O N S
=============================================================*/
require_once('theme-options/fasterthemes.php'); 
/**
 * Wiki search
 */
function mywiki_search() {
	global $wpdb;
	$mywiki_title=trim($_POST['queryString']);
	$mywiki_args = array('posts_per_page' => -1, 'order'=> 'ASC', "orderby"=> "title", "post_type" => "post",'post_status'=>'publish', "s" => $mywiki_title);
    $mywiki_posts = get_posts( $mywiki_args );
	$mywiki_output='';
	if($mywiki_posts){
		 $mywiki_h=0;
		 $mywiki_output.='<ul id="search-result">';
		 foreach ( $mywiki_posts as $mywiki_post ) 
		 { 
			 $mywiki_output.='<li class="que-icn">';
             $mywiki_output.='<a href="'.$mywiki_posts[$mywiki_h]->guid.'">'.$mywiki_posts[$mywiki_h]->post_title.'</a>';
             $mywiki_output.='</li>';
			 $mywiki_h++;
		 }
		 $mywiki_output.='</ul>';
		 echo $mywiki_output;
	}else{
	     $mywiki_output.='no';
        echo $mywiki_output;
	}
	die();
}
add_action('wp_ajax_mywiki_search', 'mywiki_search');
add_action('wp_ajax_nopriv_mywiki_search', 'mywiki_search' );
if ( ! function_exists( 'mywiki_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own mywiki_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Twelve 1.0
 */
function mywiki_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
		// Proceed with normal comments.
		global $post;
	$mywiki_tag = ( 'div' === $args['style'] ) ? '<div' : '<li'; ?>
		<?php echo $mywiki_tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> >
    	<article class="div-comment-<?php comment_ID(); ?>" id="div-comment-1">
				<footer class="comment-meta">
					<div class="comment-author vcard">
						<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
					<b class="fn">	<?php printf( __( '%s says:','mywiki' ), sprintf( '%s', get_comment_author_link() ) ); ?></b>
					</div><!-- .comment-author -->
					<div class="comment-metadata">
						<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
							<time datetime="<?php comment_time( 'c' ); ?>">
								<?php printf( __( '%1$s at %2$s', 'mywiki' ), get_comment_date(), get_comment_time() ); ?>
							</time>
						</a>
						<?php edit_comment_link( __( 'Edit','mywiki' ), '<span class="edit-link">', '</span>' ); ?>
          </div><!-- .comment-metadata -->
				</footer><!-- .comment-meta -->
				<div class="comment-content">
					<?php comment_text(); ?>
				</div><!-- .comment-content -->
				<div class="reply">
					<?php comment_reply_link( array_merge( $args, array( 'add_below' => 'div-comment', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
               </div><!-- .reply -->
			</article>
	<?php
}
endif;
add_action('wp_ajax_mywiki_header', 'mywiki_header_image_function');
add_action('wp_ajax_nopriv_mywiki_header', 'mywiki_header_image_function' );
function mywiki_header_image_function(){
	$mywiki_return['header'] = get_header_image();
	echo json_encode($mywiki_return);
	die;
}
/* 
Adding Read More
*/
function mywiki_trim_excerpt($mywiki_text) {
 $text = substr($mywiki_text,0,-10); 
 return $text.'..<div class="clear-fix"></div><a href="'.get_permalink().'" title="'.__('read more...','mywiki').'">'.__('Read more','mywiki').'</a>';
}
add_filter('get_the_excerpt', 'mywiki_trim_excerpt'); ?>