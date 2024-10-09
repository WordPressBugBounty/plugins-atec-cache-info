<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_group { 

private function atec_clean_request_license($t): string { return atec_clean_request($t,'atec_license_nonce'); } 
	
function __construct() {

if (!defined('ATEC_TOOLS_INC')) require_once('atec-tools.php');	

echo '
<div class="atec-page">';
	atec_header(__DIR__ ,'','atec','Plugins');	

	echo '
	<div class="atec-main">';
		atec_progress();

		$url					= atec_get_url();
		$nonce 			= wp_create_nonce(atec_nonce());
		$action 			= atec_clean_request('action');

		$license 			= $this->atec_clean_request_license('license');
		$licenseCode 	= atec_clean_request('licenseCode');

		if ($action==='save_license') 
		{ delete_transient('atec_license_code'); update_option('atec_license_code',$licenseCode,'auto'); }

		if ($license=='true' || $action==='save_license')
		{
			$url = atec_get_url();          
			$nonce = wp_create_nonce(atec_nonce());
			if ($licenseCode==='') $licenseCode=get_option('atec_license_code','');
			$siteName=wp_parse_url(get_site_url(),PHP_URL_HOST);
			$licenseOk=atec_check_license($licenseCode,$siteName);
			atec_little_block('„Lifetime-Site-License“');
			echo '
			<div class="atec-g atec-border-white atec-center" style="background: #fafafa; padding: 30px 0;">';
			if ($licenseOk===true)
			{
				echo '<h4>Thank you for activating your „Lifetime-Site-License“.</h4>';
			}
			else
			{
				echo '
					<h4>Get your „Lifetime-Site-License“ for ALL our plugins.</h4>';
					
				echo '<p class="atec-mt-5">
						A single license code will unlock the PRO features of ALL our plugins, site-wide.<br>
						Just pay via PayPal and send us the domain name of your site.<br>
						You will get your license code via email. <b>No registration required.</b>
					</p>';
					
				echo '<a class="atec-nodeco" style="width: fit-content !important; margin: 10px auto;" href="https://atecplugins.com/license/" target="_blank"><button class="button button-primary">GET YOUR LICENSE NOW</button></a>
				<span class="atec-small">Links to ', $plugin==='mega-cache'?'https://wpmegacache.com':'https://atecplugins.com/license', '</span>';
			}

			echo '
				<div style="background: #f0f0f0; width: fit-content; padding: 0px 20px 20px 20px; border: solid 1px #dedede; margin: 10px auto;">
				<p><span style="font-size: 24px;" class="', esc_attr(atec_dash_class('admin-site')), '"></span> ', esc_attr($siteName), '</p>
				<form style="background: #e8e8e8; border: solid 1px #dedede; padding: 10px;" name="atec_license" method="post" action="', esc_url($url), '&action=save_license&_wpnonce=', esc_attr($nonce), '">
					  <div><label><b>License code</b></label></div>
					<div><textarea cols="30" rows="5" name="licenseCode">', esc_textarea($licenseCode), '</textarea></div>
					<div><input type="submit" name="submit" id="submit" class="button button-primary" value="Save"></div>
				  </form>
				  </div>';
			  
			if ($licenseCode!=='')
			{
				echo '<div class="atec-center">';
					atec_badge('License code is valid for your site',$licenseOk,$licenseOk===true);
				echo '</div>';
			}

			echo '
			</div>';
		}
		else
		{

		echo '<br class="atec-clear">
		<div class="atec-g">
			<table style="width: auto; margin:0 auto;" class="atec-table">
			<thead>
				<tr>
				<th></th><th>Name (Link)</th><th>Size</th>
				<th colspan="2"><span class="', esc_attr(atec_dash_class('clock','atec-dil')), '"></span><span style="font-size:8px;">&nbsp;(ms)</span></th>
				<th>Status</th>
				<th>Preview</th><th>Installed</th><th>Description</th>
				</tr>
			</thead>
			<tbody>';

		$atec_group_arr	= [
			'cache-apcu','cache-info','code','database','debug',
			'deploy','dir-scan','meta','optimize','page-performance',
			'poly-addon','profiler','smtp-mail','stats','system-info',
			'web-map-service','webp'];

		$atec_group_arr_size 	= [119,104,68,90,81,		65,107,64,114,367,			78,79,100,76,103,	436,63];
		$atec_front_et 				= [0.2,0,0.1,0,0,				0,0,0,0.3,0,						0,0,0,0,	0,					0,0];
		$atec_back_et				= [0.3,0.1,0.1,0,0,			0.1,0.1,0.1,0.1,0.3,0.1,		0.1,0.1,0,0.1,0,			0.1,0.1];
		$atec_active					= ['cache-apcu','cache-info','dir-scan','system-info','web-map-service'];
		$atec_review					= ['database'];
		$atec_slug_arr				= ['wpca','wpci','wpc','wpdb','wpd',	'wpdp','wpds','wpm','wpo','wppp',		'wppo','wppr','wpsm','wps','wpsi',		'wms','wpwp'];
		$atec_desc_arr		= [
							esc_attr__('APCu object and page cache','atec_group'),
							esc_attr__('atec Cache Info & Statistics (OPcache, WP-object-cache, JIT, APCu, Memcached, Redis, SQLite-object-cache)','atec_group'),
							esc_attr__('Custom code snippets for WP','atec_group'),	
							esc_attr__('Optimize WP database tables','atec_group'),
							esc_attr__('Show debug log in admin bar','atec_group'),	
																	
							esc_attr__('Install and auto update `atec´ plugins','atec_group'),
							esc_attr__('Dir Scan & Statistics (Number of files and size per directory)','atec_group'),
							esc_attr__('Add custom meta tags to the head section','atec_group'),
							esc_attr__('Lightweight performance tuning plugin','atec_group'),
							esc_attr__('Measure the PageScore and SpeedIndex of your WordPress site','atec_group'),
							
							esc_attr__('Custom translation strings for polylang plugin','atec_group'),
							esc_attr__('Measure plugins & theme plus pages execution time','atec_group'),	
							esc_attr__('Add custom SMTP mail settings to WP_Mail','atec_group'),	
							esc_attr__('Lightweight and GDPR compliant WP statistics','atec_group'),								
							esc_attr__('System Information (OS, server, memory, PHP and database details, php.ini, wp-config, .htaccess and PHP extensions)','atec_group'),
							
							esc_attr__('Web map, conform with privacy regulations','atec_group'),
							esc_attr__('Auto convert all images to WebP format','atec_group')
						];
					
		$c=0;
		global $wp_filesystem;
		WP_Filesystem();

		function fixName($p) { return ucwords(str_replace(['-','apcu','webp'],[' ','APCu','WebP'],$p)); }

		foreach ($atec_group_arr as $a)
		{
			$installed = $wp_filesystem->exists(WP_PLUGIN_DIR.'/atec-'.esc_attr($a));
			$active = $installed && is_plugin_active('atec-'.esc_attr($a).'/atec-'.esc_attr($a).'.php');
			echo '<tr>
				<td><img alt="Plugin icon" src="',esc_url( plugins_url( '/assets/img/atec-group/atec_'.esc_attr($atec_slug_arr[$c]).'_icon.svg', __DIR__ ) ) ,'" style="height:22px;"></td>';
				$isWP=in_array($atec_group_arr[$c], $atec_active);
				$atecplugins='https://atecplugins.com/';
				$link=$isWP?'https://wordpress.org/plugins/atec-'.esc_attr($a).'/':$atecplugins;
				echo '
				<td><a class="atec-nodeco" href="', esc_url($link) ,'" target="_blank">', esc_attr(fixName($atec_group_arr[$c])), '</a></td>
				<td class="atec-table-right">', esc_attr(size_format($atec_group_arr_size[$c]*1024,$atec_group_arr_size[$c]>1024?1:0)), '</td>
				<td class="atec-table-right">', esc_attr(number_format($atec_front_et[$c],1)), '</td>
				<td class="atec-table-right">', esc_attr(number_format($atec_back_et[$c],1)), '</td>';
				if ($isWP) echo '
					<td><span title="', esc_attr__('Published','atec_group'), '" class="',esc_attr(atec_dash_class('wordpress')), '"></span></td>
					<td><a class="atec-nodeco" title="WordPress Playground" href="https://playground.wordpress.net/?plugin=atec-', esc_attr($atec_group_arr[$c]), '&blueprint-url=https://wordpress.org/plugins/wp-json/plugins/v1/plugin/atec-', esc_attr($atec_group_arr[$c]), '/blueprint.json" target="_blank"><span class="',esc_attr(atec_dash_class('welcome-view-site')), '"></span></a></td>';
				else 
				{
					$inReview=in_array($atec_group_arr[$c], $atec_review);
					echo '
					<td colspan="2">
						<span title="', $inReview?esc_attr__('In review','atec_group'):esc_attr__('In progress','atec_group'), '"><span class="',esc_attr(atec_dash_class($inReview?'visibility':'')) ,'"></span>
					</td>';
				}
				if ($installed) echo '<td><span class="',esc_attr(atec_dash_class(($active?'plugins-checked':'admin-plugins'), 'atec-'.($active?'green':'blue'))), '"></span></td>';
				else echo '
				<td>
					<a title="Download from atecplugins.com" class="atec-nodeco atec-vam button button-secondary" style="padding: 0px 4px;" target="_blank" href="', esc_url($atecplugins), '/WP-Plugins/atec-', esc_attr($a), '.zip" download><span style="padding-top: 4px;" class="', esc_attr(atec_dash_class('download','')), '"></span></a></td>';
				echo '<td>',esc_attr($atec_desc_arr[$c]),'</td>
				</tr>';
			$c++;
		} 
		echo '</tbody></table>
		</div>
		<center>
			<p style="max-width:80%;">',
				esc_attr__('All our plugins are optimized for speed, size and CPU footprint (frontend & backend)','atec_group'), '.<br>',
				esc_attr__('Also, they share the same `atec-WP-plugin´ framework – so that shared code will only load once, even with multiple plugins enabled.','atec_group'), '
			<br>
			With an average of <strong>1 <small>ms</small></strong> CPU time, we consider our plugins to be super fast. Other plugins typically require much more time (Polylang ≈ 45 <small>ms</small>, WooCommerce ≈ 500 <small>ms</small>).
			</p>
			<a class="atec-nodeco" class="button atec-center" href="https://de.wordpress.org/plugins/search/atec/" target="_blank">', esc_attr__('All atec-plugins in the WordPress directory','atec_group'), '.</a>
		</center>';
	}
	
	echo '
	</div>
</div>';
	
	if (!str_contains(add_query_arg(null,null),'?page=atec_group') && !class_exists('ATEC_footer')) require_once('atec-footer.php');
	else 
	{
		atec_reg_inline_script('group','
		jQuery(".atec-page").css("gridTemplateRows","45px 1fr");
		jQuery("#atec_loading").css("opacity",0);', true);
	}
	
}}

new ATEC_group();
?>