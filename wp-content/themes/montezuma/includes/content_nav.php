<?php 

if ( ! function_exists( 'bfa_content_nav' ) ) :

function bfa_content_nav( $args = '' ) {

   global $wp_query;
   if ( $wp_query->max_num_pages < 2 )  // Display only if more than 1 page:
       return;

   $big = 999999999; // need an unlikely integer
   $defaults = array(
       'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
       'format' => '?paged=%#%',
       'current' => max( 1, get_query_var('paged') ),
       'total' => $wp_query->max_num_pages,
       'mid_size' => 5
   );

   $id = '';
   if( strpos( $args, '=' ) !== FALSE ) { // A URL query stlye parameter was used: this=that&this=that
       $params = wp_parse_args( $args, $defaults );
       if( isset( $params['id'] ) ) {
           $id = $params['id'];
           unset( $params['id'] ); // 'id' does not belong to paginate_links parameters
       }
   } else {
       if( ! empty( $args ) ) {
           $id = $args; // old version, only $id could be passed as parameter
       }
       $params = $defaults;
   }

   if( $id != '' )
       $id = ' id="' . $id . '"'; // a CSS ID can be added
   ?>


   <nav class="multinav"<?php echo $id; ?>>
       <?php echo paginate_links( $params ); ?>
   </nav>

   <?php

}
endif;
