<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$mode				= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$signdate			= time();
$_POST['signdate']	= time();

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

switch($mode) {
    case "basic" :    

		$item_array = array('basic_name','basic_cs_time1','basic_cs_time2','basic_cs_time3','basic_cs_time4','comp_rtn_postcode','comp_rtn_address1','comp_rtn_address2');
		
		$sql = "UPDATE mallRN_vendor_configuration SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);
			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE vendor = '{$v_my_id}'";
		$mysql->query($sql);
		logMsg("스토어 정보가 설정 되었습니다.","success");

    break;	

	case "common" :

		$item_array			= array('design_main_display1', 'design_main_display2', 'design_main_display3', 'design_main_custom_code', 'design_main_custom_code_image', 'design_main_custom_code_info', 'design_main_display_order');

		$item_able_value = array('design_main_display1' => ['0','1', '2','3'], 'design_main_display2' => ['0','1', '2','3'], 'design_main_display3' => ['0','1', '2','3'], 'design_main_custom_code' => ['0','1']);

		$_POST['design_main_custom_code_image']	= checkPostVar('detail_image_order');
		$_POST['design_main_display_order']		= checkPostVar('main_display_order');
		
		$sql = "UPDATE mallRN_vendor_configuration SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_able_value)) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else $_POST[$v] = checkPostVar($v);

			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE vendor = '{$v_my_id}'";
		$mysql->query($sql);
		logMsg("메인 디자인이 설정 되었습니다.","success");

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
