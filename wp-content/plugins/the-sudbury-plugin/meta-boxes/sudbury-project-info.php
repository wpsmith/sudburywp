<?php
/**
 * The metabox for editing some extra settings for a department or committee
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Admin
 */

/**
 * @param $data
 */
function sudbury_project_settings_metabox( $data ) {
	global $wpdb;
	$fields = get_site_option('project_fields');
	$fields = array(
		array(
			'id' => 'sudbury_project_funding',
			'title' => 'Funding',
			'type' => 'text',
			'default' => ''
		),
		array(
			'id' => 'sudbury_project_updated',
			'title' => 'Last Updated',
			'type' => 'text',
			'default' => ''
		),
		array(
			'id' => 'sudbury_project_completion',
			'title' => 'Completion Date',
			'type' => 'text',
			'default' => ''
		),
		array(
			'id' => 'sudbury_project_notes',
			'title' => 'Notes',
			'type' => 'text',
			'default' => ''
		),
		array(
			'id' => 'sudbury_project_status',
			'title' => 'Status',
			'type' => 'text',
			'default' => ''
		),
	);
	?>
	<div class="sudbury-metabox sudbury-more-dept-settings-metabox">
		<div class="sudbury-settings-container">
			<section id="sudbury_projects">	
				<p>The network administrator has requested the following fields be completed for this project</p>
				<table class="form-table">
				<tbody>
					<?php foreach ( $fields as $field ) : ?>
					<tr>
						<th>
							<label for="<?php esc_attr_e( $field['id'] ); ?>">
								<b><?php esc_html_e( $field['title'] ); ?></b>
							</label>
						</th>
						<td>	
							<input type="<?php esc_attr_e( $field['type'] ); ?>" id="<?php esc_attr_e( $field['id'] ); ?>" name="<?php esc_attr_e( $field['id'] ); ?>" class="form-input-tip widefat" autocomplete="on" value="<?php echo esc_attr( get_option( $field['id'], $field['default'] ) ); ?>">
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			</section>
			
		</div><!-- /.sudbury-settings-container -->
	</div><!-- /.sudbury-metabox -->
<?php
}
