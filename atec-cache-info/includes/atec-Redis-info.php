<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_Redis_info { function __construct($url,$nonce,$wpc_tools,$redisSettings) {	
	
if (class_exists('Redis'))
{
	$redis 				= new Redis(); 
	$redisSuccess 	= true;
	$redisType 		= 'host';
	
	$host = ($redisSettings['host']??'')!==''?$redisSettings['host']:'localhost';
	$port = ($redisSettings['port']??'')!==''?absint($redisSettings['port']):6379;
	$pwd = $redisSettings['pwd']??'';
	// redis-cli: CONFIG SET requirepass secret_password
	try 
	{
		$redis->connect($host, $port);
		if ($pwd!=='') $redisSuccess=$redis->auth($pwd);
		if  ($redisSuccess) $redisSuccess = $redisSuccess && $redis->ping();
	}
	catch (RedisException $e) 
	{ 
		if (($redisSettings['unix']??'')!=='')
		{
			try 
			{ 
				$redis->connect($redisSettings['unix']); 
				$redisSuccess = $redisSuccess && $redis->ping();
				$redisType = 'socket'; 
			}
			catch (RedisException $e) 	
			{ 
				$redisSuccess = false;
				$wpc_tools->error('Redis',(strtolower(rtrim($e->getMessage(),'.'))));
			}
		}
		else $redisSuccess=false;
		
		if ($redisSuccess===false)
		{
			echo '<p>', esc_attr__('Connection failed – please define host:port OR unix path.','atec-cache-info'), '</p>',
				'<form class="atec-border-tiny" method="post" action="'.esc_url($url).'&_wpnonce='.esc_attr($nonce).'">
					<table>
					<tr>
						<td class="atec-left"><label for="redis_host">', esc_attr__('Host','atec-cache-info'), '</lable><br>
							<input size="16" type="text" placeholder="Host" name="redis_host" value="', esc_attr($redisSettings['host']??''), '">
						</td>
						<td class="atec-left"><label for="redis_port">', esc_attr__('Port','atec-cache-info'), '</lable><br>
							<input size="6" type="text" placeholder="Port" name="redis_port" value="', esc_attr($redisSettings['port']??''), '">
						</td>
						<td class="atec-left"><label for="redis_pwd">', esc_attr__('Password','atec-cache-info'), '</lable><br>
							<input size="12" type="text" placeholder="Password" name="redis_pwd" value="', esc_attr($redisSettings['pwd']??''), '">
						</td>
					</tr>
					<tr>
						<td colspan="3"><label for="redis_unix">', esc_attr__('Unix socket','atec-cache-info'), '</lable><br>
							<input size="35" type="text" placeholder="Unix socket" name="redis_unix" value="', esc_textarea($redisSettings['unix']??''), '">
						</td>
					</tr>
					<tr>
						<td colspan="3"><br><input class="button button-primary"  type="submit" value="', esc_attr__('Save','atec-cache-info'), '"></td>
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
			$server=$redis->info('server');
			$stats = $redis->info('stats');
			$memory = $redis->info('memory');

			$total=$stats['keyspace_hits']+$stats['keyspace_misses']+0.001;
			$hits=$stats['keyspace_hits']*100/$total;
			$misses=$stats['keyspace_misses']*100/$total;

			echo'
			<table class="atec-table atec-table-tiny atec-table-td-first">
			<tbody>
				<tr><td>Version:</td><td>', esc_attr($server['redis_version']), '</td><td></td></tr>';
				if ($redisType==='host')
				{
					echo '
					<tr><td>', esc_attr__('Host','atec-cache-info'), ':</td><td>', esc_html($host), '</td><td></td></tr>
					<tr><td>', esc_attr__('Port','atec-cache-info'), ':</td><td>', esc_html($port), '</td><td></td></tr>';
					if ($pwd!=='') echo '<tr><td>', esc_attr__('Password','atec-cache-info'), ':</td><td>', esc_html($pwd), '</td><td></td></tr>';
				}
				else echo '<tr><td>', esc_attr__('Socket','atec-cache-info'), ':</td><td>', esc_html($redisSettings['unix']), '</td><td></td></tr>';
				atec_empty_tr();
				echo '
				<tr><td>', esc_attr__('Used','atec-cache-info').':</td><td>', esc_attr(size_format($memory['used_memory'])), '</td><td></td></tr>
				<tr><td>', esc_attr__('Hits','atec-cache-info').':</td>
					<td>', esc_attr(number_format($stats['keyspace_hits'])), '</td><td><small>', esc_attr(sprintf(" (%.1f%%)",$hits)), '</small></td></tr>
				<tr><td>', esc_attr__('Misses','atec-cache-info').':</td>
					<td>', esc_attr(number_format($stats['keyspace_misses'])), '</td><td><small>', esc_attr(sprintf(" (%.1f%%)",$misses)), '</small></td></tr>
			</tbody>
			</table>';
				
			$wpc_tools->hitrate($hits,$misses);
			
			$testKey='atec_redis_test_key';
			$redis->set($testKey,'hello');
			$success=$redis->get($testKey)=='hello';
			atec_badge('Redis '.__('is writeable','atec-cache-info'),'Writing to cache failed',$success);
			if ($success) $redis->del($testKey);
		}
		catch (RedisException $e) { $wpc_tools->error('Redis',(strtolower(rtrim($e->getMessage(),'.')))); }
	}
	else atec_reg_inline_script('redis_flush', 'jQuery("#Redis_flush").hide();', true);
}
else $wpc_tools->error('Redis',esc_attr(__('class is NOT available','atec-cache-info')));
	
}}
?>