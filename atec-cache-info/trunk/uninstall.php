<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {	exit; }
if (!defined('ATEC_LOADER')) require __DIR__ . '/includes/ATEC/LOADER.php';

use ATEC\INIT;

INIT::delete_settings('wpci');
INIT::set_admin_bar_option('wpci', true);	// this will delete the setting
?>