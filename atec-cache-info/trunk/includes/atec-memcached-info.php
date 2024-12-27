	<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_memcached_info { function __construct($url,$nonce,$wpc_tools,$memSettings) {	

$memUnix = $memSettings['unix']??'';
if ($memUnix!=='') { $memHost=$memUnix; $memPort=0; }
else { $memHost=$memSettings['host']??''; $memPort=$memSettings['port']??0; }

$m = new Memcached(); 
if ($memHost!=='' && $memPort!=='') { $m->addServer($memHost, $memPort); $mem=$m->getStats(); }
else $mem=false;
if ($mem)
{
	$mem		= $mem[$memHost.':'.$memPort];
	$total		= $mem['get_hits']+$mem['get_misses']+0.001;
	$hits			= $mem['get_hits']*100/$total;
	$misses	= $mem['get_misses']*100/$total;
	
	if (isset($mem['bytes'])) $percent=$mem['bytes']*100/($mem['limit_maxbytes']);
	echo'
	<table class="atec-table atec-table-tiny atec-table-td-first">
		<tbody>
			<tr><td>Version:</td><td>', esc_attr($mem['version']), '</td><td></td></tr>
			<tr><td>', esc_attr($memUnix===''?'Host':'Socket'), ':</td><td>', esc_html($memUnix===''?$memHost:$memUnix), '</td><td></td></tr>
			<tr><td>', esc_attr__('Port','atec-cache-info'), ':</td><td>', esc_html($memPort), '</td><td></td></tr>';
			atec_empty_tr();
			if (isset($mem['limit_maxbytes'])) 	echo '<tr><td>', esc_attr__('Memory','atec-cache-info'), ':</td><td>', esc_attr(size_format($mem['limit_maxbytes'])), '</td><td></td></tr>';
			if (isset($mem['bytes'])) echo '<tr><td>', esc_attr__('Used','atec-cache-info'), ':</td>
				<td>', esc_attr(size_format($mem['bytes'])), '</td>
				<td><small>', esc_attr(sprintf("%.1f%%",$percent)), '</small></td></tr>';
			if (isset($mem['total_items'])) echo '<tr><td>', esc_attr__('Items','atec-cache-info'), ':</td><td>', esc_attr(number_format($mem['total_items'])), '</td><td></td></tr>';
			echo '
			<tr><td>', esc_attr(__('Hits','atec-cache-info')), ':</td>
				<td>', esc_attr(number_format($mem['get_hits'])), '</td><td><small>', esc_attr(sprintf("%.1f%%",$hits)), '</small></td></tr>
			<tr><td>', esc_attr(__('Misses','atec-cache-info')), ':</td>
				<td>', esc_attr(number_format($mem['get_misses'])), '</td><td><small>', esc_attr(sprintf("%.1f%%",$misses)), '</small></td></tr>
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
	atec_reg_inline_script('memcached_flush', 'jQuery("#Memcached_flush").hide();', true);
	
	echo 
	'<p>
		<span class="atec-red">', esc_attr__('Connection failed','atec-cache-info'), '</span>.<br>', 
		esc_attr__('Please define host:port OR unix path.','atec-cache-info'),
		'<div style="margin-top:-15px;"><small>', esc_attr__('Unix path is dominant.','atec-cache-info'), '</small></div>
	</p>
	<form class="atec-border-tiny" method="post" action="'.esc_url($url).'&action=saveMem&_wpnonce='.esc_attr($nonce).'">
		<table>
		<tr>
			<td class="atec-left"><label for="memcached_host">', esc_attr__('Host ','atec-cache-info'), '</lable><br>
				<input size="15" type="text" placeholder="localhost" name="memcached_host" value="', esc_attr($memHost), '">
			</td>
			<td class="atec-left"><label for="memcached_port">', esc_attr__('Port','atec-cache-info'), '</lable><br>
				<input size="3" type="text" placeholder="11211" name="memcached_port" value="', esc_attr($memPort), '">
			</td>
		</tr>
		<tr>
			<td colspan="2"><label for="memcached_unix">', esc_attr__('Unix socket','atec-cache-info'), '</lable><br>
				<input size="24" type="text" placeholder="/home/memcached.socket" name="memcached_unix" value="', esc_textarea($memUnix), '">
			</td>
		</tr>
		<tr>
			<td colspan="3"><br><input class="button button-primary"  type="submit" value="', esc_attr__('Save','atec-cache-info'), '"></td>
		</tr>
		</table>
	</form>';
}

}}
?>