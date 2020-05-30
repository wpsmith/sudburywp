<?php
/*
 * This file contains changes to the default user experience
 */


/**
 * Configures specific settings from a template user and defaults for a new user
 */
function sudbury_registration_save( $user_id ) {
    $settings_to_copy = array(
        'meta-box-order_dashboard',
        'metaboxhidden_dashboard',
        'closedpostboxes_dashboard'
    );

    _sudbury_log( "Initializing User {$user_id}" );
    if ( defined('TEMPLATE_USER_ID') ) {
        _sudbury_log( 'Using Template User ID: ' . TEMPLATE_USER_ID );
        foreach ( $settings_to_copy as $setting_name ) {
            if ( $setting = get_user_meta(TEMPLATE_USER_ID, $setting_name, true) ) {
                update_user_meta( $user_id, $setting_name, $setting );
                _sudbury_log( "Set {$setting_name} to value " + var_export( $setting, true ) );
            }
        }
    }
}

add_action( 'user_register', 'sudbury_registration_save', 10, 1 );

