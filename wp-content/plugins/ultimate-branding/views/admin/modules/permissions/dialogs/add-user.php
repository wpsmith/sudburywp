<div class="sui-dialog sui-dialog-alt sui-dialog-sm sui-fade-in branda-dialog-new" aria-hidden="true" tabindex="-1" id="branda-permissions-add-user">

	<div class="sui-dialog-overlay" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content sui-bounce-in" aria-labelledby="dialogTitle" aria-describedby="dialogDescription" role="dialog">

		<div class="sui-box" role="document">

				<div class="sui-box-header sui-block-content-center">

					<h3 class="sui-box-title" id="dialogTitle"><?php esc_html_e( 'Add User', 'ub' ); ?></h3>

					<div class="sui-actions-right">
						<button data-a11y-dialog-hide class="sui-dialog-close" aria-label="<?php esc_html_e( 'Close this dialog window', 'ub' ); ?>"></button>
					</div>

				</div>

				<div class="sui-box-body sui-box-body-slim sui-block-content-center">

					<p id="dialogDescription" class="sui-description"><?php esc_html_e( 'Type the username in the searchbox to add. You can add as many users as you like.', 'ub' ); ?></p>

					<div class="sui-form-field">
						<label class="sui-label" for="searchuser"><?php echo esc_html__('Search users', 'ub'); ?></label>
						<div class="sui-control-with-icon">
							<select class="sui-select sui-form-control"
							        id="searchuser"
							        name="user"
							        data-placeholder="<?php esc_html_e( 'Type Username', 'ub' ); ?>"
							        data-hash="<?php echo esc_attr( wp_create_nonce( 'usersearch' ) ); ?>"
							        data-language-searching="<?php esc_attr_e( 'Searching...', 'ub' ); ?>"
							        data-language-noresults="<?php esc_attr_e( 'No results found', 'ub' ); ?>"
							        data-language-error-load="<?php esc_attr_e( 'Searching...', 'ub' ); ?>"
							>
							</select>
							<i class="sui-icon-profile-male" aria-hidden="true"></i>
						</div>
					</div>
				</div>

				<div class="sui-box-footer">

					<a class="sui-button sui-button-ghost" data-a11y-dialog-hide><?php esc_html_e( 'Cancel', 'ub' ); ?></a>

					<button class="sui-button branda-permissions-add-user" data-nonce="<?php echo esc_attr( wp_create_nonce( 'add_user' ) ); ?>">
						<span class="sui-loading-text"><i class="sui-icon-check" aria-hidden="true"></i><?php esc_html_e( 'Add', 'ub' ); ?></span>
						<span class="sui-loading-text-adding"><i class="sui-icon-loader" aria-hidden="true"></i><?php esc_html_e( 'Adding', 'ub' ); ?></span>
					</button>

				</div>

		</div>

	</div>

</div>