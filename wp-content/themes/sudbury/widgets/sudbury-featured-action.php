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
class Sudbury_Featured_Actions extends WP_Widget {
	private $defaults = array(
		'title'       => 'Default',
		'icon'        => 'fa fa-info',
		'image'       => '',
		'description' => '',
		'link'        => '#'
	);

	/**
	 * Constructor
	 */
	function __construct() {
		$options = array( 'description' => __( 'Shows a featured action', 'sudbury' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

		parent::__construct( false, 'Sudbury - Featured Action', $options );
	}

	/**
	 * Enqueue the media upload script
	 */
	public function scripts() {
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_media();
		wp_enqueue_script( 'sudbury_featured_actions_admin', get_template_directory_uri() . '/js/sudbury_featured_actions.js', array( 'jquery' ) );
	}

	/**
	 * Enqueue the media upload script
	 */
	public function styles() {
		wp_register_style( 'fontawesome', 'https://use.fontawesome.com/releases/v5.1.0/css/all.css', array(), SUDBURY_VERSION );
		wp_enqueue_style( 'fontawesome' );
	}

	/**
	 * @param array $args     Widget Args provided by the theme
	 * @param array $instance The Settings for the Widget
	 */
	function widget( $args = array(), $instance = array() ) {

		$instance = wp_parse_args( (array) $instance, $this->defaults );
		echo $args['before_widget'];
		?>
		<a href="<?php esc_attr_e( $instance['link'] ); ?>" style="color:#000;">
			<?php if ( $instance['image'] ) : ?>
				<img src="<?php esc_attr_e( $instance['image'] ); ?>" alt="<?php esc_attr_e( $instance['description'] ); ?>" />
				<br>
			<?php else: ?>
				<i class="fa <?php esc_attr_e( $instance['icon'] ); ?>" style="font-size: 120px; padding: 15px 0; "></i>
				<br>
			<?php endif; ?>
			<b><?php esc_html_e( $instance['title'] ); ?></b><br>

			<i><?php esc_html_e( $instance['description'] ); ?></i><br>
		</a>
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
		$instance['title']       = sanitize_text_field( $new_instance['title'] );
		$instance['description'] = sanitize_text_field( $new_instance['description'] );
		$instance['icon']        = sanitize_text_field( $new_instance['icon'] );
		$instance['image']       = sanitize_text_field( $new_instance['image'] );
		$instance['link']        = sanitize_text_field( $new_instance['link'] );

		return $instance;
	}

	/**
	 * @param array $instance The Current Settings of the Widget
	 *
	 * @return string|void The HTML for the form (Form can also be echoed and return void)
	 */
	function form( $instance ) {
		//Set up some default widget settings.

		$instance = wp_parse_args( (array) $instance, $this->defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>


		<p>
			<label for="<?php echo $this->get_field_id( 'description' ); ?>">Description:</label>
			<input id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" value="<?php echo $instance['description']; ?>" style="width:100%;" />
		</p>


		<p>
			<label for="<?php echo $this->get_field_id( 'link' ); ?>">Link (optional):</label>
			<input id="<?php echo $this->get_field_id( 'link' ); ?>" name="<?php echo $this->get_field_name( 'link' ); ?>" value="<?php echo $instance['link']; ?>" style="width:100%;" />
		</p>


		<p>
			<label for="<?php echo $this->get_field_id( 'icon' ); ?>">Icon (optional):</label>
			<input id="<?php echo $this->get_field_id( 'icon' ); ?>" name="<?php echo $this->get_field_name( 'icon' ); ?>" data-for="#<?php echo $this->get_field_id( 'icon' ); ?>-preview" value="<?php echo $instance['icon']; ?>" class="icon-select" style="width:100%;" />
			<i id="<?php echo $this->get_field_id( 'icon' ); ?>-preview" class="<?php echo $instance['icon']; ?>"></i>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'image' ); ?>"><?php _e( 'Image:' ); ?></label>
			<input class="widefat upload_image_url" id="<?php echo $this->get_field_id( 'image' ); ?>" name="<?php echo $this->get_field_name( 'image' ); ?>" type="text" value="<?php echo esc_url( $instance['image'] ); ?>" />

			<img style="margin:10px;" src="<?php esc_attr_e( $instance['image'] ); ?>" />

			<input type="button" class="button widefat upload_image_button button" data-for="#<?php echo $this->get_field_id( 'image' ); ?>" value="Select or Upload Image" />
		</p>

		<?php
	}
}
