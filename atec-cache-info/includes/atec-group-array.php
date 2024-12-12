<?php
if (!defined( 'ABSPATH' )) { exit; }
	
$atec_group_arr = 
[
	['slug'=>'wpb', 'name'=>'backup','desc'=>__('All-in-one Backup and restore solution – fast & reliable','atec-cache-info'),'pro'=>'FTP storage (FTP & SSH)','wp'=>false],
	['slug'=>'wpbn', 'name'=>'banner','desc'=>__('Temporary site banner with auto-hide feature','atec-cache-info'),'pro'=>'./.','wp'=>false],
	['slug'=>'wpca', 'name'=>'cache-apcu','desc'=>__('APCu object and page cache','atec-cache-info'),'pro'=>'Advanced page cache','wp'=>true],
	['slug'=>'wpci', 'name'=>'cache-info','desc'=>__('Cache Info & Statistics (OPcache, WP-, APCu-, Memcached-, Redis-, SQLite-object-cache & JIT)','atec-cache-info'),'pro'=>'PHP extensions','wp'=>true],
	['slug'=>'wpc', 'name'=>'code','desc'=>__('Custom code snippets for WP','atec-cache-info'),'pro'=>'PHP-snippets','wp'=>false],
	
	['slug'=>'wpdb', 'name'=>'database','desc'=>__('Optimize WP database tables','atec-cache-info'),'pro'=>'Cleanup comments, posts, revisions, transients and options','wp'=>true],
	['slug'=>'wpd', 'name'=>'debug','desc'=>__('Show debug log in admin bar','atec-cache-info'),'pro'=>'Show queries, includes and wp-config.php; manage cron jobs','wp'=>true],
	['slug'=>'wpdp', 'name'=>'deploy','desc'=>__('Install and auto update `atec´ plugins','atec-cache-info'),'pro'=>'./.','wp'=>false],
	['slug'=>'wpds', 'name'=>'dir-scan','desc'=>__('Dir Scan & Statistics (Number of files and size per directory)','atec-cache-info'),'pro'=>'Deep scan for folder sizes','wp'=>true],
	['slug'=>'wpht', 'name'=>'htaccess','desc'=>__('Optimize the webserver /.htaccess file to increase the performance of your site','atec-cache-info'),'pro'=>'./.','wp'=>false],
	
	['slug'=>'wpll', 'name'=>'limit-login','desc'=>__('Limit login attempts to prevent brute-force attacks','atec-cache-info'),'pro'=>'Attack statistics','wp'=>false],
	['slug'=>'wpm', 'name'=>'meta','desc'=>__('Add custom meta tags to the head section','atec-cache-info'),'pro'=>'Automatically add description tag per page','wp'=>false],
	['slug'=>'wpo', 'name'=>'optimize','desc'=>__('Lightweight performance tuning plugin','atec-cache-info'),'pro'=>'Enable performance and WooCommerce tweaks','wp'=>false],
	['slug'=>'wppp', 'name'=>'page-performance','desc'=>__('Measure the PageScore and SpeedIndex of your WordPress site','atec-cache-info'),'pro'=>'./.','wp'=>false],
	['slug'=>'wppo', 'name'=>'poly-addon','desc'=>__('Custom translation strings for polylang plugin','atec-cache-info'),'pro'=>'./.','wp'=>false],
	
	['slug'=>'wppr', 'name'=>'profiler','desc'=>__('Measure plugins & theme plus pages execution time','atec-cache-info'),'pro'=>'Monitor page performance and queries','wp'=>false],
	['slug'=>'wpsh', 'name'=>'shell','desc'=>__('Connect to a remote server via SSH','atec-cache-info'),'pro'=>'./.','wp'=>false],
	['slug'=>'wpsm', 'name'=>'smtp-mail','desc'=>__('Add custom SMTP mail settings to WP_Mail','atec-cache-info'),'pro'=>'DKIM support and test; SPAM filter','wp'=>false],
	['slug'=>'wps', 'name'=>'stats','desc'=>__('Lightweight and GDPR compliant WP statistics','atec-cache-info'),'pro'=>'Statistics on a world map','wp'=>true],
	['slug'=>'wpsi', 'name'=>'system-info','desc'=>__('System Information (OS, server, memory, PHP info and more)','atec-cache-info'),'pro'=>'List PHP-extensions & system variables; Show the php.ini, wp-config.php & .htaccess files','wp'=>true],
	
	['slug'=>'wpsv', 'name'=>'svg','desc'=>__('Adds SVG support for media uploads.','atec-cache-info'),'pro'=>'./.','wp'=>false],
	['slug'=>'wpta', 'name'=>'temp-admin','desc'=>__('Create temporary admin accounts for maintenance purposes','atec-cache-info'),'pro'=>'./.','wp'=>false],
	['slug'=>'wpur', 'name'=>'user-roles','desc'=>__('Manage WordPress User Roles and Capabilities','atec-cache-info'),'pro'=>'List and manage users','wp'=>false],
	['slug'=>'wms', 'name'=>'web-map-service','desc'=>__('Web map, conform with privacy regulations','atec-cache-info'),'pro'=>'Discount on atecmap.com API key','wp'=>true],
	['slug'=>'wpwp', 'name'=>'webp','desc'=>__('Auto convert all images to WebP format','atec-cache-info'),'pro'=>'PNG, GIF and BMP support','wp'=>true],
	
	['slug'=>'wpmc', 'name'=>'mega-cache','desc'=>__('Ultra fast page cache to improve site speed.','atec-cache-info'),'pro'=>'Multiple storage options: APCu, Redis, Memcached, SQLite, MongoDB, MariaDB, MySQL','wp'=>true],
];
	
?>