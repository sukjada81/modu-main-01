<?php 

include_once("../common/popup_top.php");

$og_uid = checkGetVar('og_uid');

if(!$og_uid) {
	iframeViewError("필수 정보가 제대로 넘어오지 못했습니다.");
}

$sql	= "SELECT count(*) FROM mallRN_order_goods WHERE vendor = '{$v_my_id}' && uid = '{$og_uid}' && reals = 1";
if($mysql->get_one($sql) ==0) iframeViewError("해당 주문상품이 없거나 삭제 되었습니다.");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","popup_order_log.html");
$tpl->scan_area("main");

$status_array	= array("0" => "입금대기중", "1" => "결제완료", "2" => "배송준비중", "3" => "배송중", "4" => "배송완료", "5" => "구매확정", "7" => "교환", "8" => "반품", "9" => "취소"); 
$status2_array	= array("0" => "", "1" => "요청", "2" => "중", "3" => "회수완료", "4" => "발송완료", "5" => "완료"); 

$sql = "SELECT * FROM mallRN_order_log WHERE og_uid = '{$og_uid}' ORDER BY uid ASC";
$mysql->query($sql);

$NUM = 0;
while($row = $mysql->fetch_array()){ 
	$NUM ++;
	$STATUS_DATE	= date("Y-m-d H:i:s", $row['signdate']);
	$ID				= $row['id'];
	if($row['prev_status2'])	$PREV_STATUS	= $status_array[$row['prev_status']].$status2_array[$row['prev_status2']];
	else						$PREV_STATUS	= $status_array[$row['prev_status']];
	if($row['status2'])	$STATUS	= $status_array[$row['status']].$status2_array[$row['status2']];
	else				$STATUS	= $status_array[$row['status']];

	$tpl->parse("loop_list");
}

if($NUM == 0) $tpl->parse("empty_list");

$tpl->parse("main");
$tpl->tprint("main");

$tpl->close();

include_once("../common/popup_bottom.php");

?>