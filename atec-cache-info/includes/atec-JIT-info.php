<?php
defined('ABSPATH') || exit;

use ATEC\MEMORY;
use ATEC\TOOLS;
use ATEC\WPC;

final class ATEC_JIT_Info {

public static function init($una, $settings)	// fake parameters
{

	$opc_status = function_exists('opcache_get_status') ? opcache_get_status() : false;

	$percent=false;
	if ($opc_status)
	{
		$jit_size 	= $opc_status['jit']['buffer_size'];
		$jit_free	= $opc_status['jit']['buffer_free'];
		$percent	= $jit_free/($jit_size+0.0001);
	}
	else
	{
		$iniSize = ini_get('opcache.jit_buffer_size');
		$jit_size = MEMORY::KMG_2_Int($iniSize);
	}

	TOOLS::table_header([], '', 'bold');
		TOOLS::table_tr(['JIT '.__('config', 'atec-cache-info').':', ini_get('opcache.jit'), '']);
		TOOLS::table_tr(['Debug:', ini_get('opcache.jit_debug'), '']);
		TOOLS::table_tr();
		TOOLS::table_tr([__('Memory', 'atec-cache-info').':', TOOLS::size_format($jit_size), '']);
		if ($percent) TOOLS::table_tr([__('Used', 'atec-cache-info').':', TOOLS::size_format($jit_size-$jit_free), TOOLS::percent_format($percent)]);
	TOOLS::table_footer();

	if ($percent) WPC::usage($percent);
		
	if (extension_loaded('xdebug') && strtolower(ini_get('xdebug.mode')) !== 'off') 
		TOOLS::msg(false, 'Xdebug '.__('is enabled, so JIT will not work', 'atec-cache-info'));

}

}
?>