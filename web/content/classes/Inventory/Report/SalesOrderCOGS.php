<?phpclass Inventory_Report_SalesOrderCOGS extends Inventory_InventoryBase{    public $Show_Query                  = false;            public function  __construct()    {        parent::__construct();        $this->SetSQLInventory();   // set the database connection to the inventory database                $this->Classname = get_class($this);        $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2012-12-02',            'Updated By'    => 'Richard Witherspoon',            'Updated Date'  => '2013-10-07',            'Filename'      => $this->Classname,            'Version'       => '1.2',            'Description'   => 'Get the cost of goods sold for given sales orders',            'Update Log'    => array(                '2012-12-02_1.0'    => "Module Created",                '2012-12-10_1.1'    => "Renamed from InventoryReportCOGS to ReportSalesOrderCOGS",                '2013-10-07_1.2'    => "Search by date capabilities",            ),        );            } // -------------- END __construct --------------        public function Execute()    {        //$_GET['sales_order_list'] = "666, 1256";                AddStylesheet("/css/inventory.css??20121108-1");                // ----- Javascript for TABS        AddScriptOnReady('            var tabs = $( "#tabs" ).tabs();            tabs.find( ".ui-tabs-nav" ).sortable({                axis: "x",                stop: function() {                    tabs.tabs( "refresh" );                }            });        ');                echo "This report returns the cost of goods sold for any given sales order UID or date range. You can combine dates and UID to get COGS outside the date range.        <br /><br />";                $this->OutputForm();        $so_array   = $this->GetSalesOrders();        $output     = $this->CalculateGOGSSalesOrders($so_array);                echo "<br /><br />" . $output;    }        public function ExecuteAjax()    {        $QDATA = GetEncryptQuery('eq');        $action = Get('action');        $return = 0;                switch ($action) {                        case 'closedsalesorders':                            // ----- get all sales order numbers                $records = $this->SQL->GetArrayAll(array(                    'table' => "inventory_sales_orders",                    'keys'  => "so_number",                    'where' => "active=1",                    'order' => "date ASC",                ));                $this->EchoQuery();                                if ($records) {                    // ----- check status of each sales order                    foreach ($records AS $record) {                        $so_number  = $record['so_number'];                        $status     = $this->StatusSalesOrder($so_number);                                                if ($status == 'closed') {                            $return .= ", {$so_number}";                        }                    }                }            break;                        case 'opensalesorders':                            // ----- get all sales order numbers                $records = $this->SQL->GetArrayAll(array(                    'table' => "inventory_sales_orders",                    'keys'  => "so_number",                    'where' => "active=1",                    'order' => "date ASC",                ));                $this->EchoQuery();                                if ($records) {                    // ----- check status of each sales order                    foreach ($records AS $record) {                        $so_number  = $record['so_number'];                        $status     = $this->StatusSalesOrder($so_number);                        if ($status != 'closed') {                            $return .= ", {$so_number}";                        }                    }                }            break;        }                echo $return;    }        public function OutputForm()    {        // ----- Javascript Functionality -----        $this->JavascriptDatepickerFunctionality(array('date_start', 'date_end'));                        $onclick 	        = ''; //"submitSalesOrderNumbers('sales_order_list')";        $btn_submit         = MakeButton('positive', 'Submit Sales Orders', '', '', 'btn_barcode_1', $onclick, 'submit', 'btn_barcode_1');                        $onclick 	        = "getClosedSalesOrders('sales_order_list')";        $btn_closed         = MakeButton('positive', 'Get All CLOSED Orders', '', '', 'btn_barcode_2', $onclick, 'button', 'btn_barcode_2');                $onclick 	        = "getOpenSalesOrders('sales_order_list')";        $btn_open           = MakeButton('positive', 'Get All OPEN Orders', '', '', 'btn_barcode_3', $onclick, 'button', 'btn_barcode_3');                        // ----- output the form        // ---- repopulate the form        //$link = $this->getPageURL();        $link                   = Server('SCRIPT_URI');        $value                  = Post('sales_order_list');        $value_date_start       = Post('date_start');        $value_date_end         = Post('date_end');        $checked_show_lines     = (Post('show_lines')) ? "checked" : '';                        $output = '        <form action="'.$link.'" method="post" accept-charset="utf-8" id="db_edit_form" name="db_edit_form">        <div class="shadow" style="border:1px dashed blue; padding:5px; background-color:#efefef;">                                                <br class="formtitlebreak">            <div class="formtitle">Start Shipping Date:</div>            <div class="forminfo">                <input autocomplete="off" alt="Search" id="date_start" class="formitem" name="date_start" size="20" maxlength="255" value="'.$value_date_start.'" type="text">            </div>            <div style="clear:both;"></div>                                    <br class="formtitlebreak">            <div class="formtitle">End Shipping Date:</div>            <div class="forminfo">                <input autocomplete="off" alt="Search" id="date_end" class="formitem" name="date_end" size="20" maxlength="255" value="'.$value_date_end.'" type="text">            </div>            <div style="clear:both;"></div>                                                <br class="formtitlebreak">            <div class="formtitle">Sales Orders UID:</div>            Seperate each sales order UID with a comma!            <br />            <input autocomplete="off" alt="Search" id="sales_order_list" class="formitem ui-autocomplete-input" name="sales_order_list" size="60" maxlength="255" value="'.$value.'" type="text"><span class="ui-helper-hidden-accessible" aria-live="polite" role="status"></span>            <div style="clear:both;"></div>            <br /><br />                                                            <br class="formtitlebreak">            <div class="formtitle">Show Lines:</div>            <div class="forminfo">                <input id="show_lines" class="formitem" name="show_lines" value="1" type="checkbox" '.$checked_show_lines.'>            </div>            <div style="clear:both;"></div>                                    <div>                                <table>                <tr>                <td valign="top" width="100%">'.$btn_submit.'</td>                <td valign="top"></td>                </tr>                </table>                            </div>                                                        </div>        </form>';        echo $output;                        /*        <div class="forminfo" style="display:none;">                                                <table>                <tr>                <td valign="top" width="100%">'.$btn_submit.'</td>                <td valign="top">'.$btn_open.'<br />'.$btn_closed.'</td>                </tr>                </table>                                </div>                <div style="clear:both;"></div>            </div>        */                        // ----- javascript for submitting the form        //$link = $this->getPageURL();        $CLASS_EXECUTE_LINK_AJAX    = '/office/AJAX/class_execute';        $eq                         = EncryptQuery("class={$this->Classname};");        $link                       = $CLASS_EXECUTE_LINK_AJAX . '?eq=' . $eq;                $script = <<<SCRIPT            function getOpenSalesOrders(returnID)            {                // CALL THIS CLASS VIA AJAX TO GET BARCODE                $.get( '{$link};action=opensalesorders', '', function(data){                    $('#' + returnID).val(data);                });            }                        function getClosedSalesOrders(returnID)            {                // CALL THIS CLASS VIA AJAX TO GET BARCODE                $.get( '{$link};action=closedsalesorders', '', function(data){                    $('#' + returnID).val(data);                });            }SCRIPT;        AddScript($script);            }        public function GetSalesOrders()    {        $so_array           = array();    // ----- will hold all the sales orders and its barcodes        $so_list            = Post('sales_order_list');        $value_date_start   = Post('date_start');        $value_date_end     = Post('date_end');                        // GET SOs FROM DATE SHIPPED RANGE                if ($value_date_start && $value_date_end) {            $records = $this->SQL->GetArrayAll(array(                'table' => 'inventory_sales_orders',                'keys'  => 'inventory_sales_orders_id, universal_id, order_type, customer, date_ship_actual',                'where' => "date_ship_actual >= '{$value_date_start}' AND date_ship_actual <= '{$value_date_end}' AND active=1",            ));            //$this->SQL->EchoQuery();            //$output = $this->ArrayToTable($records);                        // store each record with its inventory_sales_orders_id as the index (allows de-duplication)            $records_formatted = array();            foreach ($records as $record) {                $records_formatted[$record['inventory_sales_orders_id']] = $record;            }            $so_array = $so_array + $records_formatted;       // merge records into array of all sales orders        }                                if ($so_list) {            $so_uid_list    = "";            $sales_orders   = explode(',', $so_list);                        // ----- loop through each SO and get the barcodes for it            foreach ($sales_orders AS $sales_order) {                                $sales_order = trim($sales_order);                $so_uid_list .= ($sales_order) ? "'{$sales_order}'," : "";                            }            $so_uid_list = substr($so_uid_list, 0, -1);                         // trim off trailing comma                        if ($so_uid_list) {                $records = $this->SQL->GetArrayAll(array(                    'table' => 'inventory_sales_orders',                    'keys'  => 'inventory_sales_orders_id, universal_id, order_type, customer, date_ship_actual',                    'where' => "universal_id IN ({$so_uid_list}) AND active=1",                ));                //$this->SQL->EchoQuery();                //$output = $this->ArrayToTable($records);                                // store each record with its inventory_sales_orders_id as the index (allows de-duplication)                $records_formatted = array();                foreach ($records as $record) {                    $records_formatted[$record['inventory_sales_orders_id']] = $record;                }                $so_array = $so_array + $records_formatted;       // merge records into array of all sales orders            }        }                return $so_array;            }        public function CalculateGOGSSalesOrders($so_array)    {        $output             = "";        $value_show_lines   = Post('show_lines');        $summary_arr        = array();        $out_arr            = array();        $out_arr_count      = 0;                        foreach ($so_array as $id => $record) {            $isoid = $record['inventory_sales_orders_id'];                        $Obj_COGS = new Inventory_Valuation_SalesOrderCalculateCOGS($isoid);            $Obj_COGS->Execute(true);   // true makes it return instead of echo - we don't want input on screen                        $value_sent     = $Obj_COGS->COGS_Calculated_Sent_Inventory;            $value_unsent   = $Obj_COGS->COGS_Calculated_Unsent_Inventory;            $arr_sent       = $Obj_COGS->COGS_Calculated_Sent_Inventory_Arr;            $arr_unsent     = $Obj_COGS->COGS_Calculated_Unsent_Inventory_Arr;                        $summary_arr[] = array(                'id'            => $isoid,                'UID'           => $record['universal_id'],                'Order_Type'    => $record['order_type'],                'Customer'      => $record['customer'],                'Ship_Date'     => $record['date_ship_actual'],                'COGS_sent'     => money_format('%n', $value_sent),                'COGS_unsent'   => money_format('%n', $value_unsent),                'COGS_total'    => money_format('%n', ($value_sent + $value_unsent)),            );                        if ($value_show_lines) {                foreach ($arr_sent AS $record_sent) {                                    $out_arr[$out_arr_count]['id']              = $isoid;                    $out_arr[$out_arr_count]['UID']             = $record['universal_id'];                    $out_arr[$out_arr_count]['Order_Type']      = $record['order_type'];                    $out_arr[$out_arr_count]['Customer']        = $record['customer'];                    $out_arr[$out_arr_count]['Ship_Date']       = $record['date_ship_actual'];                    $out_arr[$out_arr_count]['COGS_total']      = money_format('%n', ($value_sent + $value_unsent));                                        $out_arr[$out_arr_count]['sent_status']     = 'SENT';                    $out_arr[$out_arr_count]['barcode']         = $record_sent['barcode'];                    $out_arr[$out_arr_count]['description']     = $record_sent['description'];                    $out_arr[$out_arr_count]['line_quantity']   = $record_sent['line_quantity'];                    $out_arr[$out_arr_count]['cogs_each']       = $record_sent['cogs_each'];                    $out_arr[$out_arr_count]['cogs_total']      = $record_sent['cogs_total'];                    //$out_arr[$out_arr_count]['date']            = $record_sent['date'];                    $out_arr[$out_arr_count]['info']            = $record_sent['info'];                    //$out_arr[$out_arr_count]['assembly']        = $record_sent['assembly'];                                        $out_arr_count++;                }                                foreach ($arr_unsent AS $record_unsent) {                                        $out_arr[$out_arr_count]['id']              = $isoid;                    $out_arr[$out_arr_count]['UID']             = $record['universal_id'];                    $out_arr[$out_arr_count]['Order_Type']      = $record['order_type'];                    $out_arr[$out_arr_count]['Customer']        = $record['customer'];                    $out_arr[$out_arr_count]['Ship_Date']       = $record['date_ship_actual'];                    $out_arr[$out_arr_count]['COGS_total']      = money_format('%n', ($value_sent + $value_unsent));                                        $out_arr[$out_arr_count]['sent_status']     = 'UNSENT';                    $out_arr[$out_arr_count]['barcode']         = $record_unsent['barcode'];                    $out_arr[$out_arr_count]['description']     = $record_unsent['description'];                    $out_arr[$out_arr_count]['line_quantity']   = $record_unsent['line_quantity'];                    $out_arr[$out_arr_count]['cogs_each']       = $record_unsent['cogs_each'];                    $out_arr[$out_arr_count]['cogs_total']      = $record_unsent['cogs_total'];                    //$out_arr[$out_arr_count]['date']            = $record_unsent['date'];                    $out_arr[$out_arr_count]['info']            = $record_unsent['info'];                    //$out_arr[$out_arr_count]['assembly']        = $record_unsent['assembly'];                                        $out_arr_count++;                }            }                        unset($Obj_COGS);                    }                $output .= "</br></br></br>";        $output .= "<div class='jsonTable'>";        $output .= $this->ConvertArrayToTable($summary_arr);                if ($value_show_lines) {            $output .= "</br></br>";            $output .= $this->ConvertArrayToTable($out_arr);        }                $output .= "</div>";                return $output;    }        public function CreateTableFromArray($SO_ARR)    {        $output             = '';        $total_last_cost    = 0;                if ($SO_ARR) {                        $tab_tabs       = '';            $tab_content    = '';            $tab_count      = 0;                        foreach ($SO_ARR as $id => $so_number) {                                $obj = new Inventory_InventorySalesOrderCalculateCOGS();                $obj->SO_Number = $so_number;                $output = $obj->Execute(true);                                $tab_count++;                $tab_tabs       .= "<li><a href='#tabs-{$tab_count}'>{$so_number}</a></li>";                $tab_content    .= "<div id='tabs-{$tab_count}'>{$output}</div>";            }                                    $output = "                <div id='tabs'>                    <ul>                        {$tab_tabs}                    </ul>                    {$tab_content}                </div>";                                } else {            $output = "<center><h2>NO SALES ORDERS SUBMITTED</h2></center>";        } // end if                return $output;    }            public function ArrayToTable($ARR)    {        $row_1 = "";        $row_2 = "";                foreach ($ARR as $key => $val) {                        if (is_array($val)) {                $val = $this->ArrayToTable($val);            }                        $row_1 .= "<th>{$key}</th>";            $row_2 .= "<td>{$val}</td>";        }                $output = "            <table class='vr_assy_table' width='100%'>            <tr>{$row_1}</tr>            <tr>{$row_2}</tr>            </table>            ";                return $output;    }        }  // -------------- END CLASS --------------