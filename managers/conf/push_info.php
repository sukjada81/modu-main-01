<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","push_info.html");
$tpl->scan_area("main");

$item_array = array('push_apiKey', 'push_authDomain', 'push_projectId', 'push_storageBucket', 'push_messagingSenderId', 'push_appId', 'push_server_key', 'push_server_key2');
$item_field	= join(", ", $item_array );

$sql = "SELECT {$item_field} FROM mallRN_configuration WHERE uid = 1";
$data = $mysql->one_row($sql);

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);	
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>