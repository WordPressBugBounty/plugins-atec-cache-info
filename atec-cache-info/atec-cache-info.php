<?php
if (!defined('ABSPATH')) { exit(); }
  /**
  * Plugin Name:  atec Cache Info
  * Plugin URI: https://atecplugins.com/
  * Description: Show all system caches, status and statistics (OPcache, WP-Object-Cache, JIT, APCu, Memcached, Redis, SQLite-Object-Cache).
  * Version: 1.7.43
  * Requires at least:4.9
  * Tested up to: 6.7
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
  
if (is_admin()) 
{ 
	register_activation_hook(__FILE__, function() { @require('includes/atec-wpci-activation.php'); });
	
	if (!function_exists('atec_query')) @require('includes/atec-init.php');
	add_action('admin_menu', function() { atec_wp_menu(__FILE__,'atec_wpci','Cache Info'); });
	
	global $atec_active_slug;
	if (in_array($atec_active_slug=atec_get_slug(), ['atec_group','atec_wpci'])) { wp_cache_set('atec_wpci_version','1.7.43'); @require('includes/atec-wpci-install.php'); }
	
	if (get_option('atec_wpci_admin_bar'))
	{
		if (!class_exists('ATEC_wp_memory')) 
		{
			@require('includes/atec-wp-memory.php');
			add_action('admin_bar_menu', function($args) { (new ATEC_wp_memory)->atec_wp_memory_admin_bar($args); }, PHP_INT_MAX);
		}
	}
}
?>