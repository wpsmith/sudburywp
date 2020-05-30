<?php
/*
Plugin Name: Post Expiration
Plugin URI: http://sudbury.ma.us/
Description: Adds a post Expire section to the Wordpress Publish Metabox because Post Expirator doesn't do that
Version: 1.0
Author: Eddie Hurtig
Author URI: http://hurtigtechnologies.com
Network: True
*/

if ( ! defined( 'SECONDS_IN_DAY' ) ) {
    define( 'SECONDS_IN_DAY', 60 * 60 * 24 );
}

/**
 * Class Post_Expiration
 *
 * Adds the ability to expire posts to various post stati such as draft, private, or trash
 */
class Post_Expiration {
    /**
     * @var array The List of Post Types to allow to Expire
     */
    private $post_types = array( 'post', 'faq', 'attachment' );
    /**
     * @var array The List of Stati to expire to
     */
    private $expire_types = array( 'draft', 'public-archive', 'private', 'trash' );

    /**
     * The Default Constructor
     */
    function __construct() {
        add_action( 'post_expiration_cron', array( &$this, 'do_cron' ) );
        add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'init', array( &$this, 'set_up_schedule' ) );
    }

    /**
     * Fires on Activation of the Plugin
     */
    function set_up_schedule() {
		if ( ! wp_next_scheduled( 'post_expiration_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'post_expiration_cron' );
		}
    }

    /**
     * Fires on Deactivation of the plugin
     */
    function deactivate() {
        $timestamp = wp_next_scheduled( 'post_expiration_cron' );
        wp_unschedule_event( $timestamp, 'post_expiration_cron' );

    }

    /**
     * Init Function
     */
    function admin_init() {
        add_action( 'post_submitbox_misc_actions', array( &$this, 'add_to_publish_box' ) );
        add_action( 'save_post', array( &$this, 'save_post_meta' ) );
        add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
        foreach ( $this->post_types as $post_type ) {
            add_action( "publish_$post_type", array( &$this, 'save_post_meta' ) );
        }

    }

    /**
     * Enqueues the styles and scripts
     */
    function admin_enqueue_scripts() {
        wp_register_script( 'post-expiration-admin', plugins_url( 'post-expiration-admin.js', __FILE__ ) );
        wp_enqueue_script( 'post-expiration-admin' );


        wp_register_style( 'post-expiration-admin', plugins_url( 'post-expiration-admin.css', __FILE__ ) );
        wp_enqueue_style( 'post-expiration-admin' );
    }

    /**
     * The Cron Function
     */
    function do_cron() {
        global $wpdb, $postindexeradmin;

        sudbury_log( 'Post Expiration Cron activated' );
        if ( is_multisite() ) {
            $blogs = get_sites( array( 'limit' => null, 'number' => 0, 'count' => false ) );
            foreach ( $blogs as $_blog ) {
                $blog = get_site( $_blog )->to_array();
		switch_to_blog( $blog['blog_id'] );
                $posts_to_expire = get_posts( array(
                    'post_type'      => $this->post_types,
                    'posts_per_page' => - 1,
                    'meta_query'     => array(
                        array(
                            'key'     => '_post-expiration-enabled',
                            'value'   => 1,
                            'compare' => '='
                        ),
                        array(
                            'key'     => '_post-expiration-timestamp',
                            'value'   => current_time( 'timestamp' ),
                            'type'    => 'numeric',
                            'compare' => '<'
                        )

                    )

                ) );

                if ( ! empty( $posts_to_expire ) ) {
                    sudbury_log( 'Posts to Expire for Blog ' . $blog['blog_id'] . ' = ' . implode( ', ', array_map( function ( $post ) {
                            return $post->post_title;
                        }, $posts_to_expire ) ), array( 'echo' => false ) );
                }

                foreach ( $posts_to_expire as $post ) {
                    $status = get_post_meta( $post->ID, '_post-expiration-status', true );
                    if ( ! in_array( $status, $this->expire_types ) ) {
                        sudbury_log( 'Could not expire post ' . $post->ID . ' on blog ' . $blog->ID . ' because ' . $status . ' is not a valid post status' );
                        continue;
                    }


                    $wpdb->update( $wpdb->posts, array( 'post_status' => $status ), array( 'ID' => $post->ID ) );

                    clean_post_cache( $post->ID );

                    $old_status = $post->post_status;
                    sudbury_log( 'Updating post ' . $post->ID . ' on blog ' . $blog['blog_id'] . ' to ' . $status );

                    $post->post_status = $status;
                    $post->BLOG_ID     = $blog['blog_id'];
                    wp_transition_post_status( $status, $old_status, $post );

                    if ( isset( $postindexeradmin ) ) {
                        sudbury_log( 'Indexing Post with Post Indexer, please wait' );

                        $postindexeradmin->index_post( $post->ID, $post );
                    }
                }
                $posts_to_publish = get_posts(array(
                    'post_type' => 'any',
                    'post_status' => 'future',
                    'date_query' => array(
                        'before' => 'now',
                        'inclusive' => true
                    )
                ));
                foreach ( $posts_to_publish as $post ) { 
                    check_and_publish_future_post( $post->ID );
                }
                restore_current_blog();
            }

        } else {
            // Non Multisite Not Supported
        }


        sudbury_log( 'Done with Post Expiration Cron Job' );
    }

    /**
     * Adds the Expire On Field to the Publish Box for the configured post types
     */
    function add_to_publish_box() {
        global $post;

        if ( ! in_array( $post->post_type, $this->post_types ) ) {
            return;
        }

        $expire_enabled = get_post_meta( $post->ID, '_post-expiration-enabled', true );
        if ( '' == $expire_enabled ) {
            $expire_enabled = true;
        }
        $expire_timestamp = get_post_meta( $post->ID, '_post-expiration-timestamp', true );
        if ( '' == $expire_timestamp ) {
            $expire_timestamp = floor( ( current_time( 'timestamp' ) + SECONDS_IN_DAY * 15 ) / SECONDS_IN_DAY ) * SECONDS_IN_DAY;
        }
        $expire_status = get_post_meta( $post->ID, '_post-expiration-status', true );

        $datef = __( 'M j, Y @ G:i' );
        if ( 0 != $post->ID && $expire_enabled ) {

            if ( current_time( 'timestamp' ) < $expire_timestamp ) {
                $stamp = __( 'Expire on <b>%1$s </b> ' );
            } else {
                $stamp = __( 'Expired on <b>%1$s </b> ' );
            }
        } else {
            $stamp            = __( 'Expire: <b>never</b> ' );
            $expire_timestamp = apply_filters( 'post_expiration_default_expire_time', mktime( 0, 0, 0 ) + 24 * 60 * 60, $post );
        }

        $parts = array(
            'mm' => date( 'm', $expire_timestamp ),
            'jj' => date( 'j', $expire_timestamp ),
            'aa' => date( 'Y', $expire_timestamp ),
            'hh' => date( 'G', $expire_timestamp ),
            'mn' => date( 'i', $expire_timestamp ),
        );

        $expire_date = date( $datef, $expire_timestamp )
        ?>

        <div class="misc-pub-section curtime misc-pub-curtime">
            <?php wp_nonce_field( 'post-expiration-save-meta', 'post_expiration_nonce' ); ?>
            <input type="hidden" name="post_expiration_proccess" value="1">

            <span id="timestamp_expire"> <?php printf( $stamp, $expire_date ); ?></span>
            <a href="#edit_timestamp_expire" class="edit-timestamp hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span>
                <span class="screen-reader-text"><?php _e( 'Edit date and time' ); ?></span></a>

            <div id="timestampdiv_expire" class="">
                <p style="margin-bottom: 10px;">
                    <input type="checkbox" name="enable_expire" id="enable_expire" <?php checked( $expire_enabled ); ?> />
                    <label for="enable_expire"><?php _e( 'This Post Expires', 'post_expiration' ); ?> </label>
                </p>

                <div class="timestamp-wrap post-expiration-timestamp">
                    <select id="mm_expire" name="mm_expire" <?php disabled( ! $expire_enabled ); ?>>
                        <option value="01" <?php selected( '01' == $parts['mm'] ); ?>>01-Jan</option>
                        <option value="02" <?php selected( '02' == $parts['mm'] ); ?>>02-Feb</option>
                        <option value="03" <?php selected( '03' == $parts['mm'] ); ?>>03-Mar</option>
                        <option value="04" <?php selected( '04' == $parts['mm'] ); ?>>04-Apr</option>
                        <option value="05" <?php selected( '05' == $parts['mm'] ); ?>>05-May</option>
                        <option value="06" <?php selected( '06' == $parts['mm'] ); ?>>06-Jun</option>
                        <option value="07" <?php selected( '07' == $parts['mm'] ); ?>>07-Jul</option>
                        <option value="08" <?php selected( '08' == $parts['mm'] ); ?>>08-Aug</option>
                        <option value="09" <?php selected( '09' == $parts['mm'] ); ?>>09-Sep</option>
                        <option value="10" <?php selected( '10' == $parts['mm'] ); ?>>10-Oct</option>
                        <option value="11" <?php selected( '11' == $parts['mm'] ); ?>>11-Nov</option>
                        <option value="12" <?php selected( '12' == $parts['mm'] ); ?>>12-Dec</option>
                    </select>
                    <input type="text" id="jj_expire" name="jj_expire" value="<?php echo $parts['jj']; ?>" size="2" maxlength="2" autocomplete="off" <?php disabled( ! $expire_enabled ); ?>>
                    ,
                    <input type="text" id="aa_expire" name="aa_expire" value="<?php echo $parts['aa']; ?>" size="4" maxlength="4" autocomplete="off" <?php disabled( ! $expire_enabled ); ?>>
                    @
                    <input type="text" id="hh_expire" name="hh_expire" value="<?php echo $parts['hh']; ?>" size="2" maxlength="2" autocomplete="off" <?php disabled( ! $expire_enabled ); ?>>
                    :
                    <input type="text" id="mn_expire" name="mn_expire" value="<?php echo $parts['mn']; ?>" size="2" maxlength="2" autocomplete="off" <?php disabled( ! $expire_enabled ); ?>>
                    <div class="clear" style="margin-bottom: 10px;"></div>
                    <label for="expire_status">
                        How to Expire:
                        <select id="expire_status" name="expire_status">
                            <option value="public-archive" <?php selected( 'public-archive' == $expire_status ); ?>>Move to Public Archive</option>
                            <option value="private" <?php selected( 'private' == $expire_status ); ?>>Move to Private Archive</option>
                            <option value="draft" <?php selected( 'draft' == $expire_status ); ?>>Make it a Draft</option>
                            <option value="trash" <?php selected( 'trash' == $expire_status ); ?>>Move to Trash</option>
                        </select>
                    </label>
                    
                    <input type="hidden" id="hidden_mm_expire" name="hidden_mm_expire" value="<?php echo $parts['mm']; ?>" />
                    <input type="hidden" id="hidden_jj_expire" name="hidden_jj_expire" value="<?php echo $parts['jj']; ?>" />
                    <input type="hidden" id="hidden_aa_expire" name="hidden_aa_expire" value="<?php echo $parts['aa']; ?>" />
                    <input type="hidden" id="hidden_hh_expire" name="hidden_hh_expire" value="<?php echo $parts['hh']; ?>" />
                    <input type="hidden" id="hidden_mn_expire" name="hidden_mn_expire" value="<?php echo $parts['mn']; ?>" />
                    <p>
                        <a href="#edit_timestamp_expire" class="save-timestamp hide-if-no-js button">OK</a>
                        <a href="#edit_timestamp_expire" class="cancel-timestamp hide-if-no-js button-cancel">Cancel</a>
                    </p>
                </div>

            </div>
        </div>


    <?php
    }

    /**
     * Saves the Expire Data to this post's metadata
     *
     * @param int $post_id The Post ID that is currently being saved
     */
    function save_post_meta( $post_id ) {
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Modifying the expire date can be the same as deleting it in some cases so we require delete_post cap
        if ( ! current_user_can( 'delete_posts' ) ) {
            return;
        }

        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }
        if ( in_array( get_post_type( $post_id ), $this->post_types ) && isset( $_REQUEST['post_expiration_proccess'] ) ) {

            check_admin_referer( 'post-expiration-save-meta', 'post_expiration_nonce' );

            if ( isset( $_REQUEST['enable_expire'] ) ) {

                $year   = $_REQUEST['aa_expire'];
                $month  = $_REQUEST['mm_expire'];
                $day    = $_REQUEST['jj_expire'];
                $hour   = $_REQUEST['hh_expire'];
                $minute = $_REQUEST['mn_expire'];

                $timestamp = mysql2date( 'G', "$year-$month-$day $hour:$minute:00" ); // + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

                $status = $_REQUEST['expire_status'];

                if ( ! in_array( $status, array( 'public-archive', 'private', 'trash', 'draft' ) ) ) {
                    wp_die( "$status is not a supported way to expire" );
                }

                update_post_meta( $post_id, '_post-expiration-enabled', 1 );

                update_post_meta( $post_id, '_post-expiration-timestamp', $timestamp );
                update_post_meta( $post_id, '_post-expiration-status', $status );

            } else {

                // Post Expiration is not enabled... so set meta field for
                update_post_meta( $post_id, '_post-expiration-enabled', 0 );
            }
        }
    }
}

// KickStarts The Plugin
if ( class_exists( 'Post_Expiration' ) ) {
    $post_expiration = new Post_Expiration();
    register_deactivation_hook( __FILE__, array( $post_expiration, 'deactivate' ) );
}

