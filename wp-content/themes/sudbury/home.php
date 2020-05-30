<?php
// Intentionally Overriding the main WP_Query, use of query_posts is justified
global $wp_query;

$show_expired = ( isset( $_REQUEST['include-archived'] ) || sudbury_is_site_archived() );

// Only pull items that have a run end for the main Loop... wqe will take care of items that don't have an expiration in a secondary loop below

$wp_query->is_category = false;


get_header(); ?>
<?php if ( is_active_sidebar( 'tasks' ) ) : ?>
	<div id="tasks">
		<div class="container-fluid container-fluid-xl">
			<div class="row">
				<?php dynamic_sidebar( 'tasks' ); ?>
			</div>
		</div>
	</div>
<?php endif; ?>


<div id="cont">
	<div class="container-fluid container-fluid-xl">
		<!-- Start The Main Part of the Page -->
		<div id="body" class="department-page row">
			<?php sudbury_department_tabs(); ?>


			<div id="main-col" <?php sudbury_main_col_class(); ?>>
				<div class="articles">
					<?php dynamic_sidebar( 'showcase_widget_area' ); ?>

					<?php if ( $show_expired ) : ?>
						<h1 class=" page-title homepage-title">All News for <?php bloginfo( 'name' ); ?></h1>
					<?php else : ?>
						<h1 class=" page-title homepage-title">Recent News
							<b>
								<span class="section-header-links">
								<?php if ( get_current_blog_id() != 1 ) : ?>
									<a href="?include-archived">Include Archived Items</a>
								<?php else: ?>
									<?php wp_nav_menu( array(
											'theme_location' => 'all-links',
											'container'      => false,
											'menu_id'        => 'all-links',
											'menu_class'     => 'nav'
										)
									); ?>
								<?php endif; ?>
								</span>
							</b>
						</h1>
						<div class="space"></div>

					<?php endif ?>

					<?php if ( have_posts() ) : ?>


						<?php /* Start the Loop */ ?>
						<?php while ( have_posts() ) : the_post(); ?>
							<?php get_template_part( 'content', get_post_type() ); ?>
						<?php endwhile; ?>

						<?php sudbury_content_nav( 'nav-below' ); ?>

					<?php
					else : ?>

						<div id="post-0" class="post no-results not-found">
							<!-- .entry-header -->

							<div class="entry-content">
								<p><?php _e( 'Apologies, but no recent News Articles were found', 'sudbury' ); ?></p>
								<?php get_search_form(); ?>
							</div>
							<!-- .entry-content -->
						</div><!-- #post-0 -->

					<?php endif; ?>

					<?php if ( get_current_blog_id() != 1 )  : // General News ?>


						<?php
						$args = array(
							'meta_key'            => '_post-expiration-enabled',
							'meta_value'          => 0,
							'posts_per_page'      => - 1,
							'ignore_sticky_posts' => 1,
							'category_name'       => 'general'
						);

						if ( ! $show_expired ) {
							$args['post_status'] = 'publish';
						}


						$general_posts = new WP_Query( $args );

						if ( $general_posts->have_posts() ) : ?>

							<div class="space"></div>
							<h1>Information &amp; Services</h1>
							<div class="space"></div>
							<?php /* Start the Loop */ ?>
							<?php while ( $general_posts->have_posts() ) : $general_posts->the_post(); ?>
								<?php get_template_part( 'content', get_post_type() ); ?>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>

						<?php endif; ?>
					<?php else: ?>
					<?php endif; // End General News ?>


				</div>
			</div>
			<div class="break"></div>

			<?php get_sidebar(); ?>
		</div>
	</div>
</div>

<?php get_footer(); ?>
