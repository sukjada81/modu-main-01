<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_sales_calculate';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('field', 'keyword', 'date_type', 's_date', 'e_date', 'vendor', 'tax_bill', 'status', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page			= "calculate_list.php?{$addstring}";

if($mode=='write' || $mode=='modify') {
	
	$_POST['vendor']	= checkPostVar('vendor');

	if(!$_POST['vendor']) {
		logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
	}

	$_POST['sum']		= checkPostVar('sum');
	$_POST['price1']	= checkPostVar('price1');
	$_POST['price2']	= checkPostVar('price2');
	$_POST['price3']	= checkPostVar('price3');
	$_POST['status']	= checkPostVar('status');

	if($_POST['sum'] == 0 && $_POST['price1'] == 0 && $_POST['price2'] == 0 && $_POST['price3'] == 0) {
		logMsg("정산금액을 확인 해 보시기 바랍니다.");
	}
}

$item_array			= array('vendor', 'vendor_name', 's_date', 'e_date', 'sum', 'price1', 'price2', 'price3', 'bank_name', 'bank_num', 'bank_owner', 'tax_bill', 'status');
$item_default		= array('sum', 'price1', 'price2', 'price3');	
$item_able_value	= array('tax_bill' => ['0', '1'], 'status' => ['0', '1']);	

switch($mode) {
    case "write" :  
		
		$_POST['type']		= 1;
		$_POST['signdate']	= time();
		array_push($item_array, 'type', 'signdate');

		if($_POST['status'] == 1) {
			$_POST['status_date'] = time();
			array_push($item_array, 'status_date');	
		}

		######################## 정산내역 등록  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);		
		######################## 정산내역 등록  #########################
		
		alertMsg("정산내역이 등록 되었습니다.", $link_page);		

    break;	

	case "modify" :
		
		$uid = checkPostVar('uid');
		
		if(!$uid) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 정산내역이 존재하지 않거나 삭제 되었습니다.");
		
		if($_POST['status'] == 1 && $row['status'] == 0) {
			$_POST['status_date'] = time();
			array_push($item_array, 'status_date');	
		}
		else if($_POST['status'] == 0 && $row['status'] == 1) {
			$_POST['status_date'] = 0;
			array_push($item_array, 'status_date');	
		}
		
		######################## 정산내역 수정  #########################
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
		######################## 정산내역 수정  #########################

		alertMsg("정산내역이 수정 되었습니다.", $link_page);			
		
	break;

	case "delete" :
		
		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql	= "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
			$data	= $mysql->one_row($sql);

			$sql	= "DELETE FROM {$table_name} WHERE uid = '{$uid}'";
			$mysql->query($sql);

			if($data['type'] == 0) {
				$sql = "UPDATE mallRN_order_sales SET adjustment = 0 WHERE from_unixtime(confirm_date) BETWEEN '{$data['s_date']}' AND '{$data['e_date']} 23:59:59' && vendor = '{$data['vendor']}' && type < 2";
				$mysql->query($sql);
			}
		}
		alertMsg("{$i}건의 정산내역이 삭제 되었습니다!", $link_page);		

	break;

	case "tax_bill" :		
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		if($value == 2) $value = 0;
		else			$value = 1;		
		if(!$item || strlen($value) == 0)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
		
		$sql = "UPDATE {$table_name} SET tax_bill = '{$value}' WHERE uid IN (".join(",",$item).")";		
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());

		alertMsg("{$cnt}개의 정산내역의 세금계산서가 변경 되었습니다!", $link_page);

	break;

	case "status" :		
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		
		if($value == 2) $value = 0;
		else			$value = 1;		
		if(!$item || strlen($value) == 0)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		if($value == 0) {
			$sql = "UPDATE {$table_name} SET status = '{$value}', status_date = '0' WHERE uid IN (".join(",",$item).")";		
		}
		else {
			$sql = "UPDATE {$table_name} SET status = '{$value}', status_date = '".time()."' WHERE uid IN (".join(",",$item).") && status = 0";
		}
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());

		alertMsg("{$cnt}개의 정산내역의 상태가 변경 되었습니다!", $link_page);

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
