<?php

function nbt_load_api() {
	$plugin_dir = plugin_dir_path( __FILE__ );
	require_once( $plugin_dir . '/blogtemplatesfiles/model.php' );
	require_once( $plugin_dir . '/blogtemplatesfiles/helpers.php' );
	require_once( $plugin_dir . '/blogtemplatesfiles/copier.php' );
	require_once( $plugin_dir . '/blogtemplatesfiles/settings-handler.php' );
}

/**
 * Copy content from one blog to another without needing any template
 * 
 * @param Integer $source_blog_id the blog ID where the source content is
 * @param Integer $new_blog_id the destination blog ID
 * @param Integer $new_user_id the user ID that was associated to the new blog when it was created
 * @param Array $args Default values: 
  		array(
			'to_copy' => array(
				'settings' 	=> false, => Copy settings?
				'posts'		=> false, => Copy posts?
				'pages'		=> false, => Copy pages?
				'terms'		=> false, => Copy categories, tags...?
				'users'		=> false, => Copy users?
				'menus'		=> false, => Copy menus?
				'files'		=> false  => Copy files?
			),
			'pages_ids'		=> array( 'all-pages' ), IDs of the pages you want to copy ([to_copy][pages] must be set to true)
			'post_category' => array( 'all-categories' ), Categories of the posts you want to copy ([to_copy][posts] must be set to true)
			'template_id'	=> 0, If you are saving templates in the NBT tables you can add here the template ID but is not needed. Will dissapear in following releases
			'additional_tables' => array(), Tables you want to copy
            'block_posts_pages' => false, Block posts/pages for edition?
            'update_dates' => false Update the dates of the posts/pages copied?
		)
 * 
 * @return type
 */
function nbt_api_copy_contents_to_new_blog( $source_blog_id, $new_blog_id, $new_user_id, $args ) {
	nbt_load_api();
	$copier = new NBT_Template_copier( $source_blog_id, $new_blog_id, $new_user_id, $args );
	$copier->execute();
}


/**
 * Sample code
 * 
 * This code will copy all posts, pages and menus in the source blog. It also updates
 * the dates of the posts and pages
 * 
include_once( WP_CONTENT_DIR . '/plugins/blogtemplates/nbt-api.php' );
nbt_load_api();

add_action( 'wpmu_new_blog', 'my_test_function', 99, 2 );
function my_test_function( $new_blog_id, $new_user_id ) {
	$args = array(
			'to_copy' => array(
				'posts'		=> true,
				'pages'		=> true,
				'menus'		=> true,
			),
            'update_dates' => true
		);
	nbt_api_copy_contents_to_new_blog( 27, $new_blog_id, $new_user_id, $args );
}
*/