<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_fixit { 

private function atec_version_compare($a, $b) { return explode(".", $a) <=> explode(".", $b); }

public function atec_fixit($dir,$p,$slug,$option=null)
{
	$optName = 'atec_fix_it';
	if (!$option) $option = get_option($optName,[]);
	$ver = wp_cache_get('atec_'.$slug.'_version');
	if ($this->atec_version_compare($option[$p]??0,$ver)===-1)
	{ 
		$include = $dir.'/fixit.php';
		// @codingStandardsIgnoreStart
		if (@file_exists($include)) @require($include); 
		// @codingStandardsIgnoreEnd
		$option[$p]=$ver; 
		update_option($optName,$option); 	
	}
}

}
?>