<?php

// Creates a custom walker menu

class MDLWP_Nav_Walker_top_menu extends Walker_Nav_Menu {

	private $curItem;

	function start_lvl( &$output, $depth, $args = array() ) {

		$item = $this->curItem;
		$indent = ( $depth > 0  ? str_repeat( "\t", $depth ) : '' ); // code indent

		$display_depth = ( $depth + 1); // because it counts the first submenu as 0
		$classes = array(
				'mdl-menu','mdl-js-menu','mdl-js-ripple-effect',
				( $display_depth % 2  ? 'menu-odd' : 'menu-even' ),
				( $display_depth >=2 ? 'sub-sub-menu' : '' ),
				'menu-depth-' . $display_depth
		);
		$class_names = implode( ' ', $classes );
		/*var_dump($args);*/

		$output .= "\n" . $indent . '<ul class="' . $class_names . '" for="sub_menu_' . $args->theme_location . '_' . $item->ID . '">' . "\n";

	}

	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
    $output .= "$indent</ul>\n";
	}

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$this->curItem = $item;

		$indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); // code indent

		$class_names = $value = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );

		if ( in_array( 'current-menu-item', $classes ) )
				$class_names .= ' is-active';

		if ($depth == 0) {
			$class_names = $class_names ? ' class="mdl-navigation__link ' . esc_attr( $class_names ) . '"' : '';
		}

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '';

		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';

		// change links for sectional pages
		// https://wordpress.stackexchange.com/a/13984/31634
		// check if page has parent
		// https://codex.wordpress.org/Conditional_Tags#A_PAGE_Page

		$post_id = get_post_meta( $item->ID, '_menu_item_object_id', true ); //get post id from the menu item id
		$post_item = get_post( $post_id ); //get post by id
		$page_templates = array("page-sectional.php", "page-sectional-navi.php", "page-tabbed.php");

		if ( in_array( get_post_meta( $post_item->post_parent, '_wp_page_template', true ), $page_templates ) && !$args->walker->has_children ) { // check if current item is child of sectional page item
				$parent_item = get_post( $post_item->post_parent );
				$attributes .= ' href="' . get_permalink( $parent_item->ID ) . '#' . $post_item->post_name . '"';
		} elseif ( in_array( get_post_meta( $post_item->ID, '_wp_page_template', true ), $page_templates ) && !$args->walker->has_children ) { // check if current item is menu item of sectional page
				$attributes .= ' href="' . get_permalink( $post_item->ID ) . '#content"';
		} elseif ( $args->walker->has_children ) { // check if current item is menu item of sectional page
				$attributes .= ' href="#"';
		} else { //DEFAULT:
				$attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $item->url        ) . '"' : '';
		}

		$attributes .= $class_names;

		$item_output = $args->before;

		if ($args->walker->has_children && $depth == 0) {
			$item_output .= '<a'. $attributes .' id="sub_menu_' . $args->theme_location . '_' . $item->ID . '">';
		} elseif ($depth > 0) {
			$item_output .= '<li class="mdl-menu__item"><a'. $attributes .'>';
		} else {
			$item_output .= '<a'. $attributes .'>';
		}

		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;

		if ($args->walker->has_children && $depth == 0) {
			$item_output .= ' <i class="material-icons">arrow_drop_down</i></a>';
		} elseif ($args->walker->has_children && $depth > 0) {
			$item_output .= ' <i class="material-icons">arrow_drop_down</i></a>';
		} elseif ($depth > 0) {
			$item_output .= '</li></a>';
		} else {
			$item_output .= '</a>';
		}

		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}


	function end_el( &$output, $item, $depth = 0, $args = array() ) {
		$output .= "\n";
	}
}
