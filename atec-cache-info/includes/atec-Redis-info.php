<?php
// 1: redis-cli: 		2: auth pwd		3: CONFIG SET requirepass pwd
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_Redis_info { function __construct($url,$nonce,$wpc_tools,$redSettings) {	
	
if (class_exists('Redis'))
{
	$redis 				= new Redis(); 
	$redSuccess 		= true;
	$redType 			= 'host';
	
	$redHost 		= $redSettings['host']??'';
	$redPort 		= $redSettings['port']??'';
	$redPwd 		= $redSettings['pwd']??'';
	$redSocket 	= $redSettings['unix']??'';
	
	try 
	{
		if ($redHost!=='' && $redPort!=='')
		{
			$redis->connect($redHost, $redPort);
			if ($redPwd!=='') $redSuccess=$redis->auth($redPwd);
			if  ($redSuccess) $redSuccess = $redSuccess && $redis->ping();
		}
		else throw new RedisException('Host/port not set');
	}
	catch (RedisException $e) 
	{ 
		if (($redSettings['unix']??'')!=='')
		{
			try 
			{ 
				$redis->connect($redSettings['unix']); 
				$redSuccess = $redSuccess && $redis->ping();
				$redType = 'socket'; 
			}
			catch (RedisException $e) 	
			{ 
				$redSuccess = false;
				$wpc_tools->error('Redis',(strtolower(rtrim($e->getMessage(),'.'))));
			}
		}
		else $redSuccess=false;
		
		if ($redSuccess===false)
		{
			echo 
			'<p>
				<span class="atec-red">', esc_attr__('Connection failed','atec-cache-info'), '</span>.<br>', 
				esc_attr__('Please define host:port OR unix path.','atec-cache-info'),
				'<div style="margin-top:-15px;"><small>', esc_attr__('Unix path is dominant.','atec-cache-info'), '</small></div>
			</p>
			<form class="atec-border-tiny" method="post" action="'.esc_url($url).'&action=saveRed&_wpnonce='.esc_attr($nonce).'">
				<table>
				<tr>
					<td class="atec-left"><label for="redis_host">', esc_attr__('Host','atec-cache-info'), '</lable><br>
						<input size="15" type="text" placeholder="localhost" name="redis_host" value="', esc_attr($redHost), '">
					</td>
					<td class="atec-left"><label for="redis_port">', esc_attr__('Port','atec-cache-info'), '</lable><br>
						<input size="3" type="text" placeholder="6379" name="redis_port" value="', esc_attr($redPort), '">
					</td>
					<td class="atec-left"><label for="redis_pwd">', esc_attr__('Password','atec-cache-info'), '</lable><br>
						<input size="6" type="text" placeholder="Password" name="redis_pwd" value="', esc_attr($redPwd), '">
					</td>
				</tr>
				<tr>
					<td colspan="3"><label for="redis_unix">', esc_attr__('Unix socket','atec-cache-info'), '</lable><br>
						<input size="24" type="text" placeholder="Unix socket" name="redis_unix" value="', esc_textarea($redSocket), '">
					</td>
				</tr>
				<tr>
					<td colspan="3"><br><input class="button button-primary"  type="submit" value="', esc_attr__('Save','atec-cache-info'), '"></td>
				</tr>
				</table>
			</form>';
		}
	}

	if (is_object($redis) && !empty($redis) && $redSuccess)
	{
		try
		{
			$server		=$redis->info('server');
			$stats 		= $redis->info('stats');
			$memory 	= $redis->info('memory');

			$total=$stats['keyspace_hits']+$stats['keyspace_misses']+0.001;
			$hits=$stats['keyspace_hits']*100/$total;
			$misses=$stats['keyspace_misses']*100/$total;

			echo'
			<table class="atec-table atec-table-tiny atec-table-td-first">
			<tbody>
				<tr><td>Version:</td><td>', esc_attr($server['redis_version']), '</td><td></td></tr>';
				if ($redType==='host')
				{
					echo '
					<tr><td>', esc_attr__('Host','atec-cache-info'), ':</td><td>', esc_html($redHost), '</td><td></td></tr>
					<tr><td>', esc_attr__('Port','atec-cache-info'), ':</td><td>', esc_html($redPort), '</td><td></td></tr>';
					if ($redPwd!=='') echo '<tr><td>', esc_attr__('Password','atec-cache-info'), ':</td><td>', esc_html($redPwd), '</td><td></td></tr>';
				}
				else echo '<tr><td>', esc_attr__('Socket','atec-cache-info'), ':</td><td>', esc_html($redSettings['unix']), '</td><td></td></tr>';
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