<?php
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\TOOLS;

/** Tab-keyed Instructions renderers — keys must match $una->nav. */
$nav_key = static function (string $label): string {
	return str_replace(['(', ')'], '', str_replace([' ', '.', '-', '/'], '_', $label));
};

$map = [
	'Cache' => static function () {

		TOOLS::div('box',
			'<strong>atec Cache Info</strong> shows whether PHP opcode cache, WordPress object cache, and persistent backends (APCu, Redis, Memcached, SQLite) are active — plus usage stats and one-click flush where supported.'
		);

		TOOLS::ul('How to use', [
			'Green badges mean that layer is enabled; grey means missing or off.',
			'Use the trash button on a block to flush that cache layer only.',
			'<strong>WP Site</strong> clears the in-request WordPress object cache; <strong>All</strong> on other blocks clears the whole backend.',
			'For Redis and Memcached, save connection settings in each block before flushing or reading stats.',
		]);

		TOOLS::ul('What each block shows', [
			'<strong>OP / JIT</strong> — Zend OPcache and JIT compiler status.',
			'<strong>WP</strong> — WordPress default or drop-in object cache.',
			'<strong>APCu, Redis, Memcached, SQLite</strong> — persistent object-cache extensions.',
		]);

		TOOLS::ul('Tips', [
			'Flush OPcache after deploying PHP changes; on shared hosting that may affect every site on the same PHP worker.',
			'If Redis or Memcached stats stay empty, confirm the extension is loaded and credentials match your server.',
			'Pair with dedicated cache plugins (Redis Object Cache, Memcached, etc.) — this panel reports status; it does not replace them.',
			'Use the <strong>Server</strong> tab for memory limits and PHP settings; <strong>PHP Extensions</strong> (PRO) lists installed modules.',
		]);

		TOOLS::div(-1);
	},

	'Server' => static function () {

		$unlimited = INIT::slug() === 'atec_wpsi';

		TOOLS::div('box',
			'<strong>Server Info</strong> summarises the operating system, web server, PHP memory limits, and key upload or execution settings for this WordPress install.'
		);

		TOOLS::ul('What you see', [
			'<strong>Operating system</strong> — OS version, architecture, site timezone, and disk space on the WordPress volume.',
			'<strong>Server</strong> — hostname, IP, optional geo lookup, web server software, and cURL version.',
			'<strong>Memory</strong> — system RAM (when available), PHP and WordPress memory limits, and current peak usage.',
			'<strong>PHP Settings</strong> — max execution time, input vars, and upload/post size limits.',
		]);

		if ($unlimited) {
			TOOLS::ul('Extended panels', [
				'WordPress root path and install size.',
				'WordPress, PHP, and database versions.',
				'Database connection limits, packet size, and table/index footprint.',
			]);
		}

		TOOLS::ul('Tips', [
			'Compare PHP <strong>memory_limit</strong> with <strong>WP max. mem.</strong> when large imports or page builders fail.',
			'Low <strong>max. input vars</strong> often breaks menus or big option screens — 3000+ is common on busy sites.',
			'Disk free percentage helps spot full partitions before backups or updates fail.',
		]);

		TOOLS::div(-1);
	},
];

$map[$nav_key('OPC ' . __('Scripts', 'atec-cache-info'))] = static function () {

	TOOLS::div('box',
		'<strong>OPC Scripts</strong> (PRO) lists PHP files currently stored in OPcache memory — hits, size, last use, and time until revalidation.'
	);

	TOOLS::ul('Requirements', ['PRO license.']);

	TOOLS::ul('How to use', [
		'Browse the table to see which scripts consume OPcache memory and how often they run.',
		'Use <strong>Scan root folder for PHP scripts</strong> to count <code>.php</code> files under your site root — set <code>opcache.max_accelerated_files</code> above that total.',
		'Red rows may indicate scripts outside your account path on shared hosting.',
	]);

	TOOLS::ul('Tips', [
		'Large plugins or many small files can fill OPcache — flush OPcache on the <strong>Cache</strong> tab after major updates if behaviour looks stale.',
		'If you see a foreign-script warning, ask your host about OPcache isolation (<code>opcache.validate_permission</code> and <code>opcache.validate_root</code>).',
		'Violet-highlighted keys belong to atec plugins.',
	]);

	TOOLS::div(-1);
};

$map[$nav_key('PHP ' . __('Extensions', 'atec-cache-info'))] = static function () {

	TOOLS::div('box',
		'<strong>PHP Extensions</strong> (PRO) lists every module loaded by PHP and highlights caching-related extensions against a recommended checklist.'
	);

	TOOLS::ul('Requirements', ['PRO license.']);

	TOOLS::ul('How to use', [
		'Compare <strong>Installed extensions</strong> with the <strong>Recommended</strong> groups — green names are present on this server.',
		'Focus on the <strong>Cache</strong> row when tuning object cache or opcode cache.',
		'Missing core extensions (curl, openssl, mbstring, etc.) often explain plugin or REST API failures.',
	]);

	TOOLS::ul('Tips', [
		'Enable extensions in php.ini or your host panel, then reload this tab — PHP must restart to pick up new modules.',
		'Some hosts disable functions like <code>opcache_get_status</code> even when OPcache is on — check the <strong>Cache</strong> tab for runtime status.',
		'Ask your host before installing PECL modules yourself on managed hosting.',
	]);

	TOOLS::div(-1);
};

return $map;
