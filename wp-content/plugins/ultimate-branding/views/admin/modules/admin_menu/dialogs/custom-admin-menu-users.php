<?php
$search_nonce = empty( $search_nonce ) ? '' : $search_nonce;
?>

<div class="branda-custom-admin-menu-users">
	<div class="sui-box-body">
		<div>
			<label for="branda-admin-menu-user-search" class="sui-label">
				<?php esc_html_e( 'Custom users', 'ub' ); ?>
			</label>

			<select class="sui-select sui-select-ajax"
			        id="branda-admin-menu-user-search"
			        data-placeholder="<?php esc_attr_e( 'Search user', 'ub' ); ?>"
			        data-action="branda_admin_menu_search_user"
			        data-user-id="<?php echo esc_attr( get_current_user_id() ); ?>"
			        data-nonce="<?php echo esc_attr( $search_nonce ); ?>">
			</select>

			<span class="sui-description">
				<?php esc_html_e( 'Search and add users to customize the admin menu.', 'ub' ); ?>
			</span>
		</div>
	</div>

	<div class="branda-custom-admin-menu-user-tabs-container sui-box-body"></div>
</div>