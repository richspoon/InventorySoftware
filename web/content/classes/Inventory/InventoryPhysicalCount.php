<?phpclass Inventory_InventoryPhysicalCount extends Inventory_InventoryBase{    public $Show_Query                  = false;    public $Module_Offline              = false;            // (false) TRUE = dispaly offline message to users    public $Allow_Zero_Quantity_Adjust  = true;             // (true) TRUE = allow storing a 0-qty in or out        private $Default_Locations_ID       = 0;        // will hold the default location    private $Inventory_Locations_ID     = 0;        // holds the current location were looking at inventory for                public function  __construct()    {        parent::__construct();        $this->SetSQLInventory();   // set the database connection to the inventory database                $this->Classname = get_class($this);        $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2012-11-26',            'Updated By'    => 'Richard Witherspoon',            'Updated Date'  => '2013-10-02',            'Filename'      => $this->Classname,            'Version'       => '1.7',            'Description'   => 'Allows entering physical count of inventory',            'Update Log'    => array(                '2012-12-04_1.1'    => 'Added cost in PostProcess() to stop 0 qty inventory adjustment.',                '2012-12-17_1.2'    => 'Modified inventory-adjustment record to track inventory in and out',                '2013-01-23_1.3'    => 'Significant updates to processing to use new Inventory_CostCalculation() class',                '2013-02-24_1.4'    => 'Modified PostProcessFormValues() to use new class name for Inventory_Valuation_SingleItemCostCalculation',				'2013-03-08_1.5'	=> "Modification to use new inventory valuation classes",                '2013-05-01_1.5.1'	=> "Bug fix due to new inventory database",                '2013-07-16_1.6.0'	=> "Supports multiple locations",                '2013-10-02_1.7'	=> "Add ref_class to DB addRecord",            ),        );                        /// ===== GET VALUES PASSED INTO CLASS CONSTRUCT =====        $this->SetParameters(func_get_args());        $this->Inventory_Locations_ID   = $this->GetParameter(0);                                $this->Default_Locations_ID     = $this->GetSetting('inventory_default_location_id');        $this->Default_Locations_ID     = (!$this->Default_Locations_ID) ? 1 : $this->Default_Locations_ID;        $this->Inventory_Locations_ID   = (!$this->Inventory_Locations_ID) ? $this->Default_Locations_ID : $this->Inventory_Locations_ID;                        $this->Default_Values   = array(            'date'                      => date('Y-m-d H:i:s'),            'temp_approve_cost_check'   => 1,                        //'date'      => date('2013-08-01'),            //'notes'     => "Inventory physical count for July.",        );                        $this->Table                = "TEMP";        $this->Add_Submit_Name      = "{$this->Table}_SUBMIT_ADD";        $this->Edit_Submit_Name     = "{$this->Table}_SUBMIT_EDIT";        $this->Close_On_Success     = true;                                    } // -------------- END __construct --------------        public function Execute()    {                $this->ModuleOfflineMessage();                  // show module offline essage - if activated        $this->JavascriptDisplaySessionMessage();       // Display alert messages                        $action = Get('action');        switch ($action) {                        case 'change_location':                $this->ChangeLocation();            break;                        case 'list':                $this->ListTable();            break;                        case 'add':            default:                            $this->AddRecord();                     // add a new record            break;        }    }        public function ExecuteAjax()    {        $QDATA      		= GetEncryptQuery('eq');        $action     		= Get('action');        $this->Show_Query 	= false;		        //$_GET['show'] = true;                if (Get('show')) {            echo "<br />QDATA = " . ArrayToStr($QDATA);            echo "<br />action = $action";        }                $return = 0;                switch ($action) {                        case 'autocomplete_inventory_lookup':                                // LOOK UP ALL ACTIVE INVENTORY ITEMS                                // query database for records                $query = Get('term');                $records = $this->SQL->GetArrayAll(array(                    'table' => 'inventory_products',                    'keys'  => 'description, retailer_code, barcode, status_retired',                    'where' => "(description LIKE '%{$query}%' OR barcode LIKE '%{$query}%' OR retailer_code LIKE '%{$query}%') AND active=1",                ));                                // for records into array format for JSON                $arr = array();                foreach ($records as $record) {                                                            // get quantity available                    $inventory_locations_id = Get('ilid');                    if ($inventory_locations_id) {                        $qty = $this->InventoryItemQuantityAvailableLocation($record['barcode'], '', $inventory_locations_id);                    } else {                        $qty = 'ERR'; //$this->InventoryItemQuantityAvailable($record['barcode']);                    }                                        //echo json_encode('inventory_locations_id: ' .  $inventory_locations_id);                                        $retired = ($record['status_retired'] == 1) ? "[Retired] - " : "";                    $arr[] = array(                        'label'             => "{$record['barcode']} - {$retired}{$record['retailer_code']}",                        'description'       => $record['description'],                        'sku'               => $record['retailer_code'],                        'barcode'           => $record['barcode'],                        'qty'               => $qty,                    );                }                                // convert to JSON format                echo json_encode($arr);     // echo out in JSON form                 $return = '';               // clear return value or it will output and screw up return            break;        }                echo $return;    }                public function SetFormArrays()    {        # FUNCTION :: Output the main user form to the screen                        // ----- set the focus on first search box        AddScriptOnReady('$("#FORM_temp_0").focus();');                        $Show_Approve_Cost_Check = true;        #$Show_Approve_Cost_Check = Session('Show_Approve_Cost_Check');        #unset($_SESSION['Show_Approve_Cost_Check']);                                $this->JavascriptAutoComplete();            // Javascript auto-complete functionality        $this->JavascriptClearForm();               // Javascript "clear form" button                 $this->JavascriptDatepickerFunctionality(array('FORM_date'));        $this->JavascriptDisableFunctionality(array('FORM_temp_2','FORM_temp_4','FORM_temp_5'));        $this->JavascriptInputNoBorder(array('FORM_temp_1','FORM_temp_2','FORM_temp_4','FORM_temp_5'));                        $btn_clear          = MakeButton('negative', 'CLEAR', '', '', 'btn_clear', "clearDataTextboxes()", 'button', 'btn_clear');                                        // ----- SET THE LOCATION -----        $_POST['FORM_inventory_locations_id']   = 0;            // initialize variable        $location_name_line                     = "";           // initialize variable                if ($this->Inventory_Locations_ID != 0) {            $record = $this->SQL->GetRecord(array(                'table' => 'inventory_locations',                'keys'  => '*',                'where' => "inventory_locations_id={$this->Inventory_Locations_ID} AND active=1",            ));            //$this->SQL->EchoQuery($this->Classname, __FUNCTION__);                        if ($record) {                $inventory_location_name                = (isset($record)) ? $record['location_name'] : 'n/a';                $_POST['FORM_inventory_locations_id']   = $this->Inventory_Locations_ID;                $eq                                     = EncryptQuery("class={$this->Classname};v1={$this->Inventory_Locations_ID}");                $url                                    = "http://webmanager.whhub.com/office/class_execute.php;eq={$eq};action=change_location";                $inventory_location_change_link         = " [<a href='{$url}'>change location</a>]";                $location_name_line                     = "info|Location|{$inventory_location_name} {$inventory_location_change_link}";            }        }                                $base_array = array(            "form|$this->Action_Link|post|db_edit_form",                        "code|<div class='shadow' style='border:1px dashed blue; padding:5px; background-color:#efefef;'>",                                "hidden|inventory_locations_id",                //"text|Location ID|inventory_locations_id|N|60|100",                "{$location_name_line}",                                                                "text|Search|temp_0|N|60|100",                'text|Quantity|quantity|N|60|255',                                "code|<br />",                "text|Barcode|temp_1|N|60|100",                "text|APDM SKU|temp_2|N|60|100",                "text|Description|temp_4|N|60|100",                "text|Current QTY @ Current Location|temp_5|N|60|100",                "hidden|temp_quantity_available",                                "info||$btn_clear",                            "code|</div>",            "code|<br /><br />",                        'text|Scan Date|date|N|60|255',            'textarea|Notes|notes|N|60|2',            'checkbox|Override Value Check|temp_override_value_check||1|0',            'checkbox|Force Show Cost|temp_force_cost_show||1|0',                    );                if ($Show_Approve_Cost_Check) {            $base_array[] = "code|<br /><div class='shadow' style='border:1px dashed red; padding:5px; background-color:red; color:#fff;'>";            $base_array[] = 'checkbox|Approve Cost Calculation|temp_approve_cost_check||1|0';            $base_array[] = 'code|</div><br />';        }                if ($this->Action == 'ADD') {            $base_array[] = "submit|Add Record|$this->Add_Submit_Name";            $base_array[] = 'endform';            $this->Form_Data_Array_Add = $base_array;        } else {            //$base_array[] = 'checkbox|Active|active||1|0';            $base_array[] = "submit|Update Record|$this->Edit_Submit_Name";            $base_array[] = 'endform';            $this->Form_Data_Array_Edit = $base_array;        }    }        public function PostProcessFormValues($FormArray)     {        /* ====== P-CODE ================================================                Check if inventory item is still active        Determine if qty has increased or decreased from expected        (IF CHANGE) {            create an adjustment record - use last price for adjustment            update the qty information            update the inventory record for last scanned date        } else {            update the inventory record for last scanned date        }                show sucess message        go back to this current page for another entry                ============================================================== */                $passed                 = true;        $barcode                = $FormArray['temp_1'];        $qty_counted            = isset($FormArray['quantity']) ? $FormArray['quantity'] : 0;        $date                   = $FormArray['date'];        $override_value_check   = $FormArray['temp_override_value_check'];        $notes                  = isset($FormArray['notes']) ? $FormArray['notes'] : "Inventory adjustment due to physical count. Price based on last found price.";        $cost_approved          = isset($FormArray['temp_approve_cost_check']) ? $FormArray['temp_approve_cost_check'] : 0;        $cost_approved = 1;                $force_show_cost            = $FormArray['temp_force_cost_show'];        $inventory_locations_id     = $FormArray['inventory_locations_id'];                $ref_quantity_previous      = $FormArray['temp_quantity_available'];    // capture what system thought was present        $ref_quantity_entered       = $qty_counted;                             // capture what user entered                //$this->EchoVar('', $FormArray);        //exit();                if (!$barcode || !$date) {            $this->Error .= "<br />ERROR :: Not all required information provided.";            $passed = false;        }                if ($inventory_locations_id != 0) {            $qty_current    = $FormArray['temp_quantity_available']; //$this->InventoryItemQuantityAvailableLocation($barcode, '', $inventory_locations_id);        } else {            $this->Error .= "<br />ERROR :: No inventory location provided.";            $passed = false;            //$qty_current    = $this->InventoryItemQuantityAvailable($barcode);        }                $exist = $this->StatusInventoryItemExist($barcode);        if (!$exist) {            $this->Error .= "<br />ERROR :: Inventory item does not exist. Barcode: {$barcode}";            $passed = false;        }                                                        if ($qty_current != $qty_counted) {            // ----- qty has changes so need to make an adjustment record                        $quantity_adjust = ($qty_counted - $qty_current);                                    // ---- don't allow 0 qty adjustment record if system thinks a real qty adjustment needs to be made            if ($quantity_adjust == 0 && !$this->Allow_Zero_Quantity_Adjust) {                $this->Error .= "<br />ERROR :: 0 Quantity adjusment not allowed. Barcode: {$barcode}";                $passed = false;            }                        // ----- determine if qty going IN or OUT of system            if($quantity_adjust < 0) {                // lost inventory                $qty_in     = 0;                $qty_out    = abs($quantity_adjust);            } else {                // gained inventory                $qty_in     = abs($quantity_adjust);                $qty_out    = 0;            }                        $quantity_adjust    = abs($quantity_adjust);            //$last_price         = $this->InventoryItemLastCost($barcode);                                                // =======================================================================================================            // =======================================================================================================            #$Obj_Calculation            = new Inventory_Valuation_SingleItemCostCalculation ();         // Instantiate a barcode cost object            #$Obj_Calculation->Method    = 'total_average';                                              // Set method of cost calculation            #$Obj_Calculation->Barcode   = $barcode;                                                     // Set barcode to get cost for            #$result                     = $Obj_Calculation->Execute();                                  // Run the calcuation            												// ----- Get the build record used for this assembly			$Obj_ValueHandler                           = new Inventory_Valuation_ValueHandler();               // instantitate value class			$Obj_ValueHandler->Barcode                  = $barcode;                                       		// pass in the barcode we want value for			$Obj_ValueHandler->Execute();                                                                       // execute the function to get value			$value_array                                = $Obj_ValueHandler->Value_Array;                       // store the value array for report processing									// ----- get values back from the cost calculation            $used_default           	= false;            $price_each             	= $Obj_ValueHandler->Value_Each;            $price_shipping_each    	= 0;            $price_method           	= $Obj_ValueHandler->Value_Method_Used;            $price_method_array    	 	= $Obj_ValueHandler->Value_Array;            $build_record_array     	= $Obj_ValueHandler->Build_Record;            			            // ----- do some arithmetic to calculate total costs            $price_total            	= ($price_each * $quantity_adjust);            $price_shipping_total   	= 0; //($price_shipping_each * $quantity_adjust);            $price_approved         	= ($cost_approved) ? 1 : 0;									$is_assembly 				= $Obj_ValueHandler->Is_Assembly;									/*            // ----- completely fail if we couldn't calculate any value            if (!$result) {                $this->EchoVar('ERROR', "UNABLE TO CALCULATE VALUE. Barcode: {$barcode}", 'red');                $this->Error .= "<br />ERROR :: UNABLE TO CALCULATE VALUE. Barcode: {$barcode}";                $passed = false;            }			                        // ----- get values back from the cost calculation            $used_default           = false; //$Obj_Calculation->Used_Default_Value;            $price_each             = $Obj_Calculation->Price_Calculated_Each;            $price_shipping_each    = $Obj_Calculation->Price_Calculated_Shipping_Each;            $price_method           = $Obj_Calculation->Used_Method;            $price_method_array     = $Obj_Calculation->Value_Array;            $build_record_array     = $Obj_Calculation->Build_Record;                        // ----- do some arithmetic to calculate total costs            $price_total            = ($price_each * $quantity_adjust);            $price_shipping_total   = ($price_shipping_each * $quantity_adjust);            $price_approved         = ($used_default && $cost_approved) ? 1 : 0;            */            // ----- serialize values            $price_method_array_serialized  = ($price_method_array) ? serialize($price_method_array) : '';            $build_record_array_serialized  = ($build_record_array) ? serialize($build_record_array) : '';                        // ----- output some debug variables            if (false) {                $this->EchoVar('used_default', $used_default);                $this->EchoVar('price_each', $price_each);                $this->EchoVar('price_shipping_each', $price_shipping_each);                $this->EchoVar('quantity_adjust', $quantity_adjust);                $this->EchoVar('price_total', $price_total);                $this->EchoVar('price_shipping_total', $price_shipping_total);                $this->EchoVar('price_method', $price_method);                $this->EchoVar('price_method_array', $price_method_array);                $this->EchoVar('build_record_array', $build_record_array);                            }                        // ----- FORCE A PASS FAILURE - FOR DEBUG -----            //$passed = false;                        // =======================================================================================================            // =======================================================================================================                        //$is_assembly = $Obj_Calculation->CheckIfSubAssembly($barcode);                                    if ( ($used_default || $is_assembly || $force_show_cost) && !$cost_approved) {                // ----- we used at least one default value - this requires further approval by user                $this->Error .= "<br />YOU NEED TO APPROVE COST VALUATION BECAUSE DEFAULT VALUE USED";                $sku = $Obj_ValueHandler->GetInventoryItemRetailerCodeFromBarcode($barcode);                                echo "                    <div style='border:1px solid red;'>                    <div style='padding:5px; background-color:red; color:#fff; font-weight:bold;'>COST CALCULATION DATA</div>                    <div style='padding:5px;'>";                                $this->EchoVar('Barcode', $barcode);                $this->EchoVar('SKU', $sku);                $this->EchoVar('used_default', $used_default);                $this->EchoVar('price_each', $price_each);                $this->EchoVar('price_shipping_each', $price_shipping_each);                $this->EchoVar('quantity_adjust', $quantity_adjust);                $this->EchoVar('price_total', $price_total);                $this->EchoVar('price_shipping_total', $price_shipping_total);                $this->EchoVar('price_method', $price_method);                $this->EchoVar('price_method_array', $price_method_array);                $this->EchoVar('build_record_array', $build_record_array);                echo "</div></div>";                                                //$_SESSION['Show_Approve_Cost_Check'] = true;        // turn on approval mechanism                $passed = false;                return $FormArray;            }                                    /*            if (!$last_price && $qty_counted > 0 && !$override_value_check) {                $this->Error .= "<br />ERROR :: Inventory has no historical last price. Barcode: {$barcode}";                $passed = false;            }            */                                    # ===== START TRANSACTION ============================================================            $this->SQL->StartTransaction();                                    // ----- Insert the adjustment record -----            $db_record = array(                'barcode'                   => $barcode,                'date'                      => $date,                'quantity_in'               => $qty_in,                'quantity_out'              => $qty_out,                'price_total'               => $price_total,                'price_shipping_total'      => $price_shipping_total,                'price_method'              => $price_method,                'price_method_array'        => $price_method_array_serialized,                'build_record_array'        => $build_record_array_serialized,                'notes'                     => $notes,                'price_approved'            => $price_approved,                'ref_quantity_previous'     => $ref_quantity_previous,                'ref_quantity_entered'      => $ref_quantity_entered,                'inventory_locations_id'    => $inventory_locations_id,                'ref_class'                 => $this->Classname,            );            $result = $this->SQL->AddRecord(array(                'table'     => 'inventory_adjustments',                'keys'      => $this->SQL->Keys($db_record),                'values'    => $this->SQL->Values($db_record),            ));            $this->SQL->EchoQuery($this->Classname, __FUNCTION__ . '_a');            $passed = (!$result) ? false : $passed;            $inventory_adjustments_id = $this->SQL->Last_Insert_Id;            $this->EchoVar('result A', $result);                                                // ----- Insert the inventory count record -----            $db_record = array(                'barcode'                   => $barcode,                'date'                      => $date,                'qty_in'                    => $qty_in,                'qty_out'                   => $qty_out,                'ref_adjustment_id'         => $inventory_adjustments_id,                'inventory_locations_id'    => $inventory_locations_id,                'notes'                     => $notes,            );            $result = $this->SQL->AddRecord(array(                'table'     => 'inventory_counts',                'keys'      => $this->SQL->Keys($db_record),                'values'    => $this->SQL->Values($db_record),            ));            $this->SQL->EchoQuery($this->Classname, __FUNCTION__ . '_b');            $passed = (!$result) ? false : $passed;            $this->EchoVar('result B', $result);                                                // ----- Update the inventory product record -----            $db_record = array(                'date_last_checked' => $date,            );            $result = $this->SQL->UpdateRecord(array(                'table'         => 'inventory_products',                'key_values'    => $this->SQL->KeyValues($db_record),                'where'         => "`barcode`='{$barcode}' AND `active`=1",            ));            $this->SQL->EchoQuery($this->Classname, __FUNCTION__ . '_c');            $passed = (!$result) ? false : $passed;            $this->EchoVar('result C', $result);                    } else {                        # ===== START TRANSACTION ============================================================            $this->SQL->StartTransaction();                    // ----- Update the inventory product record -----            $db_record = array(                'date_last_checked' => $date,            );            $result = $this->SQL->UpdateRecord(array(                'table'         => 'inventory_products',                'key_values'    => $this->SQL->KeyValues($db_record),                'where'         => "`barcode`='{$barcode}' AND `active`=1",            ));            $this->SQL->EchoQuery($this->Classname, __FUNCTION__ . '_d');            $passed = (!$result) ? false : $passed;            $this->EchoVar('result D', $result);        }                        # ===== COMMIT TRANSACTION ============================================================        if ($passed) {            $this->SQL->TransactionCommit();                        // ----- Do A force redirect to bypass BaseClass further procesing this            $link = $this->getPageURL();            //$_SESSION['alert_message'] = "INVENTORY COUNTED SUCESSFULLY";            header("Location: {$link}");        } else {            return $FormArray;        }                //exit();    }                public function ChangeLocation()    {        # FUNCTION :: Show form for changing the inventory location                $output = '';       // initialize variable                                // ----- GET ALL LOCATIONS        $records = $this->SQL->GetArrayAll(array(            'table' => 'inventory_locations',            'keys'  => '*',            'where' => 'active=1',        ));        //$this->SQL->EchoQuery($this->Classname, __FUNCTION__);                               // ----- create the status select drop-down        $select_options     = '';        foreach ($records as $record) {            $selected        = ($record['inventory_locations_id'] == $this->Inventory_Locations_ID) ? ' selected' : '';            $link            = EncryptQuery("class={$this->Classname};v1={$record['inventory_locations_id']}");            $select_options .= "<option value='{$link}' {$selected}>{$record['location_name']}</option>";        }        $status_select      = "<select id='location_select'>{$select_options}</select>";        $btn_submit         = MakeButton('positive', 'SUBMIT', '', '', 'btn_clear', "locationChangeSubmit()", 'button', 'btn_clear');        $spacer             = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";                        $output .= "<div class='shadow form_section_wrapper'>";        $output .= '<div class="form_section_header">CHANGE LOCATION</div>';        $output .= "<div style='min-width:300px;'>&nbsp;</div>";        $output .= "{$status_select}{$spacer}{$btn_submit}</br></br>";        $output .= "</div>";        $output .= "</div>";                        // ----- add script for updating location        $url    = "http://webmanager.whhub.com/office/class_execute.php";        AddScript("            function locationChangeSubmit()            {                var value = $('#location_select').attr('value');                window.location = '{$url};eq=' + value;            }        ");                        echo $output;    }            public function JavascriptAutoComplete()    {        $eq = EncryptQuery("class={$this->Classname}");      // Class the autocomplete should call (ideally this class)                $script = <<<SCRIPT                        // ----- remove borders from textboxes            $('#FORM_temp_1').addClass('noborder');            $('#FORM_temp_2').addClass('noborder');            $('#FORM_temp_4').addClass('noborder');            $('#FORM_temp_5').addClass('noborder');                                    // ----- autocomplete -----            var termTemplate = "<span class='ui-autocomplete-term'>%s</span>";                        $('#FORM_temp_0').autocomplete({                                 source          : 'http://webmanager.whhub.com/office/AJAX/class_execute.php;eq={$eq};action=autocomplete_inventory_lookup;ilid={$this->Inventory_Locations_ID}',                minChars        : 0,    // how many characters to type before starting function call                selectFirst     : true,                autoFocus       : true,                                // where do we stick the returned results                select: function( event, ui ) {                    $( "#FORM_temp_0" ).val( ui.item.label );                    $( "#FORM_temp_1" ).val( ui.item.barcode );                 // store Barcode                    $( "#FORM_temp_2" ).val( ui.item.sku );                     // store APDM SKU                    $( "#FORM_temp_4" ).val( ui.item.description );             // store Description                    $( "#FORM_temp_5" ).val( ui.item.qty );                     // store quantity                         $( "#FORM_temp_quantity_available" ).val( ui.item.qty );                     // store quantity                              return false;                },                                // format the matched text in search terms                // NOTE/BUG :: Currently case sensitive so doesn't highlight non-case-matching results                open: function(e,ui) {                    var                        acData = $(this).data('autocomplete'),                        styledTerm = termTemplate.replace('%s', acData.term);                    acData                        .menu                        .element                        .find('a')                        .each(function() {                            var me = $(this);                            me.html( me.text().replace(acData.term, styledTerm) );                        });                }            })                        // format the display of the data in the autocomplete (should probably be a custom function on this class)            // (everything else should be in the base class)            .data( "autocomplete" )._renderItem = function( ul, item ) {                return $( "<li>" )                    .data( "item.autocomplete", item )                    .append( "<a>" + item.label + "<br /> " + item.description + "</a>" )                    .appendTo( ul );            };            SCRIPT;        AddScriptOnReady($script);                        $script = <<<SCRIPT            (function( $ ) {            $( ".ui-autocomplete-input" ).live( "autocompleteopen", function() {                var autocomplete = $( this ).data( "autocomplete" ),                    menu = autocomplete.menu;                if ( !autocomplete.options.selectFirst ) {                    return;                }                menu.activate( $.Event({ type: "mouseenter" }), menu.element.children().first() );            });            }( jQuery ));SCRIPT;        //AddScript($script);    }        public function JavascriptClearForm()    {        $script = <<<SCRIPT            function clearDataTextboxes() {            // -- clear the textboxes used to search on table data            $("#FORM_temp_0").val('');            $("#FORM_temp_1").val('');            $("#FORM_temp_2").val('');            $("#FORM_temp_4").val('');            $("#FORM_temp_5").val('');            $("#FORM_temp_6").val('');            $("#FORM_temp_7").val('');                        $("#FORM_temp_quantity_available").val('');                    }SCRIPT;        AddScript($script);    }    }  // -------------- END CLASS --------------