<?php 

include_once("../common/top.php");
include_once("../../{$use_skin}/info/skin_define.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","inquiry_info.html");
$tpl->scan_area("main");

$addstring			= "";
$search_variable	= array('field' ,'keyword', 'vendor', 'cate', 'answer', 'id', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
if(!$uid) alert("정보가 제대로 넘어오지 못했습니다.", "back");

$sql = "SELECT * FROM mallRN_inquiry WHERE uid='{$uid}'";
if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
$item_array = array('g_uid', 'g_name', 'vendor', 'id', 'name', 'subject', 'content', 'answer', 'files', 'acc_ip', 'signdate');
	
foreach($item_array as $k => $v) {
	if($v != 'content' && $v != 'answer')	${$v} = specialStrReplace2($data[$v]);
	else									${$v} = stripslashes($data[$v]);
}

$content	= ieHackCheck($content);
$answer		= add_escape_re_string($answer);

if($id) {
	$sql = "SELECT a.name FROM mallRN_member_level a, mallRN_member b WHERE a.level = b.level && b.id = '{$id}'";
	$member_level = stripslashes($mysql->get_one($sql));

	$MEMBER_ID		= $id;
	$MEMBER_LEVEL	= $member_level;

	$tpl->parse("is_member");
}

if($vendor) {
	$sql	= "SELECT comp_name FROM mallRN_vendor WHERE id = '{$vendor}'";
	$vendor_name	= $mysql->get_one($sql);

	$VENDOR_ID		= $vendor;
	$VENDOR			= stripslashes($vendor_name);
}
else $VENDOR = "본사";

$signdate = date("Y-m-d H:i:s", $signdate);

if($files) {
	$attach_array = explode("|", $files);
	foreach($attach_array as $k => $v) {
		$attach_name = $uid."/".$v;
		$tpl->parse("loop_attach");
	}
	unset($attach_array);
	$tpl->parse("is_attach");
}		

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>