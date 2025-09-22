<?php 

/*
###############################################
     ::: 로그인 체크함수 :::          
###############################################
*/

function checkVLogin($rand) { 
    global $_COOKIE; 

	$get_userid = base64_decode($_COOKIE['v_my_id']); 
    $get_sid	= $_COOKIE['v_sid']; 
 	if(!$get_userid || !$get_sid) return false; 
    $get_userid .= $rand;  
    $real_sid	= md5($get_userid); 
    if($get_sid == $real_sid) return true; 
	else return false; 

} 

############ 로그인 쿠키체크 ####################
if(isset($_COOKIE['v_my_id'])) {

	$v_my_id		= base64_decode($_COOKIE['v_my_id']); 	
	if(!checkVLogin(CONF_KEY))  Error("올바른 경로가 아닙니다.");	
		
	$sql					= "SELECT comp_name, cont_email, delivery_type FROM mallRN_vendor WHERE id = '{$v_my_id}'";
	$data					= $mysql->one_row($sql);
	$v_my_name				= stripslashes($data['comp_name']);
	$v_my_email				= $data['cont_email'];
	$v_my_delivery_type		= $data['delivery_type'];
	$my_level				= 0;
	$my_id					= "";
		
} 
else {
	$v_my_id = $v_my_name = $v_my_email = $v_my_delivery_type = "";
}
############ 로그인 쿠키체크 ####################

?>