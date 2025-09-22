<?php

@error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));

ob_start();

header("Content-Type: text/html; charset=utf-8");

if(substr($_SERVER['HTTP_HOST'], -1) == '.') {
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

if(!function_exists('userAbortFunc')){
	//메모리제거
	function userAbortFunc() {
		global $mysql, $listPaging, $tpl;
		if(is_object($mysql)) $mysql->close();
		if(is_object($tpl)) $tpl->close();
		if(is_object($listPaging)) $listPaging->close();		
	}
}

@ignore_user_abort(true); 
@register_shutdown_function('userAbortFunc');

define('__VENDOR__',		'1');
define('PATH_LIB',			'../../../lib');
define('PATH_INCLUDE',		'../../../include');

include_once(PATH_LIB.'/lib.Function.php');   
include_once(PATH_LIB.'/lib.Shop.php');   
include_once(PATH_INCLUDE.'/config.php');   
include_once(PATH_INCLUDE.'/dbconfig.php');   
include_once(PATH_LIB.'/class.Mysql.php');   

$mysql = new mysqlClass(); 

include_once(PATH_LIB.'/checkVLogin.php');

//==============================================================================
// SQL Injection 방어
//------------------------------------------------------------------------------
// magic_quotes_gpc 에 의한 backslashes 제거
if (7.4 > (float)phpversion()) {
	if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
		$_POST    = array_map_deep('stripslashes',  $_POST);
		$_GET     = array_map_deep('stripslashes',  $_GET);
		$_COOKIE  = array_map_deep('stripslashes',  $_COOKIE);
		$_REQUEST = array_map_deep('stripslashes',  $_REQUEST);
	}
}

// sql_escape_string 적용
$_POST    = array_map_deep('add_escape_string',  $_POST);
$_GET     = array_map_deep('add_escape_string',  $_GET);
$_COOKIE  = array_map_deep('add_escape_string',  $_COOKIE);
$_REQUEST = array_map_deep('add_escape_string',  $_REQUEST);
//==============================================================================

if(!$v_my_id) exit;	

include_once(PATH_LIB.'/class.Template.php');   

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$mysql->msgType(2);

$my_array = array();

// 템플릿
$tpl = new classTemplate;
$tpl->define("main", "index.html");
$tpl->scan_area("main");

?>