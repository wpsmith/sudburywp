<?php
/**
 * A shortcode to generate a list of all Board Members
 *
 * @author     Eddie Hurtig <hurtige@sudbury.ma.us>
 * @since      2013-08-14
 * @package    Sudbury
 * @subpackage Shortcodes
 */

class Sudbury_Meeting_AttachmentsShortcode
{

    /**
     * Hook into the init action
     */
    public function __construct()
    {
        add_action('init', array( &$this, 'init' ));
    }

    /**
     * Register the Vacancies Shortcode
     */
    public function init()
    {
        add_shortcode('meeting-attachments', array( &$this, 'shortcode' ));
    }


    /**
     * @param        $atts
     * @param string $content
     *
     * @return string
     */
    public function shortcode($atts, $content = '')
    {
        ob_start();
		foreach_blog(function ($b) {
        $meetings = get_posts(array('post_type' => 'meeting', 'posts_per_page' => -1 )); ?>

		<div class="meetings">
		<?php if ($meetings) : ?>
				<div class="tablecap">
					<h4><?php echo esc_html(get_option('blogname')); ?>
						<span style="float:right; font-weight:normal; text-align:right;font-size:12px;">
						<?php if ($email = get_option('sudbury_email')) : ?>
							<?php echo sudbury_protect_emails('<a href="mailto:' . $email . '">' . $email . '</a>'); ?>
						<?php endif; ?>
						<?php if ($phone = get_option('sudbury_telephone')) : ?>
							<?php if ($email) : ?>
								<?php echo '|' ?>
							<?php endif; ?>
							<?php echo $phone; ?>
						<?php endif; ?>
						</span>
					</h4>
					<table cellspacing="0">
						<tbody>
						<tr>
							<th width="70%">Date</th>
							<th width="30%">Attachments</th>
						</tr>
						<?php
                        foreach ($meetings as $meeting) : ?>
							<tr>
								<td><a href="<?php echo get_post_permalink( $meeting->ID ); ?>"><?php echo esc_html($meeting->post_title); ?></a></td>
								<td>
									<?php $attachments = sudbury_get_meeting_attachments($meeting); ?>
									<?php if (!empty($attachments)) : ?>
										<ul style="padding-left:30px;">
											<?php foreach ($attachments as $attachment) : ?>
												<li><a href="<?php echo esc_html( wp_get_attachment_url( $attachment->ID ) ); ?>">attachment(<?php echo esc_html( sudbury_get_meeting_attachment_type( $attachment->ID ) ); ?>)</a></li>

											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<div class="foot"></div>
				</div>
			<?php else : ?>
				<p> Not meetings found for <?php echo get_option('blogname'); ?> </p>
			<?php endif; ?>
		</div>

		<?php
		});
        return ob_get_clean();
    }
}

new Sudbury_Meeting_AttachmentsShortcode();

