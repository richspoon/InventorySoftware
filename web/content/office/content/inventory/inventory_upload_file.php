<?php
$folder = Get('folder');
$uid    = Get('uid');
$Obj    = new Inventory_Uploadify_UploadFile($folder, $uid);
$Obj->Execute();


/*
if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}
*/