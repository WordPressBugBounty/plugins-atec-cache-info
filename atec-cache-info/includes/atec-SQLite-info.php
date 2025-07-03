<?php
defined('ABSPATH') || exit;

use ATEC\ALIAS;
use ATEC\TOOLS;
use ATEC\WPC;

final class ATEC_SQLite_Info {

public static function init($una, $settings)	// fake parameters
{

	global $wp_object_cache;
	
	$total 		= $wp_object_cache->cache_hits+$wp_object_cache->cache_misses+0.0000001;
	$hits 		= $wp_object_cache->cache_hits*100/$total;
	$misses 	= $wp_object_cache->cache_misses*100/$total;

	TOOLS::table_header([], '', 'summary');
		ALIAS::tr(['Version', SQLite_Object_Cache()->_version, '']);
		ALIAS::tr([__('Hits', 'atec-cache-info'), number_format($wp_object_cache->cache_hits), '<small>'.TOOLS::percent_format($hits).'</small>']);
		ALIAS::tr([__('Misses', 'atec-cache-info'), number_format($wp_object_cache->cache_misses), '<small>'.TOOLS::percent_format($misses).'</small>']);
	TOOLS::table_footer();

	WPC::hitrate($hits, $misses);

}

}
?>