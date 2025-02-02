<?php
if (!defined('ABSPATH')) { exit(); }
/**
* Fixit: 1.7.21 | NOT critical
* Backwards compatible unix path
*/

$optName = 'atec_WPCI_settings';
$options=get_option($optName,[]); $updateOpt = false;

if (!isset($options['redis'])) { $options['redis']=['host'=>'localhost', 'port'=>6379, 'conn'=>'TCP/IP']; $updateOpt=true; }
elseif (isset($options['redis']['unix']) && $options['redis']['unix']!=='')
{
    $options['redis']['host'] = $options['redis']['unix']; // backwards compatible
    $options['redis']['port'] = 0;
    $options['redis']['conn'] = 'SOCKET';
    $updateOpt=true;
}
if (!isset($options['memcached'])) { $options['memcached']=['host'=>'localhost', 'port'=>11211, 'conn'=>'TCP/IP']; $updateOpt=true; }
elseif (isset($options['memcached']['unix']) && $options['memcached']['unix']!=='')
{
    $options['memcached']['host'] = $options['memcached']['unix']; // backwards compatible
    $options['memcached']['port'] = 0;
    $options['memcached']['conn'] = 'SOCKET';
    $updateOpt=true;
}

if ( $updateOpt) update_option($optName, $options, false);
?>