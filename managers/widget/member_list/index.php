<?php 

include_once("../widget_top.php");

######################## 회원등급 정보 #############################
$sql = "SELECT * FROM mallRN_member_level WHERE uid > 0 ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	$level_array[$row['level']] = stripslashes($row['name']);
}
######################## 회원등급 정보 #############################

$sql = "SELECT * FROM mallRN_member WHERE uid > 0 ORDER BY uid DESC LIMIT 4";
$mysql->query($sql);

$i = 0;
while($row = $mysql->fetch_array()) {
	$UID		= $row['uid'];	
	$ID			= stripslasheS($row['id']);
	$NAME		= stripslasheS($row['name']);
	$LEVEL		= $level_array[$row['level']];
	$SIGNDATE	= date("Y-m-d", $row['signdate']);

	$tpl->parse("loop_list");
	$i ++;
}

if($i == 0) $tpl->parse("empty_list");

include_once("../widget_bottom.php");

?>