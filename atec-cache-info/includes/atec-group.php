<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_group {
	
private function atec_group_is_plugin_active($plugins,$plugin)
{
	foreach ($plugins as $p) { if (str_starts_with($p,$plugin)) return $p; }
	return false;
}

private function atec_clean_request_license($t): string { return atec_clean_request($t,'atec_license_nonce'); } 

private function atec_group_star_list($mega)
{
	echo 
	'<div id="pro_package">
		<div class="atec-border-white atec-bg-w atec-fit" style="font-size: 16px !important; padding: 0 20px; text-align: left; margin:0 auto;">
			<ul class="atec-p-0">
				<li>🎁 <strong>', $mega?'Seven additional storage options':esc_attr__('Including 32 valuable plugins','atec-cache-info'), '.</strong></li>
				<li style="line-height:5px;"><br></li>
				<li>⭐ ', esc_attr__('Priority support','atec-cache-info'), '.</li>
				<li>⭐ ', esc_attr__('Upgrades & updates','atec-cache-info'), '.</li>';
				
				if ($mega) 
					echo '
					<li>⭐ Custom post types.</li>
					<li>⭐ WooCommerce product caching.</li>';
				else
					echo '
					<li>⭐ ', esc_attr__('„Lifetime-site-License“','atec-cache-info'), '.</li>
					<li>⭐ ', esc_attr__('Access to all the „PRO“ features','atec-cache-info'), '.</li>';		
			echo 
			'</ul>
		</div>';
}

private function atec_fix_name($p) { return ucwords(str_replace(['-','apcu','webp','svg','htaccess'],[' ','APCu','WebP','SVG','HTaccess'],$p)); }

private function atec_group_badge($str,$option,$reverse=false) 
{
	$option = filter_var($option,258);
	$str.=' is ';
	if ($reverse) { $str1=$str.' OFF'; $str2=$str.' ON'; $option=!$option; }
	else { $str1=$str.' ON'; $str2=$str.' OFF'; }
	atec_badge($str1,$str2,$option); 
}

function __construct() {
	
if (!function_exists('atec_header')) @require(__DIR__.'/atec-tools.php');	

$url				= atec_get_url();
$nonce 		= wp_create_nonce(atec_nonce());
$action 		= atec_clean_request('action');
$nav		 		= atec_clean_request('nav');
if ($nav==='') $nav='Dashboard';

$atec_group_arr=[];
require(__DIR__.'/atec-group-array.php');

$license 			= $this->atec_clean_request_license('license');
if ($license==='') $license = atec_clean_request('license');
if ($license==='true') $nav='License';

$plugin = $this->atec_clean_request_license('plugin');
if ($plugin==='') $plugin = atec_clean_request('plugin');

$integrity				= $this->atec_clean_request_license('integrity');
$integrityString 	= '';
if ($integrity!=='')
{
	$integrityString='Thank you. Connection to atecplugins.com is '.($integrity=='true'?'enabled':'disabled');
	if ($integrity=='true') atec_integrity_check(__DIR__,$plugin);
	update_option('atec_allow_integrity_check',$integrity);
}

$goupAssetPath = plugins_url('/assets/img/atec-group/',__DIR__);

if ($nav!=='License')
{
	if (!class_exists('ATEC_fs')) @require('atec-fs.php');
	$afs = new ATEC_fs();
}

echo 
'<div class="atec-page">';
	$licenseOk=atec_header(__DIR__,'','');
	if ($integrityString!=='') { echo '<br><center>'; atec_success_msg($integrityString); echo '</center>'; }

	echo 
	'<div class="atec-main">';
		atec_progress();
	
		$navs=['#admin-home Dashboard','#admin-plugins Plugins Overview','#awards License'];
		atec_nav_tab($url, $nonce, $nav, $navs);
		
		echo 
		'<div class="atec-g atec-border" style="padding: 20px 10px;">';

			if ($nav==='Dashboard')
			{			
				atec_little_block('Active plugins status and essential settings');
				$activePlugins = get_option('active_plugins');
				
				echo 
				'<small class="atec-mt-0">This dashboard shows the status of plugins performing some kind of background tasks – just for your convenience.</small>
				<div class="atec-border">';
				foreach($atec_group_arr as $p)
				{ 
					$prefix	=	$afs->prefix($p['name']);
					$installed = $afs->exists(WP_PLUGIN_DIR.'/'.esc_attr($prefix.$p['name']));
					$essentialPlugins = ['backup','cache-apcu','cache-memcached','cache-redis','debug','deploy','developer','limit-login','login-url','optimize','profiler','stats','smtp-mail','temp-admin','webp','mega-cache'];
					if ($installed)
					{
						$active = $this->atec_group_is_plugin_active($activePlugins,$prefix.$p['name']);
						if ($active && in_array($p['name'], $essentialPlugins))
						{
							echo 
							'<div class="atec-dilb atec-fit atec-vat">
								<p class="atec-bold atec-mb-0 atec-ml-10"><img class="atec-plugin-icon" src="', esc_url($goupAssetPath.'atec_'.$p['slug'].'_icon.svg'), '" style="height: 16px;">&nbsp;', $this->atec_fix_name($p['name']).'</p>
								<div class="atec-border atec-bg-w6" style="padding:0 0 0 10px; margin: 0 10px 0 0; order:0;">
								<hr style="border-color:white;">';
								
								switch ($p['name'])
								{
									case 'backup': 
										$atec_wpb_settings=get_option('atec_WPB_settings',[]);
										$automatic = filter_var($atec_wpb_settings['automatic']??0,258);
										$this->atec_group_badge('Automatic backup',$automatic);
										if ($automatic)
										{
											echo 
											'<table class="atec-mb-5 atec-dil atec-mr-10">
												<tr>';
													$autoArr=['db','files','content'];
													foreach($autoArr as $a)
													{
														$schedule = $atec_wpb_settings['cron_'.$a]??'-/-';
														echo
														'<td class="atec-bg-w atec-border" style="padding: 2px 6px; margin-right: 10px;">
															<strong>', esc_attr($a), '</strong>: <span', $schedule!==''?' class="atec-green"':'', '>', esc_attr($schedule), '</span>
														</td>';
													}
												echo 
												'</tr>
											</table>';
										}
										break;
									case 'cache-apcu':
										global $atec_wpca_settings; 
										$this->atec_group_badge('Object-Cache',defined('WP_APCU_KEY_SALT'));
										$this->atec_group_badge('Page-Cache',filter_var($atec_wpca_settings['cache']??0,258));
										break;				
									case 'cache-memcached':
										$this->atec_group_badge('Object-Cache',defined('WP_MEMCACHED_KEY_SALT'));
										break;									
									case 'cache-redis':
										$this->atec_group_badge('Object-Cache',defined('WP_REDIS_KEY_SALT'));
										break;	
									case 'debug': 
										$this->atec_group_badge('WP_DEBUG',defined('WP_DEBUG') && WP_DEBUG,true);
										$this->atec_group_badge('WP_DEBUG_DISPLAY',defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY,true);
										$this->atec_group_badge('WP_DEBUG_LOG',defined('WP_DEBUG_LOG') && WP_DEBUG_LOG,true);
										break;																		
									case 'deploy': 
										$this->atec_group_badge('Auto deploy',get_option('atec_WPDP_settings',false));
										break;
									case 'developer': 
										$this->atec_group_badge('Developer console',get_option('atec_WPDV_settings',false));
										break;
									case 'limit-login': 
										atec_success_msg('Limit login is active');
										break;						
									case 'login-url': 
										$this->atec_group_badge('Custom login URL',(get_option('atec_WPLU_settings',[])['url']??'')!=='');
										break;	
									case 'optimize': 
										$arr=get_option('atec_WPO_settings',[]); $active='';
										if (getType($arr)==='array') foreach($arr as $a) { $active.=$a; break; }
										$this->atec_group_badge('Optimizing',$active!=='');
										break;									
									case 'profiler': 
										$this->atec_group_badge('Profiler',defined('ATEC_MU_PROFILER') && ATEC_MU_PROFILER,true);
										$this->atec_group_badge('PP profiler',defined('ATEC_MU_PP_PROFILER') && ATEC_MU_PP_PROFILER,true);								
										break;		
									case 'stats': 
										atec_success_msg('Statistics logging is active');
										break;				
									case 'smtp-mail': 
										$str = 'Mail settings are ';
										atec_badge($str.'confirmed',$str.'NOT tested',get_option('atec_WPSM_mail_tested',false));
										break;
									case 'temp-admin': 
										$users = get_users();
										$tempUsers = false;
										foreach($users as $u) { if (str_starts_with($u->user_login,'atec_wpta_')) { $tempUsers=true; break; } }
										atec_badge('No temp admins defined','Some temp admins are active',!$tempUsers);
										break;
									case 'webp': 
										$this->atec_group_badge('WebP conversion',get_option('atec_wpwp_active',false));
										break;
									case 'mega-cache':
										global $atec_wpmc_settings; 
										$this->atec_group_badge('Page-Cache',defined('MEGA_CACHE_UPLOAD') && MEGA_CACHE_UPLOAD);
										break;
								}
								echo 
								'</div>
							</div>';
						}
						
					}
				}
				echo 
				'</div>
				<div class="tablenav">';
					atec_nav_button($url,$nonce,'_','Plugins_Overview','All atec-Plugins and features');
				echo '
				</div>';
			}
			elseif ($nav=='Plugins_Overview')
			{
				echo 
				'<center>'; atec_little_block('All atec-Plugins and features'); echo '</center>
				<div class="atec-g atec-fit" style="margin:0 auto;">';
					atec_table_header_tiny(['','Name (Link)','#wordpress','#admin-multisite',esc_attr__('Status','atec-cache-info'),esc_attr__('Description','atec-cache-info'),'#awards '.esc_attr__('PRO features','atec-cache-info')],'','atec-table-med');
			
					$atec_active			= ['cache-apcu','cache-info','database','debug','dir-scan',		'stats','system-info','web-map-service','webp','mega-cache'];
					$atec_review			= ['backup'];	
					$c=0;		
					foreach ($atec_group_arr as $a)
					{
						$prefix = $a['name']==='mega-cache'?'':'atec-';
						if ($prefix==='') atec_empty_tr();
						$installed = $afs->exists(WP_PLUGIN_DIR.'/'.esc_attr($prefix.$a['name']));
						$active = $installed && is_plugin_active(esc_attr($prefix.$a['name']).'/'.esc_attr($prefix.$a['name']).'.php');
						echo '<tr>';
							// @codingStandardsIgnoreStart | Image is not an attachement
							echo '
							<td><img class="atec-plugin-icon" alt="Plugin icon" src="',esc_url($goupAssetPath.'atec_'.esc_attr($a['slug']).'_icon.svg'), '" style="height: 22px;"></td>';
							// @codingStandardsIgnoreEnd
							$atecplugins='https://atecplugins.com/';
							$link=$a['wp']?'https://wordpress.org/plugins/'.$prefix.esc_attr($a['name']).'/':$atecplugins;
							echo '
							<td class="atec-nowrap"><a class="atec-nodeco" href="', esc_url($link) ,'" target="_blank">', esc_attr($this->atec_fix_name($a['name'])), '</a></td>';
							if ($a['wp']) echo '
								<td><a class="atec-nodeco" title="WordPress Playground" href="https://playground.wordpress.net/?plugin=', esc_attr($prefix.$a['name']), '&blueprint-url=https://wordpress.org/plugins/wp-json/plugins/v1/plugin/', esc_attr($prefix.$a['name']), '/blueprint.json" target="_blank"><span class="',esc_attr(atec_dash_class('welcome-view-site')), '"></span></a></td>';
							else 
							{
								$inReview=in_array($a['name'], $atec_review);
								echo 
								'<td>
									<span title="', $inReview?esc_attr__('In review','atec-cache-info'):esc_attr__('In progress','atec-cache-info'), '"><span class="',esc_attr(atec_dash_class($inReview?'visibility':'')) ,'"></span>
								</td>';
							}
							echo '<td>', $a['multi']?'<span class="'.esc_attr(atec_dash_class('yes')).'"></span>':'', '</td>';
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
					atec_table_footer();
				echo 
				'</div>
					
				<center>
					<p class="atec-fs-12" style="max-width:80%;">',
						esc_attr__('All our plugins are optimized for speed, size and CPU footprint with an average of only 1 ms CPU time','atec-cache-info'), '.<br>',
						esc_attr__('Also, they share the same „atec-WP-plugin“ framework. Shared code will only load once across multiple plugins','atec-cache-info'), '.	<br>',
						esc_attr__('Tested with','atec-cache-info'), ': Linux (CloudLinux, Debian, Ubuntu), Windows & Mac-OS, Apache, NGINX & LiteSpeed.
					</p>
					<a class="atec-nodeco" class="atec-center" href="https://de.wordpress.org/plugins/search/atec/" target="_blank"><button class="button">', esc_attr__('Visit atec-plugins in the WordPress directory','atec-cache-info'), '.</button></a>
				</center>';
			}
			elseif ($nav==='License')
			{
				$mega = $plugin==='mega-cache';
				if (!extension_loaded('openssl')) atec_admin_notice('warning','The openSSL extension is required for license handling.',true);

				echo 
				'<div class="atec-g atec-center" style="padding: 20px 10px;">
					<h3 class="atec-mt-0">';
					// @codingStandardsIgnoreStart | Image is not an attachement
					echo '<sub><img class="atec-plugin-icon" alt="Plugin icon" src="', esc_url($goupAssetPath.'atec_'.($mega?'wpmc':'wpa').'_icon.svg'), '" style="height: 22px;"></sub>&nbsp;';
					// @codingStandardsIgnoreEnd
					echo $mega?'Mega-Cache „PRO“ package':esc_attr__('atec-Plugins „PRO“ package','atec-cache-info'), 
					'</h3>';
					$this->atec_group_star_list($mega);
					echo 
					'<div class="atec-db atec-fit atec-box-white" style="margin: 25px auto; padding-bottom:0;">';
						if ($mega)
						{
							$pattern = '/atec-[\w\-]+/';
							$imgSrc = preg_replace($pattern, 'mega-cache', plugins_url( '/assets/img/logos/', __DIR__ ));
							foreach (['apcu','redis','memcached','sqlite','mongodb','mariadb','mysql'] as $a)
							{
								// @codingStandardsIgnoreStart | Image is not an attachement
								echo '<img class="atec-plugin-icon" src="', esc_url($imgSrc.$a.'.svg'), '" style="height: 22px; margin: 0 5px 10px 5px;">';
								// @codingStandardsIgnoreEnd
							}
						}
						else
						{
							$c=0;
							foreach ($atec_group_arr as $a)
							{
								$c++;
								if ($a['slug']==='wpmc') continue;
								if ($c % 18===0) echo '<br>';
								// @codingStandardsIgnoreStart | Image is not an attachement
								echo '<img class="atec-plugin-icon" src="', esc_url($goupAssetPath.'atec_'.$a['slug'].'_icon.svg'), '" style="height: 22px; margin: 0 5px 10px 5px;">';
								// @codingStandardsIgnoreEnd
							}	
						}
						echo 
					'</div>';
					
					$licenseUrl = $mega?'https://wpmegacache.com/license/':'https://atecplugins.com/license';
					echo 
					'<a class="atec-nodeco" style="width: fit-content !important; margin: 10px auto;" href="', esc_textarea($licenseUrl), '" target="_blank">
						<button class="button button-primary">', esc_attr__('GET YOUR „PRO“ PACKAGE NOW','atec-cache-info'), '</button>
					</a>
					<div class="atec-small">Links to ', esc_textarea($licenseUrl), '</div>';
		
					echo 
					'<p styl="font-size: 18px !important;">',
						esc_attr__('Buy the „PRO“ package through one time payment','atec-cache-info'), '.<br>',
						esc_attr__('The license is valid for the lifetime of your site (domain)','atec-cache-info'), '.<br><b>',
						esc_attr__('No subscription. No registration required.','atec-cache-info'), '</b>
					</p>';
					
				echo 
				'</div>';
							
				$include=__DIR__.'/atec-pro.php';
				if (!class_exists('ATEC_pro') && file_exists($include)) @include_once($include);
				if (class_exists('ATEC_pro')) { (new ATEC_pro)->atec_pro_form($url, $nonce, atec_clean_request('licenseCode'), $plugin); }
			}

			echo '
		</div>
	</div>
</div>';

if ($license) @require('atec-footer.php');
atec_reg_inline_script('group','jQuery(".atec-page").css("gridTemplateRows","45px 1fr"); jQuery(".atec-progressBar").css("background","transparent");', true);
	
}}

new ATEC_group();
?>