<?php 

/*
###############################################
     ::: 로그인 체크함수 :::          
###############################################
*/

function checkLogin($rand) { 
    global $_COOKIE; 

	$get_userid = base64_decode($_COOKIE['my_id']); 
    $get_sid	= $_COOKIE['sid']; 
 	if(!$get_userid || !$get_sid) return false; 
    $get_userid .= $rand;  
    $real_sid	= md5($get_userid); 
    if($get_sid == $real_sid) return true; 
	else return false; 

} 

############ 로그인 쿠키체크 ####################
if(isset($_COOKIE['my_id'])) {

	$my_id		= base64_decode($_COOKIE['my_id']); 	
	if(!checkLogin(CONF_KEY))  Error("올바른 경로가 아닙니다.");	
		
	$sql				= "SELECT name, email, level, sns_type FROM mallRN_member WHERE id = '{$my_id}'";
	if($data = $mysql->one_row($sql)) {
		$my_name			= stripslashes($data['name']);
		$my_email			= stripslashes($data['email']);
		$my_level			= $data['level'];
		$my_sns_type		= $data['sns_type'];
		
		$sql				= "SELECT * FROM mallRN_member_level WHERE level = '{$my_level}'";
		$data2				= $mysql->one_row($sql);
		
		$my_discount		= $data2['discount'];
		$my_mileage			= $data2['mileage'];
		$my_delivery_free	= $data2['delivery_free'];
	}
	else {
		$my_id = $my_name = $my_email = $my_sns_type = "";
		$my_level = $my_discount = $my_mileage = $my_delivery_free = "0";	
	}
} 
else {
	$my_id = $my_name = $my_email = $my_sns_type = "";
	$my_level = $my_discount = $my_mileage = $my_delivery_free = "0";	
}
############ 로그인 쿠키체크 ####################

?>