<?php

// 템플릿
$tpl = new classTemplate;
$tpl->define('main','../common/popup_bottom.html');
$tpl->scan_area('main');

$tpl->parse('main');
$tpl->tprint('main');
$tpl->close();

?>