<?php
$Obj = new Inventory_InventoryMovement();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}