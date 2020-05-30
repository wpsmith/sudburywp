<?php
/**
 * Created by PhpStorm.
 * User: hurtige
 * Date: 8/10/2018
 * Time: 8:21 PM
 */

class Sudbury_Broadcast_Info {
	function __construct() {
		add_action( 'sudbury_after_linked_event_meta_box', array( $this, 'form' ), 10, 1 );
		add_action( 'sudbury_network_admin_settings_begin', array( $this, 'network_admin_form' ), 10, 0 );
//		add_action( 'sudbury_network_admin_settings_save', array( $this, 'network_admin_save' ), 10, 1 );
		add_action( 'sudbury_save_plugin_option_sudbury_broadcast_message_template', array(
			$this,
			'setting_validate'
		), 10, 1 );

		add_action( 'save_post', array( $this, 'save' ), 10, 2 );

	}

	function form( $post_id ) {
		$is_broadcast = $this->is_broadcast( $post_id );
		$notes        = $this->get_broadcast_notes( $post_id );
		?>

		<tr>
			<th scope="row"><label for="default_post_format">Broadcast Information</label></th>
			<td>
				<label for="sudbury_is_broadcast">
					<input id="sudbury_is_broadcast" type="checkbox" name="sudbury_is_broadcast" value="yes" <?php checked( $is_broadcast, true ); ?>/>
					This event will be broadcast on Sudbury TV
				</label>
				<br>
				<label for="sudbury_broadcast_notes">
					Broadcast Notes (optional)
					<textarea name="sudbury_broadcast_notes" class="large-text"><?php echo $notes ?></textarea>
					<i>Use this field to provide information about the broadcast.</i>
				</label>
			</td>
		</tr>

		<?php
	}


	function save( $post_id, $post ) {
		if ( sudbury_is_guest_post( $post->ID ) ) {
			return false;
		}

		_sudbury_log( "[broadcast_info] save for post " . $post_id );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! in_array( $post->post_type, get_site_option( 'sudbury_linked_events_post_types' ) ) ) {
			return;
		}

		if ( ! isset( $_REQUEST['post_type'] ) ) {
			_sudbury_log( "[broadcast_info] Not coming from the admin Edit Page... quit" );

			return;
		}

		if ( ! sudbury_events_process_post_type( $post ) ) {
			return;
		}

		if ( ! isset( $_REQUEST['sudbury_broadcast_notes'] ) ) {
			return;
		}

		$is_broadcast = ( isset( $_REQUEST['sudbury_is_broadcast'] ) && $_REQUEST['sudbury_is_broadcast'] == 'yes' );

		update_post_meta( $post_id, 'sudbury_is_broadcast', $is_broadcast );

		$notes = $_REQUEST['sudbury_broadcast_notes'];

		update_post_meta( $post_id, 'sudbury_broadcast_notes', $notes );
	}

	function network_admin_form() {
		?>
		<div class="sudbury-setting-section">
			<label for="sudbury_broadcast_message_template"><b>Broadcast Message Template</b>:
				<br><i>use {type} as a placeholder for the type of event.</i><br>
				<textarea id="sudbury_broadcast_message_template" name="sudbury_broadcast_message_template" class="large-text"><?php echo $this->get_broadcast_message_template(); ?></textarea>
			</label>
		</div>
		<?php
	}

	function network_admin_save( $vars ) {
		//This is not required because the prefix 'sudbury_' will automatically save to site options
//		if ( isset( $vars['sudbury_broadcast_message_template'] ) ) {
//			$new_template = $vars['sudbury_broadcast_message_template'];
//			update_site_option( 'sudbury_broadcast_message_template', $new_template );
//		}
	}

	function setting_validate( $new_value ) {
		return stripslashes_deep( $new_value );
	}

	function get_broadcast_notes( $post ) {
		return apply_filters_ref_array( 'get_broadcast_notes', array(
			get_post_meta( $post, 'sudbury_broadcast_notes', true ),
			$post
		) );
	}

	function get_broadcast_message_template() {
		return get_site_option( 'sudbury_broadcast_message_template', '' );;
	}

	function is_broadcast( $post ) {
		return (bool) get_post_meta( $post, 'sudbury_is_broadcast', true );
	}
}

$GLOBALS['Sudbury_Broadcast_Info'] = new Sudbury_Broadcast_Info();


function is_broadcast( $post = 0 ) {
	return $GLOBALS['Sudbury_Broadcast_Info']->is_broadcast( $post );
}

function get_broadcast_notes( $post = 0 ) {
	return $GLOBALS['Sudbury_Broadcast_Info']->get_broadcast_notes( $post );
}

function the_broadcast_notes( $post = 0 ) {
	echo esc_html( get_broadcast_notes( $post ) );
}

function get_broadcast_message( $post = 0 ) {
	$type    = esc_html( get_post_type( $post ) == 'meeting' ? 'meeting' : 'event' );
	$message = $GLOBALS['Sudbury_Broadcast_Info']->get_broadcast_message_template();
	$message = str_ireplace( '{type}', $type, $message );

	return $message;
}

function the_broadcast_message( $post = 0 ) {
	echo get_broadcast_message( $post );
}
