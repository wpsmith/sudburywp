<?php get_header(); ?>

		<?php $postcount = 0;
			$feature_page = get_option('ne_buddybusiness_feature_id');
			$news_category = get_option('ne_buddybusiness_news_cat');
			$image_display = get_option('ne_buddybusiness_feature_image_size');
			$latest_image_display = get_option('ne_buddybusiness_news_image_size');
			$feature_link = get_option('ne_buddybusiness_feature_link');
			$feature_linktitle = get_option('ne_buddybusiness_feature_link_title');
			$homesidebar = get_option('ne_buddybusiness_homesidebar');
		?>
		<?php
			$featured_pageID = wt_get_ID_by_page_name($feature_page);
			if ($featured_pageID != ""):
				$post_query = new WP_Query('page_id='. $featured_pageID . '');
				if ( $post_query->have_posts() ):
					$post_query->the_post();
		?>
		<div id="feature-wrapper">
			<div id="feature">
				<div id="feature-image">
					<img class="attach-post-image" src="<?php the_post_image_url($image_display); ?>" />
				</div>
				<div id="feature-content">
					<h3><?php the_title(); ?></h3>
					<?php the_excerpt(); ?>
					<a href="<?php echo $feature_link; ?>" class="button"><?php echo $feature_linktitle; ?></a>
				</div>
			</div>
		</div>
		<?php
				endif;
			endif;
		?>
		<?php
			if ( $news_category == 'Select a category:' )
				$news_category = '';
		?>
		<div id="latest-wrapper">
			<div id="latest">
				<div id="latest-block-wrapper">
					<h3><?php _e('Latest News', TEMPLATE_DOMAIN);?></h3>
					<?php if ($homesidebar == "yes"): ?>
						<?php $post_query = new WP_Query('category_name='. $news_category . '&showposts=2'); ?>
						<?php while ($post_query->have_posts()): $post_query->the_post(); ?>
						<div class="latest-block">
							<div class="image">
									<span class="attach-post-image" style="height:80px;display:block;background:url('<?php the_post_image_url($latest_image_display); ?>') center center repeat">&nbsp;</span>
							</div>
							<h4><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('permalink to', TEMPLATE_DOMAIN);?><?php the_title_attribute(); ?>"><?php the_title(); ?></a></h4>
								<?php the_excerpt(); ?>
							<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('permalink to', TEMPLATE_DOMAIN);?><?php the_title_attribute(); ?>"><?php _e( 'Read more', TEMPLATE_DOMAIN ) ?></a>
						</div>
						<?php $postcount++ ?>
						<?php endwhile; ?>
						<div class="latest-block-end">
							<?php get_sidebar('home'); ?>
						</div>
					<?php else: ?>
						<?php $postcount = 1; ?>
						<?php $post_query = new WP_Query('category_name='. $news_category . '&showposts=3'); ?>
						<?php while ($post_query->have_posts()): $post_query->the_post(); ?>
						<?php

						if ($postcount != 3){
						?>
						<div class="latest-block">
							<?php }
						else{
	?>
							<div class="latest-block-end">
	<?php
							}
						?>
							<div class="image">
									<span class="attach-post-image" style="height:80px;display:block;background:url('<?php the_post_image_url($latest_image_display); ?>') center center repeat">&nbsp;</span>
							</div>
							<h4><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('permalink to', TEMPLATE_DOMAIN);?><?php the_title_attribute(); ?>"><?php the_title(); ?></a></h4>
								<?php the_excerpt(); ?>
							<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('permalink to', TEMPLATE_DOMAIN);?><?php the_title_attribute(); ?>"><?php _e( 'Read more', TEMPLATE_DOMAIN ) ?></a>
						</div>
						<?php $postcount++ ?>
						<?php endwhile; ?>
					<?php endif; ?>
					<div class="clear"></div>
				</div>
				<div class="spacer"></div>	<div class="clear"></div>
			</div>
<?php get_footer(); ?>