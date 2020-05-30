<?php
/**
 * Wrong module template.
 *
 * @since 3.2.0
 */
?>
<div class="sui-box-body">
	<div class="sui-notice sui-notice-info inline">
		<p><?php
		printf(
			__( 'This module can be overridden by subsite admins as per your <a href="%s">Permissions Settings</a>.', 'ub' ),
			esc_url( $url )
		);

?></p>
	</div>
</div>
