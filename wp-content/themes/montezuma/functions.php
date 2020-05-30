<?php 

// include all functions
foreach ( glob( get_template_directory() . "/includes/*.php") as $filename) {
    include( $filename );
}


$upload_dir = wp_upload_dir();

// 2 db queries
if( FALSE === ( $bfa_thumb_transient = get_transient( 'bfa_thumb_transient' ) ) ) {
	$bfa_thumb_transient = array();
}


// wp-content/uploads is writable and admin page was called at least once = created static css file exists:
if( is_file( $upload_dir['basedir'] . '/montezuma/style.css' ) ) {
	$bfa_css = '<link rel="stylesheet" type="text/css" media="all" href="' . $upload_dir['baseurl'] . '/montezuma/style.css" />';
// Fallback: wp-content/uploads not writable or CSS file in wp-uploads not created yet (The Montezuma admin must be visited at least once for this). 
} else {
	$bfa_css = '
/*************************************************************************
Default CSS served INLINE because wp-content/uploads is not writable.
This will change once wp-content/uploads is writable
**************************************************************************/
';
	$bfa_css .= implode( '', file( get_template_directory() . "/admin/default-templates/css/grids/resp12-px-m0px.css" ) );
	foreach ( glob( get_template_directory() . "/admin/default-templates/css/*.css") as $filename) {
		$bfa_css .= implode( '', file( $filename ) );
	}
	$bfa_css = str_replace( '%tpldir%', get_template_directory_uri(), $bfa_css );
	$bfa_css = "\n<style type='text/css'>\n" . $bfa_css . "</style>\n";
}


// Enqueuing script with IE *version* condition currently not possible http://core.trac.wordpress.org/ticket/16024
add_action( 'wp_head', 'bfa_add_inline_scripts_head' );
function bfa_add_inline_scripts_head() {
	global $is_IE; if( $is_IE ): ?>
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/javascript/html5.js" type="text/javascript"></script>
<script src="<?php echo get_template_directory_uri(); ?>/javascript/css3-mediaqueries.js" type="text/javascript"></script>
<![endif]-->
<?php endif; 
}



// JavaScript for front end
add_action('wp_enqueue_scripts', 'bfa_enqueue_scripts'); 
function bfa_enqueue_scripts() {

	global $montezuma, $upload_dir, $post;

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	
	// Check if this is a gallery page
	$is_gallery = 0;
	if( is_object( $post ) && strpos( $post->post_content,'[gallery' ) !== false ) { // check if $post is set on error page
		$is_gallery = 1;
	}
	
	$enqu_list = array( 'jquery' );

	// Load jquery-ui-core through dependencies, direct wp_enqueue_script('jquery-ui-core') may be broken
	// http://wordpress.org/support/topic/wp_enqueue_script-with-jquery-ui-and-tabs ui-core, ui-.widget and effects-core needed by smooth-menu
	$enqu_list[] = 'jquery-ui-core';
	$enqu_list[] = 'jquery-ui-widget';
	$enqu_list[] = 'jquery-effects-core';
			
	if ( is_singular() && $montezuma['comment_quicktags'] != '' ) {
		$enqu_list[] = 'quicktags';
	}
	if( $is_gallery === 1 ) {
		wp_register_script( 'colorbox', get_template_directory_uri() . '/javascript/jquery.colorbox-min.js', array( 'jquery' ) ); 
		$enqu_list[] = 'colorbox';
	}
	
	wp_register_script( 'smooth-menu', get_template_directory_uri() . '/javascript/smooth-menu.js', array( 'jquery' ) ); 
	$enqu_list[] = 'smooth-menu';

	// Premade javascript file if uploads not writable, i.e. first use or WP.org theme viewer:
	if( is_file( $upload_dir['basedir'] . '/montezuma/javascript.js' ) ) {
		$bfa_base_js_enqueue_url = $upload_dir['baseurl'] . '/montezuma/javascript.js';
	} else {
		$bfa_base_js_enqueue_url = get_template_directory_uri() . '/admin/default-templates/javascript/javascript.js';
	}
	
	wp_enqueue_script( 'montezuma-js', $bfa_base_js_enqueue_url, $enqu_list );
}    



// http://wordpress.stackexchange.com/questions/24851/wp-enqueue-inline-script-due-to-dependancies
if( ! function_exists( 'bfa_print_footer_scripts' ) ):
	function bfa_print_footer_scripts() {
		global $montezuma;
		if ( $montezuma['comment_quicktags'] != '' && wp_script_is( 'jquery', 'done' ) && is_singular() ) {
		?>
<script type="text/javascript">quicktags({ id: 'comment-form', buttons: '<?php echo $montezuma['comment_quicktags']; ?>' });</script>
		<?php
		}
	}
endif;
add_action( 'wp_footer', 'bfa_print_footer_scripts' );



function bfa_wp_title( $title, $sep ) {
	global $paged, $page;
	
	if( is_feed() ) {
		return $title;
	}

	$title .= get_bloginfo( 'name' );

	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title = "$title $sep $site_description";
	}
	
	if ( $paged >= 2 || $page >= 2 ) {
		$title = "$title $sep " . sprintf( __( 'Page %s', 'montezuma' ), max( $paged, $page ) );
	}
	
	return $title;
}
add_filter( 'wp_title', 'bfa_wp_title', 10, 2 );



// THEME OPTIONS: new ThemeOptions( $title, $id, $path ) - $path = path to directory of section files containing arrays of option fields
if( is_admin() )  {
 	new ThemeOptions( 'Montezuma Options', 'montezuma', get_template_directory() . '/admin/options' );
} 
$montezuma = get_option( 'montezuma' );


if( $montezuma['wlwmanifest_link'] != 1 ) {
	remove_action('wp_head', 'wlwmanifest_link');
}
if( $montezuma['rsd_link'] != 1 ) { 
	remove_action('wp_head', 'rsd_link');
}
if( $montezuma['wp_generator'] != 1 ) {
	remove_action('wp_head', 'wp_generator');
}
if( $montezuma['feed_links_extra'] != 1 ) {
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
if( $montezuma['feed_links'] != 1 ) { 
	remove_action( 'wp_head', 'feed_links', 2 ); 
}
if( $montezuma['adjacent_posts_rel_link_wp_head'] != 1 ) {
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
}

		
// Theme setup
if( ! function_exists( 'montezuma_setup' ) ):
function montezuma_setup() {

	if( ! isset( $content_width ) ) {
		$content_width = 640;
	}
	
	load_theme_textdomain( 'montezuma', get_template_directory() . '/languages' );

	add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	add_theme_support( "post-thumbnails" );
	// set_post_thumbnail_size( 320, 180, true );
	add_theme_support("automatic-feed-links");
	register_nav_menus( array( "menu1" => __( "Menu 1", "montezuma" ), "menu2" => __( "Menu 2", "montezuma" ) ) );
}
endif;
add_action( 'after_setup_theme', 'montezuma_setup' );



// Link post thumbs to post, not to full size image
function bfa_link_post_thumbnails_to_post( $html, $post_id, $post_image_id ) {

	$html = str_replace('width="320" height="180" ', '', $html);
	return $html;
}
add_filter( 'post_thumbnail_html', 'bfa_link_post_thumbnails_to_post', 10, 3 );



if( ! function_exists( 'bfa_comments_allowedtags' ) ) :
function bfa_comments_allowedtags( $data ) {

	global $allowedtags, $montezuma; 

	$availabletags = array(
		'a' => array( 'href' => true, 'title' => true ),
		'abbr' => array( 'title' => true ),
		'acronym' => array( 'title' => true ),
		'b' => array(),
		'blockquote' => array( 'cite' => true ),
		'br' => array(),
		'cite' => array(),
		'code' => array(),
		'del' => array( 'datetime' => true ),
		'dd' => array(),
		'dl' => array(),
		'dt' => array(),
		'em' => array (), 'i' => array (),
		'ins' => array('datetime' => array(), 'cite' => array()),
		'li' => array(),
		'ol' => array(),
		'p' => array(),
		'q' => array( 'cite' => true ),
		'strike' => array(),
		'strong' => array(),
		'sub' => array(),
		'sup' => array(),
		'u' => array(),
		'ul' => array(),
	);
	$allowednow = array();
	
	foreach( $montezuma['comment_allowed_tags'] as $tag ) {
		$allowednow[$tag] = $availabletags[$tag];
	}
	
	$allowedtags = $allowednow;
	return $data;
}
endif;
add_filter( 'preprocess_comment', 'bfa_comments_allowedtags' );



// filter tagcloud 
if( ! function_exists( 'bfa_filter_tag_cloud' ) ) :
function bfa_filter_tag_cloud( $tags ) {
	$tags = preg_replace_callback("|(class='tag-link-[0-9]+)('.*?)(style='font-size: )(.*?)(pt;')|",
		create_function(
			'$match',
			'$low=1; $high=5; $sz=round(($match[4]-8.0)/(22-8)*($high-$low)+$low); return "{$match[1]} tagsize-{$sz}{$match[2]}";'
		),
		$tags);
	return $tags;
}
endif;
add_action('wp_tag_cloud', 'bfa_filter_tag_cloud');



// Change default Excerpt Length to custom length:
function bfa_excerpt_length( $length ) { 
	return 55;
}
add_filter( 'excerpt_length', 'bfa_excerpt_length' );



// Build custom Read More link, used for both auto and manual excerpts
function bfa_read_more_link() {
	return str_replace( 
		array( '%title%', '%url%' ), 
		array( the_title( '', '', FALSE ), esc_url( get_permalink() ) ), 
		' ...<a class="post-readmore" href="%url%">' . __( 'read more', 'montezuma' ) . '</a>' 
	);
}



// Replace default Read More link with custom one:
function bfa_excerpt_more( $more ) {
	return bfa_read_more_link();
}
add_filter( 'excerpt_more', 'bfa_excerpt_more' );



// Add custom Read More link to manual excerpts:
function bfa_custom_excerpt_more( $output ) {
	if( has_excerpt() && ! is_attachment() ) {
		$output .= bfa_read_more_link();
	}
	return $output;
}
add_filter( 'get_the_excerpt', 'bfa_custom_excerpt_more' );



function bfa_include_file( $file_group, $file_name ) {

	global $montezumafilecheck, $upload_dir;
	
	$time_start = microtime(true); // Start timer
	$file = trailingslashit( $upload_dir['basedir'] ) . "montezuma/$file_name.php";

	if( ! file_exists( $file ) ) { // Edited file doesn't exist
		include trailingslashit( get_template_directory() ) . "$file_group/$file_name.php";
	} else {
		extract( $montezumafilecheck['files'][$file_group][$file_name] ); // Get file info: $time, $size, $md5:
		
		// Edited file exists. These checks should take around 5 ms on an average web server:
		$filetime = filemtime( $file );
		$filesize = filesize( $file );
		$filemd5 = md5_file( $file );

		// Include file only if live info matches with saved info:
		if( $time == $filetime && $size == $filesize && $filemd5 == $md5 ) {
			include trailingslashit( $upload_dir['basedir'] ) . "montezuma/$file_name.php";
		}

		$time_end = microtime(true); // Stop timer
		$time = $time_end - $time_start;
		echo "<!-- Rendered in $time seconds -->\n";
	}
}

add_filter('upload_mimes', 'custom_upload_mimes');
function custom_upload_mimes ( $existing_mimes=array() ) {
  
// adding 'css' and 'js' to supports Montezuma in a multisite environment
$existing_mimes['css'] = 'css file'; 
$existing_mimes['js'] = 'jscript file'; 
 
// and return the new full result
return $existing_mimes;
}

