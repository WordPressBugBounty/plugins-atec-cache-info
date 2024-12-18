<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_JIT_info { function __construct($wpc_tools,$op_status) {	

$percent=false;
if ($op_status)
{
	$jit_size=$op_status['jit']['buffer_size'];
	$jit_free=$op_status['jit']['buffer_free'];
	$percent=$jit_free/($jit_size+0.0001);
}
else
{
	$jit_size=(int) wp_convert_hr_to_bytes(ini_get('opcache.jit_buffer_size'));
}

echo '
<table class="atec-table atec-table-tiny atec-table-td-first">
<tbody>
	<tr><td>JIT ', esc_attr__('config','atec-cache-info'), ':</td><td>', esc_attr(ini_get('opcache.jit')), '</td><td></td></tr>
	<tr><td>Debug:</td><td>', esc_attr(ini_get('opcache.jit_debug')), '</td><td></td></tr>';
	atec_empty_tr();
	echo '
	<tr><td>', esc_attr__('Memory','atec-cache-info'), ':</td><td>', esc_attr(size_format($jit_size)), '</td><td></td></tr>';
	if ($percent) echo '<tr><td>', esc_attr__('Used','atec-cache-info'), ':</td>
		<td>', esc_attr(size_format($jit_size-$jit_free)), '</td><td><small>', esc_attr(sprintf("%.1f%%",$percent)), '</small></td></tr>';
echo '
</tbody>
</table>';

if ($percent) $wpc_tools->usage($percent);	
}}
?>