<?php
if (!defined('ABSPATH')) { exit(); }

	if (!isset($OPC_recommended)) $OPC_recommended = ['memory'=>128, 'strings'=>8, 'files'=>10000];
	atec_help('opcache',__('Recommended settings','atec-cache-info'));
	echo '
	<div id="opcache_help" class="atec-help">
		<p class="atec-bold atec-mb-5 atec-mt-0">', esc_attr__('Recommended settings','atec-cache-info'), ':</p>
		<ul>
			<li>opcache.enable=1</li>
			<li>opcache.memory_consumption=<b>', $OPC_recommended['memory'], '</b></li>
			<li>opcache.interned_strings_buffer=<b>', $OPC_recommended['strings'], '</b></li>
			<li>opcache.max_accelerated_files=<b>', $OPC_recommended['files'], '</b></li>
			<li>opcache.validate_timestamps=1</li>
			<li>opcache.revalidate_freq=60</li>
			<li>opcache.consistency_checks=0</li>
			<li>opcache.save_comments=0</li>
			<li>opcache.enable_file_override=1</li>
		</ul>',
		esc_attr__('A revalidate_freq of 0 will result in OPcache checking for updates on every request','atec-cache-info'),
		'.
	</div>';	
?>