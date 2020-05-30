<?php
/**
 * Maintains the EXACT Same wp_terms and wp_term_taxonomy tables for EACH AND EVERY SITE in the network
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Multisite
 */
/**
 * Schedules a taxonomy syncronization
 * @deprecated 4.2.0
 */
function sudbury_schedule_tax_sync() {
    return;
}


add_action( 'sudbury_global_categories_cron_sync', 'ms_taxonomy_sync' );

/**
 * performs a taxonomy syncronization that takes all terms in blog 1 and copies that table to all the subsites.
 *
 * WARNING: This function is Destructive... IT DROPS EVERY wp_#_terms and wp_#_term_taxonomy TABLE FROM THE DATABASE!
 * IT THEN CREATES A NEW TABLE WITH THE SAME NAME AS THE OLD ONE (wp_#_terms, wp_#_term_taxonomy) WHICH IS A COPY OF THE
 * BLOG 1 wp_terms and wp_term_taxonomy TABLES.   DO I NEED TO YELL ANYMORE ABOUT HOW DANGEROUS THIS COULD BE... USE
 * IT WISELY!
 * @deprecated 4.2.0
 * @return bool True on success, false on failue
 */
function ms_taxonomy_sync( $blog_id = false ) {
    return false;
}

/**
 * Restricts the ability to add a category to just the main blog
 *
 * @param $all_caps
 * @param $caps2
 * @param $args
 *
 * @return mixed
 */
function sudbury_has_categories_cap( $all_caps, $caps2, $args ) {
    global $blog_id;

    // This is seemingly unused
    // get_currentuserinfo();

    if ( $blog_id != 1 && false === strpos( $_SERVER['REQUEST_URI'], 'nav-menus.php' ) ) {
        if ( isset( $all_caps['manage_categories'] ) ) {
            unset( $all_caps['manage_categories'] );
        }
    }

//  Was for allowing editors to create and edit nav-menus but this is not a good way of allowing that specifically $args[1] > 5 is a big wat??? I think it is User level stuff but that should not be used
//  global $wp_current_filter;
//  if ( $args[1] > 5 && ( false !== strpos( $_SERVER['REQUEST_URI'], 'nav-menus.php' ) || ( 'add-menu-item' == $_POST['action'] && in_array( 'wp_ajax_add-menu-item', $wp_current_filter ) ) ) ) {
//      $all_caps['edit_theme_options'] = 1;
//  }

    return $all_caps;
}

add_filter( 'user_has_cap', 'sudbury_has_categories_cap', 10, 3 );

/**
 * Denies Super Admins the ability to edit categories if they are not on blog 1
 *
 * @param $caps
 * @param $cap
 *
 * @return array
 */
function sudbury_deny_super_admin_category_edit_rights( $caps, $cap ) {
    global $blog_id;

    // Categories can ONLY be added to the main site so even super admins can't create categories on sub-sites
    if ( is_multisite() && is_super_admin() && $blog_id != 1 && 'manage_categories' == $cap ) {
        $caps[] = 'do_not_allow';
    }

    return $caps;
}

add_filter( 'map_meta_cap', 'sudbury_deny_super_admin_category_edit_rights', 999, 2 );

/**
 * Hides the Categories menu
 */
function ms_taxonomy_hide_menus() {
    global $blog_id;

    if ( $blog_id != 1 ) {
        remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=category' );
    }
}

add_action( 'admin_menu', 'ms_taxonomy_hide_menus' );
