<?php

include_once('../common/ad_init.php');

################# 로그아웃 ################
SetCookie("my_id", "", -999, "/"); 
SetCookie("sid", "", -999, "/"); 
SetCookie("managers", "", -999, "/"); 
SetCookie("tempid", "", -999, "/"); 
SetCookie("cartId", "",	-999,	"/");

####################### 관리자 로그 ##########################	
$signdate	= time();
$sql		= "INSERT INTO mallRN_admin_log SET id = '{$my_id}', content = '{$my_name} 로그아웃', type = 1, acc_ip = '{$_SERVER['REMOTE_ADDR']}', signdate = '{$signdate}'";
$mysql->query($sql);
####################### 관리자 로그 ##########################

header ("Location: ../");

?>