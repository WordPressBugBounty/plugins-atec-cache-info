<?php
if (!defined('ABSPATH')) { exit(); }

function atec_memcached_flush_mwpoc($mem)
{
	$allKeys	= $m->getAllKeys();
	$reg	 	= '/'.WP_MEMCACHED_KEY_SALT.'.*/';
	if (!empty($allKeys)) foreach($allKeys as $key) if(preg_match($reg, $key)) $redis->delete($key);
}

function atec_memcached_connect($memSettings)
{
	$m = new Memcached(); 

	$memSuccess = true;
	$memConn 	= $memSettings['conn']??'TCP/IP';
	$memHost 	= $memSettings['host']??'localhost';
	$memPort 	= (int) ($memSettings['port']??11211);
	if ($memConn==='SOCKET') $memPort=0;
	$m->addServer($memHost, $memPort);
	if (!$m->getVersion()) $m = false;

	return array('m'=>$m, 'host'=>$memHost, 'port'=>$memPort, 'conn'=>$memConn); 
}
?>