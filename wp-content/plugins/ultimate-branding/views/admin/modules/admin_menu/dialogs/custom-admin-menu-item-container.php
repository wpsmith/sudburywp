<script type="text/html" id="tmpl-menu-item-container">
	<div class="sui-box-builder">
		<div class="sui-box-builder-header">
			<div class="sui-builder-options sui-options-inline">
				<label class="sui-checkbox sui-checkbox-sm">
					<input type="checkbox" class="branda-menu-select-all"/>
					<span aria-hidden="true"></span>
					<span><?php esc_html_e( 'Select All', 'ub' ); ?></span>
				</label>

				<span class="branda-custom-menu-bulk-controls">
					<button class="sui-button-icon sui-button-outlined sui-tooltip branda-custom-admin-menu-duplicate"
					        data-tooltip="<?php esc_html_e( 'Duplicate', 'ub' ); ?>">

						<i class="sui-icon-copy" aria-hidden="true"></i>
						<span class="sui-screen-reader-text">
							<?php esc_html_e( 'Duplicate', 'ub' ); ?>
						</span>
					</button>

					<button class="sui-button-icon sui-button-outlined sui-tooltip branda-custom-admin-menu-hide"
					        data-tooltip="<?php esc_html_e( 'Hide', 'ub' ); ?>">

						<i class="sui-icon-eye-hide" aria-hidden="true"></i>
						<span class="sui-screen-reader-text">
							<?php esc_html_e( 'Hide', 'ub' ); ?>
						</span>
					</button>

					<button class="sui-button-icon sui-button-outlined sui-tooltip branda-custom-admin-menu-unhide"
					        data-tooltip="<?php esc_html_e( 'Unhide', 'ub' ); ?>">

						<i class="sui-icon-eye" aria-hidden="true"></i>
						<span class="sui-screen-reader-text">
							<?php esc_html_e( 'Unhide', 'ub' ); ?>
						</span>
					</button>
				</span>
			</div>
		</div>

		<div class="sui-box-builder-body">
			<div class="sui-builder-fields sui-accordion">
			</div>

			<button class="sui-button sui-button-dashed">
				<i class="sui-icon-plus" aria-hidden="true"></i>
				<?php esc_html_e( 'Add Item', 'ub' ); ?>
			</button>
		</div>
	</div>
</script>