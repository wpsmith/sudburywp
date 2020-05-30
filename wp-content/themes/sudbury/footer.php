<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package    WordPress
 * @subpackage Twenty_Eleven
 * @since      Twenty Eleven 1.0
 */
?>

<footer>
	<div class="container">
		<div id="footer" class="row justify-content-between">
			<div class="col-sm-4">
				<div class="line">
					<a href="http://sudbury.ma.us/legal/">Copyright &copy; <?php echo date( 'Y' ); ?>, Town of Sudbury</a>, some rights reserved.
				</div>
				<div class="line">
					Send questions and comments to
					<a href="mailto:webmaster@sudbury.ma.us"><i>webmaster@sudbury.ma.us</i></a>.
				</div>
			</div>
			<div class="hover-reveal col-sm-4">
				<div id="built-with" class="line">
					<i>Built with <a href="http://wordpress.org/">WordPress</a></i>
				</div>
				<div id="powered-by" class="line">
					<i>Powered By PHP7, NGINX, &amp; IIS</i>
				</div>
				<div id="sudbury-version" class="line">
					<i>Website Release v<?php echo SUDBURY_VERSION; ?></i>
				</div>
				<div id="load-time" class="line">
					<i>Load: <?php global $start;
						echo( round( microtime( true ) - $start, 3 ) ); ?> Seconds </i>
				</div>
				<div id="rendered-at" class="line">
					<i>Rendered: <?php echo ( new DateTime( 'now', new DateTimeZone( 'America/New_York' ) ) )->format( 'Y-m-d H:i:s T' ) ?></i>
				</div>
				<div id="system-status" class="line">
					<i> System Status: <a href="#"><?php echo esc_html( sudbury_get_status() ); ?></a></i>
				</div>
			</div>
		</div>
	</div>
</footer>

<!-- Plugins Hook -->

<?php wp_footer(); ?>
<!-- /Plugins Hook -->
</body>
</html>
