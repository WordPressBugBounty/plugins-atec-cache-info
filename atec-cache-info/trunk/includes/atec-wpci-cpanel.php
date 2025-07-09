<?php
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\TOOLS;
use ATEC\WPC;

return function($una) 
{
	global $wp_object_cache;
	
	$opc_enabled = false;
	$opc_file_only = false;
	$jit_enabled = false; 
	if (function_exists('opcache_get_configuration'))
	{
		$opc_conf=opcache_get_configuration();
		$opc_enabled= $opc_conf['directives']['opcache.enable'];
		$opc_file_only = $opc_conf['directives']['opcache.file_cache_only'];
		$opc_status = function_exists('opcache_get_status') ? opcache_get_status() : false;
		if (!$opc_status) { $jit_enabled = isset($opc_status['jit']) && $opc_status['jit']['enabled'] && $opc_status['jit']['on']; }
		else { $jitIni=ini_get('opcache.jit'); $jit_enabled = $jitIni!=0 && $jitIni!== 'disable'; }
	}
		
	$enabled = [];
	$enabled['op'] = $opc_enabled;
	$enabled['wp'] = is_object($wp_object_cache);
	$enabled['jit'] = $jit_enabled;
	$enabled['apcu'] = extension_loaded('apcu')  && apcu_enabled();
	$enabled['redis'] = class_exists('redis');
	$enabled['memcached'] = class_exists('Memcached');
	$enabled['sqlite'] = function_exists('sqlite_object_cache');

	$settings = INIT::get_settings('wpci');

	$cache_settings = [];
	$cache_settings['redis'] = $settings['redis'] ?? [];
	$cache_settings['memcached'] = $settings['memcached'] ?? [];

	if ($una->action !== '')	// set redis/memcached settings
	{
		switch ($una->action)
		{
			case 'saveRed': $a = ['type'=>'redis', 'fields'=>['conn', 'host', 'port', 'pwd']]; break;
			case 'saveMem': $a = ['type'=>'memcached', 'fields'=>['conn', 'host', 'port']]; break;
			default: $a = [];
		}

		if (!empty($a))
		{
			$type = $a['type'];
			foreach($a['fields'] as $field)
				$cache_settings[$type][$field] = TOOLS::clean_request($type.'_'.$field);
			
			$settings[strtolower($type)] = $cache_settings[$type];
			INIT::update_settings('wpci', $settings);
		}
		
		TOOLS::reg_inline_script('wpci_cache','window.history.replaceState({}, "", window.location.pathname + "?page=atec_wpci");');	// prevent re-saving
	}

	TOOLS::little_block('Zend Opcode & WP '.__('Object Cache', 'atec-cache-info'));
	
	switch ($una->action)
	{
		case 'flush':
			$result = WPC::flush_cache($una, $settings);
			break;
	}

	echo
	'<div class="atec-g atec-g-25">';

		TOOLS::loader_dots();

		foreach(['OP', 'WP', 'JIT'] as $type)
		{ WPC::cache_block(__DIR__, $una, $cache_settings, $type, $enabled); }

	echo
	'</div>';

	TOOLS::loader_dots(0);
	TOOLS::little_block(__('Persistent', 'atec-cache-info').' '.__('Object Cache', 'atec-cache-info'));
	echo
	'<div class="atec-g atec-g-25">';
	
		foreach(['APCu', 'Redis', 'Memcached', 'SQLite'] as $type)
		{ WPC::cache_block(__DIR__, $una, $cache_settings, $type, $enabled); }

	echo
	'</div>';
	
	if ($opc_file_only) TOOLS::reg_inline_script('wpci_file_only','jQuery("#OP_flush, #JIT_flush").hide();');
}
?>