<?php
if (!defined( 'ABSPATH' )) { exit; }
class ATEC_wpci_results { function __construct() {

atec_check_admin_bar();
if (!class_exists('ATEC_wpc_tools')) @require_once('atec-wpc-tools.php');
if (!class_exists('ATEC_wp_memory')) @require_once('atec-wp-memory.php');
	
$wpc_tools=new ATEC_wpc_tools();
$mem_tools=new ATEC_wp_memory();

echo '
<div class="atec-page">';
	$mem_tools->memory_usage();
	atec_header(__DIR__,'wpci','Cache Info');	
	
	echo '
	<div class="atec-main">';
		atec_progress();

		global $wp_object_cache;
		$redis_enabled=class_exists('redis');			
		if ($redis_enabled)
		{
			$redisSettings=array();
			$redisSettings['unix'] = atec_clean_request('redis_unix');
			$redisSettings['host'] = atec_clean_request('redis_host');
			$redisSettings['port'] = atec_clean_request('redis_port');
			$options=get_option('atec_WPCI_settings',[]);
			if ($redisSettings['unix'].$redisSettings['host'].$redisSettings['port']!=='') { $options['redis']=$redisSettings; update_option('atec_WPCI_settings', $options, false); }
			else $redisSettings=$options['redis']??[];
		}

		$flush=atec_clean_request('flush');
		if ($flush!='')
		{
			echo '
			<div class="notice is-dismissible">
				<p>', esc_attr__('Flushing','atec-cache-info'), ' ', esc_html($flush),' ... ';
	
			$result=false;
			switch ($flush) 
			{
				case 'OPcache': $result=opcache_reset(); break;
				case 'WP_Ocache': $result=$wp_object_cache->flush(); break;
				case 'APCu': if (function_exists('apcu_clear_cache')) $result=apcu_clear_cache(); break;
				case 'Memcached': $m = new Memcached(); $m->addServer('localhost', 11211); $result=$m->flush(); break;
				case 'Redis': 
					{
						$redis = new Redis();
						try 
						{ 
							if (($redisSettings['unix']??'')!=='') $redis->connect(esc_url($redisSettings['unix']));
							else $redis->connect(($redisSettings['host']??'')!==''?$redisSettings['host']:'127.0.0.1', ($redisSettings['port']??'')!==''?absint($redisSettings['port']):6379); 
                        	$result=$redis->flushAll();
                    	}
						catch (Exception $e) 	{  echo '<font color="red">', esc_html(strtolower($e->getMessage())), '.', '</font>'; }
						break;
					}
				case 'SQLite': $result=$wp_object_cache->flush(); break;
			}
			echo $result?'<span class="atec-green">'.esc_attr__('successful','atec-cache-info').'</span>.':'<span class="atec-red">'.esc_attr__('failed','atec-cache-info').'</span>';
			echo '</p></div>';
		}
	
		$url	= atec_get_url();
		$nonce 	= wp_create_nonce(atec_nonce());
		$action	= atec_clean_request('action');
		$nav 	= atec_clean_request('nav');
		if ($nav=='') $nav='Cache';
				
		$licenseOk=atec_check_license()===true;
		atec_nav_tab($url, $nonce, $nav, ['#memory Cache','#server Server','#scroll OPC Scripts','#php PHP '.__('Extensions','atec-cache-info')], 2, !$licenseOk);
	
		echo '
		<div class="atec-border">';
			atec_flush();

			if ($nav=='Info') { @require_once('atec-info.php'); new ATEC_info(__DIR__); }
			elseif ($nav=='Server') {@require_once(__DIR__.'/atec-server-info.php'); }
			else if ($nav=='Cache')
			{				
				atec_reg_inline_style('atec_wpci_cache', '
				table td:nth-of-type(2), table td:nth-of-type(3) { text-align: right; } 
				table td:nth-of-type(3) { padding-left: 0; } 
				SMALL { font-size: 10px; }
				');
				
				$arr=array('Zlib'=>ini_get('zlib.output_compression')?'#yes-alt':'#dismiss');
				atec_little_block_with_info('Zend Opcode & WP '.__('Object Cache','atec-cache-info'), $arr);
								
				atec_reg_style('atec_cache_info',__DIR__,'atec-cache-info-style.min.css','1.0.001');

				$apcu_enabled=extension_loaded('apcu')  && apcu_enabled();
				$memcached_enabled=class_exists('Memcached');
			
				$wp_enabled=is_object($wp_object_cache);				
				$sql_enabled=function_exists('sqlite_object_cache');
	
				$opcache_enabled=false; $op_status=false; $op_conf=false; $opcache_file_only=false;
				if (function_exists('opcache_get_configuration'))
				{ 
					$op_conf=opcache_get_configuration(); 
					$opcache_enabled=$op_conf['directives']['opcache.enable']; 
					if (function_exists('opcache_get_status')) $op_status=opcache_get_status();
					$opcache_file_only=$op_conf['directives']['opcache.file_cache_only'];
				}
				else { $opcache_enabled=true; }

				echo '
				<div class="atec-g atec-g-25">
					<div class="atec-border-white">
						<h4>OPcache '; $wpc_tools->enabled($opcache_enabled);
						if ($opcache_enabled && !$opcache_file_only) echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" href="', esc_url($url), '&flush=OPcache&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span>', esc_attr__('Flush','atec-cache-info'),  '</a>';
						echo '
						</h4><hr>';
						if ($opcache_enabled) {@require_once(__DIR__.'/atec-OPC-info.php'); new ATEC_OPcache_info($op_conf,$op_status,$opcache_file_only,$wpc_tools); }
						else $wpc_tools->p('OPcache '.esc_attr(__('extension is NOT installed/enabled','atec-cache-info')));
						require_once('atec-OPC-help.php');
					echo '
					</div>
					
					<div class="atec-border-white">
						<h4>WP '.esc_attr__('Object Cache','atec-cache-info').' '; $wpc_tools->enabled($wp_enabled);
						if ($wp_enabled) echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" id="WP_Ocache_flush" href="', esc_url($url), '&flush=WP_Ocache&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span>', esc_attr__('Flush','atec-cache-info'),  '</a>';
						echo '
						</h4><hr>';
						if ($wp_enabled) {@require_once(__DIR__.'/atec-WPC-info.php'); new ATEC_WPcache_info($op_conf,$op_status,$opcache_file_only,$wpc_tools); }			
						else $wpc_tools->error('WP '.__('object cache','atec-cache-info'),__('not available','atec-cache-info'));
					echo '
					</div>';
					
					$jit=false; $jitStatus=false;
					if (!$op_status) 
					{
						$jit=isset($op_status['jit']) && $op_status['jit']['enabled'] && $op_status['jit']['on']; 
					}
					else { $jit=ini_get('opcache.jit')!=0; }
					echo '
					<div class="atec-border-white">
						<h4>JIT '; $wpc_tools->enabled($jit);
						echo '
						</h4><hr>';
						if ($jit) {@require_once(__DIR__.'/atec-JIT-info.php'); new ATEC_JIT_info($wpc_tools,$op_status); }
						else 
						{ 
							if (extension_loaded('xdebug') && strtolower(ini_get('xdebug.mode'))!=='off') $wpc_tools->error('Xdebug',esc_attr(__('is enabled, so JIT will not work','atec-cache-info'))); 
							else $wpc_tools->p(esc_attr(__('JIT is NOT enabled in php.ini','atec-cache-info')));
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
			
				atec_little_block('Persistent '.__('Object Cache','atec-cache-info'));
			
				echo'
				<div class="atec-g atec-g-25">
					<div class="atec-border-white">
						<h4>APCu '; $wpc_tools->enabled($apcu_enabled);
						if ($apcu_enabled) echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" id="APCu_flush" href="', esc_url($url), '&flush=APCu&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span>', esc_attr__('Flush','atec-cache-info'),  '</a>';
						echo '
						</h4><hr>';
						if ($apcu_enabled) {@require_once(__DIR__.'/atec-APCu-info.php'); new ATEC_APCu_info($wpc_tools); }
						else 
						{
							$wpc_tools->p('APCu '.esc_attr__('extension is NOT installed/enabled','atec-cache-info'));
							echo '<div class="atec-mt-5">'; @require_once(__DIR__.'/atec-APCu-help.php'); echo '</div>';
						}
	
					echo '
					</div>
					
					<div class="atec-border-white">
						<h4>Memcached '; $wpc_tools->enabled($memcached_enabled);
						if ($memcached_enabled) echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" id="Memcached_flush" href="', esc_url($url), '&flush=Memcached&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span>', esc_attr__('Flush','atec-cache-info'),  '</a>';
						echo '
						</h4><hr>';
						if ($memcached_enabled) { @require_once(__DIR__.'/atec-memcached-info.php'); new ATEC_memcached_info($wpc_tools); }
						else $wpc_tools->p('Memcached '.esc_attr(__('extension is NOT installed/enabled','atec-cache-info')));	
					echo '
					</div>
					
					<div class="atec-border-white">
						<h4>Redis '; $wpc_tools->enabled($redis_enabled);
						if ($redis_enabled) echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" id="Redis_flush" href="', esc_url($url), '&flush=Redis&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span>', esc_attr__('Flush','atec-cache-info'),  '</a>';
						echo '
						</h4><hr>';
						if ($redis_enabled) { @require_once(__DIR__.'/atec-Redis-info.php'); new ATEC_Redis_info($url,$nonce,$wpc_tools,$redisSettings); }
						else $wpc_tools->p('Redis '.__('extension is NOT installed/enabled','atec-cache-info'));
					echo '
					</div>
					
					<div class="atec-border-white">
						<h4>SQLite '; $wpc_tools->enabled($sql_enabled);
						if ($sql_enabled) echo '<a title="', esc_attr__('Empty cache','atec-cache-info'), '" class="atec-right button" id="SQLite_flush" href="', esc_url($url), '&flush=SQLite&_wpnonce=', esc_attr($nonce), '"><span class="', esc_attr(atec_dash_class('trash')), '"></span>', esc_attr__('Flush','atec-cache-info'),  '</a>';
						echo '
						</h4><hr>';						
						if ($sql_enabled) { @require_once(__DIR__.'/atec-SQLite-info.php'); new ATEC_SQLite_info($wpc_tools, $wp_object_cache); }
						else $wpc_tools->p('SQLite '.esc_attr(__('object cache is NOT enabled','atec-cache-info')));
					echo '
					</div>
				</div>';
			}
			elseif ($nav=='OPC_Scripts') 
			{ 
				if (atec_pro_feature('`OPC Scripts´ lists all scripts files and statistics of in the OPcache memory')) 
				{ 
					atec_include_if_exists(__DIR__,'atec-OPC-groups.php');
					if (class_exists('ATEC_oc_groups')) new ATEC_oc_groups($url,$nonce,$action);
					else atec_missing_class_check();
				}
			}
			elseif ($nav=='PHP_'.__('Extensions','atec-cache-info')) 
			{ 
				if (atec_pro_feature('`Extension´ lists all active PHP extensions and checks whether recommended extensions are installed')) 
				{ 
					atec_include_if_exists(__DIR__,'atec-extensions-info.php');
					if (class_exists('ATEC_extensions_info')) new ATEC_extensions_info();
					else atec_missing_class_check();
				}
			}
		
		echo '
		</div>
	</div>
</div>';

if (!class_exists('ATEC_footer')) @require_once('atec-footer.php');

}}

new ATEC_wpci_results;
?>