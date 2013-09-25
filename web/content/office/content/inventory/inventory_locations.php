<?php
$Obj = new Inventory_InventoryLocations();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}