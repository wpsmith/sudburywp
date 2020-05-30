<?php
/**
 * The Paging Application for Sudbury, rewritten for WordPress
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @package    Sudbury
 * @subpackage Paging
 */

require_once '../../../../wp-load.php';

if ( ! is_internal() ) {
	wp_die( 'Sorry, You Must <a href="' . wp_login_url( plugins_url( '', __FILE__ ) ) . '">Login</a> In Order to use this application externally.' );
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Sudbury Paging System</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Le styles -->
	<link href="assets/css/bootstrap.css" rel="stylesheet">
	<link href="assets/css/bootstrap-responsive.css" rel="stylesheet">
	<link href="assets/css/docs.css" rel="stylesheet">
	<link href="assets/js/google-code-prettify/prettify.css" rel="stylesheet">

	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- Le fav and touch icons -->
	<link rel="shortcut icon" href="assets/ico/favicon.ico">
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
	<link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">

	<script src="assets/js/jquery.js"></script>
	<script src="assets/js/google-code-prettify/prettify.js"></script>
	<script src="assets/js/bootstrap-transition.js"></script>
	<script src="assets/js/bootstrap-alert.js"></script>
	<script src="assets/js/bootstrap-modal.js"></script>
	<script src="assets/js/bootstrap-dropdown.js"></script>

	<script src="assets/js/bootstrap-tab.js"></script>
	<script src="assets/js/bootstrap-tooltip.js"></script>
	<script src="assets/js/bootstrap-popover.js"></script>
	<script src="assets/js/bootstrap-button.js"></script>
	<script src="assets/js/bootstrap-collapse.js"></script>
	<script src="assets/js/bootstrap-carousel.js"></script>
	<script src="assets/js/bootstrap-typeahead.js"></script>
	<script src="assets/js/bootstrap-affix.js"></script>
	<script src="assets/js/jquery.cookie.js"></script>
	<script src="assets/js/application.js"></script>

</head>

<body data-spy="scroll" data-target=".bs-docs-sidebar">


<!-- Navbar
================================================== -->
<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="brand" href="http://internal.sudbury.ma.us/webeditor/webedit.asp">Sudbury Internal</a>

			<div class="nav-collapse collapse">
				<ul class="nav">
					<li class="">
						<a href="http://internal.sudbury.ma.us/">&larr; Back to Sudbury Internal</a>
					</li>
					<li class="">
						<a href="http://sudbury.ma.us/">Main Site</a>
					</li>
					<li class="">
						<a href="http://internal.sudbury.ma.us/webeditor/webedit.asp">Webeditor</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner" style="">
		<div class="container">
			<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="brand" style="color:#aaa" href="http://sudbury.ma.us/wp-admin/">
				<?php if ( is_user_logged_in() ) : ?>
					Logged in as <?php echo wp_get_current_user()->user_login; ?>
				<?php else : ?>
					Authorized By IP
				<?php endif; ?>
			</a>

			<div class="nav-collapse collapse">
				<ul class="nav">
					<li class="">
						<a href="http://sudbury.ma.us/internal/">&larr; Back to Sudbury Internal</a>
					</li>
					<li class="">
						<a href="http://sudbury.ma.us/">Main Site</a>
					</li>
					<li class="">
						<a href="https://sudbury.ma.us/wp-admin/">WordPress</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>

<header class="jumbotron subhead" id="overview">
	<div class="container">
		<h1>Sudbury Paging System</h1>
	</div>
</header>

<div class="container">

	<!-- Docs nav
================================================== -->
	<div class="row">
		<div class="span3 bs-docs-sidebar">
			<h4 class="sidebar-header">Favorites</h4>
			<ul id="favorite-list" class="nav nav-list bs-docs-sidenav">
				<li class="favorite-none"><a href="#">No Favorites <br /> type name and click '+Favorite' below</a></li>
			</ul>
			<h4 class="sidebar-header">Employees</h4>

			<form class="form-search">
				<div class="input-append">
					<input type="text" id="filter-employees" placeholder="Filter Employees" class="span2 search-query">
					<button type="button" id="add-favorite" class="btn">+Favorite</button>
				</div>
			</form>
			<ul id="employee-list" class="nav nav-list bs-docs-sidenav">

				<?php

				$phone_posts = network_query_posts( array(
					'post_type'      => 'paging_item',
					'posts_per_page' => - 1,
					'orderby'        => 'post_title',
					'order'          => 'ASC'
				) );

				//$phones = $wpdb->get_results( "SELECT * FROM {$wpdb->base_prefix}network_posts WHERE post_type = 'paging_item' ORDER BY blog_id, post_id", ARRAY_A );
				$phones = array();
				foreach ( $phone_posts as $phone ) {
					$first_name = network_get_post_meta( $phone, '_phone_assigned_to_first_name', true );
					$last_name  = network_get_post_meta( $phone, '_phone_assigned_to_last_name', true );
					$_blog_id   = $phone->BLOG_ID;
					$info       = get_blog_details( $_blog_id, true );
					$blog_name  = $info->blogname;
					if ( ! $blog_name ) {
						$blog_name = 'No Dept Name';
					}

					if ( ! $first_name && ! $last_name ) {
						$first_name = 'No Name (' . $blog_name . ')';
					}

					$phones[] = array(
						'meta'             => network_get_post_meta( $phone ),
						'id'               => $phone->ID,
						'_blog_id'         => $phone->BLOG_ID,
						'type'             => network_get_post_meta( $phone, '_phone_type', true ),
						'number'           => network_get_post_meta( $phone, '_phone_number', true ),
						'service_provider' => network_get_post_meta( $phone, '_phone_service_provider', true ),
						'info'             => get_blog_details( $_blog_id, true ),
						'last_name'        => $last_name,
						'blog_name'        => $blog_name,
						'first_name'       => $first_name,
					);
				}
				usort( $phones, function ( $phone1, $phone2 ) {
					return strcasecmp( $phone1['last_name'] . $phone1['type'], $phone2['last_name'] . $phone2['type'] );
				} );
				foreach ( $phones as $phone ) :
					?>
					<li style="display: block;"><a href="#about"
					                               class="scrollto employee"
					                               data-id="<?php echo esc_attr( $phone['id'] ); ?>"
					                               data-blog="<?php echo esc_attr( $phone['_blog_id'] ); ?>"
					                               data-blog-name="<?php echo esc_attr( $phone['blog_name'] ); ?>"
					                               data-name="<?php echo esc_attr( "{$phone['first_name']} {$phone['last_name']}" ); ?>"
					                               data-number="<?php echo esc_attr( $phone['number'] ); ?>"
					                               data-service="<?php echo esc_attr( $phone['service_provider'] ); ?>"
					                               data-type="<?php echo esc_attr( $phone['type'] ); ?>">
							<b class="icon-remove remove-favorite"></b><i class="icon-chevron-right"></i><?php echo esc_html( "{$phone['first_name']} {$phone['last_name']}" . ( '' != $phone['type'] ? " ({$phone['type']})" : '' ) ); ?>
						</a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="span9">
			<section id="paging">
				<h3>Paging System</h3>

				<p>For Technical Support Email: <a href="mailto:webmaster@sudbury.ma.us">webmaster@sudbury.ma.us</a></p>


				<p>You are limited to <code>140 Characters</code> in your text message</p>

				<form data-id="-1" class="bs-docs-example span8" id="paging-form" style="padding-bottom: 15px;">
					<div class="row">
						<div class="input-prepend span4">
							<span class="add-on">Number</span>
							<input class="span3" id="message-to" name="number" type="text" placeholder="Select an employee on the left" disabled>
						</div>


						<div class="input-prepend span4">
							<span class="add-on">Recipient</span>
							<input id="message-name" class="span3" type="text" placeholder="Select an employee on the left" disabled>
						</div>

					</div>
					<div class="control-group message-text-group">
						<div class="controls">
							<h4>Message</h4>
							<textarea rows="3" class="span8" id="message-text" name="message"><?php if ( is_user_logged_in() ) : ?> -<?php echo wp_get_current_user()->display_name; ?><?php endif; ?></textarea>
							<span class="hide help-inline message-text-help-inline">Too Many Characters!</span>
						</div>
					</div>
					<p class=""><code class="message-counter">140</code> Characters Remaining</p>

					<p>
						<input type="hidden" id="post-id" name="post_id" value="" />
						<input type="hidden" id="blog-id" name="blog_id" value="" />
						<input type="hidden" id="service-provider" name="service_provider" value="">
						<input type="submit" class="pull-right btn btn-primary" value="Send" />
					</p>

					<div class="clearfix"></div>

					<div class="row" style="display: none;" id="loading-bar">
						<p>&nbsp;</p>

						<div class="clearfix"></div>
						<div id="progress-bar" class="progress progress-striped active span3 pull-right">
							<div class="bar" style="width: 100%;"></div>
						</div>
						<div id="progress-message" class="span5 pull-right" style="text-align: right;">Sending...</div>
					</div>
				</form>
			</section>
			<section id="about">
				<h3>About</h3>

				<p>This paging and texting system is designed to work with current and future Sudbury mobile devices. This system has replaced to old system due to outdated coding and changes on the part of our service providers which rendered the old paging system inadequate.</p>

				<p>To send a text message or page an employee simply click their name in the left hand column, type your message in the message field, and click send.</p>

				<h3>Recommendations</h3>

				<p>When paging an employee please remember to include your <code>Name</code> and
					<code>Extension</code> so that the recipient knows who you are</p>
			</section>

		</div>
	</div>
</div>


<!-- Footer
================================================== -->
<footer class="footer">
	<div class="container">

		<p>Paging.NET v3.0 | Developed By <a href="http://www.hurtigtechnologies.com">Eddie Hurtig</a></p>

		<p>Copyright <?php echo date( 'Y' ); ?></p>

	</div>
</footer>


</body>
</html>
