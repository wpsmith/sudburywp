<?php

namespace WPS\WP\Plugins\CoreClasses;

add_filter( 'body_class', function ( $classes ) {
	$classes[] = 'team';

	return $classes;
} );

genesis();