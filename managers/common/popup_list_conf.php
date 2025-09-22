<?php 

include_once("../common/popup_top.php");

$type = isset($_GET['type']) ? $_GET['type'] : '';

if(!$type) {
	iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");
}

$field_arr = array();
$field_arr['goods']		= array('no' => '번호', 'uid' => '상품번호', 'image' => '이미지', 'name' => '상품명/대표분류', 'vendor' => '판매사', 'price' => '판매가', 'orig_price' => '공급가', 'commission' => '수수료(마진)율', 'consumer_price' => '소비자가', 'display_use' => '진열상태', 'sale_use' => '판매상태', 'qty' => '재고', 'option_use' => '옵션', 'mileage_type' => '마일리지', 'delivery_type' => '배송비', 'goods_code' => '자체상품코드', 'signdate' => '등록일/수정일');

$field_arr['member']	=  array('no' => '번호', 'name' => '이름', 'id' => '아이디', 'crm' => 'CRM', 'level' => '등급', 'cell' => '휴대폰번호', 'gender' => '성별', 'birth' => '생년월일', 'address1' => '거주지역', 'mileage' => '마일리지', 'order_cnt' => '주문건수', 'order_price' => '주문금액', 'cnts' => '방문횟수', 'auth' => '가입승인', 'mailling' => '메일수신', 'sms' => 'SMS수신', 'mobile' => '가입경로', 'nondormant_time' => '휴면해제일', 'order_time' => '최종주문일', 'login_time' => '최종로그인', 'signdate' => '가입일');

$field_arr['order']	=  array('no' => '번호', 'signdate' => '주문일시', 'order_num' => '주문번호', 'name' => '주문자', 'goods' => '주문상품', 'goods_total' => '총상품금액', 'delivery_total' => '총배송비', 'discount_total' => '총할인금액', 'pay_total' => '총주문금액', 'cancel_total' => '취소금액', 'pay_type' => '결제수단', 'pay_status' => '결제상태', 'cnts1' => '미배송', 'cnts2' => '배송중', 'cnts3' => '배송완료', 'cnts4' => '취소', 'cnts5' => '교환', 'cnts6' => '반품');


$field_arr_default['goods'] = array('name', 'price');
$field_arr_default['member'] = array('name', 'id', 'level', 'signdate');
$field_arr_default['order'] = array('order_num', 'name', 'goods', 'pay_total', 'pay_type');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_list_conf.html");
$tpl->scan_area("main");

$sql		= "SELECT fields FROM mallRN_list_show_config WHERE vendor = '' && name='{$type}'";
$data		= $mysql->get_one($sql);

if(!$data) {
	if($type == 'goods') {
		$data = "no|1|*|uid|1|*|image|1|*|name|1|*|vendor|0|*|price|1|*|orig_price|0|*|commission|0|*|consumer_price|0|*|option_use|0|*|display_use|1|*|sale_use|1|*|qty|1|*|mileage_type|0|*|delivery_type|0|*|goods_code|0|*|signdate|1";
	}
	else if($type == 'member') {
		$data = "no|1|*|name|1|*|id|1|*|crm|1|*|level|1|*|cell|0|*|gender|0|*|birth|0|*|address1|0|*|mileage|1|*|order_cnt|1|*|order_price|1|*|cnts|1|*|auth|1|*|mailling|0|*|sms|0|*|mobile|0|*|nondormant_time|0|*|order_time|1|*|login_time|1|*|signdate|1";
	}
	else if($type == 'order') {
		$data = "no|1|*|signdate|1|*|order_num|1|*|name|1|*|goods|1|*|goods_total|0|*|delivery_total|0|*|discount_total|0|*|pay_total|1|*|cancel_total|1|*|pay_type|1|*|pay_status|1|*|cnts1|0|*|cnts2|0|*|cnts3|0|*|cnts4|0|*|cnts5|0|*|cnts6|0";
	}
}

$conf_data	= explode("|*|", $data);

foreach ($conf_data as $k => $v) {
	$v2 = explode("|",$v);
	$name		= $v2[0];
	$checked	= $v2[1];
	$title		=  $field_arr[$type][$name];
	
	if($checked==1) $class = "selected";
	else $class = "";

	$tpl->parse("loop_field");
}

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>