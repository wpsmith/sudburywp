<?php
/**
 * Adds functionality specific to the meetings Custom Post Type
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Links
 */
/**
 * Registers a menu item that will redirect users to the Links Menu nav-menu page
 */
function sudbury_register_admin_links_menu() {
	add_menu_page( 'Links Menu Redirect', 'Site Navigation', 'edit_pages', 'sudbury-links-redirect', 'sudbury_redirect_registry', 'dashicons-admin-links', 9 );
}

add_action( 'admin_menu', 'sudbury_register_admin_links_menu' );


/**
 * Returns the full url to the links menu for the current site
 * @return string|void
 */
function sudbury_get_links_menu_url() {
	return admin_url( 'nav-menus.php?action=edit&menu=' . sudbury_get_links_menu_id() );
}

add_action( 'redirect_url_sudbury-links-redirect', 'sudbury_get_links_menu_url' );


/**
 * Returns the id of the links custom menu for the current site
 * @return mixed
 */
function sudbury_get_links_menu_id() {
	$term = get_term_by( 'slug', 'links-menu', 'nav_menu' );
	if ( is_object( $term ) && ! is_wp_error( $term ) ) {
		return $term->term_id;	
	}
	return NULL;
}

/* This is for the List of Links Admin Section */

/**
 * @param array $args
 *
 * @return array
 */
function sudbury_get_links( $args = array() ) {
	$defaults = array(
		'category_name' => '',
		'post_type'     => 'link'
	);

	$args = wp_parse_args( $args, $defaults );

	return get_posts( $args );
}

/**
 * Gets all the 'link' posts in the Network
 *
 * @param array $args An array of args for the network_query
 *
 * @return array An Array of Posts returned by network_query_posts
 */
function sudbury_get_network_links( $args = array() ) {
	$defaults = array(
		'posts_per_page' => 9999,
		'post_type'      => 'link'
	);

	$args = wp_parse_args( $args, $defaults );

	return network_query_posts( $args );
}

/**
 * Adds the metabox with options for Links
 */
function sudbury_add_links_metabox() {
	add_meta_box( 'sudbury-links-metabox-1', 'Link Settings', 'sudbury_do_links_metabox', 'link', 'normal' );
}

add_action( 'add_meta_boxes', 'sudbury_add_links_metabox' );

/**
 * The Metabox for link posts
 */
function sudbury_do_links_metabox() {
	global $post;


	$link_url   = get_post_meta( $post->ID, 'sudbury_redirect_url', true );
	$link_class = '';

	if ( ! $link_url ) {
		$link_url = '';
	}

	// Only show an error if:
	//  1) the Link is not valid
	//  AND
	//  2) this is NOT a new link (new links don't have sudbury_redirect_enabled in post meta yet (but do after 1st save))
	if ( ! filter_var( $link_url, FILTER_VALIDATE_URL ) && get_post_meta( $post->ID, 'sudbury_redirect_enabled', true ) ) {
		$link_class .= 'error ';
	}

	?>
	<div class="form-field form-required sudbury-metabox">
		<?php wp_nonce_field( 'sudbury-link-post-meta', 'sudbury-links' ); ?>
		<table class="form-table">
			<tbody>
			<tr>
				<th>
					<label for="sudbury_redirect_url">Type or Paste Url:</label>
				</th>
				<td>
					<input type="text" class="<?php echo $link_class; ?>sudbury-field sudbury-text" name="sudbury_redirect_url" id="sudbury_redirect_url" placeholder="http://example.com/..." value="<?php echo esc_attr( $link_url ); ?>">
					<br><span class="description">Remember to include the <code>http://</code> or <code>https://</code></span>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
<?php
}

/**
 * Checks to make sure this link has a title and a url before it is allowed to publish
 */

/**
 * Saves the Post Meta when saving a link
 */
function sudbury_save_link( $post_id ) {

	if ( 'link' != get_post_type( $post_id ) ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'You Cheatin (You don\'t have access to edit Links)' );
	}


	if ( isset( $_REQUEST['sudbury_redirect_url'] ) ) {
		check_admin_referer( 'sudbury-link-post-meta', 'sudbury-links' );

		$link_url = $_REQUEST['sudbury_redirect_url'];

		/*
		 * <lecture>
		 * There is a very specific reason why this is named 'sudbury_redirect_url'
		 * We use sudbury_redirect_url for post redirection in News Articles.  The Redirector doesn't care what post_type
		 * is currently being used though, if there is a sudbury_redirect_url meta field it will 302 redirect the user to
		 * that url
		 *
		 * So Don't change this to sudbury_link_url or anything else because it will break things...
		 * </lecture>
		 */
		update_post_meta( $post_id, 'sudbury_redirect_url', $link_url );


		// Checking to make sure that there is both a title and a url if this post is published
		$title = $_REQUEST['post_title'];
		if ( $title && filter_var( $link_url, FILTER_VALIDATE_URL ) ) {
			update_post_meta( $post_id, 'sudbury_redirect_enabled', true ); // Ensure that redirection is enabled
		} else {


			// If the post is currently in publish (and it lacks either a title or url) then unpublish it
			if ( 'publish' == get_post_status( $post_id ) ) {
				// No they didn't set things right
				wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );

				wp_transition_post_status( 'draft', 'publish', $post_id );

				sudbury_redirect_error( 'This Link is missing either a title or url (or both).  <b>The Link is not published!</b>' );
			}

		}
	}
}

add_action( 'save_post', 'sudbury_save_link', 50 );

/**
 * Prints the Link URL to the Links CPT Admin Table
 *
 * @param $column  The Column Currently being Rendered
 * @param $post_id The Post ID (Row) being rendered
 */
function sudbury_admin_links_table_columns( $column, $post_id ) {

	switch ( $column ) {
		case 'url':
			$url = get_post_meta( $post_id, 'sudbury_redirect_url', true );

			echo '<a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a>';
			break;
		default :
			break;
	}
}

add_action( 'manage_link_posts_custom_column', 'sudbury_admin_links_table_columns', 10, 2 );

/**
 * Adds a URL Column for the Links Custom Post Type Admin UI
 *
 * @param $columns The Existing Columns
 *
 * @return array The New List of Columns
 */
function sudbury_add_link_url_column( $columns ) {
	return array_merge( $columns,
		array( 'url' => __( 'URL', 'sudbury' ) ) );
}

add_filter( 'manage_link_posts_columns', 'sudbury_add_link_url_column' );

/**
 * Unregisters the 'link_category' from $wp_taxonomies
 */
function sudbury_disable_link_category() {
	global $wp_taxonomies;

	unset( $wp_taxonomies['link_category'] );
}

add_action( 'muplugins_loaded', 'sudbury_disable_link_category' );
add_action( 'init', 'sudbury_disable_link_category' );
