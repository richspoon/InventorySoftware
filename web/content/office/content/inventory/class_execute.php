<?php
/* =============================================================================================
    
    CREATED BY -> 
    RAW

    DESCRIPTION -> 
    This page used to execute any class and pass in class variables. This page can only 
    handle basic Execute() or ProcessAjax() functionality meaning it should not be used 
    for standard classes where you want to immediately call an AddRecord() or ListTable(). 
    Use this page when you don't want to create individual content pages for a class but 
    you need to call a physical link instead of just instantiating a new class. 
    
    EXAMPLE -> 
    in the admin_command there are various functions an administrator might
    need to do. These functions involve instantiating a class and then recursivly calling
    that same class (adding free credits to a user). This page gives a content page to call
    within the links on that page to execute the class.

    classVars ->
    There are variables you want to instantiate the class with. You can send in 4 variables.
        In the class' _construct() use:
            $this->SetParameters(func_get_args());
            $this->var_1 = ($this->GetParameter(0)) ? $this->GetParameter(0) : 0;
            $this->var_2 = ($this->GetParameter(1)) ? $this->GetParameter(1) : 0;
    
============================================================================================= */

echo "LA L AL ALA L <br />";

$class          = Get('class');
$class_vars     = Get('classVars');
$class_vars_2   = Get('classVars2');
$class_vars_3   = Get('classVars3');
$class_vars_4   = Get('classVars4');
$class_vars_5   = Get('classVars5');

if (Get('dialogWidth')) { $DIALOG_CONTENT_WIDTH = Get('dialogWidth') . 'px'; }

if ($class) {
    $Obj = new $class($class_vars, $class_vars_2, $class_vars_3, $class_vars_4, $class_vars_5);
    if ($AJAX) {
    echo 'dddddddddddddddddddddd<br />';
        #$Obj->ProcessAjax();
        $Obj->ExecuteAjax();
    } else {
        $Obj->Execute();
    }
}



# RESIZE THE CURRENT FRAME TO FIT CONTENTS
# ================================================
$script = <<<SCRIPT
    var dialogNumber = '';
    if (window.frameElement) {
        if (window.frameElement.id.substring(0, 13) == 'appformIframe') {
            dialogNumber = window.frameElement.id.replace('appformIframe', '');
        }
    }
    ResizeIframe();
SCRIPT;
AddScript($script);