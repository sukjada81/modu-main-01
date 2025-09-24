<?php

header('Pragma: no-cache'); // 페이지 항상 새로 불러오기
include_once('../common/ad_init.php');
include_once(PATH_LIB.'/class.Template.php');   

$t = time();

// 템플릿
$tpl = new classTemplate;
$tpl->define('main','../common/popup_top.html');
$tpl->scan_area('main');

$sql			= "SELECT basic_name FROM mallRN_configuration WHERE uid = 1";
$SITE_NAME		= $mysql->get_one($sql);
if(!$SITE_NAME) $SITE_NAME = "더도매";

$SERVER_NAME	= $_SERVER["SERVER_NAME"];

$tpl->parse('main');
$tpl->tprint('main');
$tpl->close();

?>