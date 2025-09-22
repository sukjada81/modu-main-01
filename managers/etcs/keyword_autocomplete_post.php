<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_keyword_autocomplete';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'type', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword')	$value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else				$value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page	= "keyword_autocomplete_list.php?{$addstring}";
$item_array = array('keyword', 'split_keyword', 'split_keyword_ek', 'type', 'signdate');

switch($mode) {
    case "write" :  
		
		$_POST['keyword']			= checkPostVar('keyword');
		if(!$_POST['keyword']) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");

		$_POST['split_keyword']		= getJamoStr($_POST['keyword']);
		$_POST['split_keyword_ek']	= ekKeyTypeConvert($_POST['keyword']);
		$_POST['signdate']			= time();
				
		######################## 자동완성 검색어 등록  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);
			if($k == count($item_array)-1)	$sql .= " {$v} = '{$_POST[$v]}'";
			else							$sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);
		$uid = $mysql->InsertNo();
		######################## 자동완성 검색어 등록  #########################

		iframeViewMsg("자동완성 검색어가 등록 되었습니다.");

    break;	

	case "delete" :
		
		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql = "DELETE FROM {$table_name} WHERE uid = '{$uid}'";
			$mysql->query($sql);
		}
		iframeViewMsg("{$i}건의 자동완성 검색어가 삭제 되었습니다!");

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
