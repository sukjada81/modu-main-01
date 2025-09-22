<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('DEFAULT_PATH',	'../');
include_once('init.php');

$mysql->msgType(2);

$my_array	= array();
$keyword	= checkPostVar('keyword');
$ints		= checkPostVar('ints');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');
if(strlen($keyword) == 0) json_error_msg('필수 정보가 넘어오지 못했습니다.');

//$keyword = "10";
//$ints = "0";

if(is_numeric($keyword) && $ints == 1) {
	$where = "{$keyword}%";
	
	$sql = "SELECT c.uid, c.keyword, c.split_keyword, c.split_keyword_ek FROM ( SELECT a.uid FROM mallRN_keyword_autocomplete a WHERE (a.keyword like '{$where}' ) ) b JOIN mallRN_keyword_autocomplete c ON b.uid = c.uid ORDER BY c.count DESC LIMIT 10";
}
else {
	if(is_numeric($keyword) && $ints != 1) {
		$where = "{$keyword},%";
	}
	else $where = "{$keyword}%";

	$sql = "SELECT c.uid, c.keyword FROM ( SELECT a.uid FROM mallRN_keyword_autocomplete a WHERE ( a.split_keyword like '{$where}' || a.split_keyword_ek like '{$where}' ) ) b JOIN mallRN_keyword_autocomplete c ON b.uid = c.uid ORDER BY c.count DESC LIMIT 10";
}
$mysql->query($sql);

$cnt		= 0;
$in_array	= array();
while($row = $mysql->fetch_array()){
	$row['keyword'] = stripslashes($row['keyword']);
	$in_array[]		= $row['uid'];

	$my_array[] = ["keyword" => $row['keyword']];
	$cnt ++;
}

if($cnt < 10) {
	$cnt2		= 10 - $cnt;
	if(count($in_array) == 0) $in_array[] = '0';
	$in_array	= join(",", $in_array);
	if(is_numeric($keyword) && $ints == 1) {
		$where = "%{$keyword}%";
		
		$sql = "SELECT c.keyword, c.split_keyword, c.split_keyword_ek FROM ( SELECT a.uid FROM mallRN_keyword_autocomplete a WHERE ( a.keyword like '{$where}' ) ) b JOIN mallRN_keyword_autocomplete c ON b.uid = c.uid WHERE c.uid NOT IN ({$in_array}) ORDER BY c.count DESC LIMIT {$cnt2}";
	}
	else {
		if(is_numeric($keyword) && $ints != 1) {
			$where = "%{$keyword},%";
		}
		else $where = "%{$keyword}%";

		$sql = "SELECT c.keyword, c.uid FROM ( SELECT a.uid FROM mallRN_keyword_autocomplete a WHERE ( a.split_keyword like '{$where}' || a.split_keyword_ek like '{$where}' ) ) b JOIN mallRN_keyword_autocomplete c ON b.uid = c.uid WHERE c.uid NOT IN ({$in_array}) ORDER BY c.count DESC LIMIT {$cnt2}";
	}
	$mysql->query($sql);

	$cnt	= 0;
	while($row = $mysql->fetch_array()){
		$row['keyword'] = stripslashes($row['keyword']);

		$my_array[] = ["keyword" => $row['keyword']];
		$cnt ++;
	}
}

echo json_encode($my_array);

?>