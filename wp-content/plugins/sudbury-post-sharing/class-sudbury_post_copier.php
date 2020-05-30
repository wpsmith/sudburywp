<?php

/**
 * Class Sudbury_Post_Copier
 * Copies Posts and Pages from one blog to another
 *
 * @author     Eddie Hurtig <hurtige@ccs.neu.edu>
 * @package    Sudbury
 * @subpackage Multisite
 */
class Sudbury_Post_Copier {

	private $page_slug;
	private $source_blog_id;
	private $source_post_id;

	private $target_blog_id;

	private $target_post_id;

	/**
	 * Constructor
	 */
	function __construct( $post_id, $blog_id = false ) {
		$this->source_post_id = $post_id;
		$this->source_blog_id = $blog_id;
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
	 * Moves a Post.  Do not try this with attachments... you will hurt yourself
	 *
	 * @param int      $target_blog_id The Destination Blog
	 * @param bool|int $post_id        The Post to move
	 *
	 * @return int|WP_Error ID of the New Post
	 */
	function copy( $target_blog_id, $post_id = false ) {
		_sudbury_log( 'Copying Post ' . $this->source_post_id . ' To ' . $target_blog_id );

		switch_to_blog( $this->source_blog_id );


		$this->target_blog_id = $target_blog_id;
		$this->target_post_id = $post_id;

		$comments = get_comments( array( 'post_id' => $this->source_post_id ) );
		$meta     = get_post_custom( $this->source_post_id );


		// allow a natural ID on the new blog
		$post = (array) get_post( $this->source_post_id );

		$terms      = array();
		$taxonomies = get_object_taxonomies( $post );
		foreach ( $taxonomies as $taxonomy ) {
			$terms[ $taxonomy ] = wp_get_object_terms( $this->source_post_id, $taxonomy, array( 'fields' => 'ids' ) );
		}


		switch_to_blog( $this->target_blog_id );


		unset( $post['ID'] );

//		$post['post_category'] = $this->map_categories( $cats );
//		$post['tags_input']    = $this->map_tags( $tags );
		$old_status = '';

		if ( false === $this->target_post_id ) {
			$old_status          = $post['post_status'];
			$post['post_status'] = 'new';
			$new_id              = wp_insert_post( $post );
			_sudbury_log( "[COPIER] Inserted new post $new_id on {$this->target_blog_id}" );

		} else {
			$post['ID'] = $this->target_post_id;
			wp_update_post( $post );
			$new_id = $post_id;
			_sudbury_log( "[COPIER] Updated post $new_id on {$this->target_blog_id}" );
		}

		foreach ( $terms as $taxonomy => $_terms ) {
			$this->copy_terms( $new_id, $_terms, $taxonomy );
		}
		$this->copy_comments( $new_id, $comments );
		$this->copy_meta( $new_id, $meta );

		// Doing it ?p=ID style to allow the new blog to change permalinks
		$new_link  = site_url( '/?p=' . $new_id );
		$edit_link = get_edit_post_link( $new_id, 'display' );

		add_post_meta( $new_id, '_sudbury_copied_from', array(
			'ID'      => $this->source_post_id,
			'BLOG_ID' => $this->source_blog_id
		) );

		if ( $old_status ) {
			wp_update_post( array( 'ID' => $new_id, 'post_status' => $old_status ) );
		}

		restore_current_blog(); // Going back to source blog

		restore_current_blog(); // Going back to calling Blog

		return $new_id;
	}

	function update( $destination_blog, $post_id ) {
		$this->clear( $destination_blog, $post_id );
		$this->copy( $destination_blog, $post_id );
	}

	/**
	 * Clears a post of all its junk (deletes all meta and comments)
	 */
	function clear( $blog_id, $post_id ) {
		switch_to_blog( $blog_id );
		_sudbury_log( "[COPIER] Clearing Post $post_id on blog $blog_id" );

		$post = get_post( $post_id );

		if ( ! $post ) {
			_sudbury_log( "Failed to get post with ID $post_id on $blog_id.  Clear operation failed" );
			restore_current_blog();

			return;
		}
		$comments = get_comments( array( 'post_id' => $post_id ) );
		foreach ( $comments as $comment ) {
			_sudbury_log( "[COPIER] Deleting Comment {$comment->comment_ID} for post $post_id on blog $blog_id" );

			wp_delete_comment( $comment->comment_ID, true );
		}

		$meta = get_post_meta( $post_id );

		foreach ( $meta as $key => $val ) {
			_sudbury_log( "[COPIER] Deleting Post Meta {$key} for post $post_id on blog $blog_id" );

			delete_post_meta( $post_id, $key );
		}

		$taxonomies = get_object_taxonomies( $post );

		foreach ( $taxonomies as $taxonomy ) {
			_sudbury_log( "[COPIER] Removing Term relationships in taxonomy {$taxonomy} for post $post_id on blog $blog_id" );

			wp_set_object_terms( $post_id, array(), $taxonomy, false );
		}

		_sudbury_log( "[COPIER] Cleared $post_id on blog $blog_id" );

		restore_current_blog();
	}

	/**
	 * Inserts the Comments into the new post
	 *
	 * @param int   $new_post_id The new Post ID
	 * @param array $comments    AN array of comment objects returned by get_comments
	 */
	protected function copy_comments( $new_post_id, $comments ) {
		_sudbury_log( "[COPIER] Comments to copy to $new_post_id" );
		_sudbury_log( $comments );

		foreach ( $comments as $comment ) {
			$comment->comment_post_ID = $new_post_id;

			wp_insert_comment( (array) $comment );
		}

		switch_to_blog( $this->source_blog_id );

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
	protected function copy_meta( $new_post_id, $meta ) {
		_sudbury_log( "[COPIER] Copying Meta to $new_post_id" );

		foreach ( $meta as $key => $values ) {
			$new_key = apply_filters( 'sudbury_copy_meta', $key, $values, $new_post_id );
			if ( false !== $new_key ) {
				foreach ( $values as $value ) {
					_sudbury_log( "[COPIER] Adding Meta '$new_key' as '$value'" );
					add_post_meta( $new_post_id, $new_key, $value );
				}
			} else {
				_sudbury_log( "[COPIER] Skipping Meta '$key'" );
			}
		}
	}

	/**
	 * Inserts the meta into the new post
	 *
	 * @param int   $new_post_id The new post ID
	 * @param array $terms       List of term IDs
	 */
	protected function copy_terms( $new_post_id, $terms, $taxonomy ) {
		_sudbury_log( "[COPIER] Copying $taxonomy terms to $new_post_id" );
		_sudbury_log( $terms );
		wp_set_object_terms( $new_post_id, $terms, $taxonomy, false );
	}

	/**
	 * @param array $terms The List of Categories
	 *
	 * @return array The List of Term IDs
	 */
	protected function map_categories( $terms ) {
		if ( ! is_array( $terms ) ) {
			return array();
		}

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
		if ( ! is_array( $terms ) ) {
			return array();
		}

		return implode( ',', array_map( function ( $term ) {
			return $term->term_id;
		}, $terms ) );
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