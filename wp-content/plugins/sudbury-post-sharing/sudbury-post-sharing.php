<?php
/*
Plugin Name: Sudbury Post Sharing
Plugin URI: http://sudbury.ma.us/
Description: Adds sharing capabilities to documents
Version: 1.0
Author: Eddie Hurtig
Author URI: http://hurtigtechnologies.com
Network: True
*/
/**
 * Meta Storage in sudbury_shared_posts meta field
 *
 * array (
 *      '<BLOG_ID>-<ID>' => array (
 *          'ID'      =>  <ID of the Post>
 *          'BLOG_ID' =>  <The ID of the Blog>
 *          'type'    => 'root' | 'guest'
 *          'status'  => '<post_status>'
 *      ),
 *      ...
 *      ...
 * )
 *
 *
 *
 *
 */

require_once 'class-sudbury_sharing_admin.php';
require_once 'class-sudbury_post_copier.php';
require_once 'class-sudbury_shared_posts_extra.php';

/**
 * Allows for posts to be shared between departments
 */
class Sudbury_Sharing {

	/**
	 * @var array The Post types that can be shared
	 */
	private $post_types = array();

	/**
	 * @var string
	 */
	private $posts_meta_key = 'sudbury_shared_posts';

	/**
	 * @var array
	 */
	private $publish_stati = array( 'publish', 'public-archive' );

	/**
	 * @var Sudbury_Sharing_Admin
	 */
	private $admin;

	/**
	 * @var array
	 */
	private $already_processed = array();

	/**
	 * The current Post being processed (Key/Value pair of Blog ID to Post ID)
	 * @var array
	 */
	private $proccessing = array();


	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
		$this->post_types = get_site_option( 'sudbury_sharing_post_types', array( 'attachment', 'post' ) );
		$this->admin      = new Sudbury_Sharing_Admin( $this );
	}

	/**
	 * Handles Action Hooks
	 */
	function init() {

		// Whenever a post is added
		//add_action( 'publish_post', array( &$this, 'publish' ) );

		// Whenever a post is saved: if draft then just save the meta for guest posts, if publishing for the first time then create the guest posts if updating a published post, then update the guest posts
		add_action( 'wp_insert_post', array( &$this, 'save' ), 9999, 1 );
		add_action( 'edit_attachment', array( &$this, 'save' ), 9999, 1 );
		add_action( 'add_attachment', array( &$this, 'save' ), 9999, 1 );

		// If Trashing the root post, trash the guest posts
		// If Trashing a guest post, then do nothing
		add_action( 'wp_trash_post', array( &$this, 'trash' ) );

		// If deleting a root post, delete all guest posts
		// if deleting a guest post then delete the pointer on the root and other guest posts
		add_action( 'before_delete_post', array( &$this, 'delete' ) );

		// Register Admin Init Callback
		add_action( 'admin_init', array( &$this->admin, 'admin_init' ) );

		// Correct Attachment URLS and Paths to the file that is located on the root blog
		add_filter( 'wp_get_attachment_url', array( &$this, 'get_attachment_url' ), 50, 2 );
		add_filter( 'get_attached_file', array( &$this, 'get_attached_file' ), 50, 2 );

		// If deleting a guest attachment, need to stop WordPress from deleting the actual file that
		// lives on the root blog
		add_action( 'delete_attachment', array( &$this, 'delete' ), 1 );
		add_action( 'delete_attachment', array( &$this, 'save_attachment_from_deletion' ), 10 );

		add_action( 'wp_get_attachment_url', array( &$this, 'wp_get_attachment_url' ), 5, 2 );

	}

	/**
	 * Admin Post Page Save.  Saves the options for the post.  If they check sites to share with, but save as a draft then we don't publish yet
	 *
	 * @param      $root_post_id
	 * @param bool $blog_id
	 */
	function save( $root_post_id, $blog_id = false ) {
		if ( false !== $blog_id ) {
			switch_to_blog( $blog_id );
		}

		// Way to much resources to create the guests for revisions or autosaves.
		if ( wp_is_post_autosave( $root_post_id ) || wp_is_post_revision( $root_post_id ) ) {
			return;
		}

		// We only care about wp_insert_post calls to the post that was submitted
		if ( ! isset( $_REQUEST['post_ID'] ) || $root_post_id != $_REQUEST['post_ID'] ) {
			if ( false !== $blog_id ) {
				restore_current_blog();
			}
			_sudbury_log( "[DEBUG] [SHARING] [SAVE] Called with post $root_post_id which != the request's post ID" );

			return;
		}
		_sudbury_log( '[DEBUG] [SHARING] [SAVE] Called Save' );

		// The user somehow got to the edit field for a guest post
		if ( $this->is_guest( $root_post_id, $blog_id ) ) {
			if ( false !== $blog_id ) {
				restore_current_blog();
			}
			_sudbury_log( '[DEBUG] [SHARING] [SAVE] Save Called On Guest Post... quiting' );

			return;
		}

		if ( $this->proccessing ) {
			_sudbury_log( 'Already Processing a Post: ' . key( $this->proccessing ) . ' ' . current( $this->proccessing ) );
		}

		if ( in_array( $root_post_id, $this->already_processed ) ) {
			_sudbury_log( 'Already Processed Post ' . $root_post_id );
		}


		if ( ! isset( $_REQUEST[ $this->posts_meta_key ] ) ) {

			// wp_insert_post was still called on this root post so we should push out any changes.
			//   Examples of this occurring is scheduled posts
			if ( $this->is_root( $root_post_id ) ) {
				_sudbury_log( '[DEBUG] [SHARING] [SAVE] Save Called On Root Post, already root post and request var missing... performing strait update' );

				$this->update_guests( $root_post_id );
			}

			// Now quit
			_sudbury_log( '[DEBUG] [SHARING] [SAVE] Quiting' );

			if ( false !== $blog_id ) {
				restore_current_blog();
			}

			return;
		}

		/* We are going to start analysing the post that has been saved to the database for possible sharing changes
		   Then we will Delete any removed sharing relationships
		   Following that we will Create any newly added guests that don't exist.
		   And finally we will update any existing guests                                                           */

		/* Examples of this situation are:
				The User MUST have clicked the Publish/Update button on a post that supports sharing. And there must
				Have been the sharing box available to them to select from.  That is the only time the following code
				should run
		*/

		// This post is (or is going to be) the root post, so grab the current blog ID
		$root_blog_id = get_current_blog_id();

		// This prevents any subsequent calls to the `wp_insert_post` action from triggering the sharing relationships
		$this->proccessing = array( $this->parse_blog_id( $blog_id ) => $root_post_id );

		// The Raw list of blogs that should have guest posts as selected by the user
		$shared_posts_raw = array_filter( $_REQUEST[ $this->posts_meta_key ], function ( $bid ) {
			return $bid > 0;
		} );

		// Prevent future calls to this function from getting this far
		unset( $_REQUEST[ $this->posts_meta_key ] );

		// They can't publish posts on this site... Leave
		if ( ! current_user_can( 'publish_posts' ) ) {
			if ( false !== $blog_id ) {
				restore_current_blog();
			}
			_sudbury_log( '[DEBUG] [SHARING] [SAVE] Cannot publish, leave' );

			return;
		}
		_sudbury_log( '[DEBUG] [SHARING] [SAVE] Attempting Save' );

		// Generate the Meta for all the new shared posts
		$selected_blogs = array( get_current_blog_id() );
		foreach ( $shared_posts_raw as $b ) {

			switch_to_blog( (int) $b );
			if ( ! current_user_can( 'publish_posts' ) ) {

				_sudbury_log( "[DEBUG] [SHARING] [SAVE] Cannot publish on $b" );

				restore_current_blog();

				return;
			}
			$selected_blogs[] = $b;

			restore_current_blog();
		}

		// The already existing guests
		$existing_shared_posts = array();

		if ( $this->is_shared( $root_post_id, $blog_id ) ) {
			$existing_shared_posts = $this->get_guest_posts( $root_post_id, $blog_id );
		}

		$existing_blogs = wp_list_pluck( $existing_shared_posts, 'BLOG_ID' );


		// Delete posts from blogs that were unchecked
		foreach ( $existing_shared_posts as $existing ) {
			// Check if the exiting Post should be kept
			if ( ! in_array( $existing['BLOG_ID'], $selected_blogs ) ) {
				switch_to_blog( $existing['BLOG_ID'] );
				_sudbury_log( '[DEBUG] [SHARING] [SAVE] Deleting Post ' . $existing['ID'] . ' on blog ' . get_bloginfo( 'name' ) );
				if ( 'attachment' == get_post_type( $existing['ID'] ) ) {
					wp_delete_attachment( $existing['ID'], true );
				} else {
					wp_delete_post( $existing['ID'], true );
				}
				restore_current_blog();
			}
		}


		// The Guest posts that the User Selected
		$selected_shared_posts = array();

		// Save to the root post (This Post)
		$selected_shared_posts["$root_blog_id-$root_post_id"] = array(
			'ID'      => $root_post_id,
			'BLOG_ID' => get_current_blog_id(),
			'type'    => 'root'
		);

		// Add posts to blogs that were newly checked
		foreach ( $selected_blogs as $selected_blog ) {
			if ( $selected_blog == $root_blog_id ) {
				continue;
			}

			// Create a new copier with the root post as it's source.  Create this each time (even though we don't have to)
			$copier = new Sudbury_Post_Copier( $root_post_id, $root_blog_id );

			// Check if there is an existing post that just needs to be updated
			if ( in_array( $selected_blog, $existing_blogs ) ) {
				// Get the ID of the post on $selected blog to update
				$existing_post = current( wp_list_filter( $existing_shared_posts, array( 'BLOG_ID' => $selected_blog ) ) );
				$existing_post_id = $existing_post['ID'];
				if ( is_null( $existing_post_id ) ) {
					_sudbury_log( 'Your Classic THIS SHOULD NEVER HAPPEN' );
					wp_die( 'THIS SHOULD NEVER HAPPEN ERROR in ' . __FUNCTION__ );
				} else {
					// Update that post instead of creating a new one
					$copier->update( $selected_blog, $existing_post_id );

					// Add the existing post to the list of shared posts
					$selected_shared_posts["$selected_blog-$existing_post_id"] = array(
						'ID'      => $existing_post_id,
						'BLOG_ID' => $selected_blog,
						'type'    => 'guest',
					);
				}
			} else {
				// There is no guest post on the destination blog... We have to create a new one
				$id = $copier->copy( $selected_blog );

				// Add the new post to the list of shared posts
				$selected_shared_posts["$selected_blog-$id"] = array(
					'ID'      => $id,
					'BLOG_ID' => $selected_blog,
					'type'    => 'guest',
				);
			}
		}

		// If there is more than 1 post (more than to root post) selected, then we have a network to create
		if ( count( $selected_shared_posts ) > 1 ) {
			// We have built the list of posts that are guests of the root here... now lets distribute it.
			$new_post_list = $this->redistribute_new_post_list( $selected_shared_posts );
			if ( false === $new_post_list ) {
				// Error
			}
		} else {
			// There is no network in play, delete the meta from the current post in case there is any
			delete_post_meta( $root_post_id, $this->posts_meta_key );
		}

		// Restore the blog is we had switched
		if ( false !== $blog_id ) {
			restore_current_blog();
		}

		// Record that we have processed this and move on
		$this->already_processed = array_merge( $this->already_processed, $this->proccessing );
		$this->proccessing       = array();
	}

	/**
	 * Updates all the guest posts of the given root post
	 *
	 * @param      $root_post_id
	 * @param bool $blog_id
	 */
	function update_guests( $root_post_id, $blog_id = false ) {
		if ( false !== $blog_id ) {
			switch_to_blog( $blog_id );
		}
		$post_list = $this->get_shared_posts( $root_post_id, $this->parse_blog_id( $blog_id ) );
		$copier    = new Sudbury_Post_Copier( $root_post_id, $this->parse_blog_id( $blog_id ) );

		$posts = $this->get_guest_posts( $root_post_id, $blog_id );
		foreach ( $posts as $post ) {
			$copier->update( $post['BLOG_ID'], $post['ID'] );
		}

		// the call to $copier->update will clear out all the post metadata on guest posts, including the sharing info
		// we must call redistribute post list after calling update to save the post lists.
		$this->redistribute_new_post_list( $post_list );

		if ( false !== $blog_id ) {
			restore_current_blog();
		}
	}

	/**
	 * @param      $post_id
	 * @param bool $blog_id
	 */
	function trash( $post_id, $blog_id = false ) {
		$blog_id = $this->parse_blog_id( $blog_id );

		if ( $this->is_root( $post_id, $blog_id ) ) {
			$guests = $this->get_guest_posts( $post_id, $blog_id );
			foreach ( $guests as $key => $guest ) {

				$guest = apply_filters( 'sudbury_sharing_trash_guest_post', $guest, $post_id, $blog_id );

				// Allow cancellation
				if ( false === $guest ) {
					continue;
				}

				switch_to_blog( $guest['BLOG_ID'] );
				wp_trash_post( $guest['ID'] );
				restore_current_blog();


				do_action( 'sudbury_sharing_trashed_guest_post', $guest, $post_id, $blog_id );
			}
		} elseif ( $this->is_guest( $post_id, $blog_id ) ) {
			_sudbury_log( "[INFO] [SHARING] [TRASH] A guest post was just trashed: Post $post_id on Blog $blog_id" );
		}
	}

	function delete( $post_id, $blog_id = false ) {
		$blog_id = $this->parse_blog_id( $blog_id );

		if ( $this->is_root( $post_id, $blog_id ) ) {
			$guests = $this->get_guest_posts( $post_id, $blog_id );
			foreach ( $guests as $key => $guest ) {

				$guest = apply_filters( 'sudbury_sharing_delete_guest_post', $guest, $post_id, $blog_id );

				// Allow cancellation
				if ( false === $guest ) {
					continue;
				}

				switch_to_blog( $guest['BLOG_ID'] );
				wp_delete_post( $guest['ID'], true );
				restore_current_blog();
			}
		} elseif ( $this->is_guest( $post_id, $blog_id ) ) {
			// Get the Post List and Remove this guest post from it
			$new_post_list = $this->filter_post_list( $this->get_shared_posts( $post_id, $blog_id ), $post_id, $blog_id );

			// Now save the guest post list to all the guests
			$new_post_list = $this->redistribute_new_post_list( $new_post_list );
			// If there are no more guest posts shared... then remove the "root post" designation on the root post
			if ( is_array( $new_post_list ) && empty( $new_post_list ) ) {
				$root = $this->get_root_post( $post_id );
				switch_to_blog( $root['BLOG_ID'] );
				delete_post_meta( $root['ID'], $this->posts_meta_key );
				restore_current_blog();
			}
			delete_post_meta( $post_id, $this->posts_meta_key );

		}
	}

	/* HELPERS */

	/**
	 * Determines if the given post is either a guest or root post that is in the sharing system
	 *
	 * @param int|WP_Post $post    The Post to check
	 * @param bool        $blog_id (optional) Blog ID that the post lives on
	 *
	 * @return bool Whether it is either a root or guest post
	 */
	public function is_shared( $post, $blog_id = false ) {
		$posts = $this->get_shared_posts( $post, $blog_id );

		return ! empty( $posts );
	}

	/**
	 * Determines if the post is a root post.  It may or may not have guests depending on whether it's guests were deleted
	 *
	 * @param int|WP_Post $post    The Post to check
	 * @param bool        $blog_id (optional) Blog ID that the post lives on
	 *
	 * @return bool Whether it is a root post
	 */
	public function is_root( $post, $blog_id = false ) {
		if ( $this->is_shared( $post, $blog_id ) ) {
			$blog_id = $this->parse_blog_id( $blog_id );
			$post_id = $this->post_to_id( $post );

			return ( 'root' == $this->get_shared_posts( $post, $blog_id )["$blog_id-$post_id"]['type'] );
		}

		return false;
	}

	/**
	 * Determines if the Post is a guest post from another site
	 *
	 * @param int|WP_Post $post    The Post to check
	 * @param bool        $blog_id (optional) Blog ID that the post lives on
	 *
	 * @return bool Whether it is a guest post
	 */
	public function is_guest( $post, $blog_id = false ) {
		if ( $this->is_shared( $post, $blog_id ) ) {
			$blog_id = $this->parse_blog_id( $blog_id );
			$post_id = $this->post_to_id( $post );

			$meta = $this->get_shared_posts( $post, $blog_id );

			if ( $meta === '' ) {
				return false;
			}

			return ( isset( $meta["$blog_id-$post_id"] ) && isset( $meta["$blog_id-$post_id"]['type'] ) && 'guest' == $meta["$blog_id-$post_id"]['type'] );
		}

		return false;
	}

	/**
	 * Gets a List of all the posts that are shared with the given post, including the Root post and the Given Post
	 */
	public function get_shared_posts( $post, $blog_id = false ) {
		// Get the List of Posts that are duplicates of this post (this post is included in list)
		return $this->get_post_meta( $post, $this->posts_meta_key, true, $blog_id );
	}

	/**
	 * Returns all the posts except the root post
	 *
	 * @param int|WP_Post $post The Post to get the root post of
	 *
	 *
	 * @return array
	 */
	public function get_root_post( $post, $blog_id = false ) {
		$posts = $this->get_shared_posts( $post, $blog_id );
		$posts = $this->remove_guest_posts( $posts );

		if ( count( $posts ) == 1 ) {
			return current( $posts );
		}

		return array();
	}

	/**
	 * Gets the guest posts of the given post
	 *
	 * @param int|WP_Post $post    The Post
	 * @param bool        $blog_id The Blog ID, if false then assumes current blog id
	 *
	 * @return array AN Array of the Guest Posts
	 */
	public function get_guest_posts( $post, $blog_id = false ) {
		$posts = $this->get_shared_posts( $post, $blog_id );
		$posts = $this->remove_root_post( $posts, $post, $blog_id );

		return $posts;
	}


	/**
	 * Takes a new List of Posts and Distributes it to all Posts in the System
	 *
	 * @param array $new_post_list
	 *
	 * @return array|bool The Post List saved or false if the post list was not valid
	 */
	private function redistribute_new_post_list( $new_post_list ) {
		$new_post_list = $this->validate_post_list( $new_post_list );
		if ( false === $new_post_list ) {
			return false;
		}
		foreach ( $new_post_list as $post ) {
			switch_to_blog( $post['BLOG_ID'] );
			update_post_meta( $post['ID'], $this->posts_meta_key, $new_post_list );
			_sudbury_log( "[SHARING] Saved {$this->posts_meta_key} meta to {$post['ID']} on {$post['BLOG_ID']}" );
			restore_current_blog();
		}

		return $new_post_list;
	}

	/**
	 * Takes a new List of Posts and Distributes it to all Posts in the System
	 *
	 * @param $new_post_list
	 *
	 * @return array|bool The Post List to be saved or false if the post list was not valid
	 */
	private function validate_post_list( $new_post_list ) {
		_sudbury_log( '[SHARING] [DEBUG] Validating the following proposed Post List' );
		_sudbury_log( $new_post_list );
		if ( ! is_array( $new_post_list ) ) {
			_sudbury_log( '[SHARING] [ERROR] Tried to validate a post sharing network that was not an array' );

			return false;
		}
		$only_root = $this->remove_guest_posts( $new_post_list );
		_sudbury_log( $only_root );
		if ( empty( $only_root ) && ! empty( $new_post_list ) ) {
			_sudbury_log( '[SHARING] [ERROR] Tried to validate a post sharing network that didn\'t have a root post' );

			return false;
		}
		if ( count( $only_root ) > 1 ) {
			_sudbury_log( '[SHARING] [ERROR] Tried to validate a post sharing network that has multiple root posts' );

			return false;
		}
		// If the only post in the network is the root post... then there is no sharing going on and it isn't a network
		if ( count( $only_root ) == 1 && count( $new_post_list ) == 1 ) {
			_sudbury_log( '[SHARING] [ERROR] Sharing network that only has a root post, returning empty array' );

			return array();
		}
		_sudbury_log( '[SHARING] [DEBUG] Validation of Sharing Network Succeeded without any modifications' );

		return $new_post_list;
	}

	/**
	 * Wrapper for get_post_meta that gets meta from a post on the specified blog
	 *
	 * @param        $post
	 * @param string $key
	 * @param bool   $single
	 * @param bool   $blog_id
	 *
	 * @return mixed The Post Meta
	 */
	private function get_post_meta( $post, $key = '', $single = false, $blog_id = false ) {
		if ( false !== $blog_id ) {
			switch_to_blog( $blog_id );
		}
		$post_id = $this->post_to_id( $post );
		$meta    = get_post_meta( $post_id, $key, $single );
		if ( false !== $blog_id ) {
			restore_current_blog();
		}

		return $meta;
	}

	/**
	 * Removes the root post from the given $posts list
	 *
	 * @param $posts array The List of posts from Meta
	 *
	 * @return array
	 */
	private function remove_root_post( $posts ) {
		return array_filter( $posts, function ( $p ) {
			return ! ( 'root' === $p['type'] );
		} );
	}


	/**
	 * Returns all the posts except the root post
	 *
	 * @param $posts array The List of posts from Meta
	 *
	 * @return array
	 */
	public function remove_guest_posts( $posts ) {
		return array_filter( $posts, function ( $p ) {
			return ( 'guest' !== $p['type'] );
		} );
	}

	/**
	 * Takes a List of Posts (As stored in the sudbury_sharing_posts meta field) and removes the given post from the list
	 *
	 * @param $posts
	 * @param $post
	 * @param $blog_id
	 *
	 * @return array
	 */
	private function filter_post_list( $posts, $post, $blog_id ) {
		if ( is_object( $post ) ) {
			$post = $post->ID;
		}
		if ( false === $blog_id ) {
			$blog_id = get_current_blog_id();
		}

		return array_filter( $posts, function ( $p ) use ( $post, $blog_id ) {
			return ! ( $p['ID'] == $post && $p['BLOG_ID'] == $blog_id );
		} );
	}

	/**
	 * Takes the Blog ID or False and converts it to a blog ID
	 *
	 * @param false|int $blog_id The Blog ID or false (meaning the current blog id)
	 *
	 * @return int The Blog ID
	 */
	private function parse_blog_id( $blog_id ) {
		return ( false === $blog_id ? get_current_blog_id() : $blog_id );
	}

	/**
	 * Takes a WP_Post or a Post ID and returns the ID of the Post
	 *
	 * @param int|WP_Post $post The Post
	 *
	 * @return int The Post ID
	 */
	private function post_to_id( $post ) {
		return ( is_object( $post ) ? $post->ID : $post );
	}

	public function get_post_types() {
		return $this->post_types;
	}

	public function get_post_meta_key() {
		return $this->posts_meta_key;
	}

	/**
	 * Gets the attachment url for a shared attachment
	 *
	 * @param string $src           The Url where WordPress thinks that the attachment is located
	 * @param int    $attachment_id The ID of the attachment
	 *
	 * @return string The url to the global media attachment file (The original Uploaded Location)
	 */
	public function get_attachment_url( $src, $attachment_id ) {
		if ( $this->is_guest( $attachment_id ) ) {
			$root = $this->get_root_post( $attachment_id );

			switch_to_blog( $root['BLOG_ID'] );
			$return = wp_get_attachment_url( $root['ID'] );
			restore_current_blog();

			return $return;
		}

		return $src;
	}

	/**
	 * Gets the attachment file path for a shared attachment
	 *
	 * @param string $path          The Path where WordPress thinks that the attachment is located
	 * @param int    $attachment_id The ID of the attachment
	 *
	 * @return string The path to the global media attachment file (The original Uploaded Location)
	 */
	public function get_attached_file( $path, $attachment_id ) {

		if ( $this->is_guest( $attachment_id ) ) {
			$root = $this->get_root_post( $attachment_id );

			switch_to_blog( $root['BLOG_ID'] );
			$return = get_attached_file( $root['ID'] );
			restore_current_blog();

			return $return;
		}

		return $path;
	}

	/**
	 * Deletes the specified Attachment From the WordPress database but keeps the files.  It also trolls WordPress a bit :-)
	 *
	 * @param int $attachment_id The attachment ID to delete
	 *
	 * @return mixed False on failure. Post data on success.
	 */
	function save_attachment_from_deletion( $attachment_id ) {
		// Dop the regular deletion stuff (update other posts if this is a guest... delete everthying if it is a root)
		if ( $this->is_guest( $attachment_id ) ) {
			// We are going to totally troll WordPress :-)
			d( 'SHARING STOPPING DELETION OF ' . $attachment_id );
			if ( ! isset( $GLOBALS['sudbury_sharing_dont_delete'] ) ) {
				$GLOBALS['sudbury_sharing_dont_delete'] = array();
			}
			$meta         = wp_get_attachment_metadata( $attachment_id );
			$backup_sizes = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true );
			$file         = get_attached_file( $attachment_id );

			if ( ! empty( $meta['thumb'] ) ) {
				$thumbfile                                = str_replace( basename( $file ), $meta['thumb'], $file );
				$GLOBALS['sudbury_sharing_dont_delete'][] = $thumbfile;
			}

			// Remove intermediate and backup images if there are any.
			if ( isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $size => $sizeinfo ) {
					$intermediate_file                        = str_replace( basename( $file ), $sizeinfo['file'], $file );
					$GLOBALS['sudbury_sharing_dont_delete'][] = $intermediate_file;
				}
			}

			if ( is_array( $backup_sizes ) ) {
				foreach ( $backup_sizes as $size ) {
					$del_file = path_join( dirname( $meta['file'] ), $size['file'] );
					/** This filter is documented in wp-admin/custom-header.php */
					$GLOBALS['sudbury_sharing_dont_delete'][] = $del_file;
				}
			}

			$GLOBALS['sudbury_sharing_dont_delete'][] = $file;
		}
	}

	/**
	 * Returns gibberish String... not cryptographically secure though
	 *
	 * @param string $salt A salt to use in the gibberish making
	 *
	 * @return string Gibberish of length 32
	 */
	function __return_gibberish( $salt = AUTH_SALT ) {
		return wp_hash( $salt . time() );
	}

	function wp_get_attachment_url( $url, $attachment_id ) {

		if ( $copied = get_post_meta( $attachment_id, '_sudbury_copied_from', true ) ) {

			if ( $copied['BLOG_ID'] && $copied['BLOG_ID'] != get_current_blog_id() ) {
				$blog_id = get_current_blog_id();

				return str_replace( "/sites/{$blog_id}", "/sites/{$copied['BLOG_ID']}", $url );
			}
		}

		return $url;
	}

}

$GLOBALS['sudbury_sharing'] = new Sudbury_Sharing();

/**
 * Determines if the given post is part of a sharing network
 *
 * @param int|WP_Post $post    The Post to check
 * @param int|bool    $blog_id The Blog ID that the post Lives on. Default is current blog
 *
 * @return bool Whether the post is shared or not
 */
function sudbury_sharing_is_shared( $post, $blog_id = false ) {
	return $GLOBALS['sudbury_sharing']->is_shared( $post, $blog_id );
}

/**
 * Determines if the given post is a root post in a sharing network
 *
 * @param int|WP_Post $post    The Post to check
 * @param int|bool    $blog_id The Blog ID that the post Lives on. Default is current blog
 *
 * @return bool Whether the post is a root post or not
 */
function sudbury_sharing_is_root_post( $post, $blog_id = false ) {
	return $GLOBALS['sudbury_sharing']->is_root( $post, $blog_id );
}

/**
 * Determines if the given post is a guest post in a sharing network
 *
 * @param int|WP_Post $post    The Post to check
 * @param int|bool    $blog_id The Blog ID that the post Lives on. Default is current blog
 *
 * @return bool Whether the post is a guest post or not
 */
function sudbury_sharing_is_guest_post( $post, $blog_id = false ) {
	return $GLOBALS['sudbury_sharing']->is_guest( $post, $blog_id );
}

/**
 * Gets all the posts in a sharing network
 *
 * @param int|WP_Post $post    The Post to check
 * @param int|bool    $blog_id The Blog ID that the post Lives on. Default is current blog
 *
 * @example sudbury_sharing_get_shared_posts( $some_post_id );
 *          yields:
 *             array(
 *                 array( 'ID' => 123, 'BLOG_ID' => 12, 'type' => 'root' ),
 *                  array( 'ID' => 812, 'BLOG_ID' => 22, 'type' => 'guest' ),
 *                  array( 'ID' => 674, 'BLOG_ID' => 24, 'type' => 'guest' ),
 *                  array( 'ID' => 233, 'BLOG_ID' => 2, 'type' => 'guest' )
 *             )
 *
 * @return array An array of post pointers of posts in the sharing network
 */
function sudbury_sharing_get_shared_posts( $post, $blog_id = false ) {
	return $GLOBALS['sudbury_sharing']->get_shared_posts( $post, $blog_id );
}

/**
 * Gets the root post in a sharing network
 *
 * @param int|WP_Post $post    The Post to check
 * @param int|bool    $blog_id The Blog ID that the post Lives on. Default is current blog
 *
 * @example sudbury_sharing_get_shared_posts( $some_post_id );
 *          yields:
 *              array( 'ID' => 123, 'BLOG_ID' => 12, 'type' => 'root' ),
 *
 * @return array An array of post pointers of posts in the sharing network
 */
function sudbury_sharing_get_root_post( $post, $blog_id = false ) {
	return $GLOBALS['sudbury_sharing']->get_root_post( $post, $blog_id );
}

/**
 * Gets all the posts in a sharing network
 *
 * @param int|WP_Post $post    The Post to check
 * @param int|bool    $blog_id The Blog ID that the post Lives on. Default is current blog
 *
 * @example sudbury_sharing_get_shared_posts( $some_post_id );
 *          yields:
 *             array(
 *                  array( 'ID' => 812, 'BLOG_ID' => 22, 'type' => 'guest' ),
 *                  array( 'ID' => 674, 'BLOG_ID' => 24, 'type' => 'guest' ),
 *                  array( 'ID' => 233, 'BLOG_ID' => 2, 'type' => 'guest' )
 *             )
 *
 * @return array An array of post pointers of posts in the sharing network
 */
function sudbury_sharing_get_guest_posts( $post, $blog_id = false ) {
	return $GLOBALS['sudbury_sharing']->get_guest_posts( $post, $blog_id );
}

add_filter( 'wp_delete_file', 'sudbury_sharing_delete_file_halt' );
/**
 * Prevents an attached file from being deleted when WordPress calls delete_attachment on a guest attachment. (ALl guest attachments point to the Root Attachment's File Path)
 * and Deleting a guest attachment would otherwise delete the Root Attachment file... ruining everything for all the other attachments
 *
 * @param string $file The File path WordPress Thinks it should delete
 *
 * @return string The File path WordPress should actually Delete
 */
function sudbury_sharing_delete_file_halt( $file ) {
	if ( isset( $GLOBALS['sudbury_sharing_dont_delete'] ) && in_array( $file, $GLOBALS['sudbury_sharing_dont_delete'] ) ) {
		_sudbury_log( '[WARNING] Prevented File ' . $file . ' From being Deleted.' );

		// We are returning Gibberish instead of the actual File Name
		return ABSPATH . 'not-a-real-directory/' . md5( ( rand() * microtime() ) . NONCE_SALT );
	}
}

add_filter( 'sudbury_copy_meta', 'sudbury_sharing_do_not_copy_meta', 10, 3 );
function sudbury_sharing_do_not_copy_meta( $meta_key, $meta_value, $post ) {
	$do_not_copy = array( $GLOBALS['sudbury_sharing']->get_post_meta_key() );
	if ( in_array( $meta_key, $do_not_copy ) ) {
		return false;
	}

	return $meta_key;
}
