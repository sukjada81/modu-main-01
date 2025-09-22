<?php 

include_once("../common/top.php");

define('VENDOR_FOLDER', '../../image/vendor');

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","vendor_info.html");
$tpl->scan_area("main");

$mode = checkGetVar('mode');
if(!$mode) $mode = 'write';

$addstring			= "";
$search_variable	= array('field', 'keyword', 'auth', 'sell', 'goods_auth', 'account_cycle', 'image1', 'image2', 'delivery_type', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

if($mode=='modify') {
	
	$uid = $_GET['uid'];
	if(!$uid) alert("정보가 제대로 넘어오지 못했습니다.","back");

	$sql = "SELECT * FROM mallRN_vendor WHERE uid='{$uid}'";
	if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
	$item_array = array('auth', 'sell', 'delivery_type', 'id', 'passwd', 'comp_name', 'comp_owner', 'comp_license_no', 'comp_postcode', 'comp_address1', 'comp_address2', 'comp_type', 'comp_item', 'comp_email', 'comp_tel', 'comp_fax', 'cont_name', 'cont_cell', 'cont_email', 'cont_part', 'cont_position', 'goods_auth', 'commission', 'bank_name', 'bank_num', 'bank_owner', 'account_cycle', 'memo');

	foreach($item_array as $k => $v) {
		${$v} = specialStrReplace2($data[$v]);
	}

	${"checked_auth_".$auth}					= "checked='checked'";
	${"checked_sell_".$sell}					= "checked='checked'";
	${"checked_delivery_type_".$delivery_type}	= "checked='checked'";	
	${"checked_goods_auth_".$goods_auth}		= "checked='checked'";

	for($i=1;$i<3;$i++) {
		if($data['image'.$i]) {
			$image = $data['image'.$i]."?t=".$t;
			$imgSize = GetImageSize(VENDOR_FOLDER.'/'.$data['image'.$i]);
			$size = $imgSize[0];
			$tpl->parse("loop_image");
		}
	}

	$TTL = "수정";

	$tpl->parse("is_modify1");	

	$sql = "SELECT count(*) FROM mallRN_order_goods WHERE vendor = '{$id}' && reals = 1";
	if($mysql->get_one($sql) == 0) {
		$tpl->parse("is_delete");	
	}

	####################### 관리자 로그 ##########################	
	adminLog($my_id, "판매사정보 - {$id}", 7);
	####################### 관리자 로그 ##########################
}
else {
	$mode						= "write";
	$checked_auth_Y				= "checked='checked'";
	$checked_sell_A				= "checked='checked'";
	$checked_goods_auth_A		= "checked='checked'";
	$checked_delivery_type_0	= "checked='checked'";
	$account_cycle				= "1";
	$commission					= "0.00";

	$TTL = "등록";

	$tpl->parse("is_write1");
}

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>