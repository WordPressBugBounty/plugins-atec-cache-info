<?php
if (!defined( 'ABSPATH' )) { exit; }
if (!defined('ATEC_INIT_INC')) require_once('atec-init.php');

add_action('admin_menu', function() { atec_wp_menu(__DIR__,'atec_wpci','Cache Info'); });

add_action('init', function() 
{ 
	if (!class_exists('ATEC_wp_memory')) require_once('atec-wp-memory.php');
	add_action('admin_bar_menu', 'atec_wp_memory_admin_bar', PHP_INT_MAX);
	
    if (in_array($slug=atec_get_slug(), ['atec_group','atec_wpci']))
	{
		if (!defined('ATEC_TOOLS_INC')) require_once('atec-tools.php');	
		add_action( 'admin_enqueue_scripts', function() { atec_reg_style('atec',__DIR__,'atec-style.min.css','1.0.002'); });

		if (!function_exists('atec_load_pll')) { require_once('atec-translation.php'); }
		atec_load_pll(__DIR__,'cache-info');
		
		if ($slug!=='atec_group')
		{
			function atec_wpci() { require_once('atec-cache-info-dashboard.php'); }
		}
	}	
});
?>