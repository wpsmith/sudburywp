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
function sudbury_site_type_meta_box( $data ) { ?>
	<div class="sudbury-metabox sudbury-contact-metabox">
		<section id="sudbury_site_type">
			<label for="sudbury_type">
				<?php
				// The list of current types
				$currenttypes = $data['sudbury_types'];

				$allowedtypes = get_site_option( 'sudbury_allowedtypes' );
				?>
				<div class="posttypediv" style="width:45%">
					<ul class="posttype-tabs add-menu-item-tabs" style="margin-bottom:3px;">
						<li class="tabs" style="display: inline; line-height: 1.35em;">
							<a class="nav-tab-link" style="color: #333;" href="#Types">Type</a></li>
					</ul>
					<div id="recent-blogs" class="tabs-panel tabs-panel-active">
						<ul class="categorychecklist form-no-clear">

							<?php foreach ( $allowedtypes as $allowedtype ) : ?>
								<li>
									<label><input type="checkbox" name="sudbury_types_<?php echo esc_attr( $allowedtype ); ?>" <?php checked( in_array( $allowedtype, $currenttypes ) ); ?>> <?php echo ucfirst( $allowedtype ); ?>
									</label>
								</li>
							<?php endforeach; ?>

						</ul>
					</div>
				</div>
			</label>
			<br>
			To List a site as informational please select the appropriate Type of site above or none. <br>
			Please remember to list it manually in the main menu
			<a href="/wp-admin/nav-menus.php?menu=57">here - for departments</a> and
			<a href="/wp-admin/nav-menus.php?menu=42">here - for committees</a>
		</section>
	</div>
<?php
}





