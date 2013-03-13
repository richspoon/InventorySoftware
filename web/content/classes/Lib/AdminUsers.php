<?php

// FILE: /Lib/Lib_AdminUsers.php

class Lib_AdminUsers extends Lib_BaseClass
{

    public $Updating_Profile        = false;
    public $Module_Roles_Table      = 'admin_module_roles';
    public $Module_Roles_Table_id   = 'admin_module_roles_id';
    public $Class_Roles_Table       = 'admin_class_roles';
    public $Class_Roles_Table_id    = 'admin_class_roles_id';

    public $Class_Role_Names        = array();
    public $Module_Role_Names       = array();

    public $Module_Roles_Form_Item  = '';
    public $Class_Roles_Form_Item   = '';
    public $Non_Profile_Form_Items  = '';
    public $Password_Size_Options   = '8,40|SECURE autocomplete="off"';


    public function  __construct()
    {

        parent::__construct();

        $this->Default_List_Size = 500;

        $this->ClassInfo = array(
            'Created By'  => 'Michael Petrovich',
            'Description' => 'Create and manage admin_users',
            'Created'     => '2009-06-14',
            'Updated'     => '2009-06-14'
        );

        $this->Table                = 'contacts';

        $this->Add_Submit_Name      = 'ADMIN_USERS_SUBMIT_EDIT';
        $this->Edit_Submit_Name     = 'ADMIN_USERS_SUBMIT_EDIT';

        $this->Index_Name           = 'contacts_id';
        $this->Flash_Field          = 'contacts_id';
        $this->Default_Sort         = 'contacts_id';  // field for default table sort

        $this->Field_Titles = array(
            'contacts_id'       => 'ID',
            'wh_id'             => 'WH_ID',
            'email_address'     => 'Email Address',
            'first_name'        => 'First Name',
            'last_name'         => 'Last Name',
            'super_user'        => 'Super User',
            'module_roles'      => 'Module Roles',
            'class_roles'       => 'Class Roles',
            'created_by'        => 'Created By',
            'active'            => 'Active',
            'updated'           => 'Updated',
            'created'           => 'Created'
        );


        $this->Default_Fields       = 'email_address,first_name,last_name,super_user,wh_id';
        $this->Unique_Fields        = 'email_address';

        $this->Field_Values['super_user'] = array(0=> 'No', 1 => 'Yes');

    } // -------------- END __construct --------------

    public function UpdateProfile($user_id)
    {
        $this->Updating_Profile = true;
        $this->EditRecord($user_id);
    }

    public function PrePopulateFormValues($id, $field='') // --- extended from parent
    {
        global $FormPrefix;
        parent::PrePopulateFormValues($id,$field);
        $_POST[$FormPrefix.'password'] = '';
    }

    public function PostProcessFormValues($FormArray) // --- extended from parent
    {
        global $PASSWORD_HASH_INFO;
        if (!$this->Error) {

            if ($FormArray['password'] == '') {
                unset($FormArray['password']);

            } else {
                if (empty($PASSWORD_HASH_INFO)) {
                    $this->Error = 'Program Error, Cannot find Password Configuration';
                } else {
                    $FormArray['password'] = Lib_Password::GetPasswordHash($FormArray['password']);
                }
            }
        }
        return $FormArray;
    }
    
    public function SetFormArrays()
    {
        $E = chr(27);

        $this->GetRoleNames();
        
        if ($this->Module_Role_Names) {
            $module_roles_list = Form_AssocArrayToList($this->Module_Role_Names);
            $this->Module_Roles_Form_Item = "checkboxlistset|Module Roles|module_roles|N||$module_roles_list";
        } else {
            $this->Module_Roles_Form_Item = 'h3|No Module Roles Defined!|style="text-align:center; color:#f00;"';
        }

        if ($this->Class_Role_Names) {
            $class_roles_list = Form_AssocArrayToList($this->Class_Role_Names);
            $this->Class_Roles_Form_Item = "checkboxlistset|Class Roles|class_roles|N||$class_roles_list";
        } else {
            $this->Class_Roles_Form_Item = 'h3|No Class Roles Defined!|style="text-align:center; color:#f00;"';
        }

        //$time_zone_list = Form_ArrayToList($this->SQL->GetAssocArray('time_zones', 'time_zones_id', 'time_zone'));

        $this->Non_Profile_Form_Items = ($this->Updating_Profile)? '' : "
            checkbox|Super User|super_user||1|0|$E
            fieldset|Page Access|style=\"margin-bottom:20px;\"|$E
            $this->Module_Roles_Form_Item|$E
            endfieldset|$E
            
            fieldset|Class Access|$E
            $this->Class_Roles_Form_Item|$E
            endfieldset|$E
            ";

        $active = ($this->Updating_Profile)? '' : "checkbox|Active|active||1|0|$E";


        $password = ($this->Action == 'ADD')? 'password|Password|password|Y|40|' . $this->Password_Size_Options
                      : 'password|New Password|password|N|40|' . $this->Password_Size_Options;

        $base_text_array = "
            form|$this->Action_Link|post|db_add_form|$E
            text|First Name|first_name|Y|40|40|$E
            text|Last Name|last_name|Y|40|40|$E
            email|Email Address|email_address|Y|40|80|$E
            $password|$E
            $this->Non_Profile_Form_Items
            ";
            //select|Time Zone|time_zones_id|N||N|$time_zone_list|$E


        if ($this->Action == 'ADD') {
            $this->Form_Data_Array_Add =
                "xxjs|userTimeZoneOffset = new Date().getTimezoneOffset()/60 * (-1);
                |$E
                $base_text_array
                hidden|created_by|$this->User_Name|$E
                submit|Add Record|$this->Add_Submit_Name|$E
                endform|$E";
        } else {
            $this->Form_Data_Array_Edit =
                "$base_text_array
                $active
                submit|Update Record|$this->Edit_Submit_Name|$E
                endform|$E";
        }
    }

    public function GetRoleNames()
    {
        if (empty ($this->Module_Role_Names)) {
            $this->Module_Role_Names = $this->SQL->GetAssocArray(
                $this->Module_Roles_Table,
                $this->Module_Roles_Table_id,
                'role_name',
                'active=1'
            );
        }

        if (empty($this->Class_Role_Names)) {
            $this->Class_Role_Names = $this->SQL->GetAssocArray(
                $this->Class_Roles_Table,
                $this->Class_Roles_Table_id,
                'role_name',
                'active=1'
            );
        }
    }

    public function GetRoleList($field, $value)
    {
        $this->GetRoleNames();
        $RESULT = '';
        if ($field == 'class_roles' && !empty($value)) {
            $ids = explode(',', $value);
            foreach ($ids as $id) {
                if (isset($this->Class_Role_Names[$id])) {
                    $RESULT .= $this->Class_Role_Names[$id] . ', ';
                }
            }
            $RESULT = substr($RESULT, 0, -2);
        } elseif ($field == 'module_roles' && !empty($value)) {
            $ids = explode(',', $value);
            foreach ($ids as $id) {
                if (isset($this->Module_Role_Names[$id])) {
                    $RESULT .= $this->Module_Role_Names[$id] . ', ';
                }
            }
            $RESULT = substr($RESULT, 0, -2);
        }
        return $RESULT;
    }

    public function ProcessTableCell($field, &$value, &$td_options, $id='')
    {
        parent::ProcessTableCell($field, $value, $td_options, $id);

        if ($field == 'class_roles' || $field == 'module_roles') {
            $value = $this->GetRoleList($field, $value);
        } elseif ($field == 'super_user') {
            if ($value == 'Yes') {
                $td_options = 'style="background-color:#7f7;"';
            }
        }
    }

    public function ProcessRecordCell($field, &$value, &$td_options)
    {
        if ($field == 'class_roles' || $field == 'module_roles') {
            $value = $this->GetRoleList($field, $value);
        }
    }

    public function ViewRecordText($id, $field_list='', $id_field='')
    {
        $RESULT = parent::ViewRecordText($id, $field_list, $id_field);

        if (!$this->Updating_Profile  and (ArrayValue($this->User_Info,'SUPER_USER'))) {
            $E = chr(27);

            $eq = EncryptQuery("id=$id");
            $form_data = "
                form|@@PAGELINKQUERY@@|post|$E
                hidden|value|$eq|$E
                submit|Login as this User|SUBMIT|$E
                endform|$E
            ";
            $ERROR = '';
            if (HaveSubmit('SUBMIT')) {
                $array = ProcessFormNT($form_data, $ERROR);
                $qdata = GetEncryptQuery($array['value'], false);
                $id = ArrayValue($qdata, 'id');
                if ($id) {
                    $AUTH = new Authentication;
                    $AUTH->LoginAsAlias($id);
                    $form_data .= "
                        js|top.window.location = 'index';
                    ";
                }

            }
            $RESULT .= OutputForm($form_data, Post('SUBMIT'));
        }

        return $RESULT;

    }
    
    protected function TriggerAfterInsert($db_last_insert_id)
    {
        $this->SQL->UpdateRecord($this->Table, "wh_id=$db_last_insert_id + 1000000", "$this->Index_Name=$db_last_insert_id");
        #$this->Wh_Id = $this->SQL->GetValue($this->Table, 'wh_id', "$this->Index_Name=$db_last_insert_id");
    }
    
}  // -------------- END CLASS --------------