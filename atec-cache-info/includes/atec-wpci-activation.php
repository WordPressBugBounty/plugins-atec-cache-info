<?php
if (!defined('ABSPATH')) { exit(); }
if (!function_exists('atec_header')) @require(__DIR__.'/atec-tools.php');
atec_integrity_check(__DIR__);

$optName = 'atec_WPCI_settings';
$options=get_option($optName,[]); $updateOpt = false;

if (!isset($options['redis'])) { $options['redis']=['host'=>'localhost', 'port'=>6379, 'conn'=>'TCP/IP']; $updateOpt=true; }
if (!isset($options['memcached'])) { $options['memcached']=['host'=>'localhost', 'port'=>11211, 'conn'=>'TCP/IP']; $updateOpt=true; }

update_option($optName, $options, false);
?>