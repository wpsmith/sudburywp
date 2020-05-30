<?php

/**
 * A shortcode to generate a telephone directory of all departments
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Telephone_Department_Directory {

	function __construct() {

		add_action( 'init', array( &$this, 'init' ) );
	}


	function init() {
		add_shortcode( 'telephone-directory', array( &$this, 'shortcode' ) );
	}


	function shortcode( $atts = array(), $content = null ) {
		$defaults = array(
			'query_post_type' => 'post',
			'fields'          => '',
			'headers'         => '',
			'title'           => '',
			'network'         => 'false',
			'mid_link'        => '',
			'mid_link_text'   => '',
			'cache'           => 'false',
		);

		if ( ! $atts['type'] ) {
			$atts['type'] = isset( $_REQUEST['committee'] ) ? 'committee' : 'department';
		}

		if ( ! is_array( $atts ) ) {
			$atts = array();
		}
		$atts = array_merge( $defaults, $atts );

		ob_start();
		// End Setup

		switch ( $atts['type'] ) {
			case 'department':
			case 'committee':
				$this->do_blogs( $atts );
				break;
			case 'building':
				$this->do_buildings( $atts );
				break;
		}

		$html = ob_get_clean();

		return $html;
	}

	function do_blogs( $atts ) {
		$depts = get_blogs( array( 'type' => $atts['type'], 'sort' => 'longname' ) );
		$type_name = ucfirst( $atts['type'] );
		?>

		<div class="tablecap">
			<h4><?php echo esc_html( $type_name ); ?> Contact Information</h4>
			<table cellspacing="0" style="display: table;" class="filter-table">
				<thead class="headers">
				<tr>
					<th><?php echo esc_html( $type_name ); ?> Name</th>
					<th>Address</th>
					<th>Directions</th>
					<th>Phone / Fax</th>
					<th>Email</th>
					<th>Personnel</th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $depts as $dept ) {
					$location_id = get_blog_option( $dept['id'], 'sudbury_location_id' );

					if ( $location_id ) {
						$location = sudbury_get_location( $location_id );
					} else {
						$location = false;
					}

					if ( $location ) {
						setup_locationdata( $location );
					}
					?>
					<tr>
						<td style="">
							<a href="<?php echo esc_url( $dept['url'] ); ?>"><?php echo esc_html( $dept['title'] ); ?></a>
						</td>
						<td style="">
							<?php if ( $location ) { ?>
								<a href="<?php echo get_blog_permalink( location_get_the_blog_id(), location_get_the_post_id() ); ?>"><?php location_the_name(); ?></a>
								<br /><?php location_the_address(); ?>
								<br /><?php location_the_town(); ?>, <?php location_the_state(); ?>
								<?php location_the_postcode(); ?>
							<?php } ?>
						</td>
						<td style="">
							<?php if ( $location ) { ?>
								<a href="https://www.google.com/maps?daddr=<?php echo urlencode( location_get_the_address() . ', ' . location_get_the_town() . ', ' . location_get_the_state() . ' ' . location_get_the_postcode() ); ?>">Directions</a>
							<?php } ?>
						</td>

						<td style="width:15%;">
							<?php $phone = get_blog_option( $dept['id'], 'sudbury_telephone' ); ?>
							<?php if ( $phone ) : ?>
								<?php echo esc_html( 'T: ' . $phone ); ?>
							<?php endif; ?>
							<br />
							<?php $fax = get_blog_option( $dept['id'], 'sudbury_fax' ); ?>
							<?php if ( $fax ) : ?>
								<?php echo esc_html( 'F: ' . $fax ); ?>
							<?php endif; ?>
						</td>
						<td style="">
							<?php $email = get_blog_option( $dept['id'], 'sudbury_email' ); ?>
							<?php if ( $email ) : ?>
								<?php echo sudbury_protect_emails( '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' ); ?>
							<?php endif; ?>
						</td>

						<td style="">
							<?php if ( $atts['type'] == 'committee' ) : ?>
								<a href="<?php echo esc_url( $dept['url'] . "/members" ); ?>"><?php echo "Members"; ?></a>
							<?php else : ?>
								<a href="<?php echo esc_url( $dept['url'] . "/staff" ); ?>"><?php echo "Staff"; ?></a>
							<?php endif; ?>
						</td>

					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
			<div class="foot"></div>
		</div>


		<?php
	}


	function do_buildings( $atts ) {
		$posts = get_buildings();
		?>


		<div class="tablecap">
			<h4>Town Buildings</h4>
			<table cellspacing="0" style="display: table;" class="filter-table">
				<thead class="headers">

				<th style="width:40%">Building Name</th>
				<?php if ( is_internal() ) : ?>
					<th style="width:10%">Meetings</th>
				<?php endif; ?>
				<th style="width:40%">Address</th>
				<th style="width:10%">Directions</th>

				</thead>
				<tbody>
				<?php
				foreach ( $posts as $post ) {
					setup_locationdata( sudbury_get_location_from_post_id( $post->ID ), $post );
					?>
					<tr>
						<td><a href="<?php echo get_blog_permalink( location_get_the_blog_id(), location_get_the_post_id() ); ?>"><?php location_the_name(); ?></a></td>
						<?php if ( is_internal() ) : ?>
							<td><a href="/upcoming-meetings/<?php location_the_location_id(); ?>"> Meetings </a></td>
						<?php endif; ?>
						<td><?php location_the_address(); ?>, <?php location_the_town(); ?>, <?php location_the_state(); ?> <?php location_the_postcode(); ?></td>
						<td>
							<a href="https://www.google.com/maps?daddr=<?php echo urlencode( location_get_the_address() . ', ' . location_get_the_town() . ', ' . location_get_the_state() . ' ' . location_get_the_postcode() ); ?>">Map</a>
						</td>
					</tr>
					<?php
					release_locationdata();
				}
				?>
				</tbody>
			</table>
			<div class="foot"></div>
		</div>


		<?php
	}
}

new Sudbury_Telephone_Department_Directory();

