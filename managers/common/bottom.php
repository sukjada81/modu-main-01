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
$item_field	= join(", ", $item_array );

$sql = "SELECT {$item_field} FROM mallRN_configuration WHERE uid = 1";
$data = $mysql->one_row($sql);

$ck			= 0;
foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
	if($v != 'mobile_icon' && !$data[$v]) $ck = 1;
}

if($ck == 0) {
	$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
	if($mobile_icon) {		
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