<?php
/**
 * Handles the Department Head Message Metabox... can be placed on either the department info page or on its own
 *
 * @deprecated 2014-07-21 No Longer Used
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Admin
 */

/**
 * @param $data
 */
function sudbury_from_dept_head_metabox( $data ) {
	?>
	<div class="editor-container" id="sudbury_dept_head_message_container">
		<?php
		// The Editor Box to edit the Department Head Content

		wp_editor( stripslashes( $data['sudbury_dept_head_message'] ), 'sudbury_dept_head_message' );

		// The Run Start Time Label
		if ( false === $data['sudbury_dept_head_message_start'] ) {
			sudbury_show_error( 'This department has not been created properly: It is missing the `dept_head_message_start` option' );
			echo '</div>';

			return;
		} elseif ( '' === $data['sudbury_dept_head_message_start'] ) {
			$label                                   = __( 'Publish on: %s' );
			$data['sudbury_dept_head_message_start'] = current_time( 'timestamp' );
		} elseif ( time() < $data['sudbury_dept_head_message_start'] ) {
			$label = __( 'Publish on: %s' );
		} elseif ( time() > $data['sudbury_dept_head_message_start'] ) {
			$label = __( 'Published on: %s' );
		}

		?>

		<div class="misc-pub-section curtime sudbury_run_start_fields">
			<?php sudbury_datetime_editor( $label, $data['sudbury_dept_head_message_start'], 'sudbury_dept_head_start' ); ?>
		</div>


		<?php
		// Moving on to the end date
		unset( $label );
		?>

		<hr>
		<div class="sudbury_run_end_container">
			<label for="sudbury_run_indefinitely" style="font-size:16px;">
				<input type="checkbox" id="sudbury_run_indefinitely" class="check-toggle toggle-inverted toggle-disabled" data-toggle=".sudbury_run_end_fields" <?php checked( ! $data['sudbury_dept_head_message_end'] ); ?> />
				Run Indefinitely</label>
			<?php
			$disabled = false;
			// The Run End Time
			if ( ! $data['sudbury_dept_head_message_end'] ) {
				$label    = __( 'End on: %s' );
				$disabled = true;
				// 1 year from now
				$data['sudbury_dept_head_message_end'] = current_time( 'timestamp' ) + 365 * 24 * 60 * 60;
			} elseif ( is_object( $data['sudbury_dept_head_message_end'] ) && time() < $data['sudbury_dept_head_message_end'] ) {
				$label = __( 'End on: %s' );
			} elseif ( is_object( $data['sudbury_dept_head_message_end'] ) && time() > $data['sudbury_dept_head_message_end'] ) {
				$label = __( 'End on: %s' );
			} else {
				sudbury_log( 'Something Is Wrong with the Department End Time' );
				sudbury_log( $data['sudbury_dept_head_message_end'] );
			}

			?>
			<div class="misc-pub-section curtime sudbury_run_end_fields" <?php if ( $disabled ) : ?> style="display:none;" <?php endif; ?>>
				<?php sudbury_datetime_editor( $label, $data['sudbury_dept_head_message_end'], 'sudbury_dept_head_end', 'm/d/Y H:i', array( 'disabled' => $disabled ) ); ?>
			</div>

		</div>


	</div>
<?php
}