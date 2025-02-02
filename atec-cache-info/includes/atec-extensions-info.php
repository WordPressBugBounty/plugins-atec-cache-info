<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_extensions_info { 
	
private function blueArray($str, $arr, $array)
{
	echo '<p class="atec-m-0"><span  class="atec-label">', esc_attr($str), ':</span> ';
	$c=0; $count=count($array);
	foreach ($array as $a) 
	{ 
		$c++; 
		if (in_array($a,$arr)) echo '<span class="atec-bold atec-green">';
		else echo '<span>';
		echo esc_attr($a);
		if ($c<$count) echo ' | ';
		echo '</span>';
	}
	echo '</p>';
}
	
function __construct() {	

atec_little_block('PHP '.__('Extensions','atec-cache-info'));

echo '
<h4 class="atec-mb-0">', esc_attr__('Installed extensions','atec-cache-info'), '</h4>
<small class="atec-mb-10 atec-db atec-green">Caching ', esc_attr__('extensions are marked green','atec-cache-info'), '.</small>
<div class="atec-border atec-bg-w atec-fit">';
	$arr=get_loaded_extensions();
	$array = array('Zend OPcache','apcu','memcached','redis','sqlite3');
	sort($arr); $c=0; $count=count($arr);
	foreach ($arr as $a) 
	{ 
		$c++; 
		echo in_array($a,$array)?'<font style="font-weight:500;" color="green">':'<font>';
		echo esc_attr($a);
		if ($c<$count) echo ' | ';
		echo '</font>';
	}
echo '
</div>
<br>
<h4 class="atec-mb-0">', esc_attr__('Recommended extensions','atec-cache-info'), '</h4>
<small class="atec-mb-10 atec-db atec-green">', esc_attr__('Installed','atec-cache-info'), ' ', esc_attr__('extensions are marked green','atec-cache-info'), '.</small>
<div class="atec-border atec-bg-w atec-fit">';
	$this->blueArray(__('Core','atec-cache-info'), $arr, array('curl', 'dom', 'exif', 'fileinfo', 'hash', 'igbinary', 'imagick', 'intl', 'mbstring', 'openssl', 'pcre', 'xml', 'zip'));
	$this->blueArray(__('Cache','atec-cache-info'), $arr, array('apcu', 'memcached', 'redis', 'Zend OPcache'));
	$this->blueArray(__('Optional','atec-cache-info'), $arr, array('bc', 'filter', 'image', 'iconv', 'shmop', 'SimpleXML', 'sodium', 'xmlreader', 'zlib'));
echo '
</div>';

}}
?>