<?php

$Obj = new Inventory_InventorySalesOrderSort();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}