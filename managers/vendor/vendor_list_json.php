<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once('../common/ad_init.php');

$mysql->msgType(2);

$my_array	= array();
$cycle		= checkPostVar('cycle');

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i", $_SERVER['HTTP_REFERER'])) json_error_msg('정상적으로 등록하세요!');

$where = "";
if($cycle) $where = "&& account_cycle = '{$cycle}'";

$sql = "SELECT * FROM mallRN_vendor WHERE uid > 0 {$where} ORDER BY comp_name ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()){    
	$vendor_id		= stripslashes($row['id']);
	$vendor_name	= stripslashes($row['comp_name'])." ({$vendor_id})";
	
	$my_array[] = ["name" => $vendor_name, "id" => $vendor_id];
}
	
echo json_encode($my_array);

?>