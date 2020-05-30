<?php
/*
Plugin Name: Sudbury Hidden Categories
Plugin URI: http://sudbury.ma.us/
Description: Adds sharing capabilities to documents
Version: 1.0
Author: Eddie Hurtig
Author URI: http://hurtigtechnologies.com
Network: True
*/

/**
 * Class Sudbury_Hidden_Categories
 */
class Sudbury_Hidden_Categories {

    /**
     * Constructor
     */
    function __construct() {
        add_action('admin_init', array(&$this, 'init'));
    }

    /**
     * Initialize the plugin
     */
    function init() {
        add_filter("tag_row_actions", array(&$this, 'actions_row'), 10, 2);
        add_action("wp_ajax_hidden_categories_save", array(&$this, 'admin_post'));
        add_action('pre_delete_term', array(&$this, 'delete_term'), 10, 2);
        add_filter('term_name', array(&$this, 'term_status'), 31, 2);
    }

    /**
     * Register the action links to hide and unhide a category
     * @param $actions
     * @param $tag
     * @return mixed
     */
    function actions_row($actions, $tag)
    {
        if ($this->is_hidden($tag->term_id, $tag->taxonomy)) {
            $actions['unhide'] = '<a href="admin-ajax.php?action=hidden_categories_save&hide=false&category=' . urlencode($tag->term_id) . '&taxonomy=' . urlencode($tag->taxonomy) . '">Unhide</a>';
        } else {
            $actions['hide'] = '<a href="admin-ajax.php?action=hidden_categories_save&hide=true&category=' . urlencode($tag->term_id) . '&taxonomy=' . urlencode($tag->taxonomy) . '">Hide</a>';
        }
        return $actions;
    }

    function term_status($name, $term) {
        if ( ! $term ) { return $name; }
        if ( $this->is_hidden( $term->term_id, $term->taxonomy ) ) {
            $name .= '</a> <b>&#8212; Hidden</b><a href="#">';
        }
        return $name;
    }

    function delete_term($term, $tax) {
        $this->unhide_category($term->term_id, $tax);
    }

    function admin_post() {
        if (!is_super_admin()) {
            wp_die('Not Allowed to Hide/Unhide categories');
        }
        if (isset($_REQUEST['hide'])) {
            if ('true' == $_REQUEST['hide']) {
                $this->hide_category($_REQUEST['category'], $_REQUEST['taxonomy']);
            } elseif ('false' == $_REQUEST['hide']) {
                $this->unhide_category($_REQUEST['category'], $_REQUEST['taxonomy']);
            }
        }

        wp_redirect(wp_get_referer());
        exit();
    }

    function hide_category($id, $taxonomy = 'category') {
        $terms = get_site_option("hidden_categories_hide_{$taxonomy}", array());

        if (!in_array($id, $terms)) {
            $terms[] = $id;
            update_site_option("hidden_categories_hide_{$taxonomy}", $terms);
        }
    }

    function unhide_category($id, $taxonomy = 'category') {
        $terms = get_site_option("hidden_categories_hide_{$taxonomy}", array());

        if (false !==  ($index = array_search($id, $terms))) {
            unset($terms[$index]);
            update_site_option("hidden_categories_hide_{$taxonomy}", $terms);
        }
    }

    function is_hidden($id, $taxonomy = 'category') {
        $terms = get_site_option("hidden_categories_hide_{$taxonomy}", array());
        return in_array($id, $terms);
    }
}


function is_category_hidden($id, $taxonomy = 'category') {
    return $GLOBALS['Sudbury_Hidden_Categories']->is_hidden($id, $taxonomy);
}

$GLOBALS['Sudbury_Hidden_Categories'] = new Sudbury_Hidden_Categories();
