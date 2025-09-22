<?php 

include_once("../widget_top.php");

$num	= checkPostVar('num');
$min	= 100;
$max	= 112;

if($num)	{
	if($num == $max)	$rand	= $min;
	else				$rand	= $num + 1;
}
else					$rand	= mt_rand($min, $max);

$tpl->parse("is_tips_{$rand}");

include_once("../widget_bottom.php");

?>