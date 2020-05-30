<?php

if ( ! function_exists( 'sudbury_social_links' ) ) :

	function sudbury_social_links( $args = array( 'copy' => true ) ) {
		?>
		<p>

			<?php
			if ( isset( $args['email'] ) && $args['email'] ) {
				sudbury_email_link( $args['email'] );
			}
			if ( true || isset( $args['copy'] ) && $args['copy'] ) {
				sudbury_copy_link( $args['copy'] );
			}
			if ( isset( $args['return'] ) && $args['return'] ) {
				sudbury_return_link( $args['return'] );
			}
			?>

		</p>

		<?php
	}

	function sudbury_email_link( $args = array() ) {
		$defaults = array(
			'text'           => 'Email this Article',
			'hook_text'      => 'Check out this article on the Town of Sudbury Website: ',
			'before_title'   => '',
			'after_title'    => '',
			'before_subject' => '',
			'after_subject'  => '',
			'before_message' => '',
			'after_message'  => '',
		);

		extract( wp_parse_args( (array) $args, $defaults ) );

		?>
		<i class="fa fa-envelope"></i>
		<a href="mailto:?subject=<?php echo esc_attr( $before_subject . get_the_title() . $after_subject ); ?>&amp;body=<?php echo esc_attr( $hook_text . ' ' . $before_title . get_the_title() . $after_title . ' ' . urlencode( get_post_permalink( get_post() ) ) ); ?>">
			Email this <?php echo get_post_type_object( get_post_type() )->labels->singular_name; ?>
		</a>
		<br>
		<?php
	}

	function sudbury_copy_link( $args = array() ) {
		$defaults = array(
			'text'           => 'Copy Link',
			'before_title'   => '',
			'after_title'    => '',
			'before_subject' => '',
			'after_subject'  => '',
			'before_message' => '',
			'after_message'  => '',
		);

		extract( wp_parse_args( (array) $args, $defaults ) );
		$link = wp_get_shortlink(); 
		if ( get_post_type() == 'post' && $link ) : 
		?>
		<i class="fa fa-link"></i>
		<a href="<?php echo esc_url( $link ); ?>">
			Content Shortlink
		</a>
		<br>
		<?php endif;
	}

	function sudbury_return_link( $args = array() ) {
		if ( ! is_home() && ! is_front_page() ) {
			?>

			<i class="fa fa-arrow-circle-left"></i>
			<a href="<?php bloginfo( 'url' ); ?>">Back to <?php bloginfo( 'name' ); ?></a>
			<br>

			<?php
		}
	}

endif;

?>
