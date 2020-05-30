<?php
/*
Plugin Name: (Sudbury Version) Recent Global Posts Widget
Description: Show the most recent global posts in a widget
Author: Barry (Incsub), Eddie Hurtig (Town Of Sudbury)
Version:
Author URI:
*/

/*
Copyright 2012 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! defined( 'RECENT_GLOBAL_POSTS_WIDGET_MAIN_BLOG_ONLY' ) ) {
	define( 'RECENT_GLOBAL_POSTS_WIDGET_MAIN_BLOG_ONLY', false );
}

class widget_recent_global_posts extends WP_Widget {
	var $number_months_back;

	function __construct() {

		$locale = apply_filters( 'rgpwidget_locale', get_locale() );
		$mofile = dirname( __FILE__ ) . "/languages/rgpwidget-$locale.mo";

		if ( file_exists( $mofile ) ) {
			load_textdomain( 'rgpwidget', $mofile );
		}

		$widget_ops  = array( 'classname' => 'rgpwidget', 'description' => __( 'Recent Global Posts', 'rgpwidget' ) );
		$control_ops = array( 'width' => 400, 'height' => 350, 'id_base' => 'rgpwidget' );
		parent::__construct( 'rgpwidget', __( 'Recent Global Posts', 'rgpwidget' ), $widget_ops, $control_ops );
		add_shortcode( 'recent-global-posts', array( &$this, 'shortcode' ) );
	}

	function shortcode( $atts, $content = null ) {
		ob_start();
		$args = array();
		foreach ( $atts as $key => $att ) {
			if ( 0 === strpos( trim( $key ), 'widget_' ) ) {
				$args[ substr( $key, strlen( 'widget_' ) ) ] = htmlspecialchars_decode( $att );
				unset( $atts[ $key ] );
			}
		}
		$this->widget( $args, $atts );

		return ob_get_clean();
	}

	function widget( $args, $instance ) {
		global $wpdb, $current_site;
		global $network_query, $network_post;
		extract( $args );
		$defaults = array(
			'recentglobalpoststitle'             => '',
			'recentglobalpostsdisplay'           => 'title_content',
			'recentglobalpostsnumber'            => - 1,
			'recentglobalpoststitlecharacters'   => '',
			'recentglobalpostscontentcharacters' => '',
			'recentglobalpostsavatars'           => '',
			'recentglobalpostsavatarsize'        => '',
			'recentglobalpoststype'              => 'post',
			'recentglobalpostscategory'          => '',
			'recentglobalpostsnotfound'          => '',
			'count'                              => 10,
			'username'                           => 'wordpress',
			'show_all_links'                     => 'show',
			'number_months_back'                 => 3
		);

		foreach ( $defaults as $key => $value ) {
			if ( isset( $instance[ $key ] ) ) {
				$defaults[ $key ] = $instance[ $key ];
			}
		}

		extract( $defaults );
		extract( $instance );
		$this->number_months_back = $defaults['number_months_back'];
		$title                    = apply_filters( 'widget_title', $recentglobalpoststitle );

		?>
		<?php echo $before_widget; ?>
		<?php echo $before_title; ?>
		<?php echo __( $title ) ?>
		<?php if ( $show_all_links == 'show' ) : ?>
			<span class="section-header-links">
                <a href="/all-news/">All News</a> |
                <a href="/all-documents/">All Documents</a> |
                <a href="/all-faqs/">All FAQs</a> |
                <a href="/all-meetings/">All Meetings</a> |
                <a href="/all-links/">All Links</a>

            </span>
		<?php endif; ?>
		<?php echo $after_title; ?>

		<?php
		if ( $this->number_months_back ) {
			add_filter( 'network_posts_where', array( &$this, 'network_query_where' ) );
		}
		$network_query = network_query_posts( $query_args = array(
			'post_status'    => array( 'publish' ),
			'post_type'      => $recentglobalpoststype,
			'posts_per_page' => - 1,
			'category_name'  => $recentglobalpostscategory,
		) );
		if ( $this->number_months_back ) {
			remove_filter( 'network_posts_where', array( &$this, 'network_query_where' ) );
		}
		if ( network_have_posts() ) {


			?>
			<article id="news" class="articles">
				<?php
				while ( network_have_posts() ) {
					network_the_post();

					switch_to_blog( $network_post->BLOG_ID );

					if ( sudbury_is_guest_post( $network_post->ID ) ) {
						restore_current_blog();
						continue;
					}

					restore_current_blog();
					?>
					<div class="story" id="network_<?php network_the_id(); ?>">
						<?php

						if ( $recentglobalpostsavatars == 'show' ) {
							?>
							<a href="<?php echo network_get_permalink(); ?>"> <?php get_avatar( network_get_the_author_id(), $recentglobalpostsavatarsize, '' ); ?></a>
							<?php
						}
						if ( $recentglobalpostsdisplay == 'title_content' ) {
							?>
							<a class="title" href="<?php echo network_get_permalink(); ?>"> <?php network_the_title(); ?></a>

							<div class="text" id="article_<?php network_the_id(); ?>"> <?php network_the_excerpt() ?></div>
							<span class="sign"> <?php sudbury_the_relationship_path( $network_post->BLOG_ID ); ?> ( Posted: <?php echo mysql2date( 'l, F j, Y', network_get_post( $network_post->BLOG_ID, $network_post->ID )->post_modified ); ?> )</span>
							<?php
						} else {
							if ( $recentglobalpostsdisplay == 'title' ) {
								?>
								<b><a class="title" href="<?php echo network_get_permalink(); ?>"> <?php network_the_title(); ?></a></b>
								<div class="text"><?php echo mysql2date( 'l, F j, Y', network_get_post( $network_post->BLOG_ID, $network_post->ID )->post_modified ); ?></div>
								<?php
							} else {
								if ( $recentglobalpostsdisplay == 'content' ) {
									?>
									<div class="text"> <?php network_the_excerpt(); ?> </div>
									<?php
								}
							}
						} ?>
					</div>
				<?php } ?>
				<?php network_reset_query(); ?>
			</article>
			<?php
		} else {
			echo $recentglobalpostsnotfound;
		}
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {

		$defaults = array(
			'recentglobalpoststitle'             => '',
			'recentglobalpostsdisplay'           => '',
			'recentglobalpostsnumber'            => '',
			'recentglobalpoststitlecharacters'   => '',
			'recentglobalpostscontentcharacters' => '',
			'recentglobalpostsavatars'           => '',
			'recentglobalpostsavatarsize'        => '',
			'recentglobalpoststype'              => 'post',
			'recentglobalpostscategory'          => '',
			'count'                              => 10,
			'username'                           => 'wordpress',
			'post_type'                          => 'post',
			'show_all_links'                     => 'show'
		);

		foreach ( $defaults as $key => $val ) {
			$instance[ $key ] = $new_instance[ $key ];
		}

		return $instance;

	}

	function form( $instance ) {

		$defaults = array(
			'recentglobalpoststitle'             => '',
			'recentglobalpostsdisplay'           => '',
			'recentglobalpostsnumber'            => '',
			'recentglobalpoststitlecharacters'   => '',
			'recentglobalpostscontentcharacters' => '',
			'recentglobalpostsavatars'           => '',
			'recentglobalpostsavatarsize'        => '',
			'recentglobalpoststype'              => 'post',
			'recentglobalpostscategory'          => '',
			'count'                              => 10,
			'username'                           => 'wordpress',
			'post_type'                          => 'post',
			'show_all_links'                     => 'show',
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		extract( $instance );

		?>
		<div style="text-align:left">

			<label for="<?php echo $this->get_field_name( 'recentglobalpoststitle' ); ?>" style="line-height:35px;display:block;"><?php _e( 'Title', 'rgpwidget' ); ?>:<br />
				<input class="widefat" id="<?php echo $this->get_field_id( 'recentglobalpoststitle' ); ?>" name="<?php echo $this->get_field_name( 'recentglobalpoststitle' ); ?>" value="<?php echo esc_attr( stripslashes( $instance['recentglobalpoststitle'] ) ); ?>" type="text" style="width:95%;">
				<input class="widefat" id="<?php echo $this->get_field_id( 'recentglobalpoststitle' ); ?>" name="<?php echo $this->get_field_name( 'recentglobalpoststitle' ); ?>" value="<?php echo esc_attr( stripslashes( $instance['recentglobalpoststitle'] ) ); ?>" type="text" style="width:95%;">
			</label>

			<label for="<?php echo $this->get_field_name( 'recentglobalpostsdisplay' ); ?>" style="line-height:35px;display:block;"><?php _e( 'Display', 'rgpwidget' ); ?>:
				<select name="<?php echo $this->get_field_name( 'recentglobalpostsdisplay' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostsdisplay' ); ?>" style="width:95%;">
					<option value="title_content" <?php selected( $instance['recentglobalpostsdisplay'], 'title_content' ); ?> ><?php _e( 'Title + Content', 'rgpwidget' ); ?></option>
					<option value="title" <?php selected( $instance['recentglobalpostsdisplay'], 'title' ); ?> ><?php _e( 'Title Only', 'rgpwidget' ); ?></option>
					<option value="content" <?php selected( $instance['recentglobalpostsdisplay'], 'content' ); ?> ><?php _e( 'Content Only', 'rgpwidget' ); ?></option>
				</select>
			</label>

			<label for="<?php echo $this->get_field_name( 'recentglobalpostsnumber' ); ?>" style="line-height:35px;display:block;"><?php _e( 'Number', 'rgpwidget' ); ?>:<br />
				<select name="<?php echo $this->get_field_name( 'recentglobalpostsnumber' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostsnumber' ); ?>" style="width:95%;">
					<?php
					if ( empty( $instance['recentglobalpostsnumber'] ) ) {
						$instance['recentglobalpostsnumber'] = 5;
					}
					$counter = 0;
					for ( $counter = 1; $counter <= 25; $counter += 1 ) {
						?>
						<option value="<?php echo $counter; ?>" <?php selected( $instance['recentglobalpostsnumber'], $counter ); ?> ><?php echo $counter; ?></option>
						<?php
					}
					?>
				</select>

			</label>
			<label for="<?php echo $this->get_field_name( 'recentglobalpoststitlecharacters' ); ?>" style="line-height:35px;display:block;"><?php _e( 'Title Characters', 'rgpwidget' ); ?>:<br />
				<select name="<?php echo $this->get_field_name( 'recentglobalpoststitlecharacters' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpoststitlecharacters' ); ?>" style="width:95%;">
					<?php
					if ( empty( $instance['recentglobalpoststitlecharacters'] ) ) {
						$instance['recentglobalpoststitlecharacters'] = 30;
					}
					$counter = 0;
					for ( $counter = 1; $counter <= 200; $counter += 1 ) {
						?>
						<option value="<?php echo $counter; ?>" <?php selected( $instance['recentglobalpoststitlecharacters'], $counter ); ?> ><?php echo $counter; ?></option>
						<?php
					}
					?>
				</select>
			</label>

			<label for="<?php echo $this->get_field_name( 'recentglobalpostscontentcharacters' ); ?>" style="line-height:35px;display:block;"><?php _e( 'Content Characters', 'rgpwidget' ); ?>:<br />
				<select name="<?php echo $this->get_field_name( 'recentglobalpostscontentcharacters' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostscontentcharacters' ); ?>" style="width:95%;">
					<?php
					if ( empty( $instance['recentglobalpostscontentcharacters'] ) ) {
						$instance['recentglobalpostscontentcharacters'] = 100;
					}
					$counter = 0;
					for ( $counter = 1; $counter <= 500; $counter += 1 ) {
						?>
						<option value="<?php echo $counter; ?>" <?php selected( $instance['recentglobalpostscontentcharacters'], $counter ); ?> ><?php echo $counter; ?></option>
						<?php
					}
					?>
				</select>
			</label>

			<label for="<?php echo $this->get_field_name( 'recentglobalpostsavatars' ); ?>" style="line-height:35px;display:block;"><?php _e( 'Avatars', 'rgpwidget' ); ?>:<br />
				<select name="<?php echo $this->get_field_name( 'recentglobalpostsavatars' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostsavatars' ); ?>" style="width:95%;">
					<option value="show" <?php selected( $instance['recentglobalpostsavatars'], 'show' ); ?> ><?php _e( 'Show', 'rgpwidget' ); ?></option>
					<option value="hide" <?php selected( $instance['recentglobalpostsavatars'], 'hide' ); ?> ><?php _e( 'Hide', 'rgpwidget' ); ?></option>
				</select>
			</label>

			<label for="<?php echo $this->get_field_name( 'recentglobalpostsavatarsize' ); ?>" style="line-height:35px;display:block;"><?php _e( 'Avatar Size', 'rgpwidget' ); ?>:<br />
				<select name="<?php echo $this->get_field_name( 'recentglobalpostsavatarsize' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostsavatarsize' ); ?>" style="width:95%;">
					<option value="16" <?php selected( $instance['recentglobalpostsavatarsize'], '16' ); ?> ><?php _e( '16px', 'rgpwidget' ); ?></option>
					<option value="32" <?php selected( $instance['recentglobalpostsavatarsize'], '32' ); ?> ><?php _e( '32px', 'rgpwidget' ); ?></option>
					<option value="48" <?php selected( $instance['recentglobalpostsavatarsize'], '48' ); ?> ><?php _e( '48px', 'rgpwidget' ); ?></option>
					<option value="96" <?php selected( $instance['recentglobalpostsavatarsize'], '96' ); ?> ><?php _e( '96px', 'rgpwidget' ); ?></option>
					<option value="128" <?php selected( $instance['recentglobalpostsavatarsize'], '128' ); ?> ><?php _e( '128px', 'rgpwidget' ); ?></option>
				</select>
			</label>

			<?php
			$post_types = $this->get_post_types();

			?>

			<label for="<?php echo $this->get_field_name( 'recentglobalpoststype' ); ?>" style="line-height:35px;display:block;"><?php _e( 'Post Type', 'rgpwidget' ); ?>:<br />
				<select name="<?php echo $this->get_field_name( 'recentglobalpoststype' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpoststype' ); ?>" style="width:95%;">
					<?php
					if ( ! empty( $post_types ) ) {
						foreach ( $post_types as $r ) {
							?>
							<option value="<?php echo $r; ?>" <?php selected( $instance['recentglobalpoststype'], $r ); ?> ><?php _e( $r, 'rgpwidget' ); ?></option>
							<?php
						}
					} else {
						?>
						<option value="post" <?php selected( $instance['recentglobalpoststype'], 'post' ); ?> ><?php _e( 'post', 'rgpwidget' ); ?></option>
						<?php
					}
					?>
				</select>
			</label>

			<?php
			$categories = get_categories( array( 'hide_empty' => 0 ) );
			?>


			<label for="<?php echo $this->get_field_name( 'recentglobalpostscategory' ); ?>" style="line-height:35px;display:block;"><?php _e( 'Category', 'rgpwidget' ); ?>:<br />
				<select name="<?php echo $this->get_field_name( 'recentglobalpostscategory' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostscategory' ); ?>" style="width:95%;">

					<option value="" <?php selected( $instance['recentglobalpostscategory'], '' ); ?> >-- All Categories --</option>

					<?php if ( ! empty( $categories ) ) {
						foreach ( $categories as $c ) {
							?>
							<option value="<?php echo $c->slug; ?>" <?php selected( $instance['recentglobalpostscategory'], $c->slug ); ?> ><?php echo $c->name ?></option>
							<?php
						}
					}
					?>
				</select>
			</label>

			<label for="<?php echo $this->get_field_name( 'show_all_links' ); ?>" style="line-height:35px;display:block;"><?php _e( 'Show "All *" Links', 'rgpwidget' ); ?>:<br />
				<select name="<?php echo $this->get_field_name( 'show_all_links' ); ?>" id="<?php echo $this->get_field_id( 'show_all_links' ); ?>" style="width:95%;">
					<option value="show" <?php selected( $instance['show_all_links'], 'show' ); ?> ><?php _e( 'Show', 'rgpwidget' ); ?></option>
					<option value="hide" <?php selected( $instance['show_all_links'], 'hide' ); ?> ><?php _e( 'Hide', 'rgpwidget' ); ?></option>
				</select>
			</label>

			<input type="hidden" name="<?php echo $this->get_field_name( 'recentglobalpostssubmit' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostssubmit' ); ?>" value="1" />
		</div>
		<?php
	}

	function get_post_types() {
		global $wpdb;

		$sql = "SELECT post_type FROM " . $wpdb->base_prefix . "network_posts GROUP BY post_type";

		$results = $wpdb->get_col( $sql );

		return $results;
	}

	/**
	 * Restricts posts query to last 3 months
	 *
	 * @param string $where The Where part of the SQL Query String
	 *
	 * @return string The New Where part of the SQL Query String
	 */
	function network_query_where( $where ) {
		return $where . ' AND wp_network_posts.post_date > \'' . date( 'Y-m-d', strtotime( '-' . $this->number_months_back . ' months' ) ) . "'";
	}
}

function widget_recent_global_posts_register() {
	global $wpdb;

	if ( RECENT_GLOBAL_POSTS_WIDGET_MAIN_BLOG_ONLY == 'yes' ) {
		if ( $wpdb->blogid == 1 ) {
			register_widget( 'widget_recent_global_posts' );
		}
	} else {
		register_widget( 'widget_recent_global_posts' );
	}
}

add_action( 'widgets_init', 'widget_recent_global_posts_register' );

?>
