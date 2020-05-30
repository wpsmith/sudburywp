<?php

/**
 * Button Shortcode, wraps some text in a nice looking button
 * @author  Eddie Hurtig hurtige@ccs.neu.edu
 *
 * @example [button]Some Text[/button]  [button color="#FF0000"]Some Text[/button]
 *
 * @param array       $atts    The shortcode Attributes.  Accepts color="<insert any valud CSS Cclor>" bnackground="<insert any valid CSS Background>"
 * @param null|string $content The text to display in the button
 *
 * @return string The HTML for the button
 */
function sudbury_button_shortcode( $atts, $content = null ) {
	$atts = shortcode_atts( array( 'color' => '#FFF', 'background' => '#0B5394', 'href' => '#' ), $atts );

	return "<a class=\"btn btn-primary\" href=\"{$atts['href']}\" style=\"color:{$atts['color']};background: {$atts['background']};\">$content</a>";
}

add_shortcode( 'button', 'sudbury_button_shortcode' );
