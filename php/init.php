<?php

@error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));
mysqli_report(MYSQLI_REPORT_OFF);  // 8.2 over

ini_set("session.use_trans_sid",	0);
ini_set("url_rewriter.tags",		"");

define('_B2BMALL_',				'1');
define('PATH_LIB',				'lib');
define('PATH_INCLUDE',			'include');
define('PATH_PHPMAILER',		'plugin/PHPMailer');
define('PATH_COOLSMS',			'plugin/coolSMS');
define('PAGING_TYPE',			'1'); //0 : paging, 1 : pageline

include_once(DEFAULT_PATH.PATH_LIB.'/lib.Function.php');   
include_once(DEFAULT_PATH.PATH_LIB.'/lib.Shop.php');   
include_once(DEFAULT_PATH.PATH_INCLUDE.'/config.php');   
include_once(DEFAULT_PATH.PATH_INCLUDE.'/dbconfig.php');   
include_once(DEFAULT_PATH.PATH_LIB.'/class.Mysql.php');   

if(isset($_SERVER['HTTP_USER_AGENT'])) {
	$is_mobile = preg_match('/'.MOBILE_AGENT.'/i', $_SERVER['HTTP_USER_AGENT']);
}
else $is_mobile = 0;

if($is_mobile == 1) $mobile_header = "mobile_";
else				$mobile_header = "";

$mysql = new mysqlClass(); 

include_once(DEFAULT_PATH.PATH_LIB.'/checkLogin.php');

if(substr($_SERVER['HTTP_HOST'], -1) == '.') {
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

if(!function_exists('userAbortFunc')){
	//메모리제거
	function userAbortFunc() {
		global $mysql, $listPaging, $tpl;
		if(is_object($mysql)) $mysql->close();
		if(is_object($tpl)) $tpl->close();
		if(is_object($listPaging)) $listPaging->close();		
	}
}

@ignore_user_abort(true); 
@register_shutdown_function('userAbortFunc');

//==============================================================================
// SQL Injection 방어
//------------------------------------------------------------------------------
// magic_quotes_gpc 에 의한 backslashes 제거
if (7.4 > (float)phpversion()) {
	if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
		$_POST    = array_map_deep('stripslashes',  $_POST);
		$_GET     = array_map_deep('stripslashes',  $_GET);
		$_COOKIE  = array_map_deep('stripslashes',  $_COOKIE);
		$_REQUEST = array_map_deep('stripslashes',  $_REQUEST);
	}
}

// sql_escape_string 적용
$_POST    = array_map_deep('add_escape_string',  $_POST);
$_GET     = array_map_deep('add_escape_string',  $_GET);
$_COOKIE  = array_map_deep('add_escape_string',  $_COOKIE);
$_REQUEST = array_map_deep('add_escape_string',  $_REQUEST);
//==============================================================================

$Main					= "index.php";
$SERVER_NAME			= $_SERVER["SERVER_NAME"];
$cart_id				= getCartId($my_id);
$channel				= add_escape_re_string(checkGetVar('channel'));
if(preg_match("/view\//i", $channel)) {
	$tmps = explode("/", $channel);
	$channel		= $tmps[0];
	$_GET['uid']	= $tmps[1];
}
if(!$channel) $channel	= "main";
$ver					= SHOP_VERSION;
$t						= time();
$reset					= isset($_POST['reset']) ? isset($_POST['reset']) : isset($_GET['reset']);
$HeaderProtocol			= isset($_SERVER['HTTPS']) ? "https://" : "http://";

define('ABSOLUTE_PATH_SHOP',	$HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT);

$sql = "SELECT * FROM mallRN_configuration WHERE uid=1";
$shop_config = $mysql->one_row($sql);

$channel_mileage = array('view', 'cart', 'order');
if(in_array($channel, $channel_mileage)) {
	$sql									= "SELECT member_mileage_order FROM mallRN_configuration WHERE uid = 2";		
	$shop_config['member_mileage_order']	= $mysql->get_one($sql); 
}

if($shop_config['mobile_yn'] == 'N') $mobile_header = "";

$empty_array = array('goods_option_info','goods_brand_info','goods_make_info','goods_origin_info','goods_require_info','goods_icon_info');
foreach($empty_array as $k => $v) {
	$shop_config[$v] = '';
}
unset($empty_array, $k, $v);

if(DEFAULT_PATH == "") {
	$channel_mypage		= array ('mypage', 'order_list', 'cancel_list', 'my_mileage', 'my_coupon', 'my_favorite_goods', 'my_favorite_store', 'my_recent_goods', 'my_counsel', 'my_review', 'my_inquiry', 'member_modify', 'member_modify', 'member_passwd', 'member_withdrawal');
	$channel_cs_center	= array('cs_center', 'cs_board');
	$member_conf_array	= array('login', 'agreement', 'privacy', 'regist', 'regist_ok', 'regist_vendor', 'regist_vendor_ok', 'passwd_search', 'member_modify');

	if(!$my_id) {	
		if(in_array($channel, $channel_mypage)) {
			$channel_orig	= $channel;
			$channel		= "login";		
		}
	}

	if(in_array($channel, $member_conf_array)) {
		$sql			= "SELECT * FROM mallRN_configuration WHERE uid = 2";
		$member_config	= $mysql->one_row($sql);
	}	
}

######################## 상품 쿠폰 / 이밴트 할인  설정 #############################
$channel_goods_list = array('main', 'list', 'search', 'best', 'new', 'group', 'view', 'cart', 'order', 'exhibition', 'store', 'store_cate', 'my_favorite_store', 'store_list');
if(in_array($channel, $channel_goods_list)) {

	$default_goods_field = array('uid', 'image2', 'name', 'name_code_able', 'icon', 'price', 'orig_price', 'consumer_price', 'price_ment', 'cate', 'make', 'view_cnt', 'order_cnt', 'detail', 'exhibition', 'sale_use', 'option_use', 'qty_type', 'qty', 'option_soldout', 'moddate');
	
	$sql = "SELECT uid, goods_order FROM mallRN_coupon_manager WHERE type = '4'";
	$mysql->query($sql);
	
	$coupon_goods_array = array();
	$coupon_uid_array	= array();
	
	while($row = $mysql->fetch_array()){
		$coupon_goods_array[]	= $row['goods_order'];
		$coupon_uid_array		= $row['uid'];
	}

	$goods_price_limit1 = $shop_config['goods_price_limit1'];
	$goods_price_limit2 = $shop_config['goods_price_limit2'];

	$event_info_array	= array();

	$sql = "SELECT uid, discount FROM mallRN_exhibition WHERE status = '2' && discount_yn = 'Y' && discount > 0";
	$mysql->query($sql);

	while($row = $mysql->fetch_array()){
		$event_info_array[$row['uid']]	= $row['discount'];
	}
}
######################## 상품 쿠폰 / 이밴트 할인  설정 #############################

?>