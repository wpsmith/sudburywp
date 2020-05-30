<?php

/**
 * shortcode to automatically change seasonal meteor slideshow according to the current season
 *
 * @author     Moe Finigan <moe@moegood.com>
 * @package    Sudbury
 * @subpackage Shortcodes
 */
class Sudbury_Tabbed_Events {
	function __construct() {

		add_action( 'init', array( &$this, 'init' ) );
	}

	function init() {
		add_shortcode( 'sud_tabbed_events', array( &$this, 'shortcode' ) );

	}

	function shortcode() {
		ob_start();
		//do_shortcode('[tabby title="Town Events"]');
		?>
		<div class="events-widget">
			[tabby title="Town Events"]
			<h2>
				<a href="/calendar" class="link-black" title="Click for Full Town Calendar"><i class="fa fa-calendar"></i>
				</a>Today's Town Events</h2>
			<ul>
				<?php //removed limit because it was counting all events on that day which included a lot of senior events, thus not showing all town events.
				$ev_output = EM_Events::output( array(
					'scope'          => 'today',
					'no_events_text' => '<li>No events today</li>',
					'format'         => '<li>#_12HSTARTTIME - <b>#_EVENTLINK</b></li>'
				) );
				if ( ! $ev_output ) {
					$ev_output = "<li>No Town Events today</li>";
				}
				echo $ev_output;
				?></ul>
			<?php
			//do_shortcode('[tabby title="Senior Events"]');
			?>
			[tabby title="Senior Events"]
			<h2>
				<a href="https://sudburyseniorcenter.org/calendar/events/" class="link-black" title="Click for Senior Center Calendar" target="_blank"><i class="fa fa-calendar"></i>
				</a>Today's Senior Center Events</h2>
			<?php
			$args     = array(
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			);
			$instance = array(
				"title"           => "",
				"scope"           => "today",
				"order"           => "ASC",
				"limit"           => "100",
				"category"        => "381",
				"format"          => "#_12HSTARTTIME - <b>#_EVENTLINK</b>",
				"nolistwrap"      => false,
				"orderby"         => "event_start_date,event_start_time,event_name",
				"all_events"      => 0,
				"all_events_text" => "all events",
				"no_events_text"  => "<li>No events today</li>",
				"owner"           => false,
			);
			the_widget( 'EM_Widget', $instance, $args );

			//do_shortcode('[tabby title="Library Events"]');
			?>
			[tabby title="Library Events"]
			<h2>
				<a href="https://goodnowlibrary.org/calendar/" class="link-black" title="Click for Goodnow Library Calendar" target="_blank"><i class="fa fa-calendar"></i>
				</a>Goodnow Library Events</h2>
			<iframe id="goodnowframe" sandbox="allow-same-origin allow-scripts" src="about:blank" data-src="https://goodnowlibrary.assabetinteractive.com/calendar/upcoming-events/" name="ai_iframe" width="100%" height="200" frameborder="0" scrolling="yes"></iframe>
			<script type="text/javascript">
				jQuery(window).load(function () {
					if (jQuery('#goodnowframe').length <= 0) {
						return;
					}  // to avoid errors in case the element doesn't exist on the page / removed.
					jQuery('#goodnowframe').attr('src', jQuery('#goodnowframe').data('src'));
				});
			</script>

			[tabbyending]
		</div>
		<?php
		//do_shortcode('[tabbyending]');
		$shortcode_string = ob_get_clean();

		return do_shortcode( $shortcode_string );

	}
}

new Sudbury_Tabbed_Events();
