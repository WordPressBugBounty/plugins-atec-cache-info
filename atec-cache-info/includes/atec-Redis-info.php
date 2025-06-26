<?php
// 1: redis-cli: | 2: auth pwd | 3: CONFIG SET requirepass pwd
defined('ABSPATH') || exit;

use ATEC\TOOLS;
use ATEC\WPC;

final class ATEC_Redis_Info {

private static function test_redis_writable($redis)
{
	$testKey= 'atec_redis_test_key';
	$redis->set($testKey, 'hello');
	$success= $redis->get($testKey)== 'hello';
	TOOLS::badge($success, 'Redis '.'is writeable', 'Writing to cache failed');
	if ($success) $redis->del($testKey);
}

public static function init($una, $settings)
{
	$allow_settings = $una->slug === 'wpci';

	if (class_exists('Redis'))
	{
		if (isset($settings['unix']) && $settings['unix']!== '')
		{
			$redHost = $settings['unix']; // backwards compatible
			$redConn = 'SOCKET';
		}
		else
		{
			$redHost = $settings['host']??'';
			$redConn = $settings['conn']??'';
			if ($redConn=== '') $redConn= 'TCP/IP';
		}
		$redPort = $settings['port']??'';
		$redPwd  = $settings['pwd']??'';

		$result = WPC::redis_connect($settings);
		$redis = $result['redis'];

		if (is_object($redis) && !empty($redis))
		{
			try
			{
				$server			= $redis->info('server');
				$stats 			= $redis->info('stats');
				$memory 		= $redis->info('memory');
				$keyCount 		= $redis->dbSize();

				$available_serializers = [];
				if (defined('Redis::SERIALIZER_PHP')) { $available_serializers[] = 'PHP'; }
				if (defined('Redis::SERIALIZER_JSON') && function_exists('json_encode')) { $available_serializers[] = 'JSON'; }
				if (defined('Redis::SERIALIZER_IGBINARY') && function_exists('igbinary_serialize')) { $available_serializers[] = 'IGBINARY'; }
				if (defined('Redis::SERIALIZER_MSGPACK') && function_exists('msgpack_serialize')) { $available_serializers[] = 'MSGPACK'; }

				$total= $stats['keyspace_hits']+$stats['keyspace_misses']+0.0000001;
				$hits= $stats['keyspace_hits']*100/$total;
				$misses= $stats['keyspace_misses']*100/$total;

				TOOLS::table_header([], '', 'summary');
					TOOLS::table_tr(['Version', '2@'.$server['redis_version']]);
					TOOLS::table_tr(['Connection', '2@'.$redConn]);
					TOOLS::table_tr(['Host', '2@'.$redHost]);
					if ($redConn=== 'TCP/IP') TOOLS::table_tr(['Port', '2@'.$redPort]);
					if ($redPwd!== '') TOOLS::table_tr(['Password', '2@'.$redPwd]);
					if (!empty($available_serializers)) TOOLS::table_tr(['Serializers', '2@<small>'.implode(', ', $available_serializers).'</small>']);
				TOOLS::table_footer();

				TOOLS::table_header([], '', 'summary');
					TOOLS::table_tr(['Used', TOOLS::size_format($memory['used_memory']), '']);
					if ($keyCount) TOOLS::table_tr(['Items', number_format($keyCount), '']);
					TOOLS::table_tr(['Hits', number_format($stats['keyspace_hits']), '<small>'.sprintf(" (%.1f%%)", $hits).'</small>']);
					TOOLS::table_tr(['Misses', number_format($stats['keyspace_misses']), '<small>'.sprintf(" (%.1f%%)", $misses).'</small>']);
				TOOLS::table_footer();

				WPC::hitrate($hits, $misses);
				self::test_redis_writable($redis);
				
			}
			catch (RedisException $e) { TOOLS::msg(false, 'Redis: '.rtrim($e->getMessage(), '.')); }
		}
		
		if ($allow_settings)
		{
			echo	
			'<button class="', ($redis ? '' : 'atec-dn '),'button button-secondary atec-btn-small atec-mt-10" onclick="jQuery(\'#redis_settings\').removeClass(\'atec-dn\'); jQuery(this).remove();">Settings</button>';
		
			echo
			'<div id="redis_settings" ', ($redis ? 'class="atec-dn"' : ''), '>';
				TOOLS::form_header($una, 'saveRed', 'Cache', '', 'atec-border-tiny');
					echo
					'<table>
					
						<tr>
							<td colspan="3"><label for="redis_conn">', esc_attr('Connection'), '</label><br>
								<select name="redis_conn">
									<option value="TCP/IP"', ($redConn=== 'TCP/IP'?' selected="selected"' : ''), '>TCP/IP</option>
									<option value="SOCKET"', ($redConn=== 'SOCKET'?' selected="selected"' : ''), '>SOCKET</option>
								</select>
							</td>
						</tr>
						
						<tr>
							<td class="atec-left"><label for="redis_host">', esc_attr('Host or UNIX path'), '</label><br>
								<input size="15" type="text" placeholder="localhost" name="redis_host" value="', esc_attr($redHost), '"><br><br>
							</td>
							<td class="atec-left"><label for="redis_port">', esc_attr('Port'), '</label><br>
								<input size="3" type="text" placeholder="6379" name="redis_port" value="', esc_attr($redPort), '"><br>
								<span class="atec-fs-8">(TCP/IP only)</small>
							</td>
							<td class="atec-left"><label for="redis_pwd">', esc_attr('Password'), '</label><br>
								<input size="6" type="text" placeholder="Password" name="redis_pwd" value="', esc_attr($redPwd), '"><br><br>
							</td>
						</tr>
						
						<tr>
							<td colspan="3">'; TOOLS::submit_button('#editor-break '.esc_attr('Save'), true); echo '</td>
						</tr>
						
					</table>';
				TOOLS::form_footer();
			echo 
			'</div>';
		}
	}
	else 
	{
		TOOLS::reg_inline_script('wpx_redis_flush', 'jQuery("#Redis_flush").hide();', true);
	}

}

}
?>