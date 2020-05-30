<?php
/**
 * The metabox for editing the the office hours of a department or committee
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Admin
 */

/**
 * @param $data Sudbury Department Settings Data Array
 */
function sudbury_office_hours_meta_box( $data ) { ?>
	<div class="sudbury-metabox sudbury-contact-metabox">
		<label for="sudbury_office_hours">
			<?php wp_editor( $data['sudbury_office_hours'], 'sudbury_office_hours', array('media_buttons' => false, 'textarea_rows' => 4)); ?> 
		</label>
	</div>
<?php
}
