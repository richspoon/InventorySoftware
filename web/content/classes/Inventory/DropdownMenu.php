<?phpclass Inventory_DropdownMenu extends Inventory_InventoryBaseSimple{    public $Menu_Raw = array();         // Array passed in representing menu items (single array)    public $Menu_Multi = array();       // Array passed in representing multiple menu items (multi-dimensional array)            public function  __construct()    {        parent::__construct();        $this->SetSQLInventory();   // set the database connection to the inventory database                $this->Classname = get_class($this);        $this->ClassInfo = array(            'Created By'    => 'Richard Witherspoon',            'Created Date'  => '2012-12-18',            'Updated By'    => '',            'Updated Date'  => '',            'Filename'      => $this->Classname,            'Version'       => '1.0',            'Description'   => 'Create a drop-down menu on page',            'Update Log'    => array(                '2012-12-18_1.0'    => "Module Created",            ),        );            } // -------------- END __construct --------------        public function Execute()    {        $this->JavascriptDropDown();                // JavaScript needed for making menu work        //$this->Style();                             // Styles for formatting menu        $output = '';                               // initialize variable                        // ----- call the correct menu-formatting function        if (is_array($this->Menu_Raw) && (count($this->Menu_Raw) > 0)) {            $output .= $this->CreateMenuSingle();        }                if (is_array($this->Menu_Multi) && (count($this->Menu_Multi) > 0)) {            $output .= $this->CreateMenuMultiple();        }                        // ----- return the menu to the calling page        return $output;    }        private function CreateMenuSingle()    {        # FUNCTION :: Single menu item                $menu_content = '';        foreach ($this->Menu_Raw as $id => $menu) {            $menu_content .= "<a href='{$menu['link']}'>{$menu['title']}</a>";        }                $output = "            <div id='dropdownmenu' class='shadow'>            <ul>                <li><a href='#' class='dropdown'>Menu</a></li>                <li class='sublinks'>{$menu_content}</li>            </ul>            </div>            <div class='clear'></div>            <br />";                return $output;    }        private function CreateMenuMultiple()    {        # FUNCTION :: Multiple menu items                $menu_content = '';                foreach ($this->Menu_Multi as $menu_name => $menu_arr) {                    $menu_content .= "<li><a href='#' class='dropdown'>{$menu_name}</a></li>";            $menu_content .= "<li class='sublinks'>";                        foreach ($menu_arr as $id => $menu) {                $menu_content .= "<a href='{$menu['link']}'>{$menu['title']}</a>";            }                        $menu_content .= "</li>";        }                $output = "            <div id='dropdownmenu' class='shadow'>            <ul>                {$menu_content}            </ul>            </div>            <div class='clear'></div>            <br />";                return $output;    }        private function JavascriptDropDown()    {    	$script = "        $('.dropdown').mouseenter(function(){            $('.sublinks').stop(false, true).hide();                    var submenu = $(this).parent().next();            submenu.css({                position:'absolute',                top: $(this).offset().top + $(this).height() + 'px',                left: $(this).offset().left + 'px',                zIndex:1000            });                        submenu.stop().slideDown(300);                        submenu.mouseleave(function(){                $(this).slideUp(300);            });        });        ";        AddScriptOnReady($script);    }        private function Style()    {        AddStyle("                    ");    }    }  // -------------- END CLASS --------------