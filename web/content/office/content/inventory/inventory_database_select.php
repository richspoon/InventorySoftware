<?php
// ----- initialize singleton version of class
$Obj = Lib_Singleton::GetInstance('Inventory_DatabaseSelect'); 

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}