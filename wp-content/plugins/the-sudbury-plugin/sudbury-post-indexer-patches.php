<?php
/**
 * I have found some bugs with post indexer... specifically when doing category queries because it drops some posts but adds others.
 * This is because it doesn't take into account the BLOG_ID when doing GROUP BYs and JOINs  These filters are here to make my life easier and fix those
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Multisite
 */


function sudbury_fix_network_posts_join( $join, $network_query ) {

    // This if statement will try to detect if the post indexer bug has been fixed and prevent itself from running
    if ( ' INNER JOIN wp_network_term_relationships ON (wp_network_posts.ID = wp_network_term_relationships.object_id)' == $join && '' != $network_query->query_vars['category_name'] && false === strpos( $join, 'network_posts.BLOG_ID' ) ) {
        $join .= " AND (wp_network_posts.BLOG_ID = wp_network_term_relationships.blog_id) ";
    }

    return $join;
}

add_filter( 'network_posts_join', 'sudbury_fix_network_posts_join', 10, 2 );


function sudbury_fix_network_posts_groupby( $groupby, $network_query ) {

    // This if statement will try to detect if the post indexer bug has been fixed and prevent itself from running
    if ( 'wp_network_posts.ID' == $groupby && '' != $network_query->query_vars['category_name'] && false === strpos( $groupby, 'network_posts.BLOG_ID' ) ) {
        $groupby .= ", wp_network_posts.BLOG_ID";
    }

    return $groupby;
}

add_filter( 'network_posts_groupby', 'sudbury_fix_network_posts_groupby', 10, 2 );

function sudbury_exclude_internal_content( $where ) {


    return $where;
}

add_filter( 'network_posts_where', 'sudbury_exclude_internal_content' );
function sudbury_network_fix_term( $term, $taxonomy ) {
    global $wpdb;
    if ( sudbury_in_network_query() ) {
        $term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM wp_network_terms AS t INNER JOIN wp_network_term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '%s' AND slug = '%s' LIMIT 1", $taxonomy, $term->slug ) );
    }

    return $term;
}

add_filter( 'get_term', 'sudbury_network_fix_term', 10, 2 );


function sudbury_doing_network_query( $q ) {
    $GLOBALS['doing_network_query'] = true;

    return $q;
}

add_filter( 'network_pre_get_posts', 'sudbury_doing_network_query' );

function sudbury_done_network_query( $posts ) {
    $GLOBALS['doing_network_query'] = false;

    return $posts;

}

add_filter( 'network_the_posts', 'sudbury_done_network_query' );

function sudbury_in_network_query() {
    return ( isset( $GLOBALS['doing_network_query'] ) ? $GLOBALS['doing_network_query'] : false );
}

/**
 *
 * Gets the post meta for a specific post from the post indexer table
 *
 * @param int|object $post The ID of the Post that you want the meta for
 * @param string $key (optional) The meta key of the data you want
 * @param bool $single (optional) Whether to return a single Value
 * @param int|bool $blog_id The ID of the blog that the post is located on
 *
 * @return array|bool|mixed|string|void ... Whatever get_post_meta() returns normally given parameters above
 */
function network_get_post_meta( $post, $key = '', $single = false, $blog_id = false ) {
    global $wpdb;
    $meta_type = 'post';

    // If you didn't specify a blog ID
    if ( ! $blog_id ) {
        if ( isset( $post->BLOG_ID ) ) {
            // Well if you passed a Post resulting from network_query() then use that BLOG_ID
            $blog_id = $post->BLOG_ID;
        } else {
            // Use the Current Blog ID
            $blog_id = get_current_blog_id();
        }
    }

    // If you passed in a Post object, lets get the ID
    if ( is_object( $post ) ) {
        $post = $post->ID;
    }

    if ( ! $post = absint( $post ) ) {
        return false;
    }

    /**
     * Filter whether to retrieve metadata of a specific type.
     *
     * The dynamic portion of the hook, $meta_type, refers to the meta
     * object type (comment, post, or user). Returning a non-null value
     * will effectively short-circuit the function.
     *
     * @since 3.1.0
     *
     * @param null|array|string $value The value network_get_metadata() should
     *                                     return - a single metadata value,
     *                                     or an array of values.
     * @param int $object_id Object ID.
     * @param string $meta_key Meta key.
     * @param string|array $single Meta value, or an array of values.
     */
    $check = apply_filters( "network_get_{$meta_type}_metadata", null, $post, $key, $single );
    if ( null !== $check ) {
        if ( $single && is_array( $check ) ) {
            return $check[0];
        } else {
            return $check;
        }
    }

    // Check Cache first
    $meta = wp_cache_get( "network_meta_{$blog_id}-{$post}", $meta_type . '_meta' );

    // if nothing found in cache then go to DB
    if ( ! $meta ) {
        // Query EVERYTHING based on $blog_id and $post_id
        $query = $wpdb->prepare( "SELECT meta_id, meta_key, meta_value FROM {$wpdb->base_prefix}network_postmeta WHERE blog_id = %d AND post_id = %d" . ( $key ? ' AND meta_key = %s' : '' ), $blog_id, $post, $key );

        $meta = $wpdb->get_results( $query, ARRAY_A );

        // Now set everything in the cache for future calls (even in this request)
        wp_cache_set( "network_meta_{$blog_id}-{$post}", $meta );
    }

    // If there's nothing then get out
    if ( empty( $meta ) ) {
        if ( $single ) {
            return '';
        } else {
            return array();
        }
    }

    // You wanted Everything... here's everything
    if ( ! $key ) {
        return $meta;
    }

    // Now filter everything but $key
    $meta = array_filter( $meta, function ( $row ) use ( &$key ) {
        return $row['meta_key'] == $key;
    } );

    // Check to see if the key was included
    if ( ! empty( $meta ) ) {
        if ( $single ) {
            return maybe_unserialize( current( $meta )['meta_value'] );
        } else {
            return array_map( function ( $row ) {
                return maybe_unserialize( $row['meta_key'] );
            }, $meta );
        }
    } else {
        // Key isn't in meta... return the nothing stuff again per wp spec
        if ( $single ) {
            return '';
        } else {
            return array();
        }

    }
}

if ( ! function_exists( 'network_the_blog_id' ) ) {
    /**
     * Prints the Blog Id of the Current Network Post
     */
    function network_the_blog_id() {
        echo network_get_the_blog_id();
    }
}
if ( ! function_exists( 'network_get_the_blog_id' ) ) {
    /**
     * Returns the Blog Id of the Current Network Post
     */
    function network_get_the_blog_id() {
        global $network_post;

        return $network_post->BLOG_ID;
    }
}

/**
 * @param $attachment
 */
function sudbury_index_attachment( $attachment ) {
    global $postindexeradmin;
    _sudbury_log( 'Indexing Attachment' );
    $postindexeradmin->index_post( $attachment, get_post( $attachment ) );
}

add_action( 'edit_attachment', 'sudbury_index_attachment' );
add_action( 'add_attachment', 'sudbury_index_attachment' );
