<?php
defined('ABSPATH') || exit;

use ATEC\ALIAS;
use ATEC\TOOLS;

final class ATEC_OPC_Groups {

private static function scan_for_scripts($dir): int
{
	$count=0;
	// @codingStandardsIgnoreStart
	// Much faster and less memory usage than WP_Filesystem_Direct::dirlist(
	$dir_handle = opendir($dir);
	if (is_resource($dir_handle))
	{
		while(($f = readdir($dir_handle)) == true)
		{
			if ($f=== '.' || $f=== '..') continue;
			$full_path= $dir.$f;
			if (is_dir($full_path)) $count+= self::scan_for_scripts($full_path.DIRECTORY_SEPARATOR);
			elseif (str_ends_with($full_path, '.php')) $count++;
		}
		closedir($dir_handle);
	}
	// @codingStandardsIgnoreEnd
	return $count;
}

public static function init($una)
{

	$op_status = false;
	if (function_exists('opcache_get_status')) $op_status=opcache_get_status();

	if ($una->action== 'scan')
	{
		TOOLS::little_block('OPcache '.esc_attr__('Scripts', 'atec-cache-info'));
		echo 
		'<p><strong>Number of script files in root folder:</strong> ', esc_attr(self::scan_for_scripts(ABSPATH)), '
		<br>You should set `opcache.max_accelerated_files‘ option accordingly.</p>';
	}
	else
	{
		echo
		'<div class="atec-db">
			<div class="atec-dilb atec-mr-10">'; TOOLS::little_block('OPcache '.esc_attr__('Scripts', 'atec-cache-info')); echo '</div>';
			if ($una->action!== 'scan') echo '<div class="atec-dilb atec-vat">'; TOOLS::button($una, 'scan', 'OPC_Scripts',esc_attr__('Scan root folder for PHP scripts', 'atec-cache-info')); echo '</div>';
			echo
		'</div>';
	}

	if (!$op_status) TOOLS::msg(false, 'The function ‘opcache_get_status’ does not exist');
	else
	{
		$c=0; $total=0; $keys=[];
		TOOLS::table_header(['#',__('Key', 'atec-cache-info'),__('Hits', 'atec-cache-info'),__('Size', 'atec-cache-info'),__('Last used', 'atec-cache-info'),__('Revalidate', 'atec-cache-info').'&nbsp;(s)']);
			$scripts=[];
			foreach ($op_status['scripts'] as $key => $value) { $scripts[]= array_merge(array('key'=>$key), $value); }

			array_multisort($scripts);
			foreach ($scripts as $s)
			{
				$c++;
				$color=in_array($s['key'], $keys)?' atec-red' : '';
				echo 
				'<tr>
					<td class="atec-right">', esc_attr($c), '</td>
					<td class="atec-anywrap', stripos($s['key'], 'atec') !== false ? ' atec-violet' : '', esc_attr($color), '" title="', esc_url($s['full_path']) , '">', esc_attr($s['key']), '</td>
					<td class="atec-nowrap atec-right">', esc_attr($s['hits']), '</td>';
					TOOLS::td_size_format($s['memory_consumption']);
					echo
					'<td class="atec-nowrap atec-right">', esc_html(TOOLS::gmdate($s['last_used_timestamp'])), '</td>
					<td class="atec-nowrap atec-right">', esc_html($s['revalidate']-time()), '</td>
				</tr>';
				$total+= $s['memory_consumption'];
				$keys[]= $s['key'];
			}
			if ($c>0)
			{
				ALIAS::tr();
				ALIAS::tr([$c, '2@', TOOLS::size_format($total), '2@'], 'td', 'bold');
			}
		TOOLS::table_footer();
	}

}

}
?>