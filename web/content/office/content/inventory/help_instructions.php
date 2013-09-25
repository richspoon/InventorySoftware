<?php

AddStylesheet("/css/inventory.css??20120924-6");

# NOTE :: Must have the DIALOGID passed in all of the menu links for table won't refresh
$DIALOGID = Get('DIALOGID');

$OBJ_MENU = new Inventory_DropdownMenu();
$OBJ_MENU->Menu_Raw = array(
    array(  'link'  => "/office/inventory/help_instructions;DIALOGID={$DIALOGID};t=design",
            'title' => "HELP INSTRUCTIONS", ),
    array(  'link'  => "/office/inventory/help_instructions;DIALOGID={$DIALOGID};t=connect",
            'title' => "CONNECT MODULE TO HELP", ),
);
echo $OBJ_MENU->Execute();

$style = "font-weight:bold; font-size:14px; color:#000; padding-bottom:10px; padding-top:20px; border-bottom:1px solid blue;";

switch (Get('t')) {
    
    default:
    case 'design':
        //echo "<div style='{$style}'>HELP INSTRUCTIONS</div>";
        $Obj = new Inventory_Help_HelpInstructions();
    break;
    
    case 'connect':
        //echo "<div style='{$style}'>CONNECT MODULE TO HELP</div>";
        $Obj = new Inventory_Help_HelpInstructionsModule();
    break;   
}

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}

?>