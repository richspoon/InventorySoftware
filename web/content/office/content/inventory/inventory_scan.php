<?php
$Obj = new Inventory_InventoryPhysicalCount();

/*
$BARCODE = 10057;
$Obj->Show_Query = true;
$cost = $Obj->InventoryItemLastCost($BARCODE);
echo "<h2>Cost: {$cost}</h2>";
*/

if ($AJAX) {
    #echo "<h2>AjaxHandle</h2>";
    $Obj->AjaxHandle();
} else {
    #echo "<h2>Execute</h2>";
    $Obj->Execute();
}