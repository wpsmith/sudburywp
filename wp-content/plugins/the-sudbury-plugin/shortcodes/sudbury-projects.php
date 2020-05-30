<?php

/**
 * A shortcode to compile a list of Town Meeting Proceedings
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Projects {

	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	function init() {
		add_shortcode( 'projects', array( &$this, 'shortcode' ) );
	}

	function shortcode( $atts = array(), $content = null ) {
		$sites = get_sites( array( 'count' => false, 'number' => 0, 'public' => true ) );
		ob_start();
		?>
		<table class="table table-striped">
		<tbody>
			<tr>
				<th>Project</th>
				<th>Funding</th>
				<th>Last Update</th>
				<th>Notes</th>
				<th>Status</th>
			</tr>
		<?php foreach ( $sites as $site ) : ?>
			<?php if ( ! sudbury_has_type('project', $site->blog_id ) || sudbury_has_type( 'utility', $site->blog_id ) ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<?php switch_to_blog( $site->blog_id ); ?>
			<tr id="blog-<?php get_current_blog_id(); ?>">
				<td><a href="<?php echo get_site_url(); ?>"><?php bloginfo( 'name' ); ?></a></td>
				<td><?php esc_html_e( get_option( 'sudbury_project_funding' ) ); ?></td>
				<td><?php esc_html_e( get_option( 'sudbury_project_updated' ) ); ?></td>
				<td><?php esc_html_e( get_option( 'sudbury_project_notes' ) ); ?></td>
				<td><span class="badge badge-<?php esc_attr_e( get_project_status_color() ); ?>"><?php the_project_status(); ?></span></td>
			</tr>
			<?php restore_current_blog(); ?>
		<?php endforeach; ?>
		</tbody>
		</table>
		<?php
		return ob_get_clean();
	}


}

new Sudbury_Projects();

function get_project_status() {
	return get_option( 'sudbury_project_status', 'Unknown' );
}

function the_project_status() {
	esc_html_e( get_project_status() );
}

function get_project_status_color() {
	$status = get_project_status();
	switch ( strtolower( $status ) ) {
		case 'cancelled':
			return 'danger';
		case 'behind':
			return 'warning';			
		case 'on track':
		case 'planning':
			return 'success';
		default:
			return 'secondary';
	}
}
