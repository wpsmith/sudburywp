<?php
/*
Plugin Name: Sudbury Global Categories
Plugin URI: http://sudbury.ma.us/
Description: WordPress Multisite Global Category Support
Version: 1.0
Author: Eddie Hurtig - See contact info in code comments
Author URI: http://hurtigtechnologies.com
Network: True
*/

/**
 * Global categories support means that all sites in the network share the same terms and term_taxonomy table.
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Sudbury_Categories
 */
class Sudbury_Global_Categories {

	private $enabled = array();
	private $disable = false;

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'admin_menu', array( $this, 'admin_menu' ), 20 );
	}

	function init() {
		$this->enabled[ get_current_blog_id() ] = Sudbury_Global_Categories::is_enabled();
		add_filter( 'query', array( $this, 'query' ), 1, 1 );
	}

	function admin_init() {
		register_setting( 'sgc_options', 'sgc_options', array( $this, 'options_validate' ) );
		add_settings_section( 'sgc_main', 'Main Settings', array( $this, 'main_options' ), 'sgc' );
		add_settings_field( 'sgc_enabled', 'Enable Global Categories', array(
			$this,
			'enabled_field'
		), 'sgc', 'sgc_main' );
	}

	function options_validate( $input ) {
		$newinput['enabled'] = (bool) trim( $input['enabled'] );

		return $newinput;
	}

	/**
	 * Switches all select statements for wp_#_terms and wp_#_term_taxonomy to point
	 * to the root site's tables.
	 *
	 * @param $sql The SQL Query to manipulate
	 *
	 * @return string The new SQL query
	 */
	function query( $sql ) {
		global $blog_id;
		if ( $this->disable ) {
			return $sql;
		}

		if ( ! $this->check_enabled() ) {
			return $sql;
		}

		if ( get_current_blog_id() == 1 ) {
			return $sql;
		}

		// If the query contains the word "term" and the Query is not a network_term query
		if ( strpos( $sql, 'term' ) !== false ) {
			if ( strpos( $sql, 'network_term' ) === false ) {
				// If the query is a SELECT, rewrite it to the blog 1 tables (wp_term and wp_term_taxonomy)
				// Otherwise kill the query because it is writing to the DB
				if ( strpos( trim( $sql ), 'SELECT' ) === 0 || strpos( trim( $sql ), 'SHOW FULL COLUMNS FROM' ) ) {
					$old = $sql;
					$sql = preg_replace( '/(wp)_(\\d)+(_term_taxonomy)/', "$1$3", $sql, - 1 );
					$sql = preg_replace( '/(wp)_(\\d)+(_terms)/', "$1$3", $sql, - 1 );
					$this->log( "Modified Query: \n$old\nto\n$sql" );
				} else {
					// This is probably an upate query, kill it now.
					$this->log( "Would Kill Query: '$sql'" );
					//return false;
				}
			} else {
				$this->log( "Allowing network term query: $sql" );
			}
		}

		return $sql;
	}

	/**
	 * Identical to is_enabled, however it uses the instance cache to prevent extra option queries
	 */
	function check_enabled() {
		$blog_id = get_current_blog_id();
		if ( isset( $this->enabled[ $blog_id ] ) ) {
			return $this->enabled[ $blog_id ];
		}

		$this->disable             = true;
		$this->enabled[ $blog_id ] = Sudbury_Global_Categories::is_enabled();
		$this->disable             = false;

		return $this->enabled[ $blog_id ];
	}

	function admin_menu() {
		add_options_page( 'Global Categories', 'Global Categories', 'manage_options', 'sgc', array(
			$this,
			'options_page'
		) );

		if ( ! defined( 'SUDBURY_VERSION' ) ) {
			$this->log( 'The Sudbury Plugin is not activated, disabling admin features for Global Categories' );

			return;
		}

		if ( Sudbury_Global_Categories::is_enabled() && $this->current_user_can_edit_categories() ) {
			$i = 3.0;
			while ( isset( $menu[ $i ] ) ) {
				$i += .1;
			}

			add_menu_page( 'Categories Redirect', 'Categories', 'edit_posts', 'sudbury-categories-link', 'sudbury_admin_callback', 'dashicons-list-view', $i );
			sudbury_register_redirect( 'admin_page', 'sudbury-categories-link', '/wp-admin/edit-tags.php?taxonomy=category' );

			//        add_submenu_page( 'sudbury-categories-link', 'Categories Redirect', 'News Article Categories', 'edit_posts', 'sudbury-news-categories-link', 'sudbury_admin_callback', 'dashicons-list-view', $i );
			//        sudbury_register_redirect( 'admin_page', 'sudbury-news-categories-link', 'edit-tags.php?taxonomy=category' );

			add_submenu_page( 'sudbury-categories-link', 'Categories Redirect', 'Doc. Categories', 'edit_posts', 'sudbury-document-categories-link', 'sudbury_admin_callback', 'dashicons-list-view', $i );
			sudbury_register_redirect( 'admin_page', 'sudbury-document-categories-link', '/wp-admin/edit-tags.php?taxonomy=document_categories&post_type=attachment' );

			add_submenu_page( 'sudbury-categories-link', 'Categories Redirect', 'FAQ Categories', 'edit_posts', 'sudbury-faq-categories-link', 'sudbury_admin_callback', 'dashicons-list-view', $i );
			sudbury_register_redirect( 'admin_page', 'sudbury-faq-categories-link', '/wp-admin/edit-tags.php?taxonomy=faq_categories&post_type=faq' );

			add_submenu_page( 'sudbury-categories-link', 'Categories Redirect', 'Link Categories', 'edit_posts', 'sudbury-link-categories-link', 'sudbury_admin_callback', 'dashicons-list-view', $i );
			sudbury_register_redirect( 'admin_page', 'sudbury-link-categories-link', '/wp-admin/edit-tags.php?taxonomy=link_categories&post_type=link' );
		}
	}

	function options_page() { ?>
		<div>
			<h2>Sudbury Global Categories</h2>
			Site Specific Global Categories Settings
			<?php if ( get_current_blog_id() == 1 ) : ?>
				<p>This is the root site of the network (blog 1). You cannot enable global categories on the root site as it is the keeper of all categories</p>
			<?php else: ?>
				<form action="options.php" method="post">
					<?php settings_fields( 'sgc_options' ); ?>
					<?php do_settings_sections( 'sgc' ); ?>

					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	function main_options() { ?>
		<p> All pertinant settings can be found in this section </p>
	<?php }

	function enabled_field() {
		$options = get_option( 'sgc_options' );
		?>
		<input id='sgc_enabled' name='sgc_options[enabled]' type='checkbox' <?php checked( $options['enabled'] ); ?> />
	<?php }

	function current_user_can_edit_categories() {
		global $wp_roles;

		$category_editor = false;
		switch_to_blog( 1 );
		foreach ( $wp_roles->role_names as $role => $name ) :
			if ( current_user_can( $role ) ) {
				if ( 'editor' == $role || 'administrator' == $role ) {
					$category_editor = true;
				}
			}
		endforeach;
		restore_current_blog();

		return $category_editor;
	}

	function log( $expr ) {
		if ( function_exists( '_sudbury_log' ) ) {
			//_sudbury_log( $expr );
		}
	}

	public static function is_enabled( $blog_id = false ) {
		if ( $blog_id === false ) {
			$blog_id = get_current_blog_id();
		}
		switch_to_blog( $blog_id );

		$options = get_option( 'sgc_options', array() );
		restore_current_blog();

		return ( isset( $options['enabled'] ) ? $options['enabled'] : false );
	}
}

new Sudbury_Global_Categories();

