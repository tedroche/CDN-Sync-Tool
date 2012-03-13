<?php
/*
Plugin Name: CDN Sync Tool
Plugin URI: http://www.olliearmstrong.co.uk
Description: Allows wordpress owners to sync their static files to CDN
Author: Fubra Limited
Author URI: http://www.catn.com
Version: 2.0
*/

global $wpdb;

define('CST_DIR', dirname(__FILE__).'/');
define('CST_VERSION', '2.0');
define('CST_URL', admin_url('options-general.php'));
define('CST_FILE', __FILE__);
define('CST_TABLE_FILES', $wpdb->get_blog_prefix().'cst_files');
define('CST_CONTACT_EMAIL', 'support@catn.com');


if (is_admin()) {
	require_once CST_DIR.'lib/Cst.php';
	$core = new Cst();
}

function cst_install() {
	global $wpdb;

	$wpdb->query("
		CREATE TABLE IF NOT EXISTS ".CST_TABLE_FILES." (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `file_dir` text NOT NULL,
		  `remote_path` text NOT NULL,
		  `changedate` int(11) DEFAULT NULL,
		  `synced` tinyint(1) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	");

	update_option('cst-js-savepath', 'wp-content/uploads');
	wp_schedule_event(time(), 'hourly', 'cron_cst_sync');
}

function cst_deactivate() {
	wp_clear_scheduled_hook('cron_cst_sync');
}

function hourlySync() {
	$core->syncFiles();
}

register_activation_hook(__FILE__, "cst_install");
register_deactivation_hook(__FILE__, 'cst_deactivate');
add_action('cron_cst_sync', 'hourlySync');
