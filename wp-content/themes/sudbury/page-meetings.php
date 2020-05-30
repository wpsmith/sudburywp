<?php
/*
Template Name: Special - Meetings List Page
*/


inject_content( function ( $content ) {
	$show_all = isset( $_GET['show_all_meetings'] ) || is_archived( get_current_blog_id() );
	if ( $show_all ) :
		$meeting_link = '<b><a style="float:right;" href=""> Limit Meeting Results </a></b>';
	else :
		$meeting_link = '<b><a style="float:right;" href="?show_all_meetings"> Show All Meetings </a></b>';
	endif;
	?>
	<?php $meetings    = get_posts( array(
		'post_type'      => 'meeting',
		'posts_per_page' => ( $show_all ? - 1 : 50 ),
		'post_status'    => array( 'publish', 'future' ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	$flushed           = false;
	$meeting_doc_types = get_site_option( 'sudbury_attachment_types', array() )['meeting'];
	?>
	<?php if ( $meetings ) : ?>
		<div id="meetings_" class="tab visibletab">
			<div class="tablecap">
				<div class="cap">Future Meetings <?php echo $meeting_link; ?></div>
				<table cellspacing="0">
					<tbody>
					<tr>
						<th style="width:250px;">Date</th>
						<th>Details</th>
					</tr>
					<?php
					$now  = current_time( 'mysql' );
					$rows = array(); ?>
					<?php foreach ( $meetings

					as $meeting ) : ?>

					<?php setup_postdata( $meeting );

					$location          = sudbury_get_location_post( get_post_meta( $meeting->ID, '_location_id', true ), false, array(
						'post_status' => array(
							'publish',
							'public-archive',
							'private',
							'future'
						)
					) );
					$meeting_timestamp = get_post_meta( $meeting->ID, '_event_start_date', true ) . ' ' . get_post_meta( $meeting->ID, '_event_start_time', true );
					$meeting_time      = mysql2date( 'D, F j, Y (g:i a)', $meeting_timestamp );

					if ( $meeting_timestamp < $now ) : ?>
					<?php $flushed = true; ?>

					<?php if ( ! empty( $rows ) ) : ?>
						<?php echo implode( '', array_slice( array_reverse( $rows ), 0, 8 ) ); ?>
						<?php $rows = array(); ?>
					<?php else : ?>
						<tr>
							<td colspan="3"> This Committee has not posted any future meetings</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>

				<div class="foot"></div>
			</div>
			<div class="tablecap">
				<div class="cap">Past Meetings <?php echo $meeting_link; ?></div>
				<table cellspacing="0">
					<tbody>
					<tr>
						<th style="width:250px;">Date</th>
						<th>Details</th>
					</tr>
					<?php $now = 0; ?>
					<?php endif; ?>
					<?php if ( ! $flushed ) {
						ob_start();
					} ?>
					<tr>
						<?php $files = sudbury_get_meeting_attachments( $meeting->ID ); ?>
						<td>
							<a href="<?php echo get_permalink( $meeting->ID ); ?>"><?php echo esc_html( $meeting_time ); ?></a>
						</td>
						<td>
							<?php if ( $location ) : ?>
								<?php if ( 'private' == $location->post_status ) : ?>
									<?php echo esc_html( $location->post_title ); ?><br>
								<?php else : ?>
									<a href="<?php echo network_get_permalink( $location->BLOG_ID, $location->ID ); ?>"> <?php echo esc_html( $location->post_title ); ?> </a>
									<br>
								<?php endif; ?>
							<?php elseif ( $minutes || $agendas ) : ?>
								See Minutes/Agenda for Location
							<?php endif; ?>
							<?php if ( $meeting->post_excerpt ) : ?>
								<i class="meeting_comments"><?php echo $meeting->post_excerpt; ?></i><br />
							<?php endif; ?>
							<?php
							if ( $files ) {
								echo "Files: ";
								echo( implode( ' &bull; ', array_map( function ( $attachment ) use ( &$meeting_doc_types ) {
									return '<a href="' . get_permalink( $attachment ) . '">' . esc_html( $meeting_doc_types[ sudbury_get_meeting_attachment_type( $attachment->ID ) ] ) . '</a>';
								}, $files ) ) );
							}

							?>
						</td>
					</tr>
					<?php
					if ( ! $flushed ) {
						$rows[] = ob_get_clean();
					}
					wp_reset_postdata();
					endforeach;
					// If we never flushed then flush now
					if ( ! $flushed ) {
						echo implode( '', array_slice( array_reverse( $rows ), 0, 8 ) );
					}
					?>
					</tbody>
				</table>
				<div class="foot"></div>

			</div>
		</div>

	<?php else : ?>
		<?php echo apply_filters( 'sudbury_no_posts_label', get_post_type_object( 'meeting' )->labels->not_found ); ?>

	<?php
	endif;
	?>
<?php } );

get_template_part( 'page' );