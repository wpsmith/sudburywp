<?php
/**
 * The plugin post types file.
 *
 * This file creates all the post types related to this plugin.
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
use WPS\WP\Customizer\Customizer;
use WPS\WP\Fields;
use WPS\WP\Genesis\ArchiveSettings;
use WPS\WP\PostTypes\PostType;

if ( ! class_exists( __NAMESPACE__ . '\PostTypes' ) ) {
	/**
	 * Class PostTypes
	 *
	 * @package \WPS\WP\Plugins\Restaurants
	 */
	class PostTypes extends Singleton {

		/**
		 * The suffix.
		 *
		 * @var string
		 */
		public $suffix = '';

		/**
		 * Holds plugin post types.
		 *
		 * @var [sting]\WPS\WP\PostTypes\PostType
		 */
		public $post_types = array();

		/**
		 * Template loader.
		 *
		 * @var \WPS\WP\Templates\Template_Loader
		 */
		public $template_loader;

		/**
		 * Plugin constructor.
		 *
		 * @param array $args Optional args.
		 */
		protected function __construct( $args = [] ) {

			$this->suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			// Register post types and taxonomies, etc.
			add_action( 'init', array( $this, 'register' ) );

			// Add CMB2.
			add_action( 'cmb2_admin_init', [ $this, 'cmb2' ] );

			// Set query.
//			add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );

			// Templating.
			add_action( 'template_include', [ $this, 'template_include' ], PHP_INT_MAX );

			// Image Sizes.
			\add_image_size( 'restaurant', 600, 400, true );

		}

		/**
		 * Conditionally includes the template.
		 *
		 * @param string $template Template path.
		 *
		 * @return mixed|null|string
		 */
		public function template_include( $template ) {

			$post_type_name = self::get_restaurant_name();
			$post_type      = $this->post_types[ $post_type_name ];

			if ( ! $post_type->is_post_type() ) {
				return $template;
			}

			$loader = Plugin::get_instance()->get_template_loader();

			if ( is_post_type_archive( $post_type_name ) ) {
				return $loader->get_template_part( 'restaurants' );
			} elseif ( is_singular( $post_type_name ) ) {
				return $loader->get_template_part( 'restaurant' );
			}

			return $template;

		}

		/**
		 * Registers post types.
		 */
		public function register() {

			// Register Post Types.
			$this->register_restaurant();

		}

		/** RESTAURANTS */

		/**
		 * Gets post type name.
		 *
		 * @return mixed
		 */
		public static function get_restaurant_name() {

			return apply_filters( 'wps_restaurants_restaurant_post_type_name', 'restaurant' );

		}

		/**
		 * Registers chapter post type.
		 */
		public function register_restaurant() {
			$post_type_name = self::get_restaurant_name();

			try {

				$post_type = new PostType( $post_type_name, array(
					'menu_icon'    => 'dashicons-restaurant',
					'public'       => true,
					'taxonomies'   => [
						'restaurants_category',
						'cuisine',
					],
					'supports'     => [
						'title',
						'thumbnail',
						'editor',
						'custom-fields',
//						'page-attributes',
//						'genesis-cpt-archives-layout-settings',
						'genesis-cpt-archives-seo-settings',
						'genesis-title-toggle',
						'genesis-cpt-archives-settings',
//						'genesis-singular-images',
					],
					'rewrite'      => array(
						'slug'       => 'restaurant',
						'with_front' => true,
					),
					'show_in_rest' => true,
					'has_archive'  => 'restaurants',
				) );

				$post_type->create();

				$this->post_types[ $post_type_name ] = $post_type;

				$archive_settings = ArchiveSettings::get_instance();
				$archive_settings->register_post_type( $post_type_name );

			} catch ( \Exception $e ) {
				// do nothing
			}

		}

		/**
		 * Define the metabox and field configurations.
		 */
		public function cmb2() {
			$post_type_name = self::get_restaurant_name();

			// Start with an underscore to hide fields from custom fields list
			$prefix = '_';

			/**
			 * Initiate the metabox
			 */
			$cmb = new_cmb2_box( array(
				'id'           => $post_type_name . '_metabox',
				'title'        => __( 'Headline Settings', WPS_RESTAURANTS_DOMAIN ),
				'object_types' => array( $post_type_name, ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true,
			) );

//Restaurant Name:
//Cuisine Type:
//Hours:
//Phone:
//Delivery Available? (If yes, list delivery provider details):
//Pickup Details (location, instructions):
//Website:
//Please Note (this is an optional field - if there is any other special info on precautions, instructions, etc please share them here):


			// HOURS
			$group_field_id = $cmb->add_field( array(
				'id'          => 'restaurant_hours',
				'type'        => 'group',
				'description' => __( 'Restaurant hours', WPS_RESTAURANTS_DOMAIN ),
				// 'repeatable'  => false, // use false if you want non-repeatable group
				'options'     => array(
					'group_title'   => __( 'Day {#}', WPS_RESTAURANTS_DOMAIN ), // since version 1.1.4, {#} gets replaced by row number
					'add_button'    => __( 'Add Another Day', WPS_RESTAURANTS_DOMAIN ),
					'remove_button' => __( 'Remove Day', WPS_RESTAURANTS_DOMAIN ),
					'sortable'      => true,
					// 'closed'         => true, // true to have the groups closed by default
					// 'remove_confirm' => esc_html__( 'Are you sure you want to remove?', WPS_RESTAURANTS_DOMAIN ), // Performs confirmation before removing group.
				),
			) );

			// Id's for group's fields only need to be unique for the group. Prefix is not needed.
			$cmb->add_group_field( $group_field_id, array(
				'name'             => __( 'Day', WPS_RESTAURANTS_DOMAIN ),
				'id'               => 'day',
				'type'             => 'select',
				'show_option_none' => true,
				'options'          => array(
					'sunday'    => __( 'Sunday', WPS_RESTAURANTS_DOMAIN ),
					'monday'    => __( 'Monday', WPS_RESTAURANTS_DOMAIN ),
					'tuesday'   => __( 'Tuesday', WPS_RESTAURANTS_DOMAIN ),
					'wednesday' => __( 'Wednesday', WPS_RESTAURANTS_DOMAIN ),
					'thursday'  => __( 'Thursday', WPS_RESTAURANTS_DOMAIN ),
					'friday'    => __( 'Friday', WPS_RESTAURANTS_DOMAIN ),
					'saturday'  => __( 'Saturday', WPS_RESTAURANTS_DOMAIN ),
				),
			) );


			$cmb->add_group_field( $group_field_id, array(
				'name' => __( 'Open', WPS_RESTAURANTS_DOMAIN ),
				'id'   => 'open',
				'type' => 'text_time',
			) );


			// PHONE
			$cmb->add_field( array(
				'name' => __( 'Phone', WPS_RESTAURANTS_DOMAIN ),
				'id'   => $prefix . 'phone',
				'type' => 'text',
			) );

			// Call to Action.
			$cmb->add_field( array(
				'name' => __( 'Call to Action', WPS_RESTAURANTS_DOMAIN ),
				'id'   => $prefix . 'cta',
				'type' => 'wysiwyg',
			) );

//			// Headline Image.
//			$cmb->add_field( array(
//				'name' => __( 'Headline Image', WPS_RESTAURANTS_DOMAIN ),
//				'id'   => '_thumbnail',
//				'type' => 'file',
//			) );

			// Headline Color.
			$cmb->add_field( array(
				'name' => __( 'Headline Color', WPS_RESTAURANTS_DOMAIN ),
				'id'   => $prefix . 'headline_color',
				'type' => 'colorpicker',
			) );


		}

		/**
		 * Filters query to add all posts from post type.
		 *
		 * @param \WP_Query $query The query.
		 */
//		public function pre_get_posts( $query ) {
//			if (
//				! $query->is_main_query() ||
//				is_admin() ||
//				! $query->is_post_type_archive( self::get_restaurant_name() )
//			) {
//				return;
//			}
//
//			$query->set( 'posts_per_page', - 1 );
//		}

	}
}

