<?php

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('DEFAULT_PATH',			'../');
define('BOARD_DATA',			'data');
define('TEMP_UPLOAD_FOLDER',	'../image/temp_upload');

include_once('../php/init.php');

$mysql->msgType(1);

if(!preg_match("/{$_SERVER['HTTP_HOST']}/i",$_SERVER['HTTP_REFERER'])) logMsg("정상적으로 등록하세요!");

$addstring			= "";
$search_variable	= array('field' ,'keyword', 'limit', 'page');
foreach ($search_variable as $k => $v) {
	if($v=='keyword') $value = isset($_GET[$v]) ?  urldecode($_GET[$v]) : '';
	else $value = isset($_GET[$v]) ? $_GET[$v] : '';

	if($value) $addstring .= "&{$v}={$value}";
}

$b_id		= checkPostVar('b_id');
if(!$b_id) logMsg("게시판 아이디가 없습니다.<br />board.php?b_id=xxxx 형태로 게시판아이디를 넣으셔야 사용 가능 합니다.");

$b_mode		= isset($_GET['b_mode']) ? add_escape_re_string($_GET['b_mode']) : add_escape_re_string($_POST['b_mode']);
if(!$b_mode) logMsg("필수 정보가 넘어오지 못했습니다.");

$sql		= "SELECT * FROM mallRN_board_manager WHERE id = '{$b_id}'";
$board_info = $mysql->one_row($sql);

if(!$board_info) logMsg("해당 게시판이 없거나 삭제된 게시판 입니다.");

$error_msg_array	= array("write" => "글쓰기", "modify" => "글수정", "reply" => "답글쓰기", "delete" => "글삭제");
$b_mode_array		= array("write" => "write", "modify" => "write", "reply" => "reply", "delete" => "write");
$b_mode2			= $b_mode_array[$b_mode];

$managers			= checkPostVar('managers');
$vendor				= checkPostVar('vendor');
$mypages			= checkPostVar('mypages');
if($managers == 1)		$DEFAULT_LINK		= "../managers/board/board.php?b_id={$b_id}";
else if($vendor == 1)	$DEFAULT_LINK		= "../vendor/board/board.php?b_id={$b_id}";
else if($mypages)		$DEFAULT_LINK		= "../{$Main}?channel={$mypages}";
else					$DEFAULT_LINK		= "../{$Main}?channel=cs_board&b_id={$b_id}";

if($vendor == 1 && $_COOKIE['v_my_id'] && $b_id == 'vcounsel') {
	include_once(DEFAULT_PATH.PATH_LIB.'/checkVLogin.php');
	$my_id		= $v_my_id;
	$my_name	= $v_my_name;
}

if($board_info['access_'.$b_mode2] > 0) {
	if($board_info['access_'.$b_mode2] == 1) {
		if($my_level < 99) logMsg($error_msg_array[$b_mode]." 권한이 없습니다.");
	}
	else if($board_info['access_'.$b_mode2] == 2) {
		if(!$my_id) logMsg($error_msg_array[$b_mode]." 권한이 없습니다.");
	}
	else if($board_info['access_'.$b_mode2] == 3) {
		$access_level	= explode(",", $board_info['access_'.$b_mode2.'_level']);
		array_push($access_level, '99', '100');
		if(!$my_id || !in_array($my_level, $access_level)) logMsg($error_msg_array[$b_mode]." 권한이 없습니다.");
	}
	else if($board_info['access_'.$b_mode2] == 4) {
		if($my_level < 99 && !$v_my_id) logMsg($error_msg_array[$b_mode]." 권한이 없습니다.");
	}
}

if($b_mode != 'delete') {

	if($my_id) {
		$_POST['id']		= $my_id;
		$_POST['name']		= $my_name;
		$_POST['passwd']	= "****";
	}
	else {
		$_POST['id']		= "";
		$_POST['name']		= checkPostVar('name');	
		$_POST['passwd']	= checkPostVar('passwd');
	}

	$_POST['content']		= checkPostVar('content');
	$_POST['signdate']		= time();

}

if($b_mode == 'write' || $b_mode == 'modify' || $b_mode == 'reply') {
	
	$_POST['notice']		= checkPostVar('notice');
	if($_POST['notice'] == 1)	$_POST['notice'] = 0;
	else						$_POST['notice'] = 1;

	$_POST['subject']		= checkPostVar('subject');
		
	$_POST['ip']			= $_SERVER['REMOTE_ADDR'];
	$uid					= checkPostVar('uid');

	if(isset($_POST['links']))	$_POST['links'] = join('|', $_POST['links']);
	else						$_POST['links'] = "";
	if($_POST['links'] == "||||") $_POST['links'] = "";

	if($board_info['secret_type'] == 1) $_POST['secret'] = 1; 

	$item_array				= array('notice', 'name', 'subject', 'cate', 'content', 'links', 'add1', 'add2', 'add3', 'add4', 'add5', 'secret', 'ip');
	$item_default			= array('secret', 'cate');
}

$table_name	= 'mallRN_board_'.$b_id;

if($b_mode == 'write' || $b_mode == 'reply' || $b_mode == 'comment_write' || $b_mode == 'comment_reply') {
	###################### 게시판 자동등록 방지(등록파일용) #####################
	$ck_w1	= add_escape_re_string(checkPostVar('ck_w1'));
	$ck_w2	= checkPostVar('ck_w2');
	$ck_w3	= md5($ck_w1.CONF_KEY); 
	$ck_w4	= base64_decode($ck_w1);
	$gap	= time() - intval($ck_w4);

	if(!$ck_w1 || !$ck_w2 || ($ck_w2 != $ck_w3) || $gap < 2 || $gap > 1800) logMsg("스팸글의심되어 차단되었습니다.<br />다시 정상적으로 등록 하시기 바랍니다.");
	
	$ck_data	= array();
	$ck_data2	= array();
	if($board_info['ck_auto']) {
		$ck_data	= explode("|", $board_info['ck_auto']); 			
		foreach($ck_data as $k => $v) {
			if($v == $ck_w4)  logMsg("스팸글의심되어 차단되었습니다.<br />다시 정상적으로 등록 하시기 바랍니다.");
			$ck_time = time()  -  intval($v);  
			if($ck_time < 1800) {
			   $ck_data2[] = $v;
			}  				
		}		
	}  
	$ck_data2[] = $ck_w4;		
	$ck_auto	= join("|", $ck_data2);	
	
	unset($ck_data, $ck_data2);
	###################### 게시판 자동등록 방지(등록파일용) #####################
}

switch($b_mode) {

	case "write" :  case "reply" :	
		
		if(!$_POST['name'] || !$_POST['passwd'] || !$_POST['subject'] || !$_POST['content']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}
			
		if($b_mode == 'reply') {
			if(!$uid || !is_numeric($uid)) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
			$sql = "SELECT idx, main, sub, depth, secret, passwd, id FROM {$table_name} WHERE uid = '{$uid}'";
			$data = $mysql->one_row($sql);
			if(!$data) logMsg('등록된 글이 없거나 삭제되었습니다.');			

			$_POST['idx']	= $data['idx'];
			$_POST['main']	= $data['main'];
			$_POST['sub']	= $data['sub'] + 1;
			$_POST['depth']	= $data['depth'] + 1;

			$sql = "UPDATE {$table_name} SET sub = sub + 1 WHERE idx = {$data['idx']} &&  main = {$data['main']} && sub > {$data['sub']}";
			$mysql->query($sql);

			$_POST['o_id']		= $data['id'];
			$_POST['o_uid']		= $uid;
			$_POST['secret']	= $data['secret'];
			if($data['secret'] == 1)	$_POST['passwd'] = $data['passwd'];
			else						$_POST['passwd'] = md5($_POST['passwd']);
		}
		else {		
			$_POST['o_uid']		= "";
			$_POST['o_id']		= "";

			$sql = "SELECT MIN(idx) FROM {$table_name}";
			if(!$_POST['idx']	= $mysql->get_one($sql)) {
				$_POST['idx']	= "254";
				$_POST['main']	= "65535";
			}
			else {
				$sql = "SELECT MIN(main) FROM {$table_name} WHERE idx = '{$_POST['idx']}'";
				if(!$_POST['main'] = $mysql->get_one($sql)) $_POST['main'] = "65535";
				else {
					if($_POST['main'] == 0) {
						$_POST['main'] = "65535";
						if($_POST['idx'] == 0)  logMsg("더이상 글을 등록 하실 수 없습니다.");
						else $_POST['idx'] --;
					}
					else $_POST['main'] --;
				}
			}
			$_POST['sub']		= 0;
			$_POST['depth']		= 0;
			$_POST['passwd']	= md5($_POST['passwd']);
		}

		array_push($item_array, 'idx', 'main', 'sub', 'depth', 'id', 'passwd', 'o_id', 'o_uid', 'signdate');
		######################## 글 등록  #########################		
		$sql = "INSERT INTO {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);
		$uid		= $mysql->InsertNo();
		$upload_dir	=  BOARD_DATA.'/'.$b_id.'/'.$uid;
		if(!is_dir($upload_dir)) mkdir($upload_dir, 0707);	
		######################## 글 등록  #########################

		if(isset($_POST['temp_upload'])) {
			######################## 내용 이미지  #########################
			$ck_files = 0;
			$temp_upload = TEMP_UPLOAD_FOLDER.'/'.previlDecode($_POST['temp_upload']);
			$handle	= @opendir($temp_upload);	
			while ($file = @readdir($handle)) {
				if($file != '.' && $file != '..') {
					$ck_files = 1;
					break;
				}
			}
			@closedir($handle);	

			if($ck_files == 1) {			
				copyTree($temp_upload, $upload_dir);	
				if($_POST['content']) {
					$temp_upload2	= str_replace("..", "", $temp_upload);
					$temp_upload2	= str_replace("//", "/", $temp_upload2);					
					$upload_dir2	= "/".CONF_ROOT."board/".$upload_dir;
					$content		= str_replace($temp_upload2, $upload_dir2, $_POST['content']);
					$sql			= "UPDATE {$table_name} SET content = '{$content}' WHERE uid = '{$uid}'";
					$mysql->query($sql);					
					unset($content);
				}
			}		
			delTree($temp_upload);
			SetCookie("temp_upload", "", -999, "/");
			######################## 내용 이미지  #########################
		}

		######################## 첨부파일 저장  #########################		
		if($board_info['upload_file'] == 1) {
			$attach_file = array();
			for($i = 1; $i < 6; $i ++) {
				if(!preg_match("/none/i",$_FILES['attach_file'.$i]['tmp_name']) && $_FILES['attach_file'.$i]['tmp_name']) {									
					$attach_file[] = upFile($_FILES['attach_file'.$i]['tmp_name'], $_FILES['attach_file'.$i]['name'], $upload_dir, 0, '', 1, $board_info['upload_size']);
				}
			}
			if(count($attach_file) > 0) {
				$files = join("|", $attach_file);
				$sql = "UPDATE {$table_name} SET files = '{$files}' WHERE uid = '{$uid}'";
				$mysql->query($sql);
			}
			unset($files, $attach_file);
		}
		######################## 첨부파일 저장  #########################

		$sql = "UPDATE mallRN_board_manager SET ck_auto = '{$ck_auto}' WHERE id = '{$b_id}'";     
		$mysql->query($sql);
		
		if($b_mode == 'write' && $b_id == 'counsel' && $managers != 1) {
			fcmSend("신규 1:1문의 알림!", "{$_POST['name']}님의 1:1문의가 접수되었습니다.");
			alertMsg("1:1문의가 등록 되었습니다.", "{$DEFAULT_LINK}{$addstring}");
		}
		else parentMovePage("{$DEFAULT_LINK}{$addstring}");

    break;	

	case "modify" :

		if(!$uid || !is_numeric($uid) || !$_POST['name'] || !$_POST['passwd'] || !$_POST['subject'] || !$_POST['content']) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$sql = "SELECT id, passwd, files FROM {$table_name} WHERE uid = '{$uid}'";
		$data = $mysql->one_row($sql);
		if(!$data) logMsg('등록된 글이 없거나 삭제되었습니다.');

		if($my_level < 100) {
			if(!$data['id']) {
				$passwd = md5($_POST['passwd']);
				if($passwd != $data['passwd']) logMsg("비밀번호가 일치하지 않습니다.");
			}
			else if($data['id'] != $my_id) logMsg("회원정보가 일치하지 않습니다."); 
		}

		######################## 글 수정  #########################		
		$sql = "UPDATE {$table_name} SET";
		foreach ($item_array as $k => $v) {
			if($v == 'name') continue;
			if(in_array($v, $item_default)) $_POST[$v] = checkPostVar($v, 0);
			else $_POST[$v] = checkPostVar($v);
			
			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$sql .= " WHERE uid = '{$uid}'";		
		$mysql->query($sql);
		######################## 글 수정  #########################
		
		$upload_dir		=  BOARD_DATA.'/'.$b_id.'/'.$uid;
		$check_image	= array();

		######################## 첨부파일 저장  #########################		
		if($board_info['upload_file'] == 1) {

			if(!is_dir($upload_dir)) mkdir($upload_dir, 0707);
			
			$attach_file = array();

			if(!$data['files']) {	
				for($i = 1; $i < 6; $i ++) {
					if(!preg_match("/none/i",$_FILES['attach_file'.$i]['tmp_name']) && $_FILES['attach_file'.$i]['tmp_name']) {									
						$attach_file[] = upFile($_FILES['attach_file'.$i]['tmp_name'], $_FILES['attach_file'.$i]['name'], $upload_dir, 0, '', 1, $board_info['upload_size']);
					}
				}
				if(count($attach_file) > 0) {
					$files			= join("|", $attach_file);
					$check_image	= $attach_file;

					$sql = "UPDATE {$table_name} SET files = '{$files}' WHERE uid = '{$uid}'";
					$mysql->query($sql);
				}
				unset($files, $attach_file);
			}
			else {			
				$files_array = explode("|", $data['files']);
				for($i = 1; $i < 6; $i ++) {
					$attach_del = checkPostVar('attach_del'.$i);
					if($attach_del == 1) {
						delFile($upload_dir.'/'.$files_array[$i - 1]);
						$files_array[$i - 1] = "";
					}
					
					if(!preg_match("/none/i",$_FILES['attach_file'.$i]['tmp_name']) && $_FILES['attach_file'.$i]['tmp_name']) {									
						$attach_file[] = upFile($_FILES['attach_file'.$i]['tmp_name'], $_FILES['attach_file'.$i]['name'], $upload_dir, 0, '', 1, $board_info['upload_size']);
						delFile($upload_dir.'/'.$files_array[$i - 1]);
					}
					else {
						if(isset($files_array[$i - 1])) $attach_file[] = $files_array[$i - 1];
					}
				}
				if(count($attach_file) > 0) {
					$files = join("|", $attach_file);
					$check_image	= $attach_file;
				}
				else {
					$files = "";

					$ck_files = 0;
					$handle	= @opendir($upload_dir);	
					while ($file = @readdir($handle)) {
						if($file != '.' && $file != '..') {
							break;
						}
					}
					@closedir($handle);	

					if($ck_files == 0) delTree($upload_dir);
				}		

				$sql = "UPDATE {$table_name} SET files = '{$files}' WHERE uid = '{$uid}'";
				$mysql->query($sql);
				unset($files, $attach_file, $files_array);
			}
		}
		######################## 첨부파일 저장  #########################
	
		########################  사용안하는 이미지 삭제  #############################
		$arrimgurl		= array();
		$p				= explode("\n", stripslashes($_POST['content']));
		for($u=0; $u<count($p); $u++) {
			if(preg_match("/title=/i", $p[$u])) {
				$pos1 = strpos($p[$u], "title=", 0);	
				$pos2 = strpos($p[$u], "\"", $pos1)+1;
				$pos3 = strpos($p[$u], "\"", $pos2);

				$tmp_name = substr($p[$u], $pos1, ($pos3-$pos1));		
				$p[$u] = str_replace($tmp_name, "", $p[$u]);
			}
			
			while(preg_match("/[^=\"']*\.(gif|jpg|bmp|png)([?|\"|\'| |>]){1}/i", $p[$u], $val)){
				$arrimgurl[substr($val[0],0,-1)]=substr($val[0],0,-1);
				$p[$u]=str_replace(substr($val[0],0,-1),"",$p[$u]);
			}
		}

		foreach ($arrimgurl as $k => $v) {	
			$v				= trim($v);
			if(preg_match("/{$_SERVER['HTTP_HOST']}/i",$v)) continue;
			$check_image[]	= substr(strrchr($v,"/"),1);
		}

		$handle			= @opendir($upload_dir);	
		while ($file = @readdir($handle)) {
			if($file != '.' && $file != '..') {

				if(count($check_image) > 0) {
					if(!in_array($file, $check_image)) unlink("{$upload_dir}/{$file}");
				}
				else unlink("{$upload_dir}/{$file}");
			}
		}
		@closedir($handle);					
		########################  사용안하는 이미지 삭제  #############################

		parentMovePage("{$DEFAULT_LINK}&b_mode=view&uid={$uid}{$addstring}");

	break;

	case "comment_write" : case "comment_reply" :
		$b_uid	= checkPostVar('b_uid');
		$c_uid	= checkPostVar('c_uid');
		
		if(!$_POST['name'] || !$_POST['passwd'] || !$_POST['content'] || !$b_uid || !is_numeric($b_uid)) {
			logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
		}

		$item_array	= array('b_uid', 'main', 'sub', 'depth', 'id', 'passwd', 'name', 'content', 'ip', 'o_uid', 'signdate');

		if($b_mode == 'comment_reply') {
			if(!$c_uid || !is_numeric($c_uid)) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");			
			$sql = "SELECT main, sub, depth FROM {$table_name}_comment WHERE uid = '{$c_uid}'";
			$data = $mysql->one_row($sql);
			if(!$data) logMsg('등록된 댓글이 없거나 삭제되었습니다.');			

			$_POST['main']	= $data['main'];
			$_POST['sub']	= $data['sub'] + 1;
			$_POST['depth']	= $data['depth'] + 1;
			$_POST['o_uid']	= $c_uid;

			$sql = "UPDATE {$table_name}_comment SET sub = sub + 1 WHERE b_uid = '{$b_uid}' && main = {$data['main']} && sub > {$data['sub']}";
			$mysql->query($sql);			
		}
		else {		
			$sql = "SELECT MIN(main) FROM {$table_name}_comment WHERE b_uid = '{$b_uid}'";
			if(!$_POST['main'] = $mysql->get_one($sql)) $_POST['main'] = "65535";
			else {
				if($_POST['main'] == 0) logMsg("더이상 글을 등록 하실 수 없습니다.");
				else $_POST['main'] --;
			}
			$_POST['sub']		= 0;
			$_POST['depth']		= 0;			
			$_POST['o_uid']		= '';
		}		
		$_POST['passwd']	= md5($_POST['passwd']);

		######################## 글 등록  #########################		
		$sql = "INSERT INTO {$table_name}_comment SET";
		foreach ($item_array as $k => $v) {
			$_POST[$v] = checkPostVar($v);

			if($k == count($item_array)-1) $sql .= " {$v} = '{$_POST[$v]}'";
			else $sql .= " {$v} = '{$_POST[$v]}',";
		}
		$mysql->query($sql);		

		$sql = "UPDATE {$table_name} SET count_comment = count_comment + 1 WHERE uid = '{$b_uid}'";
		$mysql->query($sql);		
		######################## 글 등록  #########################		

		$sql = "UPDATE mallRN_board_manager SET ck_auto = '{$ck_auto}' WHERE id = '{$b_id}'";     
		$mysql->query($sql);

		topMovePage("{$DEFAULT_LINK}&b_mode=view&uid={$b_uid}&focus=1{$addstring}");

	break;

	case "delete" :
		$uid	= checkPostVar('uid');
		if(!$uid || !is_numeric($uid)) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");

		$sql = "SELECT id, passwd, o_uid FROM {$table_name} WHERE uid = '{$uid}'";
		$data = $mysql->one_row($sql);
		if(!$data) logMsg('등록된 글이 없거나 삭제되었습니다.');

		if($my_level < 100) {			
			if(!$data['id']) {
				$passwd	= checkPostVar('passwd');
				if(!$passwd) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
				$passwd = md5($passwd);
				if($passwd != $data['passwd']) logMsg("비밀번호가 일치하지 않습니다.");
			}
			else if($data['id'] != $my_id) logMsg("회원정보가 일치하지 않습니다."); 
		}

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

		$upload_dir	=  BOARD_DATA.'/'.$b_id.'/'.$uid;
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

		topMovePage("{$DEFAULT_LINK}{$addstring}");

	break;

	case "comment_delete" :
		$uid	= checkPostVar('uid');
		$c_uid	= checkPostVar('c_uid');
		if(!$uid || !is_numeric($uid) || !$c_uid || !is_numeric($c_uid)) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");

		$sql = "SELECT id, passwd, o_uid FROM {$table_name}_comment WHERE uid = '{$c_uid}'";
		$data = $mysql->one_row($sql);
		if(!$data) logMsg('등록된 댓글이 없거나 삭제되었습니다.');

		if($my_level < 100) {
			$passwd	= checkPostVar('passwd');
			if(!$passwd) logMsg("필수 정보가 제대로 넘어오지 못했습니다.");
			if(!$data['id']) {
				$passwd = md5($passwd);
				if($passwd != $data['passwd']) logMsg("비밀번호가 일치하지 않습니다.");
			}
			else if($data['id'] != $my_id) logMsg("회원정보가 일치하지 않습니다."); 
		}

		$sql = "SELECT count(*) FROM {$table_name}_comment WHERE o_uid = '{$c_uid}'";
		if($mysql->get_one($sql) > 0) {
			
			$item_array	=  array('id', 'passwd', 'name', 'content', 'ip', 'o_uid');
			
			$sql		= "UPDATE {$table_name}_comment SET";
			foreach ($item_array as $k => $v) {
				if($k == count($item_array)-1) $sql .= " {$v} = ''";
				else $sql .= " {$v} = '',";
			}
			$sql		.= " , dels = 1";
			$sql		.= " WHERE uid = '{$c_uid}'";		
			$mysql->query($sql);
		}
		else {		
			$sql = "DELETE FROM {$table_name}_comment WHERE uid = '{$c_uid}'";
			$mysql->query($sql);
		}
		
		$sql = "UPDATE {$table_name} SET count_comment = count_comment - 1 WHERE uid = '{$uid}'";
		$mysql->query($sql);	
		
		if($data['o_uid']) {
			$sql = "SELECT dels FROM {$table_name}_comment WHERE uid = '{$data['o_uid']}'";
			if($mysql->get_one($sql) == 1) {
				$sql = "SELECT count(*) FROM {$table_name}_comment WHERE o_uid = '{$data['o_uid']}'";
				if($mysql->get_one($sql) == 0) {
					$sql = "DELETE FROM {$table_name}_comment WHERE uid = '{$data['o_uid']}'";
					$mysql->query($sql);						
				}			
			}
		}	

		topMovePage("{$DEFAULT_LINK}&b_mode=view&uid={$uid}&focus=1{$addstring}");

	break;

	default: logMsg("정보가 제대로 넘어오지 못했습니다.");
	break;
}
?>
