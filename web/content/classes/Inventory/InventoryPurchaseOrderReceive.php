<?php/*NOTE :: Need to distribute shipping cost to all items*/class Inventory_InventoryPurchaseOrderReceive extends Inventory_InventoryBase{    public $Show_Query      = false;    public $Header_Row      = "ID|Barcode|Description|Manufacturer<br />SKU|APDM<br />SKU|Status|Price Each|Price Total|QTY<br />Ordered|QTY<br />Previously<br />Received|QTY<br />Remaining|QTY<br />Received|Price<br />Each|Price<br />Total|Receive Remaining";    public $Record_ID       = 0;    public $PO_Number       = 0;            public function  __construct()    {        parent::__construct();                $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2012-11-08',            'Updated By'    => 'Richard Witherspoon',            'Updated Date'  => '2013-01-03',            'Filename'      => 'Inventory_InventoryPurchaseOrderReceive',            'Version'       => '1.4',            'Description'   => 'USED FOR RECEIVEING INVENTORY ITEMS FROM A PURCHASE ORDER',            'Update Log'    => array(                '2012-11-08_001'    => "Class created",                '2012-11-30_001'    => "Fixed issues with receiving inventory",                '2013-01-02_001'    => "Modified query to only get and show active order lines (previously showed inactive lines). Added CleanStringForJqueryHTML() calls to fix jQuery errors.",                '2013-01-03_001'    => "Added code for entering price each when receiving inventory",            ),        );                $this->SetParameters(func_get_args());        $this->Record_ID         = $this->GetParameter(0);        $this->Add_Record_Link  = HexDecodeString($this->GetParameter(1));        #$this->Retailer_Code    = $this->GetParameter(2);                $this->Table            = 'inventory_purchase_orders';        $this->Index_Name       = 'inventory_purchase_orders_id';                $this->Default_Where    = '';               // additional search conditions        $this->Default_Sort     = 'po_number';      // field for default table sort                $this->Add_Submit_Name  = "{$this->Table}_SUBMIT_ADD";        $this->Edit_Submit_Name = "{$this->Table}_SUBMIT_EDIT";        $this->Flash_Field      = $this->Index_Name;                $this->Field_Titles = array(            "{$this->Table}.inventory_purchase_orders_id"           => 'Inventory Purchase Orders Id',                        "{$this->Table}.po_number"                              => 'PO Number',            "{$this->Table}.ref_document_url"                       => 'Ref URL',            "{$this->Table}.ref_document_number"                    => 'Ref Number',            "{$this->Table}.date"                                   => 'Date',            "{$this->Table}.cost_shipping"                          => 'Cost (shipping)',            "{$this->Table}.cost_other"                             => 'Cost (other)',            "{$this->Table}.status"                                 => 'Status',                        "{$this->Table}.notes"                                  => 'Notes',            "{$this->Table}.active"                                 => 'Active',            "{$this->Table}.updated"                                => 'Updated',            "{$this->Table}.created"                                => 'Created',                        "(SELECT SUM(`price_total`) AS EST_LINE_ITEM_COST FROM `inventory_purchase_order_lines` WHERE `inventory_purchase_order_lines`.`po_number` = `inventory_purchase_orders`.`po_number` AND `inventory_purchase_order_lines`.active=1) AS EST_LINE_ITEM_COST" => 'Estimated Line Item Cost',                    );                        $this->Join_Array = Array(            'inventory_counts'  => "LEFT OUTER JOIN `inventory_purchase_order_lines` ON `inventory_purchase_order_lines`.`po_number` = `inventory_purchase_orders`.`po_number`",        );        $this->Default_Fields   = 'po_number, date, status, EST_LINE_ITEM_COST, cost_shipping, cost_other';        $this->Unique_Fields    = 'po_number';                        $this->Add_Link         = '';       // don't allow adding a record                $this->Default_Values   = array(            'status'    => 'open',            'date'      => date('Y-m-d'),        );                $this->Close_On_Success = true;            } // -------------- END __construct --------------        public function Execute()    {                // Call the AddRecord to get a form onto the screen        // Convert the Record_Id into the barcode        // Pull the entire order info from the barcode        // Convert the order to a table for display                        $this->JavascriptReceiveRemainingInventory();                AddStylesheet("/css/inventory.css??20121108-1");        $this->AddRecord();        $record     = $this->GetRecordInfoFromBarcode($this->Record_ID);        $content    = $this->ConvertRecordInfoToArray($record);        $this->ScriptJSONTableGeneric();                                // Add in the generic code held in the BaseClass        $this->MoveArrayIntoForm($content);                // ----- get Purchase Order details        $po_record = $this->GetPurchaseOrderInformation($this->PO_Number);        if (is_array($po_record)) {            $cost_shipping      = money_format("%n", $po_record['cost_shipping']);            $cost_other         = money_format("%n", $po_record['cost_other']);            $notes              = $this->CleanStringForJqueryHTML($po_record['notes']);            $po_number          = $this->CleanStringForJqueryHTML($po_record['po_number']);            $date               = $this->CleanStringForJqueryHTML($po_record['date']);            $vendor             = $this->CleanStringForJqueryHTML($po_record['vendor']);                        AddScriptOnReady("            var varTemp = \"$notes\";                $('#temp_po_number').html('{$po_number}');                $('#temp_date').html('{$date}');                $('#temp_vendor').html('{$vendor}');                $('#temp_cost_shipping').html('{$cost_shipping}');                $('#temp_cost_other').html('{$cost_other}');                //$('#temp_notes').html('{$notes}');                $('#temp_notes').html(varTemp);                            ");            //$('#temp_notes').html('{$po_record['notes']}');        }        AddScriptOnReady("$('#FORM_po_number').val('{$this->PO_Number}');");        AddScriptOnReady("$('#temp_ponumber').html('{$this->PO_Number}');");                        // ----- add the jQuery UI datepicker functionality        AddScriptOnReady('$("#FORM_date").datepicker({dateFormat: "yy-mm-dd"});');                echo "<br /><br />";        echo "<div style='color:red; font-size:12px; font-weight:bold;'>The table below shows inventory that has already been received.<br />If you delete any of these lines the current window will refresh and you will lose any details on this screen.</div>";        $OBJ_RCVD = new Inventory_InventoryPurchaseOrderReceived($this->PO_Number);        $OBJ_RCVD->ScriptUnreceiveInventory();        echo $OBJ_RCVD->ListTable();                        /*        $action = Get('action');        switch ($action) {            case 'add':                #$this->Default_Values['barcode'] = $this->Barcode;                $this->AddRecord();            break;                        case 'list':            default:                #$this->ListTable();            break;        }        */    }            public function GetPurchaseOrderInformation($PO_NUMBER)    {        $output = 0;        if ($PO_NUMBER) {            // ===== Get the PO details from the passed in Record_Id            $record = $this->SQL->GetRecord(array(                'table' => 'inventory_purchase_orders',                'keys'  => '*',                'where' => "`po_number`='{$PO_NUMBER}' AND active=1",            ));            $this->EchoQuery();            $output = $record;        }                return $output;    }        public function ExecuteAjax()    {        $QDATA = GetEncryptQuery('eq');        $action = Get('action');                //$_GET['show'] = true;                if (Get('show')) {            echo "<br />QDATA = " . ArrayToStr($QDATA);            echo "<br />action = $action";        }                $return = 0;                switch ($action) {                        case 'autocomplete_inventory_lookup':                                // LOOK UP ALL ACTIVE INVENTORY ITEMS                                // query database for records                $query = Get('term');                $records = $this->SQL->GetArrayAll(array(                    'table' => ' inventory_products',                    'keys'  => 'description, retailer_code, barcode',                    'where' => "(description LIKE '%{$query}%' OR barcode LIKE '%{$query}%' OR retailer_code LIKE '%{$query}%') AND active=1",                ));                                // for records into array format for JSON                $arr = array();                foreach ($records as $record) {                    $arr[] = array(                        'label'             => "{$record['barcode']} - {$record['retailer_code']}",                        'description'       => $record['description'],                        'sku'               => $record['retailer_code'],                        'barcode'           => $record['barcode'],                    );                }                                // convert to JSON format                echo json_encode($arr);     // echo out in JSON form                 $return = '';               // clear return value or it will output and screw up return            break;        }                echo $return;    }        public function JavascriptReceiveRemainingInventory()    {        # FUNCTION :: Fill in the form to receive all remaining inventory quantity and price        AddScript("                function receiveRemainingInventory(price, quantity, count) {                    var newPrice = price * quantity;                    $('#price_each_' + count).val(price);                    $('#price_total_' + count).val(newPrice);                    $('#qty_' + count).val(quantity);                }            ");    }        public function GetRecordInfoFromBarcode($RID)    {        $temp_array = array();                if ($RID) {                    // ===== Get the PO details from the passed in Record_Id            $record_po = $this->SQL->GetRecord(array(                'table' => 'inventory_purchase_orders',                'keys'  => '*',                'where' => "`inventory_purchase_orders_id`='$RID' AND active=1",            ));            $this->EchoQuery();                                    // ===== get all the lines from the PO along with inventory item details            $this->PO_Number = $record_po['po_number'];            if ($this->PO_Number) {                $record_lines = $this->SQL->GetArrayAll(array(                    'table' => 'inventory_purchase_order_lines',                    'keys'  => 'inventory_purchase_order_lines.*, inventory_products.description, inventory_products.manufacturer_code, inventory_products.retailer_code,                               (SELECT SUM(`quantity`) FROM `inventory_purchase_order_received` WHERE `inventory_purchase_order_received`.`inventory_purchase_order_lines_id` = `inventory_purchase_order_lines`.`inventory_purchase_order_lines_id` AND `inventory_purchase_order_received`.active=1) AS QTY_PREV_RCVD',                    'where' => "`inventory_purchase_order_lines`.`po_number`='{$this->PO_Number}' AND inventory_purchase_order_lines.active=1",                    'joins' => "LEFT JOIN `inventory_products` ON `inventory_products`.`barcode` = `inventory_purchase_order_lines`.`barcode`",                ));                $this->EchoQuery();                                // ===== convert lines to a workable array                // ===== the order put into this array is how it will be displayed as a table                foreach ($record_lines as $line) {                                        $qty_remain                         = $line['quantity'] - $line['QTY_PREV_RCVD'];                    $inventory_purchase_order_lines_id  = $line['inventory_purchase_order_lines_id'];                    $line_status                        = $this->StatusPurchaseOrderLine($inventory_purchase_order_lines_id);                    $price_each                         = ($line['price_total'] / $line['quantity']);                                        $temp_array[] = array(                        'lines_id'          => $line['inventory_purchase_order_lines_id'],                        'barcode'           => $line['barcode'],                        'description'       => $line['description'],                        'manufacturer_code' => $line['manufacturer_code'],                        'retailer_code'     => $line['retailer_code'],                        'status'            => $line_status,                                                'price_each'        => money_format('%.4n', $price_each),                        'price_total'       => money_format('%n', $line['price_total']),                                                'quantity'          => number_format($line['quantity']),                        'quantity_rcvd'     => number_format($line['QTY_PREV_RCVD']),                        'quantity_remain'   => number_format($qty_remain),                    );                                        //echo ArrayToStr($line);                }            }        } // end RID check                return $temp_array;    }        public function ConvertRecordInfoToArray($ARR)    {        # FUNCTION :: Take an array of order lines and convert it to a delimited text        #             which will later be stored in a textarea and converted to a table                $output     = "";        $newline    = "\\n";        $delimiter  = "|";        $count      = 0;                // ----- loop through each record and form array        // ----- output will be based on the order the array values are structured        foreach ($ARR as $record) {                        //$this->EchoVar('record', $record);            $status = $record['status'];            foreach ($record as $k => $v) {                $output .= "{$v}{$delimiter}";            }                                    // ----- add in the action boxes            if (($status == 'open') || ($status == 'partial')) {                $count++;   // do count here so first row willhave an ID of 1                $qty            = "<input jqVar1='{$count}' id='qty_{$count}'           name='qty_{$count}'         class='calc_price_quantity' size='4' maxlength='255' value='' type='text' />";                $price_each     = "<input jqVar1='{$count}' id='price_each_{$count}'    name='price_each_{$count}'  class='calc_price_each'     size='4' maxlength='255' value='' type='text' />";                $price_total    = "<input jqVar1='{$count}' id='price_total_{$count}'   name='price_total_{$count}' class='calc_price_total'    size='4' maxlength='255' value='' type='text' />";                                $js_price       = ($this->CleanMoney($record['price_each']));                   // strip off dollar sign                $js_qty         = intval($this->CleanNumber($record['quantity_remain']));       // remove comans and convert to integer                $receive        = "<div class='button_receive' onclick='receiveRemainingInventory({$js_price}, {$js_qty}, {$count}); return false;'><center><a href='#' class='row_checkmark' title='Send Remaining Inventory'></a></center></div>";                            } else {                // add the actions form elements to the table row                $qty            = 'n/a';                $price_each     = 'n/a';                $price_total    = 'n/a';                $receive        = 'n/a';            }                        // add the actions form elements to the table row            $output .= $qty . $delimiter . $price_each . $delimiter . $price_total . $delimiter . $receive . $delimiter;                        $output = substr($output, 0, -1);           // trim off the trailing slash            $output .= $newline;                        // add line return        }                        // ----- put the header at the top of the table        $output         = $this->Header_Row . $newline . $output;                return $output;    }        public function MoveArrayIntoForm($CONTENT)    {        # FUNCTION :: Get the delimited text and convert to a table using JavaScript                // ----- feed this array into javascript to put in holder area        $script = "            var targetTextID        = 'FORM_autotable_holder';          // defines hidden textarea that will hold the text array            var targetTableDivID    = 'autotable_table_display';        // defines the div that wraps the created table                        var targetText          = $('#' + targetTextID);            // defines hidden textarea that will hold the text array            var targetTableDiv      = $('#' + targetTableDivID);        // defines the div that wraps the created table                        targetText.val(\"{$CONTENT}\");                             // put the array into textbox            createTableFromTextbox(targetText, targetTableDiv);         // call function to create table from array        ";        AddScriptOnReady($script);    }                    public function SetFormArrays()    {        $this->JavascriptDisplaySessionMessage();   // Display alert messages        $this->ScriptCalculatePriceArray();              // Javascript for autocompleting price textboxes                $this->JavascriptDisableFunctionality(array('FORM_temp_po_number', 'FORM_temp_date', 'FORM_temp_vendor', 'FORM_temp_cost_shipping', 'FORM_temp_cost_other', 'FORM_temp_notes'));        $this->JavascriptInputNoBorder(array('FORM_temp_po_number', 'FORM_temp_date', 'FORM_temp_vendor', 'FORM_temp_cost_shipping', 'FORM_temp_cost_other', 'FORM_temp_notes'));                $btn_add        = MakeButton('positive', 'ADD', '', '', 'btn_add', "addDataToTable()", 'button', 'btn_add');        $btn_clear      = MakeButton('negative', 'CLEAR', '', '', 'btn_clear', "clearDataTextboxes()", 'button', 'btn_clear');        $R              = 'N';                                $base_array = array(            "form|$this->Action_Link|post|db_edit_form",                        "code|<div style='font-size:16px; color:blue; padding:5px; background-color:#ccc;'>PO Number: <span id='temp_ponumber'></span></div><br />",                        "code|<div class='shadow form_section_wrapper' style='font-size:'>",                            'code|<div class="form_section_header">PURCHASE ORDER INFORMATION</div>',                                "code|                <br class='formtitlebreak'><div class='formtitle'>PO Number:</div>      <div class='forminfo'><span id='temp_po_number'></span></div>                <br class='formtitlebreak'><div class='formtitle'>Date:</div>           <div class='forminfo'><span id='temp_date'></span></div>                <br class='formtitlebreak'><div class='formtitle'>Vendor:</div>         <div class='forminfo'><span id='temp_vendor'></span></div>                <br class='formtitlebreak'><div class='formtitle'>Shipping Cost:</div>  <div class='forminfo'><span id='temp_cost_shipping'></span></div>                <br class='formtitlebreak'><div class='formtitle'>Other Cost:</div>     <div class='forminfo'><span id='temp_cost_other'></span></div>                <br class='formtitlebreak'><div class='formtitle'>Notes:</div>          <div class='forminfo'><span id='temp_notes'></span></div>                                ",            'code|</div>',            'code|<br /><br />',                                                "code|<div style='border:1px dashed blue; padding:5px; background-color:#efefef;'><table><tr>",            "code|<td valign='top'>",                #'text|PO Number|po_number|Y|10|255',                'hidden|po_number',                'text|Date|date|N|10|255',                                'code|<br />',                                'info||Shipping and Other costs will be evenly distributed to all items accepted below.',                #'info||Shipping and Other costs will be distributed to all items accepted below.',                                'text|Shipping Cost|cost_shipping|N|10|255',                'text|Other Cost|cost_other|N|10|255',            "code|</td><td valign='top'>",                'text|Reference Document URL|ref_document_url|N|60|255',                       'text|Reference Document Number|ref_document_number|N|60|1024',                'textarea|Notes|notes|N|40|1',            "code|</td></tr></table></div>",                        "code|<div style='font-size:12px; font-weight:bold; color:red;'>The items below were ordered on this Purchase Order.<br />If an item received is not in this list - edit the PO first to add it and then receive into inventory.</div>",                        "code|<div id='autotable_table_display'></div>",                                    'code|<div style="display:none;">',                'textarea|Temp 3|autotable_holder|N|60|4',            'code|</div>',                    );        if ($this->Action == 'ADD') {            $base_array[] = "submit|Add Record|$this->Add_Submit_Name";            $base_array[] = 'endform';            $this->Form_Data_Array_Add = $base_array;        } else {            //$base_array[] = 'checkbox|Active|active||1|0';            $base_array[] = "submit|Update Record|$this->Edit_Submit_Name";            $base_array[] = 'endform';            $this->Form_Data_Array_Edit = $base_array;        }    }        public function PostProcessFormValues($FormArray)     {        /* ======================== PSEUDOCODE ========================                Check each inventory item to make sure they still exist (superfulous but do it)        Verify the PO number is unique        For each line set the purchase line TOTAL (derived from qty and each price)                Start Transaction        Create the Purchase Order entry        Create each Purchase Order LINE entry        End Transaction                Clear out the entire array        Put a success message on the screen        Re-direct to the entry page                Note: This bypasses normal action of BaseClass doing the entry into database - but this is needed        because its a multi-table entry                =============================================================== */                $debug                  = false;                // (false) true = dispaly array lines        $delimiter              = '|';                  // delimiter in the textbox array        $table_holder           = 'autotable_holder';   // ID of textbox holding the array        $lines_array            = array();              // will hold lines        $passed                 = true;                 // holds check-passed status        $qty_total_for_shipping = 0;                    // will hold total number of items received - for calculating ship cost        $qty_total_for_other    = 0;                    // will hold total number of items received - for calculating other cost                if ($debug) {            echo ArrayToStr($FormArray);            echo ArrayToStr($_POST);            echo ArrayToStr($_GET);        }                        // ----- check that PO exists and is still active -----        $po_status = $this->StatusPurchaseOrder($FormArray['po_number']);        $po_exists = $this->StatusPurchaseOrderExist($FormArray['po_number']);                if ($po_status == 'closed') {            $this->Error .= "<br />ERROR :: Purchase Order has been received in full already. PO Number: {$FormArray['po_number']}";            $passed = false;        }                if ($po_status == 'canceled') {            $this->Error .= "<br />ERROR :: Purchase Order has been cancelled. PO Number: {$FormArray['po_number']}";            $passed = false;        }                if (!$po_exists) {            $this->Error .= "<br />ERROR :: Purchase Order not found. PO Number: {$FormArray['po_number']}";            $passed = false;        }                                // ----- get the main table holder value to process        $table = $FormArray[$table_holder];        if ($debug) { echo "table ===> {$table}"; }                if ($table) {            $lines = explode("\n", $table);            $header_row = true;            if ($debug) { echo ArrayToStr($lines); }                        $count = 0;            foreach ($lines as $key => $line) {                                if ($line) {                                        if ($debug) { echo ArrayToStr($line); }                                        if ($header_row == false) {                                                $count++;                                           // do here so we get the correct row were on                        $parts = explode($delimiter, $line);                        if ($debug) { echo ArrayToStr($parts); }                                                // ----- Get the information from the table row -----                        $inventory_purchase_order_lines_id  = trim($parts[0]);                        $barcode                            = trim($parts[1]);                        $qty_ordered                        = $this->CleanNumber(trim($parts[8]));                        $qty_previous                       = $this->CleanNumber(trim($parts[9]));                        $qty_remaining                      = $this->CleanNumber(trim($parts[10]));                                                // ----- Get the qty and prices from the $_POST variables -----                        $qty_received                       = $this->CleanNumber(Post("qty_{$count}"));                        $price_total                        = $this->CleanMoney(Post("price_total_{$count}"));                        $price_each                         = ($price_total / $qty_received);                                                                        $price_shipping                     = $FormArray['cost_shipping'];                        $price_other                        = $FormArray['cost_other'];                                                // ----- Get the general po information -----                        $po_number                          = $FormArray['po_number'];                        $date                               = $FormArray['date'];                        $notes                              = $FormArray['notes'];                        $ref_document_url                   = $FormArray['ref_document_url'];                        $ref_document_number                = $FormArray['ref_document_number'];                                                // ----- Misc other calculations needed -----                        $po_line_status = ($qty_ordered == ($qty_received + $qty_previous)) ? 'closed' : 'partial';                        $this->PO_Number = $po_number;                        $qty_total_for_shipping += $qty_received;    // updated total number of items received (for shipping cost)                        $qty_total_for_other += $qty_received;      // updated total number of items received (for other cost)                                                //echo "<br />qty_ordered  ---> $qty_ordered";                        //echo "<br />qty_total_for_shipping  ---> $qty_total_for_shipping";                                                /*                        echo "<br />qty_received  ---> $qty_received";                        echo "<br />qty_ordered  ---> $qty_ordered";                        echo "<br />qty_previous  ---> $qty_previous";                        echo "<br />po_line_status  ---> $po_line_status";                        echo "<br /><br />";                        */                                                // ----- check that inventory item still exists -----                        $exist = $this->StatusInventoryItemExist($barcode);                        if (!$exist) {                            $this->Error .= "<br />ERROR :: Inventory item does not exist. Barcode: {$barcode}";                            $passed = false;                        }                                                                        #$this->EchoVar('qty_ordered', $qty_ordered);                        #$this->EchoVar('qty_previous', $qty_previous);                        #$this->EchoVar('qty_received', $qty_received);                                                // ----- check that you're not receiveing more that ordered -----                        if ($qty_received > ($qty_ordered + $qty_previous)) {                            $this->Error .= "<br />ERROR :: You cannot receive more inventory than the quantity ordered. Barcode: {$barcode}";                            $passed = false;                        }                                                                                                #echo "<br />qty_received --> $qty_received";                        #echo "<br />price_total --> $price_total";                                                if (($qty_received > 0) && ($price_total > 0)) {                            $lines_array[] = array(                                'inventory_purchase_order_lines_id' => $inventory_purchase_order_lines_id,                                'po_number'                         => $po_number,                                'date'                              => $date,                                'qty_received'                      => $qty_received,                                'price_each'                        => $price_each,                                'price_total'                       => $price_total,                                'price_shipping'                    => 0, //$price_shipping,                                'ref_document_url'                  => $ref_document_url,                                'ref_document_number'               => $ref_document_number,                                'notes'                             => $notes,                                                                'po_line_status'                    => $po_line_status,                                'barcode'                           => $barcode,                                #'qty_ordered'                       => $qty_ordered,                                #'qty_previous'                      => $qty_previous,                                #'qty_remaining'                     => $qty_remaining,                            );                        }                    } else {                        // have to set header to false here or it could trigger on blank first line                        $header_row     = false;                    }                } //end blank line check                            }        } //end empty table check                                // ===== Determine the cost of shipping for each item        $price_shipping                     = $FormArray['cost_shipping'];        $price_other                        = $FormArray['cost_other'];        $price_shipping_each                = (($price_shipping + $price_other) / $qty_total_for_shipping);        //$price_other_each                if (false) {            $this->EchoVar('price_shipping', $price_shipping);            $this->EchoVar('price_other', $price_other);            $this->EchoVar('qty_total_for_shipping', $qty_total_for_shipping);            $this->EchoVar('qty_total_for_other', $qty_total_for_other);            $this->EchoVar('price_shipping_each', $price_shipping_each);            exit();        }                                        //exit();        $this->Show_Query = true;                // ----- verify if any good lines made it to processing - we don't want to allow a 0-line PO        if (!$lines_array) {            $this->Error .= "NO INVENTORY HAS BEEN RECEIVED";            $passed = false;        }                        // ----- if all checks have passed and this isn't a blank invoice after processing        if ($passed && $lines_array) {            # ===== START TRANSACTION ============================================================            $this->SQL->StartTransaction();                                    foreach ($lines_array as $line) {                            # ----- Create the Purchase Order received LINES entry -----                $price_shipping_total = ($line['qty_received'] * $price_shipping_each);   // calculate the shipping amount for this line                                $db_record = array(                    'inventory_purchase_order_lines_id' => $line['inventory_purchase_order_lines_id'],                    'barcode'                           => $line['barcode'],                    'po_number'                         => $line['po_number'],                    'date'                              => $line['date'],                    'quantity'                          => $line['qty_received'],                    'price_each'                        => $line['price_each'],                    'price_total'                       => $line['price_total'],                    'price_shipping_each'               => $price_shipping_each,                    'price_shipping'                    => $price_shipping_total,                    'ref_document_url'                  => $line['ref_document_url'],                    'ref_document_number'               => $line['ref_document_number'],                    'notes'                             => $line['notes'],                );                $result = $this->AddRecordLoc('inventory_purchase_order_received', $db_record);                $passed = (!$result) ? false : $passed;                $last_id = $this->SQL->Last_Insert_Id;                $this->EchoQuery();                                                                # ----- Add the inventory to the inventory counts -----                $db_record = array(                    'barcode'                           => $line['barcode'],                    'qty_in'                            => $line['qty_received'],                    'ref_purchase_orders_received_id'   => $last_id,                    'notes'                             => '',                    'date'                              => $line['date'],                );                $result = $this->AddRecordLoc('inventory_counts', $db_record);                $passed = (!$result) ? false : $passed;                $this->EchoQuery();                                                /*                # ----- Update the purchase order line with status -----                $db_record = array(                    'status'                            => $line['po_line_status'],                );                $where = "`inventory_purchase_order_lines_id`='{$line['inventory_purchase_order_lines_id']}'";                $this->UpdateRecordLoc('inventory_purchase_order_lines', $db_record, $where);                $this->EchoQuery();                */            }                                    /*            # ===== Possibly update the purchase order itself ============================================================            // determine if there are any outstanding items for this PO            $status = $this->StatusPurchaseOrder($this->PO_Number);                        # ----- Update the purchase order line with status -----            $db_record = array(                'status'                            => $status,            );            $where = "`po_number`='{$this->PO_Number}'";            $this->UpdateRecordLoc('inventory_purchase_orders', $db_record, $where);            $this->EchoQuery();            */                                                # ===== COMMIT TRANSACTION ============================================================            if ($passed) {                $this->SQL->TransactionCommit();                                // ----- Do A force redirect to bypass BaseClass further procesing this                $link = $this->getPageURL();                $_SESSION['alert_message'] = "RECORD ADDED SUCESSFULLY";                header("Location: {$link}");            }                                            }                        // ----- return form array to process any legitimate errors        if (!$passed) {            return $FormArray;        }    }                }  // -------------- END CLASS --------------