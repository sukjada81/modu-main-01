<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

define('CATEGORY_FOLDER', '../../image/category');

$mysql->msgType(1);

$mode	= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

switch($mode) {    

	case "match" :

		$sql = "SELECT * FROM mallRN_cate WHERE cate_sub = '0' ORDER BY cate ASC";
		$mysql->query($sql);
		
		while($row = $mysql->fetch_array()){	
			$matching = checkPostVar('cate_'.$row['cate']);
			
			$sql = "SELECT count(*) FROM mallRN_cate_matching WHERE vendor = '{$v_my_id}' && cate = '{$row['cate']}'";
			if($mysql->get_one($sql) == 0) {
				$sql = "INSERT INTO mallRN_cate_matching SET vendor = '{$v_my_id}', matching = '{$matching}', cate='{$row['cate']}'";
			}
			else {
				$sql = "UPDATE mallRN_cate_matching SET matching = '{$matching}' WHERE vendor = '{$v_my_id}' && cate='{$row['cate']}'";
			}
			$mysql->query2($sql);	
		}
		
		iframeViewMsg("분류코드 매칭 정보가 저장 되었습니다!");

	break;


	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
