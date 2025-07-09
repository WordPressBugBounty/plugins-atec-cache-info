<?php
defined('ABSPATH') || exit;

use ATEC\INIT;

(function() {

	$settings = INIT::get_settings('wpci');
	$updateOpt = false;
		if (!isset($settings['redis'])) { $settings['redis']=['host'=>'localhost', 'port'=>6379, 'conn'=>'TCP/IP']; $updateOpt=true; }
		if (!isset($settings['memcached'])) { $settings['memcached']=['host'=>'localhost', 'port'=>11211, 'conn'=>'TCP/IP']; $updateOpt=true; }
	if ($updateOpt) INIT::update_settings('wpci', $settings);

})();
?>