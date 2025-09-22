<?php 

include_once("../widget_top.php");

$sql = "SELECT uid, content, signdate FROM mallRN_review ORDER BY uid DESC LIMIT 4";
$mysql->query($sql);	

$i	= 0;
while($row = $mysql->fetch_array()){
	$UID		= $row['uid'];
	$SUBJECT	= specialStrReplace2(html2txt($row['content']));
	
	if($row['signdate'] > strtotime(date("Y-m-d")))	$DATE = date("H:i:s", $row['signdate']);
	else											$DATE = date("Y-m-d", $row['signdate']);
	
	$tpl->parse("loop_list");
	$i ++;
}	

if($i == 0) $tpl->parse("empty_list");

include_once("../widget_bottom.php");

?>