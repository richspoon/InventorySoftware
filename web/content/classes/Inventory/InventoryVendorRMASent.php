<?phpclass Inventory_InventoryVendorRMASent extends Inventory_InventoryBase{    public $Show_Query      = false;    public $Record_ID       = 0;    public $RMA_Number      = 0;            public function  __construct()    {        parent::__construct();        $this->SetSQLInventory();   // set the database connection to the inventory database                $this->Classname = get_class($this);        $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2013-01-03',            'Updated By'    => '',            'Updated Date'  => '',            'Filename'      => $this->Classname,            'Version'       => '1.0',            'Description'   => 'Show what lines have alrady been sent on a vendor RMA',            'Update Log'    => Array(                '2013-01-03_001'  => "Module Created",            ),        );                $this->Classname = get_class($this);                $this->SetParameters(func_get_args());        $this->RMA_Number           = $this->GetParameter(0);                        $this->Table                = 'inventory_vendor_rma_sent';        $this->Index_Name           = 'inventory_vendor_rma_sent_id';                $this->Default_Where        = "`rma_number`='{$this->RMA_Number}' AND inventory_vendor_rma_sent.active=1";        $this->Default_Sort         = 'date';      // field for default table sort                $this->Add_Submit_Name      = "{$this->Table}_SUBMIT_ADD";        $this->Edit_Submit_Name     = "{$this->Table}_SUBMIT_EDIT";        $this->Flash_Field          = $this->Index_Name;                $this->Field_Titles = array(            "{$this->Table}.inventory_vendor_rma_sent_id"           => 'Inventory Vendor RMA Received Id',            "{$this->Table}.inventory_vendor_rma_lines_id"          => 'RMA Line ID',            "{$this->Table}.rma_number"                             => 'RMA Number',            "{$this->Table}.date"                                   => 'Date',            "{$this->Table}.quantity"                               => 'Quantity',            //"{$this->Table}.price_shipping"                         => 'Shipping Price',            "{$this->Table}.price_each"                            => 'Price (ea)',            "{$this->Table}.price_total"                            => 'Total Price',            "{$this->Table}.price_shipping_each"                            => 'Shipping (ea)',            "{$this->Table}.price_shipping_total"                            => 'Shipping Total',                        "{$this->Table}.barcode"                                => 'Barcode',                                    "inventory_products.description"                        => 'Description',            //"inventory_products.manufacturer_code"                  => 'Manufacturer SKU',            "inventory_products.retailer_code"                      => 'APDM SKU',            "{$this->Table}.notes"                                  => 'Notes',            "{$this->Table}.active"                                 => 'Active',            "{$this->Table}.updated"                                => 'Updated',            "{$this->Table}.created"                                => 'Created',        );                        $this->Join_Array = Array(            'inventory_products'  => "LEFT JOIN `inventory_products` ON `inventory_products`.`barcode` = `inventory_vendor_rma_sent`.`barcode`",        );                        $this->Default_Fields   = 'date, barcode, description, retailer_code, quantity, price_each, price_total, price_shipping_each, price_shipping_total';        $this->Unique_Fields    = '';        $this->Default_Values   = array();        $this->Close_On_Success = true;                $this->Edit_Links_Count     = '1';          // number of links at end of table        $this->Add_Link             = '';           // don't allow adding a record        $this->Show_Export          = false;        // false = don't allow export of this table        $this->Default_List_Size    = 1000;         // how many lines to allow in table before pagination        $this->Use_Selection_Tab    = false;        // false = hide the search tab on the table                    } // -------------- END __construct --------------        public function Execute()    {                // Call the AddRecord to get a form onto the screen        // Convert the Record_Id into the barcode        // Pull the entire order info from the barcode        // Convert the order to a table for display                        AddStylesheet("/css/inventory.css??20121108-1");        $this->AddRecord();        $record     = $this->GetRecordInfoFromBarcode($this->Record_ID);        $content    = $this->ConvertRecordInfoToArray($record);        $this->ScriptJSONTableGeneric();                                // Add in the generic code held in the BaseClass        $this->MoveArrayIntoForm($content);                AddScriptOnReady("$('#FORM_rma_number').val('{$this->RMA_Number}');");                // ----- add the jQuery UI datepicker functionality        JavascriptDatepickerFunctionality(array('FORM_date'));            }        public function ExecuteAjax()    {        //$QDATA = GetEncryptQuery('eq');        $action = Get('action');                //$_GET['show'] = true;                if (Get('show')) {            echo "<br />QDATA = " . ArrayToStr($QDATA);            echo "<br />action = $action";        }                $return = 0;                switch ($action) {                        case 'delete_received_inventory':                                /* P-CODE =================================================                Start Transaction                Deactivate the 'inventory_vendor_rma_sent' line                Remove the inventory from 'inventory_holds'                Update 'inventory_vendor_rma_lines' to 'partial' or 'open'                Update 'inventory_vendor_rmas' to 'partial' or 'open'                End Transaction                ================================================= */                                $this->Show_Query = false;                $passed = true;                                $inventory_vendor_rma_sent_id = Get('id'); //0;                                if ($inventory_vendor_rma_sent_id != 0) {                                        # ===== START TRANSACTION ============================================================                    $this->SQL->StartTransaction();                                        # ----- Deactivate the 'inventory_vendor_rma_sent' line -----                    $db_record = array(                        'active'    => 0,                        'notes'     => 'Inventory previously sent to vendor via RMA deleted by user',                    );                    $where      = "`inventory_vendor_rma_sent_id`='{$inventory_vendor_rma_sent_id}'";                    $result     = $this->UpdateRecordLoc('inventory_vendor_rma_sent', $db_record, $where);                    $passed     = (!$result) ? false : $passed;                    $this->EchoQuery();                                                            # ----- Remove the inventory from 'inventory_holds' -----                    $db_record = array(                        'active'    => 0,                        'notes'     => 'Inventory previously sent to vendor via RMA deleted by user',                    );                    $where      = "`ref_vendor_rma_sent_id`='{$inventory_vendor_rma_sent_id}'";                    $result     = $this->UpdateRecordLoc('inventory_holds', $db_record, $where);                    $passed     = (!$result) ? false : $passed;                    $this->EchoQuery();                                                                                # ===== COMMIT TRANSACTION ============================================================                    if ($passed) {                        $this->SQL->TransactionCommit();                        $return = 1;                    } else {                        $return = 0;                    }                                                                        } // end checking for 'inventory_vendor_rma_sent_id'            break;        }                echo $return;    }        function ScriptUnreceiveInventory()    {        $CLASS_EXECUTE_LINK     = '/office/AJAX/class_execute';        $eq                     = EncryptQuery("class={$this->Classname}");                $script = <<<SCRIPT                    function tableUnreceiveInventoryClick(idx, value, eq)            {                var idbase = 'TABLE_ROW_ID' + idx + '_';                var rowNumber = $('#' + idbase + value + ' td:first-child').html().replace('.', '');                $('#' + idbase + value +' td').css('background-color','#ff7');                if (confirm('Are you sure you want to delete row (' + rowNumber + ')?')) {                    $.get('{$CLASS_EXECUTE_LINK}?eq={$eq}&action=delete_received_inventory&id=' + value, '', function(data){                        if (data == 1) {                            location.reload();  // reload the page as many items need to be updated                            //$('#' + idbase + value +' td').fadeOut();                        } else {                            alert('Error: Could not delete record! :: ' + data);                        }                    });                }                $('#' + idbase + value +' td').css('background-color','');                return false;            }                SCRIPT;        AddScript($script);    }        public function SetFormArrays()    {        // not using function because we won't edit these lines    }        public function GetTableHeading($colcount)    {        $export = ($this->Show_Export)? $this->GetExportBlock() : '';        $RESULT = '            <tr class="TABLE_TITLE">                <td colspan="'. $colcount. '">                ' . $export . '                    Previously Received Inventory                </td>            </tr>';        return $RESULT;    }        public function ProcessTableCell($field, &$value, &$td_options, $id='')    {        # ============ WHEN VIEWING A TABLE ============                parent::ProcessTableCell($field, $value, $td_options, $id);        switch ($field) {            default:                // ----- MODIFY THE OPTIONS IN THE MAIN TABLE DISPLAY -----                $CLASS_EXECUTE_LINK     = '/office/AJAX/class_execute';                $eq                     = EncryptQuery("class={$this->Classname};v1={$id}");                $link                   = $CLASS_EXECUTE_LINK . '?eq=' . $eq . "&action=delete_received_inventory&id={$id}";                $script                 = "top.parent.appformCreate('Window', '{$link}', 'apps'); return false;";                                $this->Edit_Links = qqn("                <td align=`center`><a href=`#` class=`row_delete`   title=`Delete` onclick=`tableUnreceiveInventoryClick('@IDX@','@VALUE@','@EQ@'); return false; `></a></td>                                        ");                    //<td align=`center`><a href=`#` class=`row_delete` title=`View Lines`  onclick=`{$script}; return false;`></a></td>                    //                                                                        break;                                    case "date":                $value = date('M d, Y', strtotime($value));            break;                        case "quantity":                $value = number_format($value);            break;                        case "price_each":            case "price_total":            case "price_shipping_each":            case "price_shipping_total":                $value = money_format('%n', $value);            break;        }    }        }  // -------------- END CLASS --------------