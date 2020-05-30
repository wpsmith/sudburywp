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
function sudbury_archive_state_meta_box( $data ) { ?>
	<div class="sudbury-metabox sudbury-contact-metabox">
		<section id="sudbury_archiving_options">
			<label for="sudbury_archived_department">
					<p>To Archive or unarchive this <?php sudbury_the_site_type(); ?> go to the Network Level Control Panel
					<a href="<?php echo network_admin_url( 'site-info.php?id=' . get_current_blog_id() ); ?>">here</a></p>
			</label>

			<p>
				<strong>Current Status: 
				<?php if ( sudbury_is_site_archived() ) : ?>
					<span class="wp-ui-text-notification">archived</span>
				<?php else : ?>
					<span class="wp-ui-text-highlight">not archived</span>
				<?php endif; ?>
				</strong>
			</p>

			<div style="<?php if ( !sudbury_is_site_archived() ) : ?>display:none;<?php endif; ?>">
			<label for="sudbury_archived_message"> <b>Archived Message</b><br>
				<input type="text" id="sudbury_archived_message" name="sudbury_archived_message" class="form-input-tip" autocomplete="on" value="<?php echo esc_attr( $data['sudbury_archived_message'] ); ?>" style="width:100%">
				<i>This is a custom message to display in the brown bar of an archived site. If specified it overrides the default archived message</i>
			</label>

			<?php if ( 'Department' == sudbury_get_site_type() ) : ?>
				<p><b>Example:</b> <code>This Department was dissolved on DATE</code></p>
				<p><b>Example:</b> <code>This Department was merged with the DEPARTMENT</code></p>
			<?php else : ?>
				<p><b>Example:</b> <code>This Committee was dissolved by the Board of Selectmen on DATE</code></p>

			<?php endif; ?>
			</div>

		</section>
	</div>
<?php
}





