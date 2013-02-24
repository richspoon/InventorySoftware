<?php
// CREATED BY RICHARD
// Customer viewing their own orders

class Inventory_InventoryCommand extends BaseClass
{

    public $URL_Purchases_View_Orders = '/office/store/view_orders';

    public $load_all_records = true; // If TRUE - will load ALL records
    public $Default_Tab                 = 0;
    
    public $num_actions_per_col         = 5;    // How many actions to show in each row - COMMON ACTIONS
    
    public $Show_Profile                = true; // Show the customer profile tab
    public $Show_Purchases              = true; // Show purchases made by customer tab
    public $Show_Sessions               = true; // Show booked session for cutomer tab
    public $Show_Touchpoints            = true; // Show all communications made with customer tab
    public $Show_Actions                = true; // Show common actions that can be done with this customer
    public $Show_Helpcenter             = true; // Show common actions that can be done with this customer
    public $Show_Session_Search         = true; // Show session search

    public $WH_ID                       = 0;
    public $table_sessions              = 'sessions';
    public $table_sessions_checklist    = 'session_checklists';
    public $table_instructor_profile    = 'instructor_profile';

    public $Loading_Image               = '/wo/images/upload.gif';
    public $Loading_Image_Html          = '<p><img src="/wo/images/upload.gif" width="32" height="32" border="0" alt="Loading..." /></p>';



    public $arrow_image_location        = "/office/images/arrow_dotted.gif";
    public $description_len_trunc       = 60; // How many characters to show before truncating description on general listing

    public $total_contents_width        = '950px';  // width of whole product-listing table
    public $categories_width            = '200px';  // width of categories area - needs to match or be larger than "category_wrapper_width"
    public $category_contents_gap       = '50px;';  // gap between categories and products
    public $products_width              = '700px';  // width of prodcuts area


    public $page_location               = '';
    public $product_detail_link         = '';

    public $colgap                      = '&nbsp;&nbsp;';

    public $category                    = '';
    public $where                       = '';
    public $breadcrumb                  = '';

    // ------ Transactions ------
    public $Transaction_Table           = 'store_transactions';
    public $Transaction_Items_Table     = 'store_transaction_items';

    public $OBJ_TABS                    = null;
    public $OBJ_PRODUCTS                = null;
    public $OBJ_PRODUCT_SCAN            = null;
    
    
    
    
    public $Tabs_Div_Prefix             = 'inventory_command_';
    public $Tabs_Function_Prefix        = 'InventoryCommand';
    public $Tab_Array                   = array();
    

    // ==================================== CONSTRUCT ====================================
    public function  __construct()
    {
        $this->SetSQL();
        
        $default_Wh_Id = $_SESSION['USER_LOGIN']['LOGIN_RECORD']['wh_id'];
        
        $this->SetParameters(func_get_args());
        $this->WH_ID = ($this->GetParameter(0)) ? $this->GetParameter(0) : $default_Wh_Id; //??????????????????????? <--- RAW (12-21) This lets an admin view any customer profile by instantiating the class with that users WH_ID. Otherwise it shows the admin's profile info
        
        
        // === INITIALIZE ALL CLASSES ===
        // === have to do this or we can't get scripts onto the pages in the right locations
        $this->OBJ_TABS                 = new Tabs('tab_inventory_command', 'tab_edit');
        $this->OBJ_PRODUCTS             = new Inventory_InventoryProducts();
        $this->OBJ_PRODUCT_SCAN         = new Inventory_InventoryScan();
        
        $this->OBJ_PRODUCTS->WH_ID      = $this->WH_ID;
        
        
        $this->Tab_Array = array(
            '1' => 'product_scan',
            '2' => 'common_actions',
            '3' => 'products',
        );
    }

    public function SetSQL()
    {
        if (empty($this->SQL)) {
            $this->SQL = Lib_Singleton::GetInstance('Lib_Pdo');
        }
    }

    public function Execute()
    {
        // === output all class scripts
        $this->OBJ_PRODUCTS->AddScript();
        
        
        // === perform a css swap
        
        
        // === add css styles
        $this->OBJ_PRODUCTS->AddStyle();
        
        
        
        // === Load tabs into window
        echo "<div style='width:800px;'></div>";
        $this->LoadTabs();
    }



    public function GetCommonActions()
    {
        $common_actions = array();
        
        $common_actions[] = array(
            'title' => 'CANCEL ACCOUNT',
            'class' => 'Profile_CancelAccountCustomer',
            'vars'  => "v1={$this->WH_ID}",
        );
        
        $common_actions[] = array(
            'title' => 'RE-ACTIVATE ACCOUNT',
            'class' => 'Profile_ReactivateAccountCustomer',
            'vars'  => "v1={$this->WH_ID}",
        );
        
        $common_actions[] = array(
            'title' => 'GIVE FREE CREDITS',
            'class' => 'Profile_CustomerProfileFreeCredits',
            'vars'  => "v1={$this->WH_ID}",
        );
        
        
        $output = "";
        
        $temp_count = 0;
        foreach ($common_actions AS $action) {
            $output .= ($temp_count == 0) ? "<div class='col'>" : '';
        
            $link   = getClassExecuteLinkNoAjax(EncryptQuery("class={$action['class']};{$action['vars']}"));
            $script = "top.parent.appformCreate('Window', '{$link}', 'apps'); return false;";
            $output .= "<div class='btn_actions'><a href='#' onclick=\"{$script}\">{$action['title']}</a></div>";
            
            $output .= ($temp_count == ($this->num_actions_per_col-1)) ? "</div><div class='col'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>" : '<br />';
            
            $temp_count++;
            $temp_count = ($temp_count == $this->num_actions_per_col) ? 0 : $temp_count;
        }
        
        $output .= (($temp_count != $this->num_actions_per_col) && ($temp_count !=0)) ? "</div>" : '';
        $output .= "<div class='clear'></div>";
        
        return $output;
    }

    public function SwitchTab($TABNUM)
    {
        $TABNUM = is_numeric($TABNUM) ? $TABNUM : "'$TABNUM'";
        $script = "setTab{$this->Tabs_Function_Prefix}($TABNUM, 'tab', 'tablink', 'tabselect');";
        AddScriptOnReady($script);
    }
    
    public function AjaxHandle()
    {
        $action = Get('action');
		switch ($action) {
			default:
				$this->LoadTabContent($action);
			break;
		}
    }

    public function LoadTabContent($action)  // MVP function ------- used for ajax
    {
        $RESULT = '';
        switch ($action) {
            
            case 'product_scan':
                $RESULT = $this->OBJ_PRODUCT_SCAN->Execute(); //ListTableText();
            break;
            
            case 'products':
                $RESULT = $this->OBJ_PRODUCTS->Execute(); //ListTableText();
            break;

            case 'common_actions':
                $RESULT = $this->GetCommonActions();
            break;

        }
        if (empty($RESULT)) {
            $RESULT = 'Not Found';
        }
        echo $RESULT;
    }


    public function LoadTabs()
    {
        $ajax_page_link = $GLOBALS['PAGE']['ajaxlink'];  // global from the $PAGE array
        
        
        $temp_script_1 = '';
        $temp_script_2 = '';
        foreach ($this->Tab_Array AS $number => $value) {
            $temp_script_1 .= "
            case '{$value}':
                newNum = {$number};
                break;
            ";
            
            $temp_script_2 .= "
            case {$number}:
                load{$this->Tabs_Function_Prefix}TabContent('{$value}');
                break;
            ";
        }
        
        
        $function_setTab = "
            function setTab{$this->Tabs_Function_Prefix}(num, group, tablink, tabselect) 
            {
                if (typeof(num)!='number') {
                    var newNum = 0;
                    switch(num) {
                        {$temp_script_1}
                    }
                    num = newNum;
                }
                
                var linkname = group + 'link';
                hideGroupExcept(group, num);
                setClassGroup(linkname, num, tablink, tabselect);
                
                switch(num) {
                    {$temp_script_2}
                }
                
                if (haveDialogTemplate) ResizeIframe();
                return false;
            }
        ";
        
        
        
        
        $script = "
        function TestAlert(addedText) {
            alert('testing ==> ' + addedText);
        }

        var haveTabContents = ''; // variable to prevent loading after inital load
        function load{$this->Tabs_Function_Prefix}TabContent(name)
        {
            //if (haveTabContents.indexOf(name) < 0 ) {
                var id = '{$this->Tabs_Div_Prefix}' + name;
                var link = '$ajax_page_link' + ';action=' + name;
                $('#' + id).load(link, function() {
                    //haveTabContents += ',' + name;
                    if (haveDialogTemplate) ResizeIframe();
                });
            //}
        }
        
        {$function_setTab}
        ";
        AddScript($script);

        
        

        
        # TAB SECTION
        # =========================================================
        $this->OBJ_TABS->Tab_Set_Function_Name = "setTab{$this->Tabs_Function_Prefix}";
        $tab_content_profile = '';
        $default_content = $tab_content_profile;
        
        foreach ($this->Tab_Array AS $number => $value) {
            $title      = ucwords(strtolower(str_replace('_', ' ', $value)));
            $div        = $this->Tabs_Div_Prefix . $value;
            $id         = "id=\"{$div}\"";
            $class      = ($number == 1) ? 'class=\"tab_content_wrapper\"' : '';
            $content    = ($number == 1) ? $default_content : $this->Loading_Image_Html;
            
            $this->OBJ_TABS->AddTab($title, "<div $id $class>{$content}</div>");
        }
        
        
        $tab_content = $this->OBJ_TABS->OutputTabs(true);
        echo $tab_content;
        
        
        if ($this->Default_Tab != '') {
            $this->SwitchTab($this->Default_Tab);
        }
    }



} //end class