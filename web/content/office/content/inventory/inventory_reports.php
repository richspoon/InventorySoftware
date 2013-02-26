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
);

$OBJ_MENU->Menu_Multi['Test'] = array(
    array(  'link'  => "{$link};t=301",
            'title' => "Inventory_InventoryAssemblyCalculateValue", ),
    array(  'link'  => "{$link};t=302",
            'title' => "Inventory_Valuation_Adjustment", ),
    array(  'link'  => "{$link};t=303",
            'title' => "Inventory_Valuation_Handler", ),
    array(  'link'  => "{$link};t=304",
            'title' => "Inventory_EconomicOrderQuantity", ),
    array(  'link'  => "{$link};t=305",
            'title' => "Inventory_Valuation_SingleItemCostCalculation", ),
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
    
    case '4':
        echo "<h1>SCRIPT TO FIX ERRORS</h1>";
        echo "<br />Class :: Inventory_FixOriginalScan()<br />";
        $OBJ = new Inventory_FixOriginalScan();
        $OBJ->Execute();
    break;
    
    case '5':
        echo "<h1>SCRIPT TO FIX ERRORS</h1>";
        echo "<br />Class :: Inventory_FixZeroDollarInventoryAdjustment()<br />";
        $OBJ = new Inventory_FixZeroDollarInventoryAdjustment();
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
        $OBJ = new Inventory_FixInventoryCountsNoDate();
        $OBJ->Execute();
    break;
    
    case '9':
        echo "<h1>DATABASE INTEGRITY CHECKS</h1>";
        echo "<br />Class :: Inventory_ReportDatabaseIntegrityCheck()<br />";
        $OBJ = new Inventory_ReportDatabaseIntegrityCheck();
        $OBJ->Execute();
    break;
    
    case '10':
        echo "<h1>FIX :: INVENTORY ADJUSTMENT - ASSEMBLY PRICE</h1>";
        echo "<br />Class :: Inventory_FixInventoryAdjustmentAssemblyPrice()<br />";
        $OBJ = new Inventory_FixInventoryAdjustmentAssemblyPrice();
        $OBJ->Execute();
    break;
    
    case '11':
        echo "<h1>FIX :: INVENTORY COUNT - MIS-MATCHED DATE</h1>";
        echo "<br />Class :: Inventory_FixInventoryDateMismatch()<br />";
        $OBJ = new Inventory_FixInventoryDateMismatch();
        $OBJ->Execute();
    break;
    
    case '12':
        echo "<h1>FIX :: INVENTORY BUILD RECORD - NO BUILD RECORD FOUND</h1>";
        echo "<br />Class :: Inventory_FixAssemblyNoBuildRecord()<br />";
        $OBJ = new Inventory_FixAssemblyNoBuildRecord();
        $OBJ->Execute();
    break;
    
    case '13':
        echo "<h1>FIX :: INVENTORY ADJUSTMENT - AVERAGE VALUE METHOD</h1>";
        echo "<br />Class :: Inventory_FixInventoryAdjustmentValue()<br />";
        $OBJ = new Inventory_FixInventoryAdjustmentValue();
        $OBJ->Execute();
    break;
    
    
    
    # ===================================================================
    # ========  TEST MENU  ========
    # ===================================================================
    
    
    case '301':
        echo "<h1>TEST OF Inventory_InventoryAssemblyCalculateValue - URL NEEDS ;id=133;barcode=10053</h1>";
        echo "<br />Class :: Inventory_InventoryAssemblyCalculateValue()<br />";
        $OBJ            = new Inventory_InventoryAssemblyCalculateValue();
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
    
}

echo "</div>";

?>
