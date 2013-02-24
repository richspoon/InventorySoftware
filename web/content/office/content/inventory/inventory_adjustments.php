<?php
AddStylesheet("/css/inventory.css??20120924-6");

$Obj = new Inventory_InventoryAdjustment();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}