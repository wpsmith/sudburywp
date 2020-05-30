<?php
/**
 * The metabox for editing the the office location of a department or committee
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Admin
 */

/**
 * @param $data Sudbury Department Settings Data Array
 */
function sudbury_relationships_meta_box( $data ) { ?>
	<div class="sudbury-metabox sudbury-contact-metabox">
			<section id="sudbury_department_relationships">
				<label for="sudbury_parent">
					<?php
					// I Cannot $wpdb->prepare this because there are no variables and thus no chance of SQL injection
					$exclude   = get_option( 'sudbury_children', array() );
					$exclude[] = get_current_blog_id();
					$blogs     = get_blogs( array( 'all' => true, 'exclude' => $exclude ) );
					usort($blogs, function ($a, $b) { return $a['title'] >= $b['title']; });
					if ( empty( $blogs ) ) {
						echo 'No Sites Found !!!! Error !!!!';
					} else {

						?>
						<table class="form-table">
							<tbody>
								<tr>
									<th>Parent Site</th>
									<td>
										<select name="sudbury_parent" id="sudbury_parent" style="display: inline-block">
											<option value="0">- No Parent -</option>
											<?php foreach ( $blogs as $blog ) : ?>
												<option value="<?php echo esc_attr( $blog['id'] ); ?>" <?php selected( $blog['id'], $data['sudbury_parent'] ); ?>> <?php echo esc_html( $blog['title'] ); ?> </option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>Show Documents</th>
									<td>
										<?php $meta = sudbury_get_relationship_meta( $data['sudbury_parent'], get_current_blog_id(), 'parent' ); ?>
										<?php $retrieve_documents = (bool) ( isset( $meta['retrieve_documents'] ) && $meta['retrieve_documents'] ); ?>
										<?php sudbury_on_off_switch( $retrieve_documents, "sudbury_relationship_meta[{$data['sudbury_parent']}][parent][retrieve_documents]", 'sudbury_parent_blog_show_docs'); ?>
									</td>
								</tr>
							</tbody>
						</table>

					<?php } ?>
				</label>
				<br />
				<label for="sudbury_counterparts">


					<p><b>Counterparts</b></p>
					<input type="hidden" name="sudbury_proccess_relationship_meta" value="1">
					<table id="counterparts-table" class="wp-list-table widefat">
						<thead>
							<tr>
								<th class="manage-column">ID</th>
								<th class="manage-column">Blog Name</th>
								<th class="manage-column">Manage</th>
								<th class="manage-column">Show Documents</th>
								<th class="manage-column">Actions</th>
							</tr>
						</thead>
						<tbody>
						<?php if ( $data['sudbury_counterparts'] ) : $row=-1; ?>
							<?php foreach ( $data['sudbury_counterparts'] as $counterpart ) :
								$row++;
								$retrieve_documents = (bool) sudbury_get_relationship_meta( $counterpart, get_current_blog_id(), 'counterpart' )['retrieve_documents'];
								?>
							<tr class="<?php if ( $row % 2 == 0 ) { echo 'alternate'; }; ?>">
								<td><?php echo esc_html( $counterpart ); ?><input type="hidden" name="sudbury_counterparts[]" value="<?php echo esc_attr( $counterpart ); ?>"></td>
								<td><?php sudbury_the_site_name( $counterpart ); ?></td>
								<td>
									<?php switch_to_blog($counterpart); ?>
									<a href="<?php echo admin_url(); ?>">Dashboard</a> -
									<a href="<?php echo site_url(); ?>">Visit</a> -
									<a href="<?php echo admin_url( 'admin.php?page=sudbury-dept-info-options-page' ); ?>">Site Info</a>
									<?php restore_current_blog(); ?>
								</td>
								<td><?php sudbury_on_off_switch( $retrieve_documents, "sudbury_relationship_meta[$counterpart][counterpart][retrieve_documents]"); ?></td>
								<td style="white-space: nowrap; text-align: left;"><span class="delete-row">Remove</span></td>
							</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="no-items">
								<td colspan="5">No Counterparts</td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>

					<p><b>Add A Counterpart</b></p>
					<?php
					$blogs = get_blogs( array(
						'all'     => true,
						'exclude' => get_current_blog_id(),
						'sort'    => 'shortname',
					) );
					if ( empty( $blogs ) ) {
						echo 'No Sites Found !!!! Error !!!!';
					} else {

						?>

						<select name="sudbury_counterparts[]" id="sudbury_counterparts_add" style="display: inline-block;">
							<option value="-1">Select A Site to add as a Counterpart</option>
							<?php foreach ( $blogs as $blog ) :
								if (in_array( $blog['id'], $data['sudbury_counterparts'] )) { continue; } ?>
								<option value="<?php echo esc_attr( $blog['id'] ); ?>"> <?php echo esc_html( $blog['title'] ); ?> </option>
							<?php endforeach; ?>
						</select>
						<input type="submit" class="button button-primary" value="Add Counterpart" id="add-counterpart" />

						<input type="hidden" name="sudbury_counterparts[]" value="-1" />
					<?php } ?>
				</label>

				<br />
				<label for="sudbury_children">


					<p><b>Children - Read Only</b></p>

					<table id="counterparts-table" class="wp-list-table widefat">
						<thead>
						<tr>
							<th class="manage-column">ID</th>
							<th class="manage-column">Blog Name</th>
							<th class="manage-column">Manage</th>
							<th class="manage-column">Show Documents</th>
						</tr>
						</thead>
						<tbody>

						<?php if ( $data['sudbury_children'] ) : $row=-1; ?>
							<?php foreach ( $data['sudbury_children'] as $child ) :
								$row++;
								$child_meta = sudbury_get_relationship_meta( $child, get_current_blog_id(), 'child' );

								$retrieve_documents = isset($child_meta['retrieve_documents']) && $child_meta['retrieve_documents'];
								?>
								<tr class="<?php if ( $row % 2 == 0 ) { echo 'alternate'; }; ?>">
									<td><?php echo esc_html( $child ); ?></td>
									<td><?php sudbury_the_site_name( $child ); ?></td>
									<td>
										<?php switch_to_blog($child); ?>
										<a href="<?php echo admin_url(); ?>">Dashboard</a> |
										<a href="<?php echo site_url(); ?>">Visit</a> |
										<a href="<?php echo admin_url( 'admin.php?page=sudbury-dept-info-options-page' ); ?>">Site Info</a>
										<?php restore_current_blog(); ?>
									</td>
									<td><?php sudbury_on_off_switch( $retrieve_documents, "sudbury_relationship_meta[$child][child][retrieve_documents]"); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="no-items">
								<td colspan="4">No Children</td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>

				</label>
				<script>
					(function($) {
						$('.delete-row').click(function () {
							var $row = $(this).parents('tr');
							$row.animate({'background-color': '#F00'}, 500, function () {
								$row.fadeOut(500, function () {
									$row.remove();
								});
							})
						})

						$('#sudbury_parent').change(function () {
							$('#sudbury_parent_blog_show_docs').attr('name', 'sudbury_relationship_meta[' + $(this).val() + '][parent][retrieve_documents]')
						})
					})(jQuery)

				</script>
				<style type="text/css">
					.delete-row {
						color: #a00;
					}
					.delete-row:hover {
						color: #f00;
					}
					.reminders {
						display: none;
						position: fixed;
						width: 250px;
						height: 80px;
						right:10px;
						top:30px;
						z-index: 1000;
					}
					.save-reminder {
						background-color: #fff;
						border: 1px solid rgb(229, 229, 229);
						margin-top: 20px;
						border-radius: 5px;
						padding: 0 20px;

					}
				</style>
			</section>
	</div>
<?php
}





