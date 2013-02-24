<?php

AddStylesheet("/css/inventory.css??20120924-6");

# NOTE :: Must have the DIALOGID passed in all of the menu links for table won't refresh
$DIALOGID = Get('DIALOGID');

$OBJ_MENU = new Inventory_DropdownMenu();
$OBJ_MENU->Menu_Raw = array(
    array(  'link'  => "/office/inventory/inventory_assembly;DIALOGID={$DIALOGID};t=design",
            'title' => "DESIGN ASSEMBLY GROUP", ),
    array(  'link'  => "/office/inventory/inventory_assembly;DIALOGID={$DIALOGID};t=place",
            'title' => "CREATE ASSEMBLY REQUEST",   ),
    array(  'link'  => "/office/inventory/inventory_assembly;DIALOGID={$DIALOGID};t=build",
            'title' => "BUILD AN ASSEMBLY", ),
);
echo $OBJ_MENU->Execute();


switch (Get('t')) {

default:
    case 'design':
        echo "<h2>DESIGN ASSEMBLY REQUEST</h2>";
        $Obj = new Inventory_InventoryAssemblyCreate();
        break;
        
    
    case 'place':
        echo "<h2>CREATE ASSEMBLY REQUEST</h2>";
        $Obj = new Inventory_InventoryAssemblyRequest();
        break;
        
    case 'receive':
        echo "<h2>BUILD ASSEMBLY FROM REQUEST</h2>";
        $Obj = new Inventory_InventoryAssemblyFulfill();
        break;
        
    case 'build':
        echo "<h2>BUILD ASSEMBLY</h2>";
        $Obj = new Inventory_InventoryAssemblyBuild();
        break;
        
}

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}