<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$type = checkPostVar('type');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

if(!isset($_POST['fields_order']) || !$type) logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

$fields_order	= explode(",",$_POST['fields_order']);	

$fields_info = array();
for($i=0,$cnt=count($fields_order);$i<$cnt;$i++) {
	if($_POST[$fields_order[$i].'_checked']!=1) $_POST[$fields_order[$i].'_checked'] = 0;	
	$fields_info[] = $fields_order[$i].'|'.$_POST[$fields_order[$i].'_checked'];
}

if(count($fields_info)>0) $fields_info = join('|*|', $fields_info);
else logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

$sql = "UPDATE mallRN_list_show_config SET fields = '{$fields_info}' WHERE vendor = '' && name='{$type}'";
$mysql->query($sql);

iframeViewMsg("설정이 적용 되었습니다.");

?>
