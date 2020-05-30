<?php
/**
 * Defines and kicks off all the scripts for the department information page
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Sudbury_Plugin
 */
require_once 'meta-boxes/dept-info-description.php';
require_once 'meta-boxes/from-department-head.php';
require_once 'meta-boxes/more-dept-settings.php';
require_once 'meta-boxes/sudbury-office-hours.php';
require_once 'meta-boxes/sudbury-more-site-settings.php';
require_once 'meta-boxes/sudbury-project-info.php';
require_once 'meta-boxes/sudbury-office-location.php';
require_once 'meta-boxes/sudbury-site-type.php';
require_once 'meta-boxes/sudbury-relationships.php';
require_once 'meta-boxes/sudbury-archive-settings.php';

/* MENU & HOOK */

$sudbury_dept_info_page_hook = false;
function sudbury_dept_settings_menu() {
	global $sudbury_dept_info_page_hook;
	$sudbury_dept_info_page_hook = add_menu_page( 'Sudbury Department Info', sudbury_get_site_type() . ' Info', 'edit_pages', 'sudbury-dept-info-options-page', 'sudbury_dept_info_options_page', 'dashicons-feedback', '10.4252' );
}

add_action( 'admin_menu', 'sudbury_dept_settings_menu' );

/* RENDER THE STRUCTURE OG THE PAGE */

function sudbury_dept_info_options_page() {
	global $sudbury_dept_info_page_hook;

	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'editor' );
	add_meta_box( 'sudbury-dept-info-contentbox-1', sudbury_get_site_type() . ' Contact Information', 'sudbury_dept_contact_info_metabox', $sudbury_dept_info_page_hook, 'normal', 'core' );
	add_meta_box( 'sudbury-dept-info-contentbox-2', sudbury_get_site_type() . ' Office Hours', 'sudbury_office_hours_meta_box', $sudbury_dept_info_page_hook, 'normal', 'core' );
	add_meta_box( 'sudbury-dept-info-contentbox-3', sudbury_get_site_type() . ' Description', 'sudbury_dept_description_metabox', $sudbury_dept_info_page_hook, 'side', 'core' );
	add_meta_box( 'sudbury-dept-info-contentbox-4', sudbury_get_site_type() . ' Office Location', 'sudbury_office_location_meta_box', $sudbury_dept_info_page_hook, 'normal', 'core' );

	if ( sudbury_has_type( 'project' ) ) {
		add_meta_box( 'sudbury-dept-info-contentbox-5', 'Project Information', 'sudbury_project_settings_metabox', $sudbury_dept_info_page_hook, 'side', 'core' );
	}

	if ( is_super_admin() ) {
		add_meta_box( 'sudbury-dept-info-contentbox-6', 'More ' . sudbury_get_site_type() . ' Settings', 'sudbury_more_dept_settings_metabox', $sudbury_dept_info_page_hook, 'side', 'core' );
		add_meta_box( 'sudbury-dept-info-contentbox-7', 'Site Type', 'sudbury_site_type_meta_box', $sudbury_dept_info_page_hook, 'side', 'core' );
		add_meta_box( 'sudbury-dept-info-contentbox-8', sudbury_get_site_type() . ' Relationship Options', 'sudbury_relationships_meta_box', $sudbury_dept_info_page_hook, 'side', 'core' );
		add_meta_box( 'sudbury-dept-info-contentbox-9', 'Archive State', 'sudbury_archive_state_meta_box', $sudbury_dept_info_page_hook, 'side', 'core' );
	}

	// This array of options will be sent to each metabox for proccessing
	$data = sudbury_get_options( array(
		'sudbury_email',
		'sudbury_office_hours',
		'sudbury_telephone',
		'sudbury_fax',
		'sudbury_address',
		'sudbury_location_id',
		'sudbury_board_membership_key',
		'sudbury_banner_color',
		'sudbury_banner_note',
		'sudbury_department_folder',
		'sudbury_description_paragraph',
		'sudbury_dept_head_message',
		'sudbury_dept_head_message_start',
		'sudbury_dept_head_message_end',
		'sudbury_types',
		'sudbury_default_event_duration',
		'sudbury_default_event_start_time',
		'sudbury_default_event_days_between',
		'sudbury_default_event_location',
		'sudbury_parent',
		'sudbury_counterparts',
		'sudbury_children',
		'sudbury_archived_message',
		'sudbury_facebook_url',
		'sudbury_twitter_url',
		'sudbury_youtube_url',
		'sudbury_google_plus_url',
		'sudbury_redirect_url',
	), false );

	?>

	<script type="text/javascript" src="/wp-admin/js/post.js"></script>

	<div class="wrap">
		<h1><?php sudbury_the_site_type(); ?> Information</h1>
		<form class="dept-info-form" action="admin-post.php" method="post">
			<?php submit_button( 'Save All Settings' ); ?>

			<?php wp_nonce_field( 'sudbury-dept-info-nonce' ); ?>
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			<input type="hidden" name="action" value="sudbury_save_dept_info" />

			<div id="">
				<div id="sudbury-meta-box-container" class="metabox-holder">
					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( $sudbury_dept_info_page_hook, 'normal', $data ); ?>
					</div>
					<div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( $sudbury_dept_info_page_hook, 'side', $data ); ?>
					</div>

				</div>
				<div class="clear"></div>

			</div>
			<?php submit_button( 'Save All Settings', 'primary' ); ?>
			<script>
				(function ($) {
					$(document).ready(function () {

						var action_is_post = false;
						$("form").submit(function () {
							action_is_post = true
						})
						var changed = false
						$("input, select, textarea").change(function () {
							changed = true
						})

						window.onbeforeunload = function () {
							if (!action_is_post && changed) {
								return 'You have unsaved changes, are you sure you want to navigate away?'
							}
						}
					});
				})(jQuery)
			</script>
		</form>
	</div>
	<?php
}

/* APPARENTLY THIS NEEDS TO BE DONE ??? */
/**
 * @param $columns
 * @param $screen
 *
 * @return mixed
 */
function sudbury_dept_info_layout_columns( $columns, $screen ) {
	global $sudbury_dept_info_page_hook;
	if ( $screen == $sudbury_dept_info_page_hook ) {
		$columns[ $sudbury_dept_info_page_hook ] = 2;
	}

	return $columns;
}

add_filter( 'screen_layout_columns', 'sudbury_dept_info_layout_columns', 999, 2 );

/* UPDATE THE OPTIONS IN THE DB */

/**
 * Run a filemaker sync if the board membership key was updated
 */
function sudbury_update_dept_info_settings() {
	check_admin_referer( 'sudbury-dept-info-nonce' );
	$result = sudbury_update_settings();

	// TODO: this is a very long running task. Should pass in the board name to just sync this board.
	if ( in_array( 'sudbury_board_membership_key', $result['updated'] ) ) {
		// immediately pull in from filemaker.
		ob_start();
		sudbury_filemaker_cron_job();
		ob_get_clean();
	}

	sudbury_redirect_updated( $result['message'], false, true );

}

add_action( 'admin_post_sudbury_save_dept_info', 'sudbury_update_dept_info_settings' );

/**
 * Handle all the Parent/Child/Counterpart relationships
 *
 * @param $data
 *
 * @return mixed
 */
function sudbury_dept_info_admin_site_structure_settings( $data ) {
	if ( 0 == $data['sudbury_parent'] ) {
		if ( get_option( 'sudbury_parent' ) && false === sudbury_delete_parent() ) {
			sudbury_redirect_error( 'Failed to Delete Parent Relationship' );
		}
	} else {
		if ( false === sudbury_update_parent( $data['sudbury_parent'] ) ) {
			sudbury_redirect_error( 'Failed to Update Parent Relationship' );
		}
	}

	if ( isset( $data['sudbury_counterparts'] ) ) {

		$data['sudbury_counterparts'] = array_filter( $data['sudbury_counterparts'], function ( $counterpart ) {
			return is_numeric( $counterpart ) && $counterpart > 0;
		} );

		if ( false === sudbury_update_counterparts( $data['sudbury_counterparts'] ) ) {
			sudbury_redirect_error( 'Failed to Update Counterparts ' );
			die();
		}
	}

	if ( isset( $_REQUEST['sudbury_proccess_relationship_meta'] ) && ! isset( $_REQUEST['sudbury_relationship_meta'] ) ) {
		delete_option( 'sudbury_relationship_meta' );
	}

	unset( $data['sudbury_parent'] );
	unset( $data['sudbury_counterparts'] );
	if ( ! is_super_admin() ) {
		unset( $data['sudbury_types'] );
	}

	return $data;
}

add_action( 'sudbury_plugin_options_bulk_validate', 'sudbury_dept_info_admin_site_structure_settings' );
