<?php

if(substr($_SERVER['HTTP_HOST'], -1) == '.') {
	header("HTTP/1.1 404 Internal Server Error");
	exit(0);
}

define('PATH_INCLUDE',		'../../include');
include_once(PATH_INCLUDE.'/config.php'); 

$ver			= SHOP_VERSION;
$t				= time();

$s_id = isset($_COOKIE['v_s_id']) ? $_COOKIE['v_s_id'] : '';
if($s_id) {

	define('PATH_LIB', '../../lib');
	include_once(PATH_LIB.'/lib.Function.php');  

	$SAVEID		= previlDecode($s_id);
	$checkedsid = "checked='checked'";
}
else $SAVEID = $checkedsid = "";

?>
<!doctype html> 
<html lang="ko">
<head>
<title>판매사 관리페이지 로그인</title>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />

<link type="text/css" rel="StyleSheet" href="../common/style.css?ver=<?php echo $ver; ?>" />
<link type="text/css" rel="stylesheet" href="../../lib/alertify.css?ver=<?php echo $ver; ?>" />

<style>
	.inputMessageBox { font-family: 'SCDream', sans-serif; z-index:9991; display:block; position:absolute; top:-28px; left:32px; }
	.inputMessageBox .inputMessage {  background-color:#FE1A00; border-radius:5px; padding:6px 10px; color:#fff; font-size:1.2em; font-family: 'SDMiSaeng', sans-serif; }
	.inputMessageBox .inputMessage_arrow { position:relative;  top:-1px; left:-40px; width: 0; height: 0; border-left: 7px solid transparent; border-right: 7px solid transparent; border-top: 7px solid #FE1A00; }	
	
</style>

<script type="text/javascript" src="//code.jquery.com/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="../../lib/lib.Function.js?ver=<?php echo $ver; ?>"></script>
<script type="text/javascript" src="../../lib/alertify.js?ver=<?php echo $ver; ?>"></script>
<script type="text/javascript" src="../../lib/clipboard.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<!--[if lte IE 8]><script type="text/javascript" src="../../lib/excanvas.js"></script><![endif]-->

<script LANGUAGE="JavaScript">
<!--
	alertify.defaults.glossary.title = "<?php echo $_SERVER["SERVER_NAME"] ?>";
//-->
</script>

</head>

<body style="min-width:660px; background-color:#f48042">
	
<form method="post" name="loginForm" action="login_ok.php" target='HFrm'>
<input type="hidden" name="url" value="admin">

<center>
	<div class="loginBg1 alignLeft fontRoboto colorWhite size08">
		<span class="small-title">모두복지몰</span><span class="size10"></span>	
	</div>
	<div class="loginBg2">
		<div class="loginTitle fontRoboto">
			<span class="textShadow size30" >Vendor Administrator Login Page!</span><br /><br />
			<span class="size14">모두복지몰 ShoppingMall Solution Version.</span>
		</div>
	</div>
	<div class="loginBg3"></div>

	<div id="loginBox" >
		<div>
			<div class="fontSCDream size18 textShadowL">판매사 로그인</div>
		</div>

		<div class="empty30" ></div>

		<div class="inputBox">
			<ul>
				<li>
					<input type="text" name="id" id="id" maxlength="12" value="<?php echo $SAVEID; ?>" required="required" data-title="아이디" data-msg="아이디를" />
				</li>
				<li>
					<input type='password' name='passwd' id="passwd" required="required" value="" data-title="비밀번호" data-msg="비밀번호를" data-on-enter=1 />
				</li>
			</ul>		
		</div>		

		<div class="btnShineBox" style="background:#fff">
			<div class="displayInline"><button class="fontSCDream weight300 shine" type="submit">로그인</button></div>		
		</div>

		<div class="empty10" ></div>

		<div id="saveIdBox" class="fontSCDream font10 colorGray">
			<label>
				<input type='checkbox' name="save_id" value="1" <?php echo $checkedsid ?> /><span></span> 아이디 저장
			</label>			
		</div>

	</div>
</center>

</form>

<iframe name="HFrm" style="display:none;"></iframe>

<script src="../../lib/lib.CheckForm.js?ver=<?php echo $ver; ?>""></script>
<script LANGUAGE="JavaScript">
<!--

var $limitX  = $(this).width();
var $limitY  = $(this).height();
var z_index = -1000;

var canvas            = document.createElement("canvas");
var ctx               = canvas.getContext("2d");
canvas.width          = $limitX;
canvas.height         = $limitY;
canvas.style.zIndex   = z_index;
canvas.style.position = "absolute";
canvas.style.top      = 0;
canvas.id				= "canvas_bg";

document.body.appendChild(canvas);

var gradient = ctx.createLinearGradient(0, 0, $limitX / 2, $limitY);
gradient.addColorStop(0, '#f48042');
gradient.addColorStop(1, '#FBB03B');

ctx.fillStyle = gradient;
ctx.fillRect(0, 0, $limitX, $limitY);

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
	if($('#id').val()) $('#passwd').focus();
	else $('#id').focus();

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