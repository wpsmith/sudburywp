	<div id="login-wrapper">
		<div id="login">
				<?php do_action( 'bp_before_search_login_bar' ) ?>

			<?php include (get_template_directory() . '/elements/join.php'); ?>

			<div id="login-box">
				<?php if ( !is_user_logged_in() ) : ?>

					<form name="login-form" id="login-form" action="<?php echo site_url( 'wp-login.php' ) ?>" method="post">
						<label>Username</label><input type="text" name="log" id="user_login" value="<?php _e( 'Username', TEMPLATE_DOMAIN ) ?>" onfocus="if (this.value == '<?php _e( 'Username', TEMPLATE_DOMAIN ) ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e( 'Username', TEMPLATE_DOMAIN ) ?>';}" size="12"/>
						<label>Password</label>	<input type="password" name="pwd" id="user_pass" class="input" value="" size="12" />

						<input type="checkbox" name="rememberme" id="rememberme" value="forever" title="<?php _e( 'Remember Me', TEMPLATE_DOMAIN ) ?>" />

						<input type="submit" name="wp-submit" id="wp-submit" value="<?php _e( 'Log In', TEMPLATE_DOMAIN ) ?>"/>
						<input type="hidden" name="redirect_to" value="<?php echo bp_root_domain() ?>" />
						<input type="hidden" name="testcookie" value="1" />

						<?php do_action( 'bp_login_bar_logged_out' ) ?>
					</form>

				<?php else : ?>

					<div id="logout-link">
								<a href="<?php echo bp_loggedin_user_domain() ?>">
									<?php bp_loggedin_user_avatar( 'type=thumb&width=40&height=40' ) ?>
								</a> &nbsp; <?php bp_loggedinuser_link() ?> / <?php bp_log_out_link() ?>

						<?php do_action( 'bp_login_bar_logged_in' ) ?>
					</div>

				<?php endif; ?>
			</div>

			<div class="clear"></div>
			<?php do_action( 'bp_search_login_bar' ) ?>
			<?php do_action( 'bp_after_search_login_bar' ) ?>
		</div>
	</div>