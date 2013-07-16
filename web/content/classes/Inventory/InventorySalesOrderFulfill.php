<?phpclass Inventory_InventorySalesOrderFulfill extends Inventory_InventoryBase{    public $Show_Query                  = false;    // (false) TRUE = show database queries    public $Record_ID                   = 0;    public $SO_Number                   = 0;    public $Show_Previous_Fulfilled     = true;     // (true) TRUE = show the inventory previously sent on this SO    public $Header_Text                 = "ID|Barcode|Description|Status|QTY<br />Ordered|Price Ea|Price Total|QTY<br />Previously<br />Sent|QTY<br />Remaining|QTY<br />Sent|Price<br />Total|Send<br />Remaining|QTY<br />In Stock";    public $Inventory_Sales_Orders_Id   = 0;	    public function  __construct()    {        parent::__construct();        $this->SetSQLInventory();   // set the database connection to the inventory database        		$this->Classname = get_class($this);        $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2012-11-14',            'Updated By'    => 'Richard Witherspoon',            'Updated Date'  => '2013-03-13',            'Filename'      => $this->Classname,            'Version'       => '1.3',            'Description'   => 'Fulfill by sending out an existing sales order - supports partial sends',            'Update Log'    => array(                '2012-11-14_1.0'    => "Module Created",                '2013-01-04_1.1'    => "Added qty available information when fulfilling and moved Header_Text to global variable",                '2013-01-07_1.2'    => "Added 'return false;' to ConvertRecordInfoToArray() function so JS wouldn't jump to top of page",				'2013-03-13_1.3'	=> "Modified to support database change - track by id not SO#",            ),        );                $this->SetParameters(func_get_args());        $this->Record_ID         = $this->GetParameter(0);        $this->Add_Record_Link  = HexDecodeString($this->GetParameter(1));        #$this->Retailer_Code    = $this->GetParameter(2);                $this->Table            = 'inventory_sales_orders';        $this->Index_Name       = 'inventory_sales_orders_id';                $this->Default_Where    = '';               // additional search conditions        $this->Default_Sort     = 'so_number';      // field for default table sort                $this->Add_Submit_Name  = "{$this->Table}_SUBMIT_ADD";        $this->Edit_Submit_Name = "{$this->Table}_SUBMIT_EDIT";        $this->Flash_Field      = $this->Index_Name;                $this->Field_Titles = array(            "{$this->Table}.inventory_sales_orders_id"           => 'Inventory Sales Orders Id',                        "{$this->Table}.so_number"                              => 'SO Number',            "{$this->Table}.ref_document_url"                       => 'Ref URL',            "{$this->Table}.ref_document_number"                    => 'Ref Number',            "{$this->Table}.date"                                   => 'Date',            #"{$this->Table}.cost_shipping"                          => 'Cost (shipping)',            #"{$this->Table}.cost_other"                             => 'Cost (other)',            "{$this->Table}.status"                                 => 'Status',                        "{$this->Table}.notes"                                  => 'Notes',            "{$this->Table}.active"                                 => 'Active',            "{$this->Table}.updated"                                => 'Updated',            "{$this->Table}.created"                                => 'Created',                        #"(SELECT SUM(`price_total`) AS EST_LINE_ITEM_COST FROM `inventory_sales_order_lines` WHERE `inventory_sales_order_lines`.`so_number` = `inventory_sales_orders`.`so_number` AND `inventory_sales_order_lines`.active=1) AS EST_LINE_ITEM_COST" => 'Estimated Line Item Cost',                    );                        $this->Join_Array = Array(            'inventory_counts'  => "LEFT OUTER JOIN `inventory_sales_order_lines` ON `inventory_sales_order_lines`.`so_number` = `inventory_sales_orders`.`so_number`",        );        $this->Default_Fields   = 'so_number, date, status, EST_LINE_ITEM_COST, cost_shipping, cost_other';        $this->Unique_Fields    = 'so_number';                        $this->Add_Link         = '';       // don't allow adding a record                $this->Default_Values   = array(            'status'    => 'open',            'date'      => date('Y-m-d'),        );                $this->Close_On_Success = true;            } // -------------- END __construct --------------        public function Execute()    {        # FUNCTION :: Main function called after instantiating this class        				// Call the AddRecord to get a form onto the screen        // Convert the Record_Id into the barcode        // Pull the entire order info from the barcode        // Convert the order to a table for display                $this->JavascriptSendRemainingInventory();        $this->AddRecord();        $record     = $this->GetRecordInfoFromBarcode($this->Record_ID);        $content    = $this->ConvertRecordInfoToArray($record);        $this->ScriptJSONTableGeneric();                                // Add in the generic code held in the BaseClass        $this->MoveArrayIntoForm($content);        		$this->Inventory_Sales_Orders_Id = $this->Record_ID;		        AddScriptOnReady("			$('#FORM_so_number').val('{$this->SO_Number}');			$('#temp_sonumber').html('{$this->SO_Number}');						$('#FORM_inventory_sales_orders_id').val('{$this->Inventory_Sales_Orders_Id}');            $('#temp_inventory_sales_orders_id').html('{$this->Inventory_Sales_Orders_Id}');            $('#temp_header_id').html('(ID: {$this->Inventory_Sales_Orders_Id})');                         // ID number at top of page        ");                // ----- add the jQuery UI datepicker functionality        AddScriptOnReady('$("#FORM_date").datepicker({dateFormat: "yy-mm-dd"});');                if ($this->Show_Previous_Fulfilled) {            echo "<br /><br />";            echo "<div style='color:red; font-size:12px; font-weight:bold;'>The table below shows inventory that has already been fulfilled.<br />If you delete any of these lines the current window will refresh and you will lose any details on this screen.</div>";            $OBJ_RCVD = new Inventory_InventorySalesOrderFulfilled($this->SO_Number);            $OBJ_RCVD->ScriptUnreceiveInventory();            echo $OBJ_RCVD->ListTable();        }    }        public function ExecuteAjax()    {        # FUNCTION :: Function handles AJAXy calles to this class                $return     = 0;                            // initialize variable        $QDATA      = GetEncryptQuery('eq');        // decode the encrypted query passed in        $action     = Get('action');                // determine what action we're trying to do                                // ----- output debug variables to screen - this will probably kill the return values - but good for debug        //$_GET['show'] = true;        if (Get('show')) {            echo "<br />QDATA = " . ArrayToStr($QDATA);            echo "<br />action = $action";        }                                switch ($action) {                        case 'autocomplete_inventory_lookup':                                // LOOK UP ALL ACTIVE INVENTORY ITEMS                                // query database for records                $query = Get('term');                $records = $this->SQL->GetArrayAll(array(                    'table' => ' inventory_products',                    'keys'  => 'description, retailer_code, barcode',                    'where' => "(description LIKE '%{$query}%' OR barcode LIKE '%{$query}%' OR retailer_code LIKE '%{$query}%') AND active=1",                ));                                // for records into array format for JSON                $arr = array();                foreach ($records as $record) {                    $arr[] = array(                        'label'             => "{$record['barcode']} - {$record['retailer_code']}",                        'description'       => $record['description'],                        'sku'               => $record['retailer_code'],                        'barcode'           => $record['barcode'],                    );                }                                // convert to JSON format                echo json_encode($arr);     // echo out in JSON form                 $return = '';               // clear return value or it will output and screw up return            break;        }                echo $return;    }                public function GetRecordInfoFromBarcode($RID)    {        $temp_array = array();                if ($RID) {                    // ===== Get the SO details from the passed in Record_Id            $record_so = $this->SQL->GetRecord(array(                'table' => 'inventory_sales_orders',                'keys'  => '*',                'where' => "`inventory_sales_orders_id`='$RID' AND (active=1 OR active=0)",            ));            if ($this->Show_Query) { echo "<br /><br />" . $this->SQL->Db_Last_Query; }                                    // ===== get all the lines from the SO along with inventory item details            $this->SO_Number = $record_so['so_number'];            if ($this->SO_Number) {                $record_lines = $this->SQL->GetArrayAll(array(                    'table' => 'inventory_sales_order_lines',                    'keys'  => 'inventory_sales_order_lines.*, inventory_products.description, inventory_products.manufacturer_code, inventory_products.retailer_code,                               (SELECT SUM(`quantity`) FROM `inventory_sales_order_sent` WHERE `inventory_sales_order_sent`.`inventory_sales_order_lines_id` = `inventory_sales_order_lines`.`inventory_sales_order_lines_id` AND `inventory_sales_order_sent`.active=1) AS QTY_PREV_RCVD',                    'where' => "`inventory_sales_order_lines`.`so_number`='{$this->SO_Number}' AND (inventory_sales_order_lines.active=1 )", //OR inventory_sales_order_lines.active=0                    'joins' => "LEFT JOIN `inventory_products` ON `inventory_products`.`barcode` = `inventory_sales_order_lines`.`barcode`",                ));                if ($this->Show_Query) { echo "<br /><br />" . $this->SQL->Db_Last_Query; }                                // ===== convert lines to a workable array                // ===== the order put into this array is how it will be displayed as a table                foreach ($record_lines as $line) {                                        $price_each                         = ($line['price_total'] / $line['quantity']);                    $qty_remain                         = $line['quantity'] - $line['QTY_PREV_RCVD'];                    $inventory_sales_order_lines_id     = $line['inventory_sales_order_lines_id'];                    $line_status                        = $this->StatusSalesOrderLine($inventory_sales_order_lines_id);                                                                                $temp_array[] = array(                        'lines_id'          => $line['inventory_sales_order_lines_id'],                        'barcode'           => $line['barcode'],                        'description'       => "<b>{$line['retailer_code']}</b><br />{$line['description']}",                        'status'            => $line_status,                        'quantity'          => number_format($line['quantity']),                        'price_each'        => money_format('%n', $price_each),                        'price_total'       => money_format('%n', $line['price_total']),                        'quantity_rcvd'     => $line['QTY_PREV_RCVD'],                        'quantity_remain'   => $qty_remain,                    );                                        //echo ArrayToStr($line);                }            }        } // end RID check                return $temp_array;    }        public function ConvertRecordInfoToArray($ARR)    {        # FUNCTION :: Take an array of order lines and convert it to a delimited text        #             which will later be stored in a textarea and converted to a table                $output     = "";        $newline    = "\\n";        $delimiter  = "|";        $count      = 0;                // ----- loop through each record and form array        // ----- output will be based on the order the array values are structured        foreach ($ARR as $record) {                        $status = $record['status'];            foreach ($record as $k => $v) {                $output .= "{$v}{$delimiter}";            }                        // ----- add in the action boxes            if (($status == 'open') || ($status == 'partial')) {                $count++;   // do count here so first row willhave an ID of 1                $qty        = "<input id='qty_{$count}' name='qty_{$count}' size='4' maxlength='255' value='' type='text' />";                $price      = "<input id='price_{$count}' name='price_{$count}' size='4' maxlength='255' value='' type='text' />";                                $js_price   = ($this->CleanMoney($record['price_each']));                   // strip off dollar sign                $js_qty     = intval($this->CleanNumber($record['quantity_remain']));       // remove comans and convert to integer                $send       = "<div class='button_send' onclick='sendRemainingInventory({$js_price}, {$js_qty}, {$count}); return false;'><center><a href='#' class='row_checkmark' title='Send Remaining Inventory'></a></center></div>";                            } else {                // add the actions form elements to the table row                $qty    = 'n/a';                $price  = 'n/a';                $send   = 'n/a';            }                        $qty_available = $this->GetInventoryQuantityAvailable($record['barcode']);                        // add the actions form elements to the table row            $output .= $qty . $delimiter . $price . $delimiter . $send . $delimiter . $qty_available . $delimiter;                        $output = substr($output, 0, -1);           // trim off the trailing slash            $output .= $newline;                        // add line return        }                        // ----- put the header at the top of the table        $output         = $this->Header_Text . $newline . $output;                return $output;    }        public function MoveArrayIntoForm($CONTENT)    {        # FUNCTION :: Get the delimited text and convert to a table using JavaScript                // ----- feed this array into javascript to put in holder area        $script = "            var targetTextID        = 'FORM_autotable_holder';          // defines hidden textarea that will hold the text array            var targetTableDivID    = 'autotable_table_display';        // defines the div that wraps the created table                        var targetText          = $('#' + targetTextID);            // defines hidden textarea that will hold the text array            var targetTableDiv      = $('#' + targetTableDivID);        // defines the div that wraps the created table                        targetText.val(\"{$CONTENT}\");                             // put the array into textbox            createTableFromTextbox(targetText, targetTableDiv);         // call function to create table from array        ";        AddScriptOnReady($script);    }                    public function SetFormArrays()    {        # FUNCTION :: Output the main user form to the screen        		$this->JavascriptDisplaySessionMessage();   // Display alert messages                $btn_add        = MakeButton('positive', 'ADD', '', '', 'btn_add', "addDataToTable()", 'button', 'btn_add');        $btn_clear      = MakeButton('negative', 'CLEAR', '', '', 'btn_clear', "clearDataTextboxes()", 'button', 'btn_clear');        $btn_submit     = ($this->Action == 'ADD') ? "Add Record|{$this->Add_Submit_Name}" : "Update Record|{$this->Edit_Submit_Name}";                //$po_status          = "open|partial|closed|canceled";       	           $base_array = array(            "form|$this->Action_Link|post|db_edit_form",                                    "code|<div style='font-size:16px; color:blue; padding:5px; background-color:#ccc;'>SO Number: <span id='temp_sonumber'></span>    <div style='float:right;'><span id='temp_header_id'></span></div>     <div class='clear'></div>    </div><br />",            						# ------------------			#			# -- SO HEADER INFORMATION ?? --			#			# ------------------									            "code|<div style='border:1px dashed blue; padding:5px; background-color:#efefef;'><table><tr>",            "code|<td valign='top'>",                				 #'text|INV SO ID|inventory_sales_orders_id|Y|10|255',                'hidden|inventory_sales_orders_id',                                                #'text|SO Number|so_number|Y|10|255',                #'hidden|so_number',												'text|Date|date|N|10|255',                                'code|<br />',                                'code|<div style="width:300px;"><b>Shipping Cost Notes:</b><br />Shipping costs need to be added as inventory line items which are fulfilled. If an order has multiple shipments - add 1 shipping line item for each shipment and associate the correct cost.</div>',                            "code|</td><td valign='top'>",                'text|Reference Document URL|ref_document_url|N|60|255',                       'text|Reference Document Number|ref_document_number|N|60|1024',                'textarea|Notes|notes|N|40|1',            "code|</td></tr></table></div>",            "code|<br /><br />",									"code|<div class='form_section_wrapper'>",                "code|<div style='font-size:12px; font-weight:bold; color:red;'>The items below were ordered on this Sales Order.<br />If an item to be sent is not in this list - edit the SO first to add it and then mark as fulfilled.</div>",                "code|<div id='autotable_table_display'></div>",                "submit|{$btn_submit}",                "code|</div>",            "code|<br /><br />",						            'code|<div style="display:none;">',                'textarea|Temp 3|autotable_holder|N|60|4',            'code|</div>',                        #'checkbox|Override Inventory Count|temp_override_inventory_count||1|0',        );        if ($this->Action == 'ADD') {            #$base_array[] = "submit|Add Record|$this->Add_Submit_Name";            $base_array[] = 'endform';            $this->Form_Data_Array_Add = $base_array;        } else {            //$base_array[] = 'checkbox|Active|active||1|0';            #$base_array[] = "submit|Update Record|$this->Edit_Submit_Name";            $base_array[] = 'endform';            $this->Form_Data_Array_Edit = $base_array;        }    }        public function PostProcessFormValues($FormArray)     {        /* ======================== PSEUDOCODE ========================                Check each inventory item to make sure they still exist (superfulous but do it)        Verify the PO number is unique        For each line set the sales line TOTAL (derived from qty and each price)                Start Transaction        Create the sales Order entry        Create each sales Order LINE entry        End Transaction                Clear out the entire array        Put a success message on the screen        Re-direct to the entry page                Note: This bypasses normal action of BaseClass doing the entry into database - but this is needed        because its a multi-table entry                =============================================================== */                $debug                  		= false;                // (false) true = dispaly array lines        $delimiter              		= '|';                  // delimiter in the textbox array        $table_holder           		= 'autotable_holder';   // ID of textbox holding the array        $lines_array            		= array();              // will hold lines        $passed                 		= true;                 // holds check-passed status        $qty_total_for_shipping		 	= 0;                    // will hold total number of items received - for calculating ship cost        $override_count         		= $FormArray['temp_override_inventory_count'];        $inventory_sales_orders_id  	= $FormArray['inventory_sales_orders_id'];                if ($debug) {            echo ArrayToStr($FormArray);            echo ArrayToStr($_POST);            echo ArrayToStr($_GET);        }                        // ----- check that PO exists and is still active -----        $so_status = $this->StatusSalesOrder(0, $inventory_sales_orders_id);        $so_exists = $this->StatusSalesOrderExist(0, $inventory_sales_orders_id);                if ($so_status == 'closed') {            $this->Error .= "<br />ERROR :: Sales Order has been fulfilled in full already.";            $passed = false;        }                if ($so_status == 'canceled') {            $this->Error .= "<br />ERROR :: Sales Order has been cancelled.";            $passed = false;        }                if (!$so_exists) {            $this->Error .= "<br />ERROR :: Sales Order not found.";            $passed = false;        }                                // ----- get the main table holder value to process        $table = $FormArray[$table_holder];        if ($debug) { echo "table ===> {$table}"; }                if ($table) {            $lines = explode("\n", $table);            $header_row = true;            if ($debug) { echo ArrayToStr($lines); }                        $count = 0;            foreach ($lines as $key => $line) {                                if ($line) {                                        if ($debug) { echo ArrayToStr($line); }                                        if ($header_row == false) {                                                $count++;                                           // do here so we get the correct row we're on                        $parts = explode($delimiter, $line);                        if ($debug) { echo ArrayToStr($parts); }                                                // ----- Get the information from the table row -----                        $inventory_sales_order_lines_id     = trim($parts[0]);                        $barcode                            = trim($parts[1]);                        $qty_ordered                        = trim($parts[4]);                        $qty_previous                       = trim($parts[7]);                        $qty_remaining                      = trim($parts[8]);                                                // ----- Get the qty and prices from the $_POST variables -----                        $qty_sent                           = $this->CleanNumber(Post("qty_{$count}"));                        $price_total                        = $this->CleanMoney(Post("price_{$count}"));                                                // ----- Get the general so information -----                        $so_number                          = $FormArray['so_number'];                        $date                               = $FormArray['date'];                        $notes                              = $FormArray['notes'];                        $ref_document_url                   = $FormArray['ref_document_url'];                        $ref_document_number                = $FormArray['ref_document_number'];                                                // ----- Misc other calculations needed -----                        $qty_total_for_shipping += $qty_sent;    // updated total number of items received (for shipping)                                                // ----- check that inventory item still exists -----                        $exist = $this->StatusInventoryItemExist($barcode);                                                if (!$exist) {                            $this->Error .= "<br />ERROR :: Inventory item does not exist. Barcode: {$barcode}";                            $passed = false;                        }                                                // ----- check that you're not sending more that ordered -----                        if ($qty_sent > ($qty_ordered + $qty_previous)) {                            $this->Error .= "<br />ERROR :: You cannot send more inventory than the quantity ordered. Barcode: {$barcode}";                            $passed = false;                        }                                                if (($qty_sent > 0) && ($price_total >= 0)) {                            $lines_array[] = array(                                'inventory_sales_order_lines_id'    => $inventory_sales_order_lines_id,                                'inventory_sales_orders_id'      	=> $inventory_sales_orders_id,                                'barcode'                           => $barcode,                                'date'                              => $date,                                'qty_sent'                          => $qty_sent,                                'price_total'                       => $price_total,                                'ref_document_url'                  => $ref_document_url,                                'ref_document_number'               => $ref_document_number,                                'notes'                             => $notes,                            );                        }                    } else {                        // have to set header to false here or it could trigger on blank first line                        $header_row     = false;                    }                } //end blank line check                            }        } //end empty table check                                //exit();        //$this->Show_Query = true;                // ----- verify if any good lines made it to processing - we don't want to allow a 0-line PO        if (!$lines_array) {            $this->Error .= "NO INVENTORY HAS BEEN SENT";            $passed = false;        }                #$this->EchoVar('passed', $passed);        #$this->EchoVar('lines_array', $lines_array);                // ----- if all checks have passed and this isn't a blank invoice after processing        if ($passed && $lines_array) {            # ===== START TRANSACTION ============================================================            $this->SQL->StartTransaction();                                    foreach ($lines_array as $line) {                            # ----- Create the sales Order sent LINES entry -----                $db_record = array(                    'inventory_sales_order_lines_id'    => $line['inventory_sales_order_lines_id'],                    'inventory_sales_orders_id'      	=> $line['inventory_sales_orders_id'],                    'barcode'                           => $line['barcode'],                    'date'                              => $line['date'],                    'quantity'                          => $line['qty_sent'],                    'price_total'                       => $line['price_total'],                    'ref_document_url'                  => $line['ref_document_url'],                    'ref_document_number'               => $line['ref_document_number'],                    'notes'                             => $line['notes'],                );                $result     = $this->AddRecordLoc('inventory_sales_order_sent', $db_record);                $passed     = (!$result) ? false : $passed;                $last_id    = $this->SQL->Last_Insert_Id;                $this->EchoQuery();                                                                # ----- Subtract the inventory from the inventory counts -----                $db_record = array(                    'barcode'                           => $line['barcode'],                    'qty_out'                           => $line['qty_sent'],                    'ref_sales_order_sent_id'           => $last_id,                    'notes'                             => '',                    'date'                              => $line['date'],                );				$result     = $this->AddRecordLoc('inventory_counts', $db_record);                $passed     = (!$result) ? false : $passed;                $this->EchoQuery();            }                                    # ===== COMMIT TRANSACTION ============================================================            if ($passed) {                $this->SQL->TransactionCommit();                				// ----- UNSET FORM VALUES				unset($FormArray);			                // ----- Do A force redirect to bypass BaseClass further procesing this                $link = $this->getPageURL();                $_SESSION['alert_message'] = "RECORD ADDED SUCESSFULLY";                header("Location: {$link}");            }        }                        // ----- return form array to process any legitimate errors        if (!$passed) {            echo $this->Error;            return $FormArray;        }    }            public function JavascriptSendRemainingInventory()    {        AddScript("                function sendRemainingInventory(price, quantity, count) {                    var newPrice = price * quantity;                    $('#price_' + count).val(newPrice);                    $('#qty_' + count).val(quantity);                }            ");    }            }  // -------------- END CLASS --------------