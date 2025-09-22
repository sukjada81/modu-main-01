<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_db_error_log';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 's_date', 'e_date', 'status', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword')	$value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else				$value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page	= "db_error_log.php?{$addstring}";

switch($mode) {

	case "status" :		
		
		$item		= checkPostVar('item');
		$value		= checkPostVar('value');
		if($value == 2) $value = 0;
		else			$value = 1;		
		if(!$item || strlen($value) == 0)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');
		
		$sql = "UPDATE mallRN_db_error_log SET status = '{$value}' WHERE uid IN (".join(",",$item).")";		
		$mysql->query($sql);
		$cnt = number_format($mysql->affected_rows());

		alertMsg("{$cnt}개의 처리상태가 변경 되었습니다!", $link_page);

	break;
    
	case "delete" :
		
		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql = "DELETE FROM {$table_name} WHERE uid = '{$uid}'";
			$mysql->query($sql);
		}
		iframeViewMsg("{$i}건의 디비오류 로그가 삭제 되었습니다!");

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
