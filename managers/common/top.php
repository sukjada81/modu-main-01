
<?php

header('Pragma: no-cache'); // 페이지 항상 새로 불러오기

define('__BASIC__',		'1');
include_once('../common/ad_init.php');
include_once(PATH_LIB.'/class.Template.php');   

// 템플릿
$tpl = new classTemplate;
$tpl->define('main','../common/top.html');
$tpl->scan_area('main');

$SERVER_NAME = $_SERVER["SERVER_NAME"];
$tmps = explode("/", $_SERVER['REQUEST_URI']);
if($tmps[1] != 'managers') {
	array_splice($tmps, 1, 1);
	$_SERVER['REQUEST_URI'] = join("/", $tmps);
}

$URI_NAME_ARR = (explode("/", $_SERVER['REQUEST_URI']));
$URI_NAME_ARR_DETAIL	= explode("?",$URI_NAME_ARR[3]);
$URI_NAME_ARR[3]		= $URI_NAME_ARR_DETAIL[0];
if(isset($URI_NAME_ARR_DETAIL[1])) {
	$URI_NAME_ARR_DETAIL2	= explode("&",$URI_NAME_ARR_DETAIL[1]);
	if($URI_NAME_ARR_DETAIL2[0]) $URI_NAME_ARR_DETAIL2[0] = "?".$URI_NAME_ARR_DETAIL2[0];
	$URI_NAME_ARR[4]		= $URI_NAME_ARR_DETAIL[0].$URI_NAME_ARR_DETAIL2[0];
}
else $URI_NAME_ARR[4] = $URI_NAME_ARR[3];
unset($URI_NAME_ARR_DETAIL, $URI_NAME_ARR_DETAIL2);

include_once('../common/menu_define.php');

$sql		= "SELECT * FROM mallRN_admin_configuration WHERE id = '{$my_id}'";
if(!$admin_info	= $mysql->one_row($sql)) {
	$sql		= "INSERT INTO mallRN_admin_configuration SET id = '{$my_id}'";
	$mysql->query($sql);

	$sql		= "SELECT * FROM mallRN_admin_configuration WHERE id = '{$my_id}'";
	$admin_info	= $mysql->one_row($sql);	
}

$push_checked	= "";
if($admin_info['push_yn'] == 'Y') $push_checked = "checked='checked'";

if(preg_match("/info.php/i",$URI_NAME_ARR[3])) {
	$mode					= isset($_GET['mode']) ? $_GET['mode'] : '';
	$info_list_change_arr	= array('exhibition', 'banner', 'board', 'popup', 'inquiry', 'coupon', 'mileage', 'order' ,'review' ,'calculate', 'cash_receipts', 'add_page', 'db_table');
	$ck						= 0; 
	foreach ($info_list_change_arr as $k => $v) {
		if(preg_match("/{$v}/i",$URI_NAME_ARR[3])) {
			$ck = 1;
			break;
		}
	}	
	if($mode=='modify' || $ck == 1) {
		$URI_NAME_ARR[3] = str_replace("info.php","list.php",$URI_NAME_ARR[3]);
		$URI_NAME_ARR[4] = str_replace("info.php","list.php",$URI_NAME_ARR[4]);
	}	
	unset($ck, $info_list_change_arr, $k, $v);
}

$PARENT_SEC_NUM = "";
for($i=0, $cnt=count($SUB_MENU_ARR); $i<$cnt ; $i++) {
	if($SUB_MENU_ARR[$i]) {
		for($j=0, $cnt2=count($SUB_MENU_ARR[$i]); $j<$cnt2 ; $j++) {
			if(($SUB_MENU_ARR[$i][$j][1] == "{$URI_NAME_ARR[2]}/{$URI_NAME_ARR[3]}") || ($SUB_MENU_ARR[$i][$j][1] == "{$URI_NAME_ARR[2]}/{$URI_NAME_ARR[4]}")) {
				$PARENT_SEC_NUM = $i;
				break;
			}
		}
	}
	if($PARENT_SEC_NUM) break;
}

$max_sub_cnt = 0;
$MENU_NUM = $MENU_SUB_NUM = '';

for($i=0, $cnt = count($MENU_ARR); $i < $cnt ; $i ++) {
	$MENU_NAME = $MENU_ARR[$i][0][0];
	$MENU_ICON = $MENU_ARR[$i][0][2];
	$MENU_LINK = "../".$MENU_ARR[$i][0][1];
	
	$cnt2= count($MENU_ARR[$i]);
	if($max_sub_cnt < $cnt2) $max_sub_cnt = $cnt2;

	for($j=1; $j<$cnt2; $j++) {
		$MENU_SUB_NAME = $MENU_ARR[$i][$j][0];
		$MENU_SUB_LINK = "../".$MENU_ARR[$i][$j][1];
		if(($MENU_ARR[$i][$j][1] == "{$URI_NAME_ARR[2]}/{$URI_NAME_ARR[3]}") || ($MENU_ARR[$i][$j][1] == "{$URI_NAME_ARR[2]}/{$URI_NAME_ARR[4]}") || ($PARENT_SEC_NUM != '' &&  $PARENT_SEC_NUM == @$MENU_ARR[$i][$j][3])) {			
			$MENU_NUM = $i;
			$MENU_SUB_NUM = $j;
			$SEC = "half_highlight";
		}		
		else $SEC = "";

		if(@$MENU_ARR[$i][0][3] != 1) $tpl->parse("loop_sub_menu");
	}

	if($MENU_NUM && $MENU_NUM == $i) $MENU_SEC = "selected";
	else $MENU_SEC = "";

	if(@$MENU_ARR[$i][0][3] != 1) $tpl->parse("loop_menu");
	$tpl->parse("loop_menu2");

	if(@$MENU_ARR[$i][0][3] != 1) $tpl->parse("loop_sec_sub1");
	else if(@$MENU_ARR[$MENU_NUM][0][3] == 1) $tpl->parse("loop_sec_sub1");
}
	
$MAIN_COLOR1 = $MAIN_COLOR2 = "";
if("{$URI_NAME_ARR[2]}/{$URI_NAME_ARR[3]}" == "main/index.php") {

	$server_time = date("m/d/Y H:i:s");

	$lunarFile = file(PATH_LIB.'/lunar.txt');
	foreach ($lunarFile as $lunar_num => $lunar) {
		if(strstr($lunar, "'".date("Y-n-j")."',")) {
			$lunars = str_replace("'".date("Y-n-j")."',", "", $lunar);
			$lunars = str_replace("'","",$lunars);
			$LUNARS = date("Y-m-d", strtotime($lunars));			
			break;
		}    
	}
	unset($lunarFile, $lunar_num, $lunar, $lunars);

	$MAIN_COLOR1	= "style='background:#fff'";
	$MAIN_COLOR2	= "style='background:#efefef'";	
	$widget_info	= $admin_info['widget_info'];

	if(!$widget_info) {
		$widget_info = "goods,order,member,count,sales,margin,order_step,notice,board,order_list,tips,member_list,inquiry,board_counsel,review";
		$sql		= "UPDATE mallRN_admin_configuration SET widget_info = '{$widget_info}' WHERE id = '{$my_id}'";
		$mysql->query($sql);
	}
	$widget_array	= explode(",", str_replace(" ", "", $widget_info));

	$handle = opendir("../widget");
	while ($widget = readdir($handle)) {
		if(!preg_match("/\./i",$widget)) {		
			include("../widget/{$widget}/define.php");
			$widget_checked = "";
			if(in_array($widget, $widget_array)) $widget_checked = "checked='checked'";
			$tpl->parse("loop_widget");
		}
	}
	closedir($handle);

	$tpl->parse("is_main");	
	$tpl->parse("is_main2");
	$tpl->parse("is_main3");
}
else {

	$MENU_TITLE = $MENU_ARR[$MENU_NUM][0][0];	
	$MENU_SUB_TITLE = $MENU_ARR[$MENU_NUM][$MENU_SUB_NUM][0];
	$MENU_SUB_DESC = $MENU_ARR[$MENU_NUM][$MENU_SUB_NUM][2];

	for($j=1, $cnt=count($MENU_ARR[$MENU_NUM]); $j<$cnt; $j++) {
		$MENU_SUB_NAME = $MENU_ARR[$MENU_NUM][$j][0];
		$MENU_SUB_LINK = "../".$MENU_ARR[$MENU_NUM][$j][1];

		if($j==$MENU_SUB_NUM) {
			$MENU_SUB_SEC	= "selected";
			$SEC			= "half_highlight";

			if(@$MENU_ARR[$MENU_NUM][$j][3]) {
				$SUB_MENU_SEC = $MENU_ARR[$MENU_NUM][$j][3];			

				if($SUB_MENU_SEC == 14 || $SUB_MENU_SEC == 15) {
					
					if($SUB_MENU_SEC == 14) $where = "&& vendor_delivery = ''";
					else					$where = "";	

					$sql	= "SELECT SUM(IF(status = '0' , 1 , 0)) as cnt0, SUM(IF(status = '1' , 1 , 0)) as cnt1, SUM(IF(status = '2' , 1 , 0)) as cnt2, SUM(IF(status = '3' , 1 , 0)) as cnt3, SUM(IF(status = '4' , 1 , 0)) as cnt4, SUM(IF(status = '5' , 1 , 0)) as cnt5, SUM(IF(status = '7' , 1 , 0)) as cnt6, SUM(IF(status = '8' , 1 , 0)) as cnt7, SUM(IF(status = '9' , 1 , 0)) as cnt8 FROM mallRN_order_goods WHERE reals = 1 {$where}";
					$ocnt	= $mysql->one_row($sql);
					for($p = 0; $p < 9; $p ++) {
						${"OCNT".$p} = number_format($ocnt['cnt'.$p]);
					}					
				}

				for($j2 = 0, $cnt2 = count($SUB_MENU_ARR[$SUB_MENU_SEC]); $j2 < $cnt2; $j2 ++) {

					$MENU_SUB_SUB_NAME		= $SUB_MENU_ARR[$SUB_MENU_SEC][$j2][0];
					$MENU_SUB_SUB_LINK		= "../".$SUB_MENU_ARR[$SUB_MENU_SEC][$j2][1];
					$MENU_SUB_SUB_NAME_CNT	= "";
					if($SUB_MENU_SEC == 14 || $SUB_MENU_SEC == 15) {
						$MENU_SUB_SUB_NAME_CNT = "&nbsp;<span class='number'>(".${"OCNT".$j2}.")</span>";
					}
					
					if(($SUB_MENU_ARR[$SUB_MENU_SEC][$j2][1] == "{$URI_NAME_ARR[2]}/{$URI_NAME_ARR[3]}") || ($SUB_MENU_ARR[$SUB_MENU_SEC][$j2][1] == "{$URI_NAME_ARR[2]}/{$URI_NAME_ARR[4]}")) {
						$SUB_SEC = "half_highlight";
						$MENU_SUB_SUB_SEC = "selected";
						$MENU_SUB_SUB_SEC_ICON = "rotate180";
					}
					else $SUB_SEC = $MENU_SUB_SUB_SEC = "";

					$tpl->parse("loop_sub_sub_menu");	
					if($PARENT_SEC_NUM) {					
						$tpl->parse("loop_sec_sub3");
						$tpl->parse("loop_content_sub");
					}
				}
				$tpl->parse("is_sub_sub_menu");				
				if($PARENT_SEC_NUM) {
					$tpl->parse("is_sec_sub3");	
					$tpl->parse("is_content_sub");
				}
			}
		}
		else $MENU_SUB_SEC = $SEC = "";	

		$MENU_SUB_SUB_SEC_ICON = $MENU_SUB_SUB_SEC = "";
		
		$tpl->parse("loop_sub_menu2");	
		$tpl->parse("loop_sec_sub2");
	}

	$tpl->parse("is_sub");
	$tpl->parse("is_sub2");
	$tpl->parse("is_sub3");	
	$tpl->parse("is_sub4");	
}

$sql	= "SELECT basic_name, mobile_icon FROM mallRN_configuration WHERE uid = 1";
$data	= $mysql->one_row($sql);
if($SITE_NAME = $data['basic_name']) { 
	$tpl->parse("is_logo_name");
}
else $tpl->parse("is_logo_default");

if($data['mobile_icon'])  {
	$mobile_icon = $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."image/mobile/".stripslashes($data['mobile_icon']);
}
else $mobile_icon = "";

$sql			= "SELECT member_login_limit_minute FROM mallRN_configuration WHERE uid = 2";
$LOGIN_LIMIT	= stripslashes($mysql->get_one($sql));

$tpl->parse('main');
$tpl->tprint('main');
$tpl->close();

?>