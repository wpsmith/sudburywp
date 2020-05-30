	<?php include (get_template_directory() . '/options.php'); ?>
				<div class="clear"></div>
			</div>
		</div>
		<?php if( $bp_existed == 'true' ) { //check if bp existed ?>
		<div id="login-wrapper">
			<div id="login">
					<?php do_action( 'bp_before_search_login_bar' ) ?>
						<?php
							locate_template( array( '/elements/join.php' ), true ); ?>
				<div id="login-box">
					<?php if ( !is_user_logged_in() ) : ?>

						<form name="login-form" id="login-form" action="<?php echo site_url( 'wp-login.php' ) ?>" method="post">
							<label>Username</label><input type="text" name="log" id="user_login" value="<?php if(isset($user_login))echo esc_attr(stripslashes($user_login)); ?>" onfocus="if (this.value == '<?php _e( 'Username', TEMPLATE_DOMAIN ) ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e( 'Username', TEMPLATE_DOMAIN ) ?>';}" size="12" tabindex="97" />
							<label>Password</label>	<input type="password" name="pwd" id="user_pass" class="input" value="" size="12" tabindex="98" />

							<input type="checkbox" name="rememberme" id="rememberme" value="forever" title="<?php _e( 'Remember Me', TEMPLATE_DOMAIN ) ?>" tabindex="99" />

							<input type="submit" name="wp-submit" id="wp-submit" value="<?php _e( 'Log In', TEMPLATE_DOMAIN ) ?>"/>
							<input type="hidden" name="redirect_to" value="<?php echo bp_root_domain() ?>" />
							<input type="hidden" name="testcookie" value="1" />

							<?php do_action( 'bp_login_bar_logged_out' ) ?>
						</form>
					<?php else : ?>
						<div id="logout-link">
						<p><?php echo bp_core_fetch_avatar( array( 'item_id' => bp_loggedin_user_id() ) ); ?> <span class="user-name"><?php echo bp_core_get_userlink( bp_loggedin_user_id() ); ?></span>
						<a class="button logout" href="<?php echo wp_logout_url( bp_get_root_domain() ) ?>"><?php _e( 'Log Out', TEMPLATE_DOMAIN) ?></a></p>
							<?php do_action( 'bp_login_bar_logged_in' ) ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="clear"></div>
						<?php if ( function_exists( 'bp_message_get_notices' ) ) : ?>
							<?php bp_message_get_notices(); /* Site wide notices to all users */ ?>
						<?php endif; ?>
							<div class="clear"></div>
				<?php do_action( 'bp_search_login_bar' ) ?>
				<?php do_action( 'bp_after_search_login_bar' ) ?>
			</div>
		</div>
			<?php if($bp_existed == 'true') : ?>
				<?php do_action( 'bp_after_container' ) ?>
				<?php do_action( 'bp_before_footer' ) ?>
		<?php endif; ?>
		<?php do_action( 'bp_before_footer' ) ?>
					<?php } ?>

		<?php
		if(has_nav_menu('footer-nav')){
		?>
			<div class="navigation-wrapper">
				<div class="top-navigation">
					<div class="navigation-block">
						<?php
						wp_nav_menu( array( 'theme_location' => 'footer-nav',
											'container' => false,
											'fallback_cb' => false,
											'menu_class' => 'sf-menu',
											'depth' => 1
											));
						?>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<?php
			}

			?>
			<div id="footer-wrapper">
				<div id="footer">
					<?php if($bp_existed == 'true') : ?>
						<?php do_action( 'bp_footer' ) ?>
					<?php endif; ?>
					<div id="footer-links">
						<?php
							$rsslink = get_option('ne_buddybusiness_rss_link');
						?>
						<div id="rss-float">
							<a href="<?php echo $rsslink ?>" title="<?php _e( 'Follow us', TEMPLATE_DOMAIN)?>"><?php _e( 'Follow us', TEMPLATE_DOMAIN)?></a>
						</div>

						<a href="<?php echo home_url(); ?>"><?php _e( 'Copyright', TEMPLATE_DOMAIN ) ?> &copy;<?php echo gmdate(__('Y')); ?> <?php get_bloginfo( 'name' ) ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;
							<a href="#header"><?php _e('Go back to top &uarr;', TEMPLATE_DOMAIN); ?></a>

					</div>
						<?php if($bp_existed == 'true') : ?>
							<?php do_action( 'bp_after_footer' ) ?>
					    <?php endif; ?>
				</div>
			</div>
		<?php wp_footer(); ?>

	</body>

</html>