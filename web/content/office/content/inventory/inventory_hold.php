<?php
$Obj = new Inventory_InventoryHold();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}