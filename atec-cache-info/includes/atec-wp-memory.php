<?php
if (!defined('ABSPATH')) { exit(); }

define('ATEC_WP_MEM_LIMIT', function_exists('atec_wp_memory_limit')?atec_wp_memory_limit():(defined('WP_MEMORY_LIMIT')?wp_convert_hr_to_bytes(WP_MEMORY_LIMIT):41943040));

function atec_wp_memory_admin_bar($wp_admin_bar): void
{
	if (function_exists('memory_get_peak_usage')) 
	{ 
		$peak 			= memory_get_peak_usage(true);
		$percent 		= $peak/ATEC_WP_MEM_LIMIT*100;
		// @codingStandardsIgnoreStart | Image is not an attachement
		$args = ['id' => 'atec_memory_admin_bar', 
						  'title' => '
						  <span style="font-size:12px;">
							  <img title="Memory usage" src="'.esc_url(plugins_url( '/assets/img/icons/memory_white.svg', __DIR__ )).'" style="vertical-align: bottom; height:14px; margin:8px 4px 10px 0;"><span style="color:'.($percent<75?'lightgreen':'lightcoral').'">'.round($percent,1).'<span style="font-size:8px;"> %</span></span>
						</span>'];
		// @codingStandardsIgnoreEnd
		$wp_admin_bar->add_node($args);
	}
}

class ATEC_wp_memory
{
	public function memory_usage(): void
	{
		if (function_exists('memory_get_peak_usage')) 
		{ 
			$peak			= memory_get_peak_usage(true);
			$percent		= round($peak/ATEC_WP_MEM_LIMIT*100,1);
			
			preg_match('/([\d]+)\s?([\w]+)/', size_format($peak), $match);
			preg_match('/([\d]+)\s?([\w]+)/', size_format(ATEC_WP_MEM_LIMIT), $match2);
			
			switch (strtolower(PHP_OS_FAMILY))
			{
				case 'Darwin': $icon='apple'; break;
				case 'Windows': $icon='windows'; break;
				case 'Linux': $icon='linux'; break;
			}
			
			if (isset($match[2]) && isset($match2[2]))
			{
				echo '
				<div class="atec-sticky-left">';
					// @codingStandardsIgnoreStart | Image is not an attachement
					if (isset($icon)) echo '<img alt="', esc_attr(PHP_OS_FAMILY), '" src="', esc_url(plugins_url( '/assets/img/system/'.$icon.'-icon.svg', __DIR__ )), '" class="atec-sys-icon" style="height:16px;">';
					echo '<img alt="Memory usage" src="', esc_url(plugins_url( '/assets/img/icons/memory.svg', __DIR__ )), '" class="atec-vam" style="height:14px; padding-right: 4px;">', 
						esc_attr($match[1]).'<span class="atec-fs-8"> '.esc_attr($match[2]).' / </span>',
							esc_attr($match2[1]).'<span class="atec-fs-8"> '.esc_attr($match2[2]).' </span>',
						' ≈ <span class="atec-bold atec-', ($percent<25?'green':($percent<75?'orange':'red')),'">', esc_attr($percent), ' <span class="atec-fs-8">%</span></span> ', esc_attr__('used','atec-cache-info');
					// @codingStandardsIgnoreEnd
				echo '
				</div>';
			}
		}
	}
}
?>