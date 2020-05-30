<?php if ( sudbury_has_event( get_the_ID() ) ) : ?>

	<?php
	$location_post = sudbury_get_location_post( get_post_meta( get_the_ID(), '_location_id', true ), true, array(
		'post_status' => array(
			'publish',
			'private',
			'public-archive',
		),
	) ); ?>

	<h3>Event Details</h3>
	<?php if ( sudbury_event_is_happening( get_the_ID() ) ) : ?>
		<div class="alert alert-info">This event is currently happening</div>
	<?php elseif ( sudbury_event_is_today( get_the_ID() ) && sudbury_event_is_over( get_the_ID() ) ) : ?>
		<div class="alert alert-warning">This event happened earlier today</div>
	<?php elseif ( sudbury_event_is_today( get_the_ID() ) ) : ?>
		<div class="alert alert-info">This event is happening today</div>
	<?php endif; ?>

	<table class="borderless" style="font-size: 16px;">
		<tr>
			<th class="text-right">Event Start:</th>
			<td><?php echo mysql2date( 'l, F j, Y (g:i a)', get_post_meta( get_the_ID(), '_event_start_date', true ) . ' ' . get_post_meta( get_the_ID(), '_event_start_time', true ) ); ?></td>
		</tr>
		<tr>
			<th class="text-right">Event End:</th>
			<td><?php echo mysql2date( 'l, F j, Y (g:i a)', get_post_meta( get_the_ID(), '_event_end_date', true ) . ' ' . get_post_meta( get_the_ID(), '_event_end_time', true ) ); ?>
				<i>(Expected)</i></td>
		</tr>
		<tr>
			<th class="text-right">Location:</th>
			<td><?php if ( 'private' == $location_post->post_status ) : ?>
					<?php echo esc_html( $location_post->post_title ); ?>
				<?php elseif ( $location_post->ID ) : ?>
					<a href="<?php echo network_get_permalink( 1, $location_post->ID ); ?>"><?php echo esc_html( $location_post->post_title ); ?></a>
				<?php else: ?>
					Not specified, see notes.
				<?php endif; ?></td>
		</tr>
		<?php if ( is_committee() ) : ?>
			<tr>
				<th class="text-right">Committee:</th>
				<td><a href="<?php site_url(); ?>"><?php bloginfo( 'name' ); ?></a></td>
			</tr>
		<?php endif; ?>

		<?php if ( get_the_excerpt() ) : ?>
			<tr>
				<th class="text-right">Notes:</th>
				<td><?php the_excerpt(); ?></td>
			</tr>
		<?php endif; ?>
		<?php if ( is_broadcast( get_the_ID() ) ): ?>
			<tr>
				<th class="text-right">Broadcast:</th>
				<td>
					<?php the_broadcast_message( get_the_ID() ); ?><br>
					<i class="notes"><?php the_broadcast_notes( get_the_ID() ); ?></i>
				</td>
			</tr>
		<?php endif; ?>
	</table>
<?php endif; ?>
