<!DOCTYPE html>
<html>
<head>
	<title>Meetings</title>
	<style type="text/css">
		h1 {
			margin-bottom: 20px;
		}

		h2 {
			margin: 15px 0px;
		}
	</style>
	<style type="text/css"></style>
</head>
<body style="text-align: center; font-size: 1.5em;">
<h1>Meetings This Week</h1>
<?php global $upcoming; ?>
<h2><?php echo esc_html( $upcoming->main_location['location_name'] ); ?></h2>
<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/meeting.png">

<?php $previous = null; ?>
<?php foreach ( $upcoming->events as $event ) : ?>
	<?php if ( ! $previous || $previous->event_start_date != $event->event_start_date ) : ?>
		<h2><?php echo mysql2date( 'l, F jS, Y', $event->event_start_date . ' ' . $event->event_start_time ); ?></h2>
	<?php endif; ?>
	<div>
		<b><?php echo mysql2date( 'g:i a', $event->event_start_time ); ?></b> <?php echo sudbury_strip_meeting_title( $event->event_name ); ?>
		<br>
		<?php echo esc_html( $event->location->location_name ); ?>
	</div>
	<?php $previous = $event; ?>
<?php endforeach; ?>
</body>
</html>