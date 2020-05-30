<?php
/**
 * A shortcode to show a list of buildings, their addresses, and contact info
 * 
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_User_Directory {

	function __construct() {

		add_action( 'init', array( &$this, 'init' ) );
	}


	function init() {
		add_shortcode( 'users', array( &$this, 'shortcode' ) );
	}


	function shortcode( $atts = array(), $content = null ) {
		$defaults = array(
		);

		$atts       = array_merge( $defaults, $atts );

		ob_start();
		// End Setup

		if (is_super_admin()) {


		?>


		<div class="tablecap">
			<h4>Sites</h4>
			<table cellspacing="0" style="display: table;" class="filter-table">
				<thead class="headers">

				<th style="width:10%">ID</th>
				<th style="width:50%">Name</th>
				<th style="width:40%">Email</th>

				</thead>
				<tbody>
				<?php
				$users = get_users( array( 'blog_id' => 0 ) );
                   foreach ( $users as $user ) : ?>
					<tr>
						<td><?php echo esc_attr( $user->data->ID ); ?></td>
						<td><?php echo esc_html( $user->data->user_login ); ?> </td>
						<td><a href="mailto:<?php echo esc_attr( $user->data->user_email ); ?>"><?php echo esc_html( $user->data->user_email ); ?></a> </td>
					</tr>
                   <?php endforeach; ?>
				</tbody>
			</table>
			<div class="foot"></div>
		</div>


		<?php } else { ?>
			<p> Please Login as a Super Admin</p>
		<?php }
		$html = ob_get_clean();

		// Cache this puppy
		return $html;
	}
}

new Sudbury_User_Directory();
