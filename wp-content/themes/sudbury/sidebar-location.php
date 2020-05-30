<div id="right-col" class="sidebar layout-<?php sudbury_the_post_layout() ?> col-md-4">
	<div id="right-col-details">


		<div class="widget" id="contact">
			<h2>Contact Info</h2>

			<?php
			$address = esc_html( get_post_meta( get_the_ID(), '_location_address', true ) );
			$town    = esc_html( get_post_meta( get_the_ID(), '_location_town', true ) );
			$state   = esc_html( get_post_meta( get_the_ID(), '_location_state', true ) );
			$zipcode = esc_html( get_post_meta( get_the_ID(), '_location_postcode', true ) );
			?>
			<?php echo $address; ?> <br />
			<?php echo $town . ', ' . $state . ' ' . $zipcode; ?>
			<br>
			<?php if ( $phone = get_post_meta( get_the_ID(), 'sudbury_location_phone', true ) ) : ?>
				<b>Phone:</b> <?php echo esc_html( $phone ); ?>
			<?php endif; ?>

		</div>

		<div class="widget">
			<h2>Upcoming Events</h2>

			<div class="upcoming-events">
				<?php echo $GLOBALS['EM_Location']->output( '#_LOCATIONNEXTEVENTS' ); ?>
			</div>
			<hr>
			<i>Additional events may occur in meeting locations within this building</i>
		</div>

		<?php
		$list = '';

		ob_start();
		$location_id = get_post_meta( get_the_ID(), '_location_id', true );

		// Do Not Get all the blogs, just public ones
		$blogs = get_blogs();
		foreach ( $blogs as $blog ) :
			switch_to_blog( $blog['id'] );

			if ( get_option( 'sudbury_location_id', - 1 ) == $location_id ) : ?>

				<li><a href="<?php sudbury_the_site_url(); ?>"><?php bloginfo( 'name' ); ?> </a></li>
			<?php endif;

			restore_current_blog();
		endforeach;

		$list = ob_get_clean();
		?>

		<?php if ( $list ) : ?>
			<div class="widget">

				<h2>Departments and Committees</h2>

				<div id="services">
					<ul>
						<?php echo $list; ?>
					</ul>
				</div>
			</div>
		<?php endif; ?>

		<div class="widget">
			<h2>Map</h2>

			<div id="map">
				<?php echo $GLOBALS['EM_Location']->output( '#_LOCATIONMAP' ); ?>
			</div>


			<?php
			$lat = esc_html( get_post_meta( get_the_ID(), '_location_latitude', true ) );
			$lng = esc_html( get_post_meta( get_the_ID(), '_location_longitude', true ) );
			?>
			<p style="font-size:10px;text-align: center;margin-top:4px;"><?php echo round( sudbury_sanitize_float( $lat ), 6 ); ?>, <?php echo round( sudbury_sanitize_float( $lng ), 6 ); ?></p>
		</div>
	</div>
</div>

<?php sudbury_department_tabs(); ?>