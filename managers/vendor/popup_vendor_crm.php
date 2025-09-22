<?php 

include_once("../common/popup_top.php");

$id = checkGetVar('id');

if(!$id) {
	iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");
}

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_vendor_crm.html");
$tpl->scan_area("main");

$sql = "SELECT * FROM mallRN_vendor WHERE id = '{$id}'";
if(!$data = $mysql->one_row($sql)) iframeViewError("등록된 판매사가 아니거나 삭제된 판매사 입니다.");

$item_array = array('auth', 'sell', 'delivery_type', 'id', 'passwd', 'comp_name', 'comp_owner', 'comp_license_no', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'comp_email', 'comp_tel', 'comp_fax', 'cont_name', 'cont_cell', 'cont_email', 'cont_part', 'cont_position', 'goods_auth', 'commission', 'bank_name', 'bank_num', 'bank_owner', 'account_cycle', 'image1', 'image2', 'memo', 'signdate');

$auth_array				= array("R" => "승인요청 ",	"Y" => "승인완료",		"N" => "승인보류"); 
$sell_array				= array("A" => "판매허용 ",	"R" => "판매준비",		"N" => "판매중지"); 
$goods_auth_array		= array("A" => "자동승인",		"P" => "관리자 수동승인"); 
$delivery_type_array	= array("0" => "판매자배송 ",	"1" => "본사배송"); 
$account_cycle_array	= array("1" => "주1회",		"2" => "월2회",		"3" => "월1회",		"4" => "자율"); 

foreach($item_array as $k => $v) {
	${$v} = stripslashes($data[$v]);
}

$auth					= $auth_array[$auth];
$sell					= $sell_array[$sell];
$goods_auth				= $goods_auth_array[$goods_auth];
$delivery_type			= $delivery_type_array[$delivery_type];
$account_cycle			= $account_cycle_array[$account_cycle];
$signdate				= date("Y-m-d H:i:s", $signdate);

if($image1)	$image1		= "둥록완료";
else		$image1		= "미등록";

if($image2)	$image2		= "둥록완료";
else		$image2		= "미등록";


$sql					= "SELECT SUM(IF(auth_ck = 'Y' , 1 , 0)) as cnt1, SUM(IF(auth_ck = 'N' , 1 , 0)) as cnt2 FROM mallRN_goods WHERE vendor = '{$id}'";
$data					= $mysql->one_row($sql);

$goods_cnt				= number_format($data['cnt1'] + $data['cnt2']);
$goods_cnt1				= number_format($data['cnt1']);
$goods_cnt2				= number_format($data['cnt2']);

$sql					= "SELECT count(*) FROM mallRN_order_goods WHERE vendor = '{$id}'";
$order_cnt				= number_format($mysql->get_one($sql));

$order_price			= 0;
$calculate_price1		= 0;
$calculate_price2		= 0;
if($order_cnt > 0) {
	$sql				= "SELECT signdate FROM mallRN_order_goods WHERE vendor = '{$id}' ORDER BY uid DESC LIMIT 1";
	$last_order_date	= date("Y-m-d H:i:s", $mysql->get_one($sql));

	for($i = 0; $i < 3; $i ++) {
		if($i < 2) {
			$sql		= "SELECT SUM(IF(status = 0 && adjustment = 0, price , 0)) as total11, SUM(IF(status = 0 && adjustment = 1, price , 0)) as total12, SUM(IF(status = 1 && adjustment = 0, price , 0)) as total21, SUM(IF(status = 1 && adjustment = 1, price , 0)) as total22 FROM mallRN_order_sales WHERE vendor = '{$id}' && type = '{$i}'";
		}
		else {
			$sql		= "SELECT SUM(IF(status = 0 && adjustment = 0, commission , 0)) as total11, SUM(IF(status = 0 && adjustment = 1, commission , 0)) as total12, SUM(IF(status = 1, commission && adjustment = 0 , 0)) as total21, SUM(IF(status = 1 && adjustment = 1, commission , 0)) as total22 FROM mallRN_order_sales WHERE vendor = '{$id}' && type = '0'";
		}

		$data			= $mysql->one_row($sql);
		$total11		= $data['total11'] ? $data['total11'] : 0;
		$total12		= $data['total12'] ? $data['total12'] : 0;
		$total21		= $data['total21'] ? $data['total21'] : 0;		
		$total22		= $data['total22'] ? $data['total22'] : 0;		
		
		if($i < 2) {			
			$calculate_price1	+= ($total11 - $total21);
			$calculate_price2	+= ($total12 - $total22);
			$order_price		+= ($total11 - $total21) + ($total12 - $total22);
		}
		else {			
			$calculate_price1	-= ($total11 - $total21);
			$calculate_price2	-= ($total12 - $total22);
			$order_price		-= ($total11 - $total21) - ($total12 - $total22);
		}
	}

	$order_price		= number_format($order_price);
	$calculate_price1	= number_format($calculate_price1);
	$calculate_price2	= number_format($calculate_price2);
}

$sql			= "SELECT count(*) FROM mallRN_board_vcounsel WHERE id = '{$id}'";
$counsel		= number_format($mysql->get_one($sql));

$sql			= "SELECT count(*) FROM mallRN_review WHERE vendor = '{$id}'";
$review			= number_format($mysql->get_one($sql));

$sql			= "SELECT count(*) FROM mallRN_inquiry WHERE vendor = '{$id}'";
$inquiry		= number_format($mysql->get_one($sql));


####################### 관리자 로그 ##########################	
adminLog($my_id, "판매사CRM - {$id}", 7);
####################### 관리자 로그 ##########################

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>