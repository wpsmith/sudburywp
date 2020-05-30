<?php

/**
 * Class Sudbury_Department_head
 *
 * This widget displays the List of services offered by this department wherever it is assigned.
 *
 * To assign this widget to a particular
 * area go to http://.../wp-admin/widgets.php in your browser and drag the 'Sudbury - Banner Paragraph' box on the left to any
 * of the areas on the right
 *
 * @author     Edddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Widgets
 */
class Sudbury_Services extends WP_Widget {
	/**
	 * Constructor - Sets the name of the widget and does wordpress magic
	 */
	function __construct() {
		parent::__construct( false, 'Sudbury - Services Offered' );
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		$services = get_posts( array(
			'posts_per_page' => - 1,
			'post_type'      => 'service',
			'orderby'        => 'post_title'
		) );

		if ( ! empty( $services ) ) {


			$title = apply_filters( 'widget_title', $instance['title'] );

			echo $args['before_widget'];

			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			?>
			<ul class="list-style-none">
				<?php foreach ( $services as $service ) : ?>
					<li>
						<a href="<?php echo get_permalink( $service->ID ); ?>"><?php echo get_the_title( $service->ID ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php
			echo $args['after_widget'];
		}
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
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * @param array $instance
	 *
	 * @return string|void
	 */
	function form( $instance ) {
		//Set up some default widget settings.
		$defaults = array( 'title' => 'Services Offered' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		<?php
	}
}