<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up through the opening <div id="body">
 *
 * @package    Sudbury
 * @subpackage Theme
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 */
global $wp_query;
$wp_query->is_search = false;

?><!DOCTYPE HTML>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php wp_title( '&raquo;', true, 'right' ); ?></title>
	<!-- Pingbacks and other meta info -->
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<!-- Icons and Logos -->
	<link rel="shortcut icon"
		  href="<?php echo sudbury_framework_url( get_stylesheet_directory_uri() . '/img/favicon.png' ); ?>"
		  type="image/x-icon" />
	<link rel="apple-touch-icon"
		  href="<?php echo sudbury_framework_url( get_stylesheet_directory_uri() . '/img/apple-touch-icon.png' ); ?> " />
	<link rel="apple-touch-icon" sizes="57x57"
		  href="<?php echo sudbury_framework_url( get_stylesheet_directory_uri() . '/img/apple-touch-icon-57x57.png' ); ?> " />
	<link rel="apple-touch-icon" sizes="72x72"
		  href="<?php echo sudbury_framework_url( get_stylesheet_directory_uri() . '/img/apple-touch-icon-72x72.png' ); ?> " />
	<link rel="apple-touch-icon" sizes="76x76"
		  href="<?php echo sudbury_framework_url( get_stylesheet_directory_uri() . '/img/apple-touch-icon-76x76.png' ); ?> " />
	<link rel="apple-touch-icon" sizes="114x114"
		  href="<?php echo sudbury_framework_url( get_stylesheet_directory_uri() . '/img/apple-touch-icon-114x114.png' ); ?> " />
	<link rel="apple-touch-icon" sizes="120x120"
		  href="<?php echo sudbury_framework_url( get_stylesheet_directory_uri() . '/img/apple-touch-icon-120x120.png' ); ?> " />
	<link rel="apple-touch-icon" sizes="144x144"
		  href="<?php echo sudbury_framework_url( get_stylesheet_directory_uri() . '/img/apple-touch-icon-144x144.png' ); ?> " />
	<link rel="apple-touch-icon" sizes="152x152"
		  href="<?php echo sudbury_framework_url( get_stylesheet_directory_uri() . '/img/apple-touch-icon-152x152.png' ); ?> " />

	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">

	<!-- Google Analytics Code -->
	<script type="text/javascript">

		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-29857741-1']);
		_gaq.push(['_setDomainName', 'sudbury.ma.us']);
		_gaq.push(['_setAllowLinker', true]);
		_gaq.push(['_trackPageview']);

		(function () {
			var ga = document.createElement('script');
			ga.type = 'text/javascript';
			ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(ga, s);
		})();

	</script>
	<!-- /Google Analytics Code -->

	<!-- SEO Meta -->
	<?php if ( is_singular() ) : ?>
		<?php $keywords = array(); ?>
		<?php if ( $tags = get_the_tags() ) : ?>
			<?php foreach ( $tags as $tag ) : ?>
				<?php $keywords[] .= $tag->name; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if ( $keywords = apply_filters( 'meta_keywords', $keywords ) ) : ?>
			<meta name="keywords" content="<?php echo esc_attr( implode( ',', $keywords ) ); ?>" />
		<?php endif; ?>

		<meta name="description"
			  content="<?php echo esc_attr( apply_filters( 'meta_description', strip_tags( get_the_excerpt() ) ) ); ?>" />
	<?php else: ?>
	<?php endif; ?>

	<?php
	wp_head();
	?>
</head>


<body <?php body_class(); ?>>

<header id="header">
	<!-- Start First Header Section -->

	<div id="header-top" class="navbar navbar-expand-lg navbar-light">
		<div class="container-fluid container-fluid-xl">
			<?php if ( get_site_icon_url( 90 ) ) : ?>
				<a class="navbar-brand"
				   href="/" id="logo"
				   title="Back to <?php sudbury_the_site_type(); ?> Hompage"><img
						src="<?php sudbury_the_site_url( 1 ); ?>/img/header.php?dept=<?php echo urlencode( get_bloginfo( 'name' ) ); ?>&note=<?php echo urlencode( get_bloginfo( 'description' ) ); ?>&color=000&img=<?php echo urlencode( get_site_icon_url( 90 ) ); ?>&v=<?php echo SUDBURY_VERSION; ?>"
						alt="Back to the Town of Sudbury Homepage" /></a>
			<?php else: ?>
				<a class="navbar-brand"
				   href="/" id="logo"
				   title="Back to <?php sudbury_the_site_type(); ?> Hompage"><img
						src="<?php echo get_template_directory_uri() . '/images/header.jpg' ?>"
						alt="Back to the Town of Sudbury Homepage" /></a>
			<?php endif; ?>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#primary-nav" aria-controls="primary-nav" aria-expanded="false" aria-label="Toggle navigation">
				Main Menu <span class="navbar-toggler-icon"></span>
			</button>
			<?php
			switch_to_blog( 1 );
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(

					'theme_location'  => 'primary',
					'container'       => 'div',
					'container_id'    => 'primary-nav',
					'container_class' => 'collapse navbar-collapse justify-content-end',
					'menu_id'         => false,
					'menu_class'      => 'primary-menu nav navbar-nav',
					'depth'           => 3,
					'fallback_cb'     => 'wp_bootstrap_navwalker::fallback',
//					'walker'          => new wp_bootstrap_navwalker()
//					'fallback_cb'     => 'Sudbury_Nav_Walker::fallback',
					'walker'          => new Sudbury_Nav_Walker()
				) );
			}
			restore_current_blog();
			?>
		</div>
	</div>

	<!-- Primary Blue Bar Navigation -->
	<?php if ( false and has_nav_menu( 'secondary' ) ) : ?>
		<div class="header-bottom bar navbar navbar-expand-lg navbar-dark bg-dark">
			<div class="container">
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#secondary-nav" aria-controls="secondary-nav" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<?php

				wp_nav_menu( array(
						'theme_location'  => 'secondary',
						'menu_class'      => 'secondary-menu nav navbar-nav',
						'container_id'    => 'secondary-nav',
						'container_class' => 'collapse navbar-collapse',
						'echo'            => true,
						'before'          => '',
						'after'           => '',
						'link_before'     => '',
						'link_after'      => '',
						'depth'           => 0,
						'walker'          => new Sudbury_Nav_Walker()
					)
				);
				// Done with menus, Going back

				?>
			</div>
		</div>
	<?php else: ?>
		<div class="bg-dark divider"></div>
	<?php endif; ?>

	<!-- Drop Container Placeholder -->
	<div class="scroll-wrap">
		<div class="container-fluid container-fluid-xl">
			<div class="scroll"></div>
		</div>
	</div>

	<!-- Start Alerts System (If Alerts Active) -->
	<?php
	$alerts          = get_alerts();
	if ( $alerts['all'] ) :
		foreach ( $alerts['all'] as $alert ) :
			$switched = false;
			if ( isset( $alert->BLOG_ID ) && $alert->BLOG_ID != get_current_blog_id() ) {
				$switched = true;
				switch_to_blog( $alert->BLOG_ID );
			} ?>

			<?php if ( is_a( $alert, 'WP_Post' ) || isset( $alert->BLOG_ID ) ) :
			$cats = wp_get_object_terms( $alert->ID, 'category' );
			$type    = sudbury_parse_alert_type( $cats );
			$details = get_blog_details( $alert->BLOG_ID, true ); ?>
			<div class="bar alert <?php echo $type['type']; ?> <?php echo $type['alert-class']; ?>">
				<div class="alert-content container">

					<a href="<?php echo esc_attr( get_permalink( $alert->ID ) ); ?> "><?php echo esc_html( $alert->post_title ); ?></a>
					<?php sudbury_edit_post_link( 'Edit Alert', $alert->ID ); ?>

				</div>
				<div class="break"></div>
			</div>
		<?php else : ?>
			<?php // The alert is in the format of an Array ('title' => '<text>', 'url' => '<redirect>', 'readmore-text', 'alert-class') and was inserted by a filter ?>
			<div class="bar alert-container alert-network-wide <?php echo $alert['alert-class']; ?>">
				<div class="alert-content container">
					<a href="<?php echo esc_url( $alert['url'] ); ?>"><?php echo esc_html( $alert['title'] ); ?></a>
				</div>
				<div class="break"></div>
			</div>
		<?php endif; ?><?php
			if ( $switched ) {
				restore_current_blog();
			}
		endforeach;
	endif;
	?>
	<!-- Archived Site Message -->
	<?php if ( sudbury_is_site_archived() ) : ?>
		<div class="bar archived-message-container">
			<div class="archived-message-content container">
				<i class="fa fa-archive"></i>
				<?php if ( $archived_message = get_option( 'sudbury_archived_message', false ) ) {
					// do not escape for HTML, might justly contain HTML
					_e( $archived_message );
				} else {
					?>
					Archived: This <?php sudbury_the_site_type(); ?>
					<?php if ( $archived_date = sudbury_get_the_archived_date() ) : ?> was archived on <?php echo esc_html( $archived_date );
					else : ?> has been archived. <?php endif; ?>
					<a href="<?php sudbury_the_help_url( 'archived-sites' ); ?>" class="archived-read-more">What
						Does this Mean?</a>
				<?php } ?>

			</div>
			<div class="break"></div>
		</div>
	<?php endif; ?>

	<!-- Individual Site Navigation Bar -->
	<?php if ( has_nav_menu( 'links' ) ) : ?>
		<div class="header-ext bar navbar navbar-expand-lg navbar-light">
			<div class="container-fluid container-fluid-xl">
				<a class="navbar-brand"></a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#links-nav" aria-controls="links-nav" aria-expanded="false" aria-label="Toggle navigation">
					Quick Links <span class="navbar-toggler-icon"></span>
				</button>
				<?php

				wp_nav_menu( array(
					'theme_location'  => 'links',
					'container_id'    => 'links-nav',
					'container_class' => 'collapse navbar-collapse',
					'menu_class'      => 'nav navbar-nav',
					'fallback_cb'     => '__return_false',
					'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'depth'           => 2,
					'walker'          => new BS_4_Nav_Walker()
				) ); ?>

			</div>
		</div>


	<?php endif; ?>

</header>
<!-- #branding -->

<?php if ( has_header_image() ) : ?>
	<div id="hero">
		<?php if ( display_header_text() ) : ?>
			<div class="container site-title">
				<?php if ( get_bloginfo( 'name' ) ) : ?>
					<h1><?php bloginfo( 'name' ); ?></h1><br>
				<?php endif; ?>

				<?php if ( get_bloginfo( 'description' ) ) : ?>
					<h2><?php bloginfo( 'description' ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<style>
			#hero {
				background: url(<?php echo get_header_image(); ?>) no-repeat #0b5394;
				background-size: cover;
				background-position: center;
			}

			.site-title h1, .site-title h2 {
				/*color: #*/
			<?php //header_textcolor(); ?> /*;*/
				/*background: #*/
			<?php //background_color(); ?> /*;*/
				background: rgba(170, 170, 170, .75);
				padding: .2rem 1rem;
				margin: 1rem 0;
				display: inline;
				font-size: 4rem;
			}

			.site-title h2 {
				font-size: 3rem;
			}
		</style>
	</div>
<?php endif; ?>


<?php if ( has_nav_menu( 'tabs' ) ) : ?>
	<div class="container">
		<div class="row">
			<div class="col">
				<?php

				$args = array(
					'theme_location' => 'tabs',
					'container_id'   => 'main-col-menu',
					'menu_id'        => 'dept-nav',
					'menu_class'     => 'bar'
				);


				wp_nav_menu( $args );
				?>
			</div>
		</div>
	</div>
<?php endif; ?>

