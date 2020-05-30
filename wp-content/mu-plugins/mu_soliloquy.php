<?php
/******** Set default width and height: sidebar width here  ******************/
add_filter( 'soliloquy_defaults', 'sud_soliloquy_default_settings', 20, 2 );
function sud_soliloquy_default_settings( $defaults, $post_id ) {
     
    $defaults['slider_width']  = 980; // Slider width.
    $defaults['slider_height'] = 653; // Slider height.
    $defaults['arrows']  = 0; // don't show arrows.
    $defaults['control'] = 0; // don't show navigation circles.
    $defaults['keyboard'] = 0; // no keyboard navigation.
     
    return $defaults;
     
}
/*********** ADD SIZES TO CHOOSE FROM ************************/
add_filter( 'soliloquy_slider_sizes', 'sud_soliloquy_slider_sizes', 20, 2 );
function sud_soliloquy_slider_sizes( $sizes ) {
     
    $sizes = array(
           /* default is same as site sidebar so we don't need it
		    array(
                'value'  => 'default',
                'name'   => esc_attr__( 'Default', 'soliloquy' ),
                'width'  => 0,
                'height' => 0
            ), */
			array(
            	'value'  => 'full_width',
            	'name'   => esc_attr__( 'Full Width', 'soliloquy' ),
            	'width'  => 980,
            	'height' => 653
        	),
            array(
                'value'  => 'news_article',
                'name'   => esc_attr__( 'News Article', 'soliloquy' ),
                'width'  => 575,
                'height' => 383
            ),
            array(
                'value'  => 'site_sidebar',
                'name'   => esc_attr__( 'Site Sidebar', 'soliloquy' ),
                'width'  => 365,
                'height' => 243
            ),
            array(
                'value'  => 'top_banner',
                'name'   => esc_attr__( 'Top Banner', 'soliloquy' ),
                'width'  => 575,
                'height' => 215
            ),
		);
		return $sizes;
}
	
