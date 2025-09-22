<?php

include_once('../common/ad_init.php');

include_once(PATH_LIB.'/class.Template.php');   

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$mysql->msgType(2);

$my_array	= array();

// 템플릿
$tpl = new classTemplate;
$tpl->define("main", "search_result.html");
$tpl->scan_area("main");

$keyword	= checkPostVar('keyword');
if(!$keyword) json_error_msg('검색어를 입력 하시기 바랍니다.');

include_once('../common/menu_define.php');

$keyword	= str_replace(" ", "", $keyword);

$ck_cnt		= 0;
for($i=0, $cnt = count($MENU_ARR); $i < $cnt ; $i ++) {
	$MENU_NAME	= $MENU_ARR[$i][0][0];
	
	$cnt2		= count($MENU_ARR[$i]);

	for($j = 1; $j < $cnt2; $j ++) {
		$MENU_SUB_NAME	= str_replace("/", "", $MENU_ARR[$i][$j][0]);
		$MENU_SUB_LINK	= "../".$MENU_ARR[$i][$j][1];
		if(isset($MENU_ARR[$i][$j][4])) {
			$MENU_SUB_TAG = $MENU_ARR[$i][$j][4];
			$MENU_SUB_TAG2	= "#".str_replace(", ", " #", $MENU_SUB_TAG);
		}
		else {
			$MENU_SUB_TAG = "";
			$MENU_SUB_TAG2	= "";
		}

		if(preg_match("/{$keyword}/i", $MENU_SUB_NAME) || preg_match("/{$MENU_SUB_NAME}/i", $keyword)) {
			$tpl->parse("loop_search_menu");
			$ck_cnt ++;
		}
		else {
			if($MENU_SUB_TAG) {
				$keyword2	= explode(",", str_replace(" ", "", $MENU_SUB_TAG));
				foreach($keyword2 as $k => $v) {
					if(preg_match("/{$keyword}/i", $v) || preg_match("/{$v}/i", $keyword)) {
						$tpl->parse("loop_search_menu");
						$ck_cnt ++;
						break;
					}
				}
			}
		}
		
	}	
}

if($ck_cnt == 0) $tpl->parse("empty_search_menu");

$tpl->parse("is_list_area");

$my_array[] = ["listHtml" => $tpl->tprint("is_list_area", 1)];
echo json_encode($my_array);
exit;

?>