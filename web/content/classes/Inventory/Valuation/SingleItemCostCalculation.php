<?phpclass Inventory_Valuation_SingleItemCostCalculation extends BaseClass       // don't need functions from InventoryBase plus makes smaller memory footprint{    public $Show_Query                      = false;                // (FALSE) TRUE = show database queries used in this class    public $Show_Error                      = false;                // (FALSE) TRUE = will echo errors in class as soon as they happen.    public $Show_Notice                     = false;                // (FALSE) TRUE = will echo notices in class as soon as they happen.            // ===== INPUTS ====================    public $Barcode                         = 0;                    // barcode you want value for    public $Date                            = '';                   // if running FIFO method - this is the date of _____ ???    public $Method                          = 'total_average';      // (total_average) this is the 'preferred' method to calculate cost            // ===== OUTPUTS ====================    public $Value_Array                     = array();              // this helps us understand how cost was calculated    public $Price_Calculated_Each           = 0;                    // final price calculated for single item    public $Price_Calculated_Shipping_Each  = 0;                    // final shipping price calculated for single item    public $Used_Default_Value              = 0;                    // if returns 1 - means the default value was used    public $Used_Method                     = '';                   // will store what method was finally used to get cost    public $Build_Record                    = array();              // if this turns out to be an assembly - it will store assy record used            // ===== GENERAL ====================    public $Classname                       = "";                   // need var because not available from InventoryBase    public $Error_Arr                       = array();              // will hold errors generated by this class    public $Have_Error                      = 0;                    // will = 1 if there are any errors     public $Notice_Arr                      = array();              // will hold notices generated by this class    public $Have_Notice                     = 0;                    // will = 1 if there are any notices             public function  __construct()    {        parent::__construct();                $this->Classname = get_class($this);        $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2013-01-23',            'Updated By'    => 'Richard Witherspoon',            'Updated Date'  => '2013-02-26',            'Filename'      => $this->Classname,            'Version'       => '2.0',            'Description'   => 'Functions used to determine the cost of an item - usually when making an adjustment.',            'Update Log'    => array(                '2013-01-23_001'    => "Module Created",                '2013-02-24_2.0'    => "Significant restructuring of all inventory valuation methodology.",                '2013-02-26_2.1'    => "Code changes to support database field change",            ),        );                $this->Date = date('Y-m-d');            } // -------------- END __construct --------------        public function Pseudocode()    {        # FUNCTION :: Pseudocode for this class.                $output = "        [Description]:        Adding an item to database - determine how much it should cost.                Determine how much an item should cost when being added to the system. This often         happens when making an inventory adjustment - we don't have a real price so we need to know what         the system thinks the cost should be. If this was a purchase order - we would know the exact price.                [Methods]:        total_average = Takes all inventory items ever purchased (or inbound) and averages cost        fifo = Functionality not implemented                [To Do List]:        Implement the 'fifo' method        ";                $title      = "$this->Classname :: Pseudocode()";        $content    = $this->PseudocodeFormat($output);        $this->EchoInformation($title, $content, 'blue');            }        public function Test_ShowOutputs()    {        # FUNCTION :: Output all the various class output variables                echo "<br />";        echo "<div style='border:2px solid green;'>";        echo "<div style='padding:5px; background-color:#e3e3e3; font-weight:bold;'>$this->Classname :: Test_ShowOutputs()</div>";        echo "<div style='padding:5px;'>";                $this->EchoVar('Value_Array', $this->Value_Array);        $this->EchoVar('Price_Calculated_Each', $this->Price_Calculated_Each);        $this->EchoVar('Price_Calculated_Shipping_Each', $this->Price_Calculated_Shipping_Each);        $this->EchoVar('Used_Default_Value', $this->Used_Default_Value);        $this->EchoVar('Used_Method', $this->Used_Method);        $this->EchoVar('Build_Record', $this->Build_Record);        $this->EchoVar('Have_Error', $this->Have_Error);        $this->EchoVar('Error_Arr', $this->Error_Arr);                        echo "</div>";        echo "</div>";        echo "<br />";    }            public function Execute()    {                /* ===== P-CODE =======================================================        Check if assembly        if assembly - get assy pricing        if not assembly - get barcode pricing        If can't derive from these methods - fall back to default cost        ==================================================================== */                switch ($this->Method) {            case 'total_average':                $result                 = 0;                                                                        // initialize result - which stores 1 if a cost can be determined                $this->Used_Method      = 'total_average';                                                          // initialize method used                $is_assembly            = $this->CheckIfSubAssembly($this->Barcode);                                // determine if barcode is an assembly                                if ($is_assembly) {                    $barcode            = $this->Barcode;                                                           // set the barcode - even though function can figure it out                    $build_record       = '';                                                                       // we have no known build record - it will be determined in next function                    $result             = $this->CalculatePrice_Assembly_TotalAverage($barcode, $build_record);     // calculate value                    $this->Used_Method  = 'assembly_total_average';                                                 // set status of what method actually used                } else {                    $barcode            = $this->Barcode;                                                           // set the barcode - even though function can figure it out                    $result             = $this->CalculatePrice_Barcode_TotalAverage($barcode);                     // calculate value                    $this->Used_Method  = 'barcode_total_average';                                                  // set status of what method actually used                                    // ----- FAILED TO GET ANY COST FOR ITEM                    if (!$result) {                        $barcode            = $this->Barcode;                                                       // set the barcode - even though function can figure it out                        $result             = $this->CalculatePrice_Barcode_DefaultCost($barcode);                  // get default value                        $this->Used_Method  = 'default_value';                                                      // set status of what method actually used                    }                }                                $output = $result;            break;                        case 'fifo':                $result                 = 0;                                                                        // initialize result - which stores 1 if a cost can be determined                $this->Used_Method      = 'fifo';                                                                   // initialize method used                $is_assembly            = $this->CheckIfSubAssembly($this->Barcode);                                // determine if barcode is an assembly                                if ($is_assembly) {                    $barcode            = $this->Barcode;                                                           // set the barcode - even though function can figure it out                    $build_record       = '';                                                                       // we have no known build record - it will be determined in next function                    $date               = $this->Date;                                                              // set the date inventory being calculated for                    $result             = $this->CalculatePrice_Assembly_FIFO($barcode, $build_record, $date);      // calculate value                    $this->Used_Method  = 'assembly_fifo';                                                          // set status of what method actually used                } else {                    $barcode            = $this->Barcode;                                                           // set the barcode - even though function can figure it out                    $date               = $this->Date;                                                              // set the date inventory being calculated for                    $result             = $this->CalculatePrice_Barcode_FIFO($barcode, $date);                      // calculate value                    $this->Used_Method  = 'barcode_fifo';                                                           // set status of what method actually used                                        // ----- FAILED TO GET ANY COST FOR ITEM                    if (!$result) {                        $barcode            = $this->Barcode;                                                       // set the barcode - even though function can figure it out                        $result             = $this->CalculatePrice_Barcode_DefaultCost($barcode);                  // get default value                        $this->Used_Method  = 'default_value';                                                      // set status of what method actually used                    }                }                                $output = $result;            break;                        default:                $error = "No Method provided";                $this->AddError($this->Classname, __FUNCTION__, $error);                $this->EchoError($this->Classname, __FUNCTION__, $error, true);                $output = 0;            break;        }                return $output;    }                public function CalculatePrice_Barcode_DefaultCost($barcode=0)    {        # FUNCTION :: Calculate the price for a new barcode item based on the logic of this function        #     NOTE :: Usually this is used when making an adjustment - to calculate value of each adjustment item. This should be last resort.        #   METHOD :: DefaultCost - Use the default cost stored for this barcode.                        $barcode                    = ($barcode != 0) ? $barcode : $this->Barcode;      // get barcode        $return                     = 0;                                                // initialize return variable        $this->Used_Default_Value   = 1;                                                // set status that we had to use default value                        if (!$barcode) {            $error = "No barcode provided";            $this->AddError($this->Classname, __FUNCTION__, $error);            $this->EchoError($this->Classname, __FUNCTION__, $error, true);            exit();        }                if ($barcode) {            // ----- get default value from database            $record = $this->SQL->GetRecord(array(                'table' => 'inventory_products',                'keys'  => '*',                'where' => "barcode='{$barcode}' AND active=1",            ));            $this->EchoQuery();                        if ($record) {                // ----- store the values -----                $this->Price_Calculated_Each            = $record['part_cost'];             // part cost stored in database                $this->Price_Calculated_Shipping_Each   = 0;                                // no default shipping cost stored in database                $this->Value_Array[] = array(                                               // store the reference information for this default cost                    'function'                  => 'CalculatePrice_Barcode_DefaultCost',                    'default_cost'              => $record['part_cost'],                    'price_reference_source'    => $record['price_reference_source'],                    'price_reference_number'    => $record['price_reference_number'],                    'price_reference_date'      => $record['price_reference_date'],                    'price_reference_url'       => $record['price_reference_url'],                    'price_reference_price'     => $record['price_reference_price'],                    'price_reference_quantity'  => $record['price_reference_quantity'],                    'price_reference_notes'     => $record['price_reference_notes'],                );                                $return = 1;                                                                // return 1 showing sucessful information            }        }                return $return;    }        public function CalculatePrice_Barcode_TotalAverage($barcode=0)    {        # FUNCTION :: Calculate the price for a new barcode item based on the logic of this function        #     NOTE :: Usually this is used when making an adjustment - to calculate value of each adjustment item        #   METHOD :: TotalAverage - Takes an average of all physical purchases (purchase orders) in the system - no date restriction                $barcode = ($barcode != 0) ? $barcode : $this->Barcode;                if (!$barcode) {            $error = "No barcode provided";            $this->AddError($this->Classname, __FUNCTION__, $error);            $this->EchoError($this->Classname, __FUNCTION__, $error, true);            exit();        }                // ----- look at all purchase orders received to determine what inbound price is        $records = $this->SQL->GetArrayAll(array(            'table' => 'inventory_purchase_order_received',            'keys'  => '*',            'where' => "barcode='{$barcode}' AND quantity > 0 AND active=1",        ));        $this->EchoQuery();                                if (!$records) {            $notice = "No purchase order records found for barcode. Barcode: {$barcode}";            $this->AddNotice($this->Classname, __FUNCTION__, $notice);            $this->EchoNotice($this->Classname, __FUNCTION__, $notice, false);            return 0;        }                $this->Value_Array                      = array();        $this->Price_Calculated_Each            = 0;        $this->Price_Calculated_Shipping_Each   = 0;        $cost_total_sum                         = 0;        $cost_shipping_total_sum                = 0;        $quantity_sum                           = 0;                foreach ($records as $record) {                        // ----- initialize variables            $price_total            = 0;            $price_shipping_total   = 0;                                    // ----- calculate the actual price            $qty_total              = $record['quantity'];            $price_total            = $record['price_total'];            $price_shipping_total   = $record['price_shipping_total'];                                    // ----- store the information            $cost_total_sum             += $price_total;            $cost_shipping_total_sum    += $price_shipping_total;            $quantity_sum               += $qty_total;                        $inventory_counts_id                    = isset($record['inventory_counts_id']) ? $record['inventory_counts_id'] : 0;            $inventory_purchase_order_received_id   = isset($record['inventory_purchase_order_received_id']) ? $record['inventory_purchase_order_received_id'] : 0;            $IC_DATE                                = isset($record['IC_DATE']) ? $record['IC_DATE'] : $record['date'];                        // ----- this helps us understand how cost was calculated            $this->Value_Array[] = array(                'function'                              => 'CalculatePrice_Barcode_TotalAverage',                'inventory_counts_id'                   => $inventory_counts_id,                'inventory_purchase_order_received_id'  => $inventory_purchase_order_received_id,                'date'                                  => $IC_DATE,                'quantity'                              => $qty_total,                'record_price_total'                    => $price_total,                'record_price_shipping_total'           => $price_shipping_total,            );        }                        // ----- calculate the total average        $this->Price_Calculated_Each            = ($cost_total_sum / $quantity_sum);        $this->Price_Calculated_Shipping_Each   = ($cost_shipping_total_sum / $quantity_sum);                return 1;    }        public function CalculatePrice_Assembly_TotalAverage($barcode=0, $assembly_bom='')    {        # FUNCTION :: Calculate the price for a new assembly item based on the logic of this function        #     NOTE :: Usually this is used when making an adjustment - to calculate value of each adjustment item        #     NOTE :: For each barcode - if it can't get a calculated cost - it will go to the default velue        #   METHOD :: TotalAverage - Takes an average of all physical purchases (purchase orders) in the system - no date restriction        #        #   P-CODE :: Break apart each assembly item to its raw inventory barcode - then calculate the average for each barcode                        $assy_barcode   = ($barcode != 0) ? $barcode : $this->Barcode;        $return         = 1;        $show_debug     = false;                        if (!$assy_barcode) {            $error = "No assy_barcode provided";            $this->AddError($this->Classname, __FUNCTION__, $error);            $this->EchoError($this->Classname, __FUNCTION__, $error, true);            exit();        }                // ----- Check to see if we passed in a BOM or build record - use that        // ----- If no build record - get the default build record (by using the Inventory_AssemblyExplode Class)        if (!$assembly_bom) {            $Obj_Explode = new Inventory_AssemblyExplode();         // Instantiate the assembly explode class            $Obj_Explode->Barcode = $assy_barcode;                  // Set barcode for the assembly we want to explode            $Obj_Explode->Quantity = 1;                             // Set count for a single assembly            $Obj_Explode->Execute();                                // Run the function            $assembly_bom = $Obj_Explode->BOM;                      // Store the BOM for this assembly Array(barcode => quantity, barcode => quantity, etc...)        }                $this->Build_Record = $assembly_bom;                #$this->EchoVar('assembly_bom', $assembly_bom);        #exit();                $value_array_temp   = array();      // initialize temp array variable        $price_total_temp   = 0;        $ship_total_temp    = 0;                $sku = $this->GetInventoryItemRetailerCodeFromBarcode($assy_barcode);                if ($show_debug) {$this->EchoVar('', '<div style="border:1px solid green; padding:10px; margin:10px;">');}        if ($show_debug) {$this->EchoVar('Notice', 'BEGINNING LOOP', 'blue');}        if ($show_debug) {$this->EchoVar('Master Barcode', $assy_barcode);}        if ($show_debug) {$this->EchoVar('sku', $sku);}        if ($show_debug) {$this->EchoVar('assembly_bom', $assembly_bom);}                foreach ($assembly_bom as $barcode => $quantity) {                        if ($show_debug) {$this->EchoVar('', '');}            if ($show_debug) {$this->EchoVar('barcode', $barcode);}            if ($show_debug) {$this->EchoVar('quantity', $quantity);}                        $this->Value_Array  = array();                                          // reset the variable            $result = $this->CalculatePrice_Barcode_TotalAverage($barcode);         // Get the average cost for this barcode                        if (!$result) {                if ($show_debug) {$this->EchoVar('Notice', 'Trying for default value', 'blue');}                $result = $this->CalculatePrice_Barcode_DefaultCost($barcode);      // Get the default cost for this barcode                if ($result) {                    if ($show_debug) {$this->EchoVar('Notice', 'Default Value Found');}                }            }                        if ($result) {                                $price_total    = ($this->Price_Calculated_Each * $quantity);               // Calc total for this barcode given qty in assembly                $ship_total     = ($this->Price_Calculated_Shipping_Each * $quantity);      // Calc ship total for this barcode given qty in assembly                                $price_total_temp   += $price_total;                                        // Store value                $ship_total_temp    += $ship_total;                                         // Store value                $value_array_temp[$barcode] = $this->Value_Array;                           // Store the value_array for this barcode - shows how barcode cost was calculated                $value_array_temp[$barcode]['function'] = 'CalculatePrice_Assembly_TotalAverage';                                if ($show_debug) {$this->EchoVar('Price Each', $this->Price_Calculated_Each);}                if ($show_debug) {$this->EchoVar('Ship Each', $this->Price_Calculated_Shipping_Each);}                if ($show_debug) {$this->EchoVar('Price Total', $price_total);}                if ($show_debug) {$this->EchoVar('Ship Total', $ship_total);}                            } else {                // ----- FAILED to get any cost for this barcode in assembly                $notice = "Couldn't calculate average for barcode. Barcode: {$barcode}";                $this->AddNotice($this->Classname, __FUNCTION__, $notice);                $this->EchoNotice($this->Classname, __FUNCTION__, $notice, false);                $return = 0;    // any failure will cause this whole function to return FALSE (0)            }        }                if ($show_debug) { $this->EchoVar('', '</div>');}                $this->Price_Calculated_Each            = $price_total_temp;        $this->Price_Calculated_Shipping_Each   = $ship_total_temp;        $this->Value_Array                      = $value_array_temp;                return $return;    }                            public function CalculatePrice_Assembly_FIFO($barcode=0, $assembly_bom='', $date='')    {        $error = "Function not implemented";        $this->AddError($this->Classname, __FUNCTION__, $error);        $this->EchoError($this->Classname, __FUNCTION__, $error, true);        exit();    }        public function CalculatePrice_Barcode_FIFO($barcode=0, $date='')    {        $error = "Function not implemented";        $this->AddError($this->Classname, __FUNCTION__, $error);        $this->EchoError($this->Classname, __FUNCTION__, $error, true);        exit();    }                    public function CheckIfSubAssembly($barcode)    {        // Check if this item is a sub-assembly                $records = $this->SQL->GetArrayAll(array(            'table' => 'inventory_assemblies',            'keys'  => 'inventory_assemblies_id',            'where' => "barcode='{$barcode}' AND active=1"        ));        $this->EchoQuery();                $is_subassembly = ($records) ? 1 : 0;        return $is_subassembly;    }        public function GetInventoryItemRetailerCodeFromBarcode($BARCODE)    {        # FUNCTION :: Return retailer_code inventory item from barcode                $return = 'ERROR :: NO BARCODE PROVIDED';                if ($BARCODE) {            $record = $this->SQL->GetRecord(array(                'table' => 'inventory_products',                'keys'  => 'retailer_code',                'where' => "`barcode`='{$BARCODE}'",            ));            $this->EchoQuery();                        $return = ($record) ? $record['retailer_code'] : "ERROR :: INVENTORY ITEM NOT FOUND";                    }                return $return;    }                public function EchoInformation($TITLE='', $INFO='', $BORDERCOLOR='black')    {        echo "            <br />            <div style='border:2px solid {$BORDERCOLOR};'>                <div style='padding:5px; background-color:#e3e3e3; font-weight:bold;'>{$TITLE}</div>                <div style='padding:5px;'>{$INFO}</div>            </div>            <br />";    }        public function PseudocodeFormat($OUTPUT)    {        # FUNCTION :: Special formatting for Pseudocode function                $search     = array('[', ']');        $replace    = array('<b>', '</b>');        $output     = str_replace($search, $replace, $OUTPUT);      // special formatting        $output     = nl2br($output);                               // create line breaks                return $output;    }                    # ===== ERROR FUNCTIONS ===============    public function AddError($CLASS='', $FUNCTION='', $ERROR='')    {        if ($ERROR) {            $this->Error_Arr[]  = "{$CLASS} :: {$FUNCTION} :: {$ERROR}";            $this->Have_Error   = 1;        }    }        public function EchoError($CLASS='', $FUNCTION='', $ERROR='', $FORCESHOW)    {        if ($this->Show_Error || $FORCESHOW) {            $this->EchoVar('ERROR', "{$CLASS} :: {$FUNCTION} :: {$ERROR}", 'red');        }    }        public function DumpErrors()    {        if ($this->Have_Error) {                        $errors = '';            foreach ($this->Error_Arr as $error) {                $errors .= "{$error}<br />";            }                        echo "<br />";            echo "<div style='border:2px solid red;'>";            echo "<div style='padding:5px; background-color:pink; font-weight:bold;'>{$this->Classname} :: DumpErrors()</div>";            echo "<div style='padding:5px;'>{$errors}</div>";            echo "</div>";            echo "<br />";        }    }                    # ===== NOTICE FUNCTIONS ===============    public function AddNotice($CLASS='', $FUNCTION='', $NOTICE='')    {        if ($NOTICE) {            $this->Notice_Arr[]  = "{$CLASS} :: {$FUNCTION} :: {$NOTICE}";            $this->Have_Notice   = 1;        }    }        public function EchoNotice($CLASS='', $FUNCTION='', $NOTICE='', $FORCESHOW)    {        if ($this->Show_Notice || $FORCESHOW) {            $this->EchoVar('NOTICE', "{$CLASS} :: {$FUNCTION} :: {$NOTICE}", 'Orange');        }    }        public function DumpNotices()    {        if ($this->Have_Notice) {                        $notices = '';            foreach ($this->Notice_Arr as $notice) {                $notices .= "{$notice}<br />";            }                        echo "<br />";            echo "<div style='border:2px solid orange;'>";            echo "<div style='padding:5px; background-color:yellow; font-weight:bold;'>{$this->Classname} :: DumpNotices()</div>";            echo "<div style='padding:5px;'>{$notices}</div>";            echo "</div>";            echo "<br />";        }    }        }  // -------------- END CLASS --------------