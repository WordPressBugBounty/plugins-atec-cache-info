<?php
/**
 * Plugin Name:  atec Cache Info
* Plugin URI: https://atecplugins.com/
* Description: Show all system caches, status and statistics (OPcache, WP-Object-Cache, JIT, APCu, Memcached, Redis, SQLite-Object-Cache).
* Version: 1.8.2
* Requires at least:4.9
* Tested up to: 6.8
* Tested up to PHP: 8.4.2
* Requires PHP: 7.4
* Requires CP: 1.7
* Premium URI: https://atecplugins.com
* Author: Chris Ahrweiler ℅ atecplugins.com
* Author URI: https://atec-systems.com/
* License: GPL2
* License URI:  https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:  atec-cache-info
*/

defined('ABSPATH') || exit;
if (!defined('ATEC_LOADER')) require __DIR__ . '/includes/ATEC/LOADER.php';

use ATEC\INIT;

INIT::set_version('wpci', '1.8.2');

if (INIT::is_real_admin()) 
{
	INIT::register_activation_deactivation_hook(__FILE__, 1, 0, 'wpci');
	add_action('admin_menu', fn() => INIT::menu(__DIR__, 'wpci', 'Cache Info'));
	add_action('admin_init', function () 
	{
		if (!INIT::current_user_can('admin')) return;
		
		if (INIT::admin_bar_option('wpci')) \ATEC\MEMORY::add_admin_bar_memory();
		
		$query = INIT::query();
		if (strpos($query, 'atec_wpci') !== false) 
		{
    		if (strpos($query, 'action=admin_bar') !== false) INIT::set_admin_bar_option('wpci');
			\ATEC\TRANSLATE::load_pll(__DIR__, 'cache-info'); 
		}
	});
}
?>