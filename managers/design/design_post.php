<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$mode = isset($_GET['mode']) ? $_GET['mode'] : $_POST['mode'];

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

switch($mode) {
    case "common" :

		$item_array = array('design_main_display1', 'design_main_display2', 'design_main_display3', 'design_main_category', 'design_main_category_info', 'design_main_custom_code', 'design_main_custom_code_image', 'design_main_custom_code_info', 'design_main_display_order', 'design_main2_display1', 'design_main2_display2', 'design_main2_display3', 'design_main2_display_order', 'design_icon_display', 'design_vendor_link');

		$item_able_value = array('design_main_display1' => ['0','1', '2','3'], 'design_main_display2' => ['0','1', '2','3'], 'design_main_display3' => ['0','1', '2','3'], 'design_main2_display1' => ['0','1', '2','3'], 'design_main2_display2' => ['0','1', '2','3'], 'design_main2_display3' => ['0','1', '2','3'], 'design_main_category' => ['0','1'], 'design_main_custom_code' => ['0','1'], 'design_vendor_link' => ['0','1']);

		if(isset($_POST['cate_goods_order'])) {
			$cate_goods_order				= explode(",",$_POST['cate_goods_order']);		
			$_POST['design_main_category_info']	= multiPostVar($cate_goods_order, array('goods_cate', 'goods_display','goods_used'));
		}
		else $_POST['design_main_category_info'];

		$_POST['design_main_custom_code_image']	= checkPostVar('detail_image_order');
		$_POST['design_main_display_order']		= checkPostVar('main_display_order');
		$_POST['design_main2_display_order']	= checkPostVar('main2_display_order');

		$design_icon1	= checkPostVar('design_icon1', 0);
		$design_icon2	= checkPostVar('design_icon2', 0);
		$design_icon3	= checkPostVar('design_icon3', 0);

		$_POST['design_icon_display'] = "{$design_icon1}|{$design_icon2}|{$design_icon3}";
	
		$sql = "UPDATE mallRN_configuration SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_able_value)) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else $_POST[$v] = checkPostVar($v);

			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid='1'";
		$mysql->query($sql);
		logMsg("디자인이 설정 되었습니다.","success");

	break;
	
	case "skin" :    
		
		$skin		= checkPostVar('item');
		$skin_ing	= checkPostVar('itemIng');
		$skin_ingX	= checkPostVar('itemIngX');
		$msg1 = $msg2 = "";

		if(isset($skin[0])) {
			$sql = "UPDATE mallRN_configuration SET design_skin = '{$skin[0]}' WHERE uid='1'";
			$mysql->query($sql);
			$msg1 = "사용스킨";
		}

		if($skin_ingX == 1) {
			$sql = "UPDATE mallRN_configuration SET design_skin_ing = '' WHERE uid='1'";
			$mysql->query($sql);
			if($msg1) $msg2 = ", 작업스킨";
			else $msg2 = "작업스킨";
		}

		if(isset($skin_ing[0])) {
			$sql = "UPDATE mallRN_configuration SET design_skin_ing = '{$skin_ing[0]}' WHERE uid='1'";
			$mysql->query($sql);
			if($msg1) $msg2 = ", 작업스킨";
			else $msg2 = "작업스킨";
		}	
		
		if($msg1 || $msg2) alertMsg("{$msg1} {$msg2}이 변경 되었습니다.", "skin.php");

    break;	

	case "top_menu" :
		
		if(isset($_POST['menu_order'])) {
			$menu_order			= explode(",", $_POST['menu_order']);	
			$design_top_menu	= multiPostVar($menu_order, array('menu_name','menu_url','menu_used'));
		}
		else $design_top_menu = "";

		$sql = "UPDATE mallRN_configuration SET design_top_menu = '{$design_top_menu}' WHERE uid='1'";
		$mysql->query($sql);

		logMsg("상단메뉴가 저장 되었습니다.","success");

	break;

	case "mail_common" :
		
		$content	= checkPostVar('content_code');
		$signdate	= time();

		$sql = "SELECT count(*) FROM mallRN_auto_mail WHERE type = 'common'";
		if($mysql->get_one($sql) == 0) {
			$sql = "INSERT INTO mallRN_auto_mail SET 
						type		= 'common',
						content		= '{$content}',
						signdate	= '{$signdate}'
					";			
		}
		else {
			$sql = "UPDATE mallRN_auto_mail SET 						
						content		= '{$content}',
						signdate	= '{$signdate}'
					WHERE type = 'common'
					";			
		}
		$mysql->query($sql);

		logMsg("공통디자인이 저장 되었습니다.","success");

	break;

	case "mail_member" :
		
		$send		= checkPostVar('send1');
		$content	= checkPostVar('content_code1');
		$signdate	= time();

		$sql = "SELECT count(*) FROM mallRN_auto_mail WHERE type = 'join'";
		if($mysql->get_one($sql) == 0) {
			$sql = "INSERT INTO mallRN_auto_mail SET 
						type		= 'join',
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					";			
		}
		else {
			$sql = "UPDATE mallRN_auto_mail SET
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					WHERE type = 'join'
					";			
		}
		$mysql->query($sql);

		$send		= 1;
		$content	= checkPostVar('content_code2');

		$sql = "SELECT count(*) FROM mallRN_auto_mail WHERE type = 'passwd'";
		if($mysql->get_one($sql) == 0) {
			$sql = "INSERT INTO mallRN_auto_mail SET 
						type		= 'passwd',
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					";			
		}
		else {
			$sql = "UPDATE mallRN_auto_mail SET
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					WHERE type = 'passwd'
					";			
		}
		$mysql->query($sql);

		$send		= checkPostVar('send3');
		$content	= checkPostVar('content_code3');		

		$sql = "SELECT count(*) FROM mallRN_auto_mail WHERE type = 'vjoin'";
		if($mysql->get_one($sql) == 0) {
			$sql = "INSERT INTO mallRN_auto_mail SET 
						type		= 'vjoin',
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					";			
		}
		else {
			$sql = "UPDATE mallRN_auto_mail SET
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					WHERE type = 'vjoin'
					";			
		}
		$mysql->query($sql);

		$send		= checkPostVar('send4');
		$content	= checkPostVar('content_code4');		

		$sql = "SELECT count(*) FROM mallRN_auto_mail WHERE type = 'sleep'";
		if($mysql->get_one($sql) == 0) {
			$sql = "INSERT INTO mallRN_auto_mail SET 
						type		= 'sleep',
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					";			
		}
		else {
			$sql = "UPDATE mallRN_auto_mail SET
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					WHERE type = 'sleep'
					";			
		}
		$mysql->query($sql);

		logMsg("회원관련 설정이 저장 되었습니다.","success");

	break;

	case "mail_order" :
		
		$send		= checkPostVar('send1');
		$content	= checkPostVar('content_code1');
		$signdate	= time();

		$sql = "SELECT count(*) FROM mallRN_auto_mail WHERE type = 'order'";
		if($mysql->get_one($sql) == 0) {
			$sql = "INSERT INTO mallRN_auto_mail SET 
						type		= 'order',
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					";			
		}
		else {
			$sql = "UPDATE mallRN_auto_mail SET
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					WHERE type = 'order'
					";			
		}
		$mysql->query($sql);

		$send		= checkPostVar('send2');
		$content	= checkPostVar('content_code2');

		$sql = "SELECT count(*) FROM mallRN_auto_mail WHERE type = 'delivery'";
		if($mysql->get_one($sql) == 0) {
			$sql = "INSERT INTO mallRN_auto_mail SET 
						type		= 'delivery',
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					";			
		}
		else {
			$sql = "UPDATE mallRN_auto_mail SET
						send		= '{$send}',
						content		= '{$content}',
						signdate	= '{$signdate}'
					WHERE type = 'delivery'
					";			
		}
		$mysql->query($sql);

		logMsg("주문관련 설정이 저장 되었습니다.","success");

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
