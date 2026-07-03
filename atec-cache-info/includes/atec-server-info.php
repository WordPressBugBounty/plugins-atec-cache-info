<?php
defined('ABSPATH') || exit;

use ATEC\CPANEL;
use ATEC\DB;
use ATEC\INIT;
use ATEC\TOOLS;

final class ATEC_Server_Info {

private static function offset2Str($tzOffset): string { return ($tzOffset>0?'+' : '').$tzOffset; }

private static function kbToBytes($value): int
{
	return intval($value) * 1024;
}

private static function isPathAllowedByOpenBasedir($path): bool
{
	$baseDirs = ini_get('open_basedir');
	if (!is_string($baseDirs) || $baseDirs== '') return true;

	// Never call realpath($path): outside open_basedir it warns (e.g. /proc/meminfo).
	$normalizedPath = rtrim(str_replace('\\', '/', $path), '/');

	foreach (explode(PATH_SEPARATOR, $baseDirs) as $baseDir)
	{
		$baseDir = trim($baseDir);
		if ($baseDir== '') continue;
		if ($baseDir== '.') return true;

		$realBase = @realpath($baseDir);
		if ($realBase=== false) $realBase = $baseDir;

		$normalizedBase = rtrim(str_replace('\\', '/', $realBase), '/');
		if ($normalizedBase== '') continue;

		if ($normalizedPath === $normalizedBase) return true;
		if (str_starts_with($normalizedPath, $normalizedBase.'/')) return true;
	}

	return false;
}

private static function getLinuxRamBytes()
{
	$ram = '';

	if (function_exists('exec'))
	{
		$output = null; $retval = null;
		@exec('free', $output, $retval);
		if ($retval==0 && getType($output)== 'array' && !empty($output) && isset($output[1]))
		{
			preg_match('/\s+([\d]*)\s+/', $output[1], $match);
			if (isset($match[1]) && $match[1]!== '') return self::kbToBytes($match[1]);
		}
	}

	// Fallback where exec/free is unavailable or restricted.
	if (self::isPathAllowedByOpenBasedir('/proc/meminfo') && is_readable('/proc/meminfo'))
	{
		$meminfo = @file_get_contents('/proc/meminfo');
		if (is_string($meminfo) && preg_match('/^MemTotal:\s+(\d+)\s+kB$/mi', $meminfo, $match)) return self::kbToBytes($match[1]);
	}

	// Final fallback where PHP file access is restricted.
	if (function_exists('shell_exec'))
	{
		$memTotal = @shell_exec("awk '/^MemTotal:/ {print $2}' /proc/meminfo");
		if (is_string($memTotal))
		{
			$memTotal = trim($memTotal);
			if (is_numeric($memTotal)) return self::kbToBytes($memTotal);
		}

		// Fallback for restricted shells where awk or /proc access is blocked.
		$freeOutput = @shell_exec("free -k 2>/dev/null | awk 'NR==2{print $2}'");
		if (is_string($freeOutput))
		{
			$freeOutput = trim($freeOutput);
			if (is_numeric($freeOutput)) return self::kbToBytes($freeOutput);
		}
	}

	return $ram;
}

private static function getSystemRamBytes($os)
{
	if ($os== 'Windows') return '';
	if ($os== 'Darwin' && function_exists('exec'))
	{
		$output = null; $retval = null;
		@exec('/usr/sbin/sysctl -n hw.memsize', $output, $retval);
		return ($retval==0 && getType($output)== 'array' && !empty($output))?intval($output[0]):'';
	}

	return self::getLinuxRamBytes();
}

private static function getGeo($ip): string
{
	$url = 'https://ipinfo.io/'.$ip.'/json?token=274eb3cf12e5f5';
	$request	= wp_remote_get( $url );
	if (is_wp_error($request)) { return ''; }
	$geo = json_decode( wp_remote_retrieve_body( $request ) );
	return (isset($geo->city) && isset($geo->country))?($geo->city.' / '.$geo->country):'';
}

private static function tblHeader($icon, $title, $arr): void
{
	echo
	'<div class="atec-mb-5">
		<div class="atec-dilb atec-mr-10 atec-head">'; 
			\ATEC\SVG::echo($icon);
			echo '<span class="atec-label atec-ml-10">', esc_attr($title), '</span>',
		'</div>';
		TOOLS::table_header($arr, '', 'atec-mb-10');
		echo
		'<tr>';
}

private static function tblFooter(): void { echo '</tr>'; TOOLS::table_footer(); echo '</div>'; }

public static function init($una = null, $license_ok = true) 
{

	CPANEL::cpanel_header($una, __('Server Info', 'atec-cache-info'));

	TOOLS::div('border-g');
		
		$empty = '-/-';
		$php_uname = ['n'=>$empty, 's'=>$empty, 'r'=>$empty, 'm'=>$empty];
		if (function_exists('php_uname'))
		{
			$arr = ['n', 's', 'r', 'm'];
			foreach($arr as $a) $php_uname[$a]=php_uname($a);
		}
		if ($php_uname['s']=== $empty) $php_uname['s']=PHP_OS;
		if ($php_uname['m']=== $empty)
		{
			$arch=isset($_ENV['PROCESSOR_ARCHITECTURE'])?sanitize_key($_ENV['PROCESSOR_ARCHITECTURE']):$empty;
			$php_uname['m']=esc_attr($arch);
		}

		$host = $php_uname['n'];
		$ip = INIT::_SERVER('SERVER_ADDR');
		if ($ip!= '') { $host .= ($host!== ''?' | ' : '').$ip; }
		if (function_exists('curl_version')) { $curl = @curl_version(); }
		else { $curl= array('version'=>'n/a', 'ssl_version'=>'n/a'); }

		global $wpdb;
		$mysql_version = $wpdb->db_version();
	
		$peak= $empty;
		if (function_exists('memory_get_peak_usage')) { $peak= size_format(memory_get_peak_usage(true)); }

		$unlimited	= INIT::slug()=== 'atec_wpsi';
		
		$tz				= date_default_timezone_get()?date_default_timezone_get():(ini_get('date.timezone')?ini_get('date.timezone'):'');
		$tzOffset		= intval(get_option('gmt_offset',0));
		$now			= new DateTime('', new DateTimeZone('UTC'));
		$now			= $now->modify(self::offset2Str($tzOffset).' hour');
		$geo				= '';

		if ($ip!= '' && $ip!= '127.0.0.1' && $ip!= '::1')
		{
			$lastIP= get_option('atec_WPSI_ip', '');
			$geo= get_option('atec_WPSI_geo', '');
			if ($ip!= $lastIP || $geo== '')
			{
				$geo= self::getGeo($ip);
				update_option('atec_WPSI_ip',esc_attr($ip), false);
				update_option('atec_WPSI_geo',esc_attr($geo), false);
			}
		}

		$dt	= disk_total_space(ABSPATH);
		$df	= disk_free_space(ABSPATH);
	
		TOOLS::div('g-50');
		
				self::tblHeader('computer',__('Operating system', 'atec-cache-info'),['OS', 'Version',__('Architecture', 'atec-cache-info'),__('Date/Time', 'atec-cache-info'), 'Disk '.__('total', 'atec-cache-info'), 'Disk '.__('free', 'atec-cache-info')]);
				
					echo
					'<td class="atec-nowrap">';
						$icon= '';
						switch ($php_uname['s'])
						{
							case 'Darwin': $icon= 'apple'; break;
							case 'Windows': $icon= 'windows'; break;
							case 'Linux': $icon= 'linux'; break;
							case 'Ubuntu': $icon= 'ubuntu'; break;
						}
						if ($icon!== '') { \ATEC\SVG::echo($icon); echo ' '; }
						echo esc_attr($php_uname['s']),
					'</td>
					<td>', esc_attr($php_uname['r']), '</td>
					<td>', esc_attr($php_uname['m']), '</td>
					<td>', esc_attr(date_format($now,"Y-m-d H:i")), ' ', esc_attr($tz.' '.self::offset2Str($tzOffset)), '</td>';
					TOOLS::td_size_format($dt);
					TOOLS::td_size_format($df);

				self::tblFooter();
	
				$headArray=['Host', 'IP'];
				if ($geo!= '') $headArray[] = __('Location', 'atec-cache-info');
				$headArray[] = 'Server';
				$headArray[] = 'CURL';
					
				self::tblHeader('server',__('Server', 'atec-cache-info'), $headArray);
				
					$serverSoftware	= INIT::_SERVER('SERVER_SOFTWARE');
					$serverName		= INIT::_SERVER('SERVER_NAME');
		
					$icon= '';
					$lowSoft= strtolower($serverSoftware);
					if (str_contains($lowSoft, 'apache')) $icon= 'apache';
					else	if (str_contains($lowSoft, 'nginx')) $icon= 'nginx';
							else if (str_contains($lowSoft, 'litespeed')) $icon= 'litespeed';
	
					echo
					'<td>', esc_html($serverName), '</td>
					<td>', esc_html($host), '</td>';
	
					if ($geo!= '') echo '<td>', esc_html($geo), '</td>';
					echo 
					'<td>';
						if ($icon!== '') { \ATEC\SVG::echo($icon); echo ' '; }
						echo esc_html($serverSoftware), '
					</td>
					<td>';
						\ATEC\SVG::echo('curl');
						echo ' ver. ', esc_attr(function_exists( 'curl_version')?$curl['version'].' | '.str_replace('(SecureTransport)', '', $curl['ssl_version']):'n/a');
					echo
					'</td>';
				
				self::tblFooter();

				$ram= self::getSystemRamBytes($php_uname['s']);
				$memArr=[];
				if (!(empty($ram))) $memArr[] = 'System RAM';
				$limitStr = __('limit', 'atec-cache-info');
				$memStr = __('mem.', 'atec-cache-info');
				$memArr= array_merge($memArr,['PHP '.$memStr.' '.$limitStr, 'WP '.$memStr.' '.$limitStr, 'WP max. '.$memStr.' '.$limitStr, $memStr.' '.__('usage', 'atec-cache-info')]);
				
				self::tblHeader('memory',__('Memory', 'atec-cache-info'), $memArr);
				
					if (!(empty($ram))) TOOLS::td_size_format($ram);
					echo 
					'<td>', esc_attr(ini_get('memory_limit')), '</td>
					<td>', esc_attr(WP_MEMORY_LIMIT), '</td>
					<td>', esc_attr(WP_MAX_MEMORY_LIMIT), '</td>
					<td>', esc_attr($peak), '</td>';
					
				self::tblFooter();

				self::tblHeader('php',__('PHP Settings', 'atec-cache-info'),['‘max. exec. time’', '‘max. input vars’', '‘post max. size’', '‘upload max. filesize’']);
				
				echo '<td>', esc_html(gmdate('H:i:s', ini_get('max_execution_time'))), ' <small>h</small></td>
					<td>', esc_html(number_format(ini_get('max_input_vars'))), '</td>
					<td>', esc_html(ini_get('post_max_size')), '</td>
					<td>', esc_html(ini_get('upload_max_filesize')), '</td>';
				
				self::tblFooter();

			//TOOLS::div(-1);
		
		if ($unlimited)
		{
			TOOLS::div(0);

				$isWP = !function_exists('classicpress_version');
				$short = ($isWP?'WP' : 'CP');
				self::tblHeader($isWP?'wordpress' : 'classicpress', $isWP?'WordPress' : 'ClassicPress',[$short.' '.__('root', 'atec-cache-info'), $short.' '.__('size', 'atec-cache-info')]);
				echo '<td>', esc_url(defined('ABSPATH')?ABSPATH:$empty), '</td>';
				TOOLS::td_size_format(get_dirsize(get_home_path()));
				self::tblFooter();
	
				self::tblHeader('calendar',__('Versions', 'atec-cache-info'),['WP', 'PHP', 'SQL']);
				echo '<td>Ver. ', esc_html($isWP?get_bloginfo('version'):classicpress_version()), '</td>
					<td>Ver. ', esc_attr(phpversion().(function_exists( 'php_sapi_name')?' | '.php_sapi_name():'')), '</td>
					<td>Ver. ', esc_attr($mysql_version ?? 'n/a'), '</td>';
				self::tblFooter();

				global $wpdb;
				// @codingStandardsIgnoreStart
				$db_max_conn		= $wpdb->get_results('SHOW VARIABLES LIKE "max_connections"');
				$db_max_package 	= $wpdb->get_results('SHOW VARIABLES LIKE "max_allowed_packet"');
				// @codingStandardsIgnoreEnd

				$db_size = DB::db_size();
				$db_info = DB::db_info();
				
				self::tblHeader($db_info['name'], __('Database', 'atec-cache-info'),
					['DB '.__('driver', 'atec-cache-info'), 'DB ver.', 'DB '.__('user', 'atec-cache-info'), 'DB '.__('user', 'atec-cache-info')]);
					
					echo 
					'<td>', esc_html($db_info['software']), '</td>
					<td>Ver. ', esc_html($db_info['version']), '</td>
					<td>', esc_attr(defined('DB_NAME')?DB_NAME:esc_attr($empty)), '</td>
					<td>', esc_attr(defined('DB_USER')?DB_USER:esc_attr($empty)), '</td>';
					
				self::tblFooter();

				self::tblHeader($db_info['name'], __('Database settings', 'atec-cache-info'),
					['DB max. '.__('conn.', 'atec-cache-info'), 'DB max. '.__('packages', 'atec-cache-info'), 'DB '.__('size', 'atec-cache-info'), 'DB Index '.__('size', 'atec-cache-info')]);
					
					echo 
					'<td>', ($db_max_conn?esc_attr($db_max_conn[0]->Value):esc_attr($empty)), '</td>';
					if (!$db_max_package) echo '<td>-/-</td>';
					else TOOLS::td_size_format($db_max_package[0]->Value);
					TOOLS::td_size_format($db_size['data']);
					TOOLS::td_size_format($db_size['index']);
				
				self::tblFooter();

			TOOLS::div(-1);
		}

	TOOLS::div(-3);

}

}
?>