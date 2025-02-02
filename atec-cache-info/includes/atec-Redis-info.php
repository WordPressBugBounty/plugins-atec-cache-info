<?php
// 1: redis-cli: 		
// 2: auth pwd		
// 3: CONFIG SET requirepass pwd
if (!defined('ABSPATH')) { exit(); }

class ATEC_Redis_info { function __construct($url,$nonce,$wpc_tools,$redSettings) {	
	
if (class_exists('Redis'))
{

	if (isset($redSettings['unix']) && $redSettings['unix']!=='')
	{
		$redHost = $redSettings['unix']; // backwards compatible
		$redConn = 'SOCKET';
	}
	else
	{
		$redHost = $redSettings['host']??'';
		$redConn = $redSettings['conn']??'';
		if ($redConn==='') $redConn='TCP/IP';
	}
	$redPort = $redSettings['port']??'';
	$redPwd  = $redSettings['pwd']??'';

	if (!function_exists('atec_redis_connect')) @require('atec-cache-redis-connect.php');
	$result = atec_redis_connect($redSettings);
	$redis = $result['redis'];

	if (!$redis)
	{
		echo 
		'<p>', esc_attr__('Please define Host:Port OR UNIX path.','atec-cache-info'), '</p>
		<form class="atec-border-tiny" method="post" action="'.esc_url($url).'&action=saveRed&nav=Cache&_wpnonce='.esc_attr($nonce).'">
			<table>
				<tr>
					<td colspan="3"><label for="redis_conn">', esc_attr__('Connection','atec-cache-info'), '</label><br>
						<select name="redis_conn">
							<option value="TCP/IP"', ($redConn==='TCP/IP'?' selected="selected"':''), '>TCP/IP</option>
							<option value="SOCKET"', ($redConn==='SOCKET'?' selected="selected"':''), '>SOCKET</option>
						</select>
					</td>
				</tr>
				<td class="atec-left"><label for="redis_host">', esc_attr__('Host or UNIX path','atec-cache-info'), '</label><br>
					<input size="15" type="text" placeholder="localhost" name="redis_host" value="', esc_attr($redHost), '"><br><br>
				</td>
				<td class="atec-left"><label for="redis_port">', esc_attr__('Port','atec-cache-info'), '</label><br>
					<input size="3" type="text" placeholder="6379" name="redis_port" value="', esc_attr($redPort), '"><br>
					<span class="atec-fs-8">(TCP/IP only)</small>
				</td>
				<td class="atec-left"><label for="redis_pwd">', esc_attr__('Password','atec-cache-info'), '</label><br>
					<input size="6" type="text" placeholder="Password" name="redis_pwd" value="', esc_attr($redPwd), '"><br><br>
				</td>
			</tr>
			<tr>
				<td colspan="3"><input class="button button-primary"  type="submit" value="', esc_attr__('Save','atec-cache-info'), '"></td>
			</tr>
			</table>
		</form>';
	}

	if (is_object($redis) && !empty($redis))
	{
		try
		{
			$server		= $redis->info('server');
			$stats 		= $redis->info('stats');
			$memory 	= $redis->info('memory');

			$total=$stats['keyspace_hits']+$stats['keyspace_misses']+0.001;
			$hits=$stats['keyspace_hits']*100/$total;
			$misses=$stats['keyspace_misses']*100/$total;

			echo'
			<table class="atec-table atec-table-tiny atec-table-td-first">
			<tbody>
				<tr><td>Version:</td><td>', esc_attr($server['redis_version']), '</td><td></td></tr>
				<tr><td>', esc_attr__('Connection','atec-cache-info'), ':</td><td>', esc_textarea($redConn), '</td><td></td></tr>
				<tr><td>', esc_attr__('Host','atec-cache-info'), ':</td><td>', esc_textarea($redHost), '</td><td></td></tr>';
				if ($redConn==='TCP/IP') echo '<tr><td>', esc_attr__('Port','atec-cache-info'), ':</td><td>', esc_attr($redPort), '</td><td></td></tr>';
				if ($redPwd!=='') echo '<tr><td>', esc_attr__('Password','atec-cache-info'), ':</td><td>', esc_textarea($redPwd), '</td><td></td></tr>';
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
		catch (RedisException $e) { atec_error_msg('Redis: '.rtrim($e->getMessage(),'.')); }
	}
	else atec_reg_inline_script('redis_flush', 'jQuery("#Redis_flush").hide();', true);
}
else atec_error_msg('Redis: '.esc_attr__('class is NOT available','atec-cache-info'));
	
}}
?>