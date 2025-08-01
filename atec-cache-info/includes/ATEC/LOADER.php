<?php
/**
* ATEC Framework LOADER
* Loads ATEC classes from includes/ATEC/*.php
* Registers PSR-4-style autoloading for ATEC\* classes in the includes/ATEC/ directory.
* Only loads once across all plugins
* Version: 1.0.1
*/
namespace ATEC;
defined('ABSPATH') || exit;
if (!defined('ATEC_LOADER')) define('ATEC_LOADER', '1.0.1');

final class LOADER {
	
static $class_map = // must have classes
[
	'ATEC\\DASHBOARD'	=> __DIR__ . '/DASHBOARD.php',
	'ATEC\\FS'					=> __DIR__ . '/FS.php',
	'ATEC\\GROUP'				=> __DIR__ . '/GROUP.php',
	'ATEC\\INFO'				=> __DIR__ . '/INFO.php',
	'ATEC\\INIT'					=> __DIR__ . '/INIT.php',
	'ATEC\\LICENSE'			=> __DIR__ . '/LICENSE.php',
	'ATEC\\SVG'					=> __DIR__ . '/SVG.php',
	'ATEC\\TOOLS'				=> __DIR__ . '/TOOLS.php',
];
	
private static function normalize_path($path) 
{
	return str_replace('\\', '/', $path);
}

private static function get_plugin_base_root($path) 
{
	return dirname(str_replace('/includes/ATEC/', '/', $path));
}

public static function autoload($class)
{
	$GLOBALS['atec_plugins_globals'] = [
		'WP_PLUGIN_URL'	=> self::get_plugin_base_root(plugin_dir_url(__FILE__)),
		'WP_PLUGIN_DIR'	=> self::get_plugin_base_root(plugin_dir_path(__FILE__)),
	];
	
	$WP_PLUGIN_DIR = $GLOBALS['atec_plugins_globals']['WP_PLUGIN_DIR'];

	static $loaded = [];
	self::$class_map['ATEC\\WPCA'] = $WP_PLUGIN_DIR . '/atec-cache-apcu/includes/ATEC/WPCA.php';
	self::$class_map['ATEC\\WPMC'] = $WP_PLUGIN_DIR . '/mega-cache/includes/ATEC/WPMC.php';
	
	if (strpos($class, 'ATEC\\') !== 0) return;		// Skip if not an ‘atec’ class	
	if (isset($loaded[$class])) return;					// Skip if class already loaded

	if (isset($class_map[$class])) 						// Load from known map
	{
		require $class_map[$class]; 
		$loaded[$class] = true;
		return; 
	}
	
	$relative_class = substr($class, 5);
	//$relative_class = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 5));	//if class files in nested directories
	$default_path = __DIR__ . '/'. $relative_class . '.php';

	if (file_exists($default_path)) 
	{
		require $default_path; 
		$loaded[$class] = true;
		return; 
	}

	// Optional fallback
	$ex = new \Exception();
	$caller = $ex->getTrace();
	
	$caller_path = '';
	foreach($caller as $call)
	{
		if (isset($call['file'])) { $caller_path=$call['file']; break; }
	}
	
	if ($caller_path !== '')
	{
		$caller_path = self::normalize_path($caller_path);
		$wp_plugin_dir = self::normalize_path($WP_PLUGIN_DIR);

		preg_match('#'.preg_quote($wp_plugin_dir, '#').'/([^/]+)#', $caller_path, $match);
		if (isset($match[1]))
		{
			$local_path = self::normalize_path($wp_plugin_dir . '/' . $match[1] . '/includes/ATEC/' . $relative_class. '.php');
			if (file_exists($local_path)) 
			{
				require $local_path;
				$loaded[$class] = true;
				return;
			}
		}
	}
}

}

\spl_autoload_register(['ATEC\\LOADER', 'autoload']);
if (\PHP_VERSION_ID < 80000) @require __DIR__.'/POLYFILL.php';

// Register global AJAX handler for notice dismiss
if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && $_REQUEST['action'] === 'atec_admin_notice_dismiss') 	// phpcs:ignore
{
	add_action('wp_ajax_atec_admin_notice_dismiss', ['ATEC\\INIT', 'dismiss_notice']); 
}
else
{
	if (\ATEC\INIT::is_real_admin()) \ATEC\INIT::admin_debug_all();
}
?>