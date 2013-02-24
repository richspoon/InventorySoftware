<?php

# NOTE :: Must have the DIALOGID passed in all of the menu links for table won't refresh
$DIALOGID = Get('DIALOGID');

$link = "/office/inventory/inventory_production_board;DIALOGID={$DIALOGID}";

$OBJ_MENU = new Inventory_DropdownMenu();
$OBJ_MENU->Menu_Raw = array(
    array(  'link'  => "{$link};t=1",
            'title' => "Edit Production Board", ),
    array(  'link'  => "{$link};t=2",
            'title' => "View Production Board", ),
);
echo $OBJ_MENU->Execute();
echo "<div style='min-width:300px; min-height:300px;'>&nbsp;";

$_GET['t'] = (Get('t')) ? Get('t') : 0;

switch(Get('t')) {
    case '1':
        echo "<div style='font-size:16px; font-weight:bold;'>Edit Production Board</div>";
        echo "<div>Class :: Inventory_ReportSalesOrderInventoryAvailable()</div><br /><br />";
        $OBJ = new Inventory_ProductionBoard();
        $OBJ->Execute();
    break;
    
    default:
    case '2':
        echo "<div style='font-size:16px; font-weight:bold;'>View Production Board</div>";
        echo "<div>Class :: Inventory_ReportSalesOrderInventoryAvailable()</div><br /><br />";
        $OBJ = new Inventory_ProductionBoard();
        $OBJ->ViewProductionBoard();
    break;
}

echo "</div>";

?>