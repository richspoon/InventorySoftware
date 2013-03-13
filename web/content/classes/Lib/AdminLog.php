<?php

// FILE: Lib/AdminLog.php

class Lib_AdminLog extends BaseClass
{
    public function  __construct()
    {
        parent::__construct();

        $this->ClassInfo = array(
            'Created By'  => 'MVP',
            'Description' => 'Create and manage admin_log',
            'Created'     => '2009-12-21',
            'Updated'     => '2009-12-21'
        );

        $this->Table       = 'admin_log';

        $this->Index_Name  = 'admin_log_id';

        $this->Default_Sort= 'admin_log_id DESC';

        $this->Field_Titles = array(
            'admin_log_id'                => 'Id',
            'admin_log.admin_users_id'    => 'User Id',
            "CONCAT(admin_users.first_name, ' ', admin_users.last_name) AS USERNAME"  => 'User',
            'admin_log.module_filename'            => 'Module File',
            'admin_modules.title'         => 'Module Title',
            'admin_log.table_update_log_id' => 'Update Log Id',
            'table_update_log.table'      => 'Table',
            'table_update_log.table_id'   => 'Table Id',
            'table_update_log.action'     => 'Action',
            //'table_update_log.old_record' => 'Old Record',
            //'table_update_log.new_record' => 'New Record',
            'admin_log.created'           => 'Created'
        );

        $this->Join_Array = array(
            'admin_users'      => 'LEFT JOIN admin_users ON admin_users.admin_users_id = admin_log.admin_users_id',
            'table_update_log' => "LEFT JOIN $this->Table_Update_Log ON $this->Table_Update_Log.table_update_log_id = admin_log.table_update_log_id",
            'admin_modules'    => 'LEFT JOIN admin_modules ON admin_modules.filename = admin_log.module_filename',
        );

        $this->Default_Fields = 'USERNAME,title,action';

        $this->SetNoEditLinks();

    } // -------------- END __construct --------------


    public function ViewRecordText($id, $field_list='', $id_field='')
    {
        $RESULT = parent::ViewRecordText($id, $field_list, $id_field);
        $update_record = $this->SQL->GetRecord(array(
            'table' => $this->Table,
            'keys'  => "$this->Table_Update_Log.old_record,$this->Table_Update_Log.new_record",
            'where' => "$this->Table.$this->Index_Name=$id",
            'joins' => "LEFT JOIN $this->Table_Update_Log ON $this->Table_Update_Log.table_update_log_id=$this->Table.table_update_log_id"
        ));
        if ($update_record) {
            if ($update_record['old_record'] or $update_record['new_record']) {
                $array = $this->GetUpdateRecordText($update_record['old_record'], $update_record['new_record']);
                $RESULT .= $this->SQL->OutputTable(
                    array(array('Old' => $array[0], 'New' => $array[1])),
                    '',
                    $this->Default_Table_Options . ' class="TABLE_DISPLAY VIEW_UPDATES_TABLE"'
                );
            }
        }
        if (function_exists('AddStyle')) {
            AddStyle("a.stdbuttoni {display:none;}");
        }
        return $RESULT;
    }

    public function AddModuleRecord($module)
    {
        $admin_users_id = $this->GetUserId();
        if ($admin_users_id and $module) {
            $this->SQL->AddRecord($this->Table, 'admin_users_id,module_filename', "$admin_users_id,'$module'");
        }
    }

    public function AddUpdateRecord($id)
    {
        $admin_users_id = $this->GetUserId();
        if ($admin_users_id and $id) {
            $this->SQL->AddRecord($this->Table, 'admin_users_id,table_update_log_id', "$admin_users_id,$id");
        }
    }


}  // -------------- END CLASS --------------