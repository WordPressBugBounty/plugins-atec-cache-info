<?php
defined('ABSPATH') || exit;

use ATEC\CPANEL;
use ATEC\MEMORY;
use ATEC\TOOLS;

(function() {

	$una = TOOLS::una(__DIR__, 'Cache');
	CPANEL::instructions_load(__DIR__, $una);
	$una->navs = ['#memory Cache', '#server Server', '#scroll OPC '.esc_attr__('Scripts', 'atec-cache-info'), '#php PHP '.__('Extensions', 'atec-cache-info')];

	if (is_null($license_ok = TOOLS::page_header($una, 2))) return;

		MEMORY::memory_usage();

		switch ($una->nav)
		{
			case 'Cache':
				TOOLS::lazy_require(__DIR__, '/atec-wpci-cpanel.php', $una, $license_ok);
				break;

			case 'Server':
				TOOLS::lazy_require_class(__DIR__, 'atec-server-info.php', 'Server_Info', $una, $license_ok);
				break;

			case 'PHP_'.__('Extensions', 'atec-cache-info'):
				if (TOOLS::pro_feature($una, '‘Extensions’ lists every module loaded by PHP and highlights caching-related extensions against a recommended checklist.', false, $license_ok))
					TOOLS::lazy_require_class(__DIR__, 'atec-extensions-info-pro.php', 'Extensions_Info', $una, $license_ok);
				break;

			default:
				if (TOOLS::pro_feature($una, '‘OPC Scripts’ lists PHP files currently stored in OPcache memory — hits, size, last use, and time until revalidation.', false, $license_ok))
				{
					if (strpos($una->nav, 'OPC_') === 0)
						TOOLS::lazy_require_class(__DIR__, 'atec-OPC-groups-pro.php', 'OPC_Groups', $una, $license_ok);
				}
				break;
		}

	TOOLS::page_footer();
	TOOLS::loader_dots(0);   // remove this line once TOOLS is rolled out 260624

})();
