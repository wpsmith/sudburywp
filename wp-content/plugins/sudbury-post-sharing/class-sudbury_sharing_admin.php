<?php

/**
 * Created by PhpStorm.
 * User: hurtige
 * Date: 12/23/2014
 * Time: 4:33 PM
 */
class Sudbury_Sharing_Admin {
	private $core;

	function __construct( $core ) {
		$this->core = $core;
	}

	function admin_init() {
		foreach ( $this->core->get_post_types() as $post_type ) {
			if ( ! isset( $_GET['referrer_post_type'] ) || $_GET['referrer_post_type'] == 'attachment' ) {
				add_meta_box( 'sudbury_sharing_metabox', __( 'Share To', 'sudbury_sharing' ), array(
					&$this,
					'meta_box'
				), $post_type, 'side', 'default' );
			}
		}
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_head' ) );
		add_action( 'admin_notices', array( &$this, 'notices' ) );
		add_filter( 'duplicate_post_blacklist_filter', array( &$this, 'duplicate_post_blacklist' ), 10, 1 );
	}

	function duplicate_post_blacklist( $meta_blacklist ) {
		$meta_blacklist[] = $this->core->get_post_meta_key(); // don't duplicate post meta

		return $meta_blacklist;
	}

	function notices() {
		if ( 'post' == get_current_screen()->id ) {
			global $post;
			if ( sudbury_sharing_is_guest_post( $post ) ) :
				$root = sudbury_sharing_get_root_post( $post ); ?>
				<div class="error">
					<p>
						<b>Warning:</b> Do not edit this guest post.
						Please
						<b><a href="<?php echo esc_url( get_site_url( $root['BLOG_ID'], 'wp-admin/post.php?action=edit&post=' . intval( $root['ID'] ) ) ); ?>">Click here to edit the root post</a></b> on the
						<b><?php echo esc_html( get_blog_option( $root['BLOG_ID'], 'blogname' ) ); ?></b>
						site instead.
					</p>
				</div>
			<?php endif;
		}
	}

	function admin_head() {
		?>
		<style type="text/css">
			.sudbury-sharing-banner {
				background: #2EA2CC;
				margin: -6px -12px;
				padding: 5px;
				text-align: center;
				color: #fff;
				font-weight: bold;
			}

			.sudbury-sharing-banner a, .sudbury-sharing-banner a:visited {
				color: #f8f8f8;
			}

			.sudbury-sharing-banner a:hover, .sudbury-sharing-banner a:active {
				color: #ffffff;
			}
		</style>
		<?php
	}

	function meta_box( $post ) {
		if ( sudbury_sharing_is_root_post( $post ) || ! sudbury_sharing_is_shared( $post ) ) {
			$existing = get_post_meta( $post->ID, $this->core->get_post_meta_key(), true );
			if ( '' === $existing ) {
				$blogs = get_post_meta( $post->ID, $this->core->get_post_meta_key() . '_temp', true );
				if ( '' === $blogs ) {
					$blogs = apply_filters( 'sudbury_sharing_default_checked', array(), $post );
				}
			} else {
				$blogs = wp_list_pluck( $existing, 'BLOG_ID' );
			}

			?>
			<label for="<?php echo esc_attr( $this->core->get_post_meta_key() ); ?>">
				<?php if ( sudbury_sharing_is_root_post( $post ) ) : ?>
					<p class="sudbury-sharing-banner">This Post is a Root Post</p>
				<?php endif; ?>
				<p>
					Check:
					<a href="#" onclick="jQuery('.sudbury_sharing_checkbox').attr('checked',true);return false;">all</a>
					|
					<a href="#"
					   onclick="jQuery('.sudbury_sharing_checkbox').attr('checked',false);return false;">none</a>
				</p>

				<div class="<?php echo esc_attr( $this->core->get_post_meta_key() ); ?>_checks"
					 style="max-height: 700px; overflow: scroll;">
					<input type="hidden" name="<?php echo esc_attr( $this->core->get_post_meta_key() ); ?>[]"
						   value="-1" />
						<?php
					$projects = array();
					if ( is_super_admin() ) {
						if ( ! $all_blogs = wp_cache_get( 'sudbury_all_blog_details' ) ) {
							if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {
								$sites = get_sites( array( 'number' => 1000 ) );
								foreach ( $sites as $site ) {
									if ( sudbury_has_type( 'project', $site->blog_id ) ) {
										$projects[ $site->blog_id ] = get_blog_details( $site->blog_id, true )->blogname;
									} else {
										$all_blogs[ $site->blog_id ] = get_blog_details( $site->blog_id, true )->blogname;
									}
								}
							} else if ( function_exists( 'wp_get_sites' ) ) {
								$all_blogs_raw = wp_get_sites( array( 'limit' => false ) );
								$all_blogs     = array();
								foreach ( $all_blogs_raw as $blog ) {
									if ( sudbury_has_type( 'project', $blog['blog_id'] ) ) {
										$projects[ $blog['blog_id'] ] = get_blog_details( $blog['blog_id'], true )->blogname;
									} else {
										$all_blogs[ $blog['blog_id'] ] = get_blog_details( $blog['blog_id'], true )->blogname;
									}
								}
							} else {
								wp_die( "Unsupported WordPress", "This version of WordPress is not Supported" );

								return;
							}

							wp_cache_set( 'sudbury_all_blog_details', $all_blogs, 3600 );
						}
					} else {
						$all_blogs = get_blogs_of_user( get_current_user_id() );
						$all_blogs = wp_list_pluck( $all_blogs, 'blogname', 'userblog_id' );
					}
					unset( $all_blogs[ get_current_blog_id() ] );
					unset( $projects[ get_current_blog_id() ] );
					asort( $all_blogs );
					asort( $projects );
					$origin_blog = get_current_blog_id();
					foreach ( array( 'Projects' => $projects, 'Sites' => $all_blogs ) as $name => $list ) :
						?>
						<?php if ( $list ) : ?>
							<b><?php esc_html_e( $name ); ?></b><br>
							
							<?php foreach ( $list as $blog_id => $blogname ) :
								if ( strlen( $blogname ) > 28 ) {
									$blogname = substr( $blogname, 0, 28 ) . '...';
								}

								$disabled = false;

								if ( 'attachment' == $post->post_type ) {
									switch_to_blog( $blog_id );
									$related_blogs = array();
									$meta          = sudbury_get_relationship_meta();
									if ( isset( $meta[ $origin_blog ] ) ) {
										foreach ( $meta[ $origin_blog ] as $relation ) {
											if ( isset( $relation['retrieve_documents'] ) && $relation['retrieve_documents'] ) {
												$disabled = true;
												restore_current_blog();
												break;
											}
										}
									}
									restore_current_blog();
								}

								?>

								<input type="checkbox" class="sudbury_sharing_checkbox"
									   name="<?php echo esc_attr( $this->core->get_post_meta_key() ); ?>[]"
									   value="<?php echo esc_attr( $blog_id ); ?>" <?php checked( in_array( $blog_id, $blogs ) ); ?> <?php disabled( $disabled ); ?>/>
								<?php if ( in_array( $blog_id, $blogs ) && is_super_admin() ) : ?>
									<a href="<?php echo esc_url( get_site_url( $blog_id, 'wp-admin/edit.php' ) ); ?>"><?php echo esc_html( get_blog_option( $blog_id, 'blogname' ) ); ?> </a>
								<?php else : ?>
									<?php echo esc_html( $blogname ); ?>
								<?php endif; ?>
								<br>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</label>
			<?php
		} elseif ( sudbury_sharing_is_guest_post( $post ) ) {
			$guests = sudbury_sharing_get_guest_posts( $post );
			$guests = array_filter( $guests, function ( $record ) use ( &$post ) {
				return $record['BLOG_ID'] != get_current_blog_id() && $record['ID'] != $post->ID;
			} );
			$root   = sudbury_sharing_get_root_post( $post );
			?>
			<p class="sudbury-sharing-banner">This is a Guest from
				<a href="<?php echo esc_url( get_site_url( $root['BLOG_ID'], 'wp-admin/post.php?action=edit&post=' . intval( $root['ID'] ) ) ); ?>"><?php echo esc_html( get_blog_option( $root['BLOG_ID'], 'blogname' ) ); ?> </a>
			</p>
			<?php if ( ! empty( $guests ) ) : ?>
				<p>Other guest copies of this post are:</p>
				<ul style="list-style: disc;">
					<?php foreach ( $guests as $guest ) : ?>
						<li>
							<a href="<?php echo esc_url( get_site_url( $guest['BLOG_ID'], 'wp-admin/post.php?action=edit&post=' . $guest['ID'] ) ); ?>"><?php echo esc_html( get_blog_option( $guest['BLOG_ID'], 'blogname' ) ); ?> </a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else: ?>
				<p style="text-align: center;">There are no other guest copies of this post</p>
			<?php endif;
		} else {
			?>
			<p class="sudbury-sharing-banner" style="background-color: #e14f1c">
				This post has something wrong with it's sharing data.
			</p>
			<?php
		}
	}
}
