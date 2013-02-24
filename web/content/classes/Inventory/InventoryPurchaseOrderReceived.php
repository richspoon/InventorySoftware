<?phpclass Inventory_InventoryPurchaseOrderReceived extends Inventory_InventoryBase{    public $Show_Query      = false;    public $Record_ID       = 0;    public $PO_Number       = 0;            public function  __construct()    {        parent::__construct();                $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2012-11-12',            'Updated By'    => '',            'Updated Date'  => '',            'Filename'      => 'Inventory_InventoryPurchaseOrderReceived',            'Version'       => '1.0',            'Description'   => 'Show what lines have alrady been received on a PO',        );                $this->SetParameters(func_get_args());        $this->PO_Number         = $this->GetParameter(0);                        $this->Table                = 'inventory_purchase_order_received';        $this->Index_Name           = 'inventory_purchase_order_received_id';                $this->Default_Where        = "`po_number`='{$this->PO_Number}' AND inventory_purchase_order_received.active=1";        $this->Default_Sort         = 'date';      // field for default table sort                $this->Add_Submit_Name      = "{$this->Table}_SUBMIT_ADD";        $this->Edit_Submit_Name     = "{$this->Table}_SUBMIT_EDIT";        $this->Flash_Field          = $this->Index_Name;                $this->Field_Titles = array(            "{$this->Table}.inventory_purchase_order_received_id"   => 'Inventory Purchase Order Received Id',            "{$this->Table}.inventory_purchase_order_lines_id"      => 'PO Line ID',            "{$this->Table}.po_number"                              => 'PO Number',            "{$this->Table}.date"                                   => 'Date',            "{$this->Table}.quantity"                               => 'Quantity',            "{$this->Table}.price_shipping"                         => 'Shipping Price',            "{$this->Table}.price_total"                            => 'Total Price',            "{$this->Table}.barcode"                                => 'Barcode',            "inventory_products.description"                        => 'Description',            "inventory_products.manufacturer_code"                  => 'Manufacturer SKU',            "inventory_products.retailer_code"                      => 'APDM SKU',            "{$this->Table}.notes"                                  => 'Notes',            "{$this->Table}.active"                                 => 'Active',            "{$this->Table}.updated"                                => 'Updated',            "{$this->Table}.created"                                => 'Created',        );                        $this->Join_Array = Array(            'inventory_products'  => "LEFT JOIN `inventory_products` ON `inventory_products`.`barcode` = `inventory_purchase_order_received`.`barcode`",        );                        $this->Default_Fields   = 'date, barcode, description, manufacturer_code, retailer_code, quantity, price_total, price_shipping, po_number';        $this->Unique_Fields    = '';        $this->Default_Values   = array();        $this->Close_On_Success = true;                $this->Edit_Links_Count     = '1';          // number of links at end of table        $this->Add_Link             = '';           // don't allow adding a record        $this->Show_Export          = false;        // false = don't allow export of this table        $this->Default_List_Size    = 1000;         // how many lines to allow in table before pagination        $this->Use_Selection_Tab    = false;        // false = hide the search tab on the table                    } // -------------- END __construct --------------        public function Execute()    {                // Call the AddRecord to get a form onto the screen        // Convert the Record_Id into the barcode        // Pull the entire order info from the barcode        // Convert the order to a table for display                        AddStylesheet("/css/inventory.css??20121108-1");        $this->AddRecord();        $record     = $this->GetRecordInfoFromBarcode($this->Record_ID);        $content    = $this->ConvertRecordInfoToArray($record);        $this->ScriptJSONTableGeneric();                                // Add in the generic code held in the BaseClass        $this->MoveArrayIntoForm($content);                AddScriptOnReady("$('#FORM_po_number').val('{$this->PO_Number}');");                // ----- add the jQuery UI datepicker functionality        AddScriptOnReady('$("#FORM_date").datepicker({dateFormat: "yy-mm-dd"});');            }        public function ExecuteAjax()    {        //$QDATA = GetEncryptQuery('eq');        $action = Get('action');                //$_GET['show'] = true;                if (Get('show')) {            echo "<br />QDATA = " . ArrayToStr($QDATA);            echo "<br />action = $action";        }                $return = 0;                        switch ($action) {                        case 'delete_received_inventory':                                /* P-CODE =================================================                Start Transaction                Deactivate the 'inventory_purchase_order_received' line                Remove the inventory from 'inventory_counts'                Update 'inventory_purchase_order_lines' to 'partial' or 'open'                Update 'inventory_purchase_orders' to 'partial' or 'open'                End Transaction                ================================================= */                                $this->Show_Query = false;                                                $inventory_purchase_order_received_id = Get('id'); //0;                                if ($inventory_purchase_order_received_id != 0) {                                        # ===== START TRANSACTION ============================================================                    $this->SQL->StartTransaction();                                                            # ----- Get needed details -----                    $record = $this->SQL->GetRecord(array(                        'table' => 'inventory_purchase_order_lines',                        'keys'  => 'po_number, barcode, inventory_purchase_order_lines_id, quantity',                        'where' => "`inventory_purchase_order_lines_id`='{$inventory_purchase_order_received_id}'",                    ));                    if ($this->Show_Query) { echo "<br /><br />Query ---> " . $this->SQL->Db_Last_Query; }                                        $po_number                          = $record['po_number'];                    $barcode                            = $record['barcode'];                    $inventory_purchase_order_lines_id  = $record['inventory_purchase_order_lines_id'];                    $qty_ordered                        = $record['quantity'];                                                                                # ----- Deactivate the 'inventory_purchase_order_received' line -----                    $db_record = array(                        'active'    => 0,                    );                    $where = "`inventory_purchase_order_received_id`='{$inventory_purchase_order_received_id}'";                    $this->UpdateRecordLoc('inventory_purchase_order_received', $db_record, $where);                    if ($this->Show_Query) { echo "<br /><br />Query ---> " . $this->SQL->Db_Last_Query; }                                                                                # ----- Remove the inventory from 'inventory_counts' -----                    $db_record = array(                        'active'    => 0,                        'notes'     => 'Inventory previously received deleted by user',                    );                    $where = "`ref_purchase_orders_received_id`='{$inventory_purchase_order_received_id}'";                    $this->UpdateRecordLoc('inventory_counts', $db_record, $where);                    if ($this->Show_Query) { echo "<br /><br />Query ---> " . $this->SQL->Db_Last_Query; }                                                                                # ===== COMMIT TRANSACTION ============================================================                    $this->SQL->TransactionCommit();                                        $return = 1;                                } // end checking for 'inventory_purchase_order_received_id'            break;        }                echo $return;    }                function ScriptUnreceiveInventory()    {        $CLASS_EXECUTE_LINK     = '/office/AJAX/class_execute';        $eq                     = EncryptQuery("class=Inventory_InventoryPurchaseOrderReceived");        //$link                   = $CLASS_EXECUTE_LINK . '?eq=' . $eq . "&action=delete_received_inventory&id={$id}";                $script = <<<SCRIPT                    function tableUnreceiveInventoryClick(idx, value, eq)            {                var idbase = 'TABLE_ROW_ID' + idx + '_';                var rowNumber = $('#' + idbase + value + ' td:first-child').html().replace('.', '');                $('#' + idbase + value +' td').css('background-color','#ff7');                if (confirm('Are you sure you want to delete row (' + rowNumber + ')?')) {                    $.get('{$CLASS_EXECUTE_LINK}?eq={$eq}&action=delete_received_inventory&id=' + value, '', function(data){                        if (data == 1) {                            location.reload();  // reload the page as many items need to be updated                            //$('#' + idbase + value +' td').fadeOut();                        } else {                            alert('Error: Could not delete record! :: ' + data);                        }                    });                }                $('#' + idbase + value +' td').css('background-color','');                return false;            }                SCRIPT;AddScript($script);    }        public function SetFormArrays()    {        // not using function because we won't edit these lines    }        public function __PostProcessFormValues($FormArray)     {        /* ======================== PSEUDOCODE ========================                Check each inventory item to make sure they still exist (superfulous but do it)        Verify the PO number is unique        For each line set the purchase line TOTAL (derived from qty and each price)                Start Transaction        Create the Purchase Order entry        Create each Purchase Order LINE entry        End Transaction                Clear out the entire array        Put a success message on the screen        Re-direct to the entry page                Note: This bypasses normal action of BaseClass doing the entry into database - but this is needed        because its a multi-table entry                =============================================================== */                $debug                  = false;                // (false) true = dispaly array lines        $delimiter              = '|';                  // delimiter in the textbox array        $table_holder           = 'autotable_holder';   // ID of textbox holding the array        $lines_array            = array();              // will hold lines        $passed                 = true;                 // holds check-passed status        $qty_total_for_shipping = 0;                    // will hold total number of items received - for calculating ship cost                if ($debug) {            echo ArrayToStr($FormArray);            echo ArrayToStr($_POST);            echo ArrayToStr($_GET);        }                        // ----- check that PO exists and is still active -----        $record = $this->SQL->GetRecord(array(            'table' => 'inventory_purchase_orders',            'keys'  => 'status',            'where' => "po_number='{$FormArray['po_number']}' AND active=1",        ));                if ($record) {            if ($record['status'] == 'closed') {                $this->Error .= "<br />ERROR :: Purchase Order has been received in full already. PO Number: {$FormArray['po_number']}";                $passed = false;            }            if ($record['status'] == 'canceled') {                $this->Error .= "<br />ERROR :: Purchase Order has been cancelled. PO Number: {$FormArray['po_number']}";                $passed = false;            }        } else {            $this->Error .= "<br />ERROR :: Purchase Order not found. PO Number: {$FormArray['po_number']}";            $passed = false;        }                                // ----- get the main table holder value to process        $table = $FormArray[$table_holder];        if ($debug) { echo "table ===> {$table}"; }                if ($table) {            $lines = explode("\n", $table);            $header_row = true;            if ($debug) { echo ArrayToStr($lines); }                        $count = 0;            foreach ($lines as $key => $line) {                                if ($line) {                                        if ($debug) { echo ArrayToStr($line); }                                        if ($header_row == false) {                                                $count++;                                           // do here so we get the correct row were on                        $parts = explode($delimiter, $line);                        if ($debug) { echo ArrayToStr($parts); }                                                // ----- Get the information from the table row -----                        $inventory_purchase_order_lines_id  = trim($parts[0]);                        $barcode                            = trim($parts[1]);                        $qty_ordered                        = trim($parts[7]);                        $qty_previous                       = trim($parts[8]);                        $qty_remaining                      = trim($parts[9]);                                                // ----- Get the qty and prices from the $_POST variables -----                        $qty_received                       = Post("qty_{$count}");                        $price_total                        = Post("price_{$count}");                        $price_shipping                     = $FormArray['cost_shipping'];                        $price_other                        = $FormArray['cost_other'];                                                // ----- Get the general po information -----                        $po_number                          = $FormArray['po_number'];                        $date                               = $FormArray['date'];                        $notes                              = $FormArray['notes'];                        $ref_document_url                   = $FormArray['ref_document_url'];                        $ref_document_number                = $FormArray['ref_document_number'];                                                // ----- Misc other calculations needed -----                        $po_line_status = ($qty_ordered == ($qty_received + $qty_previous)) ? 'closed' : 'partial';                        $this->PO_Number = $po_number;                        $qty_total_for_shipping += $qty_received;    // updated total number of items received (for shipping)                                                //echo "<br />qty_ordered  ---> $qty_ordered";                        //echo "<br />qty_total_for_shipping  ---> $qty_total_for_shipping";                                                /*                        echo "<br />qty_received  ---> $qty_received";                        echo "<br />qty_ordered  ---> $qty_ordered";                        echo "<br />qty_previous  ---> $qty_previous";                        echo "<br />po_line_status  ---> $po_line_status";                        echo "<br /><br />";                        */                                                // ----- check that inventory item still exists -----                        $row = $this->SQL->GetRecord(array(                            'table' => 'inventory_products',                            'keys'  => 'barcode',                            'where' => "barcode = '{$barcode}' AND active=1",                        ));                                                // ----- check that you're not receiveing more that ordered -----                        if ($qty_received > ($qty_ordered + $qty_previous)) {                            $this->Error .= "<br />ERROR :: You cannot receive more inventory than the quantity ordered. Barcode: {$barcode}";                            $passed = false;                        }                                                                        if (empty($row)) {                            $this->Error .= "<br />ERROR :: Inventory item does not exist. Barcode: {$barcode}";                            $passed = false;                        }                        #echo "<br />qty_received --> $qty_received";                        #echo "<br />price_total --> $price_total";                                                if (($qty_received > 0) && ($price_total > 0)) {                            $lines_array[] = array(                                'inventory_purchase_order_lines_id' => $inventory_purchase_order_lines_id,                                'po_number'                         => $po_number,                                'date'                              => $date,                                'qty_received'                      => $qty_received,                                'price_total'                       => $price_total,                                'price_shipping'                    => 0, //$price_shipping,                                'ref_document_url'                  => $ref_document_url,                                'ref_document_number'               => $ref_document_number,                                'notes'                             => $notes,                                                                'po_line_status'                    => $po_line_status,                                'barcode'                           => $barcode,                                #'qty_ordered'                       => $qty_ordered,                                #'qty_previous'                      => $qty_previous,                                #'qty_remaining'                     => $qty_remaining,                            );                        }                    } else {                        // have to set header to false here or it could trigger on blank first line                        $header_row     = false;                    }                } //end blank line check                            }        } //end empty table check                                // ===== Determine the cost of shipping for each item        $price_shipping                     = $FormArray['cost_shipping'];        $price_other                        = $FormArray['cost_other'];        $price_shipping_each                = round((($price_shipping + $price_other) / $qty_total_for_shipping), 2);                //echo "<br />price_shipping  ---> $price_shipping";        //echo "<br />price_shipping  ---> $price_shipping";        //echo "<br />price_shipping  ---> $price_shipping";                //$this->Error .= "<br />PURPOSFUL HALTING ERROR";                                        //exit();        $this->Show_Query = true;                // ----- verify if any good lines made it to processing - we don't want to allow a 0-line PO        if (!$lines_array) {            $this->Error .= "NO INVENTORY HAS BEEN RECEIVED";            $passed = false;        }                        // ----- if all checks have passed and this isn't a blank invoice after processing        if ($passed && $lines_array) {            # ===== START TRANSACTION ============================================================            $this->SQL->StartTransaction();                                    foreach ($lines_array as $line) {                            # ----- Create the Purchase Order received LINES entry -----                $price_shipping = ($line['qty_received'] * $price_shipping_each);   // calculate the shipping amount for this line                                $db_record = array(                    'inventory_purchase_order_lines_id' => $line['inventory_purchase_order_lines_id'],                    'po_number'                         => $line['po_number'],                    'date'                              => $line['date'],                    'quantity'                          => $line['qty_received'],                    'price_total'                       => $line['price_total'],                    'price_shipping'                    => $price_shipping,                    'ref_document_url'                  => $line['ref_document_url'],                    'ref_document_number'               => $line['ref_document_number'],                    'notes'                             => $line['notes'],                );                $this->AddRecordLoc('inventory_purchase_order_received', $db_record);                $last_id = $this->SQL->Last_Insert_Id;                echo "<br />last_id ---> $last_id";                if ($this->Show_Query) { echo "<br /><br />Query ---> " . $this->SQL->Db_Last_Query; }                                                                # ----- Add the inventory to the inventory counts -----                $db_record = array(                    'barcode'                           => $line['barcode'],                    'qty_in'                            => $line['qty_received'],                    'ref_purchase_orders_received_id'   => $last_id,                    'notes'                             => '',                );                $this->AddRecordLoc('inventory_counts', $db_record);                if ($this->Show_Query) { echo "<br /><br />Query ---> " . $this->SQL->Db_Last_Query; }                                # ----- Update the purchase order line with status -----                $db_record = array(                    'status'                            => $line['po_line_status'],                );                $where = "`inventory_purchase_order_lines_id`='{$line['inventory_purchase_order_lines_id']}'";                $this->UpdateRecordLoc('inventory_purchase_order_lines', $db_record, $where);                if ($this->Show_Query) { echo "<br /><br />Query ---> " . $this->SQL->Db_Last_Query; }                            }                                                # ===== Possibly update the purchase order itself ============================================================            // determine if there are any outstanding items for this PO            // if so - mark PO as "partial"            // of no items - mark PO as "closed"            $records = $this->SQL->GetArrayAll(array(                'table' => 'inventory_purchase_order_lines',                'keys'  => 'inventory_purchase_order_lines.quantity,                           (SELECT SUM(`quantity`) FROM `inventory_purchase_order_received` WHERE `inventory_purchase_order_received`.`inventory_purchase_order_lines_id` = `inventory_purchase_order_lines`.`inventory_purchase_order_lines_id` AND `inventory_purchase_order_received`.active=1) AS QTY_PREV_RCVD',                'where' => "`inventory_purchase_order_lines`.`po_number`='{$this->PO_Number}' AND (inventory_purchase_order_lines.active=1 OR inventory_purchase_order_lines.active=0)",                'joins' => "LEFT OUTER JOIN `inventory_purchase_order_received` ON `inventory_purchase_order_received`.`inventory_purchase_order_lines_id` = `inventory_purchase_order_lines`.`inventory_purchase_order_lines_id`",            ));            if ($this->Show_Query) { echo "<br /><br />Query ---> " . $this->SQL->Db_Last_Query; }                        $po_closed = true;            $po_partial = false;            foreach ($records as $record) {                $po_closed      = (($record['quantity'] - $record['QTY_PREV_RCVD']) > 0) ? false : $po_closed;                $po_partial     = ($record['QTY_PREV_RCVD'] != 0) ? true : $po_partial;            }                        $status = ($po_partial) ? 'partial' : 'open';       // determine if PO is partial or open            $status = ($po_closed) ? 'closed' : $status;        // determine if PO is closed                        # ----- Update the purchase order line with status -----            $db_record = array(                'status'                            => $status,            );            $where = "`po_number`='{$this->PO_Number}'";            $this->UpdateRecordLoc('inventory_purchase_orders', $db_record, $where);            if ($this->Show_Query) { echo "<br /><br />Query ---> " . $this->SQL->Db_Last_Query; }                                                            # ===== COMMIT TRANSACTION ============================================================            $this->SQL->TransactionCommit();                                                // ----- UNSET FORM VALUES            unset($FormArray);                                    // ----- Do A force redirect to bypass BaseClass further procesing this            $link = $this->getPageURL();            $_SESSION['alert_message'] = "RECORD ADDED SUCESSFULLY";            header("Location: {$link}");        }                        // ----- return form array to process any legitimate errors        if (!$passed) {            return $FormArray;        }    }                public function GetTableHeading($colcount)    {        $export = ($this->Show_Export)? $this->GetExportBlock() : '';        $RESULT = '            <tr class="TABLE_TITLE">                <td colspan="'. $colcount. '">                ' . $export . '                    Previously Received Inventory                </td>            </tr>';        return $RESULT;    }        public function ProcessTableCell($field, &$value, &$td_options, $id='')    {        # ============ WHEN VIEWING A TABLE ============                parent::ProcessTableCell($field, $value, $td_options, $id);        switch ($field) {            default:                // ----- MODIFY THE OPTIONS IN THE MAIN TABLE DISPLAY -----                $CLASS_EXECUTE_LINK     = '/office/AJAX/class_execute';                $eq                     = EncryptQuery("class=Inventory_InventoryPurchaseOrderReceived;v1={$id}");                $link                   = $CLASS_EXECUTE_LINK . '?eq=' . $eq . "&action=delete_received_inventory&id={$id}";                $script                 = "top.parent.appformCreate('Window', '{$link}', 'apps'); return false;";                                $this->Edit_Links = qqn("                <td align=`center`><a href=`#` class=`row_delete`   title=`Delete` onclick=`tableUnreceiveInventoryClick('@IDX@','@VALUE@','@EQ@'); return false; `></a></td>                                        ");                    //<td align=`center`><a href=`#` class=`row_delete` title=`View Lines`  onclick=`{$script}; return false;`></a></td>                    //                                                                        break;                                    case "date":                $value = date('M d, Y', strtotime($value));            break;                        case "quantity":                $value = number_format($value);            break;                        case "price_total":            case "price_shipping":                $value = "\$ {$value}";            break;        }    }        }  // -------------- END CLASS --------------