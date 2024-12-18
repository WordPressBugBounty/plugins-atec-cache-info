<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_group { 

private function atec_clean_request_license($t): string { return atec_clean_request($t,'atec_license_nonce'); } 

private function atec_group_star_list()
{
	echo 
	'<li>⭐ ', esc_attr__('Upgrades & updates','atec-cache-info'), '.</li>	
	<li>⭐ ', esc_attr__('Technical support','atec-cache-info'), '.</li>
	<li>⭐ ', esc_attr__('Access to all the „PRO“ features','atec-cache-info'), '.</li>							
	<li>⭐ ', esc_attr__('„Lifetime-site-License“ for your site (domain)','atec-cache-info'), '.</li>';
}

function __construct() {
	
if (!defined('ATEC_TOOLS_INC')) @require_once(__DIR__.'/atec-tools.php');	
if (!function_exists('atec_fix_name')) 
{ function atec_fix_name($p) { return ucwords(str_replace(['-','apcu','webp','svg','htaccess'],[' ','APCu','WebP','SVG','HTaccess'],$p)); } }

$url				= atec_get_url();
$nonce 		= wp_create_nonce(atec_nonce());
$action 		= atec_clean_request('action');

$atec_group_arr=[];
require_once(__DIR__.'/atec-group-array.php');

$license 			= $this->atec_clean_request_license('license');
if ($license==='') $license = atec_clean_request('license');

$plugin = $this->atec_clean_request_license('plugin');
if ($plugin==='') $plugin = atec_clean_request('plugin');

$integrity			= $this->atec_clean_request_license('integrity');
$integrityString 	= '';
if ($integrity!=='')
{
	$integrityString='Thank you. Connection to atecplugins.com is '.($integrity=='true'?'enabled':'disabled');
	if ($integrity=='true') atec_integrity_check(__DIR__,$plugin);
	update_option('atec_allow_integrity_check',$integrity);
}

echo '
<div class="atec-page">';

	$mega = $plugin==='mega-cache';
	if ($license!=='true')	atec_header(__DIR__ ,'','atec Plugins','');
	else
	{
		$opensslEnabled = extension_loaded('openssl');
		if (!$opensslEnabled) atec_admin_notice('warning','The openSSL extension is required for license handling.',true);

		$supportLink	= $mega?'https://wpmegacache.com/contact/':'https://atecplugins.com/contact/';
		$imgSrc = plugins_url('/assets/img/atec-group/'.($mega?'atec_wpmc_icon.svg':'atec_logo_blue.png'), __DIR__ );
		echo '
		<div class="atec-header">
			<h3 class="atec-mb-0 atec-center" style="line-height: 0.85em;">';
			// @codingStandardsIgnoreStart
			// Image is not an attachement
			echo '<sub><img alt="Plugin icon" src="', esc_url($imgSrc), '" class="atec-plugin-icon" style="height: 22px;"></sub> ', esc_html($mega?'Mega-Cache':'atec-Plugins'), 
			'</h3>';
			// @codingStandardsIgnoreEnd		
			atec_progress_div();
			echo '
			<div class="atec-center">	
				<a style="position:relative;" class="atec-fs-12 atec-nodeco atec-btn-small" href="', esc_url($supportLink), '" target="_blank">
				<span class="', esc_attr(atec_dash_class('sos')), '"></span> Plugin contact</a>
			</div>
		</div>';
	}
	
	echo '
	<div class="atec-main" style="padding-top: 30px;">';
		atec_progress();
		
		if ($integrityString!=='') { echo '<br><center>'; atec_success_msg($integrityString); echo '</center>'; }
		if ($license=='true')
		{
			if ($mega)
			{
				echo '
				<div class="atec-g atec-border atec-center" style="padding: 20px 10px;">
					<h3 class="atec-mt-0">';
					// @codingStandardsIgnoreStart
					// Image is not an attachement
					echo '<sub><img alt="Plugin icon" src="', esc_url( plugins_url( '/assets/img/atec-group/atec_wpmc_icon.svg', __DIR__ )), '" class="atec-plugin-icon" style="height: 22px;"></sub>&nbsp;';
					// @codingStandardsIgnoreEnd
					echo 'Mega-Cache „PRO“ package', 
					'</h3>
					<div id="atec_group_pro_package">
							<div id="pro_package_welcome" class="atec-fit" style="margin: 0 auto;">
								<div class="atec-border-white atec-bg-w atec-fit" style="font-size: 16px !important; padding: 10px; text-align: left;">
									<ul class="atec-m-0">
									<li>⭐ <strong>7 additional storage options.</strong></li>';
								$this->atec_group_star_list();
							echo '
									</ul>
								</div>
							</div>	
					<div>',
					'<center class="atec-mt-0 atec-mb-10"><br>';
					$c=0;
					$pattern = '/atec-[\w\-]+/';
					$imgSrc = plugins_url( '/assets/img/logos/', __DIR__ );
					$imgSrc = preg_replace($pattern, 'mega-cache', $imgSrc);
					foreach (['apcu','redis','memcached','sqlite','mongodb','mariadb','mysql'] as $a)
					{
						$c++;
						if ($c % 13===0) echo '<br>';
						// @codingStandardsIgnoreStart
						// Image is not an attachement
						echo '<img src="', esc_url($imgSrc.$a.'.svg'), '" class="atec-plugin-icon" style="height: 22px; margin: 0 5px 10px 5px;">';
						// @codingStandardsIgnoreEnd
					}
					echo '
					</center>
				</div>';
			}
			else
			{
				echo '
				<div class="atec-g atec-border atec-center" style="padding: 20px 10px;">
					<h3 class="atec-mt-0">';
					// @codingStandardsIgnoreStart
					// Image is not an attachement
					echo '<sub><img alt="Plugin icon" src="', esc_url( plugins_url( '/assets/img/atec-group/atec_logo_blue.png', __DIR__ )), '" class="atec-plugin-icon" style="height: 22px;"></sub>&nbsp;';
					// @codingStandardsIgnoreEnd
					echo esc_attr__('atec-Plugins „PRO“ package','atec-cache-info'), 
					'</h3>
					<div id="atec_group_pro_package">
						<div id="pro_package_welcome" class="atec-fit" style="margin: 0 auto;">
							<div class="atec-border-white atec-bg-w atec-fit" style="font-size: 16px !important; padding: 10px; text-align:left;">
								<ul class="atec-m-0">
								<li>⭐ <strong>', esc_attr__('28 valuable plugins','atec-cache-info'), '.</strong></li>';
								$this->atec_group_star_list();
							echo '
								</ul>
							</div>
						</div>						
					<div>',
					'<center class="atec-mt-0 atec-mb-10"><br>';
					$c=0;
					$imgSrc = plugins_url( '/assets/img/atec-group/atec_', __DIR__ );
					foreach ($atec_group_arr as $a)
					{
						$c++;
						if ($c===27) continue;
						if ($c % 14===0) echo '<br>';
						// @codingStandardsIgnoreStart
						// Image is not an attachement
						echo '<img src="', esc_url($imgSrc.$a['slug'].'_icon.svg'), '" class="atec-plugin-icon" style="height: 22px; margin: 0 5px 10px 5px;">';
						// @codingStandardsIgnoreEnd
					}
					echo '
					</center>
				</div>';				
			}
			
			$licenseUrl = $mega?'https://wpmegacache.com/license/':'https://atecplugins.com/license';
			echo '<a class="atec-nodeco" style="width: fit-content !important; margin: 10px auto;" href="', esc_textarea($licenseUrl), '" target="_blank">
			<button class="button button-primary">', esc_attr__('GET YOUR „PRO“ PACKAGE NOW','atec-cache-info'), '</button></a>
			<div class="atec-small">Links to ', esc_textarea($licenseUrl), '</div>';

			echo '<p styl="font-size: 18px !important;">',
				esc_attr__('Buy the „PRO“ package through one time payment','atec-cache-info'), '.<br>',
				esc_attr__('The license is valid for the lifetime of your site (domain)','atec-cache-info'), '.<br><b>',
				esc_attr__('No subscription. No registration required.','atec-cache-info'), '</b>
			</p>
			</div>';

			$include=__DIR__.'/atec-pro.php';
			if (!class_exists('ATEC_pro') && file_exists($include)) @include_once($include);
			if (class_exists('ATEC_pro')) { (new ATEC_pro)->atec_pro_form($url, $nonce, atec_clean_request('licenseCode'), $plugin); }
		}
		else
		{

		echo '
		<div class="atec-g">';
		atec_table_header_tiny(['','Name (Link)','WP?',esc_attr__('Preview','atec-cache-info'),esc_attr__('Status','atec-cache-info'),esc_attr__('Description','atec-cache-info'),'#awards '.esc_attr__('PRO features','atec-cache-info')],'','atec-table-med');

		$atec_active			= ['cache-apcu','cache-info','database','debug','dir-scan',		'stats','system-info','web-map-service','webp','mega-cache'];
		$atec_review			= ['backup'];
					
		$c=0;
		global $wp_filesystem;
		WP_Filesystem();

		foreach ($atec_group_arr as $a)
		{
			$prefix = $a['name']==='mega-cache'?'':'atec-';
			if ($prefix==='') atec_empty_tr();
			$installed = $wp_filesystem->exists(WP_PLUGIN_DIR.'/'.esc_attr($prefix.$a['name']));
			$active = $installed && is_plugin_active(esc_attr($prefix.$a['name']).'/'.esc_attr($prefix.$a['name']).'.php');
			echo '<tr>';
				// @codingStandardsIgnoreStart
				// Image is not an attachement
				echo '
				<td><img alt="Plugin icon" src="',esc_url( plugins_url( '/assets/img/atec-group/atec_'.esc_attr($a['slug']).'_icon.svg', __DIR__ ) ) ,'" class="atec-plugin-icon" style="height: 22px;"></td>';
				// @codingStandardsIgnoreEnd
				$atecplugins='https://atecplugins.com/';
				$link=$a['wp']?'https://wordpress.org/plugins/'.$prefix.esc_attr($a['name']).'/':$atecplugins;
				echo '
				<td class="atec-nowrap"><a class="atec-nodeco" href="', esc_url($link) ,'" target="_blank">', esc_attr(atec_fix_name($a['name'])), '</a></td>';
				if ($a['wp']) echo '
					<td><span title="', esc_attr__('Published','atec-cache-info'), '" class="',esc_attr(atec_dash_class('wordpress')), '"></span></td>
					<td><a class="atec-nodeco" title="WordPress Playground" href="https://playground.wordpress.net/?plugin=', esc_attr($prefix.$a['name']), '&blueprint-url=https://wordpress.org/plugins/wp-json/plugins/v1/plugin/', esc_attr($prefix.$a['name']), '/blueprint.json" target="_blank"><span class="',esc_attr(atec_dash_class('welcome-view-site')), '"></span></a></td>';
				else 
				{
					$inReview=in_array($a['name'], $atec_review);
					echo '
					<td colspan="2">
						<span title="', $inReview?esc_attr__('In review','atec-cache-info'):esc_attr__('In progress','atec-cache-info'), '"><span class="',esc_attr(atec_dash_class($inReview?'visibility':'')) ,'"></span>
					</td>';
				}
				if ($installed) echo '<td title="Installed', ($active?' and active':''), '"><span class="',esc_attr(atec_dash_class(($active?'plugins-checked':'admin-plugins'), 'atec-'.($active?'green':'grey'))), '"></span></td>';
				else echo '
				<td>
					<a title="Download from atecplugins.com" class="atec-nodeco atec-vam button button-secondary" style="padding: 0px 4px;" target="_blank" href="', esc_url($atecplugins), 'WP-Plugins/atec-', esc_attr($a['name']), '.zip" download><span style="padding-top: 4px;" class="', esc_attr(atec_dash_class('download','')), '"></span></a></td>';
				echo '
				<td>',esc_attr($a['desc']),'</td>
				<td><small>',esc_attr($a['pro']),'</small></td>
				</tr>';
			$c++;
		} 
		echo '</tbody></table>
		</div>
		<center>
			<p class="atec-fs-12" style="max-width:80%;">',
				esc_attr__('All our plugins are optimized for speed, size and CPU footprint with an average of only 1 ms CPU time (frontend & backend)','atec-cache-info'), '.<br>',
				esc_attr__('Also, they share the same `atec-WP-plugin´ framework – so that shared code will only load once, even with multiple plugins enabled','atec-cache-info'), '.	<br>',
				esc_attr__('Tested with','atec-cache-info'), ': Linux (CloudLinux, Debian, Ubuntu), Windows & Mac-OS, Apache, NGINX & LiteSpeed.
			</p>
			<a class="atec-nodeco" class="atec-center" href="https://de.wordpress.org/plugins/search/atec/" target="_blank"><button class="button">', esc_attr__('All atec-plugins in the WordPress directory','atec-cache-info'), '.</button></a>
		</center>';
	}
	
	echo '
	</div>
</div>';
	
	if ($license) @require_once('atec-footer.php');
	atec_reg_inline_script('group','
	jQuery(".atec-page").css("gridTemplateRows","45px 1fr");
	jQuery("#atec_loading").css("opacity",0);', true);
	
}}

new ATEC_group();
?>