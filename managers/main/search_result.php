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

######################## 회원등급 #############################
$sql = "SELECT * FROM mallRN_member_level ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	
	$name						= specialStrReplace($row['name']);
	$level_array[$row['level']] = $name;	
}
######################## 회원등급 #############################

$sql = "SELECT * FROM mallRN_member WHERE uid > 0 && (INSTR(name, '{$keyword}') || INSTR(id, '{$keyword}'))";
$mysql->query($sql);

$cnt	= 0;

while($row = $mysql->fetch_array()){
	$ID		= stripslashes($row['id']);
	$NAME	= stripslashes($row['name']);
	$LEVEL	= $level_array[$row['level']];
	
	$MTAG	= "";
	if($row['cell']) {
		$cell	= $row['cell'];
		if(strlen($cell) == 12)			$cell	= substr($cell, 0, 4)."-".substr($cell, 4, 4)."-".substr($cell, 8, 4);
		else if(strlen($cell) == 11)	$cell	= substr($cell, 0, 3)."-".substr($cell, 3, 4)."-".substr($cell, 7, 4);
		else if(strlen($cell) == 10)	$cell	= substr($cell, 0, 2)."-".substr($cell, 2, 4)."-".substr($cell, 6, 4);
		
		$MTAG .= "#{$cell} ";
	}

	if($row['address1']) {
		$address	= explode(" ", $row['address1']);
		$MTAG		.= "#{$address[0]} ";
	}	

	if($row['gender'] != 'N') {
		if($row['gender'] == 'M') $MTAG .= "#남성 ";
		else					  $MTAG .= "#여성 ";	
	}
	
	$MTAG .= "#최종로그인시간(".date("Y-m-d H시", $row['login_time']).")";

	$tpl->parse("loop_search_member");
	$cnt ++;
}

if($cnt == 0) $tpl->parse("empty_search_member");

$tpl->parse("is_list_area");

$my_array[] = ["listHtml" => $tpl->tprint("is_list_area", 1)];
echo json_encode($my_array);
exit;

?>