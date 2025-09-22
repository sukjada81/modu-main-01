<?php

include_once('../common/ad_init.php');

include_once(PATH_LIB.'/class.Template.php');   

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$mysql->msgType(2);

$my_array	= array();

// 템플릿
$tpl = new classTemplate;
$tpl->define("main", "site_map.html");
$tpl->scan_area("main");

include_once('../common/menu_define.php');

for($i=0, $cnt = count($MENU_ARR); $i < $cnt ; $i ++) {
	$NAME_MENU1	= $MENU_ARR[$i][0][0];
	$LINK_MENU1	= "../".$MENU_ARR[$i][0][1];
	
	$cnt2		= count($MENU_ARR[$i]);

	for($j = 1; $j < $cnt2; $j ++) {
		$NAME_MENU2		= $MENU_ARR[$i][$j][0];
		$LINK_MENU2		= "../".$MENU_ARR[$i][$j][1];
		$SUB_MENU2		= @$MENU_ARR[$i][$j][3];

		if($SUB_MENU2) {
			foreach($SUB_MENU_ARR[$SUB_MENU2] as $k => $v) {
				$NAME_MENU3		= $v[0];
				$LINK_MENU3		= "../".$v[1];
				$tpl->parse("loop_sitemap3");
			}
			$tpl->parse("is_sitemap3");
		}
		$tpl->parse("loop_sitemap2");
	}	

	$tpl->parse("loop_sitemap");
}

$tpl->parse("is_list_area");

$my_array[] = ["listHtml" => $tpl->tprint("is_list_area", 1)];
echo json_encode($my_array);
exit;

?>