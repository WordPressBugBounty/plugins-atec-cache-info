<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_SQLite_info { function __construct($wpc_tools, $wp_object_cache) {	
	
$total=$wp_object_cache->cache_hits+$wp_object_cache->cache_misses+0.001;
$hits=$wp_object_cache->cache_hits*100/$total;
$misses=$wp_object_cache->cache_misses*100/$total;

echo'
<table class="atec-table atec-table-tiny atec-table-td-first">
<tbody>
	<tr><td>Version:</td><td>', esc_attr(SQLite_Object_Cache()->_version), '</td><td></td></tr>
	<tr><td>', esc_attr__('Hits','atec-cache-info'), ':</td>
		<td>', esc_attr(number_format($wp_object_cache->cache_hits)), '</td><td><small>', esc_attr(sprintf("%.1f%%",$hits)), '</small></td></tr>
	<tr><td>', esc_attr__('Misses','atec-cache-info'), ':</td>
		<td>', esc_attr(number_format($wp_object_cache->cache_misses)) , '</td><td><small>', esc_attr(sprintf("%.1f%%",$misses)), '</small></td></tr>
</tbody>
</table>';

$wpc_tools->hitrate($hits,$misses);
}}
?>