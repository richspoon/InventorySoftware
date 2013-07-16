<?php

$Obj = new Inventory_CustomerOrderPipeline();

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}