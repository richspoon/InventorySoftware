<?phpclass Inventory_FixInventoryAdjustmentAssemblyPrice extends Inventory_InventoryBase{    public $Show_Query                  = false;            public function  __construct()    {        parent::__construct();                $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2012-12-17',            'Updated By'    => '',            'Updated Date'  => '',            'Filename'      => 'Inventory_FixInventoryAdjustmentAssemblyPrice',            'Version'       => '1.0',            'Description'   => 'Fix bad data - inventory adjustments from an assembly - recheck the assembly price.',            'Update Log'    => array(                '2012-12-17_1.0'    => 'Module Created',            ),        );            } // -------------- END __construct --------------        public function Execute()    {        /* ===== P-CODE ===========================                1. Get all inventory_counts with an adjustment that worth $0        2. Determine the actual adjustment price based on default value        3. Adjust the invnetory adjustment to correct value        4. Modify the notes on the adjustment        5. update the main record                ======================================== */                        $records = $this->GetAllRecords();        //$this->EchoVar('records', $records);                        $passed = false;        # ===== START TRANSACTION ============================================================        $this->SQL->StartTransaction();                                if ($records){        foreach ($records as $record) {                        // ----- ONLY PROCESS THE RECORDS WITH A REAL ASSEMBLY -----            if ($record['status_use_assembly_pricing'] == 1) {                                $inventory_assemblies_id    = $record['inventory_assemblies_id'];                $default_price              = round($this->CalculateAssemblyValue($inventory_assemblies_id), 3);                $price_each                 = $default_price;                $price_total                = $default_price * $record['quantity'];                $date                       = date("Y-m-d");                $notes                      = str_replace("Price based on last found price.", "", $record['notes']);    // replace wrong note                $notes                     .= "Price based on assembly value on {$date}.";                              // tack onto end (can't do in replace in case string not there)                                // ----- update records that don't match in pricing                if (($price_each != $record['price_each']) || $price_total != $record['price_total']) {                                                            $price_each_orig    = ($record['price_each'] != 0) ? $record['price_each'] : ($record['price_total'] / $record['quantity']);                    $price_total_orig   = ($record['price_total'] != 0) ? $record['price_total'] : ($record['price_each'] * $record['quantity']);                                        echo "<br /><br /><hr>";                    $this->EchoVar('barcode', $record['barcode']);                    $this->EchoVar('retailer_code', $record['retailer_code']);                    $this->EchoVar('quantity', $record['quantity']);                    echo "<br />";                    $this->EchoVar('ORIG price_each', $price_each_orig);                    $this->EchoVar('NEW price_each', $price_each);                    echo "<br />";                    $this->EchoVar('ORIG price_total', $price_total_orig);                    $this->EchoVar('NEW price_total', $price_total);                    //$this->EchoVar('', $);                                        // ----- Update the inventory_adjustments record -----                    $db_record = array(                        'price_each'        => $price_each,                        'price_total'       => $price_total,                        'notes'             => $notes,                    );                                        $where                      = "inventory_adjustments_id='{$record['inventory_adjustments_id']}'";                    $result                     = $this->UpdateRecordLoc('inventory_adjustments', $db_record, $where);                    //$this->EchoQuery(true);                            } // end price match check                            } // end status_use_assembly_pricing check                    } // end foreach        } else {            echo "<h2>NO REOCRDS TO MODIFY</h2>";        } // end record check                        /*        if (!$passed) {            echo "<h2>QUERY FAILED - NOT PASSED</h2>";        }        */                $passed = false;        # ===== COMMIT TRANSACTION ============================================================        if ($passed) {            $this->SQL->TransactionCommit();        }    }          public function GetAllRecords()    {        /*        $records = $this->SQL->GetArrayAll(array(            'table' => 'inventory_counts',            'keys'  => 'inventory_counts.*, inventory_products.part_cost, inventory_adjustments.inventory_adjustments_id, inventory_adjustments.quantity, inventory_products.retailer_code, inventory_products.description',            'where' => "inventory_adjustments.price_each=0 && inventory_adjustments.price_total=0 AND status_allow_zerocost=0 AND inventory_counts.active=1",            'joins' => "                LEFT JOIN inventory_products ON inventory_products.barcode = inventory_counts.barcode                LEFT JOIN inventory_adjustments ON inventory_adjustments.inventory_adjustments_id = inventory_counts.ref_adjustment_id            ",        ));        */                $records = $this->SQL->GetArrayAll(array(            'table' => 'inventory_adjustments',            'keys'  => 'inventory_adjustments.*, inventory_products.part_cost, inventory_products.retailer_code, inventory_products.description, inventory_products.status_use_assembly_pricing, inventory_products.inventory_assemblies_id',            'where' => "inventory_adjustments.active=1",            'joins' => "                LEFT JOIN inventory_products ON inventory_products.barcode = inventory_adjustments.barcode            ",        ));                if ($records) {            $this->EchoQuery();            return $records;        } else {            echo "<h2>ERROR IN QUERY</h2>";            $this->EchoQuery(true);        }    }      }  // -------------- END CLASS --------------