<?php
/*
Template Name: Special - Board Members List Page
*/
inject_content( function ( $content ) { ?>
	<div id="meetings_" class="tab visibletab">
		<div class="tablecap">
			<div class="cap">Members</div>
			<table cellspacing="0">
				<thead>
				<tr>
					<th style="width:160px">Name</th>
					<th style="width:0px">Position</th>
					<th style="width:180px">Address</th>
					<th style="width:0px">Term</th>
					<th style="width:0px">End Date</th>
					<th style="width:103px">Appointed By</th>
				</tr>
				</thead>
				<tbody>
				<?php $members = get_option( 'sudbury_board_membership', array() );
				//"name"
				//"position"
				//"address"
				//"term"
				//"end_date"

				if ( $members ) {
					foreach ( $members as $member ) : ?>
						<tr>
							<td><?php echo esc_html( $member['name'] ); ?></td>
							<td><?php echo esc_html( $member['position'] ); ?></td>
							<td><?php echo esc_html( $member['address'] ); ?></td>
							<td style="text-align:center"><?php echo esc_html( $member['term'] ); ?></td>
							<td><?php echo esc_html( $member['end_date'] ); ?></td>
							<td><?php echo esc_html( $member['appointed_by'] ); ?></td>
						</tr>
					<?php endforeach;
				} else {
					?>
					<tr>
						<td colspan="6">No Members Currently Registered with this Committee</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<div class="foot"></div>
		</div>
	</div>
<?php } );

get_template_part( 'page' );




