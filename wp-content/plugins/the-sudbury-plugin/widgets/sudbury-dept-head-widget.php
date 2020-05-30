<?php

/**
 * Class Sudbury_Department_head
 *
 * This widget displays the Department Head Message wherever it is assigned.
 *
 * To assign this widget to a particular
 * area go to http://.../wp-admin/widgets.php in your browser and drag the 'Sudbury - Banner Paragraph' box on the left to any
 * of the areas on the right
 *
 * @author     Edddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Widgets
 */
class Sudbury_Department_head extends WP_Widget {

	function __construct() {
		$options = array( 'description' => __( 'Shows your ' . sudbury_get_site_type() . '\'s Head Message', 'sudbury' ) );

		parent::__construct( false, 'Sudbury - Department Head Message', $options );
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		$pages = get_posts( array(
			'post_type'  => 'page',
			'meta_key'   => 'sudbury_department_head_message',
			'meta_value' => true
		) );

		if ( empty( $pages ) ) {
			return;
		}

		$page = $pages[0];


		$title     = apply_filters( 'widget_title', $instance['title'] );
		$link_only = isset( $instance['link-only'] ) ? $instance['link-only'] : true;

		echo $args['before_widget'];

		if ( ! $link_only && $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( $link_only ) {
			$url = get_the_permalink( $page );

			$message = '<a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $page ) ) . '</a>';
		} else {
			$message = wp_trim_excerpt( $page->post_content );
		}

		echo $message; // This is supposed to contain HTML

		echo $args['after_widget'];
	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['link-only'] = ( 'on' == $new_instance['link-only'] );

		return $instance;
	}

	/**
	 * @param array $instance
	 *
	 * @return string|void
	 */
	function form( $instance ) {
		//Set up some default widget settings.
		$defaults = array( 'title' => 'From the Department Head', 'link-only' => true );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['link-only'], true ); ?> id="<?php echo $this->get_field_id( 'link-only' ); ?>" name="<?php echo $this->get_field_name( 'link-only' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'link-only' ); ?>"><?php _e( 'Truncate Message (Please Check This)', 'example' ); ?></label>
		</p>
		<?php
	}
}