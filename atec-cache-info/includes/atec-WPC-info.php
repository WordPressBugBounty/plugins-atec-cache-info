<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_WPcache_info { function __construct($op_conf,$op_status,$opcache_file_only,$tools) {	

global $wp_object_cache;
if (isset($wp_object_cache->cache_hits))
{
	$total		= $wp_object_cache->cache_hits+$wp_object_cache->cache_misses+0.001;
	$hits		= $wp_object_cache->cache_hits*100/$total;
	$misses		= $wp_object_cache->cache_misses*100/$total;
	echo'
	<table class="atec-table atec-table-tiny atec-table-td-first">
		<tbody>
			<tr><td>', esc_attr__('Hits','atec-cache-info'), ':</td><td>', esc_attr(number_format($wp_object_cache->cache_hits).sprintf(" (%.1f%%)",$hits)), '</td></tr>
			<tr><td>', esc_attr__('Misses','atec-cache-info'), ':</td><td>', esc_attr(number_format($wp_object_cache->cache_misses).sprintf(" (%.1f%%)",$misses)), '</td></tr>
		</tbody>
	</table>';
	
	$tools->hitrate($hits,$misses);
}

if (defined('LSCWP_OBJECT_CACHE') && LSCWP_OBJECT_CACHE=='true' && (method_exists('WP_Object_Cache', 'debug'))) 
{
	$debug=$wp_object_cache->debug();
	preg_match('/\[total\]\s(\d+)\s/', $debug, $matches); $ls_total=(int) $matches[1];
	preg_match('/\[hit\]\s(\d+)\s/', $debug, $matches); $ls_hit=(int) $matches[1];
	preg_match('/\[miss\]\s(\d+)\s/', $debug, $matches); $ls_miss=(int) $matches[1];
	$total=$ls_hit+$ls_miss+0.001;
	$hits=$ls_hit*100/$total;
	$misses=$ls_miss*100/$total;
	echo ' 
	<table class="atec-table atec-table-tiny atec-table-td-first">
	<tbody>
		<tr><td>', esc_attr__('Items','atec-cache-info'), ':</td><td>', esc_attr(number_format($ls_total)), '</td></tr>
		<tr><td>', esc_attr__('Hits','atec-cache-info'), ':</td><td>', esc_attr(number_format($ls_hit).sprintf(" (%.1f%%)",$hits)), '</td></tr>
		<tr><td>', esc_attr__('Misses','atec-cache-info'), ':</td><td>', esc_attr(number_format($ls_miss).sprintf(" (%.1f%%)",$misses)), '</td></tr>
	</tbody>
	</table>';

	$tools->hitrate($hits,$misses);

	if (defined('LSCWP_V')) 
	{
		$imgSrc =plugins_url( '/assets/img/system/litespeed-icon.svg', __DIR__ );
		echo '<p><img style="height:20px;" src="', esc_url($imgSrc), '"> LiteSpeed '.esc_attr__('cache','atec-cache-info').' v.',esc_html(LSCWP_V),' '.esc_attr__('is active','atec-cache-info').'.</p>';
	}
}

global $_wp_using_ext_object_cache;
if ($_wp_using_ext_object_cache) atec_success_msg('WP '.__('object cache','atec-cache-info').' '.__('is persistent','atec-cache-info'));

$testKey='atec_wp_test_key';
wp_cache_set($testKey,'hello');
$success=wp_cache_get($testKey)=='hello';
atec_badge('WP '.__('object cache','atec-cache-info').' '.__('is writeable','atec-cache-info'),'Writing to WP '.__('object cache','atec-cache-info').' failed',$success);
if ($success) 	wp_cache_delete($testKey);

}}
?>