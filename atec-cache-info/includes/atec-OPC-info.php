<?php
defined('ABSPATH') || exit;


use ATEC\INIT;
use ATEC\TOOLS;
use ATEC\WPC;

final class ATEC_OPC_Info {

private static function increase_in_steps($value, $factor, $step = 128) { return ceil(($value * $factor) / $step) * $step; }

private static function opcache_total_memory_bytes(?array $opc_conf = null, ?array $opc_status = null): int
{
	$MB = 1048576;

	// 1) Prefer configuration if present
	$cfg = $opc_conf['directives']['opcache.memory_consumption'] ?? null;

	if ($cfg !== null && $cfg !== '') 
	{
		// tolerate strings like "256", "256M", etc.
		$v = trim((string)$cfg);

		// If someone used shorthand units, parse them
		if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*([KMG])\s*B?\s*$/i', $v, $m)) {
			$n = (float)$m[1];
			$u = strtoupper($m[2]);
			$mul = match ($u) {
				'K' => 1024,
				'M' => $MB,
				'G' => 1024 * $MB,
			};
			return (int) round($n * $mul);
		}

		// Numeric but unitless: OPcache directive is MB.
		if (is_numeric($v)) {
			$n = (float)$v;

			// Guard against environments that return bytes here (rare, but your TB indicates it can happen)
			// Heuristic: if it's >= 1MB, assume it's already bytes.
			if ($n >= $MB) return (int)$n;

			return (int) round($n * $MB);
		}
	}

	// 2) Fallback to status totals (these are bytes)
	$used   = $opc_status['memory_usage']['used_memory'] ?? 0;
	$free   = $opc_status['memory_usage']['free_memory'] ?? 0;
	$wasted = $opc_status['memory_usage']['wasted_memory'] ?? 0;

	$sum = (int)($used + $free + $wasted);
	return max(0, $sum);
}

public static function init($una, $settings)	// fake parameters
{
	$opc_conf = opcache_get_configuration();
	$opc_status = function_exists('opcache_get_status') ? opcache_get_status() : false;
	$opc_file_only = $opc_conf['directives']['opcache.file_cache_only'];
	
	if ($opc_file_only)
	{
		TOOLS::table_header([], '', 'summary');
			TOOLS::tr(['Mode', 'File only']);
			TOOLS::tr(['Max files', ini_get('opcache.max_accelerated_files')]);
		TOOLS::table_footer();
	}
	else
	{
		$opStats=isset($opc_status['opcache_statistics']); 
		$percent=0;
		if ($opc_conf)
		{
			$megaByte = 1048576;
			$total_mem = self::opcache_total_memory_bytes($opc_conf, $opc_status);
			
			TOOLS::table_header([], '', 'summary');
				TOOLS::tr([__('Memory','atec-cache-info'), TOOLS::size_format($total_mem), '']);
				if ($opStats)
				{
					$hits				= $opc_status['opcache_statistics']['hits'] ?? 0;
					$misses		= $opc_status['opcache_statistics']['misses'] ?? 0;

					$totalStats 			= $hits+$misses+0.0001;
					$hits_perc				= $hits/$totalStats*100;
					$misses_perc		= $misses/$totalStats*100;

					$used_memory		= $opc_status['memory_usage']['used_memory'] ?? 0;
					$free_memory		= $opc_status['memory_usage']['free_memory'] ?? 0;
					$wasted_memory	= $opc_status['memory_usage']['wasted_memory'] ?? 0;

					if ($used_memory<0) $used_memory = max(0, $total_mem - $free_memory);
					$percent = ($total_mem > 0) ? ($used_memory / $total_mem * 100) : 0;
	
					$total_mem_mb = $total_mem / $megaByte;
					$rec_memory = $total_mem_mb;
					if ($percent>75) $rec_memory = self::increase_in_steps($rec_memory,1.50);
					elseif ($percent>50) $rec_memory = self::increase_in_steps($rec_memory,1.25);
					$OPC_recommended = ['memory'=>$rec_memory, 'strings'=>8, 'files'=>10000];

					TOOLS::tr([__('Used', 'atec-cache-info'), TOOLS::size_format($used_memory), '<small>'.TOOLS::percent_format($percent).'</small>']);
					TOOLS::tr([__('Free', 'atec-cache-info'), TOOLS::size_format($free_memory), '']);

					TOOLS::tr();

					TOOLS::tr(['  '.__('Wasted', 'atec-cache-info'), TOOLS::size_format($wasted_memory), '<small>'.TOOLS::percent_format($opc_status['memory_usage']['current_wasted_percentage']).'</small>']);
					
					TOOLS::tr();
					
					TOOLS::tr([__('Hits', 'atec-cache-info'), number_format($hits), '<small>'.TOOLS::percent_format($hits_perc).'</small>']);
					TOOLS::tr([__('Misses', 'atec-cache-info'), number_format($misses), '<small>'.TOOLS::percent_format($misses_perc).'</small>']);
				}
			TOOLS::table_footer();

			if ($opStats)
			{
				if ($rec_memory!== $total_mem_mb) TOOLS::msg('warning', 'Try raising the limit to '.$rec_memory.' MB');
				WPC::usage($percent);
				WPC::hitrate($hits_perc, $misses_perc);
			}
			else
			{
				echo
				'<p>OPcache ', esc_attr__('statistics is not available', 'atec-cache-info'), 
					',<br>';
					$disable_functions= str_contains(strtolower(ini_get('disabled_function')), 'opcache_get_status');
					echo $disable_functions?esc_attr__('"opcache_get_status" is a disabled function.', 'atec-cache-info'):esc_attr__('Maybe opcache_get_status is a disabled_function', 'atec-cache-info');
				echo
				'</p>';
			}

			$str_buffer = $opc_conf['directives']['opcache.interned_strings_buffer'];
			TOOLS::table_header([], '', 'summary');
				TOOLS::tr([__('Strings', 'atec-cache-info'), $str_buffer.' <small>MB</small>', '']);
				if ($opStats)
				{
					$percent_str 	= $opc_status['interned_strings_usage']['used_memory']*100/$opc_status['interned_strings_usage']['buffer_size'];
					$rec_str 			= $str_buffer;
					if ($percent_str>75) $rec_str= self::increase_in_steps($rec_str,1.50,8);
					elseif ($percent_str>50) $rec_str= self::increase_in_steps($rec_str,1.25,8);
					$OPC_recommended['strings']= $rec_str;
					
					TOOLS::tr(['  '.__('Used', 'atec-cache-info'), TOOLS::size_format($opc_status['interned_strings_usage']['used_memory']), '<small>'.TOOLS::percent_format($percent_str)]);
				}
			TOOLS::table_footer();

			if (isset($percent_str))
			{
				if ($rec_str!== $str_buffer) TOOLS::msg('warning', 'Try raising the limit to '.$rec_str.' MB');
				WPC::usage($percent_str);
			}

			TOOLS::help('OPcache '.__('explained', 'atec-cache-info'),
				__('OPcache improves PHP performance by storing precompiled script bytecode in shared memory, thereby removing the need for PHP to load and parse scripts on each request', 'atec-cache-info').'.');

			$save_comments = INIT::bool($opc_conf['directives']['opcache.save_comments'] ?? 0);
			$validate_timestamps = INIT::bool($opc_conf['directives']['opcache.validate_timestamps'] ?? 0);
			$enable_file_override = INIT::bool($opc_conf['directives']['opcache.enable_file_override'] ?? 0);
			$consistency_checks = INIT::bool($opc_conf['directives']['opcache.consistency_checks'] ?? 0);

			TOOLS::div(-1);
				
		TOOLS::div(0);
			
			TOOLS::div('border');
				
				echo
				'<h4>OPcache ', esc_attr__('Details', 'atec-cache-info'), '</h4><hr>';
	
				TOOLS::table_header([], '', 'summary');
					TOOLS::tr([__('Version', 'atec-cache-info'), $opc_conf['version']['version'] ?? '']);
					TOOLS::tr([__('Revalidate freq.', 'atec-cache-info'), $opc_conf['directives']['opcache.revalidate_freq'] ?? 0]);
					TOOLS::tr([__('Validate TS.', 'atec-cache-info'), TOOLS::on_off($validate_timestamps)]);
					TOOLS::tr([__('Override', 'atec-cache-info'), TOOLS::on_off($enable_file_override)]);
					TOOLS::tr([__('Comments', 'atec-cache-info'), TOOLS::on_off($save_comments, true)]);
					TOOLS::tr([__('Max waste', 'atec-cache-info'), $opc_conf['directives']['opcache.max_wasted_percentage'] ?? '']);
					TOOLS::tr([__('Consistency', 'atec-cache-info'),TOOLS::on_off($consistency_checks, true)]);
				TOOLS::table_footer();
						
				$max_accelerated_files = $opc_conf['directives']['opcache.max_accelerated_files']??0;
			
				TOOLS::table_header([], '', 'summary');
					TOOLS::tr([__('Max acc. files', 'atec-cache-info'), number_format($max_accelerated_files)]);
					if ($opStats && $max_accelerated_files!==0)
					{
						$numScripts 	= $opc_status['opcache_statistics']['num_cached_scripts']??0;
						$numKeys 		= $opc_status['opcache_statistics']['num_cached_keys']??0;
						$maxReal			= $opc_status['opcache_statistics']['max_cached_keys']??0;
	
						$percentFiles	 	= ($numScripts+$numKeys)*100/$maxReal;
						$recFiles	 			= $max_accelerated_files;
	
						if ($percentFiles>75) $recFiles= self::increase_in_steps($max_accelerated_files, 1.5, 1000);
						elseif ($percentFiles>50) $recFiles= self::increase_in_steps($max_accelerated_files, 1.25, 1000);
						$OPC_recommended['files']= $recFiles;

						TOOLS::tr(['  '.__('Max real', 'atec-cache-info'), number_format($maxReal)]);
						TOOLS::tr();
						TOOLS::tr(['  '.__('Scripts cached', 'atec-cache-info'), number_format($numScripts)]);
						TOOLS::tr(['  '.__('Keys cached', 'atec-cache-info'), number_format($numKeys)]);
					}
				TOOLS::table_footer();
	
				if (isset($percentFiles))
				{
					if ($recFiles!== $max_accelerated_files) TOOLS::msg('warning', 'Try raising the limit to '.$recFiles);
					WPC::usage($percentFiles);
				}
		}
	}

	require('atec-OPC-help.php');
}

}
?>