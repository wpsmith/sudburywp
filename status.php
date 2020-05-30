<?php header('Content-Type: text/plain'); ?>
<?php $_SERVER['HTTP_HOST'] = 'sudbury.ma.us'; ?>
Sanity Check: Pass

<?php echo "PHP Echo: Pass"; ?>

<?php echo 'PHP Version: ' . phpversion(); ?>

<?php echo 'System Timestamp: ' . time(); ?>

<?php echo 'Server Name: ' . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '(not set)'); ?>

<?php echo 'Loading WordPress'; ?>

<?php $status_timer_start = microtime(true); ?>
<?php include 'wp-load.php'; ?>
<?php echo "Timer Stop: " . (microtime(true) - $status_timer_start); ?>

<?php echo "... Loaded" ?>

<?php if ( ( disk_free_space( '/' ) / disk_total_space( '/' ) ) < .95 ) {
  echo 'Status OK!'; 
} ?>
<?php 
if ( ! get_site_transient( 'status_healthcheck' ) ) {
    if ( file_get_contents( 'https://hchk.io/462f9d86-fc16-4b2c-9b42-eae83135feb7' ) ) { 
        set_site_transient( 'status_healthcheck', 1, time() + 60 );
        echo 'Posted Update';
    } else {
        echo 'Update to Healthcheck Endpoint Failed';
    }
}
