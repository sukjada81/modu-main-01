<?php

if(!defined('_B2BMALL_')) exit; // 개별 페이지 접근 불가

define('__MYPAGE__',		'1');

$_GET['b_id']	= 'counsel';
$mypages		= $channel;

include_once("board/board.php");

?>