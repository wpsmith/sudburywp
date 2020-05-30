<?php
// Before removing this file, please verify the PHP ini setting `auto_prepend_file` does not point to this.

if (file_exists('/mnt/dev0/nginx/sudbury.ma.us/wp-content/plugins/wordfence/waf/bootstrap.php')) {
	define("WFWAF_LOG_PATH", '/mnt/dev0/nginx/sudbury.ma.us/wp-content/wflogs/');
	include_once '/mnt/dev0/nginx/sudbury.ma.us/wp-content/plugins/wordfence/waf/bootstrap.php';
}
?>