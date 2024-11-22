<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_OPcache_info { function __construct($op_conf,$op_status,$opcache_file_only,$wpc_tools) {	
	
	if ($opcache_file_only)
	{
		echo'
		<table class="atec-table atec-table-tiny atec-table-td-first">
		<tbody>
			<tr><td>Mode:</td><td>File only</td></tr>
			<tr><td>Max files:</td><td>',esc_html(ini_get('opcache.max_accelerated_files')),'</td></tr>	
		</tbody>
		</table>';						
	}
	else
	{
		$opStats=isset($op_status['opcache_statistics']); $percent=0;
		if ($op_conf)
		{
			echo '
			<table class="atec-table atec-table-tiny atec-table-td-first">
				<tbody>
				<tr><td>',esc_attr__('Memory','atec-cache-info'),':</td><td>',esc_attr(size_format($op_conf['directives']['opcache.memory_consumption'])),'</td><td></td></tr>';
				if ($opStats)
				{
					$totalStats 	= $op_status['opcache_statistics']['hits']+$op_status['opcache_statistics']['misses']+0.001;
					$hits				= $op_status['opcache_statistics']['hits']/$totalStats*100;
					$misses		= $op_status['opcache_statistics']['misses']/$totalStats*100;
					
					$totalMem	= $op_status['memory_usage']['used_memory']+$op_status['memory_usage']['free_memory'];
					$percent		= $op_status['memory_usage']['used_memory']/$totalMem*100;

					echo '
					<tr><td>',esc_attr__('&nbsp;&nbsp;Real','atec-cache-info'),':</td>
						<td>',esc_attr(size_format($totalMem)), '</small></td><td></td></tr>
					<tr><td>',esc_attr__('&nbsp;&nbsp;Used','atec-cache-info'),':</td>
						<td>',esc_attr(size_format($op_status['memory_usage']['used_memory'])), '</td>
						<td><small>', esc_attr(sprintf("%.1f%%",$percent)), '</small></td></tr>
					<tr><td>',esc_attr__('&nbsp;&nbsp;Free','atec-cache-info'),':</td>
						<td>',esc_attr(size_format($op_status['memory_usage']['free_memory'])),'</td><td></td></tr>
					<tr><td>',esc_attr__('&nbsp;&nbsp;Wasted','atec-cache-info'),':</td>
						<td>',esc_attr(size_format($op_status['memory_usage']['wasted_memory'])),'</td><td></td></tr>';
					atec_empty_TR();
					echo '
					<tr><td>',esc_attr__('&nbsp;&nbsp;Hits','atec-cache-info'),':</td>
						<td>',esc_attr(number_format($op_status['opcache_statistics']['hits'])), '</td>
						<td><small>', esc_attr(sprintf("%.1f%%",$hits)), '</small></td></tr>
					<tr><td>',esc_attr__('&nbsp;&nbsp;Misses','atec-cache-info'),':</td>
						<td>',esc_attr(number_format($op_status['opcache_statistics']['misses'])), '</td>
						<td><small>', esc_attr(sprintf("%.1f%%",$misses)), '</small></td></tr>';
				}
				echo '
				</tbody>
			</table>';
		
			if ($opStats)
			{
				$wpc_tools->usage($percent);	
				$wpc_tools->hitrate($hits,$misses);
				if ($percent>90) $wpc_tools->error('', esc_attr__('OPcache usage is beyond 90%. Please consider increasing the „memory_consumption“ option','atec-cache-info'));
			}
			else
			{ 
				echo '
				<p>OPcache ', esc_attr__('statistics is not available','atec-cache-info'), ',<br>';
					$disable_functions=str_contains(strtolower(ini_get('disabled_function')),'opcache_get_status');
					echo $disable_functions?esc_attr__('"opcache_get_status" is a disabled function.','atec-cache-info'):esc_attr__('Maybe opcache_get_status is a disabled_function','atec-cache-info');
				echo '
				</p>';
			}
			
			echo '
			<table class="atec-table atec-table-tiny atec-table-td-first">
				<tbody>
					<tr><td>',esc_attr__('Strings','atec-cache-info'),':</td>
						<td>',esc_attr($op_conf['directives']['opcache.interned_strings_buffer']),' MB</td><td></td></tr>';
					if ($opStats)
					{
						$percentStrings = $op_status['interned_strings_usage']['used_memory']*100/$op_status['interned_strings_usage']['buffer_size'];
						echo '
						<tr><td>',esc_attr__('&nbsp;&nbsp;Used','atec-cache-info'),':</td>
							<td>',esc_attr(size_format($op_status['interned_strings_usage']['used_memory'])), '</td>
							<td><small>', esc_attr(sprintf("%.1f%%",$percentStrings)), '</small></td></tr>';
					}
				echo '
				</tbody>
			</table>';
			
			atec_help('OPcache','OPcache explained');
			echo '<div id="OPcache_help" class="atec-help atec-dn">OPcache improves PHP performance by storing precompiled script bytecode in shared memory, thereby removing the need for PHP to load and parse scripts on each request.</div>';
			
			echo '
			</div>
			<div class="atec-border-white">
			<h4>OPcache ', esc_attr__('Details','atec-cache-info'), '</h4><hr>
			<table class="atec-table atec-table-tiny atec-table-td-first">
				<tbody>
					<tr><td>',esc_attr__('Version','atec-cache-info').':</td><td>',esc_attr($op_conf['version']['version']), '</td></tr>
					<tr><td>',esc_attr__('Revalidate freq.','atec-cache-info').':</td><td>',esc_attr($op_conf['directives']['opcache.revalidate_freq']),' s</td></tr>
					<tr><td>',esc_attr__('Validate TS.','atec-cache-info').':</td><td>',esc_attr($op_conf['directives']['opcache.validate_timestamps']?'On':'Off'),'</td></tr>

					<tr><td>',esc_attr__('Override','atec-cache-info').':</td>
					<td class="', $op_conf['directives']['opcache.enable_file_override']?'atec-green':'atec-red', '">',esc_attr($op_conf['directives']['opcache.enable_file_override']?'On':'Off'),'</td></tr>
					
					<tr><td>',esc_attr__('Comments','atec-cache-info').':</td>
					<td class="', !$op_conf['directives']['opcache.save_comments']?'atec-green':'atec-red', '">',esc_attr($op_conf['directives']['opcache.save_comments']?'On':'Off'),'</td></tr>
					
					<tr><td>',esc_attr__('Max waste','atec-cache-info').':</td><td>',esc_attr($op_conf['directives']['opcache.max_wasted_percentage']),'</td></tr>
					
					<tr><td>',esc_attr__('Consistency','atec-cache-info').':</td>
					<td class="', $op_conf['directives']['opcache.consistency_checks']?'atec-red':'atec-green', '">',esc_attr($op_conf['directives']['opcache.consistency_checks']?'On':'Off'),'</td></tr>
					
				</tbody>
			</table>
			<table class="atec-table atec-table-tiny atec-table-td-first">
				<tbody>
					<tr><td>',esc_attr__('Max acc. files','atec-cache-info').':</td><td>',esc_attr($op_conf['directives']['opcache.max_accelerated_files']),'</td></tr>';
					if ($opStats)
					{
						echo '
						<tr><td>',esc_attr__('&nbsp;&nbsp;Max cached','atec-cache-info'),':</td><td>',esc_attr(number_format($op_status['opcache_statistics']['max_cached_keys'])),'</td></tr>
						<tr><td>',esc_attr__('&nbsp;&nbsp;Scripts cached','atec-cache-info'),':</td><td>',esc_attr(number_format($op_status['opcache_statistics']['num_cached_scripts'])),'</td></tr>
						<tr><td>',esc_attr__('&nbsp;&nbsp;Keys cached','atec-cache-info'),':</td><td>',esc_attr(number_format($op_status['opcache_statistics']['num_cached_keys'])),'</td></tr>
						<tr><td>',esc_attr__('&nbsp;&nbsp;Total cached','atec-cache-info'),':</td><td>',esc_attr(number_format($op_status['opcache_statistics']['num_cached_scripts']+$op_status['opcache_statistics']['num_cached_keys'])),'</td></tr>';
					}
				echo '
				</tbody>
			</table>';
		}

	}	
	
}}
?>