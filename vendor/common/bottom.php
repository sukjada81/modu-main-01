<?php

// 템플릿
$tpl = new classTemplate;
$tpl->define('main','../common/bottom.html');
$tpl->scan_area('main');

if("{$URI_NAME_ARR[2]}/{$URI_NAME_ARR[3]}" == "main/index.php") {
	@$tpl->parse("is_main");
}
else {
	$tpl->parse("is_sub");
}

$item_array = array('push_apiKey', 'push_authDomain', 'push_projectId', 'push_storageBucket', 'push_messagingSenderId', 'push_appId', 'push_server_key', 'push_server_key2', 'mobile_icon');

$ck			= 0;
foreach($item_array as $k => $v) {
	${$v} = stripslashes($shop_data[$v]);	
	if($v != 'mobile_icon' && !$shop_data[$v]) $ck = 1;
}

if($ck == 0) {
	if($mobile_icon) {
		$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
		if(CONF_ROOT)	$mobile_icon = $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."/image/mobile/{$mobile_icon}";
		else			$mobile_icon = $HeaderProtocol.$_SERVER['HTTP_HOST']."/image/mobile/{$mobile_icon}";
	}
	
	if($HeaderProtocol == 'https://') {
		if(checkGetVar('login') == 1) $tpl->parse("is_login_proc");
	
		$tpl->parse("is_firebase");
	}
}

$tpl->parse('main');
$tpl->tprint('main');
$tpl->close();

?>