<?php
/**
 * Assign a user to many blogs in bulk
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Multisite
 */

/**
 * Registers the Network User Manager Page
 */
function sudbury_register_network_user_manager_page() {
    global $sudbury_network_user_manager_hook;
    $sudbury_network_user_manager_hook = add_submenu_page( 'users.php', __( 'Assign Users', 'sudbury' ), __( 'Assign Users', 'sudbury' ), 'manage_network', 'sudbury-network-user-manager', 'sudbury_network_user_manager' );

    // Hook for contextual help
    add_action( 'load-' . $sudbury_network_user_manager_hook, 'sudbury_network_user_manager_help_tab' );
}

add_action( 'network_admin_menu', 'sudbury_register_network_user_manager_page', 20 );

/**
 * Adds an 'assign to sites' link in the User Row of the MS User List Table
 *
 * @param array   $actions The existing Action Links
 * @param WP_User $user    The User in this Row
 *
 * @return array The New List of Action Links
 */
function sudbury_network_user_manager_list_row( $actions, $user ) {
    if ( current_user_can( 'manage_network' ) ) {
        $actions['sudbury_user_assign'] = sprintf( '<a href="%s" title="Bulk assign this user to sites">Assign to Sites</a>', network_admin_url( 'users.php?page=sudbury-network-user-manager&user=' . $user->ID ) );
    }

    return $actions;
}

add_action( 'ms_user_row_actions', 'sudbury_network_user_manager_list_row', 10, 2 );

/**
 * Admin Notices for the Network User Manager
 * Alerts you when you are editing a super admin
 */
function  sudbury_network_user_manager_notices() {
    global $current_screen;

    if ( 'users_page_sudbury-network-user-manager' != $current_screen ) {
        return;
    }

    if ( ! isset( $_REQUEST['user'] ) ) {
        return;
    }

    $user_id = $_REQUEST['user'];

    if ( ! is_numeric( $user_id ) ) {
        return;
    }

    if ( is_super_admin( $user_id ) ) : ?>
        <div id="message" class="updated below-h2">
            <p>
                <b>Note:</b> You are editing a super admin. They will still have access to all sites. Assigned Sites control what will show up in the 'My Sites' Menu
            </p>
        </div>
    <?php endif;
}


add_action( 'admin_notices', 'sudbury_network_user_manager_notices' );

/**
 * Renders the Application
 */
function sudbury_network_user_manager() {
    global $wpdb;
    ?>

    <div class="wrap">
    <h2>Network User Manager - Assign User to Sites</h2>

    <?php
    // User hasn't picked a user, Show a user selection Dropdown so that they can choose a user
    if ( ! isset( $_REQUEST['user'] ) ) : ?>
        <form action="<?php echo esc_attr( network_admin_url( 'users.php' ) ); ?>" method="get">
            <h3> Please Select a user</h3>
            <input type="hidden" name="page" value="sudbury-network-user-manager">
            <select name="user">
                <?php
                $users = get_users( array( 'blog_id' => 0 ) );
                foreach ( $users as $user ) : ?>

                    <option value="<?php echo esc_attr( $user->data->ID ); ?>"><?php echo esc_html( sprintf( '%s (%s)', $user->data->user_login, $user->data->user_email ) ); ?></option>
                <?php endforeach; ?>
            </select>
            <?php submit_button( 'Choose' ); ?>
        </form>

        <?php
        // quit because they haven't selected a user
        return;
    endif;

    $user_id = $_REQUEST['user'];

    if ( ! is_numeric( $user_id ) ) {
        return;
    }

    $user = get_user_by( 'ID', $user_id );

    $assigned_blogs = get_blogs_of_user( $user_id );
    // a quick sorting subfunction
    function sort_blogs_by_blogname( $a, $b ) {
        return ( $a->blogname > $b->blogname ? 1 : - 1 );
    }

    uasort( $assigned_blogs, 'sort_blogs_by_blogname' );


    foreach ( $assigned_blogs as $indx => $blog ) {
        $assigned_blogs[ $indx ] = get_object_vars( $blog ); // converting to array
    }

    $available_blogs = wp_get_sites( array( 'limit' => false ) );
    
    foreach ( $available_blogs as $i => $blog ) {
        $available_blogs[ $i ]['title'] = get_blog_option( $blog['blog_id'], 'blogname' );
    }

    function sort_blogs_by_title( $a, $b ) {
        return ( $a['title'] > $b['title'] ? 1 : - 1 );
    }
    
    uasort( $available_blogs, 'sort_blogs_by_title' );
    
    $l = count( $available_blogs );
    foreach ( $assigned_blogs as $indx => $blog ) {
        for ( $i = 0; $i < $l; $i ++ ) {
            if ( isset( $available_blogs[ $i ] ) && $blog['userblog_id'] == $available_blogs[ $i ]['blog_id'] ) {
                unset( $available_blogs[ $i ] );
                break;
            }
        }


        // determining user's assigned role for the given blog
        switch_to_blog( $blog['userblog_id'] );

        $user = get_userdata( $user_id );

        $capabilities = $user->{$wpdb->prefix . 'capabilities'};

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        foreach ( $wp_roles->role_names as $role => $name ) :

            if ( array_key_exists( $role, $capabilities ) ) {
                $assigned_blogs[ $indx ]['role'] = $role;
            }

        endforeach;

        restore_current_blog();
    }
    $user = get_userdata( $user_id );
    ?>
    <form id="sudbury_user_manager_form" class="sudbury-user-manager-form" action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" method="post">
        <?php wp_nonce_field( 'sudbury-user-manager-nonce' ); ?>
        <input type="hidden" name="action" value="sudbury_save_user_manager" />
        <input type="hidden" name="user" value="<?php echo esc_attr( $user->data->ID ); ?>" />

        <div class="postbox" style="padding:10px;">
            <h3 class="hndle"><?php echo esc_html( $user->data->display_name ); ?></h3>

            <div class="inner">
                <div style="float:left;margin-right:25px;">
                    <?php echo get_avatar( $user_id ); ?>
                </div>
                <div style="float:left;clear:right;">
                    <b>Username:</b> <?php echo esc_html( $user->data->user_login ); ?><br><br>
                    <b>Email:</b> <?php echo esc_html( $user->data->user_email ); ?><br><br>
                    <b>Registered:</b> <?php echo esc_html( mysql2date( 'l, F j, Y g:i a', $user->data->user_registered ) ); ?>
                    <br><br>

                </div>
                <div class="clear"></div>
            </div>

        </div>

        <?php do_action( 'admin_notices' ); ?>

        <div id="settings-wrap">
            <style>

                .network-assign-users .site {
                    background-color: #d9d9d9;
                }

                .network-assign-users .site-committee {
                    background-color: #e8e9ff;
                }

                .network-assign-users .site-department {
                    background-color: #ebffe8;
                }

                .network-assign-users .site-archived, .network-assign-users .site-spam {
                    background-color: #ffebe8;
                }

                .network-assign-users .site-deleted {
                    background-color: #ff8573;
                }

                .network-assign-users-legend {
                    background: #FFF;
                    padding: 10px;
                    line-height: 15px;
                }

                .network-assign-users-legend span {
                    display: inline-block;
                    width: 15px;
                    height: 15px;
                    margin-top: 5px;

                }

            </style>
            <div class="sudbury-setting-section network-assign-users">
                <div class="sudbury-column">
                    <label for="sudbury_assigned_sites"><h3>Assigned Sites</h3>
                        <select id="sudbury_assigned_sites" name="sudbury_assigned_sites[]" style="height: 450px;min-width:200px;" multiple>
                            <?php foreach ( $assigned_blogs as $blog ) : ?>
                                <option value="<?php echo esc_attr( $blog['userblog_id'] ); ?>"
                                        data-blogname="<?php echo esc_attr( $blog['blogname'] ); ?>"
                                        data-role="<?php echo esc_attr( $blog['role'] ); ?>"
                                        class="<?php echo esc_attr( sudbury_network_user_manager_blog_classes( $blog ) ); ?>"><?php echo esc_html( ucwords( $blog['role'] ) . ' @ ' . $blog['blogname'] ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="sudbury-column">
                    <h3>Controls</h3>
                    <input type="button" class="button-primary sudbury-button-danger sudbury-unassign-blog" value="&gt;&gt; Remove Access &gt;&gt;" /><br><br>
                    <input type="button" class="button-primary sudbury-button-success sudbury-assign-blog sudbury-assign-blog-admin" data-role-text="Administrator" data-role="administrator" value="&lt;&lt; Add As Administrator &lt;&lt;" /><br><br>
                    <input type="button" class="button-primary sudbury-button-success sudbury-assign-blog sudbury-assign-blog-editor" data-role-text="Editor" data-role="editor" value="&lt;&lt; Add As Editor &lt;&lt;" /><br><br>
                    <input type="button" class="button-primary sudbury-button-success sudbury-assign-blog sudbury-assign-blog-author" data-role-text="Author" data-role="author" value="&lt;&lt; Add As Author &lt;&lt;" /><br><br>
                </div>
                <div class="sudbury-column">
                    <label for="sudbury_available_sites"><h3>Available Sites</h3>
                        <select id="sudbury_available_sites" name="sudbury_available_sites[]" style="height: 450px;min-width:200px;" multiple>
                            <?php foreach ( $available_blogs as $blog ) : ?>
                                <option value="<?php echo esc_attr( $blog['blog_id'] ); ?>"
                                        data-blogname="<?php echo esc_attr( $blog['title'] ); ?>"
                                        class="<?php echo esc_attr( sudbury_network_user_manager_blog_classes( $blog ) ); ?>"><?php echo esc_html( $blog['title'] ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="sudbury-column">
                    <h3>Key</h3>

                    <div class="network-assign-users-legend">
                        <span class="site"></span> None<br>
                        <span class="site-department"></span> Department<br>
                        <span class="site-committee"></span> Committee<br>
                        <span class="site-archived"></span> Archived<br>
                        <span class="site-spam"></span> Spam<br>
                        <span class="site-deleted"></span> Deleted (Deactivated)<br>

                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>

        <?php submit_button( 'Save User', 'primary' ); ?>

        <img class="sudbury-spinner" src="<?php echo esc_attr( plugins_url( 'images/ajax-loader.gif', __FILE__ ) ); ?>" alt="Saving User..." style="display:none;" />

    </form>
    </div>


<?php
}

function sudbury_network_user_manager_blog_classes( $blog ) {
    $classes = array( 'site' );
    if ( $blog['archived'] ) {
        $classes[] = 'site-archived';
    }
    if ( $blog['deleted'] ) {
        $classes[] = 'site-deleted';
    }
    if ( $blog['spam'] ) {
        $classes[] = 'site-spam';
    }
    if ( $blog['public'] ) {
        $classes[] = 'site-public';
    }

    $classes = array_merge( $classes, array_map( function ( $type ) {
        return "site-$type";
    }, get_blog_option( $blog['blog_id'], 'sudbury_types', array() ) ) );

    return implode( ' ', apply_filters( 'sudbury_network_user_manager_blog_classes', $classes, $blog ) );
}

/**
 * Adds or Removes the users from sites on save
 */
function sudbury_save_user_manager() {
    if ( ! current_user_can( 'manage_network' ) ) {
        sudbury_redirect_error( '<b>Access Denied:</b> You cannot manage the network, and therefore you are not allowed here.' );
    }

    check_admin_referer( 'sudbury-user-manager-nonce' );
    // Good to go on the permissions

    $user_id = $_REQUEST['user'];

    $current_blogs = get_blogs_of_user( $user_id );

    // We only care about what the new list of sites assigned to should be
    $assigned_blogs_raw = $_REQUEST['sudbury_assigned_sites'];

    $assigned_blogs = array();

    // Split up the blogid-role strings into an array of $blogid => $role pairs
    foreach ( $assigned_blogs_raw as $assigned_blog_raw ) {
        $parts                       = explode( '-', $assigned_blog_raw );
        $assigned_blogs[ $parts[0] ] = $parts[1];
    }

    $blogs_to_remove = array();

    $blogs_to_add = array();

    foreach ( $current_blogs as $current_blog ) {
        if ( ! in_array( $current_blog->userblog_id, array_keys( $assigned_blogs ) ) ) {
            $blogs_to_remove[] = $current_blog->userblog_id;
        }
    }

    foreach ( $assigned_blogs as $assignment => $role ) {
        $already_has = false;
        foreach ( $current_blogs as $current_blog ) {
            if ( $current_blog->userblog_id == $assignment ) {
                $already_has = true;
            }
        }

        if ( ! $already_has ) {
            $blogs_to_add[ $assignment ] = $role;
        }
    }

    foreach ( $blogs_to_remove as $blog_id ) {
        remove_user_from_blog( $user_id, $blog_id, 1 );
    }

    foreach ( $blogs_to_add as $blog_id => $role ) {
        add_user_to_blog( $blog_id, $user_id, $role );
    }
}

add_action( 'admin_post_sudbury_save_user_manager', 'sudbury_save_user_manager' );

/**
 * Adds Contextual Help
 */
function sudbury_network_user_manager_help_tab() {
    $screen = get_current_screen();

    // Add my_help_tab if current screen is My Admin Page
    $screen->add_help_tab( array(
        'id'      => 'sudbury_network_user_manager_help_tab',
        'title'   => __( 'Assign Users to Sites' ),
        'content' => '<p>' . __( 'Use this feature to add or remove users from a site in bulk.  Documentation on Roles can be found on <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">the codex</a>' ) . '</p>',
    ) );

    $screen->set_help_sidebar(
        '<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
        '<p>' . __( '<a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">Documentation on Roles</a>' ) . '</p>' .
        '<p>' . __( '<a href="https://wordpress.org/support/" target="_blank">Support Forums</a>' ) . '</p>' .
        '<p>' . __( '<a href="https://github.com/edhurtig/" target="_blank">Network User Manager</a>' ) . '</p>'
    );
}
