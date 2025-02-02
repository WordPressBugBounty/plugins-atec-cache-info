<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_oc_groups { 

private function scan_for_scripts($dir): int
{
	$count=0; 
	// @codingStandardsIgnoreStart
	// Much faster and less memory usage than WP_Filesystem_Direct::dirlist(
	$dir_handle = opendir($dir);
	if (is_resource($dir_handle))
	{
		while(($f = readdir($dir_handle)) == true)  
		{
			if ($f==='.' || $f==='..') continue;
			$fullpath=$dir.$f;
			if (is_dir($fullpath)) $count+=$this->scan_for_scripts($fullpath.DIRECTORY_SEPARATOR);
			elseif (str_ends_with($fullpath,'.php')) $count++;
		} 
		closedir($dir_handle);
	}
	// @codingStandardsIgnoreEnd
	return $count;
}

function __construct($url,$nonce,$action) {

$op_status = false;
if (function_exists('opcache_get_status')) $op_status=opcache_get_status();

if ($action=='scan')
{
	atec_little_block('OPcache '.esc_attr__('Scripts','atec-cache-info'));
	echo '<p><strong>Number of script files in root folder:</strong> ', esc_attr($this->scan_for_scripts(ABSPATH)), '
	<br>You should set `opcache.max_accelerated_files‘ option accordingly.</p>';
}
else
{
	echo '
	<div class="atec-db">
		<div class="atec-dilb atec-mr-10">'; atec_little_block('OPcache '.esc_attr__('Scripts','atec-cache-info')); echo '</div>';
		if ($action!=='scan') echo '<div class="atec-dilb atec-vat">'; atec_nav_button($url,$nonce,'scan','OPC_Scripts',esc_attr__('Scan root folder for PHP scripts','atec-cache-info'),false); echo '</div>';
		echo '
	</div>';
}

if (!$op_status) atec_error_msg('The function „opcache_get_status“ does not exist');
else
{
	$c=0; $total=0; $keys=[];
	atec_table_header_tiny(['#',__('Key','atec-cache-info'),__('Hits','atec-cache-info'),__('Size','atec-cache-info'),__('Last used','atec-cache-info'),__('Revalidate','atec-cache-info').' (s)']);
		$scripts=[];
		foreach ($op_status['scripts'] as $key => $value) { $scripts[]=array_merge(array('key'=>$key),$value); }

		array_multisort($scripts);
		foreach ($scripts as $s) 
		{
			$c++; 
			$color=in_array($s['key'],$keys)?' atec-red':'';
			echo '<tr>
					<td>', esc_attr($c), '</td>
					<td class="atec-anywrap', str_contains($s['key'],'atec-')?' atec-violet':'', esc_attr($color), '" title="', esc_url($s['full_path']) ,'">', esc_attr($s['key']), '</td>
					<td class="atec-nowrap atec-table-right">', esc_attr($s['hits']), '</td>
					<td class="atec-nowrap atec-table-right">', esc_attr(size_format($s['memory_consumption'])), '</td>				
					<td class="atec-nowrap atec-table-right">', esc_attr(gmdate('m/d H:m',$s['last_used_timestamp'])), '</td>				
					<td class="atec-nowrap atec-table-right">', esc_attr($s['revalidate']-time()), '</td>
				</tr>';
			$total+=$s['memory_consumption'];
			$keys[]=$s['key'];
		}
		if ($c>0)
		{
			atec_empty_tr();
			echo '<tr class="atec-table-tr-bold"><td>', esc_attr($c), '</td><td></td><td></td><td class="atec-nowrap atec-table-right">', esc_html(size_format($total)), '</td><td colspan="2"></td></tr>';
		}
	
	echo '</tbody>
	</table>';
}

}}

?>