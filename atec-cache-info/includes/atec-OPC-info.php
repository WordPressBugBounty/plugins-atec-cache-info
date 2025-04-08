<?php
if (!defined('ABSPATH')) { exit; }

class ATEC_OPcache_info { 
	
private static function increaseInSteps($value, $factor, $step = 128) { return ceil(($value * $factor) / $step) * $step; }

function __construct($op_conf,$op_status,$opcache_file_only) {	
	
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
			$memConsumption = $op_conf['directives']['opcache.memory_consumption'];
			echo '
			<table class="atec-table atec-table-tiny atec-table-td-first">
				<tbody>
				<tr><td>',esc_attr__('Memory','atec-cache-info'),':</td><td>',esc_attr(size_format($memConsumption)),'</td><td></td></tr>';
				if ($opStats)
				{	
					$hits				= $op_status['opcache_statistics']['hits'];
					$misses		= $op_status['opcache_statistics']['misses'];
					
					$totalStats 			= $hits+$misses+0.0001;
					$hitsPercent			= $hits/$totalStats*100;
					$missesPercent		= $misses/$totalStats*100;
					$used_memory		= $op_status['memory_usage']['used_memory'];
					$free_memory		= $op_status['memory_usage']['free_memory'];
					$wasted_memory	= $op_status['memory_usage']['wasted_memory'];
					
					$totalMem	= $used_memory+$free_memory;
					$percent		= $used_memory/$totalMem*100;
					
					$megaByte 	= 1048576;
					$memConsumptionroundInMB = $memConsumption/$megaByte;
					$recMemory = $memConsumptionroundInMB;
					if ($percent>75) $recMemory = self::increaseInSteps($recMemory,1.50);
					elseif ($percent>50) $recMemory = self::increaseInSteps($recMemory,1.25);
					$OPC_recommended = ['memory'=>$recMemory, 'strings'=>8, 'files'=>10000];

					echo '
					<tr><td>&nbsp;&nbsp;',esc_attr__('Used','atec-cache-info'),':</td><td>',esc_attr(size_format($used_memory)), '</td>
						<td><small>', esc_attr(sprintf("%.1f%%",$percent)), '</small></td></tr>
					<tr><td>&nbsp;&nbsp;',esc_attr__('Free','atec-cache-info'),':</td><td>',esc_attr(size_format($free_memory)),'</td><td></td></tr>
					<tr><td>&nbsp;&nbsp;',esc_attr__('Total','atec-cache-info'),':</td><td style="border-top: solid 1px #666; font-weight: 500;">',esc_attr(size_format($totalMem)), '</small></td><td></td></tr>
					<tr><td>&nbsp;&nbsp;',esc_attr__('Wasted','atec-cache-info'),':</td><td>',esc_attr(size_format($wasted_memory)),'</td>
						<td><small>', esc_attr(sprintf("%.1f%%",$op_status['memory_usage']['current_wasted_percentage'])), '</small></td></tr>';
					atec_empty_TR();
					echo '
					<tr><td>&nbsp;&nbsp;',esc_attr__('Hits','atec-cache-info'),':</td><td>',esc_html(number_format($hits)), '</td>
						<td><small>', esc_attr(sprintf("%.1f%%",$hitsPercent)), '</small></td></tr>
					<tr><td>&nbsp;&nbsp;',esc_attr__('Misses','atec-cache-info'),':</td><td>',esc_html(number_format($misses)), '</td>
						<td><small>', esc_attr(sprintf("%.1f%%",$missesPercent)), '</small></td></tr>';
				}
				echo '
				</tbody>
			</table>';
		
			if ($opStats)
			{
				if ($recMemory!==$memConsumptionroundInMB) atec_warning_msg('Try raising the limit to '.$recMemory.' MB');
				ATEC_wpc_tools::usage($percent);	
				ATEC_wpc_tools::hitrate($hitsPercent,$missesPercent);
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
			
			$stringBuffer = $op_conf['directives']['opcache.interned_strings_buffer'];
			echo '
			<table class="atec-table atec-table-tiny atec-table-td-first">
				<tbody>
					<tr><td>',esc_attr__('Strings','atec-cache-info'),':</td>
						<td>',esc_attr($stringBuffer),' MB</td><td></td></tr>';
					if ($opStats)
					{
						$percentStrings 	= $op_status['interned_strings_usage']['used_memory']*100/$op_status['interned_strings_usage']['buffer_size'];
						$recStrings 			= $stringBuffer;
						if ($percentStrings>75) $recStrings=$this->increaseInSteps($recStrings,1.50,8);
						elseif ($percentStrings>50) $recStrings=$this->increaseInSteps($recStrings,1.25,8);							
						$OPC_recommended['strings']=$recStrings;

						echo '
						<tr>
							<td>&nbsp;&nbsp;',esc_attr__('Used','atec-cache-info'),':</td>
							<td>',esc_attr(size_format($op_status['interned_strings_usage']['used_memory'])), '</td>
							<td><small>', esc_attr(sprintf("%.1f%%",$percentStrings)), '</small></td></tr>';
					}
				echo '
				</tbody>
			</table>';
						
			if (isset($percentStrings)) 
			{
				if ($recStrings!==$stringBuffer) atec_warning_msg('Try raising the limit to '.$recStrings.' MB');
				ATEC_wpc_tools::usage($percentStrings);
			}
			
			atec_help('OPcache','OPcache '.__('explained','atec-cache-info'));
			echo '<div id="OPcache_help" class="atec-help atec-dn">', esc_attr__('OPcache improves PHP performance by storing precompiled script bytecode in shared memory, thereby removing the need for PHP to load and parse scripts on each request','atec-cache-info'), '.</div>';
			
			$save_comments = filter_var($op_conf['directives']['opcache.save_comments']??0,258);
			$validate_timestamps = filter_var($op_conf['directives']['opcache.validate_timestamps']??0,258);
			$enable_file_override = filter_var($op_conf['directives']['opcache.enable_file_override']??0,258);
			$consistency_checks = filter_var($op_conf['directives']['opcache.consistency_checks']??0,258);
			
			echo '
			</div>
			<div class="atec-border-white">
			<h4>OPcache ', esc_attr__('Details','atec-cache-info'), '</h4><hr>
			<table class="atec-table atec-table-tiny atec-table-td-first">
				<tbody>
					<tr><td>',esc_attr__('Version','atec-cache-info').':</td><td>',esc_attr($op_conf['version']['version']??''), '</td></tr>
					<tr><td>',esc_attr__('Revalidate freq.','atec-cache-info').':</td><td>',esc_attr($op_conf['directives']['opcache.revalidate_freq']??0),' s</td></tr>
					<tr><td>',esc_attr__('Validate TS.','atec-cache-info').':</td><td>',esc_attr($validate_timestamps?'On':'Off'),'</td></tr>

					<tr><td>',esc_attr__('Override','atec-cache-info').':</td>
					<td class="', $enable_file_override?'atec-green':'atec-red', '">',esc_attr($enable_file_override?'On':'Off'),'</td></tr>
					
					<tr><td>',esc_attr__('Comments','atec-cache-info').':</td>
					<td class="', (!$save_comments?'atec-green':'atec-red'), '">',esc_attr($save_comments?'On':'Off'),'</td></tr>
					
					<tr><td>',esc_attr__('Max waste','atec-cache-info').':</td><td>',esc_attr($op_conf['directives']['opcache.max_wasted_percentage']??''),'</td></tr>
					
					<tr><td>',esc_attr__('Consistency','atec-cache-info').':</td>
					<td class="', ($consistency_checks?'atec-red':'atec-green'), '">',esc_attr($consistency_checks?'On':'Off'),'</td></tr>
					
				</tbody>
			</table>';
			
			$max_accelerated_files = $op_conf['directives']['opcache.max_accelerated_files']??0;
			echo
			'<table class="atec-table atec-table-tiny atec-table-td-first">
				<tbody>
					<tr><td>',esc_attr__('Max acc. files','atec-cache-info'),':</td><td>',esc_attr($max_accelerated_files),'</td></tr>';
					if ($opStats && $max_accelerated_files!==0)
					{
						$numScripts 	= $op_status['opcache_statistics']['num_cached_scripts']??0;
						$numKeys 		= $op_status['opcache_statistics']['num_cached_keys']??0;
						$maxReal			= $op_status['opcache_statistics']['max_cached_keys']??0;

						$percentFiles	 	= ($numScripts+$numKeys)*100/$maxReal;
						$recFiles	 			= $max_accelerated_files;

						if ($percentFiles>75) $recFiles=$this->increaseInSteps($max_accelerated_files, 1.5, 1000);
						elseif ($percentFiles>50) $recFiles=$this->increaseInSteps($max_accelerated_files, 1.25, 1000);							
						$OPC_recommended['files']=$recFiles;

						echo '
						<tr><td>&nbsp;&nbsp;',esc_attr__('Max real','atec-cache-info'),':</td><td>',esc_html(number_format($maxReal)),'</td></tr>';
						atec_empty_tr();
						echo '
						<tr><td>&nbsp;&nbsp;',esc_attr__('Scripts cached','atec-cache-info'),':</td><td>',esc_html(number_format($numScripts)),'</td></tr>
						<tr><td>&nbsp;&nbsp;',esc_attr__('Keys cached','atec-cache-info'),':</td><td>',esc_html(number_format($numKeys)),'</td></tr>';
					}
				echo '
				</tbody>
			</table>';
			
			if (isset($percentFiles)) 
			{
				if ($recFiles!==$max_accelerated_files) atec_warning_msg('Try raising the limit to '.$recFiles);
				ATEC_wpc_tools::usage($percentFiles);	
			}
		}
	}
	
	require('atec-OPC-help.php');
}}
?>