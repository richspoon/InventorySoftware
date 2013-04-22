<?php

# NOTE :: Must have the DIALOGID passed in all of the menu links for table won't refresh
$DIALOGID = Get('DIALOGID');

$link = "/office/inventory/inventory_reports;DIALOGID={$DIALOGID}";

$OBJ_MENU = new Inventory_DropdownMenu();
$OBJ_MENU->Menu_Multi = array();


$OBJ_MENU->Menu_Multi['General'] = array(
    array(  'link'  => "{$link};t=1",
            'title' => "Sales Order Inventory Available", ),
    array(  'link'  => "{$link};t=3",
            'title' => "Sales Order COGS", ),
    array(  'link'  => "{$link};t=2",
            'title' => "Barcode Current FIFO Value", ),
    array(  'link'  => "{$link};t=6",
            'title' => "MANUFACTURING RESOURCE PLANNING", ),
    array(  'link'  => "{$link};t=7",
            'title' => "Inventory Value Report", ),
    array(  'link'  => "{$link};t=9",
            'title' => "Database Integrity Checks", ),
);


$OBJ_MENU->Menu_Multi['Fix'] = array(
    array(  'link'  => "{$link};t=13",
            'title' => "SCRIPT - inventory adjustments - convert to average valuation", ),
    array(  'link'  => "{$link};t=4",
            'title' => "fix inventory counts", ),
    array(  'link'  => "{$link};t=5",
            'title' => "fix $0 inventory adjustments", ),
    array(  'link'  => "{$link};t=8",
            'title' => "fix no date inventory counts", ),
    array(  'link'  => "{$link};t=10",
            'title' => "fix inventory adjustments price", ),
    array(  'link'  => "{$link};t=11",
            'title' => "fix date mis-match in inventory_counts", ),
    array(  'link'  => "{$link};t=12",
            'title' => "fix inventory build records without build record", ),
    array(  'link'  => "{$link};t=208",
            'title' => "fix POs - map to ID", ),
    array(  'link'  => "{$link};t=209",
            'title' => "fix POs - wrongly deactivated records", ),
	array(  'link'  => "{$link};t=210",
            'title' => "fix SOs - map to ID", ),
);

$OBJ_MENU->Menu_Multi['Test'] = array(
    array(  'link'  => "{$link};t=301",
            'title' => "Inventory_Valuation_InventoryAssemblyCalculateValue", ),
    array(  'link'  => "{$link};t=302",
            'title' => "Inventory_Valuation_Adjustment", ),
    array(  'link'  => "{$link};t=303",
            'title' => "Inventory_Valuation_Handler", ),
    array(  'link'  => "{$link};t=304",
            'title' => "Inventory_EconomicOrderQuantity", ),
    array(  'link'  => "{$link};t=305",
            'title' => "Inventory_Valuation_SingleItemCostCalculation", ),
    array(  'link'  => "{$link};t=306",
            'title' => "Inventory_Valuation_ValueAdjustment", ),
    array(  'link'  => "{$link};t=307",
            'title' => "Inventory_PHPWord_PHPWord", ),
);


echo $OBJ_MENU->Execute();
echo "<div style='min-width:600px; min-height:300px;'>";


switch(Get('t')) {
    case '1':
        echo "<h1>REPORT :: Sales Order Inventory Available</h1>";
        echo "<br />Class :: Inventory_ReportSalesOrderInventoryAvailable()<br />";
        $OBJ = new Inventory_ReportSalesOrderInventoryAvailable();
        $OBJ->Execute();
    break;
    case '2':
        echo "<h1>REPORT :: Barcode Current FIFO Value</h1>";
        echo "<br />Class :: Inventory_ReportBarcodeCurrentCOGS()<br />";
        $OBJ = new Inventory_ReportBarcodeCurrentCOGS();
        $OBJ->Execute();
    break;
    case '3':
        echo "<h1>REPORT :: Sales Orders - COGS</h1>";
        echo "<br />Class :: Inventory_ReportSalesOrderCOGS()<br />";
        $OBJ = new Inventory_ReportSalesOrderCOGS();
        $OBJ->Execute();
    break;
    
    case '6':
        echo "<h1>MANUFACTURING RESOURCE PLANNING</h1>";
        echo "<br />Class :: Inventory_ManufacturingResourcePlanning()<br />";
        $OBJ = new Inventory_ManufacturingResourcePlanning();
        $OBJ->Execute();
    break;
    
    case '7':
        echo "<h1>REPORT :: Inventory Value at Given Date</h1>";
        echo "<br />Class :: Inventory_ReportInventoryDatedCOGS()<br />";
        $OBJ = new Inventory_ReportInventoryDatedCOGS();
        $OBJ->Execute();
    break;
    
    case '8':
        echo "<h1>SCRIPT TO FIX ERRORS</h1>";
        echo "<br />Class :: Inventory_FixInventoryCountsNoDate()<br />";
        $OBJ = new Inventory_Fix_InventoryCountsNoDate();
        $OBJ->Execute();
    break;
    
    case '9':
        echo "<h1>DATABASE INTEGRITY CHECKS</h1>";
        echo "<br />Class :: Inventory_ReportDatabaseIntegrityCheck()<br />";
        $OBJ = new Inventory_ReportDatabaseIntegrityCheck();
        $OBJ->Execute();
    break;
    
    
    
    
    
    # ===================================================================
    # ========  FIX MENU  ========
    # ===================================================================
    
    case '10':
        echo "<h1>FIX :: INVENTORY ADJUSTMENT - ASSEMBLY PRICE</h1>";
        echo "<br />Class :: Inventory_FixInventoryAdjustmentAssemblyPrice()<br />";
        $OBJ = new Inventory_Fix_InventoryAdjustmentAssemblyPrice();
        $OBJ->Execute();
    break;
    
    case '11':
        echo "<h1>FIX :: INVENTORY COUNT - MIS-MATCHED DATE</h1>";
        echo "<br />Class :: Inventory_FixInventoryDateMismatch()<br />";
        $OBJ = new Inventory_Fix_InventoryDateMismatch();
        $OBJ->Execute();
    break;
    
    case '12':
        echo "<h1>FIX :: INVENTORY BUILD RECORD - NO BUILD RECORD FOUND</h1>";
        echo "<br />Class :: Inventory_FixAssemblyNoBuildRecord()<br />";
        $OBJ = new Inventory_Fix_AssemblyNoBuildRecord();
        $OBJ->Execute();
    break;
    
    case '13':
        echo "<h1>FIX :: INVENTORY ADJUSTMENT - AVERAGE VALUE METHOD</h1>";
        echo "<br />Class :: Inventory_FixInventoryAdjustmentValue()<br />";
        $OBJ = new Inventory_Fix_InventoryAdjustmentValue();
        $OBJ->Execute();
    break;
    
    case '4':
        echo "<h1>SCRIPT TO FIX ERRORS</h1>";
        echo "<br />Class :: Inventory_FixOriginalScan()<br />";
        $OBJ = new Inventory_Fix_OriginalScan();
        $OBJ->Execute();
    break;
    
    case '5':
        echo "<h1>SCRIPT TO FIX ERRORS</h1>";
        echo "<br />Class :: Inventory_FixZeroDollarInventoryAdjustment()<br />";
        $OBJ = new Inventory_Fix_ZeroDollarInventoryAdjustment();
        $OBJ->Execute();
    break;
    
    case '208':
        echo "<h1>SCRIPT TO FIX ERRORS</h1>";
        echo "<br />Class :: Inventory_Fix_PONumberToId()<br />";
        $OBJ = new Inventory_Fix_PONumberToId();
        $OBJ->Execute();
    break;
    
    case '209':
        echo "<h1>SCRIPT TO FIX ERRORS</h1>";
        echo "<br />Class :: Inventory_Fix_PODeactivatedLinesAcivate()<br />";
        $OBJ = new Inventory_Fix_PODeactivatedLinesAcivate();
        $OBJ->Execute();
    break;
	
	case '210':
        echo "<h1>SCRIPT TO FIX ERRORS</h1>";
        echo "<br />Class :: Inventory_Fix_SONumberToId()<br />";
        $OBJ = new Inventory_Fix_SONumberToId();
        $OBJ->Execute();
    break;
    
    
    
    
    
    
    
    # ===================================================================
    # ========  TEST MENU  ========
    # ===================================================================
    
    
    case '301':
        echo "<h1>TEST OF Inventory_Valuation_InventoryAssemblyCalculateValue - URL NEEDS ;id=133;barcode=10053</h1>";
        echo "<br />Class :: Inventory_Valuation_InventoryAssemblyCalculateValue()<br />";
        $OBJ            = new Inventory_Valuation_InventoryAssemblyCalculateValue();
        $OBJ->Barcode   = Get('barcode');
        $OBJ->Execute();
    break;
    
    case '302':
        echo "<h1>TEST OF Inventory_Valuation_Adjustment - URL NEEDS ;id=XXX</h1>";
        echo "<br />Class :: Inventory_Valuation_Adjustment()<br />";
        $OBJ = new Inventory_Valuation_Adjustment();
        $OBJ->Inventory_Adjustments_ID = Get('id');
        $OBJ->Quantity = 1;
        $OBJ->Execute();
        
        $OBJ->EchoVar('');
        $OBJ->EchoVar('COGS_Single', $OBJ->COGS_Single);
        $OBJ->EchoVar('COGS_Total', $OBJ->COGS_Total);
        $OBJ->EchoVar('COGS_Array', $OBJ->COGS_Array);
    break;
    
    case '303':
        echo "<h1>TEST OF Inventory_Valuation_Handler - URL NEEDS ;id=XXX</h1>";
        echo "<br />Class :: Inventory_Valuation_Handler()<br />";
        $OBJ = new Inventory_Valuation_Handler();
        $OBJ->Inventory_Counts_ID = Get('id');
        $OBJ->Quantity = 1;
        $OBJ->Execute();
        
        $OBJ->EchoVar('');
        $OBJ->EchoVar('COGS_Single', $OBJ->COGS_Single);
        $OBJ->EchoVar('COGS_Total', $OBJ->COGS_Total);
        $OBJ->EchoVar('COGS_Array', $OBJ->COGS_Array);
    break;
    
    case '304':
        echo "<h1>TEST OF Inventory_EconomicOrderQuantity - URL NEEDS ;id=XXX</h1>";
        echo "<br />Class :: Inventory_EconomicOrderQuantity()<br />";
        $OBJ = new Inventory_EconomicOrderQuantity();
        
        $OBJ->Demand_Rate_Annual          = 0;
        $OBJ->Order_Cost                  = 0;
        $OBJ->Holding_Cost_Dollar         = 0;
        $OBJ->Holding_Cost_Percentage     = 0;
        $OBJ->Unit_Price                  = 0;
        $OBJ->Daily_Demand_Rate           = 0;
        $OBJ->Lead_Time_Days              = 0;
        
        $OBJ->Execute();
    break;
    
    case '305':
        echo "<h1>TEST OF Inventory_Valuation_SingleItemCostCalculation - URL NEEDS ;barcode=XXX   OPTIONAL => ;date=yyyy-mm-dd;method=xxx</h1>";
        echo "<br />Class :: Inventory_Valuation_SingleItemCostCalculation<br />";
        $OBJ = new Inventory_Valuation_SingleItemCostCalculation();
        
        $OBJ->Pseudocode();                     // echo out the pseudocode for this class
        
        $OBJ->Barcode = Get('barcode');
        $OBJ->Execute();
        
        $OBJ->Test_ShowOutputs();
        $OBJ->DumpNotices();
        $OBJ->DumpErrors();
        
    break;
    
    case '306':
        echo "<h1>TEST OF Inventory_Valuation_ValueAdjustment - URL NEEDS ;aid=xxxx (168) </h1>";
        echo "<br />Class :: Inventory_Valuation_ValueAdjustment<br />";
        $OBJ = new Inventory_Valuation_ValueAdjustment();
        
        #$OBJ->Pseudocode();                     // echo out the pseudocode for this class
        
        $OBJ->Inventory_Adjustments_ID = Get('aid');
        $OBJ->Execute();
        
        $OBJ->EchoVar('Value_Total', $OBJ->Value_Total);
        $OBJ->EchoVar('Value_Each', $OBJ->Value_Each);
        #$OBJ->EchoVar('Value_Array', $OBJ->Value_Array);
        
        
        $Obj_Report                 = new Inventory_Valuation_ValueSummaryReport();         // instantiate value report
        $Obj_Report->Value_Array    = $OBJ->Value_Array;                                    // pass in array to turn into report
        $report                     = $Obj_Report->Execute();                               // create the report
        echo $report;                                                                       // echo out the report
        
        
        #$OBJ->Test_ShowOutputs();
        $OBJ->DumpNotices();
        $OBJ->DumpErrors();
        
    break;
    
    case '307':
        echo "<h1>TEST OF PHPWord_PHPWord</h1>";
        echo "<br />Class :: PHPWord_PHPWord<br />";
        
        
        
        
        $template_path  = "{$ROOT}/document_templates/";
        $save_path      = "{$ROOT}/document_output/";
        //$template_file  = "rma_request_master.docx";
        $template_file  = "Template.docx";
        //$template_file  = "item.docx";
        
        $id             = date("YmdHis");
        $save_file      = "APDM_RMA_{$id}.docx";
        
        
        $PHPWord        = new PHPWord_PHPWord();
        $document       = $PHPWord->loadTemplate($template_path . $template_file );
        
        
        if (!$document) {
            echo "ERROR :: Document Not Found :: {$template}";
        } else {
            
            echo "_documentXML ===> " . $document->_documentXML; 
            
            $document->setValue('Value1', 'Sun');
            $document->setValue('Value2', 'Mercury');
            $document->setValue('Value3', 'Venus');
            $document->setValue('Value4', 'Earth');
            $document->setValue('Value5', 'Mars');
            $document->setValue('Value6', 'Jupiter');
            $document->setValue('Value7', 'Saturn');
            $document->setValue('Value8', 'Uranus');
            $document->setValue('Value9', 'Neptun');
            $document->setValue('Value10', 'Pluto');
            $document->setValue('weekday', date('l'));
            $document->setValue('time', date('H:i'));
            
            
            $document->setValue('vendor_rma', $id);
            $document->setValue('date_request', date("Y-m-d"));
            $document->setValue('date_ship', date("Y-m-d"));
            $document->setValue('date_return_request', 'N/A');
            $document->setValue('item_name', 'Monitor Case Bottom');
            $document->setValue('item_barcode', '10061');
            $document->setValue('item_sku', 'Monitor_case_bottom_unmarked');
            $document->setValue('item_quantity', '6');
            $document->setValue('notes_to_vendor', 'Notes to Vendor: Please notify if you want to send out replacements or just decrease the number received by us for invoicing. It is NOT necessary to replace with new units at this time.');
            
            
            $document->setValue('vendor-rma', $id);
            $document->setValue('date-request', date("Y-m-d"));
            $document->setValue('date-ship', date("Y-m-d"));
            $document->setValue('date-return-request', 'N/A');
            $document->setValue('item-name', 'Monitor Case Bottom');
            $document->setValue('item-barcode', '10061');
            $document->setValue('item-sku', 'Monitor_case_bottom_unmarked');
            $document->setValue('item-quantity', '6');
            $document->setValue('notes-to-vendor', 'Notes to Vendor: Please notify if you want to send out replacements or just decrease the number received by us for invoicing. It is NOT necessary to replace with new units at this time.');
            
            
            $document->setValue('vendorrma', $id);
            $document->setValue('daterequest', date("Y-m-d"));
            $document->setValue('dateship', date("Y-m-d"));
            $document->setValue('datereturnrequest', 'N/A');
            $document->setValue('itemname', 'Monitor Case Bottom');
            $document->setValue('itembarcode', '10061');
            $document->setValue('itemsku', 'Monitor_case_bottom_unmarked');
            $document->setValue('itemquantity', '6');
            $document->setValue('notestovendor', 'Notes to Vendor: Please notify if you want to send out replacements or just decrease the number received by us for invoicing. It is NOT necessary to replace with new units at this time.');
            
            
            /*
            $apdm_contact = "Any questions contact: \n Richard Witherspoon \n Richard@apdm.com \n 503-320-7730.";
            $document->setValue('apdm_contact', $apdm_contact);
            
            $ship_address_apdm = "APDM, Inc. \n 2828 Southwest Corbett Avenue \n Suite 130 \n Portland, OR 97201 USA";
            $document->setValue('apdm_contact', $ship_address_apdm);
            
            $ship_address_vendor = "A.R.E. Manufacturing, Inc. \n 518 S. Springbook Rd \n Newberg, Oregon 97132 \n Phone: (503) 538-0350 \n Fax: (503) 538-5148";
            $document->setValue('apdm_contact', $ship_address_vendor);
            */
            
            
            /* ===== NOTES ===========
            Newline character -- http://phpword.codeplex.com/discussions/249089
            */
            
            
            $document->save($save_path . $save_file);
            
            echo "
            </br></br>
            <div style='border:5px solid blue; padding:10px; margin:20px;'>
            _documentXML ===> {$document->_documentXML} 
            </div>";
            
            echo "
            </br></br>
            <div style='border:5px solid blue; padding:10px; margin:20px;'>
            File Saved: {$save_file}</br>
            <a href='http://webmanager.whhub.com/document_output/{$save_file}' target='_file'>Download File</a>
            </div>
            ";
        }
    break;
    
}

echo "</div>";

?>
