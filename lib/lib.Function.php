<?php

/******************************************************************************
 *더도매 library 
 *
 * 마지막 수정일자 : 2019. 03. 30
 *
 * by더도매 
 *
 ******************************************************************************/

/*
##############################################
    ::: 에러 표시 :::          
    사용방법 : Error('메세지'); 
##############################################
*/

function Error($str){
	
	header("Content-Type: text/html; charset=utf-8");
	echo "	<!doctype html> 
			<html lang='ko'>
			<head>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />	
			<title>Error</title>
			<style type='text/css'>
			  <!-- 
				@font-face {font-family: 'Noto Sans KR';font-style: normal;font-weight: 300;src: url(//fonts.gstatic.com/ea/notosanskr/v2/NotoSansKR-Light.woff2) format('woff2'),url(//fonts.gstatic.com/ea/notosanskr/v2/NotoSansKR-Light.woff) format('woff'),url(//fonts.gstatic.com/ea/notosanskr/v2/NotoSansKR-Light.otf) format('opentype');}\n
				.fontNoto { font-family: 'Noto Sans KR', sans-serif; font-weight:300; font-size:0.9em; line-height:1.8em }\n
				button[type=button].btns { border:0; overflow:hidden; z-index:1; color:#fff; text-align:center; cursor: pointer; background:#666; font-size: 0.8em; height:32px;  width:150px; line-height:2.5em; letter-spacing: 0.05em; justify-content: center; align-items: center; position: relative; box-shadow:0; -webkit-box-shadow:0; }\n
				button[type=button].btns::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background:#f48042; transform-origin: 0 0; transform: scale3d(0, 1, 1); transition: transform 100ms; }\n
				button[type=button].btns .text { color:#fff; position: relative; }\n
				button[type=button].btns:hover::before { transform-origin: 0 0; transform: scale3d(1, 1, 1); }\n
			  -->
			</style>
			</head>";

	echo " <center>
				<div style='margin-top:250px; width:500px; border:2px solid #f48042;box-shadow: 0px 0px 6px #aaa; -webkit-box-shadow: 0px 0px 6px #aaa;' class='fontNoto'>\n
					<div style='height:30px; line-height:1.8em; background-color:#f48042; text-align:center; color:#fff;'>ERROR</div>\n
					<div style='padding:30px 10px; text-align:center; background-color:#fff;font-size:0.9em'>{$str}</div>\n
					<center style='margin:20px 0;'><button class='btns' type='button' onclick='history.back();'><span class='text'> Move Back </span></button></center>\n
				</div>\n
			</center>"; 		  
	exit; 

}

/*
###############################################
     ::: alert 창 함수 :::          
    사용방법 : alert('메세지','이동url'); 
    ex) alert('오류~','back');
###############################################
*/

function alert($msg, $location) {
    echo "<script>alert('\\n {$msg} \\n');";
    
	if($location=="back") { echo "history.back();"; }
	else if($location=="close") { echo"self.close();";}
	else { echo "location.href = '{$location}'"; }
    echo "</script>";
	exit;

}


/*
###############################################
     ::: 사이트이동 함수 :::          
    사용방법 : movePage('이동url'); 
    ex) movePage('http://previl.net'); 
###############################################
*/

function movePage($url) {
	 echo"<meta http-equiv=\"refresh\" content=\"0; url={$url}\">";
	 exit;
}

function parentMovePage($url) {
	 echo"<script>parent.location.href = '{$url}';</script>";
	 exit;
}

function topMovePage($url) {
	 echo"<script>window.top.location.href = '{$url}';</script>";
	 exit;
}


/*
###############################################
     ::: log message 함수 :::          
    사용방법 : logMsg('메세지', '타입');   
###############################################
*/

function logMsg($msg, $type = 'error') {
	if(!is_array($msg)) $msg = addslashes($msg);
	else $msg = join(", ", $msg);
	echo "<script>";
	
	if($type=='error') echo "parent.alertify.error('{$msg}');";
	else if($type=='log') echo "parent.alertify.warning('{$msg}');";
	else if($type=='success') echo "parent.alertify.success('{$msg}');";
	
	echo "parent.$('#alertify-ok').trigger('click');";
	echo "parent.only_num_formatCk();";
	echo "</script>";
	exit;
}

/*
###############################################
     ::: alert message 함수 :::          
    사용방법 : alertMsg('메세지', '이동url');   
###############################################
*/

function alertMsg($msg, $url = '', $del = '') {
	$msg = addslashes($msg);
    echo "<script>";
	if($del == 1) echo "parent.$.cookie('mallUrl', null);";
	echo "parent.$('#alertify-ok').trigger('click');";
	echo "parent.alertify.defaults.url = '{$url}';";
	echo "parent.alertify.alert('{$msg}');";		
	echo "</script>";
	exit;
}


/*
###############################################
     ::: iframe용 Error 함수 :::          
    사용방법 : iframeViewError('메세지');   
###############################################
*/

function iframeViewError($msg) {
	$msg = addslashes($msg);
    echo "<script>";	
	echo "parent.alertify.closeAll('iframeDialog');";
	echo "parent.alertify.alert('{$msg}');";	
	echo "</script>";
	exit;
}


/*
###############################################
     ::: iframe용 메세지 함수 :::          
    사용방법 : iframeViewMsg('메세지');   
###############################################
*/

function iframeViewMsg($msg) {
	$msg = addslashes($msg);
    echo "<script>";		
	echo "parent.alertify.alert('{$msg}', function(){ window.top.location.reload(); });";	
	echo "</script>";
	exit;
}

function iframeViewMsgParent($msg) {
	$msg = addslashes($msg);
    echo "<script>";		
	echo "parent.alertify.alert('{$msg}', function(){ window.parent.location.reload(); });";
	echo "</script>";
	exit;
}

function iframeViewMsgParent2($msg) {
	$msg = addslashes($msg);
    echo "<script>";		
	echo "parent.alertify.alert('{$msg}', function(){ window.parent.parent.location.reload(); });";
	echo "</script>";
	exit;
}



/*
###############################################
     ::: json 형태 에러 메세지 :::          
    사용방법 : json_error_msg('오류');
###############################################
*/

function json_error_msg($msg) {
	echo  json_encode(array('error' => $msg));
	exit;
}

/*
##############################################
     ::: 한글 자르기 :::          
    사용방법 : hanCut('문자열','자를길이'); 
##############################################
*/

function hanCut($str, $len, $tail='...', $checkmb=false) { 

	preg_match_all('/[\xEA-\xED][\x80-\xFF]{2}|./', $str, $match); 
	$m    = $match[0]; 
	$slen = mb_strlen($str);  // length of source string 
	$tlen = mb_strlen($tail); // length of tail string 
	$mlen = count($m);    // length of matched characters 

	if($slen <= $len) return $str; 
	if(!$checkmb && $mlen <= $len) return $str; 
  
	$ret  = array(); 
	$count = 0; 
  
	for ($i=0; $i < $len; $i++) { 
		$count += ($checkmb && mb_strlen($m[$i]) > 1)?2:1; 
		if ($count + $tlen > $len) break; 
		$ret[] = $m[$i]; 
	} 
	return join('', $ret).$tail; 

} 


/*
###############################################
     ::: 랜덤수 구하기 :::          
    사용방법 : getCode('자리수');     
###############################################
*/

function getCode($len) {

    $SID = md5(uniqid(rand()));
    $code = substr($SID, 0, $len);
    return $code;

}

/*
###############################################
     :::  메일체크 함수 :::          
    사용방법 : mailCheck($email)
    참이면 true 거짓이면 false 리턴
###############################################
*/

function mailCheck($str) {

	if(!preg_match("/([a-z0-9\_\-\.]+)@([a-z0-9\_\-\.]+)/i", $str) ) return false;
	list($user, $host) = explode("@", $str);
	if(!function_exists('checkdnsrr')) return true;
	if (checkdnsrr($host, "MX") or checkdnsrr($host, "A")) return true;
	else $return = false;
	
}

/*
###############################################
     ::: 문자열에서 url을 찾아내어 링크를 시킨다.(http) :::          
    사용방법 : makeLink("문자열"); 
###############################################
*/

function makeLink($str) { 

	// URL 치환
	$homepage_pattern = "/([^\"\=\>])(mms|http|HTTP|https|HTTPS|ftp|FTP|telnet|TELNET)\:\/\/(.[^ \n\<\"]+)/";
	$str = preg_replace($homepage_pattern,"\\1<a href=\\2://\\3 target=_blank>\\2://\\3</a>", " ".$str);

	// 메일 치환
	$email_pattern = "/([ \n]+)([a-z0-9\_\-\.]+)@([a-z0-9\_\-\.]+)/";
	$str = preg_replace($email_pattern,"\\1<a href=mailto:\\2@\\3>\\2@\\3</a>", " ".$str);

	return $str;

}


/*
###############################################
     ::: 파일 삭제함수 :::          
    사용방법 : delFile('파일명');  
	ex) delFile('test.dat');
###############################################
*/

function delFile($filename) {

	@chmod($filename,0777);
	@unlink($filename);
	if(@file_exists($filename)) {
		@chmod($filename,0775);
		@unlink($filename);
	}

}

/*
###############################################
     :::  html 태그 제거 함수 :::          
    사용방법 : html2txt($document)
    html태그를 제거하고 택스트형태로 리턴
###############################################
*/

function html2txt($document){
	$search =	array('@<script[^>]*?>.*?</script>@si',	// Strip out javascript
					'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
					'@<[\/\!]*?[^<>]*?>@si',			// Strip out HTML tags
					'@<![\s\S]*?--[ \t\n\r]*>@'			// Strip multi-line comments including CDATA
				);
	$text = preg_replace($search, '', $document);
	return $text;
}


/*
###############################################
     ::: 특수문자 치환함수 :::          
    사용방법 : specialStrReplace('문자');   // selectbox 용   
###############################################
*/

function specialStrReplace($chr) { 
	
	$chr = stripslashes(trim($chr));
	$chr = str_replace("\"","&#034;",$chr);
	$chr = str_replace("'","&#039;",$chr);

	return $chr;
}

/*
###############################################
     ::: 특수문자 치환함수2 :::          
    사용방법 : specialStrReplace2('문자');     
###############################################
*/

function specialStrReplace2($chr) { 
	
	$chr = stripslashes(trim($chr));
	$chr = str_replace("<","&lt;",$chr);
	$chr = str_replace(">","&gt;",$chr);
	$chr = preg_replace('/on/i', '&#111;n', $chr);

	return $chr;
}

function specialStrReplace3($chr) { 
	
	$chr = stripslashes(trim($chr));
	$chr = str_replace("\"","&#034;",$chr);
	$chr = str_replace("<","&lt;",$chr);
	$chr = str_replace(">","&gt;",$chr);

	return $chr;
}

/*
###############################################
     ::: IE hack 방지 함수 :::          
    사용방법 : ieHackCheck($document)
    IE 해킹에 이용될 수있는 js 제거
###############################################
*/

function iehack_escape($matches){ 
	return $matches[1].'='.preg_replace('/(&#(x0*[da]|0*1[03]);|[\r\n])/i', '', $matches[2]); 
}

function ieHackCheck($html){
	
	$html = @preg_replace_callback('/(href|src)[\t\s\r\n]*=[\t\s\r\n]*((["\']).*?(?<!\x5c)\3)/is', 'iehack_escape', $html); 
	$html = preg_replace('/((href|src)=.?)(j(ava)?)?script/i', '\1javaworker', $html); 
	$html = preg_replace('/script/i', 's&#67;ript', $html); 
	$html = preg_replace('/cookie/i', '&#67;ookie', $html); 
	$html = preg_replace('/document/i', '&#68;ocument', $html);
	$html = preg_replace('/document/i', '&#68;ocument', $html);
	$html = preg_replace('/onclick/i', '&#111;nclick', $html);
	$html = preg_replace('/onmouseover/i', '&#111;nmouseover', $html);
	$html = preg_replace('/onmouseout/i', '&#111;nmouseout', $html);
	$html = preg_replace('/onfocus/i', '&#111;nfocus', $html);
	$html = preg_replace('/onblur/i', '&#111;nblur', $html);

	return $html;
}

/*
###############################################
     ::: multi-dimensional array에 사용자지정 함수적용 :::          
    사용방법 : array_map_deep("실행함수","문자열"); 
###############################################
*/

function array_map_deep($fn, $array) {
    
	if(is_array($array)) {
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $array[$key] = array_map_deep($fn, $value);
            } 
			else {
                $array[$key] = call_user_func($fn, $value);
            }
        }
    } 
	else {
        $array = call_user_func($fn, $array);
    }

    return $array;

}

/*
###############################################
     ::: SQL Injection 방어   :::   
	 사용방법 : add_escape_string(""문자열"); 
###############################################
*/

function add_escape_string($str){
	
	$pattern = ESCAFE_PATTERNS;
    $replace = ESCAFE_REPLACES;
	
	for($i=0,$cnt=count($pattern);$i<$cnt;$i++) {
		$str = preg_replace($pattern[$i], $replace[$i], $str);
	}	
	$str = call_user_func('addslashes', $str);

	return $str;
}


/*
###############################################
     ::: add_escape_string 해제   :::   
	 사용방법 : add_escape_re_string(""문자열"); 
###############################################
*/

function add_escape_re_string($str){
	
	$pattern = ESCAFE_RE_PATTERNS;
    $replace = ESCAFE_RE_REPLACES;
	
	for($i=0,$cnt=count($pattern);$i<$cnt;$i++) {		
		$str = preg_replace($pattern[$i], $replace[$i], $str);		
	}

	return $str;
}


/*
###############################################
     ::: post값 가져오기   :::   
	 사용방법 : checkPostVar(""문자열", "기본값", "가능값(array)"); 
###############################################
*/

function checkPostVar($var, $default = '', $able_value = '') {
	
	$return = isset($_POST[$var]) ? $_POST[$var] : $default;
	
	if($able_value) {
		if(!in_array($return, $able_value)) $return = $default;
	}
	
	if(!is_array($return)) $return = trim($return);	 
	
	return $return;
	
}

/*
###############################################
     ::: get값 가져오기   :::   
	 사용방법 : checkGetVar(""문자열", "기본값", "가능값(array)"); 
###############################################
*/

function checkGetVar($var, $default = '', $able_value = '') {
	$return = isset($_GET[$var]) ? $_GET[$var] : $default;

	if($able_value) {
		if(!in_array($return, $able_value)) $return = $default;		
	}

	if(!is_array($return)) $return = trim($return);	 

	return $return;
}


/*
###############################################
     ::: 숫자만 추출 후 리턴   :::   
	 사용방법 : returnNumeric("문자열"); 
###############################################
*/

function returnNumeric($var) {
	return preg_replace("/[^0-9]*/s", "", $var);
}

/*
###############################################
     ::: 링크주소체크   :::   
	 사용방법 : returnCheckLink("문자열"); 
###############################################
*/

function returnCheckLink($var) {

	if(!$var) return;
	if(preg_match("/tel:/", $var)) return $var;

	$var = stripslashes($var);

	$HeaderProtocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
	$domain_patten = '/([a-z\d\-]+(?:\.(?:asia|info|name|mobi|com|net|org|biz|tel|xxx|kr|co|so|me|eu|cc|or|pe|ne|re|tv|jp|tw|shop|io|at)){1,2})(?::\d{1,5})?(?:\/[^\?]*)?(?:\?.+)?$/i';
	
	if(!preg_match($domain_patten, $var)) {
		return $HeaderProtocol.$_SERVER["SERVER_NAME"].'/'.$var;
	}
	else {
		if(!preg_match('/^((http(s?))\:\/\/)/', $var)) {
			return $HeaderProtocol.$var;			
		}
		else return $var;
	}
}


/*
###############################################
     :::  암호화 :::          
    사용방법 : previlEncode('원문'); 
    ex) $encode_str = previlEncode('previl') ;  한글도 가능
###############################################
*/

function previlEncode($og_string){
    if(!$og_string) return false;
	$len_string = mb_strlen($og_string);
	$rand_str = getCode(21);

	//echo "원문 : $og_string";
	$string = "";
	for($h=$h2=0;$h < $len_string;$h++){      //원문과 랜덤문을 썩는다.
		$h_str1 = substr($rand_str,$h2,1);
		$h_str2 = substr($og_string,$h,1);
		$string .= $h_str1.$h_str2;				
		if($h2==20) $h2=0;
		else $h2++;
	}
	$h_str1 = substr($rand_str,-1,1);
	$string .= $h_str1;

	$ba_string = base64_encode($string); // 1차 base64로 암호화
	$str_length=mb_strlen($ba_string);

	$lens=0;
	$cnt=1;
	while($lens < $str_length){      // p re vil 1,2,3,4,5,6,7,8,9 1,2.. 글자식 짤라 배열로저장	  
	  $en_str[] = substr($ba_string,$lens,$cnt);
	  $lens+=$cnt;
	  if($cnt==9) $cnt=1;
	  else $cnt++;
	}
         
	for($k=0;$k<count($en_str);$k++){         // 리버스 시킴
	  $en_str[$k] = strrev($en_str[$k]);
	}

	$k2=2;
	for($k=0;$k<count($en_str);$k++){         // 2차 base64로 암호화
	  $en_str[$k] = base64_encode($en_str[$k]);	 
	  //echo $en_str[$k]."<br>";
	  if($k2==2) $en_str[$k] = str_replace("==","",$en_str[$k]);
	  else if($k2==1) $en_str[$k] = str_replace("=","",$en_str[$k]);
	  
	  if($k2==0) $k2=2;
	  else $k2--;
	}

	$encode_string = join("",$en_str);   //조인

	//echo "<br>암호문 : $encode_str";       

	return $encode_string;

}

/*
###############################################
     :::  복호화 :::          
    사용방법 : previlDecode('암호문'); 
    ex) $decode_str = previlDecode('sdlkglsdgf') ;   
###############################################
*/

function previlDecode($encode_string){
    if(!$encode_string) return false;
	$de_length = mb_strlen($encode_string);
    
	$k_total=0;	
	$k1=2;
	$k2=2;
	
	for($k=0;$de_length > $k_total;$k++){         // 배열로 저장	  
	  $de_str[$k] = substr($encode_string,$k_total,$k1);
	  $k_total = $k_total + $k1;	  
	  if($k2==0) $k1++;
	  else if($k2==2 && ($de_length > $k_total)) $de_str[$k] .= "==";
	  else if($k2==1 && ($de_length > $k_total)) $de_str[$k] .= "=";	  
	  //echo $de_str[$k]."<br>";
	  $de_str[$k] =  base64_decode($de_str[$k]);  //복호화
	  if($k1==13) $k1=2;
	  else $k1++;	  
      if($k2==0) $k2=2;
	  else $k2--;
	}

	for($i=0;$i<$k;$i++){         // 리버스 시킴
	  $de_str[$i] = strrev($de_str[$i]);
	}

	$decode_str = join("",$de_str);   //조인
	$decode_str = base64_decode($decode_str);   // 복호화

	$len_string2 = mb_strlen($decode_str);
	
	$decode_string = "";
	for($h=1;$h < $len_string2;$h+=2){
		$h_str3 = substr($decode_str,$h,1);		
		$decode_string .= $h_str3;
	}

	//echo "<br> 복호문 : $decode_string";
    return $decode_string;
}


/*
#-----------------------------------------
| UUA: User Agent Analyser
| https://uaa.beranek.one
#-----------------------------------------
| made by beranek1
| https://github.com/beranek1
#-----------------------------------------
*/

if (!function_exists("array_key_last")) {
    function array_key_last($array) {
        if (!is_array($array) || empty($array)) {
            return NULL;
        }
        return array_keys($array)[count($array)-1];
    }
}

function analyse_user_agent($user_agent) {
    $result = array();
    $gecko = preg_match("/Mozilla\/\d[\d.]* \([A-Za-z0-9_.\- ;:\/]*\) Gecko\/\d+/i", $user_agent);
    $webkit = preg_match("/Mozilla\/\d[\d.]* \([A-Za-z0-9_.\- ;:\/]*\) AppleWebKit\/\d[\d.]* \(KHTML, like Gecko\)/i", $user_agent);
    
	if(preg_match_all("/\w+\/\d[\d.]*/", $user_agent, $matches)) {
        $browser = preg_split("/\//",$matches[0][array_key_last($matches[0])]);
        $trident = preg_match("/trident/i", $browser[0]) && !$gecko && !$webkit;
        if($webkit) {
            if(preg_match("/safari/i", $browser[0])) {
                $browser = preg_split("/\//",$matches[0][2]);				
                $i = 3;
                while((preg_match("/version/i", $browser[0]) || preg_match("/mobile/i", $browser[0])) && isset($matches[0][$i])) {
                    $browser = preg_split("/\//",$matches[0][$i]);
                    $i++;
                }
				if(preg_match("/AdsBot-Google/i", $user_agent)) $browser[0] = "AdsBot-Google"; 
				else if(preg_match("/ZumBot/i", $user_agent)) $browser[0] = "Zum"; 
				else if(preg_match("/PetalBot/i", $user_agent)) $browser[0] = "Petal";
            }
        }
    }
	
    if(preg_match("/\([A-Za-z0-9_.\- ;:\/]*\)/", $user_agent, $match)) {	
		if(preg_match("/; Mozilla/i", $user_agent)) {
			$tmps = explode("; Mozilla", $user_agent);
			preg_match("/\([A-Za-z0-9_.\- ;:\/]*\)/", $tmps[1], $match);						
		}
		$platforms = preg_split("/; /", preg_replace("/\)/", "", preg_replace("/\(/", "", $match[0])));					
		if(preg_match("/; Mozilla/i", $user_agent)) {			
			if(preg_match("/msie/i", $tmps[1])) {				
                $tmps2 = preg_split("/ \d/", preg_replace("/ nt/i", "",$platforms[1]));
                $tmps3 = preg_split("/ /",$platforms[1]);

				$browser[0] = $tmps2[0];
				$browser[1] = $tmps3[1];
				unset($tmps, $tmps2, $tmps3);
            } 
		}

        if($trident) {			
            $browser = preg_split("/ /",$platforms[1]);
            if(preg_match("/msie/i", $browser[0])) {
                $os = preg_split("/ \d/", preg_replace("/ nt/i", "",$platforms[2]));
                $osv = preg_split("/ /",$platforms[2]);
                if(preg_match("/xbox/i", $platforms[array_key_last($platforms)])) {
                    $result["device"]["name"] = $platforms[array_key_last($platforms)];
                }
            } else {
                $browser[0] = "msie";
                $version = preg_split("/:/", $platforms[array_key_last($platforms)]);
                $browser[1] = $version[1];
            }
        }	

        if(preg_match("/windows/i", $platforms[0])) {
			$platforms[0]	= str_replace("WindowsNT", "Windows NT", $platforms[0]);
            $os = preg_split("/ \d/", preg_replace("/ nt/i", "",$platforms[0]));
            $osv = preg_split("/ /",$platforms[0]);			

			$os[0] = "Windows";
			if($osv[0] == 'Windows') {				
				if(preg_match("/windows/i", $platforms[2])) $osv = preg_split("/ /",$platforms[2]);
				else $osv = preg_split("/ /",$platforms[0]);
			}			
            if(preg_match("/phone/i", $os[0])) {
                $result["device"]["name"] = $platforms[array_key_last($platforms)-1]." ".$platforms[array_key_last($platforms)];
            }
            if(preg_match("/xbox/i", $platforms[array_key_last($platforms)])) {
                $result["device"]["name"] = $platforms[array_key_last($platforms)];
            }
            if(isset($platforms[2]) && preg_match("/x\d[\d]*/", $platforms[2])) {
                $result["device"]["cpu"] = $platforms[2];
            }
        } else if(isset($platforms[2]) && preg_match("/windows/i", $platforms[2])) {
            $os = preg_split("/ \d/", preg_replace("/ nt/i", "",$platforms[2]));
            $osv = preg_split("/ /",$platforms[2]);
            if(preg_match("/phone/i", $os[0])) {
                $result["device"]["name"] = $platforms[array_key_last($platforms)-1]." ".$platforms[array_key_last($platforms)];
            }
            if(preg_match("/xbox/i", $platforms[array_key_last($platforms)])) {
                $result["device"]["name"] = $platforms[array_key_last($platforms)];
            }
            if(isset($platforms[9]) && preg_match("/x\d[\d]*/", $platforms[9])) {
                $result["device"]["cpu"] = $platforms[9];
            }
        }else if(preg_match("/linux/i", $platforms[0])) {
            $i = preg_match("/u/i", $platforms[1]) ? 2 : 1;
            $os = preg_split("/ \d/",$platforms[$i]);
            if(preg_match("/android/i", $os[0])) {
                $osv = preg_split("/ /",$platforms[$i]);
            } else {
                $os = preg_split("/ /",$platforms[0]);
                if(isset($os[1])) {
                    $result["device"]["cpu"] = $os[1];
                }
            }
            foreach ($platforms as $property) {
                if(preg_match("/build/i", $property)) {
                    $device = preg_split("/ build/i", $property);
                    $result["device"]["name"] = $device[0];
                }
            }
        } else if(preg_match("/linux/i", $platforms[1]) || preg_match("/cros/i", $platforms[1]) || preg_match("/ubuntu/i", $platforms[1])) {
            $os = preg_split("/ /",$platforms[1]);
            if(isset($os[1])) {
                $result["device"]["cpu"] = $os[1];
            }
        } else if(preg_match("/macintosh/i", $platforms[0])) {
            $os = preg_split("/ \d/",preg_replace("/intel /i", "", $platforms[1]));
            $osv = preg_split("/ /",$platforms[1]);
            $result["device"]["name"] = $platforms[0];
        } else if(preg_match("/iphone/i", $platforms[0]) || preg_match("/ipad/i", $platforms[0]) || preg_match("/ipod/i", $platforms[0])) {
            $os = preg_split("/ \d/",preg_replace("/cpu /i", "", $platforms[1]));
            $osv = preg_split("/ /", preg_replace("/ like mac os x/i", "", $platforms[1]));
            $result["device"]["name"] = $platforms[0];
        } else if(preg_match("/android/i", $platforms[0])) {
            $os = preg_split("/ \d/",$platforms[0]);
            $osv = preg_split("/ /",$platforms[0]);
            $result["device"]["name"] = $platforms[1];
        } else if(preg_match("/googlebot/i", $platforms[0])) {
            $os = "GoogleBot";            
        } else if(preg_match('/bot|crawler/i', $user_agent)) {
            $os[0] = "ETC Robot";            
        } else $os[0] = "ETC";

		if(isset($os)) {
            $result["os"]["name"] = $os[0];
        }

        if(isset($osv)) {
            $ver	= $osv[array_key_last($osv)];
			if(preg_match('/./', $ver)) {
				$ver = explode(".", $ver);
				$ver = $ver[0];
			}
			
			if(preg_match('/_/', $ver)) {
				$ver = explode("_", $ver);
				$ver = $ver[0];
			}

			$ver	= str_replace('NT', '', $ver);

			$result["os"]["version"] = $ver;			
        }
    }
	else {		
		if((preg_match('/bot|Yeti|searches|Barkrowler|facebookexternalhit|kakaotalk-scrap|Daum|NetcraftSurveyAgent/i', $user_agent))) $result["os"]["name"] = "ETC Robot";
		else $result["os"]["name"] = "ETC";
		$result["os"]["version"] = "";
	}

    if(isset($browser)) {		
		if($browser[0] == 'Edg') $browser[0] = "MS Edge";
		else if($browser[0] == 'Yeti') $browser[0] = "Naver";
		else if($browser[0] == 'Blueno') $browser[0] = "NaverScrap";
		else if($browser[0] == 'CensysInspect') $browser[0] = "Censys";
		else if($browser[0] == 'faq' && preg_match('/daum/i', $user_agent)) $browser[0] = "Daum";
		else if($browser[0] == 'answer' && preg_match('/Google-Read-Aloud/i', $user_agent)) $browser[0] = "Google-Read-Aloud";

		$browser[0]	= preg_replace("/bot/i", "", $browser[0]);

        $result["browser"]["name"] = $browser[0];
        $result["browser"]["version"] = $browser[1];
    }
	else {
		if((preg_match('/AdsBot-Google/i', $user_agent))) $result["browser"]["name"] = "AdsBot-Google";
		else if((preg_match('/Yeti/i', $user_agent))) $result["browser"]["name"] = "Naver";		
		else if((preg_match('/daum/i', $user_agent))) $result["browser"]["name"] = "Daum";
		else $result["browser"]["name"] = "ETC";
		$result["browser"]["version"] = "";
	}

    $result["is_mobile"] = preg_match('/mobile/i', $user_agent) ? 1 : 0;
    $result["is_bot"] = (preg_match('/bot|crawler|Yeti|Daum|searches|Blueno|CensysInspect|Google-Read-Aloud/i', $user_agent)) ? 1 : 0;

    return $result;
}



/*
###############################################
     ::: 파일 중복체크  함수 :::          
    사용방법 : proc_file_dup($savedir, $filename);  
    ex) file_name = proc_file_dup('./files', 'test.gif');  
###############################################
*/

function proc_file_dup($savedir, $filename) {
	global $incNum;
    
	// 먼저 들어온 파일명의 앞부분과 확장자 부분을 분리한다. 
    $lenStr		= mb_strlen($filename);				// 파일 길이 
    $extension	= strrchr($filename, ".");			// 파일의 확장자.(. 포함) 
    $hyPos		= strrpos($filename, "_");          // 맨 마지막 _의 위치 
	$dotPos		= strrpos($filename, ".");          // 맨 마지막 도트의 위치 
    $file		= substr($filename, 0, $dotPos);    // 확장자와 점을 뺀 파일명 
  	
	if($hyPos && $incNum) {
		$ea		= $dotPos - $hyPos;
		$incNum = substr($filename, $hyPos+1,$ea-1);
		$file =	 substr($filename,0,$hyPos);	
	} 
	else $incNum = 0;                           // 중복 파일명이 계속 들어올 때마다 하나씩 확장되는 숫자 
    
    $incNum++; 

	$filename = $file."_".$incNum .$extension; 
        
    if(file_exists("{$savedir}/{$filename}")) {  // recursive call 
        $filename = proc_file_dup($savedir, $filename); 
        return $filename; 
    } 
    else return $filename; 
} 



/*
###############################################
     ::: 파일 업로드 함수 :::          
    사용방법 : upFile(업로드파일, 업로드파일명, 저장위치, 이미지만허용, 저장파일명(공백일 경우 업로드파일명으로저장), 에러출력타입);
    ex) $upfile = upFile($userfile, $userfile_name, $save_dir, 1 );
###############################################
*/

function upFile($userfile, $userfile_name, $savedir, $img="", $save_name="", $log="", $limit_size=""){
    
	// 확장자 검사
    if(!preg_match("/\.jpg|\.jpeg|\.gif|\.png|\.swf|\.bmp/i",$userfile_name) && $img) {
		if($log==1) logMsg("이미지 파일만 등록 하실 수 있습니다.");
		else if($log==2) json_error_msg('이미지 파일만 등록 하실 수 있습니다.');
		else Error("이미지 파일만 등록 하실 수 있습니다.");
    }

	if($img && !exif_imagetype($userfile)) {
		if($log==1) logMsg("정상적인 이미지 파일이 아닙니다.");
		else if($log==2) json_error_msg('정상적인 이미지 파일이 아닙니다.');
		else Error("정상적인 이미지 파일이 아닙니다.");
	}

	if(preg_match("/\.php|\.inc|\.htm|\.phtm|\.shtm|\.ztx|\.dot|\.cgi|\.pl|\.asp|\.jsp/i",$userfile_name)) {
		if($log==1) logMsg("Html, PHP 관련파일은 업로드 할 수 없습니다.");
		else if($log==2) json_error_msg('Html, PHP 관련파일은 업로드 할 수 없습니다.');
		else Error("Html, PHP 관련파일은 업로드 할 수 없습니다.");
	}
    
    // 서버환경 파일용량 제한 
	$ck_file	= 0;
	$file_size	= filesize($userfile);
	if(!$file_size) $ck_file = 1;
	else if($limit_size) {
		if(floor($file_size / 1024 / 1024) > $limit_size) $ck_file = 1;
	}

	if($ck_file == 1) {
		if($log==1) logMsg("파일 제한용량을 초과 했습니다.");
		else if($log==2) json_error_msg('파일 제한용량을 초과 했습니다.');
		else Error("파일 제한용량을 초과 했습니다.");
	}
  
	if(!$save_name) {
		// 파일 중복시 파일병 변경
		$userfile_name = str_replace(" ", "_", $userfile_name);
		if(@is_file("{$savedir}/{$userfile_name}"))   {   
			 $filename = $userfile_name;
			 $userfile_name = proc_file_dup($savedir, $filename); 
		}
    } 
	else {
		$userfile_name=$save_name.".".getExtension($userfile_name);
    }

	//파일 업로드
	if(!move_uploaded_file($userfile, $savedir."/".$userfile_name)){
		if($log==1) logMsg("파일을 저장한 디렉토리에 복사하는데 실패했습니다.");
		else if($log==2) json_error_msg('파일을 저장한 디렉토리에 복사하는데 실패했습니다.');
		else Error("파일을 저장한 디렉토리에 복사하는데 실패했습니다.");
	} 
     
    return $userfile_name;

}

/*
##############################################
    ::: 이미지 사이즈 조절 :::          
    사용방법 : imgSizeCh('이미지 경로', '이미지파일', '고정사이즈', '세로 맥스사이즈', '가로 맥스사이즈', '이미지명'); 
##############################################
*/

function imgSizeCh($dir, $file, $size_factor = "", $y_size = "", $x_size = "", $title = "", $moddate = ""){
	global $t;

	if($title) $title = "alt='{$title}' title='{$title}'";
	else $title = "alt=''";

	if(strlen($moddate) == 0) $moddate = $t;
	
	if($size_factor == 0 && !$y_size && !$x_size) {
		return "<img src='{$dir}".urlencode($file)."?t={$moddate}' border='0' {$title} />"; 
    }

	if(!$size = @GetImageSize($dir.$file)) return false; 
    if($size[0] == 0 ) $size[0] = 1; 
    if($size[1] == 0 ) $size[1] = 1; 
    
	if($x_size && $y_size) {
		$size[0] = $x_size;
		$size[1] = $y_size;
		$per = 1;
	}
	else if($y_size) {
		if($size[1] > $y_size) $per = $y_size / $size[1];
		else $per = 1;
    } 
	else if($x_size) {
		if($size[0] > $x_size) $per = $x_size / $size[0];
		else $per = 1;
	}
	else {
		if($size[0] > $size_factor || $size[1] > $size_factor){
			if($size[0]>$size[1]) { $per = $size_factor / $size[0]; }  
			else { $per = $size_factor / $size[1]; } 
		} 
		else  $per=1;    
    }

	$x_size = intVal($size[0] * $per); 
    $y_size = intVal($size[1] * $per); 

	return "<img src='{$dir}".urlencode($file)."?t={$moddate}' width='{$x_size}' height='{$y_size}' {$title} />"; 	

}

/*
##############################################
    ::: 이미지 사이즈  :::          
    사용방법 : getImgSize('이미지 경로', '이미지파일', '고정사이즈', '세로 맥스사이즈'); 
##############################################
*/

function getImgSize($dir, $file, $size_factor = "", $y_size = ""){
	
	if($size = @GetImageSize($dir.$file)) {
		if($size[0] > $size_factor) $per = $size_factor / $size[0];
		else $per = 1;        
		
		$rsize[0] = intval($size[0] * $per); 
		if($y_size) $rsize[1] = $y_size;
		else $rsize[1] = intval($size[1] * $per); 
	}
	return $rsize;

}


/*
###############################################
     ::: 공백문자열 검사함수 :::          
    사용방법 : chrtrim('문자l'); 
    ex) chrtrim($chr); 
###############################################
*/

function chrtrim($chr) { 
	
	$chr = trim($chr);
	$chr_length = mb_strlen($chr);
	if($chr_length <= 0) alert('올바른문자를 입력해주세요!','back');

}


/*
###############################################
     ::: 속도계산 함수 :::          
    사용방법 : getMicotime('처음시간','다음시간'); 
###############################################
*/

function getMicrotime($old, $new) {
   
	$old = explode(" ", $old);  //주어진 문자열을 나눔 (sec, msec으로 나누어짐)
    $new = explode(" ", $new);
    $time[msec] = $new[0] - $old[0];
    $time[sec]  = $new[1] - $old[1];
    if($time[msec] < 0) {
		$time[msec] = 1.0 + $time[msec];
		$time[sec]--;
    }

    $time = sprintf("%.3f", $time[sec] + $time[msec]);

    return $time;

}


/*
###############################################
     ::: 단위절사  :::          
    사용방법 : numberLimit('금액','절사단위'); 
    ex) $price = numberLimit('1001','1'); -> 1000;
###############################################
*/

function numberLimit($num,$lmt) { 

    $num = round($num/(10*$lmt));
	$num = $num*(10*$lmt);
    return $num;

} 


/*
###############################################
     ::: 확장자 빼오기 (소문자로 치환) :::          
    사용방법 : getExtension('파일이름'); 
    ex) $last_name = getExtension('test.php');
###############################################
*/

function getExtension($filename) { 

    $filename = trim($filename); 
    $right = strrchr($filename, "."); 
    return strtolower(substr($right,1)); 

} 


/*
###############################################
     ::: 파일사이즈 변환 (kb,mb..) :::          
    사용방법 : getFilesize('파일사이즈'); 
    ex) $filesize = getFilesize('$size');
###############################################
*/

function getFilesize($filename) {

    if(!file_exists($filename)) return "0 Byte";
	$size = filesize($filename);		
	if(!$size) return "0 Byte";
	if($size<1024) { 
		return ($size."Byte");
	} 
	else if($size >1024 && $size< 1024 *1024)  {
		return sprintf("%0.1fKB",$size / 1024);
	}
	else return sprintf("%0.2fMB",$size / (1024*1024));

}


/*
###############################################
     :::  파일다운로드 함수 :::          
    사용방법 : fileDown('풀경로','파일이름')
    ex) fileDown($filename,$dfilename);   
###############################################
*/

function fileDown($filename, $dfilename) {

	if(preg_match("/(MSIE 5.0|MSIE 5.1|MSIE 5.5|MSIE 6.0)/i", $HTTP_USER_AGENT))
	{ 
	  if(strstr($HTTP_USER_AGENT, "MSIE 5.5")) 
	  { 
		header("Content-Type: doesn/matter"); 
		header("Content-disposition: filename={$dfilename}"); 
		header("Content-Transfer-Encoding: binary"); 
		header("Pragma: no-cache"); 
		header("Expires: 0"); 
	  } 

	  if(strstr($HTTP_USER_AGENT, "MSIE 5.0")) 
	  { 
		Header("Content-type: file/unknown"); 
		header("Content-Disposition: attachment; filename={$dfilename}"); 
		header("Pragma: no-cache"); 
		header("Expires: 0"); 
	  } 

	  if(strstr($HTTP_USER_AGENT, "MSIE 5.1")) 
	  { 
		Header("Content-type: file/unknown"); 
		header("Content-Disposition: attachment; filename={$dfilename}"); 
		header("Pragma: no-cache"); 
		header("Expires: 0"); 
	  } 
	  
	  if(strstr($HTTP_USER_AGENT, "MSIE 6.0"))
	  {
		Header("Content-type: application/x-msdownload"); 
		Header("Content-Length: ".(string)(filesize($filename)));
		Header("Content-Disposition: attachment; filename={$dfilename}");   
		Header("Content-Transfer-Encoding: binary");   
		Header("Pragma: no-cache");   
		Header("Expires: 0");   
	  }
	} 
	else { 
		header("Content-Type:application/octet-stream");
		header("Content-Disposition:attachment;filename={$dfilename}");
		header("Content-Transfer-Encoding:binary");
		header("Content-Length:".(string)(filesize($filename)));
		header("Cache-Control:cache,must-revalidate");
		header("Pragma:no-cache");
		header("Expires:0");
	} 

	if (is_file($filename)) { 	  
		$fp = fopen("$filename", "rb");
		if(!fpassthru($fp)) fclose($fp);
	} 
	else { 
		logMsg("파일을 찾을 수 없습니다."); 
	}
}


/*
###############################################
     :::  폴더 및 하위 파일 삭제 :::          
    사용방법 : delTree('폴더경로')
    ex) delTree($path)
###############################################
*/

function delTree($path) {

    if (is_dir($path)) {		
		if (version_compare(PHP_VERSION, '5.0.0') < 0) {
			$entries = array();
			if ($handle = opendir($path)) {
				while (false !== ($file = readdir($handle))) $entries[] = $file;

				closedir($handle);
			}
			} else {
			$entries = scandir($path);
			if ($entries === false) $entries = array(); // just in case scandir fail...
		}

		foreach ($entries as $entry) {
			if ($entry != '.' && $entry != '..') {
				deltree($path.'/'.$entry);
			}
		}

		return rmdir($path);
	} 
	else return @unlink($path);
}


/*
###############################################
     :::  폴더 및 하위 파일 복사 :::          
    사용방법 : copyTree('원본폴더경로', '타겟폴더경로')
    ex) copyTree( $source, $target )
###############################################
*/

function copyTree( $source, $target ) {
    if ( is_dir( $source ) ) {
        @mkdir( $target );
            
        $d = dir( $source );
            
        while ( FALSE !== ( $entry = $d->read() ) ) {
            if ( $entry == '.' || $entry == '..' )continue;
                
            $Entry = $source . '/' . $entry;            
            if ( is_dir( $Entry ) ) {
                copyTree( $Entry, $target . '/' . $entry );
                continue;
            }
            @copy( $Entry, $target . '/' . $entry );
        }
            
        $d->close();
    }
	else @copy( $source, $target );        
}


/*
 * Description: chmod recursive.
 * Code-Base: official documentation php.net chmod function
 * Link: http://www.php.net/manual/en/function.chmod.php#105570
 * 
 * Example usage :
 * chmod_R( 'mydir', 0666, 0777);
 * 
 */

function chmod_R($path, $filemode, $dirmode) {
    if (is_dir($path) ) {
        if (!chmod($path, $dirmode)) {
            $dirmode_str=decoct($dirmode);
            print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
            print "  `-> the directory '$path' will be skipped from recursive chmod\n";
            return;
        }
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if($file != '.' && $file != '..') {  // skip self and parent pointing directories
                $fullpath = $path.'/'.$file;
                chmod_R($fullpath, $filemode,$dirmode);
            }
        }
        closedir($dh);
    } else {
        if (is_link($path)) {
            print "link '$path' is skipped\n";
            return;
        }
        if (!chmod($path, $filemode)) {
            $filemode_str=decoct($filemode);
            print "Failed applying filemode '$filemode_str' on file '$path'\n";
            return;
        }
    }
}


/*
###############################################
     ::: exif_imagetype 내장 함수 체크   :::         
###############################################
*/

if (!function_exists( 'exif_imagetype' ) ) {
    function exif_imagetype ( $filename ) {
        if ( ( list($width, $height, $type, $attr) = getimagesize( $filename ) ) !== false ) {
            return $type;
        }
		return false;
    }
} 


/*
###############################################
     :::  소켓전송 :::          
    사용방법 : socketPost("주소", "잔송타입(GET, POST)", "결과값리턴여부")
###############################################
*/

function socketPost($url, $type = 'GET', $return = 1) { 
	
	$parts			= parse_url($url);

	$query			= isset($parts['query']) ? $parts['query'] : ""; 
	if($parts['scheme'] == 'http') {
		$port		= isset($parts['port']) ? $parts['port'] : 80;
		$protocol	= "";
	}
	else if($parts['scheme'] == 'https') {
		$port	= isset($parts['port']) ? $parts['port'] : 443;
		$protocol	= "ssl://";
	}
	else return 0;

	$fp				= fsockopen("{$protocol}".$parts['host'], $port);    
	if(!$fp) return 0;

	stream_set_timeout($fp, 5);

    if($type == 'GET') $parts['path'] .= '?'.$query;
  
    fputs($fp, "{$type} {$parts['path']} HTTP/1.1\r\n");
	fputs($fp, "Host: {$parts['host']}\r\n");
	fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
	fputs($fp, "Referer:".$_SERVER["HTTP_HOST"]."\r\n");
	fputs($fp, "Content-length: ".strlen($query)."\r\n");
	fputs($fp, "Connection: close\r\n\r\n");
	fputs($fp, $query."\r\n\r\n");

	if($return == 1) {
		$response = array(); 
		while (!feof($fp)) { 
			$response[] = fgets($fp, 128); 			
		}   
	}     
    
    fclose($fp); 
	
	if($return == 1) return $response;
}


/*
###############################################
     :::  외부 이미지 가져오기 :::          
    사용방법 : getURLimg("이미지 주소")
###############################################
*/

function getURLimg($url) {

	if(substr($url, 0, 7) != "http://") {
		if(substr($url,0,8) != "https://") $url = "http://".$url;
	}

	$url	= explode("?", $url);
	$url	= $url[0];

	if(!preg_match("/\%/i", $url)) {
		if(preg_match("/ /i", $url)) $url = str_replace(' ','%20', $url);

		$url = urlencode($url);
		$url = str_replace('%3A',':', $url);
		$url = str_replace('%2F','/', $url);
		$url = str_replace('%2520','%20', $url);
	}
	else {
		$url = str_replace('%3A',':', $url);
		$url = str_replace('%2F','/', $url);
		$url = str_replace('%2520','%20', $url);
	}

	$header_data ="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36";
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url); //URL 지정하기
	curl_setopt($ch, CURLOPT_HEADER, 0); 
	curl_setopt($ch, CURLOPT_USERAGENT,$header_data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_NOSIGNAL,1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,1); 
	$result=curl_exec($ch); 
	curl_close ($ch);   	
	
	return $result;
}

/*
###############################################
     :::  외부로 데이터 전송 :::          
    사용방법 : getSendCurl("주소")
###############################################
*/

function getSendCurl($url) {
	if(!$url) return;
		
	if(substr($url, 0, 7) != "http://") {
		if(substr($url,0,8) != "https://") $url = "http://".$url;
	}
	
	$headers		= array('accept: application/json;charset=UTF-8');
	$agent			="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36";
	
	try {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_USERAGENT,$agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if(curl_error($ch)) {
		  return curl_error($ch);
	    }

		$result = curl_exec($ch);
		curl_close($ch);

		return $result;

	} 
	catch (Exception $err) {
		return $err;
	}
}


/*
###############################################
     :::  한글포함 여부확인 :::          
    사용방법 : chkHan("문자열")
###############################################
*/

function chkHan($str) { 
	
	$strCnt=0;
	while(mb_strlen($str) >= $strCnt) {
		$char = ord($str[$strCnt]);
		if($char >= 0xa1 && $char <= 0xfe) return true;
		$strCnt++;
	}
	return false;
}


/*
###############################################
     :::  남은 시간 출력 :::          
    사용방법 : countDown("요청타임")
###############################################
*/

function countDown($times){
	$lastTime = $times - time();	
	
	if($lastTime>0) {
		$day = floor($lastTime/86400);
		$lastTime -= $day * 86400;
		$hour = floor($lastTime/3600);
		$lastTime -=  $hour * 3600;
		$min  = floor($lastTime/60);
		$lastTime -=  $min * 60;
		$sec = floor($lastTime); 
		
		$day	= ($day>9) ? $day : '0'.$day;
		$hour	= ($hour>9) ? $hour : '0'.$hour;
		$min	= ($min>9) ? $min : '0'.$min;
		$sec	= ($sec>9) ? $sec : '0'.$sec;

		return "{$day}:{$hour}:{$min}:{$sec}";
	}
	else return "00:00:00:00";
}


/*
###############################################
     :::  POST전송값 |*| 조인 후 리턴 :::          
    사용방법 : multiPostVar("POST 확인용 문자", "POST 확인용 공통문자")
	ex) $_POST['payment_bank_info']	= multiPostVar(array("001", "002"), array('payment_bank_name','payment_bank_num'));
###############################################
*/

function multiPostVar($var, $rtn_var) {
	$arr = array();

	for($i = 0, $cnt = count($var); $i < $cnt; $i ++) {
		$arr2 = array();
		for($j = 0, $cnt2 = count($rtn_var); $j < $cnt2; $j ++) {			
			$arr2[]	= isset($_POST[$rtn_var[$j].$var[$i]]) ? $_POST[$rtn_var[$j].$var[$i]] : "";
		}
		$arr[] = join("|", $arr2);
	}
	
	if(count($arr) > 0) return join("|*|",$arr);
	else return "";
}


/*
###############################################
     :::  썸네일 생성 :::          
    사용방법 : createThumbnail("원본이미지", "썸네일 사이즈", "가로세로비율 고정 여부")	
###############################################
*/

function createThumbnail($file, $size = '100', $fixed = false) { 
	
	// 1 = GIF, 2 = JPEG, 3 = PNG		
	if(file_exists($file)) {              
		$type = getimagesize($file); 
	 
		if(!function_exists('imagegif') && $type[2] == 1) $error = 'Filetype not supported. Thumbnail not created.'; 
		elseif (!function_exists('imagejpeg') && $type[2] == 2) $error = 'Filetype not supported. Thumbnail not created.'; 
		elseif (!function_exists('imagepng') && $type[2] == 3) $error = 'Filetype not supported. Thumbnail not created.'; 
		else {     			
			if($type[2] == 1) $image = imagecreatefromgif($file); 
			elseif($type[2] == 2) $image = imagecreatefromjpeg($file); 
			elseif($type[2] == 3) $image = imagecreatefrompng($file); 
		   
			if(function_exists('imageantialias')) imageantialias($image, TRUE); 
 
			$image_attr = getimagesize($file); 
 
			if($image_attr[0] > $image_attr[1] || $fixed=='w') { 
				$image_width = $image_attr[0]; 
				$image_height = $image_attr[1]; 
									
				if($fixed && $fixed!='w') { 
					$image_new_width  = $size; 
					$image_new_height = (int)($size * 3 / 4); // 4:3 ratio 
				} 
				else { 
					$image_new_width = $size;          
					$image_ratio = $image_width / $image_new_width; 
					$image_new_height = (int) ($image_height / $image_ratio); 
				} 				
			}
			else {
				$image_width = $image_attr[0]; 
				$image_height = $image_attr[1]; 
				if($fixed) { 
					$image_new_height = $size; 
					$image_new_width  = round($size * 3 / 4); // 3:4 ratio 
				} 
				else { 
					$image_new_height = $size; 
											 
					$image_ratio = $image_height / $image_new_height; 
					$image_new_width = round($image_width / $image_ratio); 
				} 				
			}
			
			$thumbnail = imagecreatetruecolor($image_new_width, $image_new_height); 
			if($type[2]==3) {
				$black = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127 ); // 배경색 (완전투명) 
				imagesavealpha($thumbnail, true ); 
				imagefill($thumbnail,0,0,$black); 
				imagefilledrectangle($thumbnail, 0, 0, 399, 299, $black ); 
			}			

			@imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $image_attr[0], $image_attr[1]); 
			 
			$thumb = preg_replace('!(\.[^.]+)?$!', "_{$size}".'$1', basename($file), 1); 
			$thumbpath = str_replace(basename($file), $thumb, $file); 

			if($type[2] == 1) { 
				if (!imagegif($thumbnail, $thumbpath)) $error = 'Thumbnail path invalid';                     
			} 
			elseif($type[2] == 2) { 
				if (!imagejpeg($thumbnail, $thumbpath)) $error = 'Thumbnail path invalid';                     
			}    
			elseif($type[2] == 3) { 
				if (!imagepng($thumbnail, $thumbpath)) $error = 'Thumbnail path invalid';                     
			}    
		} 
	} 
	else { 
		$error = 'File not found'; 
	} 
 
	if(!empty($error)) return ''; // die($error); 
	else return $thumbpath; 	 
} 


/*
###############################################
     :::  엑셀칼럼용 :::          
    사용방법 : column_char("번호");
###############################################
*/

function column_char($i) {
	return chr( 65 + $i );
}


/*
###############################################
     :::  문자 자소분리 in PHP :::
    사용방법 : getJamoCodes('문자')

	'ㄱ','ㄲ','ㄴ','ㄷ','ㄸ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ';//초성 19개 
	'ㅏ','ㅐ','ㅑ','ㅒ','ㅓ','ㅔ','ㅕ','ㅖ','ㅗ','ㅘ','ㅙ','ㅚ','ㅛ','ㅜ','ㅝ','ㅞ','ㅟ','ㅠ','ㅡ','ㅢ','ㅣ';//중성 21개 
	'ㄱ','ㄲ','ㄳ','ㄴ','ㄵ','ㄶ','ㄷ','ㄹ','ㄺ','ㄻ','ㄼ','ㄽ','ㄾ','ㄿ','ㅀ','ㅁ','ㅂ','ㅄ','ㅅ','ㅆ','ㅇ','ㅈ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ');//종성 28개 
###############################################
*/

function JS_charAt($str, $index) {
  return mb_substr($str, $index, 1, 'UTF-8');
}

function JS_StringLength($string) {
    return mb_strlen(iconv('UTF-8', 'UTF-16LE', $string)) / 2;
}

function JS_charCodeAt($str, $index){
    $char = mb_substr($str, $index, 1, 'UTF-8');
    if (mb_check_encoding($char, 'UTF-8'))
    {
        $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
        return hexdec(bin2hex($ret));
    } else {
        return null;
    }
}

function getJamoCodes($str) { 

	$c = JS_charCodeAt($str, 0);

	if($c < 0x3130) $c = 0;
	else if($c < 0x3164) $c = $c-0x3130;
	else if($c < 0xac00) $c = 0;
	else if($c < 0xd7a5) $c = $c+68;
	else $c = 0;

	$ck_arr = Array('','1','2','1,10','3','3,13','3,19','4','6','6,1','6,7','6,8','6,10','6,17','6,18','6,19','7','8','8,10','10','11','12','13','15','16','17','18','19');

	if($c > 51) {
		$a1 = ($c - $c % 588) / 588 - 74;
		$a2 = (($c - $c % 28) / 28 ) % 21 + 1;
		$a3 = $c % 28;
	}
	else {
		if($c < 3) $a1 = $c;
		else if($c < 4) $a1 = 0;
		else if($c < 5) $a1 = $c - 1;
		else if($c < 7) $a1 = 0;
		else if($c < 10) $a1 = $c - 3;
		else if($c < 17) $a1 = 0;
		else if($c < 20) $a1 = $c - 10;
		else if($c < 21) $a1 = 0;
		else if($c < 31) $a1 = $c - 11;
		else $a1 = 0;

		if($c < 31) $a2 = 0;
		else $a2 = $c - 30;

		$a3 = 0;	
	}

	$arr_var =  Array($a1, $a2, $a3); 

	if($arr_var[0]==0) return '';
	$rtn_value = $arr_var[0];

	if($arr_var[1]>0) {
		$rtn_value = $rtn_value .",".($arr_var[1]);	
		if($arr_var[2]) {		
			if($ck_arr[$arr_var[2]]) $arr_var[2] = $ck_arr[$arr_var[2]];		
			$rtn_value = $rtn_value .",".($arr_var[2]);
		}
	}
	
	return $rtn_value;
}


function getJamoStr($str) { 
	
	$return = "";
	
	for($i = 0, $cnt = mb_strlen($str); $i < $cnt; $i++){
		$ch = JS_charAt($str, $i);
		if($tmp = getJamoCodes($ch)) {
			if($i==0) $return = $tmp;	
			else $return .= ','.$tmp;	
		} 
		else {
			$return .= $ch;
		}
	}	
	return $return;
}

function ekKeyTypeConvert($str) {
	$eng_key		= array('r', 'R', 's', 'e', 'E', 'f', 'a', 'q', 'Q', 't', 'T', 'd', 'w', 'W', 'c', 'z', 'x', 'v', 'g', 'k', 'o', 'i', 'O', 'j', 'p', 'u', 'P', 'h', 'y', 'n', 'b', 'm', 'l', 'hk', 'ho', 'hl', 'nj', 'bp', 'nl', 'ml');
	$kor_key		= array('ㄱ', 'ㄲ', 'ㄴ', 'ㄷ', 'ㄸ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅃ', 'ㅅ', 'ㅆ', 'ㅇ', 'ㅈ', 'ㅉ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ', 'ㅏ', 'ㅐ', 'ㅑ', 'ㅒ', 'ㅓ', 'ㅔ', 'ㅕ', 'ㅖ', 'ㅗ', 'ㅛ', 'ㅜ', 'ㅠ', 'ㅡ', 'ㅣ', 'ㅘ', 'ㅙ', 'ㅚ', 'ㅝ', 'ㅞ', 'ㅟ', 'ㅢ');
	$kor_key_cho	= array('', 'ㄱ','ㄲ','ㄴ','ㄷ','ㄸ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ');
	$kor_key_jung	= array('', 'ㅏ','ㅐ','ㅑ','ㅒ','ㅓ','ㅔ','ㅕ','ㅖ','ㅗ','ㅘ','ㅙ','ㅚ','ㅛ','ㅜ','ㅝ','ㅞ','ㅟ','ㅠ','ㅡ','ㅢ','ㅣ');

	$key_match1 = array();
	$key_match2 = array();

	foreach($eng_key as $k => $v) {
		$key_match1[$v]				= $kor_key[$k];
		$key_match2[$kor_key[$k]]	= $eng_key[$k];
	}	
	
	$kor_key_jung2 = array();
	foreach($kor_key_jung as $k => $v) {
		$kor_key_jung2[$v] = $k;
	}
	
	$return = "";
	for($i = 0, $cnt = mb_strlen($str); $i < $cnt; $i++){
		$ch = JS_charAt($str, $i);	
		if($tmp = getJamoCodes($ch)) {			
			$tmp	= explode(",", $tmp);
			$tmp2	= array();
			foreach($tmp as $k => $v) {				
				if($k == 1) $tmp2[] = $key_match2[$kor_key_jung[$v]];
				else $tmp2[] = $key_match2[$kor_key_cho[$v]];
			}			
			$return .= join("", $tmp2);			
		} 
		else {			
			$tmp = isset($key_match1[$ch]) ? getJamoStr($tmp) : '';			
			if($tmp) {
				if(!is_numeric($tmp)) $tmp = $kor_key_jung2[$tmp];
				if($i==0) $return = $tmp;
				else $return .= ','.$tmp;
			}
			else {
				if(isset($kor_key_jung2[$ch])) {
					$tmp = $kor_key_jung2[$ch];
					if(isset($kor_key_jung[$tmp])) $tmp = $key_match2[$kor_key_jung[$tmp]];
					else if(isset($kor_key_cho[$tmp])) $tmp = $key_match2[$kor_key_cho[$tmp]];
					$return .= $tmp;	
				}
				return;
			}
		}
	}	
	
	return $return;
}

?>