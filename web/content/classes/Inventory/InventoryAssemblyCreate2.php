<?phpclass Inventory_InventoryAssemblyCreate2 extends Inventory_InventoryBase{    public $Show_Query                      = false;	// (false) TRUE = show database queries in this class    public $Show_Report                     = false; 	// (false) TRUE = show how value was calculated    	// ----- INPUT VARIABLES -----			// ----- OUTPUT VARIABLES -----		    // ----- OTHER VARIABLES -----	public $Header_Row                      = "Barcode|APDM SKU|Description|QTY REMOVE|QTY ADD|DEL";    public $Inventory_Assemblies_ID         = 0;    public $Flags							= array();		    public function  __construct()    {        parent::__construct();        $this->SetSQLInventory();   // set the database connection to the inventory database                $this->Classname = get_class($this);        $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2012-11-19',            'Updated By'    => 'Richard Witherspoon',            'Updated Date'  => '2013-07-01',            'Filename'      => $this->Classname,            'Version'       => '2.0.0',            'Description'   => 'Create an assembly of parts',            'Update Log'    => array(                '2012-11-19_1.0.0'    => "Module Created",                '2013-01-12_1.1.0'    => "Added 'default' value to form array",                '2013-01-30_1.2.0'    => "Layout design for FormArray() and code cleanup",				'2013-03-12_1.3.0'	=> "Disable the build request option",                '2013-06-04_1.4.0'    => "Bug fixes - move away from 'Loc' database calls",                '2013-07-01_2.0.0'    => "Major change to support complex in/out assemblies",            ),        );        				$this->SetParameters(func_get_args());        $this->Inventory_Assemblies_ID = $this->GetParameter(0);        //$this->Inventory_Assemblies_ID = $this->GetParameter(0);        				                $this->Table                = 'inventory_assemblies';        $this->Index_Name           = 'inventory_assemblies_id';        $this->Default_Sort         = '';      // field for default table sort                                                $this->Field_Titles = array(            "{$this->Table}.inventory_assemblies_id"                => 'Inventory Assembly Id',            "{$this->Table}.assembly_name"                          => 'Assembly Name',            "{$this->Table}.barcode"                                => 'Barcode',            "{$this->Table}.active AS VALUE"                        => 'Value (Average Method)',                      // create fake field for function ProcessTableCell()            "{$this->Table}.notes"                                  => 'Notes',            "{$this->Table}.default"                                => 'Default',            "{$this->Table}.active"                                 => 'active',            "{$this->Table}.updated"                                => 'Updated',            "{$this->Table}.created"                                => 'Created',        );                        $this->Default_Fields   = 'inventory_assemblies_id, assembly_name, barcode';        $this->Unique_Fields    = 'assembly_name';                        $this->Default_Values   = array(            'type_advanced'      => '1',        );                $this->Default_Where        = 'type_advanced=1';        $this->Add_Submit_Name      = "{$this->Table}_SUBMIT_ADD";        $this->Edit_Submit_Name     = "{$this->Table}_SUBMIT_EDIT";        $this->Flash_Field          = $this->Index_Name;                        $this->Close_On_Success     = true;        $this->Edit_Links_Count     = '4';        $this->Use_Selection_Tab    = true;         // (TRUE) false = hide the search tab on the table        $this->Default_List_Size    = 1000;         // how many records to show when table initially loads (higher = slower performance but better user experience)                    } // -------------- END __construct --------------        public function Execute()    {        # FUNCTION :: Main function called after instantiating this class                $action = Get('action');        switch ($action) {            case 'add':                // ----- Adding a new record                #$this->Default_Values['barcode'] = $this->Barcode;                $this->AddRecord();            break;                        case 'valuereport':                // ----- getting the VALUE calculation for this barcode                                // ----- Get the build record used for this assembly                $Obj_BuildRecord                            = new Inventory_AssemblyBuildRecord();                $build_record                               = $Obj_BuildRecord->CreateBuildRecord_FromInventoryAssembliesId($this->Inventory_Assemblies_ID);                $barcode                                    = $Obj_BuildRecord->Barcode_Created;                unset($Obj_BuildRecord);                    // memory clean-up                                //$this->EchoVar('build_record', $build_record);                //exit();                //$this->EchoVar('############### barcode', $barcode);                                                // ----- Get the build record used for this assembly                $Obj_ValueHandler                           = new Inventory_Valuation_ValueHandler();               // instantitate value class                $Obj_ValueHandler->Barcode                  = $barcode;                                             // pass in the barcode we want value for                $Obj_ValueHandler->Build_Record             = $build_record;                                        // pass in the build record we want value for                $Obj_ValueHandler->Quantity                 = 1;                                                    // get value for 1 of these assemblies                $Obj_ValueHandler->Execute();                                                                       // execute the function to get value                $value_array                                = $Obj_ValueHandler->Value_Array;                       // store the value array for report processing                unset($Obj_ValueHandler);                                                                           // memory clean-up                                //$this->EchoVar('value_array', $value_array);                //exit();                                // ----- Create a value report based on the calculated value of the build record                $Obj_ValueReport                            = new Inventory_Valuation_ValueSummaryReport();         // instatiate reporting class                $Obj_ValueReport->Barcode                   = $barcode;                                       // set barcode we want report for                $Obj_ValueReport->Value_Array               = $value_array;                                         // set value arraw we want report for                $Obj_ValueReport->Execute();                                                                        // execute the function to make a report                $report                                     = $Obj_ValueReport->Report;                             // store the created report                unset($Obj_ValueReport);                                                                            // memory clean-up                                                // ----- echo out the report to the screen                echo $report;                                                /*                                // ----- getting the VALUE calculation for this assembly                //AddStylesheet("/css/inventory.css??20121108-1");                                                // ----- Get the build record used for this assembly                $Obj_BuildRecord                            = new Inventory_AssemblyBuildRecord();                $build_record                               = $Obj_BuildRecord->CreateBuildRecord_FromInventoryAssembliesId($this->Inventory_Assemblies_ID);                unset($Obj_BuildRecord);                    // memory clean-up                                                // ----- Calculate the value for this build record                $Obj_ValueAssembly                          = new Inventory_Valuation_ValueAssembly();                $Obj_ValueAssembly->Assembly_Record         = $build_record;                $Obj_ValueAssembly->CalculateValueEach_TotalAverage_AdjustmentAssembly();                $value_each                                 = $Obj_ValueAssembly->Value_Each;                $value_array                                = $Obj_ValueAssembly->Value_Array;                unset($Obj_ValueAssembly);                  // memory clean-up                                                // ----- Create a value report based on the calculated value of the build record                $Obj_ValueReport                            = new Inventory_Valuation_ValueSummaryReport();                $Obj_ValueReport->Value_Array               = $value_array;                $Obj_ValueReport->Inventory_Assemblies_ID   = $this->Inventory_Assemblies_ID;                $report                                     = $Obj_ValueReport->CreateReport_Assembly();                unset($Obj_ValueReport);                    // memory clean-up                                // ----- echo out the report to the screen                echo $report;                */                            break;                        case 'list':            default:                // ----- display list of all assemblies that can be built                $this->ListTable();            break;        }    }        public function ExecuteAjax()    {        # FUNCTION :: Function handles AJAXy calles to this class                $return     = 0;                            // initialize variable        $QDATA      = GetEncryptQuery('eq');        // decode the encrypted query passed in        $action     = Get('action');                // determine what action we're trying to do                                // ----- output debug variables to screen - this will probably kill the return values - but good for debug        //$_GET['show'] = true;        if (Get('show')) {            echo "<br />QDATA = " . ArrayToStr($QDATA);            echo "<br />action = $action";        }                                switch ($action) {                        case 'autocomplete_inventory_lookup':                                // ----- LOOK UP ALL ACTIVE INVENTORY ITEMS                                // ----- query database for records                $query = Get('term');                $records = $this->SQL->GetArrayAll(array(                    'table' => 'inventory_products',                    'keys'  => 'description, retailer_code, barcode, status_retired',                    'where' => "(description LIKE '%{$query}%' OR barcode LIKE '%{$query}%' OR retailer_code LIKE '%{$query}%') AND active=1 AND status_retired=0",                ));                                // ----- add additional fomratting to the return values                $arr = array();                foreach ($records as $record) {                    $retired = ($record['status_retired'] == 1) ? "[Retired] - " : "";                    $arr[] = array(                        'label'             => "{$record['barcode']} - {$retired}{$record['retailer_code']}",                        'description'       => $record['description'],                        'sku'               => $record['retailer_code'],                        'barcode'           => $record['barcode'],                    );                }                                // ----- convert to JSON format                echo json_encode($arr);     // echo out in JSON form                 $return = '';               // clear return value or it will output and screw up return            break;        }                echo $return;    }        private function JavascriptCreateTable()    {        $this->ScriptJSONTableGeneric();                                    // Add in the generic code held in the BaseClass        $eq = EncryptQuery('class=Inventory_InventoryAssemblyCreate');      // Class the autocomplete should call (ideally this class)                        $script = <<<SCRIPT                    // ===== USER DEFINED VARIABLES =====            var targetTextID        = "FORM_autotable_holder";          // defines hidden textarea that will hold the text array            var targetTableDivID    = "autotable_table_display";        // defines the div that wraps the created table            var requiredFieldArray  = ["FORM_temp_1"];            var headerText          = "{$this->Header_Row}";                                    function formTableRow() {                var delimiter       = "|";                var barcode         = $("#FORM_temp_1").val();                var sku             = $("#FORM_temp_2").val();                var description     = $("#FORM_temp_4").val();                var qtyOut          = $("#FORM_temp_5").val();                var qtyIn           = $("#FORM_temp_6").val();                                // ----- create the string that will be added to table.                // ----- no starting or ending delimiter                // ----- action buttons will be added by other function                var output = barcode + delimiter + sku + delimiter + description + delimiter + qtyOut + delimiter + qtyIn;                return output;            }                        function clearDataTextboxes() {                // -- clear the textboxes used to search on table data                $("#FORM_temp_0").val('');                $("#FORM_temp_1").val('');                $("#FORM_temp_2").val('');                $("#FORM_temp_4").val('');                $("#FORM_temp_5").val('');                $("#FORM_temp_6").val('');            }SCRIPT;        AddScript($script);                $script = <<<SCRIPT                        // ----- remove borders from textboxes            $('#FORM_temp_1').addClass('noborder');            $('#FORM_temp_2').addClass('noborder');            $('#FORM_temp_4').addClass('noborder');                                    // ----- autocomplete -----            var termTemplate = "<span class='ui-autocomplete-term'>%s</span>";                        $('#FORM_temp_0').autocomplete({                                 source          : 'http://webmanager.whhub.com/office/AJAX/class_execute.php;eq={$eq};action=autocomplete_inventory_lookup',                minChars        : 0,        // how many characters tot ype before starting function call                selectFirst     : true,     // allows tab to select the top option in returned values                autoFocus       : true,                                // where do we stick the returned results                select: function( event, ui ) {                    $( "#FORM_temp_0" ).val( ui.item.label );                    $( "#FORM_temp_1" ).val( ui.item.barcode );                 // store Barcode                    $( "#FORM_temp_2" ).val( ui.item.sku );                     // store APDM SKU                    $( "#FORM_temp_4" ).val( ui.item.description );             // store Description                         return false;                },                                // format the matched text in search terms                // NOTE/BUG :: Currently case sensitive so doesn't highlight non-case-matching results                open: function(e,ui) {                    var                        acData = $(this).data('autocomplete'),                        styledTerm = termTemplate.replace('%s', acData.term);                    acData                        .menu                        .element                        .find('a')                        .each(function() {                            var me = $(this);                            me.html( me.text().replace(acData.term, styledTerm) );                        });                }            })                        // format the display of the data in the autocomplete (should probably be a custom function on this class)            // (everything else should be in the base class)            .data( "autocomplete" )._renderItem = function( ul, item ) {                return $( "<li>" )                    .data( "item.autocomplete", item )                    .append( "<a>" + item.label + "<br> --- " + item.description + "</a>" )                    .appendTo( ul );            };                                                                        // -- call table creation function on load            // -- rebuilds table after coming back from form errors            var targetText          = $("#" + targetTextID);      // defines hidden textarea that will hold the text array            var targetTableDiv      = $("#" + targetTableDivID);    // defines the div that wraps the created table            createTableFromTextbox(targetText, targetTableDiv);SCRIPT;        AddScriptOnReady($script);    }                public function GetExistingRecords($IA_ID)    {        # FUNCTION :: Get existing line records that are tied to this master record                        // ----- get all records from the database        $so_number = $this->GetSalesOrderNumberFromID($this->Edit_Id);        $records = $this->SQL->GetArrayAll(array(            'table'     => "inventory_assembly_lines",            'keys'      => "inventory_assembly_lines.*, inventory_products.description, inventory_products.retailer_code",            'where'     => ".inventory_assembly_lines.`inventory_assemblies_id`='{$IA_ID}' and `inventory_assembly_lines`.`active`=1",            'joins'     => "LEFT JOIN `inventory_products` ON `inventory_products`.`barcode` = `inventory_assembly_lines`.`barcode`",        ));        $this->EchoQuery();                // ----- format records for output        $count = 1;        $table = $this->Header_Row . "\n";        foreach ($records as $record) {            $del = "<div class='button_delete' id='row_1' onclick='tableDeleteRow(\" row_{$count} \")'>X</div>";            $table .= "{$record['barcode']}|{$record['retailer_code']}|{$record['description']}|{$record['quantity_out']}|{$record['quantity_in']}|{$del}\n";            $count++;        }                // ----- put formatted records into POST array and add javascript to add extra needed line break        $_POST['FORM_autotable_holder'] = $table;        AddScriptOnReady('$("#FORM_autotable_holder").val($("#FORM_autotable_holder").val() + "\n");');    }        public function SetFormArrays()    {        # FUNCTION :: Output the main user form to the screen                                // ----- if editing record - need to get the records from database and put on the page        if (($this->Action == 'EDIT') && (!havesubmit($this->Add_Submit_Name)) && (!havesubmit($this->Edit_Submit_Name))) {                        $ia_id = Post('FORM_inventory_assemblies_id');            $this->GetExistingRecords($ia_id);                    }                $this->JavascriptDisplaySessionMessage();                                                       // Display alert messages        $this->JavascriptCreateTable();                                                                 // Javascript for creating table from array of data        $this->JavascriptToggleFunctionality();                                                         // Javascript for toggling show/hide a div area                $this->JavascriptDisableFunctionality(array('FORM_temp_1','FORM_temp_2','FORM_temp_4'));        // don't allow user to change values on these fields        $this->JavascriptInputNoBorder(array('FORM_temp_1','FORM_temp_2','FORM_temp_4'));               // don't show form border on these fields                $btn_add            = MakeButton('positive', 'ADD', '', '', 'btn_add', "addDataToTable()", 'button', 'btn_add');        $btn_clear          = MakeButton('negative', 'CLEAR', '', '', 'btn_clear', "clearDataTextboxes()", 'button', 'btn_clear');                $title_template     = "<span class=\"formtitle\" style='font-weight:bold;'>@</span>\n";         // -------- Template for the title (modifies default)        $info_template      = "<br /><span class=\"forminfo\">@</span>\n\n";                            // -------- templete for the input field (modifies default)                                $base_array = array(            "form|$this->Action_Link|post|db_edit_form",                        "code|<div class='shadow form_section_wrapper'>",                'code|<div class="form_section_header">ASSEMBLY BEING CREATED</div>',                'code|<br />',                'text|Assembly Name|assembly_name|Y|60|255',                'code|<br />',                                #"code|<div class='form_section_wrapper_search'>",                #    "text|Search|temp_10|N|60|100",                #    "text|Barcode Created|barcode|N|60|100",                #    "text|Quantity Created|quantity|N|60|100",                #    'checkbox|Default (if multiple)|default||1|0',                #"code|</div>",            "code|</div>",            'code|<br /><br />',                                    "code|<div class='shadow form_section_wrapper'>",                'code|<div class="form_section_header">ASSEMBLY PARTS</div>',                                "code|<div class='form_section_wrapper_search'>",                    "text|Search|temp_0|N|60|100",                    "text|Barcode|temp_1|N|60|100",                    "text|APDM SKU|temp_2|N|60|100",                    "text|Description|temp_4|N|60|100",                                                            "titletemplate|{$this->title_template}",                    "infotemplate|{$this->info_template}",                                        "code|<div style='padding-left:120px;'><table><tr>",                    "code|<td>",                        "text|QTY REMOVE|temp_5|N|10|100",                    "code|</td>",                    "code|<td>",                        "text|QTY ADD|temp_6|N|10|100",                    "code|</td>",                    "code|</tr></table></div>",                                        "titletemplate|STD",                    "infotemplate|STD",                                                            "info||$btn_add &nbsp;&nbsp;&nbsp; $btn_clear",                "code|</div>",                'code|<br /><br />',                                "code|<div id='autotable_table_display'></div>",                            "code|</div>",            'code|<br /><br />',                                    "code|<div class='shadow form_section_wrapper'>",                'code|<div class="form_section_header">NOTES <a class="toggle" href="#" id="menu_group_1"><span class="updown">&nbsp;</span></a></div>',                'code|<div class="menu_group" style="display:none;" id="div_menu_group_1">',                    'textarea|Notes|notes|N|60|4',                "code|</div>",            "code|</div>",                                    'code|<div style="display:none;">',                'textarea|Temp 3|autotable_holder|N|60|4',                'text|inventory_assemblies_id|inventory_assemblies_id|N|11|11',                'text|Barcode Being Created|inventory_assemblies_id|N|11|11',                'checkbox|Type Advanced|type_advanced||1|0',            'code|</div>',                                );                if ($this->Action == 'ADD') {            $base_array[] = "submit|Add Record|$this->Add_Submit_Name";            $base_array[] = 'endform';            $this->Form_Data_Array_Add = $base_array;        } else {            //$base_array[] = 'checkbox|Active|active||1|0';            $base_array[] = "submit|Update Record|$this->Edit_Submit_Name";            $base_array[] = 'endform';            $this->Form_Data_Array_Edit = $base_array;        }    }        public function PostProcessFormValues($FormArray)     {                /* ======================== PSEUDOCODE ========================                        Note: Need to put in failure checks before a transaction is allowed to go through.                =============================================================== */                $delimiter              = '|';        $table_holder           = 'autotable_holder';        $field_kickout_string   = 'temp_';        $lines_array_add        = array();                                      // will hold lines being added        $lines_array_delete     = array();                                      // will hold lines being deleted        $passed                 = true;                                         // holds check-passed status        $adding                 = ($this->Action == 'ADD') ? true : false;      // hold edit status               //echo ArrayToStr($FormArray);                                // ----- remove any temp fields from the array so they don't get processed        // ----- NOTE :: WASTED STEP IF WE BLOW OUT ARRAY AT END OF FUNCTION        foreach ($FormArray as $field => $value) {            echo "{$field} => {$value} <br />";            $pos = strpos($field, $field_kickout_string);            if ($pos !== false) {                unset($FormArray[$field]);    //remove the field            }        }                //echo ArrayToStr($_POST);        //exit();                                        // ----- check for unique Assembly Name        if ($adding) {            $unique = $this->SQL->IsUnique('inventory_assemblies', 'assembly_name', $FormArray['assembly_name'], 'active=1');            if (!$unique) {                $this->Error .= "<br />ERROR :: Assembly Name is not unique. Name: {$FormArray['assembly_name']}";                $passed = false;            }        }                                        // ----- get the main table holder value to process        $table = $FormArray[$table_holder];                //echo "table ===> {$table}";                if ($table) {            $lines = explode("\n", $table);            $header_row = true;            #echo ArrayToStr($lines);                        foreach ($lines as $key => $line) {                                //echo "<br />line ===> $line";                if ($line) {                                        //echo ArrayToStr($line);                                    if ($header_row == false) {                        $parts          = explode($delimiter, $line);                                                #echo ArrayToStr($parts);                                                $barcode        = trim($parts[0]);                        $sku            = trim($parts[1]);                        $description    = trim($parts[2]);                        $quantity_out   = trim($parts[3]);                        $quantity_in    = trim($parts[4]);                                                // ----- check that inventory still exists                        //$exist = $this->StatusInventoryItemExist($barcode);                        $exist = true;                                                                        if (!$exist) {                            $this->Error .= "<br />ERROR :: Inventory item does not exist. Barcode: {$barcode}";                            $passed = false;                        } else {                                                        $lines_array_add[] = array(                                'inventory_assemblies_id'   => '',              // will be added at later step                                'barcode'                   => $barcode,                                'quantity_out'              => $quantity_out,                                'quantity_in'               => $quantity_in,                            );                                                    }                    } else {                        // have to set header to false here or it could trigger on blank first line                        $header_row     = false;                    }                } //end blank line check                            }        } //end empty table check                                //$this->Error .= "<br />PURPOSFUL HALTING ERROR";                                                // ----- verify if any good lines made it to processing - we don't want to allow a 0-line PO        if (!$lines_array_add) {            $passed = false;            $this->Error .= "THIS ASSEMBLY HAS NO VALID LINES TO PROCESS";        }        //exit();//$passed = false;                                // ----- if all checks have passed after processing        if ($passed && $lines_array_add) {                                    # ===== START TRANSACTION ============================================================            $this->SQL->StartTransaction();                        # ----- Create the Sales Order entry            $db_record = array(                'assembly_name'         => $FormArray['assembly_name'],                'barcode'               => $FormArray['barcode'],                'quantity'              => $FormArray['quantity'],                'notes'                 => $FormArray['notes'],                'default'               => $FormArray['default'],                'type_advanced'         => $FormArray['type_advanced'],            );                        if ($adding) {                $result = $this->SQL->AddRecord(array(                        'table'     => 'inventory_assemblies',                        'keys'      => $this->SQL->Keys($db_record),                        'values'    => $this->SQL->Values($db_record),                    ));                $passed                     = (!$result) ? false : $passed;                $inventory_assemblies_id    = $this->SQL->Last_Insert_Id;                                $this->SQL->EchoQuery($this->Classname, __FUNCTION__);                            } else {                $where  = "`inventory_assemblies_id`='{$FormArray['inventory_assemblies_id']}' AND `active`=1";                $result = $this->SQL->UpdateRecord(array(                        'table'         => 'inventory_assemblies',                        'key_values'    => $this->SQL->KeyValues($db_record),                        'where'         => $where,                    ));                $passed                     = (!$result) ? false : $passed;                $inventory_assemblies_id    = $FormArray['inventory_assemblies_id'];                                $this->SQL->EchoQuery($this->Classname, __FUNCTION__);            }                                                # ----- Delete old assembly LINES entry            // ---- currently this is the fastest way to deal with editing lines - because we don't need            //      to track the IDs in the on-screen table. Just deactivate lines and let them be added again.            if (!$adding) {                $db_record = array(                    'active'            => 0,                );                $where      = "`inventory_assemblies_id`='{$inventory_assemblies_id}' AND `active`=1";                $result     = $this->SQL->UpdateRecord(array(                        'table'         => 'inventory_assembly_lines',                        'key_values'    => $this->SQL->KeyValues($db_record),                        'where'         => $where,                    ));                $passed     = (!$result) ? false : $passed;                $this->SQL->EchoQuery($this->Classname, __FUNCTION__);            }                                    # ----- Create the Sales Order LINES entry            foreach ($lines_array_add as $line) {                $db_record = array(                    'inventory_assemblies_id'   => $inventory_assemblies_id,                    'barcode'                   => $line['barcode'],                    'quantity_out'              => $line['quantity_out'],                    'quantity_in'               => $line['quantity_in']                );                                $result = $this->SQL->AddRecord(array(                        'table'     => 'inventory_assembly_lines',                        'keys'      => $this->SQL->Keys($db_record),                        'values'    => $this->SQL->Values($db_record),                    ));                $passed     = (!$result) ? false : $passed;                $this->SQL->EchoQuery($this->Classname, __FUNCTION__);            }                                                                                                # ===== COMMIT TRANSACTION ============================================================            if ($passed) {                $this->SQL->TransactionCommit();        // run the database queries                                                                // ----- trigger a sucess message                if ($adding) {                    #echo "HERE";                    #exit();                                        #return $FormArray;                    #$this->SuccessfulAddRecord();                                        // ----- Do A force redirect to bypass BaseClass further procesing this                    $link = $this->getPageURL();                    $_SESSION['alert_message'] = ($adding) ? "ASSEMBLY ADDED SUCESSFULLY" : "ASSEMBLY UPDATED SUCESSFULLY";                    header("Location: {$link}");                                    } else {                    $flash      = $this->Idx;                    $id         = $this->Idx;                    $id_field   = $this->Index_Name;                    $this->SuccessfulEditRecord($flash, $id, $id_field);                }                                /*                // ----- Do A force redirect to bypass BaseClass further procesing this                $link = $this->getPageURL();                $_SESSION['alert_message'] = ($adding) ? "ASSEMBLY ADDED SUCESSFULLY" : "ASSEMBLY UPDATED SUCESSFULLY";                header("Location: {$link}");                */            }        } else {            // ----- return form array to process any legitimate errors            return $FormArray;        }    }        public function ProcessTableCell($field, &$value, &$td_options, $id='')    {        # ============ WHEN VIEWING A TABLE OF ALL RECORDS ============                parent::ProcessTableCell($field, $value, $td_options, $id);        switch ($field) {            default:                // ----- MODIFY THE OPTIONS IN THE MAIN TABLE DISPLAY -----                $CLASS_EXECUTE_LINK     = '/office/class_execute';                $eq                     = EncryptQuery("class=Inventory_InventoryAssemblyRequest;v1={$id};");                $link                   = $CLASS_EXECUTE_LINK . '?eq=' . $eq;                $script                 = "top.parent.appformCreate('Window', '{$link}', 'apps'); return false;";                                $eq2                    = EncryptQuery("class=Inventory_InventoryAssemblyBuild;v3={$id};");                $link2                  = $CLASS_EXECUTE_LINK . '?eq=' . $eq2 . '&action=add';                $script2                = "top.parent.appformCreate('Window', '{$link2}', 'apps'); return false;";                                $eq3                    = EncryptQuery("class=Inventory_InventoryAssemblyCreate;v1={$id};");                $link3                  = $CLASS_EXECUTE_LINK . '?eq=' . $eq3 . '&action=valuereport';                $script3                = "top.parent.appformCreate('Window', '{$link3}', 'apps'); return false;";                                				// ----- output the action buttons                $col_edit       = ($this->Flags['edit'] == 'false')     ? ''    : "<a href=`#` class=`row_edit`         title=`Edit`            onclick=`tableEditClick('@IDX@','@VALUE@','@EQ@', '@TITLE@'); return false;`></a>";                $col_request    = ($this->Flags['request'] == 'false')  ? ''    : "<a href=`#` class=`row_viewline`     title=`Request`         onclick=`{$script}; return false;`></a>";                $col_build      = ($this->Flags['build'] == 'false')    ? ''    : "<a href=`#` class=`row_receive`      title=`Build`           onclick=`{$script2}; return false;`></a>";                //$col_value      = ($this->Flags['value'] == 'false')    ? ''    : "<a href=`#` class=`row_dollarsign`   title=`Value Report`    onclick=`{$script3}; return false;`></a>";                $col_value = "";                $col_delete     = ($this->Flags['delete'] == 'false')   ? ''    : "<a href=`#` class=`row_delete`       title=`Delete`          onclick=`tableDeleteClick('@IDX@','@VALUE@','@EQ@'); return false; `></a>";                				                $this->Edit_Links = qqn("                    <td align=`center`>{$col_edit}</td>                    <td align=`center`>{$col_build}</td>                    <td align=`center`>{$col_value}</td>                    <td align=`center`>{$col_delete}</td>                    ");										//<td align=`center`>{$col_request}</td>            break;                        /*            case 'VALUE':                // ----- calculate the current VALUE for this assembly                                $Obj_BuildRecord                        = new Inventory_AssemblyBuildRecord();                $build_record                           = $Obj_BuildRecord->CreateBuildRecord_FromInventoryAssembliesId($id);                                $Obj_ValueAssembly                      = new Inventory_Valuation_ValueAssembly();                $Obj_ValueAssembly->Assembly_Record     = $build_record;                $Obj_ValueAssembly->CalculateValueEach_TotalAverage_AdjustmentAssembly();                                $value_each     = $Obj_ValueAssembly->Value_Each;                $value_array    = $Obj_ValueAssembly->Value_Array;                                $report = '';                if ($this->Show_Report) {                                        // NOTE :: Has error because page_helper functions addScriptOnReady not avilable in this part of code                    //         and the report used Javacript for drop-down functionality.                                        $Obj_ValueReport                        = new Inventory_Valuation_ValueSummaryReport();                    $Obj_ValueReport->Value_Array           = $value_array;                    $report                                 = "<br />" . $Obj_ValueReport->Execute();                    unset($Obj_ValueReport);                }                                unset($Obj_BuildRecord);                unset($Obj_ValueAssembly);                                                $value = money_format("%n", $value_each) . $report;                            break;            */        }    }    }  // -------------- END CLASS --------------