<?php
if (!defined( 'ABSPATH' )) { exit; }
  /**
  * Plugin Name:  atec Cache Info
  * Plugin URI: https://atecplugins.com/
  * Description: Show all system caches, status and statistics (OPcache, WP-object-cache, JIT, APCu, Memcached, Redis, SQLite-object-cache).
  * Version: 1.7.5
  * Requires at least: 5.2
  * Tested up to: 6.7.1
  * Requires PHP: 7.4
  * Author: Chris Ahrweiler
  * Author URI: https://atec-systems.com
  * License: GPL2
  * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
  * Text Domain:  atec-cache-info
  */
  
if (is_admin()) 
{ 
	wp_cache_set('atec_wpci_version','1.7.5');
	register_activation_hook( __FILE__, function() { @require_once(__DIR__.'/includes/atec-wpci-activation.php'); });
	
	if (!defined('ATEC_INIT_INC')) @require_once('includes/atec-init.php');
	add_action('admin_menu', function() { atec_wp_menu(__FILE__,'atec_wpci','Cache Info'); });
	
	global $atec_active_slug;
	if (in_array($atec_active_slug=atec_get_slug(), ['atec_group','atec_wpci'])) @require_once(__DIR__.'/includes/atec-wpci-install.php');
	
	global $atec_wp_memory_admin_bar;
	if (!($atec_wp_memory_admin_bar??false))
	{
		if (!class_exists('ATEC_wp_memory')) @require_once('includes/atec-wp-memory.php');
		add_action('admin_bar_menu', 'atec_wp_memory_admin_bar', PHP_INT_MAX);
	}
}
?>