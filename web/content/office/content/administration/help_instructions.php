<?php
$Obj = new Help_HelpInstructions;

if ($AJAX) {
    $Obj->AjaxHandle();
} else {
    $Obj->Execute();
}