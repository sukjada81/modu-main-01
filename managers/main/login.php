<?php

if(substr($_SERVER['HTTP_HOST'], -1) == '.') {
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

define('DEFAULT_PATH',		'');
define('PATH_LIB',			'../../lib');
define('PATH_INCLUDE',		'../../include');

include_once(PATH_INCLUDE.'/config.php');   
include_once(PATH_INCLUDE.'/dbconfig.php');   
include_once(PATH_LIB.'/class.Mysql.php');   

$ver	= SHOP_VERSION;
$s_id	= isset($_COOKIE['s_id']) ? $_COOKIE['s_id'] : '';
if($s_id) {
	define('PATH_LIB', '../../lib');
	include_once(PATH_LIB.'/lib.Function.php');  

	$SAVEID		= previlDecode($s_id);
	$checkedsid = "checked='checked'";
}
else $SAVEID = $checkedsid = "";

$mysql	= new mysqlClass();

$sql		= "SELECT member_admin_auth FROM mallRN_configuration WHERE uid = 2";
$ADMIN_TUTH	= stripslashes($mysql->get_one($sql));
$ACTION		= "login_ok.php";
if($ADMIN_TUTH != 'N') $ACTION		= "";

?>
<!doctype html> 
<html lang="ko">
<head>
<title>관리자페이지 로그인</title>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />

<link type="text/css" rel="StyleSheet" href="../common/style.css?ver=<?php echo $ver; ?>" />
<link type="text/css" rel="stylesheet" href="../../lib/alertify.css?ver=<?php echo $ver; ?>" />

<style>
	.inputMessageBox { font-family: 'SCDream', sans-serif; z-index:9991; display:block; position:absolute; top:-28px; left:32px; }
	.inputMessageBox .inputMessage {  background-color:#FE1A00; border-radius:5px; padding:6px 10px; color:#fff; font-size:1.2em; font-family: 'SDMiSaeng', sans-serif; }
	.inputMessageBox .inputMessage_arrow { position:relative;  top:-1px; left:-40px; width: 0; height: 0; border-left: 7px solid transparent; border-right: 7px solid transparent; border-top: 7px solid #FE1A00; }	
	
	.inputBox .authDiv { display:none; }
	.inputBox .authSelect { margin-bottom:0px; background:#f3f3f3; width:calc(100% - 104px); padding:10px 20px 20px 20px; font-family: 'SCDream', sans-serif; font-weight:300; font-size:13px; color:#333; }
	.inputBox .authSelect p { text-align:left; padding-top:10px; }
	.inputBox .authSelect p.help { padding:6px 0 0 32px; color:#999 }
	.inputBox .auth_code_time { position:absolute; right:100px; top:23px; color:#999; font-family: 'SCDream', sans-serif; font-weight:300; font-size:11px; }
	
</style>

<script type="text/javascript" src="//code.jquery.com/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="../../lib/lib.Function.js?ver=<?php echo $ver; ?>"></script>
<script type="text/javascript" src="../../lib/alertify.js?ver=<?php echo $ver; ?>"></script>
<script type="text/javascript" src="../../lib/clipboard.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<!--[if lte IE 8]><script type="text/javascript" src="../../lib/excanvas.js"></script><![endif]-->
</head>

<body style="min-width:660px; background-color:#f48042">
	
<form method="post" name="loginForm" action="<?php echo $ACTION; ?>" target='HFrm' data-submit-proc='submitProc'>

<center>
	<div class="loginBg1 alignLeft fontRoboto colorWhite size08">
		<span class="small-title">더도매</span><span class="size10"></span>	
	</div>
	<div class="loginBg2">
		<div class="loginTitle fontRoboto">
			<span class="textShadow size30" >Administrator Login Page!</span><br /><br />
			<span class="size14">더도매 ShoppingMall</span>
		</div>
	</div>
	<div class="loginBg3"></div>

	<div id="loginBox" >
		<div>
			<div class="fontSCDream size18 textShadowL">관리자 로그인</div>
		</div>

		<div class="empty30" ></div>

		<div class="inputBox">
			<ul class="loginDiv">
				<li>
					<input type="text" name="id" maxlength="12" value="<?php echo $SAVEID; ?>" required="required" id="id" data-title="아이디" data-msg="아이디를" />
				</li>
				<li>
					<input type='password' name='passwd' required="required" id="passwd" value="" data-title="비밀번호" data-msg="비밀번호를" data-on-enter=1 />
				</li>				
			</ul>
			<?php if($ADMIN_TUTH != 'N') { ?>
			<ul class="authDiv">
				<li>
					<div class="authSelect">
						<?php if($ADMIN_TUTH == 'E') { ?>
						<p><label><input type='radio' name="auth" value="1" checked="checked" /><span class="radio"></span> 이메일인증 (<font id="authEmail"></font>)</label></p>
						<p class="help">이메일로 인증번호가 발송되었습니다.</p>
						<?php } else { ?>
						<p><label><input type='radio' name="auth" value="2" checked="checked" /><span class="radio"></span> 휴대폰인증 (<font id="authCell"></font>)</label></p>
						<p class="help">휴대폰번호로 인증번호가 발송되었습니다.</p>			
						<?php } ?>
					</div>
				</li>
				<li style="padding-top:0">
					<input type="text" name="auth_code" maxlength="10" value="" data-title="인증번호" data-msg="인증번호를" data-icon-no=1 data-on-enter=1 />
					<p class="auth_code_time">남은시간 : <span class="auth_code_times"></span></p>
				</li>
			</ul>
			<?php } ?>
		</div>		

		<div class="btnShineBox" style="background:#fff">
			<div class="displayInline"><button class="fontSCDream weight300 shine" type="submit">로그인</button></div>		
		</div>

		<div class="empty10" ></div>

		<div id="saveIdBox" class="fontSCDream font10 colorGray">
			<label>
				<input type='checkbox' name="save_id" value="1" <?php echo $checkedsid; ?> /><span></span> 아이디 저장
			</label>			
		</div>

	</div>
</center>

</form>

<iframe name="HFrm" style="display:none;"></iframe>

<script src="../../lib/lib.CheckForm.js?ver=<?php echo $ver; ?>""></script>
<script LANGUAGE="JavaScript">
<!--

var countTime1		= "";
var countTime2		= "";
var countObj		= ".auth_code_times";
var countCallBack	= "authCodeEnd";
var countStop		= 0;

var $limitX			= $(this).width();
var $limitY			= $(this).height();
var z_index			= -1000;

var canvas				= document.createElement("canvas");
var ctx					= canvas.getContext("2d");
canvas.width			= $limitX;
canvas.height			= $limitY;
canvas.style.zIndex		= z_index;
canvas.style.position	= "absolute";
canvas.style.top		= 0;
canvas.id				= "canvas_bg";

document.body.appendChild(canvas);

var gradient = ctx.createLinearGradient(0, 0, $limitX / 2, $limitY);
gradient.addColorStop(0, '#f48042');
gradient.addColorStop(1, '#FBB03B');

ctx.fillStyle = gradient;
ctx.fillRect(0, 0, $limitX, $limitY);

function authCodeEnd() {
	window.location.reload();
}

function countTime(){
	if(!countTime1) return;
	
	countTime2	++;	
	ck =		 1;
	
	lastTime = countTime1 - countTime2;	
	
	if(lastTime >= 0) {
		lastTime2 = lastTime;
		hour = Math.floor(lastTime2/3600);
		lastTime2 -=  hour * 3600;

		min  = Math.floor(lastTime2/60);
		lastTime2 -=  min * 60;

		sec = Math.floor(lastTime2); 

		hour	= hour>9 ?	hour + ''	: '0' + hour;
		min		= min>9 ?	min + ''	: '0' + min;
		sec		= sec>9 ?	sec + ''	: '0' + sec;
	
		$(countObj).html(min + '분 ' + sec + '초');
		
		if(lastTime == 0) {
			$(countObj).html('시간만료');
			if(countCallBack) eval(countCallBack + "()");
			ck = 0;
		}
	}
			
	if(ck == 1 && countStop == 0) timerID  = setTimeout(countTime, 1000);
}	

function submitProc() {

	var data = new FormData();
	
	data.append('id',		$('form[name=loginForm] input[name=id]').val());
	data.append('passwd',	$('form[name=loginForm] input[name=passwd]').val());

	$.ajax({
		url: 'passwd_check_json.php', 
		type: 'POST',	
		data: data, 
		cache: false,
		dataType: 'json',
		processData: false, 
		contentType: false, 
		success: function(data, textStatus, jqXHR) {
			if(typeof(data.error) === 'undefined') {
				$(".displayInline .shine").removeClass('shineSend');
				
				$.each(data, function(key, value) {
					if(data[key].email != 'undefined') $("#authEmail").html(data[key].email);
					if(data[key].email != 'undefined') $("#authCell").html(data[key].cell);
					
					$(".loginDiv").hide();
					$("#saveIdBox").hide();
					$(".authDiv").show();

					countTime1	= data[key].auth_time1;
					countTime2	= data[key].auth_time2;
					$(".authCode").show();
					$('form[name=loginForm] input[name=auth_code]').attr('required', true);
					$('form[name=loginForm]').attr('action', 'login_ok.php');
					countTime();
					alertify.success("인증번호가 발송 되었습니다.");					
				});
			}
			else {
				alertify.error(data.error);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alertify.error(textStatus);
		}		
	});

}

$(document).ready(function(){ 
	
	$('.inputBox').find("input:text, input:password").each(function(i) {
		
		$t = jQuery(this);
		if($t.attr("data-title")) {
			if($t.attr("data-detail"))	tmp_detail	= '<span>' + $t.attr("data-detail") + '</span>';
			else						tmp_detail	= '';
			$t.parent().append('<p class="inputTitle">' + $t.attr("data-title") + tmp_detail + '</p>');		
			if($t.val().length) {
				$t.parent().find('.inputTitle').animate({top:'-=15px'}, 'fast');
			}
		}
	});	

	$('.inputBox').find("input:text, input:password").focus(function(e) {	
		$t = jQuery(this);		
		if($t.attr("data-title")) {
			if(jQuery.trim($t.val()).length == 0 && parseInt($t.parent().find('.inputTitle').css('top')) == '20') {
				$t.parent().find('.inputTitle').animate({top:'-=15px'}, 'fast');
			}
		}

	});	

	$('.inputBox').find("input:text, input:password").blur(function(e) {
		
		$t = jQuery(this);		
		if($t.attr("data-title")) {
			if(jQuery.trim($t.val()).length == 0 && parseInt($t.parent().find('.inputTitle').css('top')) == '5' ) {
				$t.parent().find('.inputTitle').animate({top:'+=15px'}, 'fast');
			}
		}

	});	

	$('#id').css('imeMode','disabled');	
	if($('#id').val()) $('#passwd').trigger('focus');
	else $('#id').trigger('focus');

	var w	= $(this).width();
	var h	= $(this).height();

	$('#canvas_bg').css({'height' : h, 'width' : w});
	
});

$(window).resize(function(){ 
	var w	= $(this).width();
	var h	= $(this).height();

	$('#canvas_bg').css({'height' : h, 'width' : w});
});

//-->
</script>

</body>
</html>