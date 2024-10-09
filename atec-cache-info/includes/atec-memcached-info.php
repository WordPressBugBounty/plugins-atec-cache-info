<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_memcached_info { function __construct($tools) {	

$m = new Memcached();
$m->addServer('localhost', 11211);
$mem=$m->getStats();
if ($mem)
{
	$mem	= $mem['localhost:11211'];
	$total	= $mem['get_hits']+$mem['get_misses']+0.001;
	$hits	= $mem['get_hits']*100/$total;
	$misses	= $mem['get_misses']*100/$total;
	
	if (isset($mem['bytes'])) $percent=$mem['bytes']*100/($mem['limit_maxbytes']);
	echo'
	<table class="atec-table atec-table-tiny atec-table-td-first">
		<tbody>
			<tr><td>Version:</td><td>',esc_attr($mem['version']),'</td></tr>
			<tr><td>Connection:</td><td>localhost:11211</td></tr>';
			if (isset($mem['limit_maxbytes'])) 	echo '<tr><td>'.esc_attr__('Memory','atec_cache').':</td><td>',esc_attr(size_format($mem['limit_maxbytes'])),'</td></tr>';
			if (isset($mem['bytes'])) echo '<tr><td>'.esc_attr__('Used','atec_cache').':</td><td>',esc_attr(size_format($mem['bytes']),' '.sprintf(" (%.1f%%)",$percent)),'</td></tr>';
			if (isset($mem['total_items'])) echo '<tr><td>'.esc_attr__('Items','atec_cache').':</td><td>',esc_attr(number_format($mem['total_items'])),'</td></tr>';
			echo '
			<tr><td>'.esc_attr(__('Hits','atec_cache')).':</td><td>',esc_attr(number_format($mem['get_hits']).sprintf(" (%.1f%%)",$hits)),'</td></tr>
			<tr><td>'.esc_attr(__('Misses','atec_cache')).':</td><td>',esc_attr(number_format($mem['get_misses']).sprintf(" (%.1f%%)",$misses)),'</td></tr>
		</tbody>
	</table>';
	
	$tools->usage($percent);	
	$tools->hitrate($hits,$misses);

	$atec_wpci_key='atec_wpci_key';
	$m->set($atec_wpci_key,'hello');
	$success=$m->get($atec_wpci_key)=='hello';
	atec_badge('Memcached '.esc_attr__('is writeable','atec_cache'),'Writing to cache failed',$success);
	if ($success) $m->delete($atec_wpci_key);
}
else 
{
	$tools->p('Memcached '.esc_attr__('status is not available','atec_wpci'));
	atec_reg_inline_script('memcached_flush', 'jQuery("#Memcached_flush").hide();', true);
}

}}
?>