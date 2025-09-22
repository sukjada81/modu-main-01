<?php 

include_once("../common/top.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","goods_adds.html");
$tpl->scan_area("main");

$sql = "SELECT goods_require_info FROM mallRN_configuration WHERE uid=1";
$multi_data = $mysql->one_row($sql);

######################## 등록된 상품필수정보 #########################
$require_data = $multi_data['goods_require_info'];

if($require_data) {
	$require_data2 = explode("|*|",$require_data);
	for($i=0, $k=1, $cnt=count($require_data2); $i<$cnt; $i++) {
		$require_data3	= explode("|",$require_data2[$i]);
		if($require_data3[1]==0) continue;

		$require_title = specialStrReplace($require_data3[0]);		

		if($i==0) $jum = "";
		else $jum = ", ";

		$tpl->parse("loop_require");
		$k++;
	}
	unset($require_data, $require_data2, $require_data3, $require_title);
}
######################## 등록된 상품필수정보 #########################

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>