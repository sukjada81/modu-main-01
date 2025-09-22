<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

SetCookie("guestOrder1","",-999,"/");
SetCookie("guestOrder2","",-999,"/");

$channel2	= checkGetVar('channel2');
if($channel2) $channel_orig = $channel2;
$uid		= checkGetVar('uid');

?>