<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_mileage';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'date_type', 's_date', 'e_date', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page	= "mileage_list.php?{$addstring}";

if($mode=='write') {
	$type					= checkPostVar('type');
	$_POST['id']			= add_escape_re_string(checkPostVar('id'));	
	$_POST['content']		= checkPostVar('content');	
	$_POST['expired_date']	= "1000-01-01";
	$_POST['expired_use']	= 0;
	$_POST['proc_id']		= $my_id;
	$_POST['proc_acc_ip']	= $_SERVER['REMOTE_ADDR'];
	
	if($type == 1)	{
		$_POST['mileage']		= checkPostVar('mileage');
		$_POST['use_mileage']	= 0;
		$validity_yn			= checkPostVar('mileage_validity_yn');		

		if($validity_yn == 'Y') {
			$mileage_validity			= checkPostVar('mileage_validity');
			$mileage_validity_type		= checkPostVar('mileage_validity_type');
			$mileage_validity_type_arr	= array("D" => "DAY", "M" => "MONTH", "Y" => "YEAR");
			
			$_POST['expired_date']	= date("Y-m-d", strtotime("+{$mileage_validity} {$mileage_validity_type_arr[$mileage_validity_type]}", time()));
			$_POST['expired_use']	= 1;
		}
	}
	else {		
		$_POST['use_mileage']	= checkPostVar('mileage');
		$_POST['mileage']		= 0;		
	}

	$item_array = array('id', 'content', 'mileage', 'use_mileage', 'expired_use', 'expired_date', 'proc_id', 'proc_acc_ip', 'signdate');
}

switch($mode) {
    case "write" :  

		if(!$type || !$_POST['id'] || !$_POST['content']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}
		
		$_POST['signdate']	= time();
				
		######################## 마일리지 등록  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);

		mileageChange($_POST['id']);
		######################## 마일리지 등록  #########################

		alertMsg("마일리지가 등록 되었습니다.", $link_page);		

    break;	

	case "delete" :
		
		$item		= checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$item_array2 = array('id', 'content', 'mileage', 'use_mileage', 'proc_mileage', 'expired_use', 'expired', 'expired_date', 'order_num', 'goods_uid', 'proc_id', 'proc_acc_ip', 'log_signdate', 'log_proc_id', 'log_proc_acc_ip', 'log_uid', 'signdate');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql	= "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
			$data	= $mysql->one_row($sql);

			$id		= $data['id'];

			if($id) {
				$sql	= "DELETE FROM {$table_name} WHERE uid = '{$uid}'";
				$mysql->query($sql);

				mileageChange($id);

				$data['log_signdate']		= $data['signdate'];
				$data['log_proc_id']		= $my_id;
				$data['log_proc_acc_ip']	= $_SERVER['REMOTE_ADDR'];
				$data['log_uid']			= $data['uid'];

				######################## 마일리지 삭제로그 등록  #########################		
				$sql = "INSERT INTO {$table_name}_log SET";
				foreach ($item_array2 as $k => $v) {					
					if($k == count($item_array2) - 1) $sql .= " {$v} = '{$data[$v]}'";
					else $sql .= " {$v} = '{$data[$v]}',";
				}
				$mysql->query($sql);				
				######################## 마일리지 삭제로그 등록  #########################
								
			}			
		}
		alertMsg("{$i}건의 마일리지가 삭제 되었습니다!", $link_page);		

	break;

	case "repair" :
		
		$uid	= checkPostVar('uid');

		if(!$uid) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
	
		$sql	= "SELECT * FROM mallRN_mileage_log WHERE uid = '{$uid}'";
		$data	= $mysql->one_row($sql);

		if($data['status'] == 1) logMsg('이미 마일리지 복구가 되었습니다..');
		
		$item_array2 = array('uid', 'id', 'content', 'mileage', 'use_mileage', 'proc_mileage', 'expired_use', 'expired', 'expired_date', 'order_num', 'goods_uid', 'proc_id', 'proc_acc_ip', 'signdate');
		
		$data['uid']		= $data['log_uid'];
		$data['signdate']	= $data['log_signdate'];

		######################## 마일리지 복구  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array2 as $k => $v) {					
			if($k == count($item_array2) - 1) $sql .= " {$v} = '{$data[$v]}'";
			else $sql .= " {$v} = '{$data[$v]}',";
		}
		$mysql->query($sql);				

		mileageChange($data['id']);
		######################## 마일리지 삭제로그 등록  #########################

		$sql	= "UPDATE mallRN_mileage_log SET status = 1 WHERE uid = '{$uid}'";
		$mysql->query($sql);

		echo "<script>parent.repairSuccess('{$uid}');</script>";
		logMsg("마일리지가 복구 되었습니다.","success");

	break;


	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
