<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_member';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('field' ,'keyword', 'date_type', 's_date', 'e_date', 'field2', 'keyword2', 'field3', 'keyword3', 'field4', 'keyword4', 's_range1', 'e_range1', 'range1', 's_range2', 'e_range2', 'range2', 's_range3', 'e_range3', 'range3', 'level', 'mailling', 'sms', 'auth', 'gender', 'marry', 'address1', 'mobile', 'sns_type', 'sort', 'limit', 'page');

foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode(add_escape_re_string($_GET[$v])) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page	= "member_list.php?{$addstring}";

switch($mode) {

	case "level" :		
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		$cnt		= count($item);
		if(!$item || !$value)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		if($my_level < $value) logMsg($value.'등급변경 권한이 없습니다.');

		$sql		= "SELECT uid FROM mallRN_member WHERE id = '{$my_id}'";
		$my_uid		= $mysql->get_one($sql);

		$sql = "UPDATE mallRN_member SET level = '{$value}' WHERE uid IN (".join(",",$item).") && uid != '{$my_uid}'";
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());

		####################### 관리자 로그 ##########################	
		adminLog($my_id, "회원등급변경 - {$value}", 1);
		####################### 관리자 로그 ##########################

		alertMsg("{$cnt}명의 회원 등급이 변경 되었습니다!", $link_page);

	break;

	case "auth" :		
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		$cnt		= count($item);
		if(!$item || !$value)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$sql = "UPDATE mallRN_member SET auth = '{$value}' WHERE uid IN (".join(",",$item).")";		
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());

		####################### 관리자 로그 ##########################	
		adminLog($my_id, "회원승인변경 - {$value}", 3);
		####################### 관리자 로그 ##########################

		alertMsg("{$cnt}명의 회원 승인상태가 변경 되었습니다!", $link_page);

	break;
    
	case "modify" :
		
		$uid	= checkPostVar('uid');
		$name	= checkPostVar('name');
		
		if(!$uid || !$name) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql				= "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		if(!$data = $mysql->one_row($sql)) logMsg("해당 회원은 존재하지 않거나 삭제 되었습니다.");
		
		$auth				= checkPostVar('auth', 'N', ['N', 'Y']);
		$level				= checkPostVar('level');
		$passwd				= checkPostVar('passwd');

		$sql				= "SELECT count(*) FROM mallRN_member_level WHERE level = '{$level}'";
		if($mysql->one_row($sql) == 0) logMsg("회원등급(LV{$level})은 사용하지 않는 등급입니다.");

		$sql				= "UPDATE {$table_name} SET level = '{$level}' WHERE uid = '{$uid}'";		
		if($level == 100) $sql .= " && sns_type = ''";		
		$mysql->query($sql);

		$sql				= "UPDATE {$table_name} SET name = '{$name}', auth = '{$auth}'";
		if($passwd) $sql	.= ", passwd = '".md5($passwd)."'";
		$sql				.= " WHERE uid = '{$uid}'";		
		$mysql->query($sql);

		####################### 관리자 로그 ##########################	
		adminLog($my_id, "회원정보변경 - {$data['id']}", 3);
		####################### 관리자 로그 ##########################

		alertMsg("회원정보가 수정 되었습니다.", $link_page);			
		
	break;

	case "delete" : case "sleep_delete" :
		
		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		if($mode == 'sleep_delete') {
			$table_name2	= "_sleep";
			$msg			= "휴면";
			$link_page		= "member_sleep_list.php?{$addstring}";
		}
		else {
			$table_name2	= "";
			$msg			= "";
		}

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql	= "SELECT level, id FROM {$table_name}{$table_name2} WHERE uid = '{$uid}'";
			$data	= $mysql->one_row($sql);
			if($data['level'] == 100) continue;

			$sql = "DELETE FROM {$table_name}{$table_name2} WHERE uid = '{$uid}'";
			$mysql->query($sql);
			
			$sql = "UPDATE mallRN_order_info SET id = '' WHERE id = '{$data['id']}'";
			$mysql->query($sql);

			$sql = "UPDATE mallRN_order_sales SET id = '' WHERE id = '{$data['id']}'";
			$mysql->query($sql);

			$sql = "UPDATE mallRN_order_cash_receipts SET id = '' WHERE id = '{$data['id']}'";
			$mysql->query($sql);

			$sql = "UPDATE mallRN_order_status_change SET id = '' WHERE id = '{$data['id']}'";
			$mysql->query($sql);

			$sql = "DELETE FROM mallRN_review WHERE id = '{$data['id']}'";
			$mysql->query($sql);

			$sql = "DELETE FROM mallRN_inquiry WHERE id = '{$data['id']}'";
			$mysql->query($sql);

			$sql = "DELETE FROM mallRN_coupon WHERE id = '{$data['id']}'";
			$mysql->query($sql);

			$sql = "DELETE FROM mallRN_mileage WHERE id = '{$data['id']}'";
			$mysql->query($sql);

			$sql = "DELETE FROM mallRN_keyword_recent WHERE id = '{$data['id']}'";
			$mysql->query($sql);		

			$sql = "DELETE FROM mallRN_favorite_goods WHERE id = '{$data['id']}'";
			$mysql->query($sql);
			
			$sql = "DELETE FROM mallRN_favorite_store WHERE id = '{$data['id']}'";
			$mysql->query($sql);
			
			$sql = "SELECT id FROM mallRN_board_manager";
			$mysql->query($sql);

			$rand = md5(mt_rand(1000,9999));
		
			while($row = $mysql->fetch_array()){
				$sql = "UPDATE mallRN_board_{$row['id']} SET id = '', passwd = '{$rand}' WHERE id = '{$data['id']}'";
				$mysql->query2($sql);

				$sql = "UPDATE mallRN_board_{$row['id']} SET o_id = '' WHERE o_id='{$data['id']}'";
				$mysql->query2($sql);

				$sql = "UPDATE mallRN_board_{$row['id']}_comment SET id = '', passwd = '{$rand}' WHERE id = '{$data['id']}'";
				$mysql->query2($sql);
			}

			####################### 관리자 로그 ##########################	
			adminLog($my_id, "{$msg}회원삭제 - {$data['id']}", 3);
			####################### 관리자 로그 ##########################
		}
		alertMsg("{$i}명의 {$msg}회원이 삭제 되었습니다!", $link_page);		

	break;

	case "withdrawal_delete" :		
		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql = "DELETE FROM mallRN_member_withdrawal WHERE uid = '{$uid}'";
			$mysql->query($sql);
	
		}	
		alertMsg("{$i}건의 회원탈퇴 내역이 삭제 되었습니다!", "member_withdrawal_list.php?{$addstring}");

	break;

	case "sms_send" :
		
		$item		= checkPostVar('item');
		$message	= checkPostVar('message');
		if(!isset($_POST['proc_type1']) || !$message) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
			
		$proc_type2	= checkPostVar('proc_type2');
		if($proc_type2 == '2') {
			$day	= checkPostVar('day');
			$day	= str_replace("-", "", $day);
			$hour	= checkPostVar('hour');
			$minute = checkPostVar('minute');

			$date = "{$day}{$hour}{$minute}00";

			if($date <= date("YmdHi00")) logMsg($date.'예약시간은 현재시간 이후로 하셔야 됩니다.');			
		}
		
		$where = memberTypeWhere($_POST['proc_type1'], $item);

		$sort	= checkGetVar('sort');
		if(!$sort) $sort = "uid ASC";

		$sql				= "SELECT sms_calling_number FROM mallRN_configuration WHERE uid=1";
		$sms_calling_number	= $mysql->get_one($sql);
		
		$calling_number	= str_replace("-", "", $sms_calling_number);		
		
		$sql = "SELECT a.* FROM mallRN_member a WHERE a.uid > 0 {$where} ORDER BY a.{$sort}";
		$mysql->query($sql);


		while($row = $mysql->fetch_array()){
			if($row['sms'] == 'N') continue;
			
			$cell			= str_replace("-", "", $row['cell']);
			
			if($proc_type2 == '2') $send_messages = array("to" => $cell, "from" => $calling_number, "text" => $message, "datetime" => $date);
			else $send_messages = array("to" => $cell, "from" => $calling_number, "text" => $message);
			
			mallSmsSend($send_messages);
		}
		$cnt =  $mysql->affected_rows();

		$cnt = number_format($cnt);
		iframeViewMsg("{$cnt}명의 회원에게 문자가 발송 되었습니다!");
		
	break;

	case "down_coupon" :
		
		$item		= checkPostVar('item');
		$c_uid		= checkPostVar('c_uid');
		if(!isset($_POST['proc_type1']) || !$c_uid) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
			
		$where = memberTypeWhere($_POST['proc_type1'], $item);

		$sort	= checkGetVar('sort');
		if(!$sort) $sort = "uid ASC";

		$sql = "SELECT a.id FROM mallRN_member a WHERE a.uid > 0 {$where} ORDER BY a.{$sort}";
		$mysql->query($sql);

		$cnt = 0;
		while($row = $mysql->fetch_array()){
			if(couponIssuance($c_uid, $row['id'])) {
				$cnt ++;
			}
		}

		$cnt = number_format($cnt);
		iframeViewMsg("{$cnt}명의 회원에게 쿠폰이 발급 되었습니다!");
		
	break;

	case "memo" :
		
		$id = checkPostVar('id');
		$memo = checkPostVar('memo');

		if(!$id) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
	
		$sql = "UPDATE {$table_name} SET memo = '{$memo}' WHERE id = '{$id}'";
		$mysql->query($sql);

		####################### 관리자 로그 ##########################	
		adminLog($my_id, "회원메모변경 - {$id}", 3);
		####################### 관리자 로그 ##########################

		logMsg("메모가 저장 되었습니다.","success");

	break;

	case "sleep_restore" :		
		$item		= checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
		$signdate	= time();

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql	= "SELECT id FROM mallRN_member_sleep WHERE uid = '{$uid}'";
			$data	= $mysql->one_row($sql);

			$sql	= "INSERT INTO mallRN_member (SELECT * FROM mallRN_member_sleep WHERE uid = '{$uid}')";
			$mysql->query($sql);

			$sql	= "UPDATE mallRN_member SET sleep_time = '{$signdate}' WHERE uid = '{$uid}'";
			$mysql->query($sql);

			$sql	= "DELETE FROM mallRN_member_sleep WHERE uid = '{$uid}'";
			$mysql->query($sql);	

			####################### 관리자 로그 ##########################	
			adminLog($my_id, "휴면회원해제 - {$data['id']}", 3);
			####################### 관리자 로그 ##########################
		}	
		alertMsg("{$i}명의 휴면회원이 해제 되었습니다!", "member_sleep_list.php?{$addstring}");

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
