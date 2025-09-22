<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= "mallRN_member";

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$sql = "SELECT * FROM mallRN_configuration WHERE uid = 2";
$member_config = $mysql->one_row($sql);
	
$_POST['name']		= checkPostVar('name');	
$_POST['email']		= checkPostVar('email');
$_POST['signdate']	= time();

if(!mailCheck($_POST['email'])) logMsg("{$_POST['email']} 은 존재하지 않는 메일주소입니다.");

$item_array			= array('name', 'cell', 'email', 'birth', 'birth_sl', 'gender', 'sns_id', 'sns_type', 'reference', 'add1', 'add2', 'add3', 'add4', 'add5');
$item_able_value	= array('birth_sl' => ['N', 'S', 'L'], 'gender' => ['N', 'M', 'F'], 'mailling' => ['N', 'Y'], 'sms' => ['N', 'Y']);	

$_POST['sns_id']		= checkPostVar('sns_id');
		
if(!$_POST['name'] || !$_POST['sns_id'] || !$_POST['email']) {
	logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
}		

$_POST['mileage']		= 0;
$_POST['mailling_date'] = time();
$_POST['sms_date']		= time();
$_POST['login_time']	= time();
$_POST['level']			= 1;
$_POST['cnts']			= 1;

if($member_config['member_auth'] == 'A')	$_POST['auth'] = 'Y';
else										$_POST['auth'] = 'N';

if($is_mobile == 1)	$_POST['mobile'] = 'Y';
else				$_POST['mobile'] = 'N';

array_push($item_array, 'level', 'mileage', 'mailling', 'mailling_date', 'sms', 'sms_date', 'auth', 'mobile', 'cnts', 'login_time', 'signdate');
######################## 회원 등록  #########################		
$sql = "SELECT count(*) FROM {$table_name} WHERE id = ''";
if($mysql->get_one($sql) > 0) {
	$sql = "DELETE FROM {$table_name} WHERE id = ''";
	$mysql->query($sql);
}

$sql = "INSERT INTO {$table_name} SET";
foreach ($item_array as $k => $v) {
	if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
	else $_POST[$v] = checkPostVar($v);

	if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
	else $sql .= " {$v} = '{$_POST[$v]}',";
}
$mysql->query($sql);
$uid = $mysql->InsertNo();
######################## 회원 등록  #########################

######################## 아이디 생성  #########################
if($uid > 9999)	$rand = substr($uid, -4);
else			$rand = str_pad($uid, "4", "0", STR_PAD_LEFT);
$id		= date("ymdH", time()).$rand;

if($_POST['sns_type'] == 'naver')		$id = "na_".$id;
else if($_POST['sns_type'] == 'kakao')	$id = "ka_".$id;
else if($_POST['sns_type'] == 'google')	$id = "go_".$id;
else if($_POST['sns_type'] == 'payco')	$id = "pa_".$id;

$sql = "UPDATE {$table_name} SET id = '{$id}' WHERE sns_id = '{$_POST['sns_id']}'";
$mysql->query($sql);
######################## 아이디 생성  #########################

############ 적립급 처리 ############
if($member_config['member_mileage_yn'] == 'Y') {
	if($member_config['member_mileage_join'] > 0) { 
		$expired_date = "0000-00-00";
		if($member_config['member_milage_validity_yn'] == 'Y') {
			if($member_config['member_milage_validity'] > 0) {
				switch($member_config['member_milage_validity_type']) {
					case "D" : $date_type = "DAY";		break;
					case "M" : $date_type = "MONTH";	break;
					case "Y" : $date_type = "YEAR";		break;
				}
				$expired_date = date("Y-m-d", strtotime("+ {$member_config['member_milage_validity']}{$date_type}", time()));
				unset($date_type);
			}
		}
		
		$content	= "회원가입 축하 적립금";			
		$sql		= "INSERT INTO mallRN_mileage SET
							id				= '{$id}',
							content			= '{$content}',
							mileage			= '{$member_config['member_mileage_join']}',
							expired_date	= '{$expired_date}',
							signdate		= '{$signdate}'
					   ";
					   $mysql->query($sql);
	}
	
	$sql = "UPDATE {$table_name} SET mileage = '{$member_config['member_mileage_join']}' WHERE id = '{$id}'";
	$mysql->query($sql);
}
############ 적립급 처리 ############

############ 회원 가입 축하메일 보내기 ############		
$sql		= "SELECT content FROM mallRN_auto_mail WHERE type = 'join'";
$content	= stripslashes($mysql->get_one($sql));
$content	= str_replace("{ID}",			$id,											$content);
$content	= str_replace("{NAME}",			$_POST['name'],									$content);
$content	= str_replace("{SMSYN}",		$_POST['sms'] == 'Y' ? '동의함' : '동의안함',		$content);
$content	= str_replace("{SMSDATE}",		date("Y-m-d H:i:s"),							$content);
$content	= str_replace("{MAILYN}",		$_POST['mailling'] == 'Y' ? '동의함' : '동의안함',	$content);
$content	= str_replace("{MAILDATE}",		date("Y-m-d H:i:s"),							$content);
mallMailSend($_POST['email'], stripslashes($shop_config['basic_name'])." 회원가입을 진심으로 환영합니다.", $content);	
############ 회원 가입 축하메일 보내기 ############

############ 회원 가입 축하SMS 보내기 #############
if($_POST['cell']) {
	$replace_code_array	= array('NAME' => $_POST['name']);
	mallSmsAuto('regist', $_POST['cell'], $replace_code_array);
}
############ 회원 가입 축하SMS 보내기 #############

fcmSend("신규 회원가입 알림!", "{$_POST['name']}님이 새로운 회원이 되셨습니다.");

############ 로그인 처리 ########################	
if($_POST['auth'] == 'Y') {
	$sql = "UPDATE mallRN_cart SET cart_id = '".base64_encode($id)."' WHERE cart_id = '{$cart_id}'";
	SetCookie("cartId", base64_encode($id), 0, "/");

	makeLogin($id, 0, CONF_KEY); 
}
############ 로그인 처리 ########################		
		
echo "<script>parent.opener.location.href = '../../../{$Main}?channel=regist_ok'; parent.self.close();</script>";

?>