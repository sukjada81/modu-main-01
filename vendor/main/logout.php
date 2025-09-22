<?php

include_once('../common/ad_init.php');

################# 로그아웃 ################
SetCookie("v_my_id",	"", -999,	"/"); 
SetCookie("v_sid",		"",	-999,	"/"); 

####################### 관리자 로그 ##########################	
$signdate	= time();
$sql		= "INSERT INTO mallRN_vendor_log SET id = '{$v_my_id}', content = '{$v_my_name} 로그아웃', type = 1, acc_ip = '{$_SERVER['REMOTE_ADDR']}', signdate = '{$signdate}'";
$mysql->query($sql);
####################### 관리자 로그 ##########################

header ("Location: ../");

?>