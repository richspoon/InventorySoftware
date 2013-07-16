<?php

$Obj = new Inventory_InventorySalesOrderPlaceNew();


if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}