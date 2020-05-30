<?php
// If there is going to be a problem loading the template system
if ( ! file_exists( __DIR__ . '/../wp-includes/formatting.php' ) ) {
	die( '<h1>Temporarily offline, We\'ll be back in a second</h1>' );
}
// Pull in the template formatting functions
require_once __DIR__ . '/../wp-includes/formatting.php';
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Sudbury MA | Temporarily Offline</title>
	<meta name="author" content="Eddie Hurtig" />
	<meta name="application-name" content="Sudbury Error Reporting" />

	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<link href="<?php echo esc_url( $base_url ); ?>/wp-content/error-assets/css/bootstrap/bootstrap.css" rel="stylesheet" />
	<link href="<?php echo esc_url( $base_url ); ?>/wp-content/error-assets/css/bootstrap/bootstrap-responsive.css" rel="stylesheet" />
	<link href="<?php echo esc_url( $base_url ); ?>/wp-content/error-assets/css/icons.css" rel="stylesheet" type="text/css" />

	<link href="<?php echo esc_url( $base_url ); ?>/wp-content/error-assets/css/main.css" rel="stylesheet" type="text/css" />

	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body class="errorPage">
<div class="container-fluid">
	<div class="errorContainer offline">
		<div class="page-header">
			<img src="/img/header.jpg" />

			<h1 class="center"><?php if ( isset( $heading ) ) {
					echo $heading;
				} else {
					echo 'Temporarily Offline';
				} ?></h1>
		</div>

		<h2 class="center"><?php if ( isset( $subheading ) ) {
				echo $subheading;
			} else {
				echo 'We will be back up shortly.';
			} ?></h2>


		<div class="social">
			<div class="row-fluid">
				<div class="span12">
					<div class="span6 center">
						<span class="icon16 icomoon-icon-twitter-2 blue"></span><a href="https://twitter.com/Sudbury_Town">Follow our Twitter</a>
					</div>
					<div class="span6 center">
						<span class="icon16 icomoon-icon-facebook-2 blue"></span><a href="http://facebook.com/TownofSudbury">Check our Facebook</a>
					</div>
				</div>
			</div>
			<?php
			if ( isset( $GLOBALS['maintenance_message'] ) ) : ?>
				<hr />
				<p class="center">
					<b>System Message: </b><?php echo $GLOBALS['maintenance_message']; //not escaping because this is allowed to contain html ?>
				</p>
			<?php endif; ?>

		</div>
		<hr />
		<div class="center">
			<a href="javascript: history.go(-1)" class="btn" style="margin-right:10px;"><span class="icon16 icomoon-icon-arrow-left-10"></span>Go back</a>
			<a href="<?php echo esc_url( $base_url ); ?>/wp-admin/" class="btn"><span class="icon16 icomoon-icon-screen"></span>Admin Login</a>
		</div>
	</div>
</div>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo esc_url( $base_url ); ?>/wp-content/error-assets/js/bootstrap/bootstrap.js"></script>
<script type="text/javascript">
	$(document).ready(function () {
		$('.errorContainer').hide();
		$('.errorContainer').fadeIn(1000).animate({
			'top': "50%", 'margin-top': +($('.errorContainer').height() / -2 - 30)
		}, {duration: 750, queue: false}, function () {
		});
	});
</script>
</body>
</html>
