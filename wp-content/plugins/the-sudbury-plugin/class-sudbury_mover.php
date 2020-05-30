<?php

/**
 * Class Sudbury_Mover
 * Moves Posts and Pages from one blog to another
 *
 * @author     Eddie Hurtig <hurtige@ccs.neu.edu>
 * @package    Sudbury
 * @subpackage Multisite
 */
class Sudbury_Mover {
	var $page_slug;

	var $source_blog;
	var $destination;

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * Init: Register Actions and menu pages
	 */
	function init() {

		add_filter( 'post_row_actions', array( &$this, 'post_row_actions' ), 10, 2 );
		add_filter( 'page_row_actions', array( &$this, 'post_row_actions' ), 10, 2 );
		// Media moving unsupported ATM

		add_action( 'admin_post_sudbury_move_post', array( &$this, 'admin_post' ) );

		$this->page_slug = add_management_page( 'Move Post', 'Move Post', 'delete_posts', 'sudbury-move-post', array(
			&$this,
			'admin_page'
		) );
	}

	/**
	 * Adds a link to move the post to the Post Lists Table
	 *
	 * @param array   $actions The existing Actions
	 * @param WP_Post $post    The Post
	 *
	 * @return array The New Actions
	 */
	function post_row_actions( $actions, $post ) {
		if ( $post->post_status != 'trash' && ! sudbury_is_multi_post( $post->ID ) ) {
			$actions['move'] = '<a href="' . $this->get_admin_page_link($post) . '">Move</a>';
		}
		return $actions;
	}

	/**
	 * Returns the appropriate link to move a post to the specified blog
	 *
	 * @param int|WP_Post $post        The Post to move.  0 for current post
	 * @param int|string  $source_blog The source blog. Empty String for current blog
	 *
	 * @return string The URL used to move the $post to the $destination
	 */
	function get_admin_page_link( $post = 0, $source_blog = '' ) {
		if ( ! $source_blog ) {
			$source_blog = get_current_blog_id();
		}
		switch_to_blog( $source_blog );

		if ( $post ) {
			$post = get_post( $post );

			$url = admin_url( 'admin.php?page=sudbury-move-post&post=' . $post->ID );

			$url = wp_nonce_url( $url, 'sudbury-select-destination', 'sudbury-select-destination-nonce' );
		} else {
			$url = admin_url( 'admin.php?page=sudbury-move-post' );
		}

		restore_current_blog();

		return $url;
	}

	/**
	 * Get the admin_post URL for the source blog
	 *
	 * @param int|string $source_blog The Source Blog
	 *
	 * @return string The URL to the source blog's admin_post with the sudbury_mover action added
	 */
	function get_admin_post_link( $source_blog = '' ) {
		if ( ! $source_blog ) {
			$source_blog = get_current_blog_id();
		}
		switch_to_blog( $source_blog );
		$url = admin_url( 'admin-post.php?action=sudbury_move_post' );
		restore_current_blog();

		return $url;
	}

	/**
	 * Render the Admin UI
	 */
	function admin_page() {
		if ( ! isset( $_REQUEST['post'] ) ) {
			$this->choose_post_page();
		} else {
			// Check nonce for security purposes
			check_admin_referer( 'sudbury-select-destination', 'sudbury-select-destination-nonce' );
			$post_id = absint( $_REQUEST['post'] );
			$this->choose_destination_page( get_post( $post_id ) );
		}
	}

	/**
	 * Choose the post to move
	 */
	protected function choose_post_page() {

		$posts = get_posts( array( 'post_type' => 'any', 'post_status' => 'any', 'posts_per_page' => - 1 ) );
		?>
		<h2>Choose a Post to Move</h2>
		<form method="get" action="">
			<?php wp_nonce_field( 'sudbury-select-destination', 'sudbury-select-destination-nonce' ); ?>
			<input type="hidden" name="page" value="sudbury-move-post">
			<?php $this->render_notices(); ?>
			<label for="post">
				<select id="post" name="post">
					<?php foreach ( $posts as $post ) : ?>
						<option value="<?php echo esc_attr( $post->ID ); ?>"> <?php echo esc_html( $post->post_title ) ?> <?php echo esc_html( $post->post_type ) ?> </option>
					<?php endforeach; ?>
				</select>
			</label>

			<?php submit_button( 'Next' ); ?>
		</form>
	<?php
	}

	/**
	 * Choose the destination blog
	 *
	 * @param WP_Post $post The Post
	 */
	protected function choose_destination_page( $post ) {
		$GLOBALS['override_all_blogs_please'] = true;
		$blogs                                = get_blogs_of_user( get_current_user_id() );
		unset( $GLOBALS['override_all_blogs_please'] );
		uasort( $blogs, function ( $a, $b ) {
			return $a->blogname > $b->blogname;
		} );
		?>
		<h2>Choose Destination</h2>

		<?php $this->render_notices(); ?>
		<div class="postbox" style="padding:10px;">
			<h3 class="hndle">Transfer <?php echo esc_html( get_post_type_object( $post->post_type )->labels->singular_name ); ?></h3>

			<div class="inner">
				<h2>
					<a href="<?php echo esc_url( get_post_permalink( $post->ID ) ); ?>"><?php echo esc_html( $post->post_title ); ?></a>
				</h2>

				<div style="float: left;">
					<?php get_the_post_thumbnail( $post->ID ); ?>
				</div>

				<p><b>Teaser:</b></p>
				<?php if ( $post->post_content && ! $post->post_excerpt ) { ?>
					<code style="display: block;"> <?php echo wp_trim_excerpt( $post->post_content ); ?></code>
				<?php } elseif ( $post->post_content ) { ?>
					<code style="display: block;"> <?php echo $post->post_excerpt; ?></code>
				<?php } else { ?>
					<code>No Teaser or Content</code>
				<?php } ?>

				<div class="clear"></div>
			</div>

		</div>
		<form method="post" action="<?php echo esc_url( $this->get_admin_post_link() ); ?>">
			<?php wp_nonce_field( 'sudbury-move-post', 'sudbury-move-post-nonce' ); ?>
			<?php wp_referer_field(); ?>
			<input type="hidden" name="post" value="<?php echo esc_attr( $post->ID ); ?>" />
			<label><b>Move To</b>
				<select name="destination">
					<option value="-1">-- Select a Department or Committee --</option>
					<?php foreach ( $blogs as $blog ) : ?>
						<?php if ( get_current_blog_id() == $blog->userblog_id ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<option value="<?php echo esc_attr( $blog->userblog_id ); ?>"><?php echo esc_html( $blog->blogname ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php submit_button( 'Move Post' ); ?>
		</form>

	<?php
	}

	/**
	 * Checks the authenticity of an admin-post request to move a post and verifies permissions
	 */
	function admin_post() {

		check_admin_referer( 'sudbury-move-post', 'sudbury-move-post-nonce' );

		if ( ! isset( $_POST['destination'] ) | ! isset( $_POST['post'] ) ) {
			$this->error( 'Destination or post missing' );
		}

		$destination = absint( $_POST['destination'] );
		if ( ! ( $destination > 0 ) ) {
			$this->error( 'Destination must be greater than 0' );
		}

		$post = absint( $_POST['post'] );
		if ( ! ( $post > 0 ) ) {
			$this->error( 'Post ID must be greater than 0' );
		}
		$post = get_post( $post );
		if ( ! $post || 'trash' == $post->post_status ) {
			$this->error( "That Post Does not Exist here or is in the trash" );
		}
		// Delete the Post on this blog
		if ( ! current_user_can( 'delete_post', $post->ID ) ) {
			$this->error( 'Access Denied: Cannot Delete Posts on this site' );
		}

		switch_to_blog( $destination );
		// On destination blog can they create posts

		if ( 'publish' == $post->post_status ) {
			if ( ! current_user_can( 'publish_posts' ) ) {
				restore_current_blog();

				$this->error( 'Access Denied: Will not be able to republish post on destination site' );
			}
		} else {
			if ( ! current_user_can( 'edit_posts' ) ) {
				restore_current_blog();

				$this->error( 'Access Denied: You cannot edit posts on destination site' );
			}
		}

		restore_current_blog();

		if ( $this->move_post( $post, $destination ) ) {
			$this->message( 'Moved Post to ' . get_blog_option( $this->destination, 'blogname' ) );
		}
	}

	/**
	 * Moves a Post.  Do not try this with attachments... you will hurt yourself
	 *
	 * @param WP_Post $post        The Post to move
	 * @param int     $destination The Destination Blog
	 *
	 * @return bool success/fail
	 */
	function move_post( $post, $destination ) {
		_sudbury_log( 'Moving Post ' . $post->ID . ' To ' . $destination );

		$old_id            = $post->ID;
		$this->destination = $destination;
		$this->source_blog = get_current_blog_id();
		$cats              = get_the_terms( $post->ID, 'category' );
		$tags              = get_the_terms( $post->ID, 'post_tag' );
		$comments          = get_comments( array( 'post_id' => $post->ID ) );
		$meta              = get_post_custom( $post->ID );

		switch_to_blog( $this->destination );

		// allow a natural ID on the new blog
		$post = (array) $post;
		unset( $post['ID'] );
		$post['post_category'] = $this->map_categories( $cats );
		$post['tags_input']    = $this->map_tags( $tags );
		$new_id                = wp_insert_post( $post );
		_sudbury_log( $meta );
		if ( $meta['_event_id'][0] ) {
			_sudbury_log( '[WARNING] Updating Linked Event' );
			$this->update_linked_event( $new_id, $meta['_event_id'][0] );
		}
		$this->move_comments( $new_id, $comments );
		$this->move_meta( $new_id, $meta );

		// Doing it ?p=ID style to allow the new blog to change permalinks
		$new_link  = trailingslashit( sudbury_get_the_site_url( $this->destination ) ) . '?p=' . $new_id;
		$edit_link = get_edit_post_link( $new_id, 'display' );
		add_post_meta( $new_id, '_sudbury_moving_history', $this->source_blog );

		restore_current_blog();

		sudbury_register_redirect( 'post', $old_id, $new_link );
		sudbury_register_redirect( 'direct', get_post_permalink( $old_id ), $new_link );

		wp_trash_post( $old_id );

		return true;
	}

	/**
	 * Inserts the Comments into the new post
	 *
	 * @param int   $new_post_id The new Post ID
	 * @param array $comments    AN array of comment objects returned by get_comments
	 */
	protected function move_comments( $new_post_id, $comments ) {
		foreach ( $comments as $comment ) {
			$comment->comment_post_ID = $new_post_id;

			wp_insert_comment( (array) $comment );
		}

		switch_to_blog( $this->source_blog );

		foreach ( $comments as $comment ) {
			wp_delete_comment( $comment->comment_ID, true );
		}

		restore_current_blog();
	}

	/**
	 * Inserts the meta into the new post
	 *
	 * @param int   $new_post_id The new post ID
	 * @param array $meta        All the Meta
	 */
	protected function move_meta( $new_post_id, $meta ) {
		foreach ( $meta as $key => $values ) {
			foreach ( $values as $value ) {
				add_post_meta( $new_post_id, $key, $value );
			}
		}

	}

	/**
	 * @param array $terms The List of Categories
	 *
	 * @return array The List of Term IDs
	 */
	protected function map_categories( $terms ) {
		return array_map( function ( $term ) {
			return $term->term_id;
		}, $terms );
	}

	/**
	 * @param array $terms The List of tags
	 *
	 * @return string The Comma separated List of Term IDs
	 */
	protected function map_tags( $terms ) {
		return implode( ',', array_map( function ( $term ) {
			return $term->term_id;
		}, $terms ) );
	}

	protected function update_linked_event( $new_post_id, $event_id ) {
		global $wpdb;

		$event = sudbury_get_event( $event_id, ARRAY_A );

		if ( ! $event ) {
			_sudbury_log( 'Could Not Find Event ' . $event_id );

			return false;
		}
		_sudbury_log( 'Updating Linked Event Info' );
		_sudbury_log( $result = $wpdb->update( $wpdb->base_prefix . 'em_events', array(
			'post_id' => $new_post_id,
			'blog_id' => get_current_blog_id()
		), array( 'event_id' => $event_id ), '%d' ) );

		return $result;

	}

	/**
	 * Quit admin-post with the specified Error Message
	 *
	 * @param string $message The Error Message
	 * @param bool   $die     Whether to die or not
	 */
	function error( $message, $die = true ) {
		$this->add_notice( $message, 'error', $die );
	}

	/**
	 * Quit admin-post with the specified Updated Message
	 *
	 * @param string $message The Error Message
	 * @param bool   $die     Whether to die or not
	 */
	function message( $message, $die = true ) {
		$this->add_notice( $message, 'updated', $die );
	}

	/**
	 * Adds a notice and kills the admin-post request if $die is set to true
	 *
	 * @param string $message The message to show to the user... can be HTML
	 * @param string $type    'updated' or 'error'
	 * @param bool   $die     Whether to quit or not (default true)
	 */
	function add_notice( $message, $type, $die = true ) {
		$url = wp_get_referer();
		$url = remove_query_arg( $type . 's', $url );
		$url = add_query_arg( array( $type . 's' => array( urlencode( $message ) ) ), $url );

		wp_redirect( $url, 302 );
		if ( $die ) {
			exit;
		}
	}

	/**
	 * Renders the notices
	 */
	function render_notices() {
		if ( isset( $_REQUEST['updateds'] ) ) {
			foreach ( $_REQUEST['updateds'] as $message ) {
				?>
				<div class="updated">
					<p><?php echo wp_unslash( $message ); ?></p>
				</div>
			<?php
			}
		}
		if ( isset( $_REQUEST['errors'] ) ) {
			foreach ( $_REQUEST['errors'] as $error ) {
				?>
				<div class="error">
					<p><?php echo wp_unslash( $error ); ?></p>
				</div>
			<?php
			}
		}
	}
}


$sudbury_mover = new Sudbury_Mover();
