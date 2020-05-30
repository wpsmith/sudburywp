<?php
/**
 * The plugin taxonomies file.
 *
 * This file is creates the necessary taxonomies related to the plugin.
 *
 * You may copy, distribute and modify the software as long as you track
 * changes/dates in source files. Any modifications to or software including
 * (via compiler) GPL-licensed code must also be made available under the GPL
 * along with build & install instructions.
 *
 * PHP Version 7.2
 *
 * @category   WPS\WP\Plugins\Restaurants
 * @package    WPS\WP\Plugins\Restaurants
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2019 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://wpsmith.net/
 * @since      0.0.1
 */

namespace WPS\WP\Plugins\SocialDistancing;

use WPS\Core\Singleton;
use WPS\WP\Taxonomies\Taxonomy;

if ( ! class_exists( __NAMESPACE__ . '\Taxonomies' ) ) {
	/**
	 * Class Taxonomies
	 *
	 * @package \WPS\WP\Plugins\Restaurants
	 */
	class Taxonomies extends Singleton {

		/**
		 * The suffix.
		 *
		 * @var string
		 */
		public $suffix = '';

		/**
		 * Holds plugin post types.
		 *
		 * @var [sting]Taxonomy
		 */
		public $taxonomies = array();

		/**
		 * Plugin constructor.
		 *
		 * @param array $args Optional args.
		 */
		protected function __construct( $args = [] ) {

			$this->suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			$this->register();

			add_action( 'admin_init', [ $this, 'genesis_add_taxonomy_archive_options' ], 999 );

		}

		/**
		 * Replace genesis_taxonomy_archive_options with our own.
		 */
		public function genesis_add_taxonomy_archive_options() {

			remove_action( 'restaurants_category_edit_form', 'genesis_taxonomy_archive_options' );
			add_action( 'restaurants_category_edit_form', [ $this, 'genesis_taxonomy_archive_options' ], 10, 2 );

		}

		/**
		 * Echo headline, headline image, and introduction fields on the taxonomy term edit form.
		 *
		 * If populated, the values saved in these fields may display on taxonomy archives.
		 *
		 * @param \stdClass $tag      Term object.
		 * @param string    $taxonomy Name of the taxonomy.
		 */
		public function genesis_taxonomy_archive_options( $tag, $taxonomy ) {

			global $tax_archive_settings;
			$tax_archive_settings = new Admin\TaxArchiveSettings();
			$tax_archive_settings->show_meta_box( 'genesis-term-meta-settings', $tag );

		}

		/**
		 * Registers all taxonomies.
		 */
		public function register() {

			$this->register_restaurants_category();
			$this->register_restaurants_cuisine();

		}

		/**
		 * Registers custom category.
		 */
		public function register_restaurants_cuisine() {

			$active = new Taxonomy( 'cuisine', [
				PostTypes::get_restaurant_name(),
			], [
				'public'  => false,
				'rewrite' => [
					'slug' => 'cuisines',
				],
			] );

			$this->taxonomies['cuisine'] = $active;

		}

		/**
		 * Registers custom category.
		 */
		public function register_restaurants_category() {

			$active = new Taxonomy( 'restaurants_category', [
				PostTypes::get_restaurant_name(),
			], [
				'public'  => false,
				'rewrite' => [
					'slug' => 'restaurants-category',
				],
			] );

			$this->taxonomies['restaurants_category'] = $active;

		}

	}
}

