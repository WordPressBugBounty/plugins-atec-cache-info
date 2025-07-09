<?php
defined('ABSPATH') || exit;

use ATEC\MEMORY;
use ATEC\TOOLS;

(function() {

	$una = TOOLS::una(__DIR__, 'Cache');
	$una->navs = ['#memory Cache', '#server Server', '#scroll OPC '.esc_attr__('Scripts', 'atec-cache-info'), '#php PHP '.__('Extensions', 'atec-cache-info')];

	if (is_null( $license_ok = TOOLS::page_header($una,2) )) return;

		MEMORY::memory_usage();

		switch ($una->nav)
		{
			case 'Cache':
				TOOLS::lazy_require(__DIR__, '/atec-wpci-cpanel.php', $una);
				break;

			case 'Server':
				require(__DIR__.'/atec-server-info.php'); 
				break;
				
				
			case 'PHP_'.__('Extensions', 'atec-cache-info'):
				if (TOOLS::pro_feature($una, '„Extension“ lists all active PHP extensions and checks whether recommended extensions are installed', false, $license_ok))
					TOOLS::lazy_require_class(__DIR__, 'atec-extensions-info-pro.php', 'Extensions_Info');
				break;
				
			default:
				if (strpos($una->nav, 'OPC_')===0)
				{
					if (TOOLS::pro_feature($una, '„OPC Scripts“ lists all scripts files and statistics of in the OPcache memory', false, $license_ok))
						TOOLS::lazy_require_class(__DIR__, 'atec-OPC-groups-pro.php', 'OPC_Groups', $una);
				}
				break;
		}

	TOOLS::page_footer();

})();
?>