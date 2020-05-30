<?php
/**
 * Social Distancing Plugin
 *
 * @package   social-distancing
 * @link      https://github.com/johnbillion/query-monitor
 * @author    Travis Smith <t@wpsmith.net>
 * @copyright 2020 Travis Smith
 * @license   GPL v2 or later
 *
 * Plugin Name:  Social Distancing Plugin
 * Description:  Social Distancing Enhancements
 * Version:      0.0.1
 * Plugin URI:   https://sudbury.ma.us
 * Author:       Travis Smith
 * Author URI:   https://wpsmith.net
 * Text Domain:  social-distancing
 * Domain Path:  /languages/
 * Requires PHP: 7.0
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */


namespace WPS\WP\Plugins;

use WPS\WP\Plugin;

// Require autoloader.
require_once dirname( __FILE__ ) . '/vendor/autoload.php';

/**
 * Root plugin file.
 * @const WPS_RESTAURANTS_FILE __FILE__
 */
define( 'WPS_RESTAURANTS_FILE', __FILE__ );

/**
 * Root plugin file.
 * @const WPS_RESTAURANTS_DIRNAME __FILE__
 */
define( 'WPS_RESTAURANTS_DIRNAME', dirname(__FILE__ ));

/**
 * Language domain.
 *
 * @const WPS_RESTAURANTS_DOMAIN 'wps-restaurants'
 */
define( 'WPS_RESTAURANTS_DOMAIN', 'wps-restaurants' );

// Instantiate!
SocialDistancing\Plugin::get_instance();

// Register our on activation hook.
register_activation_hook( WPS_RESTAURANTS_FILE, array( __NAMESPACE__ . '\Restaurants\Plugin', 'on_activation' ) );

// Hide this plugin from plugins list.
//new Plugin\HidePlugin( plugin_basename( __FILE__ ) );

// Prevent plugin from being able to be updated.
new Plugin\PreventUpdate( plugin_basename( __FILE__ ) );
