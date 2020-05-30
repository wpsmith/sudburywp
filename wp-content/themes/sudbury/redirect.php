<?php
/**
 * The Template for displaying all single posts.
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 * @since      Twenty Eleven 1.0
 */
get_header();
?>

<?php sudbury_department_tabs(); ?>
<div id="main-col" <?php sudbury_main_col_class(); ?>>
	<h1>Leaving Our Website?</h1>

	<div class="space"></div>
	<div class="nav-container">
		<div style="float:right">
			<a href="<?php echo esc_url( sudbury_get_redirect_url() ); ?>" class="btn btn-primary">Continue to
				<?php echo parse_url( sudbury_get_redirect_url() )['host']; ?>&rarr; </a>
		</div>

		<div style="float:left;">
			<a href="javascript:history.go(-1)" class="btn btn-primary">&larr; Go Back</a>


		</div>
		<div class="clear"></div>


	</div>
	<p>&nbsp;</p>

	<p>&nbsp;</p>

	<p>&nbsp;</p>

	<p>
		The Link you clicked on will take you to '<?php echo sudbury_get_redirect_url(); ?>' which is neither endorsed nor controlled by the Town of Sudbury.
	</p>
</div>
<!-- #main-col -->
<?php get_footer(); ?>
