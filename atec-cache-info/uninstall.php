<?php
	if (!defined('ABSPATH')) { exit; }
	wp_cache_delete('atec_wpci_version');
	delete_option('atec_WPCI_settings');
?>