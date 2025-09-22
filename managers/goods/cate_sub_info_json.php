<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

define('__BASIC__',		'1');
define('__VENDOR_ABLE__', '1');

include_once('../common/ad_init.php');

$mysql->msgType(2);

$my_array = array();

$cate = checkPostVar('cate');

if(!$cate) json_error_msg('필수 정보가 넘어오지 못했습니다.');

$sql	= "SELECT * FROM mallRN_cate WHERE cate = '{$cate}'";
$data	= $mysql->one_row($sql);

if(!$data) json_error_msg('해당분류가 삭제되었거나 존재하지 않습니다.');

$sql = "SELECT cate, cate_name, cate_sub FROM mallRN_cate WHERE cate_parent = {$cate} ORDER BY sequence ASC";
$mysql->query($sql);

while($row = $mysql->fetch_array()){    
	$row['cate_name'] = addslashes($row['cate_name']);
	if($row['cate_sub']==1) {
 		$cate_name = "{$row['cate_name']}→";
    } 
	else {
		$cate_name = $row['cate_name'];
	}	
	$my_array[] = ["name"=>$cate_name, "id"=>$row['cate']];
}
	
echo json_encode($my_array);

?>