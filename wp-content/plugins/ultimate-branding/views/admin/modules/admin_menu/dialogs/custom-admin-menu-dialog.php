<?php
$dialog_id = empty( $dialog_id ) ? '' : $dialog_id;
$search_nonce = empty( $search_nonce ) ? '' : $search_nonce;
?>

<div class="sui-dialog"
     aria-hidden="true" tabindex="-1"
     id="<?php echo esc_attr( $dialog_id ); ?>">

	<div class="sui-dialog-overlay" data-a11y-dialog-hide></div>
	<div class="sui-dialog-content"
	     role="dialog">

		<div class="sui-box branda-custom-admin-menu-dialog" role="document">
			<div class="sui-box-header">
				<h3 class="sui-box-title">
					<?php esc_html_e( 'Custom admin menu', 'ub' ); ?>
				</h3>

				<div class="sui-actions-right">
					<button data-a11y-dialog-hide class="sui-dialog-close"
					        aria-label="<?php echo esc_attr_x( 'Close this dialog window', 'button', 'ub' ); ?>">
					</button>
				</div>
			</div>

			<div class="sui-box-body">
				<p><?php esc_html_e( "Customize the admin menu by user role or separately for custom users. You can change the order of menu items, hide items you don't want and add new items as needed.", 'ub' ); ?></p>

				<div class="sui-row">
					<div class="sui-col-md-6">
						<label>
							<select class="sui-select"
							        id="branda-admin-menu-role-user-switch"
							        data-minimum-results-for-search="-1">
								<option value="roles" <?php checked( true ); ?>>
									<?php echo esc_html( 'User Roles' ); ?>
								</option>
								<option value="users">
									<?php echo esc_html( 'Custom Users' ); ?>
								</option>
							</select>
						</label>
					</div>
				</div>
			</div>

			<?php
			$this->render( 'admin/modules/admin_menu/dialogs/custom-admin-menu-roles', array() );
			$this->render( 'admin/modules/admin_menu/dialogs/custom-admin-menu-users', array(
				'search_nonce' => $search_nonce,
			) );
			?>
		</div>
	</div>
</div>