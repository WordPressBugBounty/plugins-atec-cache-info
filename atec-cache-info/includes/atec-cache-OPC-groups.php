<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_oc_groups { 

	function __construct() {

	atec_little_block('OPC Scripts');

	$op_status = false;
	if (function_exists('opcache_get_status')) $op_status=opcache_get_status();
	
	if (!$op_status) atec_error_msg('The function `opcache_get_status´ does not exist');
	else
	{
		$c=0; $total=0;
		atec_table_header_tiny(['#',__('Key','atec-cache-apcu'),__('Hits','atec-cache-apcu'),__('Size','atec-cache-apcu'),__('Revalidate','atec-cache-apcu').' (s)']);
		foreach ($op_status['scripts'] as $key => $value) 
		{
			$c++;
			echo '<tr>
					<td class="atec-nowrap">', esc_attr($c), '</td>
					<td class="atec-anywrap">', esc_attr($key), '</td>
					<td class="atec-nowrap atec-table-right">', esc_attr($value['hits']), '</td>
					<td class="atec-nowrap atec-table-right">', esc_attr(size_format($value['memory_consumption'])), '</td>				
					<td class="atec-nowrap atec-table-right">', esc_attr($value['revalidate']-time()), '</td>
				</tr>';
			$total+=$value['memory_consumption'];
			//<td class="atec-nowrap">', esc_attr($value['full_path']), '</td>
		}
		atec_TR_empty();
		echo '<tr class="atec-table-tr-bold"><td>', esc_attr($c), '</td><td></td><td></td><td class="atec-nowrap">', esc_html(size_format($total)), '</td><td></td></tr>
				</tbody></table>';
	}

}}

?>