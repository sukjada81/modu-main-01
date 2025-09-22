<?php 

include_once("../common/popup_top.php");

$type = isset($_GET['type']) ? $_GET['type'] : '';

if(!$type) {
	iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");
}

$field_arr = array();
$field_arr['goods']		= array('no' => '번호', 'uid' => '상품번호', 'image' => '이미지', 'name' => '상품명/대표분류', 'price' => '판매가', 'orig_price' => '공급가', 'commission' => '수수료율', 'consumer_price' => '소비자가', 'display_use' => '진열상태', 'sale_use' => '판매상태', 'qty' => '재고', 'option_use' => '옵션', 'delivery_type' => '배송비', 'goods_code' => '자체상품코드', 'signdate' => '등록일/수정일');

$field_arr['order']	=  array('no' => '번호', 'signdate' => '주문일시', 'order_num' => '주문번호', 'name' => '주문자', 'goods' => '주문상품', 'goods_total' => '총상품금액', 'delivery_total' => '총배송비', 'discount_total' => '총할인금액', 'pay_total' => '총주문금액', 'cancel_total' => '취소금액', 'pay_type' => '결제수단', 'pay_status' => '결제상태', 'cnts1' => '미배송', 'cnts2' => '배송중', 'cnts3' => '배송완료', 'cnts4' => '취소', 'cnts5' => '교환', 'cnts6' => '반품');


$field_arr_default['goods'] = array('name', 'price');
$field_arr_default['member'] = array('name', 'id', 'level', 'signdate');
$field_arr_default['order'] = array('order_num', 'name', 'goods', 'pay_total', 'pay_type');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_list_conf.html");
$tpl->scan_area("main");

$sql		= "SELECT fields FROM mallRN_list_show_config WHERE vendor = '{$v_my_id}' && name='{$type}'";
$data		= $mysql->get_one($sql);
if(!$data) {
	if($type == 'goods') {
		$data = "no|1|*|uid|1|*|image|1|*|name|1|*|price|1|*|orig_price|0|*|commission|0|*|consumer_price|0|*|option_use|0|*|display_use|1|*|sale_use|1|*|qty|1|*|delivery_type|0|*|goods_code|0|*|signdate|1";
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