<?php
if (!defined('ABSPATH')) { exit(); }
class ATEC_wpci_results { function __construct() {

atec_fixit(dirname(__DIR__),'cache-info','wpci'); // backwards compatible unix path

$url			= atec_get_url();
$nonce 	= wp_create_nonce(atec_nonce());
$action 	= atec_clean_request('action');
$nav 		= atec_clean_request('nav');
if ($nav=='') $nav='Cache';

if ($action==='adminBar') atec_check_admin_bar('wpci',$url,$nonce,$nav);

if (!class_exists('ATEC_wp_memory')) @require('atec-wp-memory.php');
$mem_tools=new ATEC_wp_memory();

echo '
<div class="atec-page">';
	$mem_tools->memory_usage();
	atec_header(__DIR__,'wpci','Cache Info');	
	
	echo '
	<div class="atec-main">';
		atec_progress();

		$licenseOk=atec_check_license()===true;
		atec_nav_tab($url, $nonce, $nav, ['#memory Cache','#server Server','#scroll OPC '.esc_attr__('Scripts','atec-cache-info'),'#php PHP '.__('Extensions','atec-cache-info')], 2, !$licenseOk);
	
		echo '
		<div class="atec-border">';
			atec_flush();

			if ($nav=='Info') { @require('atec-info.php'); new ATEC_info(__DIR__); }
			{
				global $wp_object_cache;
				$optName = 'atec_WPCI_settings';
				$options=get_option($optName,[]); 
				
				$redSettings = $options['redis']??[];
				$memSettings = $options['memcached']??[];
				$red_enabled = class_exists('redis');
				$mem_enabled = class_exists('Memcached');
				
				if ($action!=='')
				{
					$arr = 
					[
						['action'=>'saveRed', 'type'=>'redis', 'fields'=>['conn','host','port','pwd']],
						['action'=>'saveMem', 'type'=>'memcached', 'fields'=>['conn','host','port']]
					];
					foreach($arr as $a)
					{
						if ($action===$a['action'])
						{
							$option=$options[$a['type']]??[];
							foreach($a['fields'] as $o)	$option[$o]=atec_clean_request($a['type'].'_'.$o);
							$options[$a['type']]=$option; update_option('atec_WPCI_settings', $options, false); 
							if ($a['type']==='redis') $redSettings=$option;
							else $memSettings=$option;
						}
					}
				}
				
				if ($action==='flush')
				{
					$type = atec_clean_request('type');
					echo '
					<div class="notice is-dismissible">
						<p>', esc_attr__('Flushing','atec-cache-info'), ' ', esc_html(str_replace('_',' ',$type)),' ... ';
				
					$result=false;
					switch ($type) 
					{
						case 'OPcache': $result=opcache_reset(); break;
						case 'WP_Ocache': 
						{
							if ($_wp_using_ext_object_cache = wp_using_ext_object_cache()) wp_using_ext_object_cache(false);
							$result = wp_cache_flush(); wp_cache_init();
							if ($_wp_using_ext_object_cache) 	wp_using_ext_object_cache(true);
							break;
						}
						case 'APCu': if (function_exists('apcu_clear_cache')) $result=apcu_clear_cache(); break;
						case 'Memcached': 
							{
								if (!function_exists('atec_memcached_connect')) @require('atec-cache-memcached-connect.php');
								$result = atec_memcached_connect($memSettings);
								$m = $result['m'];
								$result=$m?$m->flush():false;
								break;
							}
						case 'Redis': 
							{
								if (!function_exists('atec_redis_connect')) @require('atec-cache-redis-connect.php');
								$result = atec_redis_connect($redSettings);
								$redis = $result['redis'];
								$result=$redis?$redis->flushAll():false;
								break;
							}
						case 'SQLite': $result=$wp_object_cache->flush(); break;
					}
					if (!$result) echo '<span class="atec-green">', esc_attr__('failed','atec-cache-apcu'), '</span>.';
					echo '</p>
					</div>';
					if ($result) atec_reg_inline_script('wpci_redirect','window.location.assign("'.esc_url($url).'&nav=Cache&action=flushed&type='.$type.'&_wpnonce='.$nonce.'")'); 
				}

				if (!class_exists('ATEC_wpc_tools')) @require('atec-wpc-tools.php');
				$wpc_tools=new ATEC_wpc_tools();

				if ($nav=='Server') {@require(__DIR__.'/atec-server-info.php'); }
				else if ($nav=='Cache')
				{				
					atec_reg_inline_style('wpci_cache', '
					table td:nth-of-type(2), table td:nth-of-type(3) { text-align: right; } 
					table td:nth-of-type(3) { padding-left: 0; } 
					SMALL { font-size: 10px; }
					');
					
					$arr=array('Zlib'=>ini_get('zlib.output_compression')?'#yes-alt':'#dismiss');
					atec_little_block_with_info('Zend Opcode & WP '.__('Object Cache','atec-cache-info'), $arr);
					
					if (str_contains($action,'flushed')) 	atec_success_msg(esc_attr__('Flushing','atec-cache-apcu').' '.esc_html(str_replace('_',' ',atec_clean_request('type'))).' '.esc_attr__('successful','atec-cache-apcu'));
					
					atec_reg_style('atec_cache_info',__DIR__,'atec-cache-info-style.min.css','1.0.002');
	
					$apcu_enabled=extension_loaded('apcu')  && apcu_enabled();
				
					$wp_enabled=is_object($wp_object_cache);				
					$sql_enabled=function_exists('sqlite_object_cache');
		
					$opc_enabled=false; $opc_status=false; $opc_conf=false; $opcache_file_only=false;
					if (function_exists('opcache_get_configuration'))
					{ 
						$opc_conf=opcache_get_configuration(); 
						$opc_enabled=$opc_conf['directives']['opcache.enable']; 
						if (function_exists('opcache_get_status')) $opc_status=opcache_get_status();
						$opcache_file_only=$opc_conf['directives']['opcache.file_cache_only'];
					}
					else { $opc_enabled=true; }
	
					echo '
					<div class="atec-g atec-g-25">
						<div class="atec-border-white">
							<h4>OPcache '; atec_enabled($opc_enabled);
							if ($opc_enabled && !$opcache_file_only) 
							echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" href="', esc_url($url), '&action=flush&type=OPcache&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span><span>', esc_attr__('All','atec-cache-info'), '</span></a>';
							echo '
							</h4><hr>';
							if ($opc_enabled) {@require(__DIR__.'/atec-OPC-info.php'); new ATEC_OPcache_info($opc_conf,$opc_status,$opcache_file_only,$wpc_tools); }
							else atec_p('OPcache '.esc_attr__('extension is NOT installed/enabled','atec-cache-info'));
							@require('atec-OPC-help.php');
						echo '
						</div>
						
						<div class="atec-border-white">
							<h4>WP ', esc_attr__('Object Cache','atec-cache-info'), ' '; atec_enabled($wp_enabled);
							if ($wp_enabled) echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" id="WP_Ocache_flush" href="', esc_url($url), '&action=flush&type=WP_Ocache&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span><span>', esc_attr__('Site','atec-cache-info'), '</span></a>';
							echo '
							</h4><hr>';
							if ($wp_enabled) { @require(__DIR__.'/atec-WPC-info.php'); new ATEC_WPcache_info($wpc_tools); }			
							else atec_error_msg('WP '.__('object cache','atec-cache-info'),__('not available','atec-cache-info'));
						echo '
						</div>';
						
						$jit=false; $jitStatus=false;
						if (!$opc_status) { $jit=isset($opc_status['jit']) && $opc_status['jit']['enabled'] && $opc_status['jit']['on']; }
						else { $jitIni=ini_get('opcache.jit'); $jit=$jitIni!=0 && $jitIni!=='disable'; }
						echo '
						<div class="atec-border-white">
							<h4>JIT '; atec_enabled($jit);
							echo '
							</h4><hr>';
							if ($jit) { @require(__DIR__.'/atec-JIT-info.php'); new ATEC_JIT_info($wpc_tools,$opc_status); }
							else 
							{ 
								if (extension_loaded('xdebug') && strtolower(ini_get('xdebug.mode'))!=='off') atec_error_msg('Xdebug '.__('is enabled, so JIT will not work','atec-cache-info'));
								else atec_p('JIT '.esc_attr__('is NOT enabled','atec-cache-info'));
								echo '<br>'; 
							}						
							atec_help('jit',__('Recommended settings','atec-cache-info'));
							echo '
							<div id="jit_help" class="atec-help">
								<p class="atec-bold atec-mb-5 atec-mt-0">', esc_attr__('Recommended settings','atec-cache-info'), ':</p>
								<ul class="atec-m-0">
									<li>opcache.jit=1254</li>
									<li>opcache.jit_buffer_size=8M</li>
								</ul>
							</div>						
						</div>
					</div>';
				
					atec_little_block(__('Persistent','atec-cache-info').' '.__('Object Cache','atec-cache-info'));
				
					echo'
					<div class="atec-g atec-g-25">
						<div class="atec-border-white">
							<h4>APCu '; atec_enabled($apcu_enabled);
							if ($apcu_enabled) echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" id="APCu_flush" href="', esc_url($url), '&action=flush&type=APCu&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span><span>', esc_attr__('All','atec-cache-info'), '</span></a>';
							echo '
							</h4><hr>';
							if ($apcu_enabled) {@require(__DIR__.'/atec-APCu-info.php'); new ATEC_APCu_info($wpc_tools); }
							else 
							{
								atec_p('APCu '.esc_attr__('extension is NOT installed/enabled','atec-cache-info'));
								echo '<div class="atec-mt-5">'; @require(__DIR__.'/atec-APCu-help.php'); echo '</div>';
							}
		
						echo '
						</div>
						
						<div class="atec-border-white">
							<h4>Memcached '; atec_enabled($mem_enabled);
							if ($mem_enabled) echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" id="Memcached_flush" href="', esc_url($url), '&action=flush&type=Memcached&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span><span>', esc_attr__('All','atec-cache-info'), '</span></a>';
							echo '
							</h4><hr>';
							if ($mem_enabled) { @require(__DIR__.'/atec-Memcached-info.php'); new ATEC_memcached_info($url,$nonce,$wpc_tools,$memSettings); }
							else atec_p('Memcached '.esc_attr__('extension is NOT installed/enabled','atec-cache-info'));	
						echo '
						</div>
						
						<div class="atec-border-white">
							<h4>Redis '; atec_enabled($red_enabled);
							if ($red_enabled) echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" id="Redis_flush" href="', esc_url($url), '&action=flush&type=Redis&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span><span>', esc_attr__('All','atec-cache-info'), '</span></a>';
							echo '
							</h4><hr>';
							if ($red_enabled) { @require(__DIR__.'/atec-Redis-info.php'); new ATEC_Redis_info($url,$nonce,$wpc_tools,$redSettings); }
							else atec_p('Redis '.__('extension is NOT installed/enabled','atec-cache-info'));
						echo '
						</div>
						
						<div class="atec-border-white">
							<h4>SQLite '; atec_enabled($sql_enabled);
							if ($sql_enabled) echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" id="SQLite_flush" href="', esc_url($url), '&action=flush&type=SQLite&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span><span>', esc_attr__('Site','atec-cache-info'), '</span></a>';
							echo '
							</h4><hr>';						
							if ($sql_enabled) { @require(__DIR__.'/atec-SQLite-info.php'); new ATEC_SQLite_info($wpc_tools, $wp_object_cache); }
							else atec_p('SQLite '.esc_attr__('object cache','atec-cache-info').' '.esc_attr__('is NOT enabled','atec-cache-info'));
						echo '
						</div>
					</div>';
				}
				elseif (str_starts_with($nav,'OPC_'))
				{ 
					if (atec_pro_feature('„OPC Scripts“ lists all scripts files and statistics of in the OPcache memory')) 
					{ 
						atec_include_if_exists(__DIR__,'atec-OPC-groups.php');
						if (class_exists('ATEC_oc_groups')) new ATEC_oc_groups($url,$nonce,$action);
						else atec_missing_class_check();
					}
				}
				elseif ($nav=='PHP_'.__('Extensions','atec-cache-info')) 
				{ 
					if (atec_pro_feature('„Extension“ lists all active PHP extensions and checks whether recommended extensions are installed')) 
					{ 
						atec_include_if_exists(__DIR__,'atec-extensions-info.php');
						if (class_exists('ATEC_extensions_info')) new ATEC_extensions_info();
						else atec_missing_class_check();
					}
				}
			}
		
		echo '
		</div>
	</div>
</div>';

@require('atec-footer.php');

}}

new ATEC_wpci_results;
?>