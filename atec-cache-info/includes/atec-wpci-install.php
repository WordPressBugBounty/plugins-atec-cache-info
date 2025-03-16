<?php
if (!defined('ABSPATH')) { exit(); }
if (!function_exists('atec_header')) @require(__DIR__.'/atec-tools.php');	

add_action( 'admin_enqueue_scripts', function() 
{ 
	atec_reg_style('atec',__DIR__,'atec-style.min.css','1.0.011');
	
	global $atec_active_slug;
	if ($atec_active_slug!=='atec_group')
	{
		atec_reg_style('atec_check',__DIR__,'atec-check.min.css','1.0.005');
		$atec_query = atec_query();
		if (str_contains($atec_query, 'nav=Cache') || str_ends_with($atec_query, 'page=atec_wpci')) atec_reg_style('atec_cache_info',__DIR__,'atec-cache-info-style.min.css','1.0.004');
	}
});
	
if ($atec_active_slug!=='atec_group')
{
	function atec_wpci() { @require(__DIR__.'/atec-cache-info-dashboard.php'); }
	
	add_action('admin_init', function() 
	{
		if (!function_exists('atec_load_pll')) @require('atec-translation.php');
		atec_load_pll(__DIR__,'cache-info');
	});
}
?>