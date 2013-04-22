<?php

$Obj = new General_ModuleInstructions;

if (Get('module')) {
    $Obj->ShowInstructions(Get('module'), Get('subpage'));
} else {
    $Obj->ListTable();
}