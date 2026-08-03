<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= "mallRN_member";

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

if($mode=='new' || $mode=='modify') {

	$sql = "SELECT * FROM mallRN_configuration WHERE uid = 2";
	$member_config = $mysql->one_row($sql);
	
	$_POST['name']		= specialStrReplace3(checkPostVar('name'));
	if(!preg_match("/^[가-힣a-zA-Z ]+$/", $_POST['name'])) logMsg("정상적으로 등록하세요!");
	$_POST['passwd']	= checkPostVar('passwd');
	$_POST['email']		= add_escape_re_string(checkPostVar('email'));
	if(checkPostVar('hobby')) {
		$_POST['hobby']		= join("|", checkPostVar('hobby'));
	}
	else $_POST['hobby'] = '';
	$_POST['signdate']	= time();
	// 모두복지는 이메일 입력을 받지 않기 때문에 강제로 이메일을 공통으로 사용
	$_POST['email'] = 'sukjada81@naver.com';
	if(!mailCheck($_POST['email'])) logMsg("{$_POST['email']} 은 존재하지 않는 메일주소입니다.");

	$item_array			= array('name', 'tel', 'cell', 'postcode', 'address1', 'address2', 'email', 'gender', 'marry', 'hobby', 'job', 'comp', 'comp_owner', 'comp_num', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'add1', 'add2', 'add3', 'add4', 'add5', 'reference');
	$item_able_value	= array('birth_sl' => ['N', 'S', 'L'], 'gender' => ['N', 'M', 'F'], 'marry' => ['N', 'M', 'S'], 'mailling' => ['N', 'Y'], 'sms' => ['N', 'Y']);	
}

switch($mode) {

	case "passwd" :

		if(!$my_id) logMsg("먼저 로그인을 하시기 바랍니다.");
		
		$orig_passwd	= checkPostVar('orig_passwd');
		$passwd			= md5(checkPostVar('passwd'));

		if(!$orig_passwd || !$passwd) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}	
		
		$sql			= "SELECT passwd FROM mallRN_member WHERE id='{$my_id}'";
		$db_passwd		= $mysql->get_one($sql);
		
		if(md5($orig_passwd) != $db_passwd) logMsg("비밀번호가 일치 하지 않습니다. 다시 입력 하시기 바랍니다.");

		$sql = "UPDATE mallRN_member SET passwd = '{$passwd}' WHERE id = '{$my_id}'";
		$mysql->query($sql);

		echo "<script>parent.infoReset();</script>";

		logMsg("비밀번호가 변경 되었습니다.", "success");

	break;

    case "new" :  		
		
		$_POST['id']		= add_escape_re_string(checkPostVar('id'));
		if(!preg_match("/^[a-zA-Z0-9]+$/", $_POST['id'])) logMsg("정상적으로 등록하세요!");
		
		if(!$_POST['name'] || !$_POST['id'] || !$_POST['passwd'] || !$_POST['email']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}		

		if($member_config['member_unavailable_id']) {
			$unavailable_id = explode(",", $member_config['member_unavailable_id']);
			foreach($unavailable_id as $k => $v) {
				if($_POST['id'] == $v) {
					logMsg("{$_POST['id']}는 사용하실 수 없는 아이디 입니다.");
				}
			}	
		}

		$sql = "SELECT count(*) FROM {$table_name} WHERE id='{$_POST['id']}'";
		if($mysql->get_one($sql)>0) {
			logMsg("{$_POST['id']}는 사용하실 수 없는 아이디 입니다.");
		}		

		$sql = "SELECT count(*) FROM {$table_name}_sleep WHERE id='{$_POST['id']}'";
		if($mysql->get_one($sql)>0) {
			logMsg("{$_POST['id']}는 사용하실 수 없는 아이디 입니다.");
		}	

		############ 적립급 처리 ############
		if($member_config['member_mileage_yn'] == 'Y') {
			$_POST['mileage'] = $member_config['member_mileage_join'];
			if($member_config['member_mileage_join'] > 0) { 
				$expired_date = "0000-00-00";
				if($member_config['member_mileage_validity_yn'] == 'Y') {
					if($member_config['member_mileage_validity'] > 0) {
						switch($member_config['member_mileage_validity_type']) {
							case "D" : $date_type = "DAY";		break;
							case "M" : $date_type = "MONTH";	break;
							case "Y" : $date_type = "YEAR";		break;
						}
						$expired_date = date("Y-m-d", strtotime("+ {$member_config['member_mileage_validity']}{$date_type}", time()));
						unset($date_type);
					}
				}
				
				$content	= "회원가입 축하 적립금";			
				$sql		= "INSERT INTO mallRN_mileage SET
									id				= '{$_POST['id']}',
									content			= '{$content}',
									mileage			= '{$member_config['member_mileage_join']}',
									expired_date	= '{$expired_date}',
									signdate		= '{$signdate}'
							   ";
				$mysql->query($sql);
			}			
		}
		else $_POST['mileage'] = 0;
		############ 적립급 처리 ############
		
		$_POST['mailling_date'] = time();
		$_POST['sms_date']		= time();
		$_POST['login_time']	= time();
		$_POST['passwd']		= md5($_POST['passwd']);
		$_POST['level']			= 1;
		$_POST['cnts']			= 1;

		if($member_config['member_auth'] == 'A')	$_POST['auth'] = 'Y';
		else										$_POST['auth'] = 'N';

		if($is_mobile == 1)	$_POST['mobile'] = 'Y';
		else				$_POST['mobile'] = 'N';

		array_push($item_array, 'id', 'passwd', 'level', 'birth', 'birth_sl', 'mileage', 'mailling', 'mailling_date', 'sms', 'sms_date', 'auth', 'mobile', 'cnts', 'login_time', 'signdate');
		######################## 회원 등록  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else $_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);
		######################## 회원 등록  #########################

		############ 회원 가입 축하쿠폰 발급 ############
		$sql = "SELECT * FROM mallRN_coupon_manager WHERE type = 1";
		$mysql->query($sql);

		while($row = $mysql->fetch_array()) {
			couponIssuance($row['uid'], $_POST['id']);
		}				
		############ 회원 가입 축하쿠폰 발급 ############

		############ 회원 가입 축하메일 보내기 ############		
		$sql			= "SELECT content, send FROM mallRN_auto_mail WHERE type = 'join'";
		$data			= $mysql->one_row($sql);
	
		if($data['send'] == 1) {
			$content	= stripslashes($data['content']);
			$content	= str_replace("{ID}",			$_POST['id'],									$content);
			$content	= str_replace("{NAME}",			$_POST['name'],									$content);
			$content	= str_replace("{SMSYN}",		$_POST['sms'] == 'Y' ? '동의함' : '동의안함',		$content);
			$content	= str_replace("{SMSDATE}",		date("Y-m-d H:i:s"),							$content);
			$content	= str_replace("{MAILYN}",		$_POST['mailling'] == 'Y' ? '동의함' : '동의안함',	$content);
			$content	= str_replace("{MAILDATE}",		date("Y-m-d H:i:s"),							$content);
			$content	= str_replace("{SHOPNAME}",		stripslashes($shop_config['basic_name']),		$content);
			mallMailSend($_POST['email'], "[".stripslashes($shop_config['basic_name'])."] 회원가입을 진심으로 환영합니다.", $content);	
		}
		############ 회원 가입 축하메일 보내기 ############

		############ 회원 가입 SMS 보내기 #############
		if($_POST['cell']) {
			$replace_code_array	= array('NAME' => $_POST['name']);
			mallSmsAuto('regist', $_POST['cell'], $replace_code_array);
		}
		############ 회원 가입 SMS 보내기 #############

		fcmSend("신규 회원가입 알림!", "{$_POST['name']}님이 새로운 회원이 되셨습니다.");

		############ 로그인 처리 ########################	
		if($_POST['auth'] == 'Y') {
			$sql = "UPDATE mallRN_cart SET cart_id = '".base64_encode($_POST['id'])."' WHERE cart_id = '{$cart_id}'";
			$mysql->query($sql);

			SetCookie("cartId", base64_encode($_POST['id']), 0, "/");

			makeLogin($_POST['id'], 0, CONF_KEY); 
		}
		############ 로그인 처리 ########################	
		
		parentMovePage("../{$Main}?channel=regist_ok");

    break;	

	case "modify" :
		
		if(!$my_sns_type) {
			$passwd			= md5(checkPostVar('passwd'));		
			$sql			= "SELECT passwd FROM mallRN_member WHERE id='{$my_id}'";
			$db_passwd		= $mysql->get_one($sql);		
			if($passwd != $db_passwd) logMsg("비밀번호가 일치 하지 않습니다. 다시 입력 하시기 바랍니다.");
		}

		$_POST['mailling_date'] = time();
		$_POST['sms_date']		= time();

		array_push($item_array, 'mailling', 'mailling_date', 'sms', 'sms_date');
		######################## 회원 수정  #########################		
		$sql = "UPDATE {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else $_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= "WHERE id = '{$my_id}'";
		$mysql->query($sql);
		######################## 회원 수정  #########################

		logMsg("회원정보가 변경 되었습니다.", "success");

	break;

	case "withdrawal" :	

		if(!$my_id) logMsg("먼저 로그인을 하시기 바랍니다.");

		$_POST['signdate']	= time();

		$sql			= "SELECT level, passwd, mobile FROM mallRN_member WHERE id = '{$my_id}'";
		$data			= $mysql->one_row($sql);	
		
		if(!$my_sns_type) {
			$passwd			= md5(checkPostVar('passwd'));								
			$db_passwd		= $data['passwd'];		
			if($passwd != $db_passwd) logMsg("비밀번호가 일치 하지 않습니다. 다시 입력 하시기 바랍니다.");
		}

		if($data['level'] == 100) logMsg("관리자는 회원탈퇴가 되지 않습니다.");

		$item_array			= array('id', 'name', 'reason', 'order_cnt', 'message', 'mobile', 'signdate');
		
		$_POST['id']		= $my_id;
		$_POST['name']		= $my_name;
		$_POST['order_cnt']	= 0;
		$_POST['mobile']	= $data['mobile'];

		######################## 회원 탈퇴 등록  #########################		
		$sql = "INSERT INTO mallRN_member_withdrawal SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);
		######################## 회원 탈퇴 등록  #########################		
	
		$sql = "DELETE FROM mallRN_member WHERE id = '{$my_id}'";
		$mysql->query($sql);

		$sql = "UPDATE mallRN_order_info SET id = '' WHERE id = '{$my_id}'";
		$mysql->query($sql);

		$sql = "UPDATE mallRN_order_sales SET id = '' WHERE id = '{$my_id}'";
		$mysql->query($sql);

		$sql = "UPDATE mallRN_order_cash_receipts SET id = '' WHERE id = '{$my_id}'";
		$mysql->query($sql);

		$sql = "UPDATE mallRN_order_status_change SET id = '' WHERE id = '{$my_id}'";
		$mysql->query($sql);

		$sql = "DELETE FROM mallRN_coupon WHERE id = '{$my_id}'";
		$mysql->query($sql);
		
		$sql = "DELETE FROM mallRN_mileage WHERE id = '{$my_id}'";
		$mysql->query($sql);

		$sql = "DELETE FROM mallRN_keyword_recent WHERE id = '{$my_id}'";
		$mysql->query($sql);

		$sql = "DELETE FROM mallRN_review WHERE id = '{$my_id}'";
		$mysql->query($sql);

		$sql = "DELETE FROM mallRN_inquiry WHERE id = '{$my_id}'";
		$mysql->query($sql);
		
		$sql = "DELETE FROM mallRN_favorite_goods WHERE id = '{$my_id}'";
		$mysql->query($sql);

		$sql = "DELETE FROM mallRN_favorite_store WHERE id = '{$my_id}'";
		$mysql->query($sql);

		$sql = "SELECT id FROM mallRN_board_manager";
		$mysql->query($sql);

		$rand = md5(mt_rand(1000,9999));
		
		while($row = $mysql->fetch_array()){
			$sql = "UPDATE mallRN_board_{$row['id']} SET id = '', passwd = '{$rand}' WHERE id = '{$my_id}'";
			$mysql->query2($sql);

			$sql = "UPDATE mallRN_board_{$row['id']} SET o_id = '' WHERE o_id='{$my_id}'";
			$mysql->query2($sql);

			$sql = "UPDATE mallRN_board_{$row['id']}_comment SET id = '', passwd = '{$rand}' WHERE id = '{$my_id}'";
			$mysql->query2($sql);
		}

		SetCookie("my_id", "", -999, "/"); 
		SetCookie("sid", "", -999, "/"); 
		SetCookie("tempid", "", -999, "/");

		parentMovePage("../{$Main}?channel=member_withdrawal_ok");		

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
