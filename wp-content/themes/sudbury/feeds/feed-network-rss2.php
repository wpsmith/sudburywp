<?php
/**
 * Produces the Network RSS Feed
 */

$posts = network_query_posts( array( 'post_type' => array( 'post', 'meeting' ), 'posts_per_page' => 50 ) );


