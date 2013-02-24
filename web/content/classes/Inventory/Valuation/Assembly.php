<?phpclass Inventory_Valuation_Assembly extends Inventory_InventoryBase{    public $Show_Query                  = false;    public $Barcode                     = 0;    public $Date                        = 0;    public $Quantity                    = 0;    public $Record_Type                 = '';   // salesorder || adjustment || assembly        public $Inventory_Counts_ID         = array();    public $Inventory_Assembly_Build_ID = 0;            public $Inventory_All               = array();      // both INBOUND and OUTBOUND    public $Inventory_In                = array();      // only INBOUND    public $Inventory_Out               = array();      // only OUTBOUND    public $Inventory_In_Out_Matched    = array();      // matching up INBOUND with OUTBOUND    public $Inventory_In_Out_Date       = array();    public $Inventory_Available         = array();      // inventory AVAILABLE for record            // ----- OUTPUT VARIABLES -----    public $COGS                        = 0;    public $COGS_Array                  = array();            public function  __construct()    {        parent::__construct();                $this->Classname = get_class($this);        $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2013-01-13',            'Updated By'    => 'Richard Witherspoon',            'Updated Date'  => '2013-02-24',            'Filename'      => $this->Classname,            'Version'       => '2.0',            'Description'   => 'Calculate the COGS for an inventory assembly record.',            'Update Log'    => array(                '2013-01-11_1.0'    => "Module Created",                '2013-02-24_2.0'    => "Significant restructuring of all inventory valuation methodology.",            ),        );                            } // -------------- END __construct --------------                public function GetBuildRecord_InventoryAssemblyBuildId()    {        # FUNCTION :: Load the Build Record when given a specific ID from the database                if (!$this->Inventory_Assembly_Build_ID) {  echo "<br />ERROR :: $this->Class :: GetBuildRecord_InventoryAssemblyBuildId :: Missing Inventory_Assembly_Build_ID"; exit(); }                        // ----- get the build record        $record = $this->SQL->GetRecord(array(            'table' => 'inventory_assembly_build',            'keys'  => 'build_record_array, inventory_assemblies_id',            'where' => "inventory_assembly_build_id='{$this->Inventory_Assembly_Build_ID}' AND active=1",        ));        $this->EchoQuery();                        // ----- if no build record found - get a default build record        if (!$record['build_record_array']) {            // ----- get assembly build from ID            $inventory_assemblies_id    = $record['inventory_assemblies_id'];            $records_assembly_lines     = $this->GetAssemblyRecord($inventory_assemblies_id);                        // ----- create the build record from assembly lines            $Obj_Build                  = new Inventory_InventoryAssemblyBuild();            $build_record               = $Obj_Build->CreateBuildRecord($records_assembly_lines);                        // ===== Because this build record wasn't in database filed - we can't be positive that its the actual one used.            // ===== Instead we now have to treat this like an ADJUSTMENT - and call that class                        echo "<br />ERROR :: $this->Class :: GetBuildRecord_InventoryAssemblyBuildId() :: Had to use default build record";            exit();                    } else {                                    $build_record   = $record['build_record_array'];          // get build record from database            $build_record   = unserialize($build_record);             // unserialize the build record                        $COGS    = 0;            $cogs_total     = 0;            $cogs_array     = array();                        //$this->EchoVar('COGSAssembly :: build_record', $build_record);                        foreach ($build_record as $build_record_line) {                                $barcode_b  = $build_record_line['barcode'];                $quantity   = $build_record_line['quantity_out'];                                                                // ----- find the record in the database that was used to build the same Inventory_Assembly_Build_ID                $records_used_for_build = $this->SQL->GetArrayAll(array(                    'table' => 'inventory_counts',                    'keys'  => '*',                    'where' => "ref_assembly_build_id='{$this->Inventory_Assembly_Build_ID}' AND qty_out > 0 AND barcode='{$barcode_b}' AND active=1",                ));                                                                $cogs    = 0;                $cogs_total     = 0;                $cogs_array_temp = array();                foreach ($records_used_for_build as $rec_u) {                                        //$this->EchoVar('NOTICE', 'Processing a record loop in records_c', 'blue');                                        // ----- this may be an outbound record - and we need to process inbound inventory                                        // ?????????????????????????????????????????? //                    // ?????????????????????????????????????????? //                    // ?????????????????????????????????????????? //                                        $this->EchoVar('COGSAssembly() :: rec_u', $rec_u);                                        // ===== CALL THE HANDLER CLASS AND PROCESS THIS RECORD =====                    $Obj_Handler = new Inventory_COGSHandler();                    $Obj_Handler->Inventory_Counts_ID   = $rec_u['inventory_counts_id'];                    $Obj_Handler->Quantity              = $quantity;                        // how many of this item we need to build                    $Obj_Handler->Execute();                                        // ----- Store the COGS information                    $cogs        += $Obj_Handler->COGS;                    $cogs_array_temp     = $Obj_Handler->COGS_Array;                }                                $sku = $this->GetInventoryRecordFromBarcode($barcode_b);                $sku = $sku['retailer_code'];                                $cogs_array[] = array(                    'barcode'           => $barcode_b,                    'sku'               => $sku,                    'quantity'          => $quantity,                    'cogs'              => $cogs,                    'class'             => 'Inventory_COGSAssembly',                    'cogs_array'        => $cogs_array_temp,                );                            }                        $this->COGS      += $COGS;            $this->COGS_Array       = $cogs_array;        }                                                        // ===== NOW WE HAVE A BUILD RECORD        // ===== PROCESS THROUGH EACH OF ITS PARTS                // ----- since we have a legitimate build record                //$this->EchoVar('this->Inventory_Assembly_Build_ID', $this->Inventory_Assembly_Build_ID);        //$this->EchoVar('AAAAA build_record', $build_record);                        //$Obj_TZ = new Inventory_InventoryAssemblyCalculateValue();            }            public function GetAssemblyRecord($inventory_assemblies_id)    {        $records = $this->SQL->GetArrayAll(array(            'table' => " inventory_assembly_lines",            'keys'  => "*",            'where' => "`inventory_assemblies_id`='{$inventory_assemblies_id}' AND active=1",        ));        $this->EchoQuery();        return $records;    }                                    public function Execute()    {                if (false) {            $this->Barcode      = 10000;            $this->Date         = '2012-11-21';            $this->Quantity     = 1;            $this->Record_Type  = 'assembly';        }                if (!$this->Barcode || !$this->Date || !$this->Quantity || !$this->Record_Type) {            echo "<br />ERROR :: Inventory_GetRecordUsedOnDate :: Execute() :: Missing information";            $this->EchoVar('this->Barcode', $this->Barcode);            $this->EchoVar('this->Date', $this->Date);            $this->EchoVar('this->Quantity', $this->Quantity);            $this->EchoVar('this->Record_Type', $this->Record_Type);            return 0;        }                $rec            = $this->GetInventoryRecordFromBarcode($this->Barcode);        $sku            = $rec['retailer_code'];                if (true) {            $this->EchoVar('<br /><br />');            $this->EchoVar('this->Barcode', $this->Barcode);            $this->EchoVar('sku', $sku);            $this->EchoVar('this->Date', $this->Date);        }                $records = $this->GetInventoryMovements();      // get all the inbound and outbound inventory movements for given barcode(s)        $this->ExplodeMovements($records);              // make each barcode movement take up one line - and additional processing        $this->CreateInOutMatchedArray();               // match up inventory movements - creates new array                 $this->GetRecordsOnDate();                      // get the inventory movements happening on date interested in - creates new array                        #$this->EchoVar('Inventory_In', $this->Inventory_In);        #$this->EchoVar('Inventory_Out', $this->Inventory_Out);                        #echo "<br />INVENTORY IN / OUT<br />" .             $this->ConvertArrayToTable($this->Inventory_All);        #echo "<br />INVENTORY IN<br />" .                   $this->ConvertArrayToTable($this->Inventory_In);        #echo "<br />INVENTORY OUT<br />" .                  $this->ConvertArrayToTable($this->Inventory_Out);        #echo "<br />INVENTORY IN - OUT COMBINED<br />" .    $this->ConvertArrayToTable($this->Inventory_In_Out_Matched);        #echo "<br />DATE ARRAY<br />" .                     $this->ConvertArrayToTable($this->Inventory_In_Out_Date);        #echo "<br />INVENTORY AVAILABLE<br />" .            $this->ConvertArrayToTable($this->Inventory_Available);                        //$this->EchoVar('records', $records);                //$this->EchoVar('<br /><br /><hr>');        $cogs_temp = $this->GetCogs();        //$this->EchoVar('cogs_temp', $cogs_temp);    }            public function GetCogs()    {        # FUNCTION :: Determine what the actual COGS value is                $qty_available = count($this->Inventory_Available);                if ($this->Quantity > $qty_available) {            echo "<br />ERROR :: Inventory_GetRecordUsedOnDate :: GetCogs() :: Not enough inventory available";            exit();        }                #$this->EchoVar('', '');        #$this->EchoVar('qty_available', $qty_available);        #$this->EchoVar('qty conuting', $this->Quantity);                $cogs = 0;        for ($i=0; $i<$this->Quantity; $i++) {            $cogs += $this->Inventory_Available[$i]['cogs_in'];        }                $this->COGS = $cogs;        return $this->COGS;    }        public function GetInventoryCountsID()    {        # FUNCTION :: Determine what inventory_counts_id were used                $qty_available = count($this->Inventory_Available);                if ($this->Quantity > $qty_available) {            echo "<br />ERROR :: Inventory_GetRecordUsedOnDate :: GetInventoryCountsID() :: Not enough inventory available";            exit();        }                #$this->EchoVar('', '');        #$this->EchoVar('qty_available', $qty_available);        #$this->EchoVar('qty conuting', $this->Quantity);                $inventory_counts_id = array();        for ($i=0; $i<$this->Quantity; $i++) {            $inventory_counts_id[] = $this->Inventory_Available[$i]['inventory_counts_id_in'];        }                $this->Inventory_Counts_ID = $inventory_counts_id;        return $this->Inventory_Counts_ID;    }            public function ExplodeMovements($ARR)    {        # FUNCTION :: Explode the inventory groupings into individual array lines                foreach ($ARR as $id => $record) {                        $qty = $record['qty_in'] + $record['qty_out'];      // can't be both in and out so sum is total qty                        for ($i=0; $i<$qty; $i++) {                                // ----- determine the actual cost for this item                $cost_each_default  = $record['DEFAULT_PRICE_EACH'];                $cost_total_sum     = $record['IN_PRICE_TOTAL'] + $record['IN_PRICE_SHIPPING'] + $record['ADJ_PRICE_TOTAL'] + $record['ADJ_PRICE_SHIPPING'] + $record['ASSY_PRICE_TOTAL'];                $cost_each_sum      = ($cost_total_sum / ($record['qty_in'] + $record['qty_out']));                $cost_each          = ($cost_total_sum == 0) ? $cost_each_default : $cost_each_sum;                                // ----- determine the method the inventory change was made with                $method             = 'default';                $method             = (isset($record['IN_DATE']))               ? "purchase order received<br />(PO#: {$record['IN_REF_NUMBER']})"  : $method;                $method             = (isset($record['ADJ_DATE']))              ? 'inventory adjustment'                                            : $method;                $method             = (isset($record['ASSY_DATE']))             ? 'assembly build'                                                  : $method;                $method             = (isset($record['OUT_DATE']))              ? "sales order sent<br />(SO#: {$record['OUT_REF_NUMBER']})"        : $method;                                $method_type        = 'default';                $method_type        = (isset($record['IN_DATE']))               ? 'purchaseorder'                   : $method_type;                $method_type        = (isset($record['ADJ_DATE']))              ? 'adjustment'                      : $method_type;                $method_type        = (isset($record['ASSY_DATE']))             ? 'assembly'                        : $method_type;                $method_type        = (isset($record['OUT_DATE']))              ? 'salesorder'                      : $method_type;                                // ----- determine the method ID the inventory change was made with                $method_id          = 0;                $method_id          = (isset($record['IN_DATE']))               ? $record['IN_REF_ID']              : $method_id;                $method_id          = (isset($record['ADJ_DATE']))              ? $record['ADJ_REF_ID']             : $method_id;                $method_id          = (isset($record['ASSY_DATE']))             ? $record['ASSY_REF_ID']            : $method_id;                $method_id          = (isset($record['OUT_DATE']))              ? $record['OUT_SO_REF_ID']          : $method_id;                                                $price_total        = 0;                $price_total        = (isset($record['IN_PRICE_TOTAL']))        ? $record['IN_PRICE_TOTAL']         : $price_total;                $price_total        = (isset($record['ADJ_PRICE_TOTAL']))       ? $record['ADJ_PRICE_TOTAL']        : $price_total;                $price_total        = (isset($record['ASSY_PRICE_TOTAL']))      ? 0                                 : $price_total; //$record['ASSY_PRICE_TOTAL']                $price_total        = (isset($record['OUT_DATE']))              ? 0                                 : $price_total;                                                $price_each         = 0;                $price_each         = (isset($record['IN_PRICE_EACH']))         ? $record['IN_PRICE_EACH']          : $price_each;                $price_each         = (isset($record['ADJ_PRICE_EACH']))        ? $record['ADJ_PRICE_EACH']         : $price_each;                $price_each         = (isset($record['ASSY_PRICE_EACH']))       ? 0                                 : $price_each; //$record['ASSY_PRICE_EACH']                $price_each         = (isset($record['OUT_DATE']))              ? 0                                 : $price_each;                                                $price_shipping     = 0;                $price_shipping     = (isset($record['IN_PRICE_SHIPPING']))     ? $record['IN_PRICE_SHIPPING']      : $price_shipping;                $price_shipping     = (isset($record['ADJ_PRICE_SHIPPING']))    ? $record['ADJ_PRICE_SHIPPING']     : $price_shipping;                $price_shipping     = (isset($record['ASSY_PRICE_EACH']))       ? 0                                 : $price_shipping;                $price_shipping     = (isset($record['OUT_DATE']))              ? 0                                 : $price_shipping;                                                $method             = ucfirst($method);                $source             = "$method<br />ID: $method_id";                $in                 = ($record['qty_in'] > 0) ? 1 : 0;                $out                = ($record['qty_out'] > 0) ? 1 : 0;                $is_subassembly     = $this->CheckIfSubAssembly($record['barcode']);                                // ----- if we have a per-item cost - use that -- otherwise calculate from total price                if (!$is_subassembly) {                                        $price_shipping_each    = ($price_shipping / $qty);                    $price_product_each     = ($price_each) ? $price_each : ($price_total / $qty);                    $cogs                   = $price_product_each + $price_shipping_each;                                                        } else {                    // -----= recursively call to breakdown this item and get real price                    $cogs = 'ASSY';                }                                                $arr_temp = array (                    'barcode'               => $record['barcode'],                    'date'                  => $record['date'],                    'in'                    => $in,                    'out'                   => $out,                    'cogs'                  => $cogs,                    'source'                => $source,                    'source_id'             => $method_id,                    'method_type'           => $method_type,                    'inventory_counts_id'   => $record['inventory_counts_id'],                    'is_subassembly'        => $is_subassembly,                );                                                if ($in)    { $this->Inventory_In[]     = $arr_temp; };                if ($out)   { $this->Inventory_Out[]    = $arr_temp; };                                $this->Inventory_All[]            = $arr_temp;            }        }                //return $ARR;    }        public function CreateInOutMatchedArray()    {        $last_id                        = -1;        $inventory_in_remaining_arr     = $this->Inventory_In;        $current_inventory_in_id        = 0;        $current_inventory_out_id       = 0;                if (is_array($this->Inventory_Out)) {            foreach ($this->Inventory_Out as $record) {                                $current_inventory_in_id        = $last_id + 1;                $current_inventory_out_id       = $last_id + 1;                                $barcode                        = $record['barcode'];                $date_out                       = $record['date'];                $next_available_date_in         = $this->Inventory_In[$current_inventory_in_id]['date'];                                // ----- check if there's inventory in before or on this date                if ($date_out < $next_available_date_in) {                    $passed     = false;                    $error      = "ERROR :: Inventory does not exist in system as of the date trying to remove. As of <b>({$date_out})</b> there needs to be more in inventory for barcode <b>({$barcode})</b>.";                    $this->AddError($error);                    echo "<br />{$error}";                } else {                                        $record_in  = $this->Inventory_In[$current_inventory_in_id];                    $record_out = $this->Inventory_Out[$current_inventory_out_id];                                        $arr = array(                        'barcode'                   => $record_in['barcode'],                        'is_subassembly'            => $record_in['is_subassembly'],                        'date_in'                   => $record_in['date'],                        'qty_in'                    => $record_in['in'],                        'cogs_in'                   => $record_in['cogs'],                        'source_in'                 => $record_in['source'],                        'source_type_in'            => $record_in['method_type'],                        'source_id_in'              => $record_in['source_id'],                        'inventory_counts_id_in'    => $record_in['inventory_counts_id'],                                                'date_out'                  => $record_out['date'],                        'qty_out'                   => $record_out['out'],                        'cogs_out'                  => $record_out['cogs'],                        'source_out'                => $record_out['source'],                        'source_type_out'           => $record_out['method_type'],                        'source_id_out'             => $record_out['source_id'],                        'inventory_counts_id_out'   => $record_out['inventory_counts_id'],                    );                                        $this->Inventory_In_Out_Matched[] = $arr;                                        if ($record_out['method_type'] == $this->Record_Type) {                        $this->Inventory_Available[] = $arr;                    }                                        #unset($this->Inventory_In[$current_inventory_in_id]);                    #unset($this->Inventory_Out[$current_inventory_out_id]);                    unset($inventory_in_remaining_arr[$current_inventory_in_id]);                }                                $last_id++;                            } // end foreach        }                // ----- Determine if there are any remaining inbound - add to array        if (is_array($inventory_in_remaining_arr)) {            foreach ($inventory_in_remaining_arr as $record) {                //$this->EchoVar('record', $record);                                $arr = array(                    'barcode'                   => $record['barcode'],                    'is_subassembly'            => $record['is_subassembly'],                    'date_in'                   => $record['date'],                    'qty_in'                    => $record['in'],                    'cogs_in'                   => $record['cogs'],                    'source_in'                 => $record['source'],                    'source_type_in'            => $record['method_type'],                    'source_id_in'              => $record['source_id'],                    'inventory_counts_id_in'    => $record['inventory_counts_id'],                                        'date_out'                  => '',                    'qty_out'                   => '',                    'cogs_out'                  => '',                    'source_out'                => '',                    'source_type_out'           => '',                    'source_id_out'             => '',                    'inventory_counts_id_out'   => '',                );                                $this->Inventory_In_Out_Matched[]   = $arr;     // inbound inventory should show in table                $this->Inventory_Available[]        = $arr;     // all inbound inventory is available            }        }    }        public function GetRecordsOnDate()    {        foreach ($this->Inventory_In_Out_Matched as $record) {            if ($record['date_out'] == $this->Date) {                $this->Inventory_In_Out_Date[] = $record;            }        }    }        public function GetInventoryMovements($BARCODE='', $DATE='')    {        # FUNCTION :: Get all the inventory INs and OUTs related to a specified barcode                $wheredate = ($this->Date) ? " AND inventory_counts.date <= '{$this->Date}' " : "";                $records = $this->SQL->GetArrayAll(array(            'table' => 'inventory_counts',            'keys'  => "    inventory_counts.*,                             `inventory_purchase_order_received`.`price_total`                               AS IN_PRICE_TOTAL,                            `inventory_purchase_order_received`.`price_each`                                AS IN_PRICE_EACH,                            `inventory_purchase_order_received`.`price_shipping`                            AS IN_PRICE_SHIPPING,                            `inventory_purchase_order_received`.`date`                                      AS IN_DATE,                            `inventory_purchase_order_received`.`inventory_purchase_order_received_id`      AS IN_REF_ID,                            `inventory_purchase_order_received`.`po_number`                                 AS IN_REF_NUMBER,                            `inventory_purchase_order_received`.`active`                                    AS IN_ACTIVE,                            `inventory_purchase_order_received`.`notes`                                     AS IN_NOTES,                                                        `inventory_adjustments`.`price_total`                                           AS ADJ_PRICE_TOTAL,                            `inventory_adjustments`.`price_each`                                            AS ADJ_PRICE_EACH,                            `inventory_adjustments`.`price_shipping`                                        AS ADJ_PRICE_SHIPPING,                            `inventory_adjustments`.`date`                                                  AS ADJ_DATE,                            `inventory_adjustments`.`inventory_adjustments_id`                              AS ADJ_REF_ID,                            `inventory_adjustments`.`active`                                                AS ADJ_ACTIVE,                            `inventory_adjustments`.`notes`                                                 AS ADJ_NOTES,                                                        `inventory_assembly_build`.`price_total`                                        AS ASSY_PRICE_TOTAL,                            `inventory_assembly_build`.`price_each`                                         AS ASSY_PRICE_EACH,                            `inventory_assembly_build`.`date`                                               AS ASSY_DATE,                            `inventory_assembly_build`.`inventory_assembly_build_id`                        AS ASSY_REF_ID,                            `inventory_assembly_build`.`active`                                             AS ASSY_ACTIVE,                            `inventory_assembly_build`.`notes`                                              AS ASSY_NOTES,                                                        `inventory_sales_order_sent`.`date`                                             AS OUT_DATE,                            `inventory_sales_order_sent`.`so_number`                                        AS OUT_SO_NUMBER,                            `inventory_sales_order_sent`.`inventory_sales_order_sent_id`                    AS OUT_SO_REF_ID,                            `inventory_sales_order_sent`.`so_number`                                        AS OUT_REF_NUMBER,                            `inventory_sales_order_sent`.`active`                                           AS OUT_ACTIVE,                            `inventory_sales_order_sent`.`notes`                                            AS OUT_NOTES,                                                        `inventory_products`.`part_cost`                                                AS DEFAULT_PRICE_EACH,                            `inventory_products`.`description`                                              AS description,                            `inventory_products`.`retailer_code`                                            AS retailer_code,                            `inventory_products`.`price_reference_source`,                            `inventory_products`.`price_reference_number`,                            `inventory_products`.`price_reference_date`,                            `inventory_products`.`price_reference_url`,                            `inventory_products`.`price_reference_price`,                            `inventory_products`.`price_reference_quantity`,                            `inventory_products`.`status_use_assembly_pricing`,                            `inventory_products`.`inventory_assemblies_id`            ",            'where' => "`inventory_counts`.`barcode`='{$this->Barcode}' {$wheredate} AND                                 (                `inventory_counts`.`active`=1 OR                 `inventory_purchase_order_received`.`active`=1 OR                 `inventory_adjustments`.`active`=1 OR                `inventory_assembly_build`.`active`=1  OR                 `inventory_sales_order_sent`.`active`=1                  ) AND                                 (                `inventory_counts`.`qty_in` > 0 OR                 `inventory_counts`.`qty_out` > 0                 ) AND                                 `inventory_counts`.`active` = 1            ",            'joins' => "                        LEFT JOIN `inventory_purchase_order_received`   ON `inventory_purchase_order_received`.`inventory_purchase_order_received_id`   = `inventory_counts`.`ref_purchase_orders_received_id`                         LEFT JOIN `inventory_adjustments`               ON `inventory_adjustments`.`inventory_adjustments_id`                           = `inventory_counts`.`ref_adjustment_id`                         LEFT JOIN `inventory_assembly_build`            ON `inventory_assembly_build`.`inventory_assembly_build_id`                     = `inventory_counts`.`ref_assembly_build_id`                         LEFT JOIN `inventory_sales_order_sent`          ON `inventory_sales_order_sent`.`inventory_sales_order_sent_id`                 = `inventory_counts`.`ref_sales_order_sent_id`                         LEFT JOIN `inventory_products`                  ON `inventory_products`.`barcode`                                               = `inventory_counts`.`barcode`            ",            'order' => "`inventory_counts`.`date` ASC",        ));        $this->EchoQuery();        //$this->EchoVar('records', $records);                return $records;    }        public function CheckIfSubAssembly($barcode)    {        // Check if this item is a sub-assembly                $records = $this->SQL->GetArrayAll(array(            'table' => 'inventory_assemblies',            'keys'  => 'inventory_assemblies_id',            'where' => "barcode='{$barcode}' AND active=1"        ));        $this->EchoQuery();                $is_subassembly = ($records) ? 1 : 0;        return $is_subassembly;    }        }  // -------------- END CLASS --------------