<?php
	$join_message = get_option('ne_buddybusiness_join_message');
	$members_message = get_option('ne_buddybusiness_members_message');
?>

<?php if ( !is_user_logged_in() ) { ?>
		<div id="signup-box">
			<div id="signup-action">
					<?php if ( bp_get_signup_allowed() ) : ?>
						<?php printf( __( '<a href="%s" title="Create an account" class="button">Sign up</a>', TEMPLATE_DOMAIN ), site_url( BP_REGISTER_SLUG . '/' ) ) ?>
					<?php endif; ?>
			</div>
					<p><?php echo stripslashes($join_message); ?></p>
		</div>
	<?php } else {?>
			<div id="signup-box">
						<p><?php echo stripslashes($members_message); ?></p>
			</div>
		<?php } ?>