<?php
namespace ATEC;
defined('ABSPATH') || exit;

final class MEMORY {
	
	private static $wp_memory_limit = null;
	private static $memory_admin_bar = null;

	public static function KMG_2_Int($string): int
	{
		sscanf(strtoupper($string), '%u%c', $number, $suffix);
		$number = (int) $number ?: 0;
		if (isset ($suffix)) { $number = $number * pow (1024, strpos(' KMG', strtoupper($suffix))); }
		return (int) $number;
	}

	public static function wp_memory_limit(): int
	{
		if (self::$wp_memory_limit !== null) return self::$wp_memory_limit;

		self::$wp_memory_limit = defined('WP_MEMORY_LIMIT')?self::KMG_2_Int(WP_MEMORY_LIMIT):41943040;
		return self::$wp_memory_limit;
	}
	
	public static function add_admin_bar_memory()
	{ 
		add_action('admin_bar_menu', function($wp_admin_bar) 
		{ 
			\ATEC\MEMORY::admin_bar_memory($wp_admin_bar); 
		}, 999); 
	}

	public static function admin_bar_memory($wp_admin_bar): void
	{
		if (self::$memory_admin_bar !== null) return;
		self::$memory_admin_bar = true;

		if (function_exists('memory_get_peak_usage'))
		{
			$mega		= 1048576;
			$peak		= memory_get_peak_usage(true);
			$avail		= self::wp_memory_limit();
			$percent	= $peak/$avail*100;

			$wp_admin_bar->add_node([
				'id'		=> 'atec_memory_admin_bar',
				'meta'	=> [ 'title' => sprintf('%s of %s MB used.', round($peak/$mega), round($avail/$mega)) ],
				'title'		=> '<div class="atec-admin-bar-row">'.
									\ATEC\SVG::plain('memory_white') . 
									'<span style="color:'.($percent<75?'lightgreen' : 'lightcoral').'">'.round($percent,1).
										'<span style="font-size:8px;"> %</span>'.
									'</span>'.
								'</div>',
			]);
		}
	}

	public static function memory_usage(): void
	{
		if (function_exists('memory_get_peak_usage'))
		{
			$peak			= memory_get_peak_usage(true);
			$percent		= round($peak/self::wp_memory_limit()*100,1);

			preg_match('/([\d]+)\s?([\w]+)/', size_format($peak), $match);
			preg_match('/([\d]+)\s?([\w]+)/', size_format(self::wp_memory_limit()), $match2);

			if (isset($match[2]) && isset($match2[2]))
			{
				$plugin_img_url = plugins_url('', dirname(__DIR__)).'/assets/img';
				echo 
				'<div class="button atec-sticky-left atec-inner-notice-left">';
					\ATEC\SVG::echo('memory');
					echo esc_attr($match[1]).'<span class="atec-fs-8"> '.esc_attr($match[2]).' </span>',
						'<span>≈</span><span class="atec-bold atec-', ($percent<25?'green':($percent<75?'orange' : 'red')), '">', esc_attr($percent), '</span>',
						'<span class="atec-fs-8">%</span>',
				'</div>';
			}
		}
	}

}
?>