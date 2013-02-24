<?php

# NOTE :: Must have the DIALOGID passed in all of the menu links for table won't refresh
$DIALOGID = Get('DIALOGID');

$link = "/office/inventory/inventory_reports;DIALOGID={$DIALOGID}";

$OBJ_MENU = new Inventory_DropdownMenu();
$OBJ_MENU->Menu_Raw = array(
    array(  'link'  => "{$link};t=1",
            'title' => "Sales Order Inventory Available", ),
    array(  'link'  => "{$link};t=3",
            'title' => "Sales Order COGS", ),
    array(  'link'  => "{$link};t=2",
            'title' => "Barcode Current FIFO Value", ),
    array(  'link'  => "{$link};t=4",
            'title' => "SCRIPT - fix inventory counts", ),
    array(  'link'  => "{$link};t=5",
            'title' => "SCRIPT - fix $0 inventory adjustments", ),
    array(  'link'  => "{$link};t=8",
            'title' => "SCRIPT - fix no date inventory counts", ),
    array(  'link'  => "{$link};t=10",
            'title' => "SCRIPT - fix inventory adjustments price", ),
    array(  'link'  => "{$link};t=11",
            'title' => "SCRIPT - fix date mis-match in inventory_counts", ),
    array(  'link'  => "{$link};t=12",
            'title' => "SCRIPT - fix inventory build records without build record", ),
    array(  'link'  => "{$link};t=13",
            'title' => "SCRIPT - inventory adjustments - convert to average valuation", ),
    array(  'link'  => "{$link};t=6",
            'title' => "MANUFACTURING RESOURCE PLANNING", ),
    array(  'link'  => "{$link};t=7",
            'title' => "Inventory Value Report", ),
    array(  'link'  => "{$link};t=9",
            'title' => "Database Integrity Checks", ),
    array(  'link'  => "{$link};t=99",
            'title' => "TEST Inventory_InventoryAssemblyCalculateValue", ),
    array(  'link'  => "{$link};t=100",
            'title' => "TEST Inventory_COGSAdjustment", ),
    array(  'link'  => "{$link};t=101",
            'title' => "TEST Inventory_COGSHandler", ),
    array(  'link'  => "{$link};t=200",
            'title' => "TEST Inventory_EOQ", ),
            
            
    
);
echo $OBJ_MENU->Execute();
echo "<div style='min-width:300px;'>&nbsp;</div>";


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
    
    
    case '99':
        echo "<h1>TEST OF Inventory_InventoryAssemblyCalculateValue - URL NEEDS ;id=133;barcode=10053</h1>";
        echo "<br />Class :: Inventory_InventoryAssemblyCalculateValue()<br />";
        $OBJ            = new Inventory_InventoryAssemblyCalculateValue();
        $OBJ->Barcode   = Get('barcode');
        $OBJ->Execute();
        
        
        
        /*
        $inventory_assembly_build_id = Get('id');
        $return_arr = $OBJ->Execute($inventory_assembly_build_id);
        
        echo "<hr><div style='background-color:red'>==========================================================</div><hr>";
        echo ArrayToStr($return_arr);
        */
    break;
    
    case '100':
        echo "<h1>TEST OF Inventory_COGSAdjustment - URL NEEDS ;id=XXX</h1>";
        echo "<br />Class :: Inventory_COGSAdjustment()<br />";
        $OBJ = new Inventory_COGSAdjustment();
        $OBJ->Inventory_Adjustments_ID = Get('id');
        $OBJ->Quantity = 1;
        $OBJ->Execute();
        
        $OBJ->EchoVar('');
        $OBJ->EchoVar('COGS_Single', $OBJ->COGS_Single);
        $OBJ->EchoVar('COGS_Total', $OBJ->COGS_Total);
        $OBJ->EchoVar('COGS_Array', $OBJ->COGS_Array);
    break;
    
    case '101':
        echo "<h1>TEST OF Inventory_COGSHandler - URL NEEDS ;id=XXX</h1>";
        echo "<br />Class :: Inventory_COGSHandler()<br />";
        $OBJ = new Inventory_COGSHandler();
        $OBJ->Inventory_Counts_ID = Get('id');
        $OBJ->Quantity = 1;
        $OBJ->Execute();
        
        $OBJ->EchoVar('');
        $OBJ->EchoVar('COGS_Single', $OBJ->COGS_Single);
        $OBJ->EchoVar('COGS_Total', $OBJ->COGS_Total);
        $OBJ->EchoVar('COGS_Array', $OBJ->COGS_Array);
    break;
    
    case '200':
        echo "<h1>TEST OF Inventory_EOQ - URL NEEDS ;id=XXX</h1>";
        echo "<br />Class :: Inventory_EOQ()<br />";
        $OBJ = new Inventory_EOQ();
        
        $OBJ->Demand_Rate_Annual          = 0;
        $OBJ->Order_Cost                  = 0;
        $OBJ->Holding_Cost_Dollar         = 0;
        $OBJ->Holding_Cost_Percentage     = 0;
        $OBJ->Unit_Price                  = 0;
        $OBJ->Daily_Demand_Rate           = 0;
        $OBJ->Lead_Time_Days              = 0;
        
        $OBJ->Execute();
    break;
    
}


?>
