<?php

include_once('../common/ad_init.php');

$mysql->msgType(2);

$my_array		= array();
$mode			= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$uid			= checkPostVar('uid');
$type			= checkPostVar('type');
$type2			= checkPostVar('type2');
$cate			= checkPostVar('cate');
$goods_order	= checkPostVar('goods_order');
$where			= '';
$moddate		= time();

if($type != 1 && $type != 2 && $type != 3) $type = '';
if($type2 != 1 && $type2 != 2 && $type2 != 3) $type2 = '';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

switch($mode) {
    case "main_insert" :  		
		if(!$uid || !$type || !$type2) json_error_msg('필수 정보가 제대로 넘어오지 못했습니다.');
		if($type == 2 && !$cate) json_error_msg('필수 정보가 제대로 넘어오지 못했습니다.');
		if($cate) $where = " && SUBSTRING(cate, 1,3) = '".substr($cate, 0, 3)."'";

		$sql = "SELECT MAX(main{$type}_display{$type2}_sequence) FROM mallRN_goods WHERE main{$type}_display{$type2} = '1' && main{$type}_display{$type2}_sequence < 99999 {$where}";
		if($sequence = $mysql->get_one($sql)) $sequence += 1;
		else $sequence = 1;

		$sql = "UPDATE mallRN_goods SET main{$type}_display{$type2} = '1', main{$type}_display{$type2}_sequence = '{$sequence}', moddate='{$moddate}' WHERE uid = '{$uid}'";
		$mysql->query($sql);

		$my_array[] = ["label"=>"Success"];
    break;	

	case "main_delete" :  
		if(!$uid || !$type || !$type2)  json_error_msg('필수 정보가 제대로 넘어오지 못했습니다.');
		
		$sql = "UPDATE mallRN_goods SET main{$type}_display{$type2} = '0', main{$type}_display{$type2}_sequence = '99999', moddate='{$moddate}' WHERE uid = '{$uid}'";
		$mysql->query($sql);

		$my_array[] = ["label"=>"Success"];
    break;	

	case "main_order" :  
		if(!$goods_order || !$type)  json_error_msg('필수 정보가 제대로 넘어오지 못했습니다.');
		if($type == 2 && !$cate) json_error_msg('필수 정보가 제대로 넘어오지 못했습니다.');
		if($cate) $where = " && SUBSTRING(cate, 1,3) = '".substr($cate, 0, 3)."'";

		$goods_order = explode(",", $goods_order);
		for($i = 0, $cnt = count($goods_order); $i < $cnt; $i ++) {
			$uid = trim($goods_order[$i]);
			if($uid) {
				$i2 = $i + 1;
				$sql = "UPDATE mallRN_goods SET main{$type}_display{$type2}_sequence = '{$i2}' WHERE uid = '{$uid}' &&  main{$type}_display{$type2} = '1' {$where}";
				$mysql->query($sql);
			}
		}

		$my_array[] = ["label"=>"Success"];
    break;

	case "cate_order" :  
		if(!$goods_order || !$type || !$cate)  json_error_msg('필수 정보가 제대로 넘어오지 못했습니다.');
		
		$cate_dep = strlen(str_replace("000", "", $cate)) / 3;

		for($i=3; $i<10; $i=$i+3) {
			if(substr($cate, $i, ($i+3))=='000') break;						
		}					
			
		$where = " && SUBSTRING(b.cate, 1, {$i}) = '".substr($cate, 0, $i)."' && a.order_priority = 0";

		$goods_order = explode(",", $goods_order);
		for($i = 0, $cnt = count($goods_order); $i < $cnt; $i ++) {
			$uid = trim($goods_order[$i]);
			if($uid) {
				$i2 = $i + 1;
				$sql = "UPDATE mallRN_goods a, mallRN_goods_cate b SET b.sequence{$cate_dep} = '{$i2}' WHERE a.uid = b.guid && guid = '{$uid}' {$where}";
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
