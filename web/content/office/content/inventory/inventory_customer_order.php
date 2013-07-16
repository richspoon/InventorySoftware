<?php

$Obj = new Inventory_CustomerOrder();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}