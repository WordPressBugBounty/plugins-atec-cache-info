<?php
defined('ABSPATH') || exit;

use ATEC\TOOLS;

echo 
'<div class="atec-mt-5">';

	TOOLS::help(__('Recommended settings', 'atec-cache-info'),
	'<ul>
		<li>opcache.jit=1254</li>
		<li>opcache.jit_buffer_size=8M</li>
	</ul>');
	
echo 
'</div>';
?>