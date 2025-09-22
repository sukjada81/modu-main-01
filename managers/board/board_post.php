<?php 

header("Cache-Control: no-store");
header("Pragma: no-cache");

include_once('../common/ad_init.php');

define('BOARD_FOLDER',	'../../board/data');

$mysql->msgType(1);

$mode		= isset($_GET['mode']) ? add_escape_re_string($_GET['mode']) : add_escape_re_string($_POST['mode']);
$table_name	= 'mallRN_board_manager';

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('type', 'field' ,'keyword', 'sort', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$link_page	= "board_list.php?{$addstring}";

if($mode=='write' || $mode=='modify') {

	$_POST['id']					= checkPostVar('id');
	$_POST['name']					= checkPostVar('name');
	$_POST['access_list_level']		= isset($_POST['access_list_level'])	? ','.join(",", $_POST['access_list_level']).','	: '';
	$_POST['access_write_level']	= isset($_POST['access_write_level'])	? ','.join(",", $_POST['access_write_level']).','	: '';
	$_POST['access_view_level']		= isset($_POST['access_view_level'])	? ','.join(",", $_POST['access_view_level']).','	: '';
	$_POST['access_reply_level']	= isset($_POST['access_reply_level'])	? ','.join(",", $_POST['access_reply_level']).','	: '';
	$_POST['access_comment_level']	= isset($_POST['access_comment_level'])	? ','.join(",", $_POST['access_comment_level']).','	: '';
	
	if($_POST['cate_order']) {		
		$cate_order			= explode(",", $_POST['cate_order']);
		$cate_max_num		= $_POST['cate_max_num'];		
		$_POST['cate_info']	= $cate_max_num."|*|".multiPostVar($cate_order, array('cate_num','cate_name'));		
	}
	else $_POST['cate_info'] = "";
}

$item_array			= array('name', 'skin', 'record_num', 'types', 'start_page', 'view_type', 'secret_type', 'privacy_type', 'new_icon', 'upload_file', 'upload_size', 'links', 'cate_info', 'access_list', 'access_list_level', 'access_view', 'access_view_level', 'access_write', 'access_write_level', 'access_reply', 'access_reply_level', 'access_comment', 'access_comment_level', 'form_add1_title', 'form_add1', 'form_add2_title', 'form_add2', 'form_add3_title', 'form_add3', 'form_add4_title', 'form_add4', 'form_add5_title', 'form_add5', 'header', 'header_content', 'footer', 'footer_content');
$item_default		= array('types', 'view_type', 'privacy_type', 'upload_file', 'links ', 'header', 'footer');	
$item_able_value	= array('start_page' => ['0', '1', '2'], 'secret_type' => ['0', '1', '2'],'access_list' => ['0', '1', '2', '3', '4'], 'access_view' => ['0', '1', '2', '3', '4'], 'access_write' => ['0', '1', '2', '3', '4'], 'access_reply' => ['0', '1', '2', '3', '4'], 'access_comment' => ['0', '1', '2', '3', '4'], 'form_add1' => ['0', '1', '2'], 'form_add2' => ['0', '1', '2'], 'form_add3' => ['0', '1', '2'], 'form_add4' => ['0', '1', '2'], 'form_add5' => ['0', '1', '2']);	

switch($mode) {
    case "write" :  

		if(!$_POST['id'] || !$_POST['name']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT count(*) FROM {$table_name} WHERE id = '{$_POST['id']}'";
		if($mysql->get_one($sql) > 1) logMsg("{$_POST['id']}는 이미 등록된 아이디 입니다.");
		
		$_POST['signdate']	= time();
				
		array_push($item_array, 'id', 'signdate');

		######################## 게시판 등록  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);
		######################## 게시판 등록  #########################

		if(!$mysql->table_list("","mallRN_board_{$_POST['id']}")) {
			$sql = "
			CREATE TABLE mallRN_board_{$_POST['id']} ( 
				`uid` int unsigned NOT NULL AUTO_INCREMENT COMMENT '고유값',
				`notice` tinyint unsigned DEFAULT '1' NOT NULL COMMENT '공지사항 0(공지사항), 1(일반글)',
				`idx` tinyint unsigned DEFAULT '0' NOT NULL COMMENT '게시글 블럭',
				`main` smallint unsigned DEFAULT '65535' NOT NULL COMMENT '일반글 정렬용',
				`sub` smallint unsigned DEFAULT '0' NOT NULL COMMENT '답글 정렬용',
				`depth` tinyint unsigned DEFAULT '0' NOT NULL COMMENT '답글 깊이용',
				`id` varchar(50) NOT NULL default '' COMMENT '아이디',
				`name` varchar(50) NOT NULL default '' COMMENT '이름',		
				`subject` varchar(250) NOT NULL default '' COMMENT '제목',
				`cate` tinyint unsigned DEFAULT '0' NOT NULL COMMENT '분류번호',
				`content` text COMMENT '내용',
				`count` int unsigned NOT NULL default '0' COMMENT '카운터',
				`count_comment` int unsigned NOT NULL default '0' COMMENT '댓글카운터',
				`files` text COMMENT '업로드 파일 (파일명,파일명,...)',
				`links` text COMMENT '링크주소 (링크주소,링크주소,...)',
				`add1` varchar(250) NOT NULL default '' COMMENT '추가필드 #1',
				`add2` varchar(250) NOT NULL default '' COMMENT '추가필드 #2',
				`add3` varchar(250) NOT NULL default '' COMMENT '추가필드 #3',
				`add4` varchar(250) NOT NULL default '' COMMENT '추가필드 #4',
				`add5` varchar(250) NOT NULL default '' COMMENT '추가필드 #5',
				`secret` tinyint unsigned DEFAULT '0' NOT NULL COMMENT '비밀글 0(일반글), 1(비밀글)',
				`passwd` varchar(100) NOT NULL default '' COMMENT '비밀번호(MD5)',
				`goods_info` varchar(250) NOT NULL default '' COMMENT '상품정보 (상품UID|상품명|선택상품옵션)',
				`ip` varchar(20) NOT NULL default '' COMMENT '아이피',
				`o_id` varchar(50) NOT NULL default '' COMMENT '비밀글 확인용 원본 아이디',
				`o_uid` varchar(50) NOT NULL default '' COMMENT '답변글 확인용 원본 uid',
				`dels` tinyint unsigned DEFAULT '0' NOT NULL COMMENT '삭제글 여부',
				`signdate` int unsigned NOT NULL default '0' COMMENT '등록일시',
				PRIMARY KEY (uid), 
				INDEX POS (notice, idx, main, sub)
				) DEFAULT CHARSET=utf8 COMMENT='{$_POST['name']} 게시판'
			";
			$mysql->query($sql);		
		}
			
		if(!$mysql->table_list("","mallRN_board_{$_POST['id']}_comment")) {
			$sql = "
			CREATE TABLE mallRN_board_{$_POST['id']}_comment ( 
				`uid` int unsigned NOT NULL AUTO_INCREMENT COMMENT '고유값',
				`b_uid` int unsigned NOT NULL default '0' COMMENT '본문 고유값',
				`main` smallint unsigned DEFAULT '65535' NOT NULL COMMENT '일반댓글 정렬용',
				`sub` smallint unsigned DEFAULT '0' NOT NULL COMMENT '답댓글 정렬용',
				`depth` tinyint unsigned DEFAULT '0' NOT NULL COMMENT '답댓글 깊이용',
				`id` varchar(50) NOT NULL default '' COMMENT '아이디',
				`name` varchar(50) NOT NULL default '' COMMENT '이름',
				`content` text  COMMENT '내용',		
				`passwd` varchar(100) NOT NULL default '' COMMENT '비밀번호(MD5)',
				`ip` varchar(20) NOT NULL default '' COMMENT '아이피',
				`o_uid` varchar(50) NOT NULL default '' COMMENT '답변글 확인용 원본 uid',
				`dels` tinyint unsigned DEFAULT '0' NOT NULL COMMENT '삭제글 여부',
				`signdate` int unsigned NOT NULL default '0' COMMENT '등록일시',
				PRIMARY KEY (uid),
				INDEX POS (main, sub)
				) DEFAULT CHARSET=utf8 COMMENT='{$_POST['name']} 게시판 댓글'
			";
			$mysql->query($sql);		
		}

		mkdir(BOARD_FOLDER.'/'.$_POST['id'], 0707);	

		alertMsg("게시판이 등록 되었습니다.", $link_page);		

    break;	

	case "modify" :
		
		$uid = checkPostVar('uid');
		
		if(!$uid || !$_POST['name']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT * FROM {$table_name} WHERE uid = '{$uid}'";
		if(!$row = $mysql->one_row($sql)) logMsg("해당 게시판이 존재하지 않거나 삭제 되었습니다.");
		
		######################## 게시판 수정  #########################
		$sql = "UPDATE {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(isset($item_able_value[$v])) $_POST[$v] = checkPostVar($v, $item_able_value[$v][0], $item_able_value[$v]);
			else if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k==count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid = '{$uid}'";		
		$mysql->query($sql);
		######################## 모음전 수정  #########################

		alertMsg("게시판이 수정 되었습니다.", $link_page);			
		
	break;

	case "delete" :
		
		$item = checkPostVar('item');
		if(!$item)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid = $item[$i];

			$sql	= "SELECT id FROM {$table_name} WHERE uid = '{$uid}'";
			$b_id	= $mysql->get_one($sql);

			if(!$b_id) continue;

			$sql = "DELETE FROM {$table_name} WHERE uid = '{$uid}'";
			$mysql->query($sql);

			$sql = "DROP TABLE mallRN_board_{$b_id}";
			$mysql->query($sql);

			$sql = "DROP TABLE mallRN_board_{$b_id}_comment";
			$mysql->query($sql);
			
			delTree(BOARD_FOLDER.'/'.$b_id);	
		}

		alertMsg("{$i}건의 게시판이 삭제 되었습니다!", $link_page);		

	break;

	case "article_delete" :
		
		$item		= checkPostVar('item');
		$b_id		= checkPostVar('b_id');
		if(!$item || !$b_id)  logMsg('필수 정보가 제대로 넘어오지 못했습니다.');

		$table_name	= 'mallRN_board_'.$b_id;

		for($i = 0, $cnt = count($item); $i < $cnt; $i ++) {
			$uid	= $item[$i];
			
			$sql = "SELECT id, passwd, o_uid FROM {$table_name} WHERE uid = '{$uid}'";
			$data = $mysql->one_row($sql);
			if(!$data) continue;

			$sql = "SELECT count(*) FROM {$table_name} WHERE o_uid = '{$uid}'";
			if($mysql->get_one($sql) > 0) {
				$item_array	= array('id', 'name', 'passwd', 'subject', 'content', 'files', 'links', 'add1', 'add2', 'add3', 'add4', 'add5', 'ip', 'o_id', 'o_uid');
				
				$sql		= "UPDATE {$table_name} SET";
				foreach ($item_array as $k => $v) {
					$sql .= " {$v} = '',";
				}
				$sql		.= " secret = 0, cate = 0, count_comment = 0, dels = 1 ";
				$sql		.= " WHERE uid = '{$uid}'";		
				$mysql->query($sql);

				$sql = "UPDATE {$table_name} SET o_id = '' WHERE o_uid = '{$uid}'";
				$mysql->query($sql);
			}
			else {		
				$sql = "DELETE FROM {$table_name} WHERE uid = '{$uid}'";
				$mysql->query($sql);
			}

			$sql = "DELETE FROM {$table_name}_comment WHERE b_uid = '{$uid}'";
			$mysql->query($sql);

			$upload_dir	=  BOARD_FOLDER.'/'.$b_id.'/'.$uid;
			delTree($upload_dir);

			if($data['o_uid']) {
				$sql = "SELECT dels FROM {$table_name} WHERE uid = '{$data['o_uid']}'";
				if($mysql->get_one($sql) == 1) {
					$sql = "SELECT count(*) FROM {$table_name} WHERE o_uid = '{$data['o_uid']}'";
					if($mysql->get_one($sql) == 0) {
						$sql = "DELETE FROM {$table_name} WHERE uid = '{$data['o_uid']}'";
						$mysql->query($sql);						
					}			
				}
			}	
			
		}

		alertMsg("{$i}건의 게시글이 삭제 되었습니다!", "board.php?b_id={$b_id}{$addstring}");

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}

?>
