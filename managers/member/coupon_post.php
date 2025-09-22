<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_coupon_manager';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('type', 'field' , 'keyword', 'c_uid', 'status', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page	= "coupon_list.php?{$addstring}";


if($mode=='write' || $mode=='modify') {

	$_POST['name']			= checkPostVar('name');
	$_POST['use_type']		= checkPostVar('use_type');
	$_POST['use_s_date']	= checkPostVar('use_s_date');
	$_POST['use_e_date']	= checkPostVar('use_e_date');
	
	if($_POST['use_type'] == '0') {
		if(!$_POST['use_s_date'] || !$_POST['use_e_date']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}
	
		$_POST['use_e_date'] = $_POST['use_e_date']." 23:59:59";
	}
}

$item_array			= array('name', 'type', 'discount', 'discount_type', 'discount_limit', 'use_type', 'use_s_date', 'use_e_date', 'use_day', 'use_limit', 'use_limit2', 'goods_order');
$item_default		= array('use_type');	
$item_able_value	= array('type' => ['0', '1', '2', '3', '4'], 'discount_type' => ['P', 'W']);	

switch($mode) {
    case "write" :  

		if(!$_POST['name']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}
		
		$_POST['signdate']	= time();
				
		array_push($item_array, 'signdate');

		######################## 쿠폰 등록  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);
		######################## 쿠폰 등록  #########################

		alertMsg("쿠폰이 등록 되었습니다.", $link_page);

    break;	

	case "modify" :
		
		$uid = checkPostVar('uid');
		
		if(!$uid || !$_POST['name']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 쿠폰이 존재하지 않거나 삭제 되었습니다.");
		
		######################## 모음전 수정  #########################
		$sql = "UPDATE {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid = '{$uid}'";		
		$mysql->query($sql);
		######################## 모음전 수정  #########################

		alertMsg("쿠폰이 수정 되었습니다.", $link_page);			
		
	break;

	case "delete" :
		
		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql = "DELETE FROM {$table_name} WHERE uid = '{$uid}'";
			$mysql->query($sql);

			$sql = "DELETE FROM mallRN_coupon WHERE c_uid = '{$uid}'";
			$mysql->query($sql);			
		}
		alertMsg("{$i}건의 쿠폰이 삭제 되었습니다!", $link_page);		

	break;

	case "delete2" :
		
		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql = "DELETE FROM mallRN_coupon WHERE uid = '{$uid}'";
			$mysql->query($sql);			
		}
		alertMsg("{$i}건의 발급쿠폰이 삭제 되었습니다!", "coupon_down_list.php?{$addstring}");		

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
