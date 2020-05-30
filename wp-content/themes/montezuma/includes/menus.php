<?php 

function bfa_cat_menu($args){

	$menu = '';
	$args['echo'] = false;
	$args['title_li'] = '';

	if( $args['container'] ) {
		$menu = '<'. $args['container'];			
		if( $args['container_id'] ) {
			$menu .= ' id="' . $args['container_id'] . '"';
		}
		if( $args['container_class'] ) {
			$menu .= ' class="' . $args['container_class'] . '"';
		}	
		$menu .= ">\n";
	}

	$menu .= '<ul id="' . $args['menu_id'] . '" class="' . $args['menu_class'] . '">';
	$menu .= str_replace( "<ul class='children'>", '<ul class="sub-menu">', wp_list_categories( $args ) );
	$menu .= '</ul>';

	if( $args['container'] ) {
		$menu .= '</' . $args['container'] . ">\n";
	}
	echo $menu;
}



function bfa_page_menu($args){

	$menu = '';
	$args['echo'] = false;
	$args['title_li'] = '';

	// If the front page is a page, add it to the exclude list
	if( get_option( 'show_on_front' ) == 'page' ) {
		$args['exclude'] = get_option( 'page_on_front' );
	}
	
	if( $args['container'] ) {
		$menu = '<'. $args['container'];		
		if( $args['container_id'] ) {
			$menu .= ' id="' . $args['container_id'] . '"';
		}
		if( $args['container_class'] ) {
			$menu .= ' class="' . $args['container_class'] . '"';
		}
		$menu .= ">\n";
	}

	$menu .= '<ul id="' . $args['menu_id'] . '" class="' . $args['menu_class'] . '">';
	$menu .= str_replace( "<ul class='children'>", '<ul class="sub-menu">', wp_list_pages( $args ) );
	$menu .= '</ul>';

	if( $args['container'] ) {
		$menu .= '</' . $args['container'] . ">\n";
	}
	echo $menu;
}



function bfa_simplify_wp_list_categories($output) {

	$output = preg_replace_callback(
		'/class="cat-item cat-item-(\d+)( current-cat)?(-parent)?"/',
		create_function(
			'$matches',
			'if( isset($matches[2]) && isset($matches[3]) ) $extra = " parent";
			elseif( isset($matches[2]) ) $extra = " active";
			else $extra = "";
			$cat = get_category( $matches[1] ); return "class=\"cat-" . $cat->slug . $extra . "\"";'
		),
		$output
	);
	return $output;
}
add_filter('wp_list_categories', 'bfa_simplify_wp_list_categories');
add_filter('the_category', 'bfa_simplify_wp_list_categories');



function bfa_simplify_wp_nav_menu( $classes, $item ) {
	
	$item_type = 'item';
	$new_classes = array();

	foreach( $classes as $class ) {
		if( $class == 'menu-item-object-category' ) {
			$item_type = 'cat';
		} elseif( $class == 'menu-item-object-page' ) {
			$item_type = 'page';
			
		} elseif( $class == 'current-menu-item' ) {
			$new_classes[] = 'active';
		} elseif( $class == 'current-menu-parent' ) { 
			$new_classes[] = 'parent';
		} elseif( $class == 'current-menu-ancestor' ) { 
			$new_classes[] = 'ancestor';
		}
	}
	
	// static homepage returns '' with basename( get_permalink( $item->object_id ) ) from below
	if( trailingslashit( get_permalink( $item->object_id ) ) == trailingslashit( home_url() ) 
			&& get_option( 'show_on_front' ) == 'page' ) { 
			
		$homepage_id = get_option( 'page_on_front' );
		$thispage = get_post( $homepage_id ); 
		$slug = $thispage->post_name;
		$new_classes[] = $item_type . '-' . $slug;
	} else {
		if( $item_type == 'cat' ) {
			$slug = esc_attr( basename( get_category_link( $item->object_id ) ) );
		} else { 
			$slug = esc_attr( basename( get_permalink( $item->object_id ) ) );
		}
		$new_classes[] = $item_type . '-' . $slug;
	}
	return $new_classes;
}
add_filter( 'nav_menu_css_class', 'bfa_simplify_wp_nav_menu', 100, 2 );



function bfa_strip_wp_nav_menu_ids( $menu ) {
    $menu = preg_replace( '/\<li id="(.*?)"/','<li', $menu );
    return $menu;
}
add_filter ( 'wp_nav_menu', 'bfa_strip_wp_nav_menu_ids' );



function bfa_simplify_wp_list_pages( $classes, $page ) {

	$new_classes = array( 'page-' . $page->post_name );
	foreach( $classes as $class ) {
		if( $class == 'current_page_item' ) {
			$new_classes[] = 'active';
		} elseif( $class == 'current_page_parent' ) { 
			$new_classes[] = 'parent';
		} elseif( $class == 'current_page_ancestor' ) { 
			$new_classes[] = 'ancestor';
		}
	}
	return $new_classes;
}
add_filter( 'page_css_class', 'bfa_simplify_wp_list_pages', 100, 2 );



