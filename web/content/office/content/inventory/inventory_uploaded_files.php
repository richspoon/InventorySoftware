<?php
$Obj = new Inventory_Uploadify_UploadedFiles();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}