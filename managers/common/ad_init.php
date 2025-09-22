<?php

@error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));
mysqli_report(MYSQLI_REPORT_OFF);  // 8.2 over

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

define('__MANAGERS__',		'1');
define('DEFAULT_PATH',		'');
define('PATH_LIB',			'../../lib');
define('PATH_INCLUDE',		'../../include');
define('PATH_PHPMAILER',	'../../plugin/PHPMailer');
define('PATH_COOLSMS',		'../../plugin/coolSMS');
define('PAGING_TYPE',		'1'); //0 : paging, 1 : pageline
define('MYSQL_DEBUG',	'Y');  // 디비에러를 출력한다. "Y"

include_once(PATH_LIB.'/lib.Function.php');   
include_once(PATH_LIB.'/lib.Shop.php');   
include_once(PATH_INCLUDE.'/config.php');   
include_once(PATH_INCLUDE.'/dbconfig.php');   
include_once(PATH_LIB.'/class.Mysql.php');   

$mysql = new mysqlClass(); 

include_once(PATH_LIB.'/checkLogin.php');

if(!$my_id && defined('__VENDOR_ABLE__'))	{
	if(isset($_COOKIE['v_my_id'])) include_once(PATH_LIB.'/checkVLogin.php');
}

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

if(!$my_id) {
	if(!defined('__BASIC__')) logMsg("로그아웃 되었습니다. 호그인 하시기 바랍니다.");
	if(!(isset($v_my_id) && defined('__VENDOR_ABLE__'))) movePage('../main/login.php');	
}
else if(!isset($_COOKIE['managers']) || $my_level < 99) movePage('../main/login.php');	

$sql			= "SELECT design_skin FROM mallRN_configuration WHERE uid=1";
$use_skin		= "skin/".$mysql->get_one($sql);
$SMain			= "../../index.php";
$ver			= SHOP_VERSION;
$t				= time();
$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";

define('ABSOLUTE_PATH_SHOP',	$HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT);

?>