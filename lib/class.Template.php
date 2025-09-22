<?php

/******************************************************************
* =======================================================
* 클래스: 쇼핑몰을 위한 템플릿 클래스
*
* 제  작: 베어템플릿 (http://cafe.naver.com/hopegiver.cafe)
*
* 
*
* 수정일: 2019.03.30 
*
* =======================================================
******************************************************************/

class classTemplate {

	var $Src;			//템플릿 원본 내용
	var $Var;			//치환될 변수들이 저장
	var $Root;			//템플릿 디렉토리 경로
	var $separator;
	var $pg_string;    // 페이징연동용
	var $lt_string;    // 상품분류위치출력연동용

/*
####################################################################
     ::: 생성자 :::          
####################################################################
*/
	function __construct($path=".") {
		
		$this->Src = array();
		$this->Var = array();		//치환변수를 글로벌 변수로 초기화함.
		$this->Root = preg_replace("/[\/]*$/", "", $path);	//템플릿 디렉토리 초기경로 보정
		$this->separator = array("{{", "}}");

	}

 
/*
####################################################################
     ::: 템플릿 영역을 정의하는 함수 :::          
####################################################################
*/
	function define($var, $parent="") {
       
		if(!preg_match("/^[a-z0-9_-]+$/i", $parent)) return $this->define_file($var, $parent);
		else return $this->define_area($var, $parent);
		
	}


/*
####################################################################
     ::: 템플릿 영역을 정의하는 함수 :::          
####################################################################
*/
	function define2($var, $parent="") {
       
		$this->Src[$var] = $parent;	
		
	}


/*
####################################################################
     ::: 파일을 읽어 템플릿을 정의하는 함수 :::          
####################################################################
*/

	function define_file($var, $filename) {
		global $skin;
		
		$path = $this->Root."/".$filename;

		if(!is_file($path)) $this->errorMsg("템플릿 파일을 찾을 수 없습니다.<br> 파일이름 : {$filename}");

		$fp = fopen($path,"r");
		$buffer = fread($fp,filesize($path));
		fclose($fp);
		$this->Src[$var] = $buffer;
		return true;
		
     }


/*
####################################################################
     ::: 내부 다이나믹 영역을 정의하는 함수 :::          
####################################################################
*/

	function define_area($var, $parent) {
		
		$buffer = $this->Src[$parent];
		$buff = explode("<!-- DYNAMIC @$var@ -->", $buffer);
		if(count($buff) == 3) {
			$this->Src[$var] = $buff[1];
			$this->Src[$parent] = $buff[0].$this->separator[0].$var.$this->separator[1].$buff[2];
			return true;
		} 
		else return false;

	}


/*
####################################################################
     ::: 페이징 연동 페이징 형태 정의하는 함수 :::          
####################################################################
*/

	function define_paging($var,$parent) {
		
		$buffer = $this->Src[$parent];
		$buff = explode("<!-- DYNAMIC @$var@ -->", $buffer);
		if(count($buff) == 3) {
			$this->Src[$var] = "{{PAGING}}";
			preg_match('/PAGING\(([^"]*)\)/',$buff[1], $matches);
			$this->pg_string = $matches[1];
			$this->Src[$parent] = $buff[0].$this->separator[0].$var.$this->separator[1].$buff[2];
			return true;
		} else return false;

	}

	function getPgstring() {
		
		return $this->pg_string;

	}

/*
####################################################################
     ::: 분류상품 연동 정의하는 함수 :::          
####################################################################
*/

	function define_catelist($var,$parent) {
		
		$buffer = $this->Src[$parent];
		$buff = explode("<!-- DYNAMIC @$var@ -->", $buffer);
		if(count($buff) == 3) {
			preg_match('/CATELIST\(([^"]*)\)/',$buff[1], $matches);
			$this->cl_string = $matches[1];
			$this->Src[$parent] = $buff[0].$this->separator[0].$var.$this->separator[1].$buff[2];
			return true;
		} 
		else return false;

	}

	function getClstring() {

		return $this->cl_string;

	}

/*
####################################################################
     ::: 게시판 최근글 연동 정의하는 함수 :::          
####################################################################
*/

	function define_boardlist($var,$parent) {
		$buffer = $this->Src[$parent];
		$buff = explode("<!-- DYNAMIC @$var@ -->", $buffer);
		if(count($buff) == 3) {
			preg_match('/BOARDLIST\(([^"]*)\)/',$buff[1], $matches);
			$this->bl_string = $matches[1];
			$this->Src[$parent] = $buff[0].$this->separator[0].$var.$this->separator[1].$buff[2];
			return true;
		} 
		else return false;

	}

	function getBlstring() {

		return $this->bl_string;

	}


/*
####################################################################
     ::: 상품 분류 위치 출력 형태 정의하는 함수 :::          
####################################################################
*/

	function define_location($var,$parent) {
		
		$buffer = $this->Src[$parent];
		$buff = explode("<!-- DYNAMIC @$var@ -->", $buffer);
		if(count($buff) == 3) {
			$this->Src[$var] = "{{LOCATION}}";
			preg_match('/LOCATION\(([^"]*)\)/',$buff[1], $matches);
			$this->lt_string = $matches[1];
			$this->Src[$parent] = $buff[0].$this->separator[0].$var.$this->separator[1].$buff[2];
			return true;
		} 
		else return false;

	}

	function getLtstring() {

		return $this->lt_string;

	}


/*
####################################################################
     ::: 내부 다이나믹 영역을 검색하는 재귀 함수 :::          
####################################################################
*/

	function scan_area($parent) {

		$buffer = &$this->Src[$parent];
		$pos_offset = 0;
		while(is_long($pos = strpos($buffer, "<!-- DYNAMIC @", $pos_offset))) { 
			$pos += 14; 
			$endpos = strpos($buffer, "@ -->", $pos); 
			$child = substr($buffer, $pos, $endpos-$pos);
 			if($child=="define_catelist") {
				$this->define_catelist($child,$parent);
			} 
			else if($child=="define_boardlist") {
				$this->define_boardlist($child,$parent);
			}
			else if($child=="define_pg") {
				$this->define_paging($child,$parent);
			}
			else if($child=="define_location") {
				$this->define_location($child,$parent);
			}
			else if($this->define_area($child, $parent)) {
				$pos_offset = $pos + strlen($child) - 14;
				$this->scan_area($child); // 재귀호출				
			} 
			else {
				$pos_offset = $endpos + 5;
			}
		}

	}

/*
####################################################################
     ::: 파싱 함수 :::          
####################################################################
*/

	function parse($var,$ctl="") {  
		
		if(!isset($this->Src[$var])) return false; 
		if(!$buffer = $this->Src[$var]) return false;
		$buff1 = explode($this->separator[0], $buffer);
		$arr[] = $buff1[0];

		$tmp_key = array();

		for($i=1; $i<count($buff1); $i++) {

			$buff2 = explode($this->separator[1], $buff1[$i]);

			if(count($buff2) == 2 && preg_match("/^[a-z0-9\_\-\.]+$/i", $buff2[0])) {
				$key = $buff2[0];
				if(isset($GLOBALS[$key])) {
					$arr[] = $GLOBALS[$key];
				}
				else $arr[] = &$this->Var[$key]; 
				
				$arr[] = $buff2[1];
				
				if(preg_match("/loop_/i", $var) && (preg_match("/loop_/i", $key) || preg_match("/is_/i", $key))) {
					$tmp_key[] = $key;
				}
				else if(preg_match("/is_/i", $var) && (preg_match("/loop_/i", $key) || preg_match("/is_/i", $key))) {
					$tmp_key[] = $key;				
				}				
			} 
			else {				
				$arr[] = ""; 
				$arr[] = $buff1[$i];
			}
			
		}

		@$this->Var[$var] .= implode("", $arr);
				
		foreach ($tmp_key as $k => $v) {			
			$this->Var[$v] = "";			
		}
		
	}


/*
####################################################################
     ::: 출력 함수 :::          
####################################################################
*/

	function tprint($var, $return = "") {
		global $skin;	

		$buffer = $this->Var[$var];
		$org = "./img|img/|images/";
		$org = str_replace("/", "\/",$org);       //이미지 경로 보정
		$buffer = preg_replace("/([='\"])($org)/i", "\\1$skin/\\2", $buffer);
		$buffer = preg_replace("/([=(])($org)/i", "\\1$skin/\\2", $buffer);
		//$buffer = preg_replace("/''/i", "'", $buffer);
		if($return) return $buffer;
		else print($buffer);

	}


/*
####################################################################
     ::: 에러 메세지 :::          
     에러 메세지 출력후 종료
####################################################################
*/

    function errorMsg($str) {
        echo "<style type='text/css'>
			  <!-- 
				button[type=button].btns { border:0; overflow:hidden; z-index:1; color:#fff; text-align:center; cursor: pointer; background:#666; font-size: 0.8em; height:32px;  width:150px; line-height:2.5em; letter-spacing: 0.05em; justify-content: center; align-items: center; position: relative; box-shadow:0; -webkit-box-shadow:0; }\n
				button[type=button].btns::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background:#f48042; transform-origin: 0 0; transform: scale3d(0, 1, 1); transition: transform 100ms; }\n
				button[type=button].btns .text { color:#fff; position: relative; }\n
				button[type=button].btns:hover::before { transform-origin: 0 0; transform: scale3d(1, 1, 1); }\n
			  -->
			</style>";			

		echo " <center>
					<div style='margin:200px 0; width:500px; border:2px solid #f48042;box-shadow: 0px 0px 6px #aaa; -webkit-box-shadow: 0px 0px 6px #aaa;' class='fontSCDream'>\n
						<div style='height:30px; line-height:1.8em; background-color:#f48042; text-align:center; color:#fff;'>ERROR</div>\n
						<div style='padding:30px 10px; text-align:center; background-color:#fff;' class='eng'>{$str}</div>\n
						<center style='margin:20px 0;'><button class='btns' type='button' onclick='history.back();'><span class='text'> Move Back </span></button></center>\n
					</div>\n
				</center>"; 		  
		exit; 

     }



/*
####################################################################
     ::: 변수삭제 :::          
####################################################################
*/

	function close($var="") {
		
		$keys = array();

		if(is_array($var)) $keys = $var;
		elseif($var != "") $keys[0] = $var; 
		else $keys = array_keys($this->Src);

		foreach($keys as $key) {
			unset($this->Var[$key]);
			unset($this->Src[$key]);
		}

	}

}    // End of class

?>