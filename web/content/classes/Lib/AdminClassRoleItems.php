<?php

// FILE: Lib/Lib_AdminClassRoles.php

class Lib_AdminClassRoleItems extends Lib_BaseClass
{
    public function  __construct()
    {
        parent::__construct();

        $this->ClassInfo = array(
            'Created By'  => 'Michael Petrovich',
            'Description' => 'Create and manage admin_class_role_items',
            'Created'     => '2009-06-24',
            'Updated'     => '2009-06-24'
        );

        $this->Table  = 'admin_class_role_items';

        $this->Add_Submit_Name  = 'ADMIN_CLASS_ITEM_ROLES_SUBMIT_ADD';
        $this->Edit_Submit_Name = 'ADMIN_CLASS_ITEM_ROLES_SUBMIT_EDIT';

        $this->Index_Name = 'admin_class_role_items_id';

        $this->Flash_Field = 'admin_class_role_items_id';

        $this->Default_Sort  = 'role_item_name';
        
        $this->Field_Titles = array(
            'admin_class_role_items_id' => 'ID',
            'role_item_name' => 'Role Item Name',
            'class' => 'Class',
            'no_edit' => 'No Editing',
            'demo_mode' => 'Demo Mode',
            'where_clause' => 'Where Clause',
            'flags' => 'Flags',
            'active' => 'Active',
            'updated' => 'Updated',
            'created' => 'Created'
        );


        $this->Field_Values['no_edit']   = array(0=> 'Edit', 1 => 'No Edit');
        $this->Field_Values['demo_mode'] = array(0=> 'No',   1 => 'Yes');
        
        $this->Default_Fields = 'role_item_name,class,no_edit,demo_mode,where_clause,flags,active';

        $this->Unique_Fields = '';

        $this->Autocomplete_Fields ='';  // associative array: field => table|field|variable

    } // -------------- END __construct --------------


    public function SetFormArrays()
    {
        $classes = GetDirectory(CLASS_DIR, '.php', 'autoload,archive' . DIRECTORY_SEPARATOR );
        foreach ($classes as $key => $value) {
            $classes[$key] = str_replace(array('class.', '.php', DIRECTORY_SEPARATOR), array('', '', '_'), $value);
        }
        $class_list = implode('|', $classes);
    
        $base_array = array(
            "text|Role Name|role_item_name|Y|50|50",
            "select|Class|class|Y||$class_list",
            "checkbox|No Editing|no_edit||1|0",
            "checkbox|Demo Mode|demo_mode||1|0",
            "html|Where Clause|where_clause|N|80|10",
            "html|Flags|flags|N|80|10",
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

}  // -------------- END CLASS --------------