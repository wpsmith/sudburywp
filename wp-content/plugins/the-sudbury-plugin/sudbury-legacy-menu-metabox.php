<?php
/**
 * Adds the Legacy Navigations Menu to the Dashboard.  This is a very important feature for launch
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Legacy
 */

function sudbury_add_legacy_menu_metabox() {
	add_meta_box( 'sudbury-legacy-menu-metabox', 'Legacy Main Menu', 'sudbury_legacy_menu_metabox', 'dashboard', 'normal', 'default' );
}

add_action( 'admin_menu', 'sudbury_add_legacy_menu_metabox' );


function sudbury_legacy_menu_metabox() {
	?>
	<div class="legacy-menu-container">
		<h1 class="BarRed text-center"><?php bloginfo( 'name' ); ?> </h1>
		<div class="Menu" style="text-align: center;  width: 100%; max-width:440px">

			<div>
				<h2>Welcome to Wordpress <br>
					<small>(The New Webeditor)</small>
				</h2>
			</div>
			<div>

                <span class="ColumnItem">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=sudbury-dept-info-options-page' ) ); ?>" title="Change the department contact information and description." class="Button Section">Dept. Information</a>
                </span>
                <span class="ColumnItem">
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=post' ) ); ?>" title="Announce department news on the department page, front page, and calendar." class="Button Section">News Articles</a>
                </span>
                <span class="ColumnItem">
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=faq' ) ); ?>" title="Answer frequently-asked questions." class="Button Section">Questions (FAQs)</a>
                </span>
                <span class="ColumnItem">
                    <a href="<?php echo esc_url( admin_url( 'upload.php?post_mime_type=application' ) ); ?>" title="Publish Office documents and make them available for download." class="Button Section">Documents</a>
                </span>

				<?php if ( is_committee() ) : ?>
					<span class="ColumnItem">
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=meeting' ) ); ?>" title="Announce a scheduled meeting and publish an agenda or meeting summary." class="Button Section">Meetings</a>
					</span>
				<?php endif; ?>

				<span class="ColumnItem">
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=link' ) ); ?>" title="Recommend related resources located on the Internet." class="Button Section">Links</a>
                </span>
                <span class="ColumnItem">
                    <?php if (is_committee()) { ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sudbury-dept-head-options-page' ) ); ?>" title="Publish a message from the department head." class="Button Section">From the Chairperson</a>
                    <?php } else { ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=sudbury-dept-head-options-page' ) ); ?>" title="Publish a message from the department head." class="Button Section">From the Dept. Head</a>
                    <?php } ?>
                </span>
                <span class="ColumnItem">
                    <a href="<?php echo esc_url( admin_url( 'upload.php?post_mime_type=image' ) ); ?>" title="Publish pictures for use in news articles, and create galleries." class="Button Section">Photos and Images</a>
                </span>
                <?php if ( ! is_committee() ) : ?>
                <span class="ColumnItem">
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=paging_item' ) ); ?>" title="Add, change, or remove internal pager numbers." class="Button Section">Paging Numbers</a>
                </span>
                <?php endif; ?>

     

			</div>

			<?php

			$features = sudbury_get_active_features( array( 'source_req' => true ) );
			if ( $features ) : ?>
				<div class="department-features">

					<h2>Department Features</h2>
					<?php
					foreach ( $features as $feature )  : ?>
						<span class="ColumnItem">
                        <a href="<?php echo sudbury_extra_features_source( $feature ); ?>" title="<?php echo esc_attr( $feature['page-title'] ); ?>" class="Button Section" target="_blank"><?php echo esc_html( $feature['page-title'] ); ?></a>
                    </span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div style="font-size: smaller;">
				<h2>User Information</h2>

            <span class="ColumnItem">
                <a href="<?php sudbury_the_help_url( 'network-passwords' ); ?>" title="Change your Wordpress account password." class="Button Action">Change Password</a>
            </span>
            <span class="ColumnItem">
                <?php global $current_user;
                get_currentuserinfo(); ?>


	            <a href="<?php echo esc_url( wp_logout_url() ); ?>" title="Sign out from the WebEditor." class="Button Action" target="_top">Logout (<?php echo esc_html( $current_user->user_login ); ?>)</a>
            </span>

            <span class="ColumnItem">
                <a href="<?php echo esc_url( admin_url( 'my-sites.php' ) ); ?>" title="Manage another <?php sudbury_the_site_type(); ?>'s features and publications." class="Button Action" target="_top">Switch <?php sudbury_the_site_type(); ?></a>
            </span>

			</div>

			<?php if ( is_super_admin() ) : ?>
				<div style="font-size: smaller;">
					<h2>Web Site Administration</h2>

            <span class="ColumnItem">
                <a href="<?php echo esc_url( network_admin_url( 'users.php' ) ); ?>" title="Add, change, or remove WebEditor users." class="Button Tool">Users</a>
            </span>
            <span class="ColumnItem">
                <a href="<?php echo esc_url( network_admin_url( 'sites.php' ) ); ?>" title="Add, change, or remove departments, and choose their features." class="Button Tool">Departments</a>
            </span>
            <span class="ColumnItem">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sudbury-extra-feature-admin' ) ); ?>" title="Add, change, or remove department features." class="Button Tool">Department Features</a>
            </span>
            <span class="ColumnItem">
                <a href="<?php echo esc_url( '/wp-admin/edit.php?post_type=location&location_categories=meeting-location' ); ?>" title="Add, change, or remove meeting locations available." class="Button Tool">Meeting Locations</a>
            </span>
            <span class="ColumnItem">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=location' ) ); ?>" title="Add, change, or remove town properties and their addresses." class="Button Tool">Town Properties</a>
            </span>
            <span class="ColumnItem">
                <a href="<?php sudbury_the_help_url( 'polls-and-surveys' ); ?>" title="Surveys" class="Button Tool">Surveys</a>
            </span>
            <span class="ColumnItem">
                <a href="<?php sudbury_the_help_url( 'polls-and-surveys' ); ?>" title="Homepage Poll" class="Button Tool">Homepage Poll</a>
            </span>
            <span class="ColumnItem">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=event' ) ); ?>" title="Add, change, or remove Events from the town calendar." class="Button Tool">Easy Events</a>
            </span>

            <span class="ColumnItem">
                <a href="https://www.google.com/analytics/web/?pli=1#report/visitors-overview/a29857741w56134388p57206012/" target="_blank" title="View All Google Analytics Data for the entire Site" class="Button Tool">Google Analytics (all)</a>
            </span>
        <span class="ColumnItem">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>" title="View All Custom Pages in the Database" class="Button Tool">Custom Pages</a>
            </span>


				</div>

			<?php endif; ?>

		</div>
	</div>

<?php

}
