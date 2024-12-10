<?php
if (!defined( 'ABSPATH' )) { exit; }
if (!defined('ATEC_TOOLS_INC')) @require_once(__DIR__.'/atec-tools.php');	

add_action( 'admin_enqueue_scripts', function() { atec_reg_style('atec',__DIR__,'atec-style.min.css','1.0.002'); });

if ($atec_active_slug!=='atec_group')
{
	function atec_wpci() { @require_once(__DIR__.'/atec-cache-info-dashboard.php'); }
	
	if (!function_exists('atec_load_pll')) { @require_once('atec-translation.php'); }
	atec_load_pll(__DIR__,'cache-info');
}
?>