<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 * @since      Twenty Eleven 1.0
 */

get_header();
?>

<div id="cont">
	<div class="container-fluid container-fluid-xl">
		<div class="row">
			<?php sudbury_department_tabs(); ?>
			<main id="main-col" <?php sudbury_main_col_class( '', 'full' ); ?>>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1>
						Page Not Found
					</h1>
					<div class="text-center">
						<p><i class="fa fa-5x fa-exclamation-triangle text-warning"></i></p>
						<p>Looks like you've found a broken link!</p>
						<p>The content you are looking for might be somewhere else on our site.</p>
					</div>
					<h2>Search</h2>
					<p>Use the search form below to search for it.</p>

					<?php get_search_form(); ?>
				</article>
			</main>
		</div>
	</div>
</div>


<?php get_footer(); ?>




