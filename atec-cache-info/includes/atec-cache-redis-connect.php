<?php
if (!defined('ABSPATH')) { exit(); }

function atec_redis_flush_rwpoc($redis)
{
	$allKeys = $redis->keys(WP_REDIS_KEY_SALT.'*');
	if (!empty($allKeys)) foreach($allKeys as $key) $redis->del($key);
}

function atec_redis_connect($redSettings)
{
	$redis 		= new Redis(); 
	$redSuccess = true;
	$redConn 	= $redSettings['conn']??'TCP/IP';
	$redHost 	= $redSettings['host']??'localhost';
	$redPort 	= (int) ($redSettings['port']??6379);
	$redPwd 	= $redSettings['pwd']??'';
	try 
	{
		if ($redHost!=='' && ($redPort!=='' || $redConn==='SOCKET'))
		{
			if ($redConn==='SOCKET') $redis->connect($redSettings['host']); 
			else $redis->connect($redHost, $redPort);
			if ($redPwd!=='') $redSuccess = $redis->auth($redPwd);
			if ($redSuccess) $redSuccess = $redSuccess && $redis->ping();

		}
		else throw new RedisException('Connection parameter missing');
	}
	catch (RedisException $e) { return array('redis'=>null, 'error'=>rtrim($e->getMessage(),'.')); }
	return array('redis'=>$redSuccess?$redis:null, 'error'=>$redSuccess?'':'Connection failed', 	'host'=>$redHost, 'port'=>$redPort, 'conn'=>$redConn); 

}
?>