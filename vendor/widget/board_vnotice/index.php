<?php 

include_once("../widget_top.php");

$sql = "SELECT uid, subject, signdate FROM mallRN_board_vnotice WHERE dels = 0 ORDER BY notice ASC, idx ASC, main ASC, sub ASC LIMIT 4";
$mysql->query($sql);	

$i	= 0;
while($row = $mysql->fetch_array()){
	$UID		= $row['uid'];
	$SUBJECT	= specialStrReplace2($row['subject']);	
	
	if($row['signdate'] > strtotime(date("Y-m-d")))	$DATE = date("H:i:s", $row['signdate']);
	else											$DATE = date("Y-m-d", $row['signdate']);
	
	$tpl->parse("loop_list");
	$i ++;
}	

if($i == 0) $tpl->parse("empty_list");

include_once("../widget_bottom.php");

?>