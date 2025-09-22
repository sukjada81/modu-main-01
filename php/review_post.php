<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_PATH',	'../');
define('REVIEW_DATA',	'../image/review');

include_once('init.php');

$mysql->msgType(1);

$table_name	= "mallRN_review";

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

if($my_id) {
	$_POST['id']		= $my_id;
	$_POST['name']		= $my_name;
	$_POST['passwd']	= "****";
}
else {
	$_POST['id']		= "";
	$_POST['name']		= checkPostVar('name');	
	$_POST['passwd']	= checkPostVar('passwd');
}

$_POST['g_uid']			= checkPostVar('g_uid');
$_POST['og_uid']		= checkPostVar('og_uid');
$_POST['content']		= checkPostVar('content');
$_POST['acc_ip']		= $_SERVER['REMOTE_ADDR'];
$_POST['signdate']		= time();

if(!$_POST['name'] || !$_POST['og_uid'] || !$_POST['g_uid'] || !$_POST['content']) {
	logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
}

$_POST['passwd'] = md5($_POST['passwd']);

$sql = "SELECT vendor, name FROM mallRN_goods WHERE uid = '{$_POST['g_uid']}'";
$data				= $mysql->one_row($sql);
$_POST['g_name']	= $data['name'];
$_POST['vendor']	= $data['vendor'];

$item_array			= array('vendor', 'og_uid', 'g_uid', 'g_name', 'op_name', 'id', 'passwd', 'name', 'stars', 'content', 'files', 'acc_ip', 'signdate');
$item_able_value	= array('stars' => ['1', '2', '3','4','5']);

######################## 글 등록  #########################		
$sql = "INSERT INTO {$table_name} SET";
foreach ($item_array as $k => $v) {
	if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
	else $_POST[$v] = checkPostVar($v);

	if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
	else $sql .= " {$v} = '{$_POST[$v]}',";
}
$mysql->query($sql);
$uid		= $mysql->InsertNo();
$upload_dir	=  REVIEW_DATA.'/'.$uid;
if(!is_dir($upload_dir)) mkdir($upload_dir, 0744);	
######################## 글 등록  #########################

$attach_file = array();
for($i = 1; $i < 6; $i ++) {
	if(!preg_match("/none/i",$_FILES['attach_file'.$i]['tmp_name']) && $_FILES['attach_file'.$i]['tmp_name']) {									
		$attach_file[] = upFile($_FILES['attach_file'.$i]['tmp_name'], $_FILES['attach_file'.$i]['name'], $upload_dir, 0, '', 1, 5);
	}
}
if(count($attach_file) > 0) {
	$files = join("|", $attach_file);
	$sql = "UPDATE {$table_name} SET files = '{$files}' WHERE uid = '{$uid}'";
	$mysql->query($sql);
}

iframeViewMsg("등록 되었습니다.");

?>
