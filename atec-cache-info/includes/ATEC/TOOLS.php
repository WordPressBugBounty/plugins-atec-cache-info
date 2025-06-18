<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\PRO;

final class TOOLS
{

// TOOLS AREA START

public static function on_off($bool, $reverse = false): string 
{
	$label = $bool ? 'On' : 'Off';
	if ($reverse) { $class = $bool ? 'red' : 'green'; }
	else { $class = $bool ? 'green' : 'red'; }

	return '<span class="atec-'.$class.'">'.$label.'</span>';
}

public static function gmdate($ts, $format= 'm/d H:i') 
{
	if (!is_numeric($ts)) $ts = strtotime($ts); // Convert date string to timestamp
	return gmdate($format, $ts); 
}

public static function progress_percent($id)
{
	echo
	'<div class="atec-border atec-percent-block" style="width:260px; background:rgb(250, 250, 250);">',
		'<div class="atec-dilb atec-fs-12">Progress</div><div class="atec-dilb atec-float-right atec-fs-12">100%</div><br>',
		'<div class="atec-percent-div" style="width:250px; background: rgb(235, 235, 235);">',
			'<span  id="atec_progress_percent_', esc_attr($id), '" style="background-color:lightgreen;"></span>',
		'</div>',
	'</div>';
}

public static function lazy_require_class(string $dir, string $path, string $class= '', mixed ...$args): bool
{
	$full_path = "$dir/$path";
	if (is_file($full_path)) 
	{
		require $full_path;
		if ($class!= '')
		{
			if (!str_contains($class, 'ATEC_')) $class = 'ATEC_'.$class;	// This will add class name or use full class if passed like "ATEC_WPC"
			if (class_exists($class)) 
			{
				if (method_exists($class, 'init')) $class::init(...$args);
				else new $class(...$args);
				return true; 
			}
		}
	}
	if (str_ends_with($path,'-pro.php')) self::pro_missing();
	return false;
}

public static function lazy_require(string $dir, string $path, mixed ...$args): void
{
	$full_path = "$dir/$path";
	if (is_file($full_path)) 
	{
		$callable = require $full_path;
		if (is_callable($callable)) { $callable(...$args); }
	}
}

private static function br($str): void
{
	$str = str_replace('<br>', "\n", $str);
	$ex = explode("\n", $str);
	$last = count($ex) - 1;

	foreach ($ex as $index => $t)
	{
		echo esc_html($t);
		if ($index < $last) echo '<br>';
	}
}

public static function percent_format($value, $decimals = 1) 
{
	return sprintf("%.{$decimals}f", $value) . '<small> %</small>';
}

public static function td_size_format($bytes, $decimals = 0, $class='') 
{
	if (!$bytes) { echo '<td>-/-</td>'; return; }
	echo '<td class="atec-nowrap atec-right', $class==='' ? '' : ' '.esc_html($class), '">';
		echo wp_kses_post(self::size_format($bytes, $decimals));
	echo '</td>';
}

public static function size_format($bytes, $decimals = 0) 
{
	$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
	$bytes = max(0, $bytes);

	$pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
	$pow = min($pow, count($units) - 1);

	$value = $bytes / pow(1024, $pow);

	// Format number with fixed decimal places
	$formatted = number_format($value, $decimals);

	// Wrap unit in <small> tag
	return $formatted . ' <small>' . esc_html($units[$pow]) . '</small>';
}

public static function abspath()
{
	static $cached = null;
	if ($cached === null) { $cached = function_exists('get_home_path') ? @get_home_path() : ABSPATH; }
	return $cached;
}

public static function clear()
{ echo '<br class="atec-clear">'; }

public static function una($dir, $nav_default = '')
{
	$nonce = INIT::nonce();
	$nav = self::clean_request('nav', $nonce);
	
	if ($nav_default=== '') $nav_default = 'Dashboard';
	if ($nav=== '') $nav= $nav_default;
	$navs = $nav_default === 'Dashboard' ? ['#admin-home Dashboard'] : ($nav_default === 'Settings'?['#admin-generic Settings']:[]);

	$arr =	
	[
		'dir'		=> dirname($dir),
		'url' 		=> self::url(),
		'nonce' 	=> wp_create_nonce($nonce),
		'action' 	=> self::clean_request('action',$nonce),
		'nav' 		=> $nav,
		'navs' 	=> $navs,
		'id'		=> self::clean_request('id',$nonce),
		'slug' 	=> str_replace('atec_', '',  INIT::slug())
	];

	return (object) $arr;
}

public static function p($str= '', $class= ''): void
{ echo '<p class="atec-mb-'.($str!== '' ? '0' : '5'), esc_html($class=== '' ? '' : ' '.$class) , '">', esc_html($str!== '' ? INIT::trailingdotit($str) : '&nbsp;'), '</p>'; }

public static function enabled($enabled, $active=false): void
{
	echo
	'<span style="color:', ($enabled?($active?'black':'green'):'red'), '" title="', $enabled ? 'Enabled' : 'Disabled', '" ',
		'class="', esc_attr(self::dash_class($enabled?'yes-alt':'dismiss')), '">',
	'</span>';
}

public static function random_string($length, $lower=false): string
{
	$charset = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ'; $string = '';
	while(strlen($string)<$length) { $string .= substr($charset,random_int(0,61),1); }		// phpcs:ignore
	return $lower?strtolower($string):$string;
}

public static function short_string($str, $len=128): string
{
	if ($str== '') return $str;
	return strlen($str)>$len?substr($str, 0, $len).' ...':$str;
}

public static function format_duration($seconds)
{
	$dtF = new \DateTime('@0');
	$dtT = new \DateTime();
	$dtT->setTimestamp((int) $seconds);
	$diff = $dtF->diff($dtT);

	$days = (int) $diff->format('%a');
	$time = $diff->format('%H:%I');

	if ($days > 0)
	{
		$label = $days === 1 ? 'day' : 'days';
		return "$days $label, $time";
	}

	return $time;
}


public static function url(): string
{
	static $cached = null;
	if ($cached === null) $cached = strtok(rtrim(add_query_arg($_GET, admin_url('admin.php')), '/'), '&');	// phpcs:ignore
	return $cached;
}

// TOOLS AREA START

// PROGRESS AREA START

public static function loader_div($id='', $str=''): void
{
	echo '<div id="', esc_attr($id), '" class="atec-badge atec-bg-w atec-bold atec-mt-10 atec-mb-5">', esc_html($str), ' '; 
		self::loader_dots(); 
	echo '<br></div>';
	self::flush();
}

public static function loader_dots(int $count = 9): void
{
	echo '<div class="atec-loader-dots atec-dilb">';
	for ($i = 1; $i <= $count; $i++) echo "<span style='--i:$i'></span>";	// phpcs:ignore
	echo '</div>';
	self::flush();
}

private static function progress_div(): void
{
	echo '<div id="atec_loading" class="atec-progress"><div class="atec-progressBar"></div></div>';
	self::reg_inline_script('progress', 'setTimeout(()=>{ jQuery("#atec_loading").css("opacity",0); },4500);', true);
}

public static function progress(): void { ob_start(); self::flush(); }

public static function flush(): void
{
	while (ob_get_level() > 0)
	{
		$status = ob_get_status();
		if (!empty($status['name']) && str_contains($status['name'], 'gzhandler')) { break; }	// gzhandler buffers often trigger: "Failed to send buffer of zlib output compression"
		@ob_end_flush();
	}
	@flush();
}

// PROGRESS AREA START

// DASH AREA START

private static $dashArr = ['admin-comments', 'admin-generic', 'admin-home', 'admin-plugins', 'admin-settings', 'admin-site', 'admin-tools', 'analytics', 'archive', 'awards', 'backup', 'businessman', 'clipboard', 'code-standards', 'controls-play', 'cover-image', 'database', 'editor-code', 'editor-removeformatting', 'editor-table', 'forms', 'groups', 'hourglass', 'info', 'insert', 'list-view', 'performance', 'trash', 'translation', 'update'];

public static function dash_class($icon, $class = ''): string
{ return 'dashicons dashicons-' . $icon . ($class ? ' ' . $class : ''); }

public static function dash_span($dash, $class = '', $style = ''): void
{ echo '<span' . ($style ? ' style="' . esc_attr($style) . '"' : '') . ' class="' . esc_attr(self::dash_class($dash, $class)) . '"></span>'; }

private static function dash_and_button(string $dnb): object
{
	preg_match('/#([\w\-]+)\s?(.*)/i', $dnb, $matches);
	$dash = $matches[1] ?? '';
	$button = isset($matches[2]) ? trim($matches[2]) : $dnb;
	return (object) ['dash' => $dash, 'button' => $button];
}

private static function dash_and_button_div($dnb, string $class = ''): void
{
	if (!empty($dnb->dash)) 
	{
		self::dash_span($dnb->dash);
		if ($dnb->button=== '') return;
	}
	if (isset($dnb->button))
	{
		if ($dnb->button===true || (int)$dnb->button===1) { self::dash_span('yes-alt', 'atec-green'); }
		elseif ($dnb->button=== '' || $dnb->button===false || $dnb->button===0) { self::dash_span('dismiss', 'atec-red'); }
		elseif ($dnb->button !== '') echo '<span',  ($class ? ' class="' . esc_html($class) . '"' : ''), '>', esc_html($dnb->button), '</span>';
	}
}

// DASH AREA START

// PRO AREA START

public static function integrity_banner($dir) : void
{
	//$una->nonce = wp_create_nonce('atec_group_nonce');
	$plugin = INIT::plugin_by_dir($dir);
	$link_yes = INIT::build_url('group', '', '', ['integrity' => true, 'plugin' => $plugin]);
	$link_no= str_replace('true', 'false', $link_yes);
	echo
	'<div class="button atec-sticky-left">',
		'<div class="atec-dilb" style="line-height:8px; padding-bottom: 2px;" title="One time connection on activation.">',
			'<span class="atec-fs-8">Allow connection to</span><br><span class="atec-fs-10">atecplugins.com</span>',
		'</div>',
		'<div class="atec-row atec-ml-5">',
			'<a style="background: rgba(0, 180, 0, 0.5);" class="atec-integritry" href="', esc_url($link_yes), '">YES</a>',
			'<a style="background: rgba(180, 0, 0, 0.5); margin-left: -5px;" class="atec-integritry" href="', esc_url($link_no), '">NO</a>',
		'</div>',
	'</div>';
}

public static function pro_license($licenseCode=null, $siteName=null): bool
{
	if (class_exists('ATEC\PRO')) return PRO::pro_check_license($licenseCode, $siteName);
	return false;
}

private static function pro_banner($slug): bool
{
	$licenseOk= self::pro_license();
	$href = INIT::build_url($slug, '', 'License');
	echo
	'<div id="atec_pro_banner" class="atec-sticky-right">
		<a class="button atec-', ($licenseOk ? 'green' : 'blue') ,'" href="', esc_url($href), '" style="', ($licenseOk ? ' border: var(--border-lightgrey);' : ''), '">';
			self::dash_span('awards', 'atec-'.($licenseOk ? 'green' : 'blue'));
			echo
			'<span>',
				$slug=== 'wpmc' ? 'MC ' : ($slug=== 'wpct' ? 'CT4W ' : ''),
				($licenseOk ? '„PRO“ version' : 'Upgrade to „PRO“'),
			'.</span>',
		'</a>
	</div>';
	return $licenseOk;
}

public static function pro_feature($una, $desc= '', $small=false, $licenseOk=null): bool
{
	
	if (is_null($licenseOk)) $licenseOk= self::pro_license()===true;
	if (!$licenseOk)
	{
		$href = INIT::build_url($una, '', 'License');
		echo '
		<div class="', ($desc!== ''?'atec-dilb':''), '">
			<a class="atec-dilb atec-nodeco atec-blue" href="', esc_url($href), '">';
			if ($small)
			{
				echo
				'<div class="atec-dilb atec-blue atec-badge atec-fs-12" style="background: #f9f9ff; border: solid 1px #dde; margin: 0; padding: 4px 5px;">',
					'<div class="atec-dilb atec-vat">'; self::dash_span('awards', 'atec-blue atec-fs-14', 'padding-top: 2px;'); echo '</div>',
					'<div class="atec-dilb atec-vat">Upgrade to „PRO“', str_starts_with($desc,'<br>')?'.':' '; self::br($desc); echo '.</div>',
				'</div>';
				$desc= '';
			}
			else self::msg('blue','„PRO“ feature - please upgrade');
		echo '
			</a>
		</div>';
		if ($desc!== '') { echo '<br><div class="atec-pro-box"><h4>'; self::br($desc); echo '.'; echo '</h4></div>'; 	}
	}
	return $licenseOk; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

public static function pro_block($una, $str = ''): void
{
	$href = INIT::build_url($una, '', 'License');
	$str =
		$str === '' ?
		'This is a „PRO“ ONLY plugin.<br>A license is required to use the basic functions' :
		'Please upgrade to „PRO“ version '.$inline;

	echo
	'<div class="atec-df atec-pro-box">',
		'<div class="atec-df1 atec-vat" style="max-width: 22px;">'; 
			self::dash_span('awards', 'atec-blue atec-fs-14', 'padding-top: 2px;'); 
		echo 
		'</div>',
		'<div class="atec-df1 atec-vat atec-nowrap">',
			'<a class="atec-nodeco atec-blue" href="', esc_url($href), '">', esc_html($str), '</a>',
		'</div>',
	'</div><br>';
}

public static function pro_only($una): void
{ self::pro_block($una); }

public static function pro_missing($class= ''): void
{
	if ($class!== '' && class_exists($class)) return;
	echo  '
	<div class="atec-badge atec-dilb" style="background: #fff0f0;">
		<div class="atec-dilb" style="width:20px; margin-right:5px;">'; self::dash_span('dismiss'); echo '</div>
		<div class="atec-dilb atec-vam">A required class-file is missing – please ';
		if (is_plugin_active('atec-deploy/atec-deploy.php')) echo 'use <a href="', esc_url(admin_url().'admin.php?page= atec_wpdp'), '">';
		else echo 'download/activate <a href="https://atecplugins.com/WP-Plugins/atec-deploy.zip">';
		echo 'atec-deploy</a> to install the „PRO“ version of this plugin.
		</div>
	</div>';
}

// PRO AREA END

// NAV AREA START

public static function add_nav(&$una, $option, $nav)
{
	if (is_array($nav)) $una->navs = array_merge($una->navs, $nav);
	elseif (INIT::bool($option)) $una->navs[] = $nav;
}

public static function nav_tab_dashboard($una): void
{ $una->navs=['#admin-home Dashboard']; self::nav_tab($una); }

private static function single_nav_tab($una, $act_nav, $icon, $button, $licenseOk=false, $break=false, $single=false) : void
{
	$href = INIT::build_url($una, '', $act_nav);
	echo
	'<div class="atec-dilb">',
		'<div class="atec-db atec-pro" style="height:15px; padding-left:10px;">', 
			(!$licenseOk && $break ? 'PRO' : '&nbsp;'), 
		'</div>',
		'<a href="', esc_url($href), '" class="nav-tab', ($single ? ' nav-tab-single' : ''), ($una->nav=== $act_nav ? ' nav-tab-active' : ''), '">';
			if (!empty($icon))
			{
				if (in_array($icon,self::$dashArr)) self::dash_span($icon,'atec-blue');
				else \ATEC\SVG::echo($icon);
			}
			if (!empty($button)) { echo ' ', esc_attr($button); }
		echo
		'</a>
	</div>';
}

public static function nav_tab($una, $break=999, $licenseOk=null, $about=false, $update=false, $debug=false): void
{
	self::progress();
	$margin_top = $licenseOk ? '-15' : '-5';
	echo
	'<h2 class="nav-tab-wrapper" style="padding: 0; margin: '.esc_attr($margin_top).'px 0 5px 0;">';
		$c = 0;
		foreach($una->navs as $a)
		{
			$c++;
			$dnb = self::dash_and_button($a);
			$nice = str_replace(['(', ')'], '', str_replace([' ', '.', '-', '/'], '_', $dnb->button));
			if (!$licenseOk && $c-1 === $break) echo '<div class="atec-dilb atec-mr-10"></div>';
			self::single_nav_tab($una, $nice, $dnb->dash, $dnb->button, $licenseOk, $c > $break, false);
		}

		echo
		'<div class="atec-dilb atec-float-right atec-ml-10 atec-mr-10">';
			if ($debug) self::single_nav_tab($una, 'Debug', 'code-standards', '', false, false, true);
			if ($update) self::single_nav_tab($una, 'Update', 'update', '', false, false, true);
			if ($about) self::single_nav_tab($una, 'About', 'clipboard', '', false, false, true);
			self::single_nav_tab($una, 'License', 'awards', '', false, false, true);
			if (!str_contains($una->url, 'atec_group')) self::single_nav_tab($una, 'Info', 'info', '', false, false, true);
		echo
		'</div>
	</h2>';
}

// NAV AREA END

// TABLE AREA START

public static function table_header($tds = [], $id = '', $class = ''): void
{
	$class = str_replace(['summary'], ['atec-table-td-bold-first atec-table-td-right-not-first'], $class);
	echo '<table', esc_attr(!empty($id) ? ' id="'.$id.'"' : ''), ' class="atec-table atec-table-tiny atec-fit ', esc_attr($class), '">';
	if (!empty($tds))
	{
		echo '<thead>';
		self::table_tr($tds, 'th');
		echo '</thead>';
	}
	echo '<tbody>';
}

public static function table_footer(): void
{ echo '</tbody></table>'; }

private static $allowed_tr = 
	[
		'small'	=> [],
		'b' 		=> [],
		'a'			=> ['href' => [], 'target'=> [] ],
		'span'	=> ['class' => [] ],
		'hr'		=> ['class' => [] ] 
	];

public static function table_tr($tds = [], $tag = 'td', $class = ''): void
{
	if (empty($tds)) 
	{
		echo 
		'<tr class="empty_TR"><td colspan="99"></td></tr>
		<tr class="empty_TR"><td colspan="99"></td></tr>';
		return;
	}
	
	$class = str_replace('bold', 'atec-table-tr-bold', $class);
	
	$tag = $tag === '' ? 'td' : $tag; // fallback
	echo '<tr', (!empty($class) ? ' class="'.esc_html($class).'"' : '') ,'>';

	$dash_reg = '/#([\-|\w]+)\s?(.*)/i';
	foreach ($tds as $td)
	{
		
		// Check for leading number followed by '@' to set colspan
		if (preg_match('/^(\d+)@(.*)$/', $td, $colMatches)) { $colspan = (int) $colMatches[1]; $td = $colMatches[2]; }
		else $colspan = 1;

		// Check for leading # to set dashicon
		preg_match($dash_reg, $td, $matches);
		if (!empty($matches[1])) { $dash = $matches[1]; $td = isset($matches[2]) ? $matches[2] : ''; }
		else $dash = '';
		
		if (!empty($td))
		{
			$td_plain = wp_strip_all_tags($td);
			$right = 
				preg_match('/^\s*[\d\.,]+\s*$/', $td_plain) // pure numbers
				|| preg_match('/^\s*[\d\.,]+\s*(B|KB|MB|GB|TB|ms|s|%)\s*$/i', $td_plain); // size_format style
		}
		else $right = false;

		echo 
		'<', 
			esc_attr($tag), 
			($colspan > 1 ? ' colspan="' . esc_attr($colspan) . '"' : ''), 
			($right ? ' class="atec-right atec-nowrap"' : ''),
		'>';
			
			if (!empty($dash)) { self::dash_span($dash); echo (!empty($td)) ? ' ' : ''; }
			echo wp_kses($td, self::$allowed_tr);
		
		echo 
		'</', esc_attr($tag), '>';
	}
	echo '</tr>';
}

public static function table_td($value= '', $class= '')
{ echo '<td', ($class ? ' class="'.esc_attr($class).'"' : ''), '>', esc_html($value), '</td>'; }

// TABLE AREA END

public static function button($una, $action, $nav= '', $button= '', $primary=false, $confirm=false): void
{
	$dnb = self::dash_and_button($button);
	$href = INIT::build_url($una, $action, $nav);
	echo 
	'<div', ($confirm ? ' class="atec-btn-confirm"' : ''), '>';
		if ($confirm) { echo '<input class="atec-mr-10" title="Confirm action" type="checkbox" onchange="jQuery(this).siblings(\'a\').toggleClass(\'atec-disabled-link\');">'; }
		echo 
		'<a href="', esc_url($href), '" class="button button-', ($primary ? 'primary' : 'secondary'), esc_html($confirm ? ' atec-disabled-link' : ''), '">';
			self::dash_and_button_div($dnb);
		echo 
		'</a>
	</div>';
}

public static function button_confirm($una, $action, $nav, $button): void
{ self::button($una, $action, $nav, $button, false, true); }

public static function button_confirm_td($una, $action, $nav, $button): void
{
	echo '<td class="atec-nowrap">';
		self::button($una, $action, $nav, $button, false, true);
	echo '</td>';
}

public static function dash_button($una, $action, $nav, $dash, $enabled, $id, $primary=false): void
{
	$href = INIT::build_url($una, $action, $nav, ['id' => $id]);
	echo 
	'<a', 
		esc_attr(!$enabled ? ' disabled ':''), ' href="', esc_url($href ), '" ',
		'class="button ', esc_attr(self::dash_class($dash, 'button-'.($primary ? 'primary' : 'secondary'))), '">',
	'</a>';
}

public static function dash_button_td($una, $action, $nav, $dash, $enabled, $id, $primary=false): void
{ echo '<td>'; self::dash_button($una, $action, $nav, $dash, $enabled, $id, $primary); echo '</td>'; }

// MSG AREA START

public static function badge($ok, $str_success, $str_failed = '', $margin = false): void
{
	$bg_color = $ok === 'blue' ? '#f9f9ff' : ($ok === 'info' ? '#fff' : ($ok === 'warning' ? 'rgba(255, 253, 253, 0.95)' : ($ok ? 'var(--bg-success)' : 'var(--bg-error)')));
	$border = $ok === 'blue' ? 'button' : ($ok === 'info' ? 'lightgrey' : ($ok === 'warning' ? 'warning' : ($ok ? 'success' : 'error')));
	$icon = $ok === 'blue' ? 'awards' : ($ok === 'info' ? 'info-outline' : ($ok === 'warning' ? 'warning' : ($ok ? 'yes-alt' : 'dismiss')));
	$color_class = 'atec-' . ($ok === 'blue' ? 'blue' : ($ok === 'info' ? 'black' : ($ok === 'warning' ? 'orange' : ($ok ? 'green' : 'red'))));

	// Optional tag logic
	if (strpos($str_success, '#') !== false)
	{
		$ex = explode('#', $str_success);
		if (isset($ex[1]))
		{
			$str_success = $ex[0].' '.$ex[1];
			$str_failed = $ex[0] . ' ' . $str_failed;
		}
	}

	$str = $ok ? $str_success : $str_failed;
	$str = INIT::trailingdotit($str);

	echo 
	'<div class="atec-badge', ($margin ? ' atec-mr-5 atec-mb-5' : ''), '" style="background:', esc_attr($bg_color), '; border: var(--border-', esc_attr($border), ');">',
		'<div class="atec-col atec-vat ', esc_attr(self::dash_class($icon, $color_class)), '" style="max-width:20px"></div>',
		'<div class="atec-col atec-anywrap ', esc_attr($color_class), '">';
			self::br($str);
		echo 
		'</div>',
	'</div>';
}

public static function p_info($str, $bold=false, $class= ''): void
{
	$str = INIT::trailingdotit($str); 
	echo '<div class="atec-badge atec-box-info', ($class!== '' ? ' '.esc_html($class) : ''), '">';
		echo '<div>🔹</div>';
		// phpcs:ignore
		echo '<div ', ($bold ? ' atec-bold' : ''), '">', wp_kses_post($str), '</div>';
	echo '</div>';
}

public static function msg($ok, $str, $before=false, $after=false): void
{
	if ($before) echo '<br>';
	self::badge($ok, $str, $str);
	if ($after) echo '<br>';
}

public static function help($title, $str, $warning=false, $hide=false): void
{
	$id = uniqid();
	echo 
	'<div id="', esc_attr($id), '_help_button" class="atec-help-button" ',
		'style="border: ', $warning ? 'var(--border-warning)' :'var(--border-button)', '"',
		'onclick="return showHelp(\'', esc_attr($id), '\');">';
			self::dash_span($warning ? 'editor-help' : 'info', 'atec-'.($warning ? 'orange' : 'lightgrey'));
			echo esc_html($title);
	echo
	'</div>',
	'<div id="', esc_attr($id), '_help" class="atec-help atec-dn">', 
		'<p class="atec-bold atec-mb-5 atec-mt-5 atec-', ($warning ? 'orange' : 'black'), '"><u>', esc_html($title), '</u>:</p>',
		wp_kses_post($str), 
	'</div>';
	
	self::reg_inline_script('help', 'function showHelp(id) { jQuery("#"+id+"_help").removeClass("atec-dn").addClass("atec-db"); jQuery("#"+id+"_help_button").remove(); return false; }');
}

// MSG AREA END

// FORM AREA START

public static function form_header($una, $action= '', $nav= '', $id= '', $class= '')
{
	$href = INIT::build_url($una, $action, $nav);
	echo
	'<form class="atec-form', ($class ? ' '.esc_attr($class) : ''), '" method="post" action="'.esc_url($href).'">';
		self::form_add_fields($una, $action, $nav, $id);
}

public static function form_footer()
{ echo '</form>'; }

public static function form_add_fields($una, $action = null, $nav = null, $id = null)
{
	$fields = [	'_wpnonce' => $una->nonce, 'action' => $action, 'nav' => $nav, 'id' => $id ];
	foreach ($fields as $name => $value)
		{ if (!is_null($value)) { echo '<input type="hidden" name="', esc_attr($name), '" value="', esc_attr($value), '">'; } }
}

public static function submit_button($button= '', $inline=false)
{
	$button = $button ?: '#editor-break Save';
	$dnb = self::dash_and_button($button);
	if (!$inline) echo '<p>';
	echo '<button id="submit" type="submit" name="submit" class="button button-primary">'; self::dash_and_button_div($dnb); echo '</button>';
	if (!$inline) echo '</p>';
}

public static function safe_redirect($una, $action=null, $nav=null, $args = []): void
{ wp_safe_redirect(INIT::build_url($una, $action, $nav)); }

public static function redirect($una, $action=null, $nav=null, $args = []): void
{
	self::reg_inline_script('redirec', 'window.location.assign("'.INIT::build_url($una, $action, $nav, $args).'");');
	self::flush();
}

public static function clean_request_bool($key) : bool
{ return INIT::bool(self::clean_request($key)); }

public static function clean_request($key, $nonce = '', $type= 'text')
{	
	$source = $_POST['submit'] ?? false ? $_POST : $_REQUEST;		// phpcs:ignore
	if (!isset($source[$key])) return '';

	$nonce_to_check = $nonce !== '' ? $nonce : INIT::nonce(); 		// Validate nonce

	$nonce_valid =
		(isset($_POST['submit']) && check_admin_referer($nonce_to_check)) ||
		(isset($source['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($source['_wpnonce'])), $nonce_to_check));
		
	if (!$nonce_valid) return '';
	$value = wp_unslash($source[$key]);
	return $type=== 'textarea' ? sanitize_textarea_field($value) : sanitize_text_field($value);
}

// FORM AREA END

// ENQUEUE AREA START

public static function reg_style($id, $dir, $css, $ver): void
{
	static $cached = null;	// Dev mode ?
	if ($cached === null) { $cached = get_option('atec_dev_mode') ? '.min' : ''; }
	if ($cached!== '') $css = str_replace($cached, '', $css);

	$id = 'atec_'.$id;
	$pluginURL = INIT::plugin_url_by_dir($dir);
	wp_enqueue_style($id, $pluginURL.'/assets/css/'.$css, [], $ver);
}

public static function reg_script($id, $dir, $js, $ver): void
{
	static $cached = null;	// Dev mode ?
	if ($cached === null) { $cached = get_option('atec_dev_mode') ? '.min' : ''; }
	if ($cached!== '') $js = str_replace($cached, '', $js);

	$id = 'atec_'.$id;
	$pluginURL = INIT::plugin_url_by_dir($dir);
	wp_enqueue_script($id, $pluginURL.'/assets/js/'.$js, [], $ver, true);
}

public static function reg_inline_style($id, $css_safe):void
{
	$id = ($id=== '')?'atec-css':'atec_'.$id; 
	wp_register_style($id, false, [], '1.0.0'); wp_enqueue_style($id); wp_add_inline_style($id, $css_safe);
	self::flush();
}

public static function reg_inline_script($id, $js_safe, $jquery=false):void
{
	$id = 'atec_'.$id;
	wp_register_script($id, false, $jquery?array('jquery'):array(), '1.0.0', false); wp_enqueue_script($id); wp_add_inline_script($id, $js_safe);
	self::flush();
}

public static function load_atec_style($dir, $styles=[])
{
	static $versions = ['style' => '1.0.1', 'check' => '1.0.1'];
	foreach($styles as $style) 	
	{
		$version = $versions[$style] ?? '1.0.1';
		self::reg_style('atec_'.$style, $dir, 'atec-'.$style.'.min.css', $version);
	}
}

public static function load_atec_script($dir, $scripts=[])
{
	static $versions = [ 'check' => '1.0.1' ];
	foreach($scripts as $script)
	{
		$version = $versions[$script] ?? '1.0.1';
		self::reg_script('atec_'.$script, $dir, 'atec-'.$script.'.min.js', $version);
	}
}

// ENQUEUE AREA END

// HEADER AREA START

public static function header($una): bool
{
	static $admin_bar_slugs = ['wpci', 'wpd', 'wpdp'];
	
	$plugin 			= INIT::plugin_by_dir($una->dir);
	$approved 		= \ATEC\GROUP::is_plugin_approved($una->slug);
	$wordpress		= 'https://wordpress.org/support/plugin/';

	$supportLink = 
		$approved
		? $wordpress . $plugin 
		: ($una->slug === 'wpmc' ? 'wpmegacache' : 'atecplugins') . '.com/contact/';

	$version			= wp_cache_get('atec_'.$una->slug.'_version', 'atec_np');
	$licenseOk 		= self::pro_banner($una->slug);
	if (is_null(get_option('atec_allow_integrity_check',null))) self::integrity_banner($una->dir);

	echo
	'<div class="atec-header">',
		'<h3 class="atec-m-auto atec-mb-5 atec-row" style="align-items: center;">';

			if ($una->slug=== 'wpmc') 
			{
				\ATEC\SVG::echo('wpmc');
				echo '<span style="color:#2340b1;">Mega</span><span style="color:#fe5300; margin-left: -5px;">Cache</span>';
			}
			else
			{
				\ATEC\SVG::echo('wpa');
				echo 
				'<div><span class="atec-logo-text">tec</span>', esc_html(str_replace('atec','',INIT::plugin_fixed_name($plugin))), '</div>';
				\ATEC\SVG::echo($una->slug);
			}
			echo '<div class="atec-fs-10" style="margin-left: -5px;">v', esc_attr($version), '</div>';

		echo
		'</h3>';

		echo
		'<div class="atec-row atec-header-box">',

			'<a class="button atec-btn-small" href="', esc_url($supportLink), '" target="_blank">',
				'<span class="', esc_attr(self::dash_class('sos')), '"></span>Plugin support',
			'</a>';
			
			if (in_array($una->slug, $admin_bar_slugs))	// Plugins with admin switch
			{
				$option = INIT::admin_bar_option($una->slug);

				$href = INIT::build_url($una, 'admin_bar', '', ['set' => $option ? 0 : 1]);
				$id = uniqid();
				echo
				'<div class="atec-mini-switch-box" title="Toggle admin bar display.">',
						'<span class="', esc_attr(self::dash_class('dashboard','font-size: 20px;')), '"></span>',
						'<div class="atec-ckbx atec-dilb atec-ckbx-mini">',
							'<label class="switch" for="check_', esc_attr($id), '" onclick="location.href=\'', esc_url($href), '\'">',
								'<input name="check_', esc_attr($id), '" type="checkbox" value="', esc_attr($option), '"', checked($option,true,true), '>',
								'<div class="slider round"></div>',
							'</label>',
						'</div>',
				'</div>';
			}

			if ($approved)
			{
				echo
				'<a class="button atec-btn-small" href="', esc_url($wordpress.$plugin.'/reviews/#new-post'), '" target="_blank">',
					'<span class="', esc_attr(self::dash_class('admin-comments')), '"></span>Post a review',
				'</a>';
			}
			
		echo '
		</div>
		
	</div>';
	self::flush();
	return $licenseOk;
}

public static function little_block($str, $class= '', $info= ''): void
{
	$str = str_replace(['<s>', '</s>'], ['<span class="atec-small">', '</span>'], $str);
	echo 
	'<div class="atec-db">
		<div class="atec-dilb atec-head', esc_html($class=== ''? '' : ' '.$class), '">',
			'<h3 class="atec dilb">', wp_kses_post($str), '</h3>',
		'</div>'; 
		if ($info!== '')
		{
			echo'<div class="atec-dilb atec-ml-10">';
				// phpcs:ignore
				self::p_info($info);
			echo '</div>';
		}
	echo
	'</div>'; 
}

public static function little_block_multi($una, $str, $abpArr, $nav= '', $infoArr=[]): void
{
	echo
	'<div class="" style="padding-bottom:0;">',
	
		'<div class="atec-dilb atec-mr-10 atec-head"><h3>', esc_html($str), '</h3></div>';

		if (!empty($abpArr))
		{
			foreach($abpArr as $key => $value)
			{
				echo '<div class="atec-dilb atec-vat atec-ml-10">'; 
					self::button($una, $key, $nav, $value); 
				echo '</div>';
			}
		}

		if (!empty($infoArr))
		{
			echo
			'<div class="atec-dilb atec-vat atec-float-right">';
				foreach ($infoArr as $key => $value)
				{
					echo
					'<div class="atec-dilb atec-border-tiny atec-ml-5 atec-bg-w85 atec-nowrap" style="padding: 5px 5px 2px 5px;">';
						self::dash_and_button_div(self::dash_and_button($key),'atec-bold');
						echo '<span class="atec-dilb atec-mr-5">:</span>';
						self::dash_and_button_div(self::dash_and_button($value));
					echo
					'</div>';
				}
			echo
			'</div>';
		}

	echo
	'</div>';
}

public static function active_no_cfg($str, $ok=true, $noCfg=true): void
{
	$bg_color 	= $ok ? 'var(--bg-success)' : 'var(--bg-error)';
	$border		= $ok ? 'var(--border-success)' : 'var(--border-error)';
	$color_class	= $ok ? 'green' : 'red';
	echo
	'<div class="atec-badge atec-box-nocfg" style="background:', esc_attr($bg_color), '; border: ', esc_attr($border), ';">
		<div>'; self::dash_span('plugins-checked', 'atec-'.$color_class); echo '</div>
		<div class="atec-', esc_attr($color_class), '">', esc_html($str), '.', ($noCfg ? ' No configuration required.' : ''), '</div>
	</div>';
}

public static function page_header($una, $break=999, $about=false, $update=false, $debug=false)
{
	echo
	'<div class="atec-page">';
		$licenseOk = self::header($una);

		if ($break<0) $break = $licenseOk ? 999 : abs($break);

		echo
		'<div class="atec-main">';
			self::nav_tab($una, $break, $licenseOk, $about, $update, $debug);

			echo
			'<div class="atec-g atec-border">';
				self::flush();

				$short_circuit = false;
				switch ($una->nav)
				{
					case 'Info':
						\ATEC\INFO::init($una);
						$short_circuit = true;
						break;
						
					case 'License':
						\ATEC\LICENSE::init($una);
						$short_circuit = true;
						break;
				}
			
				if ($short_circuit) 
				{
					self::page_footer();
					return null;
				}

	return $licenseOk;
}

public static function page_footer($slug= ''): void
{
			echo
			'</div>
		</div>
	</div>';
	self::footer($slug= '');
	self::flush();
}

// HEADER AREA END

// FOOTER AREA START

public static function footer($slug= '')
{
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$loadTime = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'])*1000);
	$domain	= $slug=== 'wpmc' ? 'wpmegacache' : ($slug=== 'wpct' ? 'cachetune' : 'atecplugins');
	$href = INIT::admin_url('group');
	echo
	'<div class="atec-footer atec-center atec-fs-12">
		<span class="atec-ml-10" style="float:left;">
			⏱️ <span>', esc_attr($loadTime), '</span> <span class="atec-fs-10">ms</span>';
			if ($domain=== 'atecplugins') echo '&middot; <a class="atec-nodeco" href="', esc_url($href), '">atec-Plugins</a>';
			echo '
		</span>
		<span class="atec-dilb atec-float-right atec-mr-10">
			© 2023/', esc_html(gmdate('y')), ' <a href="https://', esc_attr($domain), '.com/" target="_blank" class="atec-nodeco">', esc_attr($domain), '.com</a>
		</span>
	</div>';
}

// FOOTER AREA START

}
?>