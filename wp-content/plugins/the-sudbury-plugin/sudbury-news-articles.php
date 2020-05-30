<?php
/**
 * Contains functionality to enhance News Articles
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage News_Articles
 */


/**
 * Adds the restrictions to stop users from applying spotlight news categories who aren't allowed to
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage News_Articles
 */
class Spotlight_News_Manager {

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * WordPress Init. Registers required actions
	 */
	function init() {
		if ( is_admin() && current_user_can( 'promote_users' ) ) {
			add_action( 'show_user_profile', array( &$this, 'add_user_field' ) );
			add_action( 'edit_user_profile', array( &$this, 'add_user_field' ) );
			add_action( 'profile_update', array( &$this, 'save' ), 10, 2 );
		}

	}

	function add_user_field( $user ) {
		?>
		<table class="form-table">
			<?php wp_nonce_field( 'sudbury-update-spotlight-cap', 'sudbury-update-spotlight-cap-nonce' ); ?>
			<tr>
				<th>
					<label for="sudbury_spotlight_news_cap"><?php _e( 'Spotlight News' ); ?></label>
				</th>
				<td>
					<input type="checkbox" name="sudbury_spotlight_news_cap" id="sudbury_spotlight_news_cap" <?php checked( user_can( $user, 'create_spotlight_news' ) ); ?> <?php disabled( is_super_admin( $user->ID ) ); ?> class="checkbox" />
					<span class="description"><?php _e( 'Spotlight News Capability', 'sudbury' ); ?></span>
				</td>
			</tr>
		</table>
	<?php
	}

	function save( $user_id, $old_data ) {

		if ( ! current_user_can( 'promote_users' ) ) {
			return;
		}

		check_admin_referer( 'sudbury-update-spotlight-cap', 'sudbury-update-spotlight-cap-nonce' );
		$user = new WP_User( $user_id );

		if ( $user->has_cap( 'create_spotlight_news' ) && ! isset( $_REQUEST['sudbury_spotlight_news_cap'] ) ) {
			$user->remove_cap( 'create_spotlight_news' );
		} elseif ( ! $user->has_cap( 'create_spotlight_news' ) && isset( $_REQUEST['sudbury_spotlight_news_cap'] ) ) {
			$user->add_cap( 'create_spotlight_news' );
		}
	}
}

new Spotlight_News_Manager();