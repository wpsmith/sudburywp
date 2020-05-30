<?php
/**
 * A shortcode to generate a list of all Board Members
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @since      2013-08-14
 * @package    Sudbury
 * @subpackage Shortcodes
 */

class Sudbury_Vacancies_Shortcode {

	/** 
	 * Hook into the init action
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * Register the Vacancies Shortcode
	 */
	function init() {
		add_shortcode( 'vacancies', array( &$this, 'shortcode' ) );
	}


	/**
	 * @param        $atts
	 * @param string $content
	 *
	 * @return string
	 */
	function shortcode( $atts, $content = '' ) {
		ob_start();
		$officials = get_site_option( 'sudbury_all_board_membership' ); 
		foreach ( $officials as $board => $members ) {
			$officials[$board] = array_filter( $members, function ($m) { return strcasecmp( $m['Name Formal'], 'VACANCY') == 0; } );
			if ( empty( $officials[$board] ) ) {
				unset( $officials[$board] );
			}
		} 

		
		?>
		<div class="space"></div>
		<h3>Boards and Committees</h3>
		<hr>
		<div class="board-list">
			<?php
			$board_cols = array_chunk( $board_names = array_keys( $officials ), ceil( count( $officials ) / 2 ) );

			foreach ( $board_cols as $col ) : ?>
				<ul class="categories-list">
					<?php foreach ( $col as $board ) : ?>
						<li><a href="#<?php echo esc_attr( trim( $board ) ); ?>"><?php echo esc_html( $board ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach; ?>
			<div class="clear"></div>
		</div>
		<div class="officials">
			<?php if ( $officials ) :
				reset( $board_names ); ?>
				<?php foreach ( $officials as $board => $members ) : ?>
				<div class="tablecap">
					<h4><?php echo esc_html( $board ); ?>
						<a name="<?php echo esc_attr( trim( next( $board_names ) ) ); ?>"></a>
						<?php if ( ! empty( $members ) && isset( $members[0]['site'] ) ) : ?>
							<?php switch_to_blog( $members[0]['site']->blog_id ); ?>
							<span style="float:right; font-weight:normal; text-align:right;font-size:12px;">
						<?php if ( $email = get_option( 'sudbury_email' ) ) : ?>
							<?php echo sudbury_protect_emails( '<a href="mailto:' . $email . '">' . $email . '</a>' ); ?>
						<?php endif; ?>
						<?php if ( $phone = get_option( 'sudbury_telephone' ) ) : ?>
							<?php if ( $email ) : ?>
								<?php echo '|' ?>
							<?php endif; ?>
							<?php echo $phone; ?>
						<?php endif; ?>
						</span>
							<?php restore_current_blog(); ?>
						<?php endif; ?>

					</h4>
					<table cellspacing="0">
						<tbody>
						<tr>
							<th width="30%">Name</th>
							<th width="30%">Appointed By</th>
							<th width="10%">Started</th>
							<th width="30%">End of Term</th>
						</tr>
						<?php foreach ( $members as $member ) : ?>
							<tr>
								<td><?php echo esc_html( $member['Name Formal'] ); ?></td>
								<td><?php echo esc_html( $member['Appointed by'] ); ?></td>
								<td><?php echo esc_html( $member['First Appointment Year'] ); ?></td>
								<td><?php echo esc_html( $member['Term Expiration'] ? $member['Term Expiration'] : 'Indefinite' ); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<div class="foot"></div>
				</div>
				<?php $prev_board = $board; ?>
			<?php endforeach; ?>
			<?php else : ?>
				<div class="error danger">Vacancies Not loaded from Filemaker</div>
			<?php endif; ?>
		</div>
		<?php

		return ob_get_clean();
	}
}

new Sudbury_Vacancies_Shortcode();
