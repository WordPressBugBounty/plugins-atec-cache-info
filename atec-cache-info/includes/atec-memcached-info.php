<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_memcached_info { function __construct($wpc_tools) {	

$host='localhost';
$port=11211;
$m = new Memcached();
$m->addServer($host, $port);
$mem=$m->getStats();
if ($mem)
{
	$mem		= $mem[$host.':'.$port];
	$total		= $mem['get_hits']+$mem['get_misses']+0.001;
	$hits			= $mem['get_hits']*100/$total;
	$misses	= $mem['get_misses']*100/$total;
	
	if (isset($mem['bytes'])) $percent=$mem['bytes']*100/($mem['limit_maxbytes']);
	echo'
	<table class="atec-table atec-table-tiny atec-table-td-first">
		<tbody>
			<tr><td>Version:</td><td>', esc_attr($mem['version']), '</td><td></td></tr>
			<tr><td>', esc_attr__('Hort','atec-cache-info'), ':</td><td>', esc_html($host), '</td><td></td></tr>
			<tr><td>', esc_attr__('Port','atec-cache-info'), ':</td><td>', esc_html($port), '</td><td></td></tr>';
			atec_empty_tr();
			if (isset($mem['limit_maxbytes'])) 	echo '<tr><td>', esc_attr__('Memory','atec-cache-info'), ':</td><td>', esc_attr(size_format($mem['limit_maxbytes'])), '</td><td></td></tr>';
			if (isset($mem['bytes'])) echo '<tr><td>', esc_attr__('Used','atec-cache-info'), ':</td>
				<td>', esc_attr(size_format($mem['bytes'])), '</td>
				<td><small>', sprintf("%.1f%%",$percent), '</small></td></tr>';
			if (isset($mem['total_items'])) echo '<tr><td>', esc_attr__('Items','atec-cache-info'), ':</td><td>', esc_attr(number_format($mem['total_items'])), '</td><td></td></tr>';
			echo '
			<tr><td>', esc_attr(__('Hits','atec-cache-info')), ':</td>
				<td>', esc_attr(number_format($mem['get_hits'])), '</td><td><small>', sprintf("%.1f%%",$hits), '</small></td></tr>
			<tr><td>', esc_attr(__('Misses','atec-cache-info')), ':</td>
				<td>', esc_attr(number_format($mem['get_misses'])), '</td><td><small>', sprintf("%.1f%%",$misses), '</small></td></tr>
		</tbody>
	</table>';
	
	$wpc_tools->usage($percent);	
	$wpc_tools->hitrate($hits,$misses);

	$atec_wpci_key='atec_wpci_key';
	$m->set($atec_wpci_key,'hello');
	$success=$m->get($atec_wpci_key)=='hello';
	atec_badge('Memcached '.__('is writeable','atec-cache-info'),'Writing to cache failed',$success);
	if ($success) $m->delete($atec_wpci_key);
}
else 
{
	$wpc_tools->p('Memcached '.__('status is not available','atec-cache-info'));
	atec_reg_inline_script('memcached_flush', 'jQuery("#Memcached_flush").hide();', true);
}

}}
?>