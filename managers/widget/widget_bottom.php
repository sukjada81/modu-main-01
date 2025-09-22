<?php

$tpl->parse("is_list_area");

$my_array[] = ["listHtml" => $tpl->tprint("is_list_area", 1)];
echo json_encode($my_array);
exit;

?>