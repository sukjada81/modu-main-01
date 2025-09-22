<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');
define('VENDOR_FOLDER', '../image/vendor');

include_once('init.php');

$mysql->msgType(1);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$item_array			= array('auth', 'sell', 'delivery_type', 'comp_name', 'comp_owner', 'comp_license_no', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'comp_email', 'comp_tel', 'comp_fax', 'cont_name', 'cont_cell', 'cont_email', 'cont_part', 'cont_position', 'goods_auth', 'commission', 'bank_name', 'bank_num', 'bank_owner', 'account_cycle');

$_POST['id']				= add_escape_re_string(checkPostVar('id'));
if(!preg_match("/^[a-zA-Z0-9]+$/", $_POST['id'])) logMsg("정상적으로 등록하세요!");
$_POST['comp_name']			= specialStrReplace3(checkPostVar('comp_name'));	
$_POST['comp_license_no']	= checkPostVar('comp_license_no');	
$_POST['comp_email']		= add_escape_re_string(checkPostVar('comp_email'));
$_POST['passwd']			= checkPostVar('passwd');
$_POST['cont_name']			= specialStrReplace3(checkPostVar('cont_name'));	
$_POST['cont_cell']			= checkPostVar('cont_cell');	

if(!$_POST['id'] || !$_POST['passwd'] || !$_POST['comp_name'] || !$_POST['comp_license_no'] || !$_POST['comp_email'] || !$_POST['cont_name'] || !$_POST['cont_cell']) {
	logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
}		

$sql = "SELECT * FROM mallRN_configuration WHERE uid = 2";
$member_config = $mysql->one_row($sql);

if($member_config['member_unavailable_id']) {
	$unavailable_id = explode(",", $member_config['member_unavailable_id']);
	foreach($unavailable_id as $k => $v) {
		if($_POST['id'] == $v) {
			logMsg("{$_POST['id']}는 사용하실 수 없는 아이디 입니다.");
		}
	}	
}

$sql = "SELECT count(*) FROM mallRN_vendor WHERE id='{$_POST['id']}'";
if($mysql->get_one($sql)>0) {
	logMsg("{$_POST['id']}는 사용하실 수 없는 아이디 입니다.");
}		

$_POST['passwd']		= md5($_POST['passwd']);
$_POST['auth']			= 'R';
$_POST['sell']			= 'R';
$_POST['goods_auth']	= 'A';
$_POST['commission']	= '0';
$_POST['delivery_type'] = '0';
$_POST['signdate']		= time();

for($i=1;$i<3;$i++) {
	if(!preg_match("/none/i",$_FILES['attach_file'.$i]['tmp_name']) && $_FILES['attach_file'.$i]['tmp_name']) {									
		$_POST['image'.$i] = upFile($_FILES['attach_file'.$i]['tmp_name'], $_FILES['attach_file'.$i]['name'], VENDOR_FOLDER, 1, $_POST['id']."_attach_file{$i}", 1);
	}
	else $_POST['image'.$i] = '';		
}

array_push($item_array,'id', 'passwd', 'image1', 'image2', 'signdate');

$sql = "INSERT INTO mallRN_vendor SET";
foreach ($item_array as $k => $v) {
	$_POST[$v] = checkPostVar($v, '');
	if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
	else $sql .= " {$v} = '{$_POST[$v]}',";
}
$mysql->query($sql);

$sql	= "SELECT goods_delivery_info, goods_refund_info, goods_exchange_info, goods_as_info FROM mallRN_configuration WHERE uid = 1";
$data	= $mysql->one_row($sql);

$sql = "INSERT INTO mallRN_vendor_configuration SET 
			vendor					= '{$_POST['id']}',
			goods_delivery_info		= '{$data['goods_delivery_info']}',
			goods_refund_info		= '{$data['goods_refund_info']}',
			goods_exchange_info		= '{$data['goods_exchange_info']}',
			goods_as_info			= '{$data['goods_as_info']}',
			delivery_p_price1		= '30000',
			delivery_p_price2		= '3000'
		";

$mysql->query($sql);

############ 판매사 가입 축하메일 보내기 ############		
$sql			= "SELECT content, send FROM mallRN_auto_mail WHERE type = 'vjoin'";
$data			= $mysql->one_row($sql);

if($data['send'] == 1) {
	$content	= stripslashes($data['content']);
	$content	= str_replace("{ID}",			$_POST['id'],									$content);
	$content	= str_replace("{NAME}",			$_POST['comp_name'],							$content);
	mallMailSend($_POST['comp_email'], "[".stripslashes($shop_config['basic_name'])."] 판매사 가입을 진심으로 환영합니다.", $content);	
}
############ 판매사 가입 축하메일 보내기 ############

parentMovePage("../{$Main}?channel=regist_vendor_ok");

?>
