<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","member_level_info.html");
$tpl->scan_area("main");

$sql = "SELECT * FROM mallRN_member_level ORDER BY level ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()) {

	$uid		= $row['uid'];
	$level		= $row['level'];
	$name		= stripslashes($row['name']);
	$discount	= stripslashes($row['discount']);
	$mileage	= stripslashes($row['mileage']);

	$sql = "SELECT count(*) FROM mallRN_member WHERE level='{$level}'";
	$member = number_format($mysql->get_one($sql));

	if($row['delivery_free']==1) $checked = "checked='checked'";
	else $checked = "";

	if($level>1 && $level<100) $tpl->parse("is_del_icon");
	
	$tpl->parse("loop_level");
}


$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>