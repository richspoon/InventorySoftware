<?php

$Obj = new Inventory_ProductionWhitesheet();


if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}