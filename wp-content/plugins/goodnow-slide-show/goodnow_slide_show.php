<?php
/*
Plugin Name: Goodnow Slide Show
Plugin URI: http://www.levers.com/
Description: A Wordpress plugin for handling the homepage slide show for the Goodnow Library.
Version: 1.0
Author: Jonathan Tegg
Author URI: http://www.teggweb.com/
License: GPL2
*/
/*
Copyright 2013 Jonathan Tegg  (email: jonathan@teggweb.com)

Based on WP Plugin Template by Francis Yaconiello https://github.com/fyaconiello/goodnow_slide_show

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!class_exists('Goodnow_Slide_Show'))
{
	class Goodnow_Slide_Show
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
        	// Initialize Settings
            require_once(sprintf("%s/settings.php", dirname(__FILE__)));
            $Goodnow_Slide_Show_Settings = new Goodnow_Slide_Show_Settings();
        	
        	// Register custom post types
            require_once(sprintf("%s/post-types/slide_show.php", dirname(__FILE__)));
            $Slide_Show = new Slide_Show();
		} // END public function __construct
	    
		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
			// Do nothing
		} // END public static function activate
	
		/**
		 * Deactivate the plugin
		 */		
		public static function deactivate()
		{
			// Do nothing
		} // END public static function deactivate
	} // END class Goodnow_Slide_Show
} // END if(!class_exists('Goodnow_Slide_Show'))

if(class_exists('Goodnow_Slide_Show'))
{
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('Goodnow_Slide_Show', 'activate'));
	register_deactivation_hook(__FILE__, array('Goodnow_Slide_Show', 'deactivate'));

	// instantiate the plugin class
	$goodnow_slide_show = new Goodnow_Slide_Show();
	
    // Add a link to the settings page onto the plugin page
    if(isset($goodnow_slide_show))
    {
        // Add the settings link to the plugins page
        function goodnow_slide_show_plugin_settings_link($links)
        { 
            $settings_link = '<a href="options-general.php?page=goodnow_slide_show">Settings</a>'; 
            array_unshift($links, $settings_link); 
            return $links; 
        }

        $plugin = plugin_basename(__FILE__); 
        add_filter("plugin_action_links_$plugin", 'goodnow_slide_show_plugin_settings_link');
    }
}