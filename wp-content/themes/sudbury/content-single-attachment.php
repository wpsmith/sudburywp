<?php
/**
 * The template for displaying content for an attachment
 *
 * @package    Sudbury
 * @subpackage Sudbury Theme
 */
?>
<h2>Attachment</h2>
<?php
if ( strstartswith( 'image', get_post_mime_type() ) ) { ?>
	<img src="<?php echo esc_url( wp_get_attachment_image_src( get_the_ID(), 'full', true )[0] ); ?>" width="100%"
		 alt="<?php echo esc_attr( get_post_meta( get_the_ID(), '_wp_attachment_image_alt', true ) ); ?>" />
	<?php
} elseif ( 'application/pdf' == get_post_mime_type() ) {
	if ( false and filesize( get_attached_file( $post->ID ) ) < 5 * 1024 * 1024 ) {
		?>
		<iframe src="https://docs.google.com/viewer?embedded=true&url=<?php echo urlencode( wp_get_attachment_url() ); ?>" width="100%" height="600" wmode="opaque"></iframe>
		<p style="text-align:center;">
			<i class="text-center">If the preview above fails to load, please click "View in Browser". </i></p>
	<?php } else { ?>
		<div class="text-center" style="padding: 2rem 0;">
			<p><a href="<?php echo wp_get_attachment_url(); ?>" class="btn btn-primary"
				  download="<?php echo basename( parse_url( wp_get_attachment_url(), PHP_URL_PATH ) ); ?>">
					<i class="fa fa-5x fa-download"></i><br><span>Download Now</span>
				</a>
			</p>
			<p>
				<span class="badge badge-secondary"><?php echo size_format( filesize( get_attached_file( $post->ID ) ) ); ?></span>
			</p>
		</div>
	<?php } ?>
<?php } else { ?>
	<p class="error">Sorry, No Preview Available
		<br>Please download click 'Download Now' or 'View in Browser' to get the file</p>
	<p>
		<a href="<?php echo wp_get_attachment_url(); ?>" class="btn btn-primary"
		   download="<?php echo basename( parse_url( wp_get_attachment_url(), PHP_URL_PATH ) ); ?>">Download Now</a>
		<a href="<?php echo wp_get_attachment_url(); ?>" class="btn btn-primary">View In Browser</a>
	</p>
<?php } ?>


<?php if ( $post->post_excerpt ) : ?>
	<h2>Caption</h2>

	<p><?php echo $post->post_excerpt; ?></p>
<?php endif; ?>

<?php if ( $post->post_content ) : ?>
	<h2>Description</h2>

	<p><?php echo $post->post_content; ?></p>
<?php endif; ?>
<table class="table table-striped">
	<thead>
	<tr>
		<th>Filename</th>
		<th>Size</th>
		<th>Updated</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td>
			<a href="<?php echo wp_get_attachment_url(); ?>"><?php echo basename( get_attached_file( get_the_ID() ) ); ?></a>
		</td>
		<td><?php echo size_format( filesize( get_attached_file( $post->ID ) ) ); ?></td>
		<td><?php echo mysql2date( 'l, n/j/Y g:i a', $post->post_modified ); ?></td>
	</tr>
	</tbody>
</table>

