<?php
if (!defined( 'ABSPATH' )) { exit; }
  /**
  * Plugin Name:  atec Cache Info
  * Plugin URI: https://atecplugins.com/
  * Description: Show all system caches, status and statistics (OPcache, WP-object-cache, JIT, APCu, Memcached, Redis, SQLite-object-cache).
  * Version: 1.6.9
  * Requires at least: 5.2
  * Tested up to: 6.7
  * Requires PHP: 7.4
  * Author: Chris Ahrweiler
  * Author URI: https://atec-systems.com
  * License: GPL2
  * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
  * Text Domain:  atec-cache-info
  */
  
if (is_admin()) 
{ 
	wp_cache_set('atec_wpci_version','1.6.9');
	register_activation_hook( __FILE__, function() { require_once('includes/atec-wpci-activation.php'); });
	require_once('includes/atec-wpci-install.php');
}
?>