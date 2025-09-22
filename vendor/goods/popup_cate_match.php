<?php 

include_once("../common/popup_top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_cate_match.html");
$tpl->scan_area("main");

$sql = "SELECT * FROM mallRN_cate WHERE cate_sub = '0' ORDER BY cate ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()){
	$CATE			= $row['cate'];	
	$sql			= "SELECT matching FROM mallRN_cate_matching WHERE vendor = '{$v_my_id}' && cate = '{$row['cate']}'";
	$MATCH			= $mysql->get_one($sql);
	$CATE_LOCATION	= getCateAllName($CATE);

	$tpl->parse("loop_cate");
}

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>