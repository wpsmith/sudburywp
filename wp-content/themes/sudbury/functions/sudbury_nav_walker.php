<?php


class BS_4_Nav_Walker extends Walker_Nav_menu {

	function start_lvl( &$output, $depth = 0, $args = array() ) { // ul
		$indent  = str_repeat( "\t", $depth ); // indents the outputted HTML
		$submenu = ( $depth > 0 ) ? ' sub-menu' : '';
		$output  .= "\n$indent<ul class=\"dropdown-menu$submenu depth_$depth\">\n";
	}

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) { // li a span
		$args   = (object) $args;
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$li_attributes = '';
		$class_names   = $value = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		$classes[] = ( $args->walker->has_children ) ? 'dropdown' : '';
		$classes[] = ( $item->current || $item->current_item_anchestor ) ? 'active' : '';
		$classes[] = 'nav-item';
		$classes[] = 'nav-item-' . $item->ID;
		if ( $depth && $args->walker->has_children ) {
			$classes[] = 'dropdown-menu';
		}

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = ' class="' . esc_attr( $class_names ) . '"';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args );
		$id = strlen( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li ' . $id . $value . $class_names . $li_attributes . '>';

		$attributes = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
		$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
		$attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $item->url ) . '"' : '';

		$attributes .= ( $args->walker->has_children ) ? ' class="nav-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"' : ' class="nav-link"';

		$item_output = $args->before;
		$item_output .= ( $depth > 0 ) ? '<a class="dropdown-item"' . $attributes . '>' : '<a' . $attributes . '>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );

	}

}


class BS_4_Nav_Walker_Main extends Walker_Nav_menu {

	function start_lvl( &$output, $depth = 0, $args = array() ) { // ul
		$indent  = str_repeat( "\t", $depth ); // indents the outputted HTML
		$submenu = ( $depth > 0 ) ? ' sub-menu' : '';
		$output  .= "\n$indent<ul class=\"sub-menu depth_$depth\">\n";
	}

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) { // li a span
		$args = (object) $args;

		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$li_attributes = '';
		$class_names   = $value = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		$classes[] = ( $args->walker->has_children ) ? '' : '';
		$classes[] = ( $item->current || $item->current_item_anchestor ) ? 'active' : '';
		$classes[] = 'nav-item';
		$classes[] = 'nav-item-' . $item->ID;
		if ( $depth && $args->walker->has_children ) {
			$classes[] = 'sub-menu';
		}

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = ' class="' . esc_attr( $class_names ) . '"';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args );
		$id = strlen( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li ' . $id . $value . $class_names . $li_attributes . '>';

		$attributes = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
		$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
		$attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $item->url ) . '"' : '';

		$attributes .= ( $args->walker->has_children ) ? ' class="nav-link" aria-haspopup="true" aria-expanded="false"' : ' class="nav-link"';

		$item_output = $args->before;
		$item_output .= ( $depth > 0 ) ? '<a class=""' . $attributes . '>' : '<a' . $attributes . '>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );

	}

}


/**
 * Sudbury Nav Walker for Primary Menu.  Adds custom menu functionality for certain dropdowns such as contact and search
 *
 */
class Sudbury_Nav_Walker extends BS_4_Nav_Walker_Main {


	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		if ( $item->attr_title && wp_get_nav_menu_object( 'Replace-' . $item->attr_title ) ) {

			$_args = (array) $args;

			$_args['menu'] = 'Replace-' . $item->title;

			$_args['items_wrap'] = '%3$s';
			ob_start();
			$_args['container'] = false;

			wp_nav_menu( $_args );
			$out    = ob_get_clean();
			$output .= $out;
		} else {
			parent::start_el( $output, $item, $depth, $args, $id );
		}
	}
}
