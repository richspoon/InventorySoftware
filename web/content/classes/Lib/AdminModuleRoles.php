<?php

// FILE: /Lib/Lib_AdminModuleRoles.php

class Lib_AdminModuleRoles extends Lib_BaseClass
{
    public $Admin_Modules_Table = 'admin_modules';

    public function  __construct()
    {
        parent::__construct();

        $this->ClassInfo = array(
            'Created By'  => 'Michael Petrovich',
            'Description' => 'Create and manage admin_module_roles',
            'Created'     => '2009-06-24',
            'Updated'     => '2009-06-24'
        );

        $this->Table  = 'admin_module_roles';

        $this->Add_Submit_Name  = 'ADMIN_MODULE_ROLES_SUBMIT_ADD';
        $this->Edit_Submit_Name = 'ADMIN_MODULE_ROLES_SUBMIT_EDIT';

        $this->Index_Name = 'admin_module_roles_id';

        $this->Flash_Field = 'admin_module_roles_id';

        $this->Default_Sort  = 'admin_module_roles_id';  // field for default table sort
        $this->Field_Titles = array(
            'admin_module_roles_id' => 'ID',
            'role_name' => 'Role Name',
            'modules' => 'Modules',
            'active' => 'Active',
            'updated' => 'Updated',
            'created' => 'Created'
        );


        $this->Default_Fields = 'role_name,modules,active';

        $this->Unique_Fields = '';

        $this->Autocomplete_Fields ='';  // associative array: field => table|field|variable

    } // -------------- END __construct --------------


    public function SetFormArrays()
    {
        $modules     = $this->SQL->GetArrayAll($this->Admin_Modules_Table, 'category,is_folder,filename,title', "active=1", 'is_folder DESC,category,title');
        $module_list = ''; 
        foreach ($modules as $row) {
            $category = $row['category'];
            $folder   = $row['is_folder'];
            $filename = $row['filename'];
            $title    = $row['title'];
            if ($category) {
                $title = "<b>[$category]</b> $title";
            }
            if ($folder) {
                $title = ' <b>FOLDER</b> &mdash; ' . $title;
            }
            $module_list .= "$filename=$title|";
        }
        $module_list = substr($module_list, 0, -1);
    
        $base_array = array(
            "text|Role Name|role_name|N|32|32",
            "checkboxlistset|Modules|modules|N||$module_list"
        );

        $this->Form_Data_Array_Add = array_merge(
            array(
                "form|$this->Action_Link|post|db_edit_form"
            ),
            $base_array,
            array(
                "submit|Add Record|$this->Add_Submit_Name",
                "endform"
            )
        );

        $this->Form_Data_Array_Edit = array_merge(
            array(
                "form|$this->Action_Link|post|db_edit_form"
            ),
            $base_array,
            array(
                "checkbox|Active|active||1|0",
                "submit|Update Record|$this->Edit_Submit_Name",
                "endform"
            )
        );
    }
    
    public function ProcessRecordCell($field, &$value, &$td_options)  // extended from parent
    {
        if ($field == 'modules') {
            $value  = str_replace(',', "<br />\n", $value);
        }
    }

    // ----------- Process a record table cell before it is output when viewing a table  ---------------
    public function ProcessTableCell($field, &$value, &$td_options, $id='')
    {
        parent::ProcessTableCell($field, $value, $td_options, $id);
        if ($field == 'modules') {
            $value  = str_replace(',', ', ', $value);
            $value = TruncStr($value, 100);
        }
    }

}  // -------------- END CLASS --------------