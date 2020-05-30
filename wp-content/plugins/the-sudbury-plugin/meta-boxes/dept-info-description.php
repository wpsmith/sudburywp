<?php
/**
 * The metabox for editing the description for department or committee
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Admin
 */

/**
 * Renders the description paragraph
 *
 * @param array $data The Department Info DataSet
 */
function sudbury_dept_description_metabox( $data ) {
	?>
	<div class="editor-container" id="sudbury_banner_description_container">
		<?php
		wp_editor( $data['sudbury_description_paragraph'], 'sudbury_description_paragraph' );
		?>
	</div>
<?php
}


/* Option saving is taken care of in ../sudbury-dept-info-admin.php by the sudbury settings api */