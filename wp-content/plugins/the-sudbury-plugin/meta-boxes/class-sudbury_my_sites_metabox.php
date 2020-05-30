<?php

class Sudbury_My_Sites_Metabox {
	function __construct() {
		add_action( 'admin_init', array( &$this, 'add_meta_box' ) );
	}

	function add_meta_box() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( is_super_admin() ) {
			$title = 'My Favorite Sites';
		} else {
			$title = 'My Sites';
		}
		add_meta_box( 'sudbury-my-sites-metabox', $title, array( &$this, 'render' ), 'dashboard', 'normal', 'default' );
	}

	function render() {

		$blogs = get_blogs_of_user( get_current_user_id() );

		?>
		<table class="widefat fixed">
			<?php

			reset( $blogs );
			$num  = count( $blogs );
			$cols = 1;
			if ( $num >= 20 ) {
				$cols = 2;
			} elseif ( $num >= 10 ) {
				$cols = 2;
			}
			$num_rows = ceil( $num / $cols );
			$split    = 0;
			for ( $i = 1; $i <= $num_rows; $i ++ ) {
				$rows[] = array_slice( $blogs, $split, $cols );
				$split  = $split + $cols;
			}

			$c = '';
			foreach ( $rows as $row ) {
				$c = $c == 'alternate' ? '' : 'alternate';
				echo "<tr class='$c'>";
				$i = 0;
				foreach ( $row as $user_blog ) {
					$s = $i == 3 ? '' : 'border-right: 1px solid #ccc;';
					echo "<td style='$s'>";
					echo "<h3>{$user_blog->blogname}</h3>";
					/**
					 * Filter the row links displayed for each site on the My Sites screen.
					 *
					 * @since MU
					 *
					 * @param string $string    The HTML site link markup.
					 * @param object $user_blog An object containing the site data.
					 */
					echo "<p>" . apply_filters( 'myblogs_blog_actions', "<a href='" . esc_url( get_home_url( $user_blog->userblog_id ) ) . "'>" . __( 'Visit' ) . "</a> | <a href='" . esc_url( get_admin_url( $user_blog->userblog_id ) ) . "'>" . __( 'Dashboard' ) . "</a>", $user_blog ) . "</p>";
					/** This filter is documented in wp-admin/my-sites.php */
					echo apply_filters( 'myblogs_options', '', $user_blog );
					echo "</td>";
					$i ++;
				}
				echo "</tr>";
			}?>
		</table>
	<?php
	}
}

new Sudbury_My_Sites_Metabox();