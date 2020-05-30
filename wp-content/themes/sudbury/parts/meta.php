<?php if ( is_sticky() ) : ?>
	<i class="fa fa-thumbtack text-warning"></i>
<?php endif; ?>
<?php if ( get_post_type() != 'page' ) : ?>
	<span class="meta-info-published">Published <a href="#"><?php the_time( get_option( 'date_format' ) ); // ( 'l, n/j/Y g:i a' ); ?></a> | <a href="<?php sudbury_the_site_url(); ?>"><?php sudbury_the_site_name(); ?></a></span>
<?php endif; ?>

<?php // Don't show updated on archive lists ?>
<?php if ( is_singular() && get_post_type() != 'page' && get_the_modified_date( 'U' ) > get_the_date( 'U' ) + 24 * 60 * 60 ) : ?>
	<span class="meta-info-updated"> | Updated <a href="<?php echo esc_url( get_month_link( get_the_modified_date( 'Y' ), get_the_modified_date( 'm' ) ) ); ?>"><?php the_modified_date(); ?></a></span>
<?php endif; ?>

<?php if ( sudbury_is_post_archived() ) : ?>
	<span class="meta-info-archived">
		<?php if ( get_post_meta( get_the_ID(), '_post-expiration-enabled', true ) && ( $expired_at = get_post_meta( get_the_ID(), '_post-expiration-timestamp', true ) ) < current_time( 'timestamp' ) ) { ?>
			| Automatically Archived on <?php echo date( 'n/j/Y ', $expired_at + 4 * 60 * 60 ); ?>
		<?php } else { ?>
			| <span class="badge badge-warning">Archived</span>
		<?php } ?>
	</span>
<?php endif; ?>


