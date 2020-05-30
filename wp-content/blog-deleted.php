<?php
/**
 * Handles the error message for a Deleted (Deactivated) Blog because the default message could raise problems with patrons
 *
 * @author Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package Sudbury
 * @subpackage Multisite
 */

wp_die( get_sudbury_contact_admin_message( 'This Site has been Deactivated. <br><br>If you believe this is an error' ) );