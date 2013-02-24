<?php
$Obj = new Inventory_InventoryCommand();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}