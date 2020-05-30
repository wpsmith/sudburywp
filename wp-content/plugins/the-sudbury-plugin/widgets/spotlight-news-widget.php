<?php

/**
 * Class Spotlight_News_Widget
 *
 * This widget displays the Spotlight News for a specified category wherever it is assigned
 *
 * To assign this widget to a particular
 * area go to http://.../wp-admin/widgets.php in your browser and drag the 'Sudbury - Banner Paragraph' box on the left to any
 * of the areas on the right
 *
 * @author     Edddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Widgets
 */
class Spotlight_News_Widget extends WP_Widget {

	/**
	 * Constructor
	 */
	function __construct() {
		$options = array( 'description' => __( 'Shows Spotlight News Articles for the selected category', 'sudbury' ) );
		parent::__construct( false, 'Sudbury - Spotlight News', $options );
	}

	/**
	 * @param array $args     Widget Args provided by the theme
	 * @param array $instance The Settings for the Widget
	 */
	function widget( $args, $instance ) {

		network_query_posts( array(
			'posts_per_page' => $instance['posts_per_page'],
			'category_name'  => $instance['category_name'],
			'post_status'    => 'publish',
		) );

		if ( network_have_posts() ) {

			echo $args['before_widget'];

			// Title
			echo $args['before_title'];

			echo $instance['category_name'];

			echo $args['after_title'];

			?>
			<aside id="spotlight">
				<?php
				while ( network_have_posts() ) {
					network_the_post();
					switch_to_blog( network_get_the_blog_id() );
					if ( sudbury_is_guest_post( network_get_the_ID() ) ) { ?>
						<!-- Skipping Post <?php echo esc_html( network_get_the_ID() ); ?> for site <?php echo esc_html( network_get_the_blog_id() ); ?> because it is a guest post -->
						<?php
						restore_current_blog();
						continue;
					}
					restore_current_blog();

					?>
					<div class="story" id="network_<?php network_the_ID(); ?>_<?php network_the_blog_id(); ?>">

						<b><a class="title" href="<?php echo network_get_permalink(); ?>"> <?php network_the_title(); ?></a></b>

						<div class="text" id="article_<?php network_the_id(); ?>"> <?php network_the_excerpt() ?></div>

					</div>
				<?php } ?>
			</aside>
			<?php
			echo $args['after_widget'];
		}


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
		$instance['posts_per_page'] = sudbury_sanitize_int( $new_instance['posts_per_page'] );
		$instance['category_name']  = sanitize_text_field( $new_instance['category_name'] );

		return $instance;
	}

	/**
	 * @param array $instance The Current Settings of the Widget
	 *
	 * @return string|void The HTML for the form (Form can also be echoed and return void)
	 */
	function form( $instance ) {
		//Set up some default widget settings.
		$defaults = array( 'posts_per_page' => 10, 'category_name' => 'Legal Notices' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'category_name' ); ?>">Category</label>
			<select id="<?php echo $this->get_field_id( 'category_name' ); ?>" name="<?php echo $this->get_field_name( 'category_name' ); ?>">
				<?php foreach ( get_terms( array( 'category' ), array( 'hide_empty' => false ) ) as $cat ) : ?>
					<option value="<?php echo esc_attr( $cat->name ); ?>" <?php selected( $instance['category_name'], $cat->name ); ?>><?php echo esc_html( $cat->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'posts_per_page' ); ?>"><?php _e( 'Max Number of Articles', 'sudbury' ); ?></label>
			<input type="number" min="-1" max="20" id="<?php echo $this->get_field_id( 'posts_per_page' ); ?>" name="<?php echo $this->get_field_name( 'posts_per_page' ); ?>" value="<?php echo esc_attr( $instance['posts_per_page'] ); ?>" />
		</p>

		<?php
	}
}
