<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_APCu_info { function __construct($wpc_tools) {	
	
$apcu_cache=apcu_cache_info(true);
if ($apcu_cache)
{
	$apcu_mem	= apcu_sma_info();
	$total		= $apcu_cache['num_hits']+$apcu_cache['num_misses']+0.001;
	$hits		= $apcu_cache['num_hits']*100/$total;
	$misses		= $apcu_cache['num_misses']*100/$total;
	$percent	= $apcu_cache['mem_size']*100/($apcu_mem['num_seg']*$apcu_mem['seg_size']);
	
	echo'
	<table class="atec-table atec-table-tiny atec-table-td-first">
	<tbody>
		<tr><td>'.esc_attr__('Version','atec-cache-info').':</td><td>',esc_attr(phpversion('apcu')),'</td></tr>
		<tr><td>'.esc_attr__('Type','atec-cache-info').':</td><td>',esc_attr($apcu_cache['memory_type']),'</td></tr>
		<tr><td>'.esc_attr__('Memory','atec-cache-info').':</td><td>',esc_attr(size_format($apcu_mem['num_seg']*$apcu_mem['seg_size'])),'</td></tr>';
		if ($percent>0)
		{
			echo '
			<tr><td>'.esc_attr__('Used','atec-cache-info').':</td><td>',esc_attr(size_format($apcu_cache['mem_size'])),' <font color="#ff7721">',esc_attr(sprintf(" (%.1f%%)",$percent)),'</font></td></tr>
			<tr><td>'.esc_attr__('Items','atec-cache-info').':</td><td>',esc_attr(number_format($apcu_cache['num_entries'])),'</td></tr>
			<tr><td>'.esc_attr__('Hits','atec-cache-info').':</td><td>',esc_attr(number_format($apcu_cache['num_hits']).sprintf(" (%.1f%%)",$hits)),'</td></tr>
			<tr><td>'.esc_attr__('Misses','atec-cache-info').':</td><td>',esc_attr(number_format($apcu_cache['num_misses']).sprintf(" (%.1f%%)",$misses)),'</td></tr>';
		}
	echo '
	</tbody>
	</table>';	

	$wpc_tools->usage($percent);	
	$wpc_tools->hitrate($hits,$misses);

	if ($percent>90) $wpc_tools->error('', __('APCu usage is beyond 90%. Please consider increasing „apc.shm_size“ option','atec-cache-info'));
	elseif ($percent===0)
	{
		$wpc_tools->p(__('Not in use','atec-cache-info'));
		atec_reg_inline_script('APCu_flush', 'jQuery("#APCu_flush").hide();',true);
	}
	
	$testKey='atec_apcu_test_key';
	apcu_add($testKey,'hello');	
	$success=apcu_fetch($testKey)=='hello';
	atec_badge('APCu '.__('is writeable','atec-cache-info'),'Writing to cache failed',$success);
	if ($success) apcu_delete($testKey);
}
else 
{ 
	$wpc_tools->error('APCu',__('cache data could NOT be retrieved','atec-cache-info')); 
}

}}
?>