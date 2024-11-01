<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_Redis_info { function __construct($url,$nonce,$wpc_tools,$redisSettings) {	
	
if (class_exists('Redis'))
{
	$redis = new Redis(); 
	$redisSuccess=true;
	
	try 
	{
		$host = ($redisSettings['host']??'')!==''?$redisSettings['host']:'localhost';
		$port = ($redisSettings['port']??'')!==''?absint($redisSettings['port']):6379;
		$redis->connect($host, $port);
		$redisSuccess = 'host';
	}
	catch (Exception $e) 
	{ 
		if (($redisSettings['unix']??'')!=='')
		{
			try { $redis->connect(esc_url($redisSettings['unix'])); $redisSuccess = 'unix'; }
			catch (Exception $e) 	
			{ 
				$redisSuccess=false;
				$wpc_tools->error('Redis',(strtolower($e->getMessage())));
			}
		}
		else $redisSuccess=false;
		
		if ($redisSuccess===false)
		{
			$wpc_tools->error('Redis',(strtolower($e->getMessage())));
			echo '<p>', esc_attr__('Not available, please define host:port or unix path.','atec-cache-info'), '</p>';

			echo
				'<form class="atec-border-tiny" method="post" action="'.esc_url($url).'&_wpnonce='.esc_attr($nonce).'">
					<table>
					<tr>
						<td><lable for="redis_host">', esc_attr__('Host','atec-cache-info'), '</lable><br><input size="24" type="text" placeholder="Host" name="redis_host" value="', esc_url($redisSettings['host']??''), '"></td>
						<td><lable for="redis_port">', esc_attr__('Port','atec-cache-info'), '</lable><br><input size="6" type="text" placeholder="Port" name="redis_port" value="', esc_url($redisSettings['port']??''), '"></td>
					</tr>
					<tr>
						<td colspan="2"><lable for="redis_unix">', esc_attr__('Unix socket','atec-cache-info'), '</lable><br><input size="35" type="text" placeholder="Unix socket" name="redis_unix" value="', esc_url($redisSettings['unix']??''), '"></td>
					</tr>
					<tr>
						<td colspan="2"><br><input class="button button-primary"  type="submit" value="', esc_attr__('Save','atec-cache-info'), '"></td>
					</tr>
					</table>
				</form>
				<br>';
		}
	}

	if (is_object($redis) && !empty($redis) && $redisSuccess)
	{
		try
		{
			$pong=@$redis->ping();
			if (!$redis->ping()) { $wpc_tools->error('Redis',esc_attr(__('connection failed','atec-cache-info'))); }
			else
			{
				$server=$redis->info('server');
				$stats = $redis->info('stats');
				$memory = $redis->info('memory');

				$total=$stats['keyspace_hits']+$stats['keyspace_misses']+0.001;
				$hits=$stats['keyspace_hits']*100/$total;
				$misses=$stats['keyspace_misses']*100/$total;

				echo'
				<table class="atec-table atec-table-tiny atec-table-td-first">
				<tbody>
					<tr><td>Version:</td><td>', esc_attr($server['redis_version']), '</td></tr>
					<tr><td>', esc_attr__('Connection','atec-cache-info'), ':</td><td>', esc_html($redisSuccess==='host'?$host.':'.$port:$redisSettings['unix']), '</td></tr>
					<tr><td>', esc_attr__('Used','atec-cache-info').':</td><td>', esc_attr(size_format($memory['used_memory'])), '</td></tr>
					<tr><td>', esc_attr__('Hits','atec-cache-info').':</td><td>', esc_attr(number_format($stats['keyspace_hits']).sprintf(" (%.1f%%)",$hits)), '</td></tr>
					<tr><td>', esc_attr__('Misses','atec-cache-info').':</td><td>', esc_attr(number_format($stats['keyspace_misses']).sprintf(" (%.1f%%)",$misses)), '</td></tr>
				</tbody>
				</table>';
				
				$wpc_tools->hitrate($hits,$misses);
				
				$testKey='atec_redis_test_key';
				$redis->set($testKey,'hello');
				$success=$redis->get($testKey)=='hello';
				atec_badge('Redis '.__('is writeable','atec-cache-info'),'Writing to cache failed',$success);
				if ($success) $redis->del($testKey);
			}
		}
		catch (Exception $e) { $wpc_tools->error('Redis',(strtolower($e->getMessage()))); }
	}
	else atec_reg_inline_script('redis_flush', 'jQuery("#Redis_flush").hide();', true);
}
else $wpc_tools->error('Redis',esc_attr(__('class is NOT available','atec-cache-info')));
	
}}
?>