<?php
/*
Template name: Wiki template
*/ 
get_header(); ?>
<div id="content" class="clearfix">
  <div id="main" class="col-sm-8 clearfix nopadding-left" role="main">
    <div id="home-main" class="home-main home">
      <header>
        <div class="page-catheader">
          <h2 class="page-title"><?php _e('Article Categories','mywiki'); ?></h2>          
        </div>
      </header>
        <?php
	$mywiki_cat = array(
			'child_of'                 => 0,
			'parent'                   => '',
			'orderby'                  => 'name',
			'order'                    => 'ASC',
			'hide_empty'               => 0,
			'hierarchical'             => 1,
			'include'                  => '',
			'exclude'                  => '',
			'number'                   => '',
			'taxonomy'                 => 'category',
			'pad_counts'               => false
			 );
	 $mywiki_cat = get_categories( $mywiki_cat ); 
		$mywiki_i=0;
		foreach ($mywiki_cat as $mywiki_categories) {
			$mywiki_i++;
			if($mywiki_i<5)$mywiki_cat_id="cat-id"; else $mywiki_cat_id='';
			if($mywiki_i % 2 == 1)
			{
				echo"<div class='border-bottom' style='float:left;'>";
			} ?>
        
        <div class="cat-main-section <?php echo $mywiki_cat_id ?>">
          <header>
           <a href="<?php echo get_category_link( $mywiki_categories->term_id ); ?>"> <h4> <?php echo $mywiki_categories->name ;?> <span>(<?php echo $mywiki_categories->count?>)</span></h4> </a>
          </header>
          <?php $mywiki_args = array(
'posts_per_page' => 5,
	'tax_query' => array(
		'relation' => 'AND',
		array(
		'taxonomy' => 'category',
			'field' => 'id',
			'terms' => array($mywiki_categories->term_id),
		),
	)
); $mywiki_cat_post = new WP_Query( $mywiki_args );
if ( $mywiki_cat_post->have_posts() ) :?>
          <div class="content-according">
          	<ul>
            <?php while ( $mywiki_cat_post->have_posts() ):$mywiki_cat_post->the_post(); ?>
            <li><a href="<?php echo get_permalink($post->ID)?>">
              <?php the_title();?>
            </a></li>
            <?php endwhile;?>
            </ul>
          </div>
          <?php endif;?>
        </div>
        <?php 	
		if($mywiki_i % 2 ==0)
			{
				echo"</div>";
			}
		}
		if($mywiki_i % 2 ==1)
			{
				echo"</div>";
			} ?>
    </div>    
    <!-- end #main --> 
  </div>  
  <?php get_sidebar(); // sidebar 1 ?>
</div></div>
<!-- end #content -->
<?php get_footer(); ?>