<?php

/**
 * Class Sudbury_Contact_Info
 *
 * This widget displays the Department Contact Info wherever it is assigned.
 *
 * To assign this widget to a particular
 * area go to http://.../wp-admin/widgets.php in your browser and drag the 'Sudbury - Banner Paragraph' box on the left to any
 * of the areas on the right
 *
 * @author     Edddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Widgets
 */
class Sudbury_Contact_Info extends WP_Widget {

	function __construct() {
		$options = array( 'description' => __( 'Shows your ' . sudbury_get_site_type() . '\'s Contact Information', 'sudbury' ) );

		parent::__construct( false, 'Sudbury - Contact Information', $options );
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		extract( $args );
		global $post;

		$data = sudbury_get_options( array(
			'sudbury_email',
			'sudbury_office_hours',
			'sudbury_telephone',
			'sudbury_fax',
			'sudbury_address',
			'sudbury_location_id',
			'sudbury_facebook_url',
			'sudbury_twitter_url',
			'sudbury_youtube_url',
			'sudbury_google_plus_url',
		) );

		if ( $data['sudbury_location_id'] ) {
			$data['location']      = sudbury_get_location( $data['sudbury_location_id'] );
			$data['location_post'] = sudbury_get_location_post( $data['location']['location_id'] );
			setup_locationdata( $data['location'], $data['location_post'] );
		}

		# Trim whitespace from all values
		$data = array_map( function ( $element ) {
			return ( is_string( $element ) ? trim( $element ) : $element );
		}, $data );

		// is there any contact information?
		$empty = array_reduce( $data, function ( $any, $element ) {
			return $any && empty( $element );
		}, true );

		// Return if all the values in the $data are empty
		if ( $empty ) {
			return;
		}

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		?>
		<div id="contact">
			<?php if ( trim( $data['sudbury_office_hours'] ) ) : ?>
				<div>
					<b>Hours:</b><br>
					<?php echo wpautop( $data['sudbury_office_hours'] ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $data['sudbury_email'] ) : ?>
				<div><b>Email:</b>
					<?php echo sudbury_protect_emails( '<a href="mailto:' . esc_attr( $data['sudbury_email'] ) . '">' . esc_html( $data['sudbury_email'] ) . '</a>' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $data['sudbury_telephone'] ) : ?>
				<div><b>Phone:</b> <?php echo esc_html( $data['sudbury_telephone'] ); ?></div>
			<?php endif; ?>

			<?php if ( $data['sudbury_fax'] ) : ?>
				<div><b>Fax:</b> <?php echo esc_html( $data['sudbury_fax'] ); ?></div>
			<?php endif; ?>

			<?php if ( isset( $data['location'] ) || $data['sudbury_address'] ) : ?>
				<div>
					<?php if ( isset( $data['location'] ) ): ?>
						<b>Building: </b>
						<a href="<?php location_the_permalink(); ?>"> <?php location_the_name(); ?></a><br>
						<?php location_the_address(); ?><br>
						<?php location_the_town(); ?>, <?php location_the_state(); ?><?php location_the_postcode(); ?>
						<br>
					<?php endif; ?>
					<i><?php echo wpautop( $data['sudbury_address'] ); ?></i>
				</div>
			<?php endif; ?>
			<div>&nbsp;</div>
			<div>
				<?php if ( $data['sudbury_facebook_url'] ) : ?>
					<a href="<?php echo esc_url( $data['sudbury_facebook_url'] ); ?>" alt="Facebook"><i class="fab fa-facebook fa-2x"></i></a>
				<?php endif; ?>
				<?php if ( $data['sudbury_twitter_url'] ) : ?>
					<a href="<?php echo esc_url( $data['sudbury_twitter_url'] ); ?>" alt="Twitter"><i class="fab fa-twitter fa-2x"></i></a>
				<?php endif; ?>
				<?php if ( $data['sudbury_youtube_url'] ) : ?>
					<a href="<?php echo esc_url( $data['sudbury_youtube_url'] ); ?>" alt="YouTube"><i class="fab fa-youtube fa-2x"></i></a>
				<?php endif; ?>
				<?php if ( $data['sudbury_google_plus_url'] ) : ?>
					<a href="<?php echo esc_url( $data['sudbury_google_plus_url'] ); ?>" alt="Google Plus"><i class="fab fa-google-plus-g fa-2x"></i></a>
				<?php endif; ?>
				<a href="<?php bloginfo( 'rss2_url' ); ?>" alt="RSS 2.0"><i class="fas fa-rss fa-2x"></i></a>

			</div>
		</div>

		<?php

		echo $args['after_widget'];
		release_locationdata();
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
		$defaults = array( 'title' => 'Contact' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		<?php
	}
}
