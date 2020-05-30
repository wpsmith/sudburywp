<?php

$sudbury_migrate_latest = 2;

function sudbury_migrate() {
	if ( ! is_super_admin() ) {
		wp_die( 'You are not authorized to view this page' );
	}

	wp_verify_nonce( 'sudbury-migrate-nonce' );

	$version = get_site_option( 'sudbury_migration_version' );
	if ( $version === false ) {
		$version = 0;
	}
	$version = absint( $version );
	if ( $version < 2 ) {
		// Run v2 Upgrades (July 2018 Responsive Design Upgrades)

		// Change all Search Titles to "Search" if they currently say "Search the entire Sudbury site"
		foreach_blog( function ( $b ) {
			$widgets = get_option( 'widget_search', false );

			if ( $widgets === false ) {
				return;
			}

			$new_widgets = array();
			foreach ( $widgets as $idx => $widget ) {
				if ( $widget['title'] == 'Search the entire Sudbury site' ) {
					$widget['title'] = 'Search';
				}
				$new_widgets[ $idx ] = $widget;
			}

			update_option( 'widget_search', $new_widgets );
		} );
		$version = 2;
	}

	update_site_option( 'sudbury_migration_version', $version );
	wp_redirect( admin_url( 'network/admin.php?page=sudbury-migrate-page' ) );
}

add_action( 'admin_post_sudbury_migrate', 'sudbury_migrate' );


function sudbury_migrate_notice() {
	global $sudbury_migrate_latest;
	$version = get_site_option( 'sudbury_migration_version' );

	if ( $version < $sudbury_migrate_latest ) {
		?>
		<div class="error">
			<p>
				Migrations pending!
				<a href="<?php echo admin_url( 'network/admin.php?page=sudbury-migrate-page' ); ?>">Run Migrations</a>
			</p>
		</div>
		<?php
	}
}

add_action( 'network_admin_notices', 'sudbury_migrate_notice' );


/**
 * Registers the Sudbury Plugin Settings Page in the Wordpress Network Admin
 */
function sudbury_register_migrate_page() {
	if ( current_user_can( 'manage_network' ) ) {
		add_menu_page( 'Sudbury Migrate', 'Sudbury Migrate', 'manage_network', 'sudbury-migrate-page', 'sudbury_migrate_page', '', 13 );
	}
}

add_action( 'network_admin_menu', 'sudbury_register_migrate_page' );

/**
 * Renders the Sudbury Plugin's Settings Page
 */
function sudbury_migrate_page() {
	global $sudbury_migrate_latest;
	$version = get_site_option( 'sudbury_migration_version' );

	if ( ! current_user_can( 'manage_network' ) ) {
		wp_die( 'You don\'t have permission to access this page' );
	}
	?>
	<div class="wrap">
		<h2>Sudbury Plugins Settings</h2>

		<form class="dept-info-form" action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<?php wp_nonce_field( 'sudbury-migrate-nonce' ); ?>

			<input type="hidden" name="action" value="sudbury_migrate" />

			<p> Current Migration Version: <?php echo esc_html( $version ); ?></p>

			<p> New Migration Version: <?php echo esc_html( $sudbury_migrate_latest ); ?></p>

			<?php do_action( 'admin_notices' ); ?>

			<?php submit_button( 'Run Migrations' ); ?>
		</form>
	</div>
	<?php
}