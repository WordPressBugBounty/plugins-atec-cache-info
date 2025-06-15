<?php
defined('ABSPATH') || exit;

use ATEC\DB;
use ATEC\INIT;
use ATEC\TOOLS;

final class ATEC_Server_Info {

private static function envExists($str): string { return isset($_SERVER[$str])?sanitize_text_field(wp_unslash
($_SERVER[$str])):''; }
private static function offset2Str($tzOffset): string { return ($tzOffset>0?'+' : '').$tzOffset; }

private static function getGeo($ip): string
{
	$url			= 'https://ipinfo.io/'.$ip.'/json?token=274eb3cf12e5f5';
	$request	= wp_remote_get( $url );
	if (is_wp_error($request)) { return ''; }
	$geo = json_decode( wp_remote_retrieve_body( $request ) );
	return (isset($geo->city) && isset($geo->country))?($geo->city.' / '.$geo->country):'';
}

private static function tblHeader($icon, $title, $arr): void
{
	echo
	'<div class="atec-mb-5">
		<div style="padding: 0 0 5px 10px;">'; 
			\ATEC\SVG::echo($icon);
			echo '<span class="atec-label atec-ml-10">', esc_attr($title), '</span>',
		'</div>';
		TOOLS::table_header($arr, '', 'atec-mb-10');
		echo
		'<tr>';
}

private static function tblFooter(): void { echo '</tr>'; TOOLS::table_footer(); echo '</div>'; }

public static function init() 
{
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
	$ip		= self::envExists('SERVER_ADDR');
	if ($ip!= '') { $host .= ($host!== ''?' | ' : '').$ip; }
	if (function_exists('curl_version')) { $curl = @curl_version(); }
	else { $curl= array('version'=>'n/a', 'ssl_version'=>'n/a'); }

	global $wpdb;
	$mysql_version = $wpdb->db_version();

	$peak= $empty;
	if (function_exists('memory_get_peak_usage')) { $peak= size_format(memory_get_peak_usage(true)); }

	TOOLS::little_block('Server Info');

	$dt	= disk_total_space(ABSPATH);
	$df	= disk_free_space(ABSPATH);
	$dp	= ($dt && $df)?'('.round($df/$dt*100,1).'%)' : '';

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

	echo
	'<div class="atec-g atec-g-auto-2 atec-pt-10">
	
		<div class="atec-border-white">';

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

			echo '<br>';

			$headArray=['Host', 'IP'];
			if ($geo!= '') $headArray[] = __('Location', 'atec-cache-info');
			$headArray[] = 'Server';
			$headArray[] = 'CURL';
			self::tblHeader('server',__('Server', 'atec-cache-info'), $headArray);
			$serverSoftware	= self::envExists('SERVER_SOFTWARE');
			$serverName		= self::envExists('SERVER_NAME');

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

		echo 
		'</div>
		
		<div class="atec-border-white">';

			$ram= '';
			if (function_exists('exec'))
			{
				if ($php_uname['s']== 'Darwin')
				{
					$output=null; $retval=null; $cmd= '/usr/sbin/sysctl -n hw.memsize';
					@exec($cmd, $output, $retval);
					$ram = ($retval==0 && getType($output)== 'array' && !empty($output))?intval($output[0]):0;
				}
				elseif ($php_uname['s']!== 'Windows')
				{
					$output=null; $retval=null; $cmd= 'free';
					@exec($cmd, $output, $retval);
					$ram = ($retval==0 && getType($output)== 'array' && !empty($output) && count($output)>=1)?$output[1]:'';
					if ($ram!== '')
					{
						preg_match('/\s+([\d]*)\s+/', $ram, $match);
						$ram = $match[1] ?? '';
					}
				}
			}
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

			echo '<br>';

			self::tblHeader('php',__('PHP Settings', 'atec-cache-info'),['„max. exec. time“', '„max. input vars“', '„post max. size“', '„upload max. filesize“']);
			echo '<td>', esc_html(gmdate('H:i:s', ini_get('max_execution_time'))), ' <small>h</small></td>
				<td>', esc_html(number_format(ini_get('max_input_vars'))), '</td>
				<td>', esc_html(ini_get('post_max_size')), '</td>
				<td>', esc_html(ini_get('upload_max_filesize')), '</td>';
			self::tblFooter();

		echo 
		'</div>
		
	</div>';

	if ($unlimited)
	{
	echo '
	<div class="atec-g atec-g-50">
	
		<div class="atec-border-white">';

			$isWP = !function_exists('classicpress_version');
			$short = ($isWP?'WP' : 'CP');
			self::tblHeader($isWP?'wordpress' : 'classicpress', $isWP?'WordPress' : 'ClassicPress',[$short.' '.__('root', 'atec-cache-info'), $short.' '.__('size', 'atec-cache-info')]);
			echo '<td>', esc_url(defined('ABSPATH')?ABSPATH:$empty), '</td>';
			TOOLS::td_size_format(get_dirsize(get_home_path()));
			self::tblFooter();

			echo '<br>';

			self::tblHeader('calendar',__('Versions', 'atec-cache-info'),['WP', 'PHP', 'SQL']);
			echo '<td>Ver. ', esc_html($isWP?get_bloginfo('version'):classicpress_version()), '</td>
				<td>Ver. ', esc_attr(phpversion().(function_exists( 'php_sapi_name')?' | '.php_sapi_name():'')), '</td>
				<td>Ver. ', esc_attr($mysql_version ?? 'n/a'), '</td>';
			self::tblFooter();

		echo 
		'</div>
		
		<div class="atec-border-white">';

			global $wpdb;
			// @codingStandardsIgnoreStart
			$db_max_conn			= $wpdb->get_results('SHOW VARIABLES LIKE "max_connections"');
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

			echo '<br>';

			self::tblHeader($db_info['name'], __('Database settings', 'atec-cache-info'),
				['DB max. '.__('conn.', 'atec-cache-info'), 'DB max. '.__('packages', 'atec-cache-info'), 'DB '.__('size', 'atec-cache-info'), 'DB Index '.__('size', 'atec-cache-info')]);
				echo 
				'<td>', ($db_max_conn?esc_attr($db_max_conn[0]->Value):esc_attr($empty)), '</td>';
				if (!$db_max_package) echo '<td>-/-</td>';
				else TOOLS::td_size_format($db_max_package[0]->Value);
				TOOLS::td_size_format($db_size['data']);
				TOOLS::td_size_format($db_size['index']);
			self::tblFooter();

		echo 
		'</div>
		
	</div>';
	}

}

}

ATEC_Server_Info::init();
?>