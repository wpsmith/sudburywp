<?php

/**
 * Class Sudbury_Description_Paragraph
 *
 * This widget displays the Department Description Paragraph wherever it is assigned.
 *
 * To assign this widget to a particular
 * area go to http://.../wp-admin/widgets.php in your browser and drag the 'Sudbury - Banner Paragraph' box on the left to any
 * of the areas on the right
 *
 * @author     Edddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Widgets
 */
class Sudbury_Description_Paragraph extends WP_Widget {

	/**
	 * Constructor
	 */
	function __construct() {
		$options = array( 'description' => __( 'Shows your ' . sudbury_get_site_type() . '\'s Description Paragraph', 'sudbury' ) );

		parent::__construct( false, 'Sudbury - ' . sudbury_get_site_type() . ' Description', $options );
	}


	/**
	 * @param array $args     Widget Args provided by the theme
	 * @param array $instance The Settings for the Widget
	 */
	function widget( $args, $instance ) {

		$paragraph = get_option( 'sudbury_description_paragraph' );
		if ( $paragraph ) {
			if ( ! $instance['title'] ) {
				$args['before_title'] = $args['before_title'] . '<a href="' . get_site_url() . '">';
				$args['after_title']  .= '</a>';
				$instance['title']    = get_bloginfo( 'blogname' );
			}

			$title = apply_filters( 'widget_title', $instance['title'] );

			echo $args['before_widget'];

			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}


			echo do_shortcode( wpautop( $paragraph ) ); // This Is Supposed to Contain HTML
		}

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
		$defaults = array( 'title' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title (optional):</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		<p>
			You can edit the description paragraph
			<a href="<?php echo admin_url( 'admin.php?page=sudbury-dept-info-options-page' ); ?>">here</a>
		</p>
		<?php
	}
}