<?php

AddStylesheet("/css/inventory.css??20120924-6");

/*
echo "
<a class='menu' href='/office/inventory/inventory_po;t=place'>PURCHASE ORDER PLACE</a><br />
<a class='menu' href='/office/inventory/inventory_po;t=receive'>PURCHASE ORDER RECEIVE</a>
";
*/

switch (Get('t')) {

    default:
    case 'place':
        //echo "<h2>CREATE PO</h2>";
        $Obj = new Inventory_InventoryPurchaseOrderPlace();
        break;
        
    case 'receive':
        //echo "<h2>RECEIVE PO</h2>";
        $Obj = new Inventory_InventoryPurchaseOrderReceive();
        break;
        
}

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}