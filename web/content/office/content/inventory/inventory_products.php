<?php
$Obj = new Inventory_InventoryProducts();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}