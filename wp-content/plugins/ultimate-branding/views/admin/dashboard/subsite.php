<?php
$args = array(
	'title' => __( 'Dashboard', 'ub' ),
	'show_manage_all_modules_button' => $show_manage_all_modules_button,
	'documentation_chapter' => $this->get_current_group_documentation_chapter(),
	'helps' => $helps,
);
$this->render( 'admin/common/header', $args );
?>
<section id="sui-branda-content" class="sui-container">
<?php
$args = array(
	'stats' => $stats,
	'modules' => $modules,
	'groups' => $groups,
	'mode' => 'subsite',
	'class' => $this->get_hide_branding_class(),
	'style' => $this->get_box_summary_image_style(),
);
$this->render( 'admin/dashboard/subsite/widget-summary', $args );
$this->render( 'admin/dashboard/widget-modules', $args );
?>
</section>
<?php if ( $message['show'] && is_super_admin() ) { ?>
<div
    id="branda-notice-permissions-settings"
    class="sui-notice-top sui-notice-success sui-can-dismiss"
    data-nonce="<?php echo esc_attr( $message['nonce'] ); ?>"
    data-id="<?php echo esc_attr( $message['user_id'] ); ?>"
>
    <p><?php
	printf(
		__( '%s, only modules you have allowed subsite access to will be able here. You can add or remove modules from the <a href="%s">Permissions Settings</a>.', 'ub' ),
		$message['username'],
		$message['url']
	);
?></p>
    <span class="sui-notice-dismiss">
        <a role="button" href="#" aria-label="<?php esc_attr_e( 'Dismiss', 'ub' ); ?>" class="sui-icon-check"></a>
    </span>
</div>
<?php } ?>