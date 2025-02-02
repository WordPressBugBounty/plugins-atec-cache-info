<?php
if (!defined('ABSPATH')) { exit(); }
if (!function_exists('atec_header')) @require(__DIR__.'/atec-tools.php');	

add_action( 'admin_enqueue_scripts', function() 
{ 
	atec_reg_style('atec',__DIR__,'atec-style.min.css','1.0.007'); 
	global $atec_active_slug;
	if ($atec_active_slug!=='atec_group')
	{
		atec_reg_style('atec_check',__DIR__,'atec-check.min.css','1.0.003');	
		if (str_contains(atec_query(), 'nav=Cache')) atec_reg_style('atec_cache_info',__DIR__,'atec-cache-info-style.min.css','1.0.002');
	}
});

if ($atec_active_slug!=='atec_group')
{
	function atec_wpci() { @require(__DIR__.'/atec-cache-info-dashboard.php'); }
	if (!function_exists('atec_load_pll')) @require('atec-translation.php');
	atec_load_pll(__DIR__,'cache-info');
}
?>