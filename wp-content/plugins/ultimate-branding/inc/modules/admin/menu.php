<?php
/**
 * Branda Administrator Menu class.
 *
 * Class that handles admin menu functionality.
 *
 * @package Branda
 * @subpackage Admin Menu
 */
if ( ! class_exists( 'Branda_Admin_Menu' ) ) {

	/**
	 * Class Branda_Admin_Menu.
	 */
	class Branda_Admin_Menu extends Branda_Helper {
		const USER_MENU_KEY_PREFIX = 'user-';
		const MENU_ITEM_SETTING_PREFIX = 'menu_item_';
		const CUSTOM_ADMIN_MENU_OPTION = 'ub_custom_admin_menu';

		/**
		 * Admin menu module option name.
		 *
		 * @var string
		 */
		protected $option_name = 'ub_admin_menu';

		/**
		 * Branda_Admin_Menu constructor.
		 */
		public function __construct() {
			parent::__construct();
			$this->module = 'admin_menu';
			// Base hooks.
			add_filter( 'ultimatebranding_settings_admin_menu', array( $this, 'admin_options_page' ) );
			add_filter( 'ultimatebranding_settings_admin_menu_process', array( $this, 'update' ) );
			// Upgrade options to new.
			add_action( 'init', array( $this, 'upgrade_options' ) );
			// Dashboard link.
			add_action( 'user_admin_menu', array( $this, 'remove_dashboard_link' ) );
			// Link manager.
			add_filter( 'pre_option_link_manager_enabled', array( $this, 'link_manager' ) );
			// WP Admin -> Settings -> permalinks.
			add_action( 'admin_menu', array( $this, 'remove_permalinks_menu_item' ) );
			// Admin panel tips.

			// Add dialog
			add_filter( 'branda_get_module_content', array( $this, 'add_dialog' ), 10, 2 );
			$this->maybe_load_tips();

			add_action( 'branda_admin_enqueue_module_admin_assets', array( $this, 'enqueue_scripts' ) );

			add_action( 'wp_ajax_branda_admin_bar_load_menu', array( $this, 'ajax_load_menu' ) );
			add_action( 'wp_ajax_branda_admin_bar_save_menu', array( $this, 'ajax_save_menu' ) );
			add_action( 'wp_ajax_branda_admin_bar_remove_menu', array( $this, 'ajax_delete_menu' ) );
			add_action( 'wp_ajax_branda_admin_menu_search_user', array( $this, 'ajax_search_user' ) );

			add_filter( 'parent_file', array( $this, 'start_admin_menu_buffer' ) );
			add_action( 'adminmenu', array( $this, 'end_admin_menu_buffer' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_global_admin_assets' ) );
			add_action( 'admin_menu', array( $this, 'block_hidden_page' ), 999999 );

			add_filter( 'ultimate_branding_options_names', array( $this, 'add_options_names' ) );
		}

		public function add_options_names( $options ) {
			$options[] = self::CUSTOM_ADMIN_MENU_OPTION;

			return $options;
		}

		/**
		 * Adds assets throughout the admin area
		 */
		public function enqueue_global_admin_assets() {
			wp_enqueue_style(
				'branda-admin-menu-global',
				ub_files_url( 'modules/admin/assets/css/admin/admin-menu-global.css' ),
				array(), $this->build
			);
		}

		public function start_admin_menu_buffer( $parent_file ) {
			ob_start();
			return $parent_file;
		}

		public function end_admin_menu_buffer() {
			$menu = $this->get_user_menu_settings();

			if ( empty( $menu ) || $this->is_network_admin ) {
				echo ob_get_clean();
				return;
			}

			ob_end_clean();

			$menu = $this->prepare_menu_items( $menu, true );

			if ( ! class_exists( 'Branda_Admin_Menu_Builder' ) ) {
				require_once dirname( __FILE__ ) . '/custom-admin-menu/branda-admin-menu-builder.php';
			}
			global $self, $parent_file, $submenu_file, $plugin_page, $typenow;

			$builder = new Branda_Admin_Menu_Builder(
				$menu, $self, $parent_file,
				$submenu_file, $plugin_page, $typenow
			);

			$builder->build();
			$builder->render();
		}

		public function block_hidden_page() {
			if ( wp_doing_ajax() || ! apply_filters( 'branda_custom_admin_menu_block_access', true ) ) {
				return;
			}

			$menu_settings = $this->get_user_menu_settings();
			if ( empty( $menu_settings ) ) {
				return;
			}

			$menu_settings = $this->prepare_menu_items( $menu_settings, false );
			global $menu, $submenu;

			foreach ( $menu as $menu_item_index => $menu_item ) {
				if ( count( $menu_item ) < 7 ) {
					continue;
				}

				list( , , $menu_slug ) = $menu_item;
				$menu_item_id = $this->menu_slug_to_id( $menu_slug );
				$is_menu_item_hidden = (boolean) ub_get_array_value( $menu_settings, array(
					$menu_item_id,
					'is_hidden',
				) );

				if ( $is_menu_item_hidden ) {
					$this->prevent_admin_page_access( $menu_slug );
				}

				if ( ! empty( $submenu[ $menu_slug ] ) ) {
					foreach ( $submenu[ $menu_slug ] as $submenu_item_index => $submenu_item ) {
						if ( count( $submenu_item ) < 3 ) {
							continue;
						}

						list( , , $submenu_slug ) = $submenu_item;
						$submenu_item_id = $this->menu_slug_to_id( $submenu_slug );
						$is_submenu_item_hidden = (boolean) ub_get_array_value( $menu_settings, array(
							$menu_item_id,
							'submenu',
							$submenu_item_id,
							'is_hidden',
						) );

						if ( $is_menu_item_hidden || $is_submenu_item_hidden ) {
							$this->prevent_admin_page_access( $submenu_slug );
						}
					}
				}
			}
		}

		private function prevent_admin_page_access( $slug ) {
			$url = basename( esc_url_raw( $_SERVER['REQUEST_URI'] ) );
			$url = htmlspecialchars( $url );
			if ( empty( $url ) ) {
				return;
			}

			$uri = wp_parse_url( $url );
			$uri_path = ub_get_array_value( $uri, 'path' );
			if ( empty( $uri_path ) ) {
				return;
			}
			$uri_query = ub_get_array_value( $uri, 'query' );
			$callback = array( $this, 'show_admin_page_access_error' );

			$is_dashboard = $uri_path === 'wp-admin' && $slug === 'index.php'; // e.g. /wp-admin/
			$is_admin_file = empty( $uri_query ) && strpos( $uri_path, $slug ) !== false; // e.g. /wp-admin/edit.php
			if ( $is_dashboard || $is_admin_file ) {
				add_action( 'load-' . $slug, $callback );
				return;
			}

			$is_plugin_page = urldecode( $uri_query ) === "page=$slug"; // e.g. /wp-admin/admin.php?page=branda
			if ( $is_plugin_page ) {
				$plugin_page_hook = get_plugin_page_hook( $slug, $uri_path );
				add_action( 'load-' . $plugin_page_hook, $callback );
				return;
			}

			// URL is equal the slug of WP menu.
			if (
				$slug === $url
				|| $this->fix_customizer_slug( $slug ) === $this->fix_customizer_slug( $url )
			) {
				add_action( 'load-' . basename( $uri_path ), $callback );
				return;
			}
		}

		public function show_admin_page_access_error() {
			wp_die( __( 'Sorry, you are not allowed to access this page.', 'ub' ), 403 );
		}

		private function get_user_menu_settings() {
			$user = wp_get_current_user();
			$roles = $user->roles;
			$role = array_shift( $roles );

			$role_menu = $this->get_menu_settings( $role );
			$user_menu = $this->get_menu_settings( 'user-' . $user->ID, $role_menu );

			return $user_menu;
		}

		public function enqueue_scripts( $module ) {
			if ( $this->module !== $module ) {
				return;
			}

			if ( ! wp_script_is( 'branda-dashicon-picker' ) ) {
				$dashicon_select = ub_files_url( 'modules/admin/assets/js/admin/dashicon-picker.js' );
				wp_enqueue_script( 'branda-dashicon-picker', $dashicon_select, array( 'jquery' ), $this->build, true );
			}

			if ( ! wp_style_is( 'branda-dashicon-picker' ) ) {
				wp_enqueue_style(
					'branda-dashicon-picker',
					ub_files_url( 'modules/admin/assets/css/admin/dashicon-picker.css' ),
					array(), $this->build
				);
			}

			$this->enqueue_menu_assets();
		}

		function enqueue_menu_assets() {
			wp_enqueue_media();

			$scripts = array(
				'custom-admin-menu',
				'menu-icon-uploader',
				'menu-item',
				'menu-item-container',
				'menu-item-model',
				'menu-tabs',
				'menu-utils',
			);
			$base_url = $admin_menu = ub_files_url( 'modules/admin/assets/js/admin/admin-menu/' );

			foreach ( $scripts as $script ) {
				$script_id = 'branda-' . $script;
				if ( wp_script_is( $script_id ) ) {
					continue;
				}

				wp_enqueue_script( $script_id, $base_url . $script . '.js', array(
					'jquery',
					'underscore',
				), $this->build, true );
			}

			wp_localize_script( 'branda-custom-admin-menu', 'branda_custom_menu', array(
				'discard_confirm'     => esc_html__( 'Are you sure you want to discard all the changes?', 'ub' ),
				'duplicate_postfix'   => esc_html_x( '[DUPLICATE]', 'Duplicate item postfix - custom admin menu', 'ub' ),
				'nonce'               => wp_create_nonce( 'branda-custom-admin-menu' ),
				'user_menu_keys'      => $this->get_user_menu_keys(),
				'role_menu_keys'      => $this->get_custom_admin_menu_roles(),
				'setting_updated'     => esc_html__( 'Your settings have been updated!', 'ub' ),
				'title_missing_error' => esc_html__( 'Title the item to continue.', 'ub' ),
			) );
		}

		/**
		 * Add the custom admin menu dialog
		 *
		 * @param string $content Current module content.
		 * @param array $module Current module.
		 *
		 * @return string
		 */
		public function add_dialog( $content, $module ) {
			if ( $this->module !== $module['module'] ) {
				return $content;
			}

			$dialog_markup = $this->render(
				$this->get_template_name( 'dialogs/custom-admin-menu-dialog' ),
				array(
					'dialog_id'    => $this->get_name( 'custom-admin-menu' ),
					'search_nonce' => wp_create_nonce( $this->get_nonce_action_name( 'search' ) ),
				),
				true
			);
			$custom_admin_menu_template = $this->render(
				$this->get_template_name( 'dialogs/custom-admin-menu' ), array(), true
			);
			$menu_item_container_template = $this->render(
				$this->get_template_name( 'dialogs/custom-admin-menu-item-container' ), array(), true
			);
			$menu_item_template = $this->render(
				$this->get_template_name( 'dialogs/custom-admin-menu-item' ),
				array( 'is_main_item' => true ),
				true
			);
			$submenu_item_template = $this->render(
				$this->get_template_name( 'dialogs/custom-admin-menu-item' ),
				array( 'is_main_item' => false ),
				true
			);
			$dashicon_picker_template = $this->render( 'admin/common/dashicon-picker', array(
				'list' => include( ub_dir( '/etc/dashicons.php' ) ),
			), true );
			$tabs_template = $this->render(
				$this->get_template_name( 'dialogs/custom-admin-menu-tabs' ), array(), true
			);
			$tab_template = $this->render(
				$this->get_template_name( 'dialogs/custom-admin-menu-tab' ), array(), true
			);
			$tab_content_template = $this->render(
				$this->get_template_name( 'dialogs/custom-admin-menu-tab-content' ), array(), true
			);
			$notice_top_template = $this->render( 'admin/common/notice-top', array(), true );
			return $content
			       . $dialog_markup
			       . $custom_admin_menu_template
			       . $menu_item_container_template
			       . $menu_item_template
			       . $submenu_item_template
			       . $dashicon_picker_template
			       . $notice_top_template
			       . $tabs_template
			       . $tab_template
			       . $tab_content_template
			       . $this->get_media_template();
		}

		private function get_media_template() {
			return $this->render(
				$this->get_template_name( 'dialogs/custom-admin-menu-media-template' ),
				array(
					'template_id' => 'admin-menu-media-uploader',
					'input_name'  => 'icon_image_id',
				),
				true
			);
		}

		/**
		 * @return string[]
		 */
		public function get_custom_admin_menu_roles() {
			$roles = array();

			foreach ( wp_roles()->get_names() as $role_id => $role_name ) {
				$roles[ $role_id ] = $role_name;
			}

			return $roles;
		}

		/**
		 * Set options for module admin page.
		 *
		 * @since 3.0.0
		 */
		protected function set_options() {
			$options = array(
				'custom-admin-menu' => array(
					'title'       => __( 'Custom Admin menu', 'ub' ),
					'description' => __( 'Customize the admin menu by user roles or by custom user. You can add, hide and reorder the menu items as needed.', 'ub' ),
					'fields'      => array(
						'button' => array(
							'type'  => 'button',
							'value' => __( 'Customize', 'ub' ),
							'icon'  => 'wrench-tool',
							'sui'   => 'ghost',
							'data'  => array(
								'a11y-dialog-show' => $this->get_name( 'custom-admin-menu' ),
							),
						),
					),
				),
				'dashboard-link'    => array(
					'network-only' => true,
					'title'        => __( 'Dashboard Link', 'ub' ),
					'description'  => __( 'Remove "Dashboard" link from admin panel for users without site (in WP Multisite).', 'ub' ),
					'fields'       => array(
						'status' => array(
							'checkbox_label' => __( 'Remove link for users without site', 'ub' ),
							'type'           => 'checkbox',
							'classes'        => array( 'switch-button' ),
						),
					),
				),
				'link-manager'      => array(
					'title'       => __( 'Link Manager', 'ub' ),
					'description' => __( 'Enables the Link Manager that existed in WordPress until version 3.5.', 'ub' ),
					'fields'      => array(
						'status' => array(
							'checkbox_label' => __( 'Enable link manager', 'ub' ),
							'type'           => 'checkbox',
							'classes'        => array( 'switch-button' ),
						),
					),
				),
				'tips'              => array(
					'title'       => __( 'Admin Tips', 'ub' ),
					'description' => __( 'Provide your users with helpful random tips, or promotions/news in their admin panels.', 'ub' ),
					'fields'      => array(
						'status' => array(
							'checkbox_label' => __( 'Enable Admin Tips', 'ub' ),
							'type'           => 'checkbox',
							'description'    => array(
								'content'  => __( 'Add a custom post type “Tips” in the WordPress menu and start adding tips for the users.', 'ub' ),
								'position' => 'bottom',
							),
							'classes'        => array( 'switch-button' ),
						),
					),
				),
			);
			$this->options = $options;
		}

		public function menu_slug_to_id( $slug ) {
			if ( $this->is_custom_menu_slug( $slug ) ) {
				return $slug;
			}

			$slug = $this->fix_customizer_slug( $slug );

			/**
			 * https://stackoverflow.com/a/28602439/3871020
			 */
			$short_hash = substr( base_convert( md5( $slug ), 16, 32 ), 0, 12 );

			return self::MENU_ITEM_SETTING_PREFIX . $short_hash;
		}

		/**
		 * Menu items that take the user to customizer.php can include a query var 'return'.
		 * The value of this var depends on the page that the user is currently on.
		 * Since we use href of menu items to as identifiers we have to remove this variable part.
		 *
		 * @param $slug
		 *
		 * @return string
		 */
		private function fix_customizer_slug( $slug ) {
			if (
				strpos( $slug, 'customize.php' ) === false
				|| strpos( $slug, 'return' ) === false
			) {
				return $slug;
			}

			return remove_query_arg( 'return', wp_specialchars_decode( $slug ) );
		}

		private function build_menu_item_settings_array() {
			return array(
				'title'         => array(
					'name'        => 'title',
					'type'        => 'text',
					'label'       => esc_html__( 'Menu item title', 'ub' ),
					'description' => array(
						'content'  => esc_html__( 'Give this menu item a title.', 'ub' ),
						'position' => 'bottom',
					),
					'default'     => '{{ data.title }}',
					'placeholder' => '{{ data.title_default }}',
				),
				'id_attribute'  => array(
					'name'         => 'id_attribute',
					'type'         => 'text',
					'label'        => esc_html__( 'ID attribute', 'ub' ),
					'description'  => array(
						'content'  => esc_html__( 'Must be unique.', 'ub' ),
						'position' => 'bottom',
					),
					'before_field' => '<div class="sui-row"><div class="sui-col">',
					'after_field'  => '</div>',
					'default'      => '{{ data.id_attribute }}',
					'placeholder'  => '{{ data.id_attribute_default }}',
				),
				'css_classes'   => array(
					'name'         => 'css_classes',
					'type'         => 'text',
					'label'        => esc_html__( 'CSS Classes', 'ub' ),
					'description'  => array(
						'content'  => esc_html__( 'Add your own CSS class. Separate multiple classes with a space.', 'ub' ),
						'position' => 'bottom',
					),
					'before_field' => '<div class="sui-col">',
					'after_field'  => '</div></div>',
					'default'      => '{{ data.css_classes }}',
					'placeholder'  => '{{ data.css_classes_default }}',
				),
				'icon_svg'      => array(
					'name'         => 'icon_svg',
					'type'         => 'callback',
					'callback'     => array( $this, 'print_icon_svg_textarea' ),
					'label'        => esc_html__( 'SVG code', 'ub' ),
					'master'       => $this->get_name( 'icon-type' ),
					'master-value' => 'svg',
					'display'      => 'sui-tab-content',
				),
				'icon_url'      => array(
					'name'         => 'icon_url',
					'type'         => 'url',
					'label'        => esc_html__( 'URL', 'ub' ),
					'master'       => $this->get_name( 'icon-type' ),
					'master-value' => 'url',
					'display'      => 'sui-tab-content',
					'description'  => array(
						'content'  => __( 'Enter the URL of your icon. This should preferably be a PNG file.', 'ub' ),
						'position' => 'bottom',
					),
					'default'      => '{{ data.icon_url }}',
					'placeholder'  => '{{ data.icon_url_default }}',
				),
				'icon_image_id' => array(
					'name'         => 'icon_image_id',
					'type'         => 'callback',
					'callback'     => array( $this, 'print_icon_uploader' ),
					'master'       => $this->get_name( 'icon-type' ),
					'master-value' => 'upload',
					'display'      => 'sui-tab-content',
					'description'  => array(
						'content'  => sprintf( __( 'Upload an icon to override the default menu item icon. The recommended size is %s.', 'ub' ), '<strong>20x20</strong>' ),
						'position' => 'bottom',
					),
				),
				'dashicon'      => array(
					'name'         => 'dashicon',
					'type'         => 'callback',
					'callback'     => array( $this, 'print_dashicon_selector' ),
					'master'       => $this->get_name( 'icon-type' ),
					'master-value' => 'dashicon',
					'display'      => 'sui-tab-content',
					'default'      => '{{ data.dashicon }}',
				),
				'icon_type'     => array(
					'name'        => 'icon_type',
					'type'        => 'sui-tab',
					'label'       => __( 'Icon', 'ub' ),
					'options'     => array(
						'dashicon' => __( 'Dashicon', 'ub' ),
						'svg'      => __( 'Svg code', 'ub' ),
						'url'      => __( 'URL link', 'ub' ),
						'upload'   => __( 'Upload', 'ub' ),
						'none'     => __( 'None', 'ub' ),
					),
					'slave-class' => $this->get_name( 'icon-type' ),
				),
				'custom_url'    => array(
					'name'         => 'custom_url',
					'type'         => 'url',
					'label'        => esc_html__( 'URL', 'ub' ),
					'placeholder'  => esc_attr__( '{{ data.custom_url_default }}', 'ub' ),
					'master'       => $this->get_name( 'link_type' ),
					'master-value' => 'custom',
					'display'      => 'sui-tab-content',
					'default'      => '{{ data.custom_url }}',
				),
				'link_type'     => array(
					'name'        => 'link_type',
					'type'        => 'sui-tab',
					'label'       => __( 'Link to', 'ub' ),
					'options'     => array(
						'none'     => __( 'None', 'ub' ),
						'frontend' => __( 'Main Site', 'ub' ),
						'admin'    => __( 'WP Admin Area', 'ub' ),
						'custom'   => __( 'Custom URL', 'ub' ),
					),
					'slave-class' => $this->get_name( 'link_type' ),
				),
				'link_target'   => array(
					'name'    => 'link_target',
					'type'    => 'sui-tab',
					'label'   => __( 'Open link in', 'ub' ),
					'options' => array(
						''       => __( 'Same Tab', 'ub' ),
						'_blank' => __( 'New Tab', 'ub' ),
					),
				),
				'is_hidden'     => array(
					'name'     => 'is_hidden',
					'type'     => 'callback',
					'callback' => '__return_empty_string',
				),
				'was_native'    => array(
					'name' => 'was_native',
					'type' => 'hidden',
				),
			);
		}

		public function print_icon_svg_textarea() {
			ob_start();
			?>
			<textarea name="icon_svg"
			          class="sui-form-control"
			          placeholder="{{ data.icon_svg_default }}">{{ data.icon_svg }}</textarea>
			<span class="sui-description">
				<?php esc_html_e( "Paste a base64-encoded SVG using a data URI, which will be colored to match the color scheme. This should begin with 'data:image/svg+xml;base64,'", 'ub' ); ?>
			</span>
			<?php
			return ob_get_clean();
		}

		public function menu_item_settings() {
			$fields = $this->build_menu_item_settings_array();
			foreach ( $fields as $field_id => $field ) {
				$fields[ $field_id ]['id'] = "{{ data.id }}_{$field_id}";
			}

			$options = array(
				"menu_item" => array(
					'show-as' => 'flat',
					'fields'  => $fields,
				),
			);
			$simple_options = new Simple_Options();

			return $simple_options->build_options( $options, array(), $this->module );
		}

		public function print_icon_uploader() {
			return '<div class="branda-admin-menu-icon-uploader"></div>';
		}

		public function print_dashicon_selector() {
			return '<input type="hidden" name="dashicon"/>';
		}

		private function menu_hook_to_id_attr( $menu_hook ) {
			return preg_replace( '|[^a-zA-Z0-9_:.]|', '-', $menu_hook );
		}

		private function get_menu_defaults( $include_separators ) {
			global $menu, $submenu;
			$menu_defaults = array();
			$separator = 0;

			foreach ( $menu as $menu_item ) {
				if ( count( $menu_item ) < 7 ) {
					if ( $include_separators ) {
						$menu_defaults[ 'separator_' . $separator ] = $menu_item;
						$separator ++;
					}
					continue;
				}

				$menu_item_defaults = array_combine(
					array_keys( $this->build_menu_item_settings_array() ),
					array_fill( 0, count( $this->build_menu_item_settings_array() ), '' )
				);

				list( $menu_title, $menu_capability,
					$menu_slug, $menu_page_title, $menu_classes,
					$menu_hookname, $menu_icon ) = $menu_item;

				// Don't include defaults for custom admin menu items added by us because they only contain dummy values
				if ( $this->is_custom_menu_slug( $menu_slug ) ) {
					continue;
				}

				$menu_id = $this->menu_slug_to_id( $menu_slug );

				$submenu_items = isset( $submenu[ $menu_slug ] )
					? $submenu[ $menu_slug ]
					: array();
				$menu_url = $this->get_menu_item_default_url( $menu_slug, $submenu_items );

				$menu_defaults[ $menu_id ] = wp_parse_args(
					$this->menu_item_extras(
						$menu_title, $menu_slug, $menu_url,
						$this->menu_hook_to_id_attr( $menu_hookname ), $menu_classes, $menu_icon
					),
					$menu_item_defaults
				);

				if ( ! empty( $submenu_items ) ) {
					$submenu_items = $submenu[ $menu_slug ];

					foreach ( $submenu_items as $submenu_item ) {
						if ( count( $submenu_item ) < 3 ) {
							continue;
						}

						list(
							$submenu_title,
							$submenu_capability,
							$submenu_slug ) = $submenu_item;

						$submenu_classes = isset( $submenu_item[4] ) ? $submenu_item[4] : '';

						if ( $this->is_custom_menu_slug( $submenu_slug ) ) {
							continue;
						}

						$submenu_id = $this->menu_slug_to_id( $submenu_slug );

						$submenu_url = $this->get_sub_menu_item_default_url( $submenu_slug, $menu_slug, $menu_url );

						$menu_defaults[ $menu_id ]['submenu'][ $submenu_id ] = wp_parse_args(
							$this->menu_item_extras( $submenu_title, $submenu_slug, $submenu_url, '', $submenu_classes ),
							$menu_item_defaults
						);
					}
				} else {
					$menu_defaults[ $menu_id ]['submenu'] = true;
				}
			}

			return $menu_defaults;
		}

		public function ajax_search_user() {
			$user_id = filter_input( INPUT_GET, 'user_id', FILTER_SANITIZE_STRING );
			$nonce_action = $this->get_nonce_action_name( 'search', $user_id );
			$this->check_input_data( $nonce_action, array( 'user_id', 'q' ) );
			$q = filter_input( INPUT_GET, 'q', FILTER_SANITIZE_STRING );
			if ( empty( $q ) ) {
				$this->json_error();
			}

			$users = get_users( array(
				'search' => '*' . $q . '*',
				'fields' => 'all_with_meta',
			) );

			$result = array();
			foreach ( $users as $user ) {
				$result[] = array(
					'id'       => $user->get( 'ID' ),
					'text'     => '',
					'title'    => $user->get( 'display_name' ),
					'subtitle' => '',
				);
			}

			wp_send_json_success( $result );
		}

		public function ajax_load_menu() {
			check_ajax_referer( 'branda-custom-admin-menu' );

			if ( ! Branda_Permissions::get_instance()->current_user_has_access() ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Access denied', 'ub' ) ) );
				return;
			}

			$data = stripslashes_deep( $_POST );
			$menu_key = ub_get_array_value( $data, 'key' );
			if ( empty( $menu_key ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Something went wrong', 'ub' ) ) );
				return;
			}

			if ( $this->is_user_menu_key( $menu_key ) ) {
				wp_set_current_user( $this->get_user_id_from_key( $menu_key ) );
			} else {
				$this->simulate_role( $menu_key );
			}
			if ( is_multisite() ) {
				$this->clear_super_admins();
			}

			$this->maybe_load_menu_data();

			do_action( 'branda_custom_admin_menu_loaded' );

			$menu = $this->get_user_menu_settings();
			$menu = $this->prepare_menu_items( $menu );

			wp_send_json_success( array( 'menu' => $menu ) );
		}

		private function get_user_menu_keys() {
			$menus = $this->get_menu_settings();
			$all_keys = array_keys(
				empty( $menus ) || ! is_array( $menus ) ? array() : $menus
			);
			$user_menu_keys = array_filter( $all_keys, array( $this, 'is_user_menu_key' ) );
			$return = array();

			foreach ( $user_menu_keys as $user_menu_key ) {
				$user_id = $this->get_user_id_from_key( $user_menu_key );
				$user = get_user_by( 'id', $user_id );
				if ( $user ) {
					$return[ $user_menu_key ] = $user->get( 'display_name' );
				}
			}

			return $return;
		}

		private function prepare_menu_items( $menu, $include_separators = false ) {
			$menu = $this->parse_args_deep( $menu, $this->get_menu_defaults( $include_separators ) );

			return $this->remove_old_native_items( $menu );
		}

		private function remove_old_native_items( $menu ) {
			foreach ( $menu as $menu_item_id => $menu_item_settings ) {
				$is_native = (boolean) ub_get_array_value( $menu_item_settings, 'is_native' );
				$was_native = (boolean) ub_get_array_value( $menu_item_settings, 'was_native' );

				if ( ! $is_native && $was_native ) {
					unset( $menu[ $menu_item_id ] );
					continue;
				}

				$submenu = ub_get_array_value( $menu_item_settings, 'submenu' );
				if ( is_array( $submenu ) ) {
					$menu[ $menu_item_id ]['submenu'] = $this->remove_old_native_items( $submenu );
				}
			}

			return $menu;
		}

		private function is_user_menu_key( $key ) {
			return 0 === strpos( $key, self::USER_MENU_KEY_PREFIX );
		}

		private function parse_args_deep( $array, $defaults ) {
			if ( empty( $array ) ) {
				return $defaults;
			}

			if ( empty( $defaults ) ) {
				return $array;
			}

			$default_position = 0;
			foreach ( $defaults as $default_id => $default_value ) {
				if ( is_array( $default_value ) ) {
					$parsed_value = $this->parse_args_deep(
						ub_get_array_value( $array, $default_id ),
						$default_value
					);

					if ( isset( $array[ $default_id ] ) ) {
						$array[ $default_id ] = $parsed_value;
					} else {
						$array = $this->array_insert_at_position( $array, $default_position, $default_id, $parsed_value );
					}
				} else {

					if ( ! isset( $array[ $default_id ] ) ) {
						$array = $this->array_insert_at_position( $array, $default_position, $default_id, $default_value );
					}
				}

				$default_position ++;
			}

			return $array;
		}

		private function array_insert_at_position( $array, $position, $key, $value ) {
			$size = count( $array );

			return $this->array_splice( $array, 0, $position )
			       + array( $key => $value )
			       + $this->array_splice( $array, $position, $size - $position );
		}

		private function array_splice( $array, $offset, $length ) {
			return array_splice( $array, $offset, $length, true );
		}

		/**
		 * We need these values in the js templates but they shouldn't be saved in the DB
		 *
		 * @param string $title_default
		 * @param string $slug
		 * @param string $url_default
		 * @param string $id_attribute_default
		 * @param string $css_classes_default
		 * @param string $icon_default
		 *
		 * @return array
		 */
		private function menu_item_extras( $title_default = '', $slug = '', $url_default = '', $id_attribute_default = '', $css_classes_default = '', $icon_default = '' ) {
			list( $title_only_default, $notification ) = $this->get_menu_item_title_parts( $title_default );

			$extras = array(
				// A native item is one that is not created through Branda
				'is_native'            => true,
				// The following flag indicates that the menu item was originally native so we can remove it when necessary
				'was_native'           => 1,
				'slug'                 => $slug,
				'title_default'        => $title_only_default,
				'notification'         => $notification,
				'id_attribute_default' => $id_attribute_default,
				'css_classes_default'  => $css_classes_default,
				'link_type_default'    => 'custom',
				'custom_url_default'   => $url_default,
			);

			$extras = $this->maybe_hide_permalinks_submenu( $slug, $extras );
			$extras = $this->menu_item_default_icons( $icon_default, $extras );

			return $extras;
		}

		private function maybe_hide_permalinks_submenu( $slug, $menu_item_settings ) {
			if (
				$slug === 'options-permalink.php'
				&& $this->is_permalinks_page_removed()
			) {
				$menu_item_settings['is_hidden'] = 1;
			}

			return $menu_item_settings;
		}

		private function is_permalinks_page_removed() {
			return $this->get_value( 'permalink', 'status', 'off' ) !== 'off';
		}

		private function menu_item_default_icons( $menu_icon, $menu_item_settings ) {
			$has_dashicon = strpos( $menu_icon, 'dashicons' ) === 0;
			$menu_item_settings['dashicon_default'] = $has_dashicon ? $menu_icon : '';

			$has_svg = strpos( $menu_icon, 'data:image/svg' ) === 0
			           || strpos( $menu_icon, 'data:image/png' ) === 0;
			$menu_item_settings['icon_svg_default'] = $has_svg ? $menu_icon : '';

			$has_url = $menu_icon && esc_url_raw( $menu_icon ) === $menu_icon;
			$menu_item_settings['icon_url_default'] = $has_url ? $menu_icon : '';

			if ( $has_dashicon ) {
				$default_type = 'dashicon';
			} elseif ( $has_svg ) {
				$default_type = 'svg';
			} elseif ( $has_url ) {
				$default_type = 'url';
			} else {
				$default_type = 'none';
			}
			$menu_item_settings['icon_type_default'] = $default_type;

			return $menu_item_settings;
		}

		public function ajax_save_menu() {
			check_ajax_referer( 'branda-custom-admin-menu' );

			$data = stripslashes_deep( $_POST );
			$menu_key = ub_get_array_value( $data, 'key' );
			$menu_json = ub_get_array_value( $data, 'menu' );

			if ( empty( $menu_key ) || empty( $menu_json ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Something went wrong', 'ub' ) ) );
				return;
			}

			$menu = json_decode( $menu_json, true );
			if ( empty( $menu ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Could not parse menu data', 'ub' ) ) );
				return;
			}

			$menu = $this->sanitize_custom_admin_menu( $menu );

			$this->set_menu_settings( $menu_key, $menu );
			wp_send_json_success();
		}

		public function ajax_delete_menu() {
			check_ajax_referer( 'branda-custom-admin-menu' );

			$data = stripslashes_deep( $_POST );
			$menu_key = ub_get_array_value( $data, 'key' );
			if ( empty( $menu_key ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Something went wrong', 'ub' ) ) );
				return;
			}

			$this->delete_menu_settings( $menu_key );
			wp_send_json_success();
		}

		private function sanitize_custom_admin_menu( $menu ) {
			foreach ( $menu as $menu_item_id => $menu_item_settings ) {
				if ( ! is_array( $menu_item_settings ) ) {
					return array();
				}

				// Sanitize each setting value
				foreach ( $menu_item_settings as $setting_key => $setting_value ) {
					if ( $setting_key !== 'submenu' ) {
						$menu[ $menu_item_id ][ $setting_key ] = sanitize_text_field( $setting_value );
					}
				}

				$extras = array_diff_key(
					$this->menu_item_extras(),
					$this->build_menu_item_settings_array()
				);
				// Remove any 'extra' items not meant for saving
				$menu[ $menu_item_id ] = array_diff_key(
					$menu_item_settings, $extras
				);

				// Repeat the same process on the submenu
				if ( ! empty( $menu_item_settings['submenu'] ) ) {
					$menu[ $menu_item_id ]['submenu'] = $this->sanitize_custom_admin_menu( $menu_item_settings['submenu'] );
				}
			}

			return $menu;
		}

		private function get_menu_item_title_parts( $title ) {
			$matches = array();
			preg_match( '/(.*?)(<span[^>]*>[\s\S]*<\/span>)/', $title, $matches );
			$notification = '';

			if ( isset( $matches[1] ) && isset( $matches[2] ) ) {
				$title = $matches[1];
				$notification = $matches[2];
			}

			return array(
				wp_strip_all_tags( trim( $title ) ),
				$notification,
			);
		}

		/**
		 * Upgrade options to new structure.
		 *
		 * @since 3.0.0
		 */
		public function upgrade_options() {
			$uba = ub_get_uba_object();
			$modules = get_ub_activated_modules();
			$update = false;
			$data = array(
				'dashboard-link' => array(
					'status' => 'off',
				),
				'link-manager'   => array(
					'status' => 'off',
				),
				'tips'           => array(
					'status' => 'off',
				),
				'permalink'      => array(
					'status' => 'off',
				),
			);
			$m = array(
				'admin-panel-tips/admin-panel-tips.php'            => 'tips',
				'link-manager.php'                                 => 'link-manager',
				'remove-dashboard-link-for-users-without-site.php' => 'dashboard-link',
				'remove-permalinks-menu-item.php'                  => 'permalink',
			);
			foreach ( $m as $module => $option_name ) {
				if (
					isset( $modules[ $module ] )
					&& 'yes' === $modules[ $module ]
				) {
					$data[ $option_name ]['status'] = 'on';
					$update = true;
					$uba->deactivate_module( $module );
				}
			}
			if ( ! $update ) {
				return;
			}
			$this->update_value( $data );
		}

		/**
		 * Remove dashboard link from WP Admin.
		 *
		 * Remove dashboard link for users withou any sites.
		 */
		public function remove_dashboard_link() {
			$status = $this->get_value( 'dashboard-link', 'status', 'off' );
			if ( 'off' === $status ) {
				return;
			}
			$user_blogs = get_blogs_of_user( get_current_user_id() );
			if ( 0 === count( $user_blogs ) ) {
				remove_menu_page( 'index.php' );
				$current_url = $this->get_admin_current_page_url();
				if ( preg_match( '/user\//', $current_url ) && ! preg_match( '/profile.php/', $current_url ) ) {
					wp_redirect( 'profile.php' );
				}
			}
		}

		/**
		 * Get current admin page url.
		 *
		 * @access private
		 *
		 * @return string $page_url
		 */
		private function get_admin_current_page_url() {
			$page_url = 'http';
			if ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) {
				$page_url .= 's';
			}
			$page_url .= '://';
			if ( isset( $_SERVER['SERVER_PORT'] ) && '80' !== $_SERVER['SERVER_PORT'] ) {
				$page_url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
			} else {
				$page_url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			}
			return $page_url;
		}

		/**
		 * Handle link manager enable/disable.
		 *
		 * @param bool $status Disabled status.
		 *
		 * @return bool|mixed|null|string
		 */
		public function link_manager( $status ) {
			$status = $this->get_value( 'link-manager', 'status', 'off' );
			if ( 'off' === $status ) {
				return false;
			}
			return true;
		}

		/**
		 * Remove permalinks option from admin menu.
		 */
		public function remove_permalinks_menu_item() {
			$custom_user_menu = $this->get_user_menu_settings();
			if ( ! empty( $custom_user_menu ) ) {
				// If there is a custom menu we will let that take care of the permalink menu item
				return;
			}
			if ( wp_doing_ajax() ) {
				// If we are loading menu items for the custom admin menu dialog
				return;
			}
			$status = $this->get_value( 'permalink', 'status', 'off' );
			if ( 'off' === $status ) {
				return;
			}
			global $submenu;
			// Check parent menu.
			if ( ! isset( $submenu['options-general.php'] ) || ! is_array( $submenu['options-general.php'] ) ) {
				return;
			}
			foreach ( $submenu['options-general.php'] as $key => $data ) {
				if ( 'options-permalink.php' === $data[2] ) {
					unset( $submenu['options-general.php'][ $key ] );
					return;
				}
			}
		}

		/**
		 * Load tips for admin panel.
		 */
		public function maybe_load_tips() {
			$status = $this->get_value( 'tips', 'status', 'off' );
			if ( 'off' === $status ) {
				return;
			}
			$file = ub_files_dir( 'modules/admin/tips.php' );
			include_once $file;
		}

		private function maybe_load_menu_data() {
			global $menu, $submenu;
			$wp_menu_file = ABSPATH . 'wp-admin/menu.php';

			if ( ( is_null( $menu ) || is_null( $submenu ) ) && file_exists( $wp_menu_file ) ) {
				global $menu_order, $default_menu_order, $_wp_last_object_menu;
				$menu_order = $default_menu_order = array();

				require $wp_menu_file;
			}
		}

		/**
		 * @param $menu_slug
		 *
		 * @return bool
		 */
		private function is_custom_menu_slug( $menu_slug ) {
			return strpos( $menu_slug, self::MENU_ITEM_SETTING_PREFIX ) === 0;
		}

		/**
		 * @see _wp_menu_output
		 *
		 * @param $menu_slug
		 * @param $submenu_items
		 *
		 * @return string
		 */
		private function get_menu_item_default_url( $menu_slug, $submenu_items ) {
			if ( ! empty( $submenu_items ) ) {
				$submenu_items = array_values( $submenu_items );  // Re-index.
				$first_submenu_slug = $submenu_items[0][2];
				if ( $this->is_admin_parent( $first_submenu_slug, $menu_slug ) ) {
					$default_url = "admin.php?page={$first_submenu_slug}";
				} else {
					$default_url = $first_submenu_slug;
				}
			} else {
				if ( $this->is_admin_parent( $menu_slug ) ) {
					$default_url = "admin.php?page={$menu_slug}";
				} else {
					$default_url = $menu_slug;
				}
			}

			return $default_url;
		}

		/**
		 * @see _wp_menu_output
		 *
		 * @param $submenu_item_slug
		 * @param $menu_item_slug
		 * @param $menu_item_url
		 *
		 * @return string
		 */
		private function get_sub_menu_item_default_url( $submenu_item_slug, $menu_item_slug, $menu_item_url ) {
			if ( $this->is_admin_parent( $submenu_item_slug, $menu_item_slug ) ) {
				$main_item_has_admin_parent = strpos( $menu_item_url, 'admin.php?page=' ) === 0;
				$menu_file = $this->get_menu_file( $menu_item_slug );

				if (
					file_exists( $menu_file )
					|| ( ! $main_item_has_admin_parent
					     && file_exists( WP_PLUGIN_DIR . "/{$menu_file}" )
					     && ! is_dir( WP_PLUGIN_DIR . "/{$menu_item_slug}" ) )
				) {
					$default_url = add_query_arg( array( 'page' => $submenu_item_slug ), $menu_item_slug );
				} else {
					$default_url = add_query_arg( array( 'page' => $submenu_item_slug ), 'admin.php' );
				}
				$default_url = esc_url( $default_url );
			} else {
				$default_url = $submenu_item_slug;
			}

			return $default_url;
		}

		private function is_admin_parent( $menu_slug, $parent_page = 'admin.php' ) {
			$menu_hook = get_plugin_page_hook( $menu_slug, $parent_page );
			$menu_file = $this->get_menu_file( $menu_slug );

			return ! empty( $menu_hook )
			       || (
				       ( 'index.php' != $menu_slug )
				       && file_exists( WP_PLUGIN_DIR . "/$menu_file" )
				       && ! file_exists( ABSPATH . "/wp-admin/$menu_file" )
			       );
		}

		private function get_menu_file( $menu_slug ) {
			$menu_file = $menu_slug;
			if ( false !== ( $pos = strpos( $menu_file, '?' ) ) ) {
				$menu_file = substr( $menu_file, 0, $pos );
			}

			return $menu_file;
		}

		private function simulate_role( $role_name ) {
			global $current_user;

			$role = get_role( $role_name );

			$current_user->ID = PHP_INT_MAX;
			$current_user->allcaps = $role->capabilities;
			$current_user->caps = $role->capabilities;
			$current_user->roles = array( $role_name );
		}

		private function clear_super_admins() {
			global $super_admins;
			$super_admins = array();

			add_filter( 'pre_site_option_site_admins', '__return_empty_array' );
		}

		/**
		 * @param $user_menu_key
		 *
		 * @return mixed
		 */
		private function get_user_id_from_key( $user_menu_key ) {
			return str_replace( self::USER_MENU_KEY_PREFIX, '', $user_menu_key );
		}

		private function get_menu_settings( $key = '', $default = array() ) {
			$menus = ub_get_option( self::CUSTOM_ADMIN_MENU_OPTION );
			if ( empty( $key ) ) {
				return is_array( $menus )
					? $menus
					: $default;
			}

			$menu = ub_get_array_value( $menus, $key );

			return is_array( $menu )
				? $menu
				: $default;
		}

		private function set_menu_settings( $key, $menu_settings ) {
			$menus = $this->get_menu_settings();
			$menus[ $key ] = $menu_settings;

			return ub_update_option( self::CUSTOM_ADMIN_MENU_OPTION, $menus );
		}

		private function delete_menu_settings( $key ) {
			$menus = $this->get_menu_settings();
			unset( $menus[ $key ] );

			return ub_update_option( self::CUSTOM_ADMIN_MENU_OPTION, $menus );
		}
	}
}
new Branda_Admin_Menu();