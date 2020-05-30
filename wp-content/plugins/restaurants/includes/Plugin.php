<?php
/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * You may copy, distribute and modify the software as long as you track
 * changes/dates in source files. Any modifications to or software including
 * (via compiler) GPL-licensed code must also be made available under the GPL
 * along with build & install instructions.
 *
 * PHP Version 7.2
 *
 * @category   WPS\WP\Plugins\CoreClasses
 * @package    WPS\WP\Plugins\CoreClasses
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2019-2020 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://wpsmith.net/
 * @since      0.0.1
 */

namespace WPS\WP\Plugins\SocialDistancing;

use WPS\Core\Singleton;

if ( ! class_exists( __NAMESPACE__ . '\Plugin' ) ) {
	/**
	 * Class Plugin
	 *
	 * @package \WPS\WP\Plugins\CoreClasses
	 */
	class Plugin extends Singleton {

		/**
		 * Plugin Version Number
		 */
		const VERSION = '0.0.1';

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    0.1.0
		 * @access   protected
		 * @var      string $plugin_name The string used to uniquely identify this plugin.
		 */
		protected static $plugin_name = 'restaurant';

		/**
		 * @var Taxonomies
		 */
		public $taxonomies;

		/**
		 * @var PostTypes
		 */
		public $post_types;

		/**
		 * @var \WPS\WP\Templates\Template_Loader
		 */
		public $template_loader;

		/**
		 * Plugin constructor.
		 *
		 * @param array $args Optional args.
		 */
		protected function __construct( $args = [] ) {

			$this->taxonomies = Taxonomies::get_instance();
			$this->post_types = PostTypes::get_instance();

			$this->template_loader = $this->get_template_loader();

		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     0.1.0
		 * @return    string    The name of the plugin.
		 */
		public static function get_plugin_name() {
			return self::$plugin_name;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     0.1.0
		 * @return    string    The version number of the plugin.
		 */
		public static function get_version() {
			return self::VERSION;
		}

		/**
		 * Gets the template loader.
		 *
		 * @param array $args Template Loader args.
		 *
		 * @return \WPS\WP\Templates\TemplateLoader
		 */
		public function get_template_loader() {
			if ( $this->template_loader ) {
				return $this->template_loader;
			}

			// Create template loader.
			$this->template_loader = new \WPS\WP\Templates\TemplateLoader( [
				'filter_prefix'    => 'wps',
				'plugin_directory' => WPS_RESTAURANTS_DIRNAME,
			] );

			return $this->template_loader;
		}

		/**
		 * Activation function.
		 */
		public static function on_activation() {

			$instance = self::get_instance();

//			$instance->post_types->register();
//			$instance->taxonomies->register();

			flush_rewrite_rules();

		}

	}
}

