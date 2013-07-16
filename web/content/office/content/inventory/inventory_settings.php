<?php
$Obj = new Inventory_Settings();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}