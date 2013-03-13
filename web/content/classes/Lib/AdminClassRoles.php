<?php

// FILE: Lib/Lib_AdminClassRoles.php

class Lib_AdminClassRoles extends Lib_BaseClass
{

    public $Class_Role_Items_Array  = array();
    public $Class_Role_Item_Table = 'admin_class_role_items';

    public function  __construct()
    {
        parent::__construct();

        $this->ClassInfo = array(
            'Created By'  => 'Michael Petrovich',
            'Description' => 'Create and manage admin_class_roles',
            'Created'     => '2009-06-24',
            'Updated'     => '2009-06-24'
        );

        $this->Table  = 'admin_class_roles';

        $this->Add_Submit_Name  = 'ADMIN_CLASS_ROLES_SUBMIT_ADD';
        $this->Edit_Submit_Name = 'ADMIN_CLASS_ROLES_SUBMIT_EDIT';

        $this->Index_Name = 'admin_class_roles_id';

        $this->Flash_Field = 'admin_class_roles_id';

        $this->Default_Sort  = 'admin_class_roles_id';  // field for default table sort
        $this->Field_Titles = array(
            'admin_class_roles_id' => 'Admin Class Roles Id',
            'role_name' => 'Role Name',
            'class_role_items' => 'Class Role Items',
            'active' => 'Active',
            'updated' => 'Updated',
            'created' => 'Created'
        );


        $this->Field_Values['no_edit'] = array(0=> 'Edit', 1 => 'No Edit');

        $this->Default_Fields = 'role_name,class_role_items';

        $this->Unique_Fields = '';

        $this->Autocomplete_Fields ='';  // associative array: field => table|field|variable

    } // -------------- END __construct --------------


    public function SetFormArrays()
    {
        $this->GetClassRoleItemsArray();

        if ($this->Class_Role_Items_Array) {
            $items_list = Form_AssocArrayToList($this->Class_Role_Items_Array);
            $class_role_items = "checkboxlistset|Class Role Items|class_role_items|N||$items_list";
        } else {
            $class_role_items = 'h3|No Class Role Items Defined!|style="text-align:center; color:#f00;"';
        }


        $base_array = array(
            "text|Role Name|role_name|Y|50|50",
            $class_role_items
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

    public function GetClassRoleItemsArray()
    {
        if (empty($this->Class_Role_Items_Array)) {
            $this->Class_Role_Items_Array = $this->SQL->GetAssocArray(
                $this->Class_Role_Item_Table,
                'admin_class_role_items_id',
                'role_item_name',
                'active=1'
            );
        }
    }

    public function ProcessRecordCell($field, &$value, &$td_options)  // extended from parent
    {
        if (($field == 'class_role_items') and ($value)) {
            $this->GetClassRoleItemsArray();
            $ids = explode(',', $value);
            $value = '';
            foreach ($ids as $id) {
                $value  .= ArrayValue($this->Class_Role_Items_Array, $id) . "<br />\n";
            }
        }
    }

    // ----------- Process a record table cell before it is output when viewing a table  ---------------
    public function ProcessTableCell($field, &$value, &$td_options, $id='')
    {
        parent::ProcessTableCell($field, $value, $td_options, $id);
        if (($field == 'class_role_items') and ($value)) {
            $this->GetClassRoleItemsArray();
            $ids = explode(',', $value);
            $value = '';
            foreach ($ids as $id) {
                $value  .= ArrayValue($this->Class_Role_Items_Array, $id) . ', ';
            }
            $value  = substr($value, 0, -2);
            $value  = TruncStr($value, 100);
        }
    }

}  // -------------- END CLASS --------------