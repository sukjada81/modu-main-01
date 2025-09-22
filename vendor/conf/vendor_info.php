<?php 

include_once("../common/top.php");

define('VENDOR_FOLDER', '../../image/vendor');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","vendor_info.html");
$tpl->scan_area("main");

$sql = "SELECT * FROM mallRN_vendor WHERE id = '{$v_my_id}'";
if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");

$item_array				= array('auth', 'sell', 'delivery_type', 'id', 'passwd', 'comp_name', 'comp_owner', 'comp_license_no', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'comp_email', 'comp_tel', 'comp_fax', 'cont_name', 'cont_cell', 'cont_email', 'cont_part', 'cont_position', 'goods_auth', 'commission', 'bank_name', 'bank_num', 'bank_owner', 'account_cycle');
$auth_array				= array('R' => '승인요청', 'Y' => '승인완료','N' => '승인보류');
$sell_array				= array('A' => '판매허용', 'R' => '판매준비','N' => '판매중지');
$goods_auth_array		= array('A' => '자동승인', 'P' => '관리자 수동승인');
$delivery_type_array	= array('판매자배송', '본사배송');
$account_cycle_array	= array('', '월1회정산', '월2회정산', '월3회정산', '월4회정산');

for($i=0,$cnt=count($item_array); $i<$cnt; $i++) {
	${$item_array[$i]} = stripslashes($data[$item_array[$i]]);
}

$auth			= $auth_array[$auth];
$sell			= $sell_array[$sell];
$goods_auth		= $goods_auth_array[$goods_auth];
$delivery_type	= $delivery_type_array[$delivery_type];
$account_cycle	= $account_cycle_array[$account_cycle];

for($i=1;$i<3;$i++) {
	if($data['image'.$i]) {
		$image = $data['image'.$i]."?t=".$t;
		$imgSize = @GetImageSize(VENDOR_FOLDER.'/'.$data['image'.$i]);
		$size = $imgSize[0];
		$tpl->parse("loop_image");
	}
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>