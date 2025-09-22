<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","top_menu.html");
$tpl->scan_area("main");

######################## 분류 정보 #############################
$sql = "SELECT cate, cate_name FROM mallRN_cate WHERE cate_dep = 1 ORDER BY sequence ASC";
$mysql->query($sql);

while($row=$mysql->fetch_array()){    
	$URL	= "index.php?channel=list&cate=".specialStrReplace($row['cate']);
	$NAME	= specialStrReplace($row['cate_name']);
	$tpl->parse("loop_cate");
}
######################## 분류 정보 #############################

$sql = "SELECT mobile_top_menu FROM mallRN_configuration WHERE uid=1";
$top_menu_info = $mysql->get_one($sql);

if($top_menu_info) {
	$top_menu_info = explode("|*|", $top_menu_info);
	for($i = 0, $cnt = count($top_menu_info); $i < $cnt; $i ++) {
		$top_menu_info2 = explode("|", $top_menu_info[$i]);

		$menu_name	= addslashes($top_menu_info2[0]);
		$menu_url	= $top_menu_info2[1];
		$menu_used	= $top_menu_info2[2];
		if($menu_used != '1') $menu_used = "0";

		$tpl->parse("loop_menu");
	}
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>