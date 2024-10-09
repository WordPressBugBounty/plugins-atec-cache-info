<?php
if (!defined( 'ABSPATH' )) { exit; }

	echo '<br>';
	atec_help('opcache','Recommended settings');
	echo '
	<div id="opcache_help" class="atec-help">
		<p class="atec-bold atec-mb-5 atec-mt-0">Recommended settings:</p>
		<ul class="atec-m-0">
			<li>opcache.enable=1</li>
			<li>opcache.memory_consumption=128</li>
			<li>opcache.interned_strings_buffer=16</li>
			<li>opcache.max_accelerated_files=10000</li>
			<li>opcache.validate_timestamps=1</li>
			<li>opcache.revalidate_freq=0</li>
		</ul>
	</div>';	
?>