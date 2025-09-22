<?php

header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');
include_once(DEFAULT_PATH.PATH_LIB.'/class.Template.php'); 

$skin = "../skin/{$shop_config['design_skin']}";

$skin_ing = isset($_GET['skin_ing']) ? $_GET['skin_ing'] : ((isset($_COOKIE['skin_ing'])) ? $_COOKIE['skin_ing'] : '');
if($skin_ing == 'Y') {
	$skin = "../skin/{$shop_config['design_skin_ing']}";
	SetCookie("skin_ing", $skin_ing, 0, "/");
}

if(!file_exists("{$skin}/info/skin_define.php")) Error("스킨파일이 존재하지 않습니다.");
include_once("{$skin}/info/skin_define.php");

################################# Header ###################################
$tpl = new classTemplate;
$tpl->define("main","../popup_header.html");
$tpl->scan_area("main");

if($shop_config['mobile_icon'])  {
	$mobile_icon = "image/mobile/".$shop_config['mobile_icon'];
	$tpl->parse("is_mobile_icon");
}
if($mobile_header == "mobile_") $tpl->parse("is_mobile_header");

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();
################################# Header ###################################

?>