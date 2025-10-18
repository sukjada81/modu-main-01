<?php

ob_start();

header("Content-Type: text/html; charset=utf-8");

if(isset($_GET['channel'])) {
	if($_GET['channel'] == 'order' || $_GET['channel'] == 'cart') {
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}
}


define('DEFAULT_PATH',	'');

include_once('php/init.php');

define('BANNER_FOLDER', "image/{$mobile_header}banner/");

include_once(PATH_LIB.'/class.Template.php'); 

$skin = "skin/{$shop_config['design_skin']}";

$skin_ing = isset($_GET['skin_ing']) ? $_GET['skin_ing'] : ((isset($_COOKIE['skin_ing'])) ? $_COOKIE['skin_ing'] : '');
if($skin_ing == 'Y') {
	$skin = "skin/{$shop_config['design_skin_ing']}";
	SetCookie("skin_ing", $skin_ing, 0, "/");
}

if($reset == 1) {
	######################## 리스트 항목만 출력시 정의 (JOSON)  #############################
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");

	if(!file_exists("{$skin}/info/skin_define.php")) {
		echo json_encode(array('error' => "스킨파일이 존재하지 않습니다."));
		exit;
	}
	include_once("{$skin}/info/skin_define.php");

	 if($channel == "list") {
		if(!$cate = checkGetVar('cate')) {
			echo json_encode(array('error' => "필수 정보가 제대로 넘어오지 못했습니다."));
			exit;
		}
	}
	if($channel == 'search') {
		$keyword = isset($_POST['keyword']) ?  urldecode(trim($_POST['keyword'])) : ((isset($_GET['keyword'])) ?  urldecode(trim($_GET['keyword'])) : '');
		if(strlen($keyword) == 0) {
			echo json_encode(array('error' => "검색어를 입력 하시기 바랍니다."));
			exit;
		}
	}

	$mysql->msgType(2);

	$my_array = array();
	######################## 리스트 항목만 출력시 정의 (JOSON)  #############################
}
else {
	if(!file_exists("{$skin}/info/skin_define.php")) Error("스킨파일이 존재하지 않습니다.");
	include_once("{$skin}/info/skin_define.php");

	if(preg_match("/\//i", $channel)) {
		$tmps = explode("/",$channel);
		$channel		= $tmps[0];
		$_GET['uid']	= $tmps[1];
		$_GET['cate']	= $tmps[2];
		unset($tmps);
	}

	$site_title = "";
	if($channel == "view") {
		$uid	= checkGetVar('uid');
		if(!$uid || !is_numeric($uid)) Error('필수 정보가 제대로 넘어오지 못했습니다.');
		$sql = "SELECT name FROM mallRN_goods WHERE uid='{$uid}'";
		$site_title = html2txt(stripslashes($mysql->get_one($sql)))." - ";
	}
	else if($channel == "list") {
		if(!$cate = checkGetVar('cate')) Error('필수 정보가 제대로 넘어오지 못했습니다.');	
		$site_title = html2txt(getCateAllName($cate))." - ";
	}

	################################# Header ###################################
	$tpl = new classTemplate;
	$tpl->define("main","header.html");
	$tpl->scan_area("main");

	if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/rv/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/Trident/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT'])) {
		$tpl->parse("is_ie");		
	}
	else $tpl->parse("is_default");

	$site_name			= stripslashes($shop_config['basic_name']);
	$site_title			= stripslashes($shop_config['basic_title']);
	$site_keyword		= stripslashes($shop_config['basic_keyword']);
	$site_url			= stripslashes($shop_config['basic_url']);
	$site_description	= stripslashes($shop_config['basic_description']);	
	$site_image			= $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."image/common/".stripslashes($shop_config['basic_image']);

	switch($channel) {
		case "view" :
			$sql				= "SELECT name, detail, keyword FROM mallRN_goods WHERE uid='{$uid}'";
			$data				= $mysql->one_row($sql);
			$site_title			= html2txt(stripslashes($data['name']))." - ".stripslashes($shop_config['basic_title']);
			$site_description	= html2txt(stripslashes($data['name']));
			if($data['detail'])  $site_description .= " ".html2txt(stripslashes($data['detail']));
			if($data['keyword']) $site_description .= " ".substr(stripslashes($data['keyword']), 1, -1);
		break;
		case "list" :
			if(!$cate = checkGetVar('cate')) Error('필수 정보가 제대로 넘어오지 못했습니다.');	
			$site_title			= html2txt(getCateAllName($cate))." - ".stripslashes($shop_config['basic_title']);
			$site_description	= html2txt(getCateAllName($cate))." 상품리스트";
		break;		
		case "cs_center" :
			$site_title			= stripslashes($shop_config['basic_title'])." : 고객센터";
			$site_description	= "고객센터 정보, FAQ, 자주 찾는 질문, 1:1문의, 공지사항";
		break;
		case "cs_board" :
			switch(checkGetVar('b_id')) {
				case "faq" :
					$site_title			= stripslashes($shop_config['basic_title'])." : 자주 찾는 질문";
					$site_description	= "자주 찾는 질문, FAQ, 게시판";
				break;
				case "notice" :
					$site_title			= stripslashes($shop_config['basic_title'])." : 공지사항";
					$site_description	= "공지사항, 게시판, 업데이트, 패치";
				break;
			}
		break;
	}

	if($naver_tag = stripslashes($shop_config['script_naver_tag'])) {
		$tpl->parse("is_naver_tag");
	}

	if($google_analytics = stripslashes($shop_config['script_google_analytics'])) {
		$tpl->parse("is_google_analytics");
	}

	if($shop_config['mobile_icon'])  {
		$mobile_icon = $HeaderProtocol.$_SERVER['HTTP_HOST']."/".CONF_ROOT."image/mobile/".stripslashes($shop_config['mobile_icon']);
		$tpl->parse("is_mobile_icon");
	}
	if($mobile_header == "mobile_") $tpl->parse("is_mobile_header");

	if($shop_config['naverpay_used']) {
		if($shop_config['naverpay_mode'] == 0) {
			if($my_id != $shop_config['naverpay_test_id']) $shop_config['naverpay_used'] = 0;
		}
	}

	if($shop_config['naverpay_used']) {
		$naverpay_key3		= $shop_config['naverpay_key3'];
		$http_host_name		= str_replace("www.", "", $_SERVER['HTTP_HOST']);
		$tpl->parse("is_naver_pay");
	}

	$tpl->parse("main");
	$tpl->tprint("main");
	$tpl->close();
	################################# Header ###################################

	include_once('php/top.php');
}

$channel_group													= "";
if(in_array($channel, $channel_mypage)) $channel_group			= array("mypage",		"MY SHOP");
else if(in_array($channel, $channel_cs_center)) $channel_group	= array("cs_center",	"고객센터");

if($channel_group) include_once('php/sub_menu.php');

$tpl		= new classTemplate;

$skin_channel = $channel;
if($channel == "my_mileage") {
	$type = checkGetVar("type");
	if($type == 2) $skin_channel = $channel."2";
	else if($type == 3) $skin_channel = $channel."3";
}
else if($channel	== "order_list_guest") {
	$skin_channel = "order_list";
}

$tpl->define("main","{$skin}/{$mobile_header}{$skin_channel}.html");
$tpl->scan_area("main");

switch ($channel) {

	case "list" : case "search" : case "best" : case "view" : case "new" : case "group" : case "store" : case "store_cate" :
	case "regist" : case "regist_ok" : case "regist_vendor" : case "regist_vendor_ok" : case "login" : case "passwd_search" : case "login_guest" :
	case "agreement" : case "privacy" :	
	case "cs_board" : case "cs_center" :
	case "exhibition_list" : case "exhibition" : case "add_page" :
	case "mypage" : case "my_recent_goods" : case "my_favorite_goods" : case "my_favorite_store" : case "my_counsel" : case "member_withdrawal" : case "my_inquiry" : case "my_review" :
	case "my_coupon" : case "my_mileage" : 
	case "view_inquiry" : case "view_review" : case "review" :
	case "cart" : case "order" : case "order_ok" : case "order_list" : case "order_list_guest" : case "order_detail" : 	
	case "store_list" :	
		include_once("php/{$channel}.php");
	break;
	
	case "member_modify" :		
		include_once("php/regist.php");
	break;

	case "id_search" : case "member_passwd" : case "member_withdrawal_ok" : case "member_sleep" :
	break;

	default : 
		include_once("php/main.php");

}

if($reset == 0) commonBannerCheck($channel);

if($channel != 'order') {
	$tpl->parse("main");
	$tpl->tprint("main");
	$tpl->close();
}

if($reset == 0) {
	include_once('php/bottom.php');
	include_once('php/counter.php');

	if($channel == 'store' || $channel == 'store_cate') include_once('php/store_counter.php');
}

if($shop_config['naverpay_used']) {
	echo "\n<script>wcs_do();</script>\n\n";
}

if($shop_config['script_bottom_code']) {
	echo "\n".add_escape_re_string(stripslashes($shop_config['script_bottom_code']))."\n\n";
}

?>

</body>
</html>