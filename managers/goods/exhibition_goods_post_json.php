<?php

include_once('../common/ad_init.php');

$mysql->msgType(2);

$my_array		= array();
$mode			= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$uid			= checkPostVar('uid');
$exhibition		= checkPostVar('exhibition');
$cate			= checkPostVar('cate');
$goods_order	= checkPostVar('goods_order');
$where			= 0;

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

if(!$exhibition)  json_error_msg($uid.'필수 정보가 제대로 넘어오지 못했습니다.');

$where = "";
$sql = "SELECT cate_info FROM mallRN_exhibition WHERE uid = '{$exhibition}'";
if($cate_info = $mysql->get_one($sql)) {
	$cate_info = explode("|*|", $cate_info);
	$cate_max_num = $cate_info[0];
	if($cate_max_num > 100) {
		if(!$cate) json_error_msg('필수 정보가 제대로 넘어오지 못했습니다.');
		$where = " && ecate = '{$cate}'";
	}
}

switch($mode) {
    case "insert" :  	
		if(!$uid)  json_error_msg('필수 정보가 제대로 넘어오지 못했습니다.');

		$sql = "SELECT MAX(sequence) FROM mallRN_exhibition_goods WHERE euid = '{$exhibition}' && sequence < 99999 {$where}";
		if($sequence = $mysql->get_one($sql)) $sequence += 1;
		else $sequence = 1;

		$sql = "INSERT INTO mallRN_exhibition_goods SET
					euid		= '{$exhibition}',
					guid		= '{$uid}',
					ecate		= '{$cate}',
					sequence	= '{$sequence}'
				";
		$mysql->query($sql);

		$sql = "SELECT exhibition FROM mallRN_goods WHERE uid = '{$uid}'";
		$goods_exhibition = $mysql->get_one($sql);
		
		if(!$goods_exhibition) $goods_exhibition = ",{$exhibition},";
		else $goods_exhibition .= "{$exhibition},";
		
		$sql = "UPDATE mallRN_goods SET exhibition = '{$goods_exhibition}' WHERE uid = '{$uid}'";
		$mysql->query($sql);

		$my_array[] = ["label"=>"Success"];
    break;	

	case "delete" :  
		if(!$uid)  json_error_msg('필수 정보가 제대로 넘어오지 못했습니다.');

		exhibitionGoodsDel($uid, $exhibition, $where);
		
		$my_array[] = ["label"=>"Success"];
    break;	

	case "order" :  
		$goods_order = explode(",", $goods_order);
		for($i = 0, $cnt = count($goods_order); $i < $cnt; $i ++) {
			$uid = trim($goods_order[$i]);
			if($uid) {
				$i2 = $i + 1;
				$sql = "UPDATE mallRN_exhibition_goods SET sequence = '{$i2}' WHERE euid = '{$exhibition}' && guid = '{$uid}' {$where}";
				$mysql->query($sql);
			}
		}

		$my_array[] = ["label"=>"Success"];
    break;

	default: json_error_msg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

echo json_encode($my_array);

?>
