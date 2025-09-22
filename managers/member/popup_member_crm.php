<?php 

include_once("../common/popup_top.php");

$id = add_escape_re_string(checkGetVar('id'));

if(!$id) {
	iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");
}

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_member_crm.html");
$tpl->scan_area("main");

$adds	= "";
$sql = "SELECT * FROM mallRN_member WHERE id = '{$id}'";
if(!$data = $mysql->one_row($sql)) {
	$sql = "SELECT * FROM mallRN_member_sleep WHERE id = '{$id}'";
	if(!$data = $mysql->one_row($sql)) iframeViewError("등록된 회원이 아니거나 탈퇴한 회원 입니다.");
	$adds	= "[휴면] ";
}

######################## 회원등급 #############################
$sql = "SELECT * FROM mallRN_member_level ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	$level_array[$row['level']] = $row['name'];
}
######################## 회원등급 #############################

$item_array		= array('name', 'tel', 'cell', 'postcode', 'address1', 'address2', 'email', 'birth', 'birth_sl', 'gender', 'marry', 'hobby', 'job', 'comp', 'comp_owner', 'comp_num', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'add1', 'add2', 'add3', 'add4', 'add5', 'memo', 'reference', 'id', 'passwd', 'level', 'mileage', 'mailling', 'mailling_date', 'sms', 'sms_date', 'auth', 'mobile', 'cnts', 'login_time', 'order_time', 'signdate');
$birth_sl_array	= array("S" => "양력",		"L" => "음력",		"N" => "미선택"); 
$gender_array	= array("M" => "남성",		"F" => "여성",		"N" => "미선택"); 
$marry_array	= array("M" => "기혼",		"S" => "미혼",		"N" => "미선택"); 
$mailling_array	= array("Y" => "수신허용",		"N" => "수신안함"); 
$sms_array		= array("Y" => "수신허용",		"N" => "수신안함"); 
$mobile_array	= array("Y" => "모바일",		"N" => "PC"); 
$auth_array		= array("Y" => "승인",		"N" => "미승인"); 

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);
}

$name			= $adds.$name;
$level			= $level_array[$level];
$birth			= $birth		? substr($birth, 0, 4)."년 ".substr($birth, 4, 2)."월 ".substr($birth, 6, 2)."일 "	: "";
$comp_num		= $comp_num		? substr($comp_num, 0, 3)."-".substr($comp_num, 3, 2)."-".substr($comp_num, 5, 5)	: "";
$auth			= $auth_array[$auth];
$birth_sl		= $birth_sl_array[$birth_sl];
$gender			= $gender_array[$gender];
$marry			= $marry_array[$marry];
$mailling		= @$mailling_array[$mailling];
$sms			= @$sms_array[$sms];
$mobile			= $mobile_array[$mobile];
$mailling_date	= date("Y-m-d H:i:s", $mailling_date);
$sms_date		= date("Y-m-d H:i:s", $sms_date);
if($login_time == 0)	$login_time = "-";
else					$login_time		= date("Y-m-d H:i:s", $login_time);
if($order_time == 0)	$order_time = "-";
else					$order_time		= date("Y-m-d H:i:s", $order_time);
$signdate		= date("Y-m-d H:i:s", $signdate);

if(strlen($tel) == 12)		$tel	= substr($tel, 0, 4)."-".substr($tel, 4, 4)."-".substr($tel, 8, 4);
else if(strlen($tel) == 11)	$tel	= substr($tel, 0, 3)."-".substr($tel, 3, 4)."-".substr($tel, 7, 4);
else if(strlen($tel) == 10)	$tel	= substr($tel, 0, 2)."-".substr($tel, 2, 4)."-".substr($tel, 6, 4);

if(strlen($cell) == 12)			$cell	= substr($cell, 0, 4)."-".substr($cell, 4, 4)."-".substr($cell, 8, 4);
else if(strlen($cell) == 11)	$cell	= substr($cell, 0, 3)."-".substr($cell, 3, 4)."-".substr($cell, 7, 4);
else if(strlen($cell) == 10)	$cell	= substr($cell, 0, 2)."-".substr($cell, 2, 4)."-".substr($cell, 6, 4);

$mileage		= number_format($mileage);

$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 2";
$member_config	= $mysql->one_row($sql);

$item_array		= array('member_form_comp','member_form_comp_num','member_form_comp_owner','member_form_comp_address','member_form_comp_type','member_form_comp_item');
$ck_comp		= 0;

foreach($item_array as $k => $v) {
	if($member_config[$v] == 0) continue;	
	$v2			= str_replace("member_form_", "", $v);	
	$ck_comp	= 1;
	$tpl->parse("is_{$v2}");
}

if($ck_comp == 1) $tpl->parse("is_comp_info");

$sql			= "SELECT count(*) FROM mallRN_order_info WHERE id = '{$id}' && pay_status = 'C'";
$order_cnt		= number_format($mysql->get_one($sql));

$sql			= "SELECT SUM(pay_total) as pay, SUM(cancel_total) as cancel, SUM(refund_total) as refund FROM mallRN_order_info WHERE id = '{$id}' && pay_status = 'C'";
$data			= $mysql->one_row($sql);
$order_price_o	= $data['pay'] - $data['cancel'] - $data['refund'];
$order_price	= number_format($order_price_o);

$main_pay_type	= $last_order_date = "-";
$average_order_price = 0;
if($order_cnt > 0) {	
	$pay_type_array		= array("B" => "무통장입금", "C" => "카드결제", "R" => "실시간계좌이체", "V" => "가상계좌이체", "H" => "휴대폰결제", "M" => "마일리지");
	$sql				= "SELECT pay_type, count(*) as cnt FROM mallRN_order_info WHERE id = '{$id}' && pay_status = 'C' GROUP BY pay_type ORDER BY cnt desc LIMIT 1";
	$data				= $mysql->one_row($sql);
	$main_pay_type		= $pay_type_array[$data['pay_type']];	
	
	$average_order_price = number_format($order_price_o / $order_cnt);
}

$sql			= "SELECT SUM(mileage) as mileage, SUM(use_mileage) as use_mileage FROM mallRN_mileage WHERE id = '{$id}'";
$data			= $mysql->one_row($sql);
$save_mileage	= number_format($data['mileage']);
$use_mileage	= number_format($data['use_mileage']);

$sql			= "SELECT count(*) FROM mallRN_coupon WHERE id = '{$id}' && status = 0 && e_date > '".date("Y-m-d 23:59:59")."'";
$coupon			= number_format($mysql->get_one($sql));

$sql			= "SELECT count(*) FROM mallRN_coupon WHERE id = '{$id}' && status = 1";
$use_coupon		= number_format($mysql->get_one($sql));

if($use_coupon) {
	$sql				= "SELECT SUM(IF(status = 0, price , 0)) as total1, SUM(IF(status = 1, price , 0)) as total2 FROM mallRN_order_sales WHERE id = '{$id}' && type = 2";
	$data				= $mysql->one_row($sql);
	$use_coupon_price	= number_format($data['total1'] - $data['total2']);
}
else $use_coupon_price	= 0;

$sql			= "SELECT count(*) FROM mallRN_board_counsel WHERE id = '{$id}'";
$counsel		= number_format($mysql->get_one($sql));

$sql			= "SELECT count(*) FROM mallRN_review WHERE id = '{$id}'";
$review			= number_format($mysql->get_one($sql));

$sql			= "SELECT count(*) FROM mallRN_inquiry WHERE id = '{$id}'";
$inquiry		= number_format($mysql->get_one($sql));

####################### 관리자 로그 ##########################	
adminLog($my_id, "회원CRM - {$id}", 2);
####################### 관리자 로그 ##########################

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>