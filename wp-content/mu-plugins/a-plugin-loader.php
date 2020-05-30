<?php
/**
 * Plugin Name: Sudbury Plugin Loader
 * Plugin URI: http://sudbury.ma.us/
 * Description: Allows Forced Disabling of Network Plugins on individual sites
 * Version: 1.0
 * Author: Eddie Hurtig
 * Author URI: http://hurtigtechnologies.com
 */

/**
 * Defines functionality to override the network plugins that are loaded
 */
class Sudbury_Plugin_Loader {
	function __construct() {
		add_filter('site_option_active_sitewide_plugins', array(&$this, 'enabled_plugins'), 1, 1);
	}

	function enabled_plugins($plugins) {
		if ( ! did_action( 'plugins_loaded' ) ) {
			$disable = $this->get_forced_plugins();
			$plugins = array_diff_key($plugins, $disable);
		}
		return $plugins;
	}

	function get_forced_plugins() {
		return get_option('disable_network_plugins', array());
	} 
}

new Sudbury_Plugin_Loader();
