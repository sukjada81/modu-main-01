<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

define('UPLOAD_FOLDER', '../../image/mobile');

$mysql->msgType(1);

$mode = isset($_GET['mode']) ? $_GET['mode'] : $_POST['mode'];

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

switch($mode) {
    case "common" :
		$mobile_yn	= checkPostVar('mobile_yn', 'Y', ['N', 'Y']);

		######################## 아이콘 등록  #########################
		if(!preg_match("/none/i",$_FILES['image1']['tmp_name']) && $_FILES['image1']['tmp_name']) {									
			$image1 = upFile($_FILES['image1']['tmp_name'], $_FILES['image1']['name'], UPLOAD_FOLDER, 1, "mobile_icon", 1);
			$image1 = ", mobile_icon = '{$image1}'";
		}
		else {
			$image1 = "";
		}
		######################## 아이콘 등록  #########################

		$sql = "UPDATE mallRN_configuration SET mobile_yn = '{$mobile_yn}' {$image1} WHERE uid = '1'";
		$mysql->query($sql);

		logMsg("모바일샵 설정의 저장 되었습니다.","success");

	break;
	
	case "top_menu" :
		
		if(isset($_POST['menu_order'])) {
			$menu_order			= explode(",", $_POST['menu_order']);	
			$design_top_menu	= multiPostVar($menu_order, array('menu_name','menu_url','menu_used'));
		}
		else $design_top_menu = "";

		$sql = "UPDATE mallRN_configuration SET mobile_top_menu = '{$design_top_menu}' WHERE uid='1'";
		$mysql->query($sql);

		logMsg("상단메뉴가 저장 되었습니다.","success");

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
