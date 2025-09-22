<?php 

include_once("../common/top.php");
include_once("../../{$use_skin}/info/skin_define.php");

// 템플릿
$tpl = new classTemplate;
$tpl->define("main","review_info.html");
$tpl->scan_area("main");

$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'vendor', 'stars', 'best', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

######################## 회원등급 정보 #############################
$sql = "SELECT * FROM mallRN_member_level WHERE uid > 0 ORDER BY level ASC";
$mysql->query($sql);

$level_array = array();
while($row = $mysql->fetch_array()) {
	$level_array[$row['level']] = stripslashes($row['name']);
}
######################## 회원등급 정보 #############################

$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
if(!$uid) alert("정보가 제대로 넘어오지 못했습니다.", "back");

$sql = "SELECT * FROM mallRN_review WHERE uid='{$uid}'";
if(!$data = $mysql->one_row($sql)) alert("등록된 정보가 없거나 삭제되었습니다.","back");
	
$item_array = array('g_uid', 'g_name', 'vendor', 'id', 'name', 'stars', 'content', 'files', 'acc_ip', 'best', 'signdate');
	
foreach($item_array as $k => $v) {
	if($v != 'content')	${$v} = specialStrReplace2($data[$v]);
	else				${$v} = stripslashes($data[$v]);
}

$content	= ieHackCheck($content);

if($id) $id = "/ {$id}";

for($i = 0; $i < $stars; $i ++) $tpl->parse("loop_stars");

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

${"checked_best_".$best} = "checked";

$tpl->parse("main");
$tpl->tprint("main");
$tpl->close();

include_once("../common/bottom.php");

?>