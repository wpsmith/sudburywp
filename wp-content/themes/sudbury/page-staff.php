<?php
/*
Template Name: Special - Staff List Page
*/

inject_content( function ( $content ) {
	$counterparts = get_option( 'sudbury_counterparts' );
	$children     = get_option( 'sudbury_children' );
	$related      = array();
	$the_blog_id  = get_current_blog_id();
// List through the counterparts and add related[i] = 'counterpart' where i is the blog id
	foreach ( $counterparts as $counterpart ) {
		$related[ $counterpart ] = 'counterpart';
	}
// Now list through all the Children and overwrite any Counterpart as a Child
	foreach ( $children as $child ) {
		$related[ $child ] = 'child';
	}

	if ( $personnel = get_option( 'sudbury_fm_personnel' ) ) :
		?>

		<div id="staff_" class="tab">
			<div class="tablecap">
				<div class="cap">
					<i class="fa fa-home"></i> <?php bloginfo( 'blogname' ); ?>
					<span class="pull-right"><a
							href="mailto:<?php echo esc_attr( get_option( 'sudbury_email' ) ); ?>"><?php echo esc_html( get_option( 'sudbury_email' ) ); ?></a>  | <?php echo esc_html( get_option( 'sudbury_telephone' ) ); ?></span>

				</div>

				<table cellspacing=0>
					<tbody>
					<tr>
						<th width="25%">Name</th>
						<th width="35%">Position</th>
						<th>Office Location</th>
					</tr>
					<?php foreach ( $personnel as $person ) : ?>

						<?php $staff_department = $person['dept']; ?>
						<tr>
							<?php if ( $person['title'] ) : ?>
								<td class="coltitle"><?php echo esc_html( $person['full_name'] ); ?> </td>
								<td><?php echo esc_html( $person['title'] ); ?></td>
							<?php else : ?>
								<td colspan=2 class="coltitle"><?php echo $person['full_name']; ?></td>
							<?php endif; ?>
							<td><?php echo esc_html( $person['building'] ); ?></td>

						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<div class="foot"></div>
			</div>
		</div>

	<?php else: ?>
		<p class="no-documents"> Sorry The Staff Listing for this Department is not complete </p>

	<?php endif; ?>
<?php } );
get_template_part( 'page' );