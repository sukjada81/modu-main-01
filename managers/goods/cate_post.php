<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

define('CATEGORY_FOLDER', '../../image/category');

$mysql->msgType(1);

$mode	= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

switch($mode) {
    case "write" :  
		
		if(!isset($_POST['cate_dep'])) logMsg("분류정보가 제대로 넘어오지 못했습니다.");

		$_POST['cate_parent']	= checkPostVar('cate', 0);
		$_POST['cate_sub']		= "0";
		$_POST['access_level']	= isset($_POST['access_level']) ? ','.join(",", $_POST['access_level']).',' : '';

		if($_POST['cate_parent']) $_POST['cate_dep'] = $_POST['cate_dep'] + 1;
		
		switch($_POST['cate_dep']) {
			case "1" : 
				$where = ""; 				
				$def_num = 1000000000;				
			break;
			case "2" : 
				$where = "&& SUBSTRING(cate,1,3) = '".substr($_POST['cate_parent'],0,3)."' "; 
				$def_num = 1000000;
			break;
			case "3" : 
				$where = "&& SUBSTRING(cate,1,6) = '".substr($_POST['cate_parent'],0,6)."' "; 
				$def_num = 1000;
			break;
			case "4" : 
				$where = "&& SUBSTRING(cate,1,9) = '".substr($_POST['cate_parent'],0,9)."' "; 
				$def_num = 1;
			break;
		}
		
		$sql = "SELECT MAX(cate) as cate, MAX(sequence) as sequence FROM mallRN_cate WHERE cate_dep = '{$_POST['cate_dep']}' {$where}";
		$row = $mysql->one_row($sql);		

		if(!$row['cate']) {
			if($_POST['cate_dep'] == 1) $_POST['cate'] = "100000000000";
			else $_POST['cate'] = $_POST['cate_parent'] + $def_num;
		}
		else $_POST['cate'] = $row['cate'] + $def_num;
		if(!isset($row['sequence'])) $_POST['sequence'] = 1;
		else $_POST['sequence'] = $row['sequence'] + 1;	
		
		for($i=1;$i<4;$i++) {
			if(!preg_match("/none/i",$_FILES["image".$i]['tmp_name']) && $_FILES["image".$i]['tmp_name']) {
				$up_file = upFile($_FILES["image".$i]['tmp_name'],$_FILES["image".$i]['name'],CATEGORY_FOLDER, 1, $_POST['cate'].'_'.$i, 1);
				$_POST['image'.$i] = $up_file;				
			}			
			else $_POST['image'.$i] = '';
		}

		$item_array			= array('cate', 'cate_name', 'cate_dep', 'cate_parent', 'cate_sub', 'used', 'sequence', 'access_type', 'access_level', 'image1', 'image2', 'image3');
		$item_default		= array('cate_sub', 'used');
		$item_able_value	= array('access_type' => ['0', '1', '2']);		

		$sql = "INSERT INTO mallRN_cate SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_able_value)) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);

		if($_POST['cate_dep'] > 1) {
			$sql = "SELECT cate_sub, cate_name FROM mallRN_cate WHERE cate = '{$_POST['cate_parent']}'";
			$row = $mysql->one_row($sql);
			if($row['cate_sub'] != '1') {
				$sql = "UPDATE mallRN_cate SET cate_sub = '1' WHERE cate = '{$_POST['cate_parent']}'";
				$mysql->query($sql);
				echo "<script>parent.cateMod('{$_POST['cate_parent']}','1','{$row['cate_name']}');</script>";
			}
		}

		echo "<script>parent.cateAdd('{$_POST['cate_dep']}','{$_POST['cate']}','{$_POST['cate_name']}');</script>";

		logMsg("{$_POST['cate_dep']}차 분류가 등록 되었습니다.","success");

    break;	

	case "modify" :
		
		$cate = checkPostVar('cate');
		if(!$cate) logMsg("분류정보가 제대로 넘어오지 못했습니다.");

		$_POST['access_level']	= isset($_POST['access_level']) ? ','.join(",", $_POST['access_level']).',' : '';

		$sql = "SELECT * FROM mallRN_cate WHERE cate='{$cate}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 분류가 존재하지 않거나 삭제 되었습니다.");

		$item_array			= array('cate_name', 'used', 'access_type', 'access_level');
		$item_default		= array('used');
		$item_able_value	= array('access_type' => ['0', '1', '2']);
		
		$sql = "UPDATE mallRN_cate SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_able_value)) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);
			
			if($k==0) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= ", {$v} = '{$_POST[$v]}'";
		}

		for($i=1;$i<4;$i++) {
			if(!preg_match("/none/i",$_FILES["image".$i]['tmp_name']) && $_FILES["image".$i]['tmp_name']) {
				$up_file = upFile($_FILES["image".$i]['tmp_name'],$_FILES["image".$i]['name'],CATEGORY_FOLDER, 1, $cate.'_'.$i, 1);
				$sql .= ", image{$i} = '{$up_file}'";
			}			
			else {
				if($_POST['img_del'.$i]=='1') {
					@unlink(CATEGORY_FOLDER.'/'.$row['image'.$i]);
					$sql .= ", image{$i} = ''";
				}
			}			
		}

		$sql .= "WHERE cate='{$cate}'";
		$mysql->query($sql);

		######################## 하위분류 접근권한 뱐경 #########################
		if($_POST['access_type'] != $row['access_type']) {

			for($i = 3; $i < 10; $i = $i + 3) {
				if(substr($cate, $i, ($i + 3)) == '000') break;
			}

			$sql = "UPDATE mallRN_cate SET access_type = '{$_POST['access_type']}' WHERE SUBSTRING(cate, 1, {$i}) = '".substr($cate, 0, $i)."'";
			$mysql->query($sql);
			
		}
		######################## 하위분류 접근권한 뱐경 #########################

		
		######################## 하위분류 사용상태 변경 및 상품 노출상태 뱐경 #########################
		if($_POST['used'] != $row['used']) {

			for($i = 3; $i < 10; $i = $i + 3) {
				if(substr($cate, $i, ($i + 3)) == '000') break;
			}

			if($_POST['used']=='0') $cate_hide = 1;
			else $cate_hide = 0;

			$sql = "UPDATE mallRN_cate SET used = '{$_POST['used']}' WHERE SUBSTRING(cate, 1, {$i}) = '".substr($cate, 0, $i)."'";
			$mysql->query($sql);

			$sql = "UPDATE mallRN_goods SET cate_hide = '{$cate_hide}' WHERE SUBSTRING(cate, 1, {$i}) = '".substr($cate, 0, $i)."'";
			$mysql->query($sql);				
		}
		######################## 하위분류 사용상태 변경 및 상품 노출상태 뱐경 #########################

		echo "<script>parent.cateMod('{$cate}','{$row['cate_sub']}','{$_POST['cate_name']}');</script>";

		logMsg("{$_POST['cate_name']} 분류가 수정 되었습니다.","success");

	break;

	case "delete" :

		$cate = checkPostVar('cate');
		if(!$cate) logMsg("분류정보가 제대로 넘어오지 못했습니다.");

		$sql = "SELECT * FROM mallRN_cate WHERE cate='{$cate}'";
		$row = $mysql->one_row($sql);

		if(!$row) logMsg("해당 분류가 존재하지 않거나 삭제 되었습니다.");

		$where = " && SUBSTRING(cate,1,".($row['cate_dep']*3).")='".substr($cate,0,($row['cate_dep']*3))."'";
		
		$sql = "SELECT * FROM mallRN_cate WHERE uid>0 {$where}";
		$mysql->query($sql);

		while($row2=$mysql->fetch_array()){
			for($i=1;$i<4;$i++) {
				@unlink(CATEGORY_FOLDER.'/'.$row2['image'.$i]);
			}
			
			$sql = "DELETE FROM mallRN_cate WHERE cate='{$row2['cate']}'";
			$mysql->query2($sql);
			
			//상품 분류 미선택으로 변경
		}
		
		echo "<script>parent.cateDel('{$cate}','{$row['cate_parent']}');</script>";
	
		######################## 하위분류 삭제처리 #########################
		if($row['cate_dep']>1) {
			$sql = "SELECT cate_sub FROM mallRN_cate WHERE cate='{$row['cate_parent']}'";
			if($mysql->get_one($sql)=='1') {
				$sql = "SELECT count(*) FROM mallRN_cate WHERE cate_parent='{$row['cate_parent']}'";
				if($mysql->get_one($sql)==0) {
					$sql = "UPDATE mallRN_cate SET cate_sub = '0' WHERE cate = '{$row['cate_parent']}'";
					$mysql->query($sql);					
					echo "<script>parent.cateMod2('{$row['cate_parent']}','0');</script>";	
				}
			}
		}
		######################## 하위분류 삭제처리 #########################

		logMsg("{$row['cate_name']} 분류가 삭제 되었습니다.","success");

	break;

	case "match" :

		$sql = "SELECT * FROM mallRN_cate WHERE cate_sub = '0' ORDER BY cate ASC";
		$mysql->query($sql);
		
		while($row = $mysql->fetch_array()){	
			$matching = checkPostVar('cate_'.$row['cate']);
			
			$sql = "UPDATE mallRN_cate SET matching = '{$matching}' WHERE cate='{$row['cate']}'";
			$mysql->query2($sql);	
		}
		
		iframeViewMsg("분류코드 매칭 정보가 저장 되었습니다!");

	break;


	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
