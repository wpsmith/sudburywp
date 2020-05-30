<?php if (is_category()) { ?>

<h3 class="pagetitle"><?php printf( __( 'You are browsing the archive for %1$s.', TEMPLATE_DOMAIN ), wp_title( false, false ) ); ?></h3>

<?php } else if (is_tag()) { ?>

<h3 class="pagetitle"><?php printf( __( 'You are browsing the archive for %1$s.', TEMPLATE_DOMAIN ), wp_title( false, false ) ); ?></h3>

<?php } else if (is_archive()) { ?>

<h3 class="pagetitle"><?php printf( __( 'You are browsing the archive for %1$s.', TEMPLATE_DOMAIN ), wp_title( false, false ) ); ?></h3>

<?php } else if (is_single()) { ?>

<?php } else if (is_search()) { ?>

<h3 class="pagetitle"><?php _e( 'Search Results', TEMPLATE_DOMAIN ) ?></h3>

<?php } ?>