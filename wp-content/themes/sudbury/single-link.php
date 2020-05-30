<?php
/**
 * Posts with the post_type of link store their link url in the sudbury_redirect_url post meta (convenient right?!)
 * Look at the sudbury_link_redirect function in ./functions.php to see how redirect urls are handled on the front end
 * (sudbury_link_redirect) fires on the 'wp' hook before any output should be sent so headers should still be available
 */
?>
<!DOCTYPE html>
<html>
<head>
	<title>Link</title>
</head>
<body>
<h1>Redirecting</h1>

<p>Taking you to <?php the_title(); ?> at
	<a href="<?php echo $url = esc_url( get_post_meta( get_the_ID(), 'sudbury_redirect_url', true ) ); ?>"><?php echo $url ?></a>
</p>
</body>
</html>



