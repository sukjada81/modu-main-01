<?php

session_start(); 

define('DEFAULT_PATH',		'../');
define('PATH_LIB',			'lib');
define('PATH_INCLUDE',		'include');

include_once(DEFAULT_PATH.PATH_LIB.'/lib.Function.php');
include_once(DEFAULT_PATH.PATH_INCLUDE.'/config.php');   
include_once(DEFAULT_PATH.PATH_INCLUDE.'/dbconfig.php');   
include_once(DEFAULT_PATH.PATH_LIB.'/class.Mysql.php');  

$mysql = new mysqlClass(); 

$my_id		= base64_decode($_COOKIE['my_id']); 	
$sql		= "SELECT sns_type FROM mallRN_member WHERE id = '{$my_id}'";
$sns_type	= $mysql->get_one($sql);

################# 로그아웃 ################
SetCookie("my_id",	"",	-999,	"/"); 
SetCookie("sid",	"",	-999,	"/"); 
SetCookie("tempid",	"",	-999,	"/");
SetCookie("cartId", "",	-999,	"/");

if($sns_type) movePage("../plugin/social/{$sns_type}_login.php?logout=1"); 

header ("Location: ../");

?>