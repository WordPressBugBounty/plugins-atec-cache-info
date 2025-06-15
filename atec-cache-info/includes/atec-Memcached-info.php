	<?php
defined('ABSPATH') || exit;

use ATEC\TOOLS;
use ATEC\WPC;

final class ATEC_Memcached_Info {

private static function test_memcache_writable($m)
{
	$atec_wpci_key= 'atec_wpci_key';
	$m->set($atec_wpci_key, 'hello');
	$success= $m->get($atec_wpci_key)== 'hello';
	TOOLS::badge($success, 'Memcached '.__('is writeable', 'atec-cache-info'), 'Writing to cache failed');
	if ($success) $m->delete($atec_wpci_key);
}

public static function init($una, $settings)
{
	$allow_settings = $una->slug === 'wpci';

	$result = WPC::memcached_connect($settings);

	$m 			= $result['m'];
	$memConn 	= $result['conn'];
	$memHost 	= $result['host'];
	$memPort 	= $result['port'];

	if ($m && !$m->getVersion()) $m = false;

	if ($m)
	{
		$mem		= $m?$m->getStats():false;
		$mem		= $mem[$memHost.':'.$memPort] ?? false;
		if ($mem)
		{
			$total		= $mem['get_hits']+$mem['get_misses']+0.0000001;
			$hits			= $mem['get_hits']*100/$total;
			$misses	= $mem['get_misses']*100/$total;
		}
	
		if (isset($mem['bytes'])) $percent= $mem['bytes']*100/($mem['limit_maxbytes']);
		else $percent = false;
		
		$available_serializers = [];
		if (defined('Memcached::SERIALIZER_PHP') && function_exists('igbinary_serialize')) $available_serializers[]= 'PHP';
		if (defined('Memcached::SERIALIZER_JSON') && function_exists('igbinary_serialize')) $available_serializers[]= 'JSON';
		if (defined('Memcached::SERIALIZER_IGBINARY') && function_exists('igbinary_serialize')) $available_serializers[]= 'IGBINARY';
		if (defined('Memcached::SERIALIZER_MSGPACK') && function_exists('msgpack_serialize')) $available_serializers[]= 'MSGPACK';

		TOOLS::table_header([], '', 'bold');
			if (isset($mem['version'])) TOOLS::table_tr(['Version', '2@'.$mem['version']], '', 'bold');
			TOOLS::table_tr([__('Connection', 'atec-cache-info'), '2@'.$memConn]);
			TOOLS::table_tr([__('Host', 'atec-cache-info'), '2@'.$memHost]);
			TOOLS::table_tr([__('Port', 'atec-cache-info'), '2@'.$memPort]);
			if (!empty($available_serializers)) TOOLS::table_tr([__('Serializers', 'atec-cache-info'), '2@<small>'.implode(', ', $available_serializers).'</small>']);
		TOOLS::table_footer();
			
		if ($mem)
		{
			TOOLS::table_header([], '', 'bold');
				TOOLS::table_tr([__('Memory', 'atec-cache-info'), TOOLS::size_format($mem['limit_maxbytes']), '']);
				TOOLS::table_tr([__('Used', 'atec-cache-info'), TOOLS::size_format($mem['bytes']), '<small>'.TOOLS::percent_format($percent).'</small>']);
				TOOLS::table_tr([__('Items', 'atec-cache-info'), number_format($mem['total_items']), '']);
				TOOLS::table_tr([__('Hits', 'atec-cache-info'), number_format($mem['get_hits']), '<small>'.TOOLS::percent_format($hits).'</small>']);
				TOOLS::table_tr([__('Memory', 'atec-cache-info'), TOOLS::size_format($mem['limit_maxbytes']), '']);
				TOOLS::table_tr([__('Misses', 'atec-cache-info'), number_format($mem['get_misses']), '<small>'.TOOLS::percent_format($misses).'</small>']);
			TOOLS::table_footer();
		}
		
		if ($percent)
		{
			WPC::usage($percent);
			WPC::hitrate($hits, $misses);
		}
		self::test_memcache_writable($m);
	
		if ($allow_settings)
		{
			echo	
			'<button class="', ($mem ? '' : 'atec-dn '),'button button-secondary atec-btn-small atec-mt-10" onclick="jQuery(\'#memcached_settings\').removeClass(\'atec-dn\'); jQuery(this).remove();">Settings</button>';
			
			echo
			'<div id="memcached_settings" ', ($mem ? 'class="atec-dn"' : ''), '>';
			
				TOOLS::form_header($una, 'saveMem', 'Cache', '', 'atec-border-tiny');
				
					echo
					'<table>
						<tr>
							<td colspan="3"><label for="memcached_conn">', esc_attr__('Connection', 'atec-cache-info'), '</label><br>
								<select name="memcached_conn">
									<option value="TCP/IP"', ($memConn=== 'TCP/IP'?' selected="selected"' : ''), '>TCP/IP</option>
									<option value="SOCKET"', ($memConn=== 'SOCKET'?' selected="selected"' : ''), '>SOCKET</option>
								</select>
							</td>
						</tr>
						
						<tr>
							<td class="atec-left"><label for="memcached_host">', esc_attr__('Host', 'atec-cache-info'), '</label><br>
								<input size="15" type="text" placeholder="localhost" name="memcached_host" value="', esc_attr($memHost), '"><br><br>
							</td>
							<td class="atec-left"><label for="memcached_port">', esc_attr__('Port', 'atec-cache-info'), '</label><br>
								<input size="3" type="text" placeholder="11211" name="memcached_port" value="', esc_attr($memPort), '"><br>
								<span class="atec-fs-8">(TCP/IP only)</small>
							</td>
						</tr>
						
						<tr>
							<td colspan="3">'; TOOLS::submit_button('#editor-break '.esc_attr__('Save', 'atec-cache-info'), true); echo '</td>
						</tr>
					</table>';
					
				TOOLS::form_footer();
					
			echo
			'</div>';
		}
	}
	else
	{
		TOOLS::reg_inline_script('wpx_memcached_flush', 'jQuery("#Memcached_flush").hide();', true);
	}

}

}
?>