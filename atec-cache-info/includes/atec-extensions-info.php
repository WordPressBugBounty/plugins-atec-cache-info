<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_extensions_info { 
	
private function blueArray($str, $arr, $array)
{
	echo '<p class="atec-m-0"><span  class="atec-label">', esc_attr($str), ':</span> ';
	$c=0; $count=count($array);
	foreach ($array as $a) 
	{ 
		$c++; 
		if (in_array($a,$arr)) echo '<span class="atec-bold atec-blue">';
		else echo '<span>';
		echo esc_attr($a);
		if ($c<$count) echo ' | ';
		echo '</span>';
	}
	echo '</p>';
}
	
function __construct() {	

atec_little_block('PHP '.__('Extensions','atec-cache-info'));

echo '<div class="atec-border atec-mb-10">
<h4>'.esc_attr__('Installed extensions','atec-cache-info').' (<font style="font-weight:500;" color="green">'.esc_attr__('cache','atec-cache-info').'</font>):</h4>';

	$arr=get_loaded_extensions();
	$array = array('Zend OPcache','apcu','memcached','redis','sqlite3');
	sort($arr); $c=0; $count=count($arr);
	foreach ($arr as $a) 
	{ 
		$c++; 
		if (in_array($a,$array)) echo '<font style="font-weight:500;" color="green">';
		else echo '<font>';
		echo esc_attr($a);
		if ($c<$count) echo ' | ';
		echo '</font>';
	}
echo '
</div>
<div class="atec-border atec-mb-10">
	<h4>'.esc_attr__('Recommended extensions for WordPress','atec-cache-info').' (<span class="atec-blue">'.esc_attr__('installed','atec-cache-info').'</span>):</h4>';
		
	$this->blueArray('Core', $arr, array('curl', 'dom', 'exif', 'fileinfo', 'hash', 'igbinary', 'imagick', 'intl', 'mbstring', 'openssl', 'pcre', 'xml', 'zip'));
	$this->blueArray('Cache', $arr, array('apcu', 'memcached', 'redis', 'Zend OPcache'));
	$this->blueArray('Optional', $arr, array('bc', 'filter', 'image', 'iconv', 'shmop', 'SimpleXML', 'sodium', 'xmlreader', 'zlib'));
	
echo '
</div>';

}}
new ATEC_extensions_info();
?>