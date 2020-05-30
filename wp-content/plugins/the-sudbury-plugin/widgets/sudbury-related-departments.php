<?php

/**
 * Class Related_Departments_Widget
 *
 * This widget displays the Department Related Departments and Committees wherever it is assigned.
 *
 * To assign this widget to a particular
 * area go to http://.../wp-admin/widgets.php in your browser and drag the 'Sudbury - Banner Paragraph' box on the left to any
 * of the areas on the right
 *
 * @author     Edddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Widgets
 */
class Related_Departments_Widget extends WP_Widget {

	/**
	 * Constructor
	 */
	function __construct() {
		$options = array( 'description' => __( 'Shows your ' . sudbury_get_site_type() . '\'s Related Departments and Committees', 'sudbury' ) );

		parent::__construct( false, 'Sudbury - Relationships', $options );
	}


	/**
	 * @param array $args     Widget Args provided by the theme
	 * @param array $instance The Settings for the Widget
	 */
	function widget( $args = array(), $instance = array() ) {

		$defaults = array( 'append_url' => '' );

		$args = array_merge( $defaults, $args );


		$parent       = get_option( 'sudbury_parent' );
		$children     = get_option( 'sudbury_children' );
		$counterparts = get_option( 'sudbury_counterparts' );

		// If no relationships then die
		if ( ! $parent && ! $children && ! $counterparts ) {
			return;
		}

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$append_url = esc_attr( $args['append_url'] );

		?>
		<?php if ( $parent ) : ?>
			<h6 class="parent">Parent <?php sudbury_the_site_type(); ?>s</h6>
			<div class="link-list">
				<ul>
					<li>
						<a href="<?php sudbury_the_site_url( $parent ); ?><?php echo $append_url; ?>"><?php sudbury_the_site_name( $parent ); ?></a>
					</li>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( $children ) : ?>
			<h6 class="child">Child <?php sudbury_the_site_type(); ?>s</h6>
			<div class="link-list">
				<ul>
					<?php foreach ( $children as $child ) : ?>
						<li>
							<a href="<?php sudbury_the_site_url( $child ); ?><?php echo $append_url; ?>"><?php sudbury_the_site_name( $child ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<?php if ( $counterparts ) : ?>
			<h6 class="counterpar">Counterpart <?php sudbury_the_site_type(); ?>s</h6>
			<div class="link-list">
				<ul>
					<?php foreach ( $counterparts as $counterpart ) : ?>
						<li>
							<a href="<?php sudbury_the_site_url( $counterpart ); ?><?php echo $append_url; ?>"><?php sudbury_the_site_name( $counterpart ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<?php


		echo $args['after_widget'];
	}

	/**
	 * @param array $new_instance The New Settings for the Widget
	 * @param array $old_instance The Old Settings for the Widget
	 *
	 * @return array The New Settings for the Widget
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		return $instance;
	}

	/**
	 * @param array $instance The Current Settings of the Widget
	 *
	 * @return string|void The HTML for the form (Form can also be echoed and return void)
	 */
	function form( $instance ) {
		//Set up some default widget settings.
		$defaults = array( 'title' => 'Related ' . sudbury_get_site_type() . 's' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title (optional):</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<?php
	}
}
