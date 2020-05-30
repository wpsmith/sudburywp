<?php include( get_template_directory() . '/includes/conditional-functions.php' ); ?>
			<ul class="sf-menu">
								<li<?php if ( is_front_page()) : ?> class="selected"<?php endif; ?>>
						<a href="<?php echo site_url() ?>" title="<?php _e( 'Home', TEMPLATE_DOMAIN ) ?>"><?php _e( 'Home', TEMPLATE_DOMAIN ) ?></a>
						</li>
<?php
$feature_navigation = get_option('ne_buddybusiness_feature_navigation');
$feature_page = get_option('ne_buddybusiness_feature_id');
$member_navigation = get_option('ne_buddybusiness_community_navigation');
$member_page = get_option('ne_buddybusiness_community_id');
?>
<?php $featured_pageID = wt_get_ID_by_page_name($feature_page); ?>
<?php $member_pageID = wt_get_ID_by_page_name($member_page); ?>

<?php
if ($feature_navigation == "no" && $member_navigation == "no"){ wp_list_pages('exclude='. $featured_pageID . ','. $member_pageID . '&title_li='); }
else if ($feature_navigation == "no" && $member_navigation == "yes"){ wp_list_pages('exclude='. $featured_pageID . '&title_li='); }
else if ($feature_navigation == "yes" && $member_navigation == "no"){ wp_list_pages('exclude='. $member_pageID . '&title_li='); }
else{ wp_list_pages('title_li='); }
?>

<?php

	$bp_nav_pos = get_option('ne_buddybusiness_bp_bar');

	if ($bp_nav_pos == "top"){

?>

<?php if( $bp_existed == 'true' ) { //check if bp existed ?>

		<?php if ( 'activity' != bp_dtheme_page_on_front() && bp_is_active( 'activity' ) ) : ?>
	<li<?php if ( bp_is_page( BP_ACTIVITY_SLUG ) ) : ?> class="selected"<?php endif; ?>>
		<a href="<?php echo site_url() ?>/<?php echo BP_ACTIVITY_SLUG ?>/" title="<?php _e( 'Activity', TEMPLATE_DOMAIN ) ?>"><?php _e( 'Activity', TEMPLATE_DOMAIN ) ?></a>
	</li>
<?php endif; ?>

	<li<?php if (  bp_is_page( BP_MEMBERS_SLUG ) || bp_is_user() ) : ?> class="selected"<?php endif; ?>>
		<a href="<?php echo site_url() ?>/<?php echo BP_MEMBERS_SLUG ?>/" title="<?php _e( 'Members', TEMPLATE_DOMAIN ) ?>"><?php _e( 'Members', TEMPLATE_DOMAIN ) ?></a>
	</li>

	<?php if ( bp_is_active( 'groups' ) ) : ?>
		<li<?php if ( bp_is_page( BP_GROUPS_SLUG ) || bp_is_group() ) : ?> class="selected"<?php endif; ?>>
			<a href="<?php echo site_url() ?>/<?php echo BP_GROUPS_SLUG ?>/" title="<?php _e( 'Groups', TEMPLATE_DOMAIN ) ?>"><?php _e( 'Groups', TEMPLATE_DOMAIN ) ?></a>
		</li>
	<?php endif; ?>

	<?php if ( bp_is_active( 'forums' ) && bp_is_active( 'groups' ) && ( function_exists( 'bp_forums_is_installed_correctly' ) && !(int) bp_get_option( 'bp-disable-forum-directory' ) ) && bp_forums_is_installed_correctly() ) : ?>
		<li<?php if ( bp_is_page( BP_FORUMS_SLUG ) ) : ?> class="selected"<?php endif; ?>>
			<a href="<?php echo site_url() ?>/<?php echo BP_FORUMS_SLUG ?>/" title="<?php _e( 'Forums', TEMPLATE_DOMAIN ) ?>"><?php _e( 'Forums', TEMPLATE_DOMAIN ) ?></a>
		</li>
	<?php endif; ?>

	<?php if ( bp_is_active( 'blogs' ) && is_multisite() ) : ?>
		<li<?php if ( bp_is_page( BP_BLOGS_SLUG ) ) : ?> class="selected"<?php endif; ?>>
			<a href="<?php echo site_url() ?>/<?php echo BP_BLOGS_SLUG ?>/" title="<?php _e( 'Blogs', TEMPLATE_DOMAIN ) ?>"><?php _e( 'Blogs', TEMPLATE_DOMAIN ) ?></a>
		</li>
	<?php endif; ?>
<?php do_action( 'bp_nav_items' ); ?>

<?php

}

?>
	<?php

	}

	?>

					<?php do_action( 'bp_nav_items' ); ?>
			</ul>