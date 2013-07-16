<?php
$Obj = new Inventory_InventorySalesOrderAssemblyCreate();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}