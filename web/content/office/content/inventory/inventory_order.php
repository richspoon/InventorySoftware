<?php
$Obj = new Inventory_InventoryOrder();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}