<?php
/**
 * A shortcode to show a list of buildings, their addresses, and contact info
 * 
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Site_Directory {

	function __construct() {

		add_action( 'init', array( &$this, 'init' ) );
	}


	function init() {
		add_shortcode( 'sites', array( &$this, 'shortcode' ) );
	}


	function shortcode( $atts = array(), $content = null ) {
		$defaults = array(
		);

		$atts       = array_merge( $defaults, $atts );

		ob_start();
		// End Setup


		?>


		<div class="tablecap">
			<h4>Sites</h4>
			<table cellspacing="0" style="display: table;" class="filter-table">
				<thead class="headers">

				<th style="width:10%">ID</th>
				<th style="width:60%">Name</th>
				<th style="width:30%">Path</th>

				</thead>
				<tbody>
				<?php
				foreach_blog (function ($b) { ?>
					
<tr>
    <td><?php echo $b['blog_id'];?></td>
    <td><?php echo get_blog_option($b['blog_id'], 'blogname'); ?></td>
    <td><a href="<?php echo $b['path']; ?>"><?php echo $b['path']; ?></a></td>
</tr>
				<?php }); ?>
				</tbody>
			</table>
			<div class="foot"></div>
		</div>


		<?php
		$html = ob_get_clean();

		// Cache this puppy
		return $html;
	}
}

new Sudbury_Site_Directory();
