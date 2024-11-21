<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_pro { 

public function atec_pro_check_license($licenseCode=null, $siteName=null, $plugin=null)
{
	if ($plugin) $suffix=$plugin==='mega-cache'?'_MEGA':'';
	else { $slug=atec_get_slug(); $suffix=$slug==='atec_wpmc'?'_MEGA':''; }
	$atec_license_code='atec_license_code'.$suffix;
	if (!$licenseCode)
	{
		if (get_transient($atec_license_code)) return true;
		$licenseCode=get_option($atec_license_code,'');
	}
	if ($licenseCode==='') return false; // 'Empty license code';
	if (!$siteName) $siteName=wp_parse_url(get_site_url(),PHP_URL_HOST);
	if(!extension_loaded('openssl')) return 'OpenSSL extension is required to verify the license';

$publicKey='-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC5zzmHZQNovGx5j6v3I+E9d3ry
5vqZJduXkur14y8g3dHUIwKBTT8LdGnNA6injcj0C7ja75zGTphPBXIhql756XhJ
UlHIMXYppEhrzp8SpmpLViw98aMQuBmglj78kFmR8yI6VZG10H4Pl2MsDdBhA/x2
riq3aeQVZ8yllQ3bbwIDAQAB
-----END PUBLIC KEY-----';

	@openssl_public_decrypt(base64_decode($licenseCode), $decrypted, $publicKey);
	$licenseOk=in_array($decrypted,[$siteName.$suffix,$siteName.'MULTI']);
	if ($licenseOk) { set_transient($atec_license_code,true,86400); update_option($atec_license_code,$licenseCode); }
	else { delete_transient($atec_license_code); delete_option($atec_license_code); }
	return $licenseOk;
}

public function atec_pro_form($url, $nonce, $licenseCode, $plugin)
{
	if ($licenseCode==='') $licenseCode=get_option('atec_license_code','');
	else update_option('atec_license_code',$licenseCode,'auto');
	$siteName=wp_parse_url(get_site_url(),PHP_URL_HOST);
	$licenseOk=$this->atec_pro_check_license($licenseCode,$siteName);

	if ($licenseOk) 
	{ 
		echo '<h4 class="atec-green">', esc_attr__('Thank you for activating your „Lifetime-Site-License“.','atec-cache-info'),'</h4>'; 
		atec_reg_inline_script('atec_group_pro_package','jQuery("#atec_group_pro_package").remove();');
	}

	echo '
		<div style="background: #f0f0f0; width: fit-content; padding: 0px 20px 20px 20px; border: solid 1px #dedede; margin: 10px auto;">
		<p><sub><img src="', esc_url(get_site_icon_url()) ,'" style="height:22px; margin-right: 5px;"></sub>', esc_attr($siteName), '</p>
		<form class="atec-box-white" name="atec_license" method="post" action="', esc_url($url), '&license=true&save=true&_wpnonce=', esc_attr($nonce), '">
			<div><label><b>', esc_attr__('Paste your license code here','atec-cache-info'), '</b></label></div>
			<div><textarea cols="40" rows="3" name="licenseCode">', esc_textarea($licenseCode), '</textarea></div>
			<div><br><input type="submit" name="submit" id="submit" class="button button-primary" value="', esc_attr__('Save','atec-cache-info'), '"></div>
		</form>
		</div>';
			  
		if ($licenseCode!=='')
		{ echo '<center>'; atec_badge(__('The license code is valid for your site','atec-cache-info'),__('The license code is not valid','atec-cache-info'),$licenseOk); echo '</center>'; }

}

function __construct() {
}}
?>