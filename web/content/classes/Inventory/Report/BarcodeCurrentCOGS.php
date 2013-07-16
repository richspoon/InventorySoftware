<?phpclass Inventory_Report_BarcodeCurrentCOGS extends Inventory_InventoryBase{    public $Show_Query                  = false;            public function  __construct()    {        parent::__construct();        $this->SetSQLInventory();   // set the database connection to the inventory database                $this->Classname = get_class($this);        $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2012-11-30',            'Updated By'    => 'Richard Witherspoon',            'Updated Date'  => '2013-01-14',            'Filename'      => $this->Classname,            'Version'       => '1.1',            'Description'   => 'Get the current cost of given barcodes',            'Update Log'    => array(                '2012-11-30_1.0'    => "Module Created",                '2012-12-10_1.1'    => "Renamed from InventoryReportCurrentCost to ReportBarcodeCurrentCOGS",            ),        );            } // -------------- END __construct --------------        public function Execute()    {        //$_GET['sales_order_list'] = "666, 1256";                AddStylesheet("/css/inventory.css??20121108-1");                echo "This report returns the current value for any given barcode. This is the COGS value as of today.        <br /><br />";                $this->OutputForm();        $bc_array   = $this->GetBarcodes();        $output     = $this->CreateTableFromArray($bc_array);                echo "<br /><br />" . $output;    }        public function ExecuteAjax()    {        $QDATA = GetEncryptQuery('eq');        $action = Get('action');        $return = 0;                switch ($action) {            default:            break;        }                echo $return;    }        public function OutputForm()    {        $onclick 	        = ''; //"submitSalesOrderNumbers('list')";        $id 		        = 'btn_barcode';        $name 		        = 'btn_barcode';        $btn_submit         = MakeButton('positive', 'Submit Barcodes', '', '', $id, $onclick, 'submit', $name);                       // ----- output the form        // ---- repopulate the form        //$link = $this->getPageURL();        $link   = Server('SCRIPT_URI');        $value  = Post('list');        $output = '        <form action="'.$link.'" method="post" accept-charset="utf-8" id="db_edit_form" name="db_edit_form">        <div class="shadow" style="border:1px dashed blue; padding:5px; background-color:#efefef;">            <br class="formtitlebreak"><div class="formtitle">Barcodes:</div>            <div class="forminfo">                Seperate each barcodes with a comma!                <br />                <input autocomplete="off" alt="Search" id="list" class="formitem ui-autocomplete-input" name="list" size="60" maxlength="255" value="'.$value.'" type="text"><span class="ui-helper-hidden-accessible" aria-live="polite" role="status"></span>                <br /><br />                <div>                '.$btn_submit.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;                                </div>                <div style="clear:both;"></div>            </div>        </div>        </form>';        echo $output;                        /*        // ----- javascript for submitting the form        //$link = $this->getPageURL();        $CLASS_EXECUTE_LINK_AJAX    = '/office/AJAX/class_execute';        $eq                         = EncryptQuery("class=Inventory_InventoryReportCurrentCost;");        $link                       = $CLASS_EXECUTE_LINK_AJAX . '?eq=' . $eq;                $script = <<<SCRIPT            function getOpenSalesOrders(returnID)            {                // CALL THIS CLASS VIA AJAX TO GET BARCODE                $.get( '{$link};action=barcodevalue', '', function(data){                    $('#' + returnID).val(data);                });            }SCRIPT;        AddScript($script);        */    }        public function GetBarcodes()    {        $bc_array       = array();    // ----- will hold all the sales orders and its barcodes        $bc_list        = Post('list');                if ($bc_list) {            $barcodes = explode(',', $bc_list);                        // ----- loop through each SO and get the barcodes for it            foreach ($barcodes AS $barcode) {                                $barcode = trim($barcode);                                if ($barcode) {                    $bc_array[] = $barcode;                }            }        }                return $bc_array;    }        public function CreateTableFromArray($BC_ARR)    {        $output             = '';        $total_last_cost    = 0;                if ($BC_ARR) {                    // ---------- HEADER ----------            $output .= '<table border=1 id="jsonTable" border="1" cellpadding="1" cellspacing="1">';            $output .= "<tr>                        <th>Barcode</th>                        <th>Details</th>                        <th>Last Item Cost</th>                        <th>QTY Available</th>                        <th>QTY Available Value</th>                        </tr>";                                                // ----- loop though total array to get all barcodes            foreach ($BC_ARR AS $barcode) {                                $details                = $this->GetInventoryItemDetailsFromBarcode($barcode);                $qty_available          = $this->InventoryItemQuantityAvailable($barcode);                $qty_available_value    = $this->CalculateInventoryValue($barcode);                                #$this->EchoVar('qty_available_value', $qty_available_value);                                $last_cost              = intval($this->InventoryItemLastCost($barcode));                $item_last_cost         = money_format("%n", $last_cost);                                $total_last_cost        += $last_cost;                                // ---------- DATA ----------                $output .= "<tr>                        <th>{$barcode}</th>                        <th>{$details}</th>                        <th>{$item_last_cost}</th>                        <th>{$qty_available}</th>                        <th>{$qty_available_value}</th>                        </tr>";            }                        // ---------- CLOSE TABLE ----------            $total_last_cost = money_format("%n", $total_last_cost);            $output .= "<tr>                        <th></th>                        <th></th>                        <th><b>{$total_last_cost}</b></th>                        <th></th>                        <th></th>                        </tr>";            $output .= "</table>";                                } else {            $output = "<center><h2>NO BARCODES SUBMITTED</h2></center>";        } // end if                return $output;    }        }  // -------------- END CLASS --------------