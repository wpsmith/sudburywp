<?php

/**
 * Class Related_Departments_Widget
 *
 * This widget displays the Department Related Departments and Committees wherever it is assigned.
 *
 * To assign this widget to a particular
 * area go to http://.../wp-admin/widgets.php in your browser and drag the 'Sudbury - Banner Paragraph' box on the left to any
 * of the areas on the right
 *
 * @author     Edddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Widgets
 */
class Sudbury_Internal_Menu extends WP_Nav_Menu_Widget {

	/**
	 * Constructor
	 */
	function __construct() {
		$widget_ops = array(
			'description'                 => __( 'Add a navigation menu to your sidebar.' ),
			'customize_selective_refresh' => true,
		);
		WP_Widget::__construct( 'internal_menu', __( 'Internal Menu' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current Navigation Menu widget instance.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Navigation Menu widget instance.
	 */
	public function widget( $args, $instance ) {
		if ( is_internal() ) {
			parent::widget( $args, $instance );
		}
	}
}
