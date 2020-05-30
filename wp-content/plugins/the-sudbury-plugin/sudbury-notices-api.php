<?php
/**
 * The Notices API for the Sudbury Plugin. Primarily used for admin notices when saving a form to admin-post.php or save_post functions
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Notices
 */

/**
 * Function that will detect if a message should be shown and print it in the admin page using the specified format (updated | error)
 */
function sudbury_admin_message_api() {
    if ( is_network_admin() ) {
        remove_action( 'admin_notices', '' );
    }
    if ( isset( $_REQUEST['sudbury_message'] ) ) {
        ?>
        <div id="message" class="<?php echo esc_attr( $_REQUEST['sudbury_message_type'] ); ?>">
            <p><?php echo urldecode( $_REQUEST['sudbury_message'] ); ?></p>
        </div>
    <?php
    }
}

add_action( 'admin_notices', 'sudbury_admin_message_api', 1 );

/**
 * This is the callback for the action added in sudbury_redirect_notice if a delayed redirect was requested.  It completes the redirect call by exiting PHP and letting the browser preform a 301, 302, ect redirect.
 */
function sudbury_message_api_delayed_redirect() {
    exit;
}

/**
 * Will automatically redirect the user to an admin page and display the specified message.
 *
 * @param string $message (required) The message to place in the Box
 * @param string $class (optional) The wrapper's class for styling (updated | error). default is 'updated'
 * @param bool $redirect (optional) A custom url to redirect to.  If false then $_REQUEST['_wp_http_referer'] will be used. default is false
 * @param bool $delayed (optional) Will Delay redirection to another action or if set to true redirection will fire on shutdown. default is false
 * @param int $code (optional) The HTTP status code to use in the redirection: default is 302s
 *
 * @return bool|void        Returns false if headers have already been sent and cannot redirect, true if it will delay, exit otherwise
 */
function sudbury_redirect_notice( $message, $class = "updated", $redirect = false, $delayed = false, $code = 302 ) {
    global $post;

    if ( headers_sent() ) {
        return false;
    }

    $class = urlencode( $class );
    $message = urlencode( $message );

    if ( isset( $_REQUEST['_wp_http_referer'] ) && false === $redirect ) {
        $parsed = parse_url( $_REQUEST['_wp_http_referer'] );

        if ( strendswith( 'post-new.php', $parsed['path'] ) && is_numeric( $post->ID ) ) {
            $url = add_query_arg( array( 'post' => $post->ID, 'action' => 'edit' ), $_SERVER['REQUEST_URI'] );
        } else {
            $url = $_REQUEST['_wp_http_referer'];
        }
    } else {
        if ( false !== $redirect ) {
            $url = $redirect;
        } else {
            $url = admin_url();
        }
    }
    $url = remove_query_arg( array( 'sudbury_message', 'sudbury_message_type' ), $url );

    // Add  &sudbury_message=$message and  sudbury_message_type=$class
    $url = add_query_arg( array(
        "sudbury_message" => urlencode( $message ),
        "sudbury_message_type" => urlencode( $class )
    ), $url );

    wp_redirect( $url, $code );

    if ( false !== $delayed ) {
        return add_action( ( $delayed === true ? 'shutdown' : $delayed ), 'sudbury_message_api_delayed_redirect' );
    } else {
        // End the current request and all server processing, redirect to determined URL
        exit;
    }
}

/**
 * A wrapper for sudbury_redirect_notice that will produce a yellow updated message
 *
 * @param string $message (required) The message to place in the Box
 * @param bool $redirect (optional) A custom url to redirect to.  If false then $_REQUEST['_wp_http_referer'] will be used. default is false
 * @param bool $delayed (optional) Will Delay redirection to another action or if set to true redirection will fire on shutdown. default is false
 */
function sudbury_redirect_updated( $message, $redirect = false, $delayed = false ) {
    return sudbury_redirect_notice( $message, 'updated', $redirect, $delayed );
}

/**
 * A wrapper for sudbury_redirect_notice that will produce a red error message
 *
 * @param string $message (required) The message to place in the Box
 * @param bool $redirect (optional) A custom url to redirect to.  If false then $_REQUEST['_wp_http_referer'] will be used. default is false
 * @param bool $delayed (optional) Will Delay redirection to another action or if set to true redirection will fire on shutdown. default is false
 */
function sudbury_redirect_error( $message, $redirect = false, $delayed = false ) {
    return sudbury_redirect_notice( $message, 'error', $redirect, $delayed );
}


/**
 * @param        $message
 * @param string $class
 * @param string $where
 */
function sudbury_show_notice( $message, $class = "updated", $where = 'here' ) {
    if ( 'normal' == $where ) {
        //
    } else {
        if ( 'here' == $where ) {
            ?>
            <div id="message" class="<?php echo esc_attr( $class ); ?>">
                <p><?php echo $message; // No $message can contain html so esc_html is not a good idea ?></p>
            </div>
        <?php
        }
    }
}

/**
 * A wrapper for sudbury_redirect_notice that will produce a yellow updated message
 *
 * @param string $message (required) The message to place in the Box
 * @param bool $redirect (optional) A custom url to redirect to.  If false then $_REQUEST['_wp_http_referer'] will be used. default is false
 * @param bool $delayed (optional) Will Delay redirection to another action or if set to true redirection will fire on shutdown. default is false
 */
function sudbury_show_updated( $message, $where = 'here' ) {
    return sudbury_show_notice( $message, 'updated', $where );
}

/**
 * A wrapper for sudbury_redirect_notice that will produce a red error message
 *
 * @param string $message (required) The message to place in the Box
 * @param bool $redirect (optional) A custom url to redirect to.  If false then $_REQUEST['_wp_http_referer'] will be used. default is false
 * @param bool $delayed (optional) Will Delay redirection to another action or if set to true redirection will fire on shutdown. default is false
 */
function sudbury_show_error( $message, $where = 'here' ) {
    return sudbury_show_notice( $message, 'error', $where );
}