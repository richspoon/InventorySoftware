<?php
/*
    Office Baseclass for table management - By Michael V. Petrovich 2009+
    Changes
    2010-11-06:
        added $this->Use_Selection_Tab (displays table with tabs, set to false to remove tabs)
        added $this->Show_Export (shows export links, set to false to remove)
        Export now uses a Post instead of a session variable
        $this->GetExportBlock() is now a function to output the export section
        $this->GetTableHeading($colcount) has been added to customize the table heading section with extension

*/


class Lib_BaseClass
{
    public  $ClassInfo;                                                     //class information
    public  $Class_Name;
    public  $Classname;
    public  $Base_Class_Name                = 'Lib_BaseClass';
    public  $Table;                                                         // the table name
    public  $Table_Title;
    public  $Span_Tables                    = array();                      // array of tables to span in adding or editing
    public  $Span_Tables_Join_Field         = '';                           //linking field to use for spans
    public  $Span_Joins                     = '';
    public  $Demo                           = false;                        // When true does not write to database
    public  $Custom_Search_Table            = 'admin_custom_searches';
    public  $User_Login_Var                 = 'USER_LOGIN';
    public  $User_Session_Name              = 'USER_LOGIN';
    public  $Admin_Id_Name                  = 'admin_users_id';
    public  $Flags                          = '';
    public  $Index_Name                     = 'id';                         // the table index
    public  $Add_Submit_Name                = 'DB_SUBMIT_ADD';              //the name of the submit button when adding
    public  $Edit_Submit_Name               = 'DB_SUBMIT_EDIT';             //the name of the submit button when editing
    public  $Action                         = '';                           //What action (ADD,EDIT, etc)
    public  $Action_Copy                    = false;                        //for copy record
    public  $Error                          = '';                           //the error variable
    public  $Edit_Id                        = 0;
    public  $Edit_Id_Field                  = '';
    public  $Action_Link;                                                   // action link is the link used for posting forms
    public  $Field_Titles;                                                  // this is an associative array created in extensions that define: field name => title
    public  $Form_Data_Array_Add;                                           // this is the form array used to add a record
    public  $Form_Data_Array_Edit;                                          // this is the form array used when editing a record
    public  $Form_Array                     = '';                           // data processed from form;
    public  $Unique_Fields;                                                 // this is a comma delimited list of fields that must be unique in the database
    public  $Default_Fields;                                                //this is a comma delimited list of fields that initially display in a table
    public  $Default_Values                 = array();                      // this is an associative array of fields and their default values when adding a record
    public  $Autocomplete_Fields            = '';                           // this is an associative array of: field => table|field|variable
    public  $Form_Table                     = '';                           // this is the html table returned from form processing
    public  $Field_Values = array (
                'active' => array(0=> 'No', 1 => 'Yes')
            );                                                              // Field_Values are the used to change the display of values in a table: field => array of values=>new values

    public  $Default_Where                  = '';
    public  $Default_Sort                   = '';
    public  $Default_List_Size              = 25;
    public  $Flash_Field                    = '';                           // this is a field that displays briefly when a record is added
    public  $Idx;                                                           // this is an index used for displaying tables.  The index is used so more than one table can be displayed.
    public  $Dialog_Id                      = '';
    public  $User_Info;
    public  $User_Name;                                                     // the user name accessing the class
    public  $Insert_Id                      = 0;
    public  $Export_Xml_Header_Text_Color   = '#FFFFFF';
    public  $Export_Xml_Header_Cell_Color   = '#0070C0';
    public  $Parameters                     = array();
    public  $Parameter_Count                = 0;
    public  $Parameter_String               = '';
    public  $Parameter_String_Prefix        = 'PARAM:';
    public  $SQL                            = '';                           //SQL PDO connection object
    private $Table_Creation_Query;                                          // the is supposed to be the table creation query, but is not used
    public  $Default_Table_Options          = 'cellspacing="1" cellpadding="0"';
    public  $Default_View_Table_Options     = 'cellspacing="1" cellpadding="0" class="VIEW_RECORD_TABLE"';
    public  $Default_Td_Options             = '';
    public  $Default_Th_Options             = '';
    public  $Edit_Links                     = '';                           //this is the links used to view/edit/delete or anything else for each row.  Variables are swapped into this template
    public  $Add_Link                       = '';
    public  $Edit_Links_Count               = 3;                            // this is the number of links, used to know how many table cells are needed for the edit links
    public  $Joins                          = '';                           // this is any joins need for a table
    public  $Default_Joins                  = '';                           // this is any joins need for a start table (used in creating initial table)
    public  $Join_Array                     = '';
    public  $Use_Join_Array                 = array();
    public  $Table_Update_Log               = 'table_update_log';                       // this is the name of the table that is used for updating
    public  $Admin_Log_Table                = 'admin_log';                              // this is the name of the table that is used for updating
    public  $Export_Link                    = '/wo/wo_export_helper.php';               // this is the default link to an export_helper program
    public  $Auto_Complete_Helper           = '/wo/wo_autocomplete_helper.php';
    public  $Tab_Search_Table_Function      = 'setTabSearchTable';
    public  $Set_Custom_Search              = true;
    public  $Use_Active                     = true;                         // use active is set to use the 'active' field
    private $Db_Query_Display               = '';                           // used to display queries in ajax calls
    protected $Last_Update_Id               = '';
    public  $Record_Found                   = true;
    public  $Close_On_Success               = true;                         // close dialog on sucessfull editing
    public  $Use_Selection_Tab              = true;                         // tab for selection of items
    public  $Show_Export                    = true;

    // ----------- this is a variable of the search operators ---------------
    public  $Search_Selection_Operators = array(
       'All', '=', 'Not =',
       'List (x,y)', '&lt;', '&gt;', '&lt;=', '&gt;=',
       'Includes', 'Begins With', 'Does Not Include',
       'Between (x|y)'
    );

    public $Active_Keys                     = array(1=>'Active Only',2=>'Inactive Only',3=>'Active and Inactive');
    public $TableHeading_ShowTitle          = true;
    public $Show_Table_Post_Process         = false;
    public $Bypass_Form_Processing          = false;                        // (false) TRUE = don't process the form after running PostProcessFormValues() function.
    
    
    // ----------- construction ---------------
    public function  __construct()
    {
        
        $this->SetSQL();                                                    // connect to database

        $this->Action_Link      = $_SERVER['REQUEST_URI'];                  // this page's URL
        $this->User_Name        = Session('DB_UPDATE_USER_NAME');           // store the User's name for tracking
        $this->User_Info        = Session($this->User_Session_Name);
        $this->Dialog_Id        = Get('DIALOGID');                          // current window ID
        
        if (empty($GLOBALS['TABLE_BASE_INDEX_COUNT'])) {                    // calculate next table index
            $GLOBALS['TABLE_BASE_INDEX_COUNT'] = 1;
        } else {
            $GLOBALS['TABLE_BASE_INDEX_COUNT']++;
        }
        
        $this->Idx              = $GLOBALS['TABLE_BASE_INDEX_COUNT'];       // store current table index
        
        $this->Class_Name   = get_class($this);                             // initialize class name variable
        $this->Classname    = get_class($this);                             // initialize class name variable
        
        $this->ClassInfo = array(
            'Created By'    => 'Michael Petrovich',
            'Created Date'  => '2010-10-01',
            'Updated By'    => 'Richard Witherspoon',
            'Updated Date'  => '2013-05-01',
            'Filename'      => $this->Classname,
            'Version'       => '2.0',
            'Description'   => 'Base class for all classes',
            'Update Log'    => array(
                '2013-05-01_2.0'    => "Modifications to how 'search' tab displays table",
            ),
        );
        
        
        $this->GetSpanJoins();


        $this->Edit_Links  = qqn("
        <td align=`center`><a href=`#` class=`row_view`   title=`View`   onclick=`tableViewClick('@IDX@','@VALUE@','@EQ@', '@TITLE@'); return false;`></a></td>
        <td align=`center`><a href=`#` class=`row_edit`   title=`Edit`   onclick=`tableEditClick('@IDX@','@VALUE@','@EQ@', '@TITLE@'); return false;`></a></td>
        <td align=`center`><a href=`#` class=`row_delete` title=`Delete` onclick=`tableDeleteClick('@IDX@','@VALUE@','@EQ@'); return false; `></a></td>");

        $this->Add_Link = qqn("<a href=`#` class=`add_record` title=`Add Record` onclick=`tableAddClick('@EQ@', '@TITLE@'); return false; `>Add</a>");

        $this->SetDefaultWhere();
    }

    public function AddCopyLinks()
    {
        $this->Edit_Links  .= qqn("
        <td align=`center`><a href=`#` class=`row_copy`  title=`Copy`   onclick=`tableCopyClick('@IDX@','@VALUE@','@EQ@', '@TITLE@'); return false;`></a></td>");
        $this->Edit_Links_Count++;
    }


    public function SetSQL()
    {
        $this->SQL = Lib_Singleton::GetInstance('Lib_Pdo');
    }


    public function GetParameter($num)
    {
        return isset($this->Parameters[$num])? $this->Parameters[$num] : '';
    }

    public function SetParameters($parameters)
    {
        if (!empty($parameters)) {
            if ((count($parameters) == 1) and (strpos($parameters[0], $this->Parameter_String_Prefix) === 0 )) {
                $this->Parameter_String = $parameters[0];
                $param = strFrom($parameters[0], $this->Parameter_String_Prefix);
                $this->Parameters = unserialize(HexDecodeString($param));
            } else {
                $this->Parameters = $parameters;
                $this->Parameter_String = $this->Parameter_String_Prefix . HexEncodeString(serialize($parameters));
            }
        }
        $this->Parameter_Count = count($this->Parameters);
    }

    public function GetParameterStringFromArray($array)
    {
        if (is_array($array) and !empty($array)) {
            return $this->Parameter_String_Prefix . HexEncodeString(serialize($array));
        } else {
            return '';
        }
    }

    public function GetFlag($key)
    {
        return ArrayValue($this->Flags, $key);
    }

    public function AddDefaultWhere($new_where)
    {
        if ($new_where) {
            $this->Default_Where .= ($this->Default_Where)?
            ' AND ' . $new_where : $new_where;
        }
    }

    public function SetDefaultWhere()
    {
        $class_roles = ArrayValue($this->User_Info, 'CLASS_ROLES');
        if ($class_roles) {
            foreach ($class_roles as $role) {
                if (($role['class'] == $this->Class_Name) or ($role['class'] == $this->Base_Class_Name)){
                    if ($role['no_edit']) {
                        $this->SetNoEditLinks();
                    }
                    if ($role['demo_mode']) {
                        $this->Demo = true;
                    }
                    if ($role['where_clause']) {
                        $this->AddDefaultWhere($role['where_clause']);
                    }
                    if ($role['flags']) {
                        $flags = explode("\n", $role['flags']);
                        foreach ($flags as $flag) {
                            if (strpos($flag, '=') !== false) {
                                list($key, $value) = explode('=', $flag);
                                $key = trim($key);
                                $value = trim($value);
                                $this->Flags[$key] = $value;
                            }
                        }
                    }
                }
            }
        }
    }

    public function SetIdValue($style)
    {
        switch ($style) {
            case 1 : $this->Index_Name = 'id';
                     break;
            case 2 : $this->Index_Name = $this->Table . '_id';
                     break;
            case 3 : $this->Index_Name = 'id_' . $this->Table;
                     break;
            default: $this->Index_Name = 'id';
        }
    }

    public function GetTableTitle()
    {
        if (empty($this->Table_Title)) {
            $this->Table_Title = NameToTitle($this->Table);
        }
        return $this->Table_Title;
    }

    public function GetUserId()
    {
        $session = Session($this->User_Login_Var);
        if ($session) {
            $user_id = intOnly(ArrayValue($session, 'USER_ID'));
        } else {
            $user_id = 0;
        }
        return $user_id;
    }

    // ----------- function to remove any links except viewing ---------------
    public function SetNoEditLinks()
    {
        $this->Edit_Links  = qqn("
        <td align=`center`><a href=`#` class=`row_view` title=`View` onclick=`tableViewClick('@IDX@','@VALUE@','@EQ@','@TITLE@'); return false;`></a></td>");
        $this->Edit_Links_Count  = 1;
        $this->Add_Link = '';
    }

    // ----------- functions to get a field from a title definition in Tables which may contain CONCAT ---------------
    public function GetFieldAlias($key)
    {
        $RESULT = strFromLast($key, ' AS ');
        if (empty($RESULT)) {
            $RESULT = strFrom($key, '.');
        }
        if (empty($RESULT)) {
            $RESULT = $key;
        }
        return $RESULT;
    }

    // ---- takes a return field from a mysql query and finds the key in the Field_Titles ---
    public function GetFieldTitleFromAlias($field)
    {
        //if (in_array($field, $this->Field_Titles)) {
        if (array_key_exists($field, $this->Field_Titles)) {
            return $field;
        } else {
            foreach ($this->Field_Titles as $key => $value) {
                $alias = $this->GetFieldAlias($key);
                if ($alias == $field) {
                    return $key;
                }
            }
            return '';
        }
        return '';
    }

    // ----------- would create a table if the query was defined ---------------
    public function CreateTable()
    {
        if ($this->Table_Creation_Query) {
            return $this->SQL->Query($this->Table_Creation_Query);
        } else {
            return false;
        }
    }

    // ----------- function to update the change table ---------------
    public function UpdateChangeLog($table_id, $action, $old_record_array, $new_record_array)
    {
        /*  -------- fields -------
            `table` varchar(80) NOT NULL,
            `table_id` int(11) NOT NULL,
            `action` varchar(20) NOT NULL,
            `old_record` text,
            `new_record` text,
            `changed_by` varchar(80)
        */
        
        /*
        $keys = '`table`,`table_id`, `action`, `old_record`, `new_record`, `changed_by`';
        $values = $this->SQL->QuoteValueC($this->Table) . $this->SQL->QuoteValueC($table_id) . $this->SQL->QuoteValueC($action)
                . $this->SQL->QuoteValueC(serialize($old_record_array))
                . $this->SQL->QuoteValueC(serialize($new_record_array))
                . $this->SQL->QuoteValue($this->User_Name);

        $RESULT = $this->SQL->AddRecord($this->Table_Update_Log, $keys, $values);

        // ------- udpate admin log --------
        if ($RESULT) {
            $last_id = $this->SQL->GetLastInsertId();
            $this->AddAdminLogUpdateRecord($last_id);
        }

        return $RESULT;
        */
        
        return true;
    }

    public function AddAdminLogUpdateRecord($id)
    {
        $admin_users_id = $this->GetUserId();
        if ($admin_users_id and $id) {
            $this->SQL->AddRecord($this->Admin_Log_Table, 'admin_users_id,table_update_log_id', "$admin_users_id,$id");
        }
    }


    // ----------- function to view updates to a record ---------------
    public function GetUpdateRecordText($old_record, $new_record)
    {
        $RESULT = array();
        $old = '';

        if (!empty($old_record)) {
            $old_record_array = unserialize($old_record);

            if (!empty($old_record_array)) {
                foreach ($old_record_array as $key => $value) {
                    $old .= "$key=$value<br />";
                }
            }
        }

        $RESULT[] = $old;

        $new = '';
        if (!empty($new_record)) {
            $new_record_array = unserialize($new_record);
            if (!empty($new_record_array)) {
                foreach ($new_record_array as $key => $value) {
                    $test1 = empty($old_record_array[$key])? '' : $old_record_array[$key];
                    $test2 = empty($new_record_array[$key])? '' : $new_record_array[$key];
                    if ($test1 != $test2) {
                        $new .= "<span class=\"VIEW_UPDATES_DIFF\">$key=$value</span><br />";
                    } else {
                        $new .= "$key=$value<br />";
                    }
                }
            }
        }
        $RESULT[] = $new;
        return $RESULT;
    }


    public function ViewUpdatesText($table_id)
    {
        $records = $this->SQL->GetArrayAll($this->Table_Update_Log, '*', "`table`='$this->Table' AND `table_id`=$table_id");
        $record_count = count($records);
        for ($i=0; $i< $record_count; $i++) {
            $array = $this->GetUpdateRecordText($records[$i]['old_record'], $records[$i]['new_record']);
            $records[$i]['old_record'] = $array[0];
            $records[$i]['new_record'] = $array[1];
        }
        if ($record_count) {
            $RESULT = $this->SQL->OutputTable($records, '', $this->Default_Table_Options . ' class="TABLE_DISPLAY VIEW_UPDATES_TABLE"');
        } else {
            $RESULT = '<h3>No Updates Found!</h3>';
        }
        return $RESULT;

    }

    // ----------- function to view updates to a record and echo it ---------------
    public function ViewUpdates($table_id)
    {
        echo $this->ViewUpdatesText($table_id);
    }


    // ----------- function to add a record and output the code ---------------
    public function AddRecord()
    {
        echo $this->AddRecordText();
    }

    // ----------- function equivalent to trigger on add ---------------

    protected function TriggerAfterInsert($db_last_insert_id)
    {
        // extend this function to add DB calls after a record has been added
    }


    // ----------- function equivalent to trigger on update ---------------

    protected function TriggerAfterUpdate($id, $id_field='', $tables='', $span_where='', $joins='')
    {
        // extend this function to add DB calls after a record has been edited
    }

    // ----------- function to add a record to database ---------------

    public function AddDatabaseRecord($FormArray, $update_log=true)
    {
        // no errors, valid array

        //$result_message = '';

        if ($this->Unique_Fields) {
            // check for unique fields
            $fields = explode(',', $this->Unique_Fields);
            foreach ($fields as $field) {
                $parts = explode('__', $field);
                if (count($parts) == 1) {
                    $table = $this->Table;
                    $unique_field = $field;
                } else {
                    $table = $parts[0];
                    $unique_field = $parts[1];
                }

                if ( !$this->SQL->IsUnique($table, $unique_field, $FormArray[$field], 'active=1')) {
                    $var = $this->GetFieldTitleFromAlias($field);
                    $this->Error = $this->Field_Titles[$var] . ' already exits!';
                    break;
                }
            }
        }

        if (!$this->Error) {

            $span_tables = $this->Span_Tables;
            array_unshift($span_tables, $this->Table);

            $span_count = count($span_tables);
            $interation = 0;
            $join_value = '';


            try {
                $this->SQL->StartTransaction();  // start transaction

                foreach ($span_tables as $table) {
                    $interation++;
                    if (($span_count == 1)){
                        $fields = $this->SQL->Keys($FormArray) . ',created';
                        $values = $this->SQL->Values($FormArray) . ',NOW()';
                    } else {
                        $keys = array_keys($FormArray);
                        $fields_array = array();
                        $values_array = array();
                        foreach ($keys as $key) {
                            $key_table = strTo($key, '__');
                            if ($key_table == $table) {
                                $fields_array[] = strFrom($key, '__');
                                $values_array[] = $FormArray[$key];
                            }
                        }
                        $fields = $this->SQL->Keys($fields_array, false);
                        $values = $this->SQL->Values($values_array);
                        if ($interation == 1) {
                            $fields .= ',created';
                            $values .= ',NOW()';
                        } else {
                            $fields .= ",`{$this->Span_Tables_Join_Field}`";
                            $values .= ',' . $this->SQL->QuoteValue($join_value);
                        }
                    }

                    if ($this->SQL->AddRecord($table, $fields, $values)) {

                        $this->Insert_Id = $this->SQL->GetLastInsertId();

                        if ($interation == 1) {
                            $this->TriggerAfterInsert($this->Insert_Id);
                            $id_for_update_log = $this->Insert_Id;
                            $this->Last_Update_Id = $id_for_update_log;
                            $join_value = ($this->Span_Tables_Join_Field == $this->Index_Name)? $this->Insert_Id :
                                  $this->SQL->GetValue($this->Table, $this->Span_Tables_Join_Field,
                                  "{$this->Index_Name}=$id_for_update_log");

                        } else {
                            $id_for_update_log = 0;
                        }

                        if ($update_log and $id_for_update_log and ($interation == $span_count)) {
                            $new_record_array = $this->SQL->GetRecord($this->Table, '*', "`{$this->Table}`.{$this->Index_Name}=$id_for_update_log", $this->Span_Joins);
                            $this->UpdateChangeLog($id_for_update_log, 'ADDED', '', $new_record_array);
                        }

                    } else {
                        $this->Error .= 'DB Write Error';
                        throw new Exception('DB Write Error');
                    }
                }

                $this->SQL->TransactionCommit();

            } catch (Exception $e) {

                $this->Error = 'DB Write Error (Exception Caught)';
                $this->SQL->Rollback();

            }

        }
    }

     // ----------- function to set form arrays (define and use when database calls are needed, so the calls are only used when adding or editing)---------------
    public function SetFormArrays()
    {
        return;
    }

    public function SuccessfulAddRecord()
    {
        $table = $this->GetTableTitle();

        $close = $this->Close_On_Success? "top.parent.appformClose('appform' + dialogNumber);" : '';

        $dialog_id         = $this->GetEqValue('dialog');
        $idx               = $this->GetEqValue('idx');
        $return_function   = $this->GetEqValue('return_function');
        $return_parameters = $this->GetEqValue('return_parameters');
        
        /*
        echo "<br />dialog_id   ----> " . $dialog_id;
        echo "<br />idx   ----> " . $idx;
        echo "<br />return_function   ----> " . $return_function;
        echo "<br />return_parameters   ----> " . $return_parameters;
        exit();
        */
        
        if ($return_parameters) {
            $return_parameters = ',' . $return_parameters;
        }

        $return = '';
        if ($dialog_id) {
            if ($return_function) {
                $return = "
                if (parent.document.getElementById('appformIframe$dialog_id')) {
                    parent.document.getElementById('appformIframe$dialog_id').contentWindow.$return_function($this->Last_Update_Id$return_parameters);
                }";
            } elseif($idx) {
                $eq = Get('eq');
                $return = "
                if (parent.document.getElementById('appformIframe$dialog_id')) {
                    parent.document.getElementById('appformIframe$dialog_id').contentWindow.tableSearch('SHOW','$eq','$idx');
                }";

            }

        }

        AddScript(
            //"top.parent.setTopFlash('Record [$this->Last_Update_Id] Added to $table');
            "top.parent.setTopFlash('Record Added to $table');
            $return
            $close"
        );

        return '';
    }

    public function SuccessfulEditRecord($flash, $id, $id_field)
    {
        $table = $this->GetTableTitle();

        $idx       = $this->GetEqValue('idx');
        $dialog_id = $this->GetEqValue('dialog');
        $eq        = Get('eq');

        $script = ($dialog_id)? "if (parent.document.getElementById('appformIframe$dialog_id')) {
            parent.document.getElementById('appformIframe$dialog_id').contentWindow.rowUpdate('$eq', $idx, $id);
        }" : '';

        $close = $this->Close_On_Success? "top.parent.appformClose('appform' + dialogNumber);" : '';

        AddScript(
            "$script;
            top.parent.setTopFlash('$table Record [$flash] Updated');
            $close"
        );

        return '';
    }
    
    public function SuccessfulDeleteRecord()
    {
        $table              = $this->GetTableTitle();
        $close              = $this->Close_On_Success? "top.parent.appformClose('appform' + dialogNumber);" : '';
        
        /*
        $dialog_id          = $this->GetEqValue('dialog');
        $idx                = $this->GetEqValue('idx');
        $return_function    = $this->GetEqValue('return_function');
        $return_parameters  = $this->GetEqValue('return_parameters');
        
        if ($return_parameters) {
            $return_parameters = ',' . $return_parameters;
        }

        $return = '';
        if ($dialog_id) {
            if ($return_function) {
                $return = "
                if (parent.document.getElementById('appformIframe$dialog_id')) {
                    parent.document.getElementById('appformIframe$dialog_id').contentWindow.$return_function($this->Last_Update_Id$return_parameters);
                }";
            } elseif($idx) {
                $eq = Get('eq');
                $return = "
                if (parent.document.getElementById('appformIframe$dialog_id')) {
                    parent.document.getElementById('appformIframe$dialog_id').contentWindow.tableSearch('SHOW','$eq','$idx');
                }";

            }

        }
        */
        //$return
        AddScript(
            "top.parent.setTopFlash('Record Deleted from $table');
            
            $close"
        );

        return '';
    }

    // ----------- function to add a record (only creates a string)---------------

    public function AddRecordText()
    {
        global $AJAX;

        $this->Action = 'ADD';

        if ($AJAX) {
            $this->ProcessAjax();
        }

        $this->SetFormArrays();

        $this->Error = '';
        $RESULT = '';
        if (havesubmit($this->Add_Submit_Name)) {

            $this->Form_Array = ProcessFormNT(
                $this->Form_Data_Array_Add,
                $this->Error);

            $this->Form_Array = $this->PostProcessFormValues($this->Form_Array);

            if (!$this->Error) {
                if ($this->Demo) {
                    $RESULT .= "<h3>Demo Mode &mdash; Record Not Added</h3>\n";
                } else {
                    
                    if (!$this->Bypass_Form_Processing) {
                        $this->AddDatabaseRecord($this->Form_Array);
                    }
                    
                    if (!$this->Error) {
                        $RESULT .= $this->SuccessfulAddRecord();
                        return $RESULT;
                    }
                }
            }

        }
        if (!havesubmit($this->Add_Submit_Name) or $this->Error) {
            $RESULT .= WriteErrorText($this->Error);

            if (!$this->Error) {
                $this->SetDefaultValues();
            }
            $RESULT .= OutputForm($this->Form_Data_Array_Add, Post($this->Add_Submit_Name));
        }

        return $RESULT;
    }


    // ----- setup default values --------
    public function SetDefaultValues()
    {
        if (!empty($this->Default_Values)) {
            foreach ($this->Default_Values as $key=>$value) {
                Form_PostValue($key, $value);
            }
        }
    }

    // ----------- function to post process any form values ---------------
    public function PostProcessFormValues($FormArray)
    {
        // extend this function to process values -- simply return the array back
        return $FormArray;
    }

    public function GetSpanJoins()
    {
        $this->Span_Joins = '';
        if (!empty($this->Span_Tables)) {
            foreach ($this->Span_Tables as $table) {
                $this->Span_Joins .= "
                 LEFT JOIN `$table` ON
                `$table`.{$this->Span_Tables_Join_Field} = `{$this->Table}`.{$this->Span_Tables_Join_Field}";
            }
        }
    }

    // ----------- function to pre-poplulate a form from a record ---------------
    public function PrePopulateFormValues($id, $field='')
    {
        global $FormPrefix;

        if (empty($field)) {
            $field = $this->Index_Name;
        }

        // ------- prepopulate fields -------
        $record = array();
        $qid = $this->SQL->QuoteValue($id);
        $record[$this->Table] = $this->SQL->GetRecord($this->Table, '*', "`$this->Table`.`$field`=$qid");

        if (!empty($this->Span_Tables)) {
            $join_id = $this->SQL->GetValue($this->Table, $this->Span_Tables_Join_Field, "`$this->Table`.`$field`='$id'");
            foreach ($this->Span_Tables as $table) {
                $record[$table] = $this->SQL->GetRecord($table, '*', "`$table`.`$this->Span_Tables_Join_Field`='$join_id'");
            }
        }

        // if ($this->Autocomplete_Fields) {
            // // ------  setup Autocomplete fields with listed as assoc array: field => 'table|id|namefield'

            // foreach ($this->Autocomplete_Fields as $key => $list) {
                // if (empty($this->Span_Tables)) {
                    // $this_id  = ArrayValue($record[$this->Table], $key);
                // } else {
                    // $test_key = strFrom($key, '__');
                    // $test_table = strTo($key, '__');
                    // $this_id  = ArrayValue($record[$test_table], $test_key);
                // }

                // if ($this_id) {
                    // $parts       = explode('|', $list);
                    // $table       = $parts[0];
                    // $link_id     = $parts[1];
                    // $title_field = $parts[2];
                    // $joins       = (count($parts) > 3)? $parts[3] : '';

                    // $link_id = $this->SQL->QuoteKey($link_id);
                    // $value = $this->SQL->GetValue($table, $title_field, "$link_id='$this_id'", $joins);
                    // if ($value) $_POST["AC_$FormPrefix$key"] = $value;
                // }
            // }
        // }

        $this->Record_Found = false;
        if ($record) {
            if (empty($this->Span_Tables)) {
                if (!empty($record[$this->Table])) {
                    foreach ($record[$this->Table] as $key => $value) {
                        $_POST[$FormPrefix.$key] = $value;
                    }
                    $this->Record_Found = true;
                } else {
                }
            } else {
                foreach ($record as $table => $array) {
                    if ($array) {
                        foreach ($array as $key => $value) {
                            $_POST[$FormPrefix.$table . '__' . $key] = $value;
                        }
                        $this->Record_Found = true;
                    }
                }
            }
        }
    }

    // ----------- function edit a record and ouput the code ---------------
    public function EditRecord($id, $id_field='')
    {
        if (empty($id_field)) {
            $id_field = $this->Index_Name;
        }
        echo $this->EditRecordText($id, $id_field);
    }

    // ----------- function for AJAX for Add/Edit ---------------
    public function ProcessAjax()
    {
        exit;  // <<----------- this should exit
    }

    // ----------- function to edit a record and only returns a string ---------------
    public function EditRecordText($id, $id_field='')
    {
        global $AJAX;

        $this->Action = 'EDIT';
        $this->Error = '';

        if (empty($id_field)) {
            $id_field = $this->Index_Name;
        }

        $this->Edit_Id = $id;
        $this->Edit_Id_Field = $id_field;

        if ($AJAX) {
            $this->ProcessAjax();
        }

        $RESULT = '';

        if (!havesubmit($this->Edit_Submit_Name)) {
            // ------- prepopulate fields -------
            $this->PrePopulateFormValues($id, $id_field);
            if (!$this->Record_Found) {
                $RESULT .= WriteErrorText('Record Not Found');
                return $RESULT;
            }
        }

        $this->SetFormArrays();

        // add an updated hidden field
        if (is_array($this->Form_Data_Array_Edit)) {
            $end_index = array_search('endform', $this->Form_Data_Array_Edit);
            if ($end_index) {
                $this->Form_Data_Array_Edit[$end_index] = 'hidden|updated';
                $this->Form_Data_Array_Edit[] = 'endform';
            }
        }


        if (HaveSubmit($this->Edit_Submit_Name)) {

            $this->Form_Array = ProcessFormNT($this->Form_Data_Array_Edit, $this->Error);

            // check updated value
            if (!empty($this->Form_Array['updated'])) {
                $qid = $this->SQL->QuoteValue($id);
                $last_update_value = $this->SQL->GetValue($this->Table, 'updated', "`$this->Table`.`$id_field`=$qid");
                if ($last_update_value > $this->Form_Array['updated']) {
                    $this->Error = 'A newer version of this record exists. You will need to reload the record to make edits.';
                }
                unset($this->Form_Array['updated']);
            }

            $this->Form_Array = $this->PostProcessFormValues($this->Form_Array);

            
            if (!$this->Bypass_Form_Processing) {
                
                if ($this->Unique_Fields) {
                    // check for unique fields
                    $fields = explode(',', $this->Unique_Fields);
                    foreach ($fields as $field) {
                        
                        $parts = explode('__', $field);
                        if (count($parts) == 1) {
                            $table          = $this->Table;
                            $unique_field   = $field;
                        } else {
                            $table          = $parts[0];
                            $unique_field   = $parts[1];
                        }

                        $qid = $this->SQL->QuoteValue($id);
                        if (isset($this->Form_Array[$field])) {
                            if ( !$this->SQL->IsUnique($table, $unique_field, $this->Form_Array[$field], "`$table`.`$id_field`!=$qid AND active=1")) {
                                $var            = $this->GetFieldTitleFromAlias($field);
                                $this->Error    = $this->Field_Titles[$var] . ' already exits!';
                                break;
                            }
                        }
                    }
                }

                if ($this->Demo) {
                    $RESULT .= "<h3>Demo Mode &mdash; Record Not Updated</h3>\n";
                    $RESULT .= $this->ViewRecordText($id, '', $id_field);
                    AddFlash("Demo Mode &mdash; Record [$id] Not Updated");

                } elseif (!$this->Error) {

                    $joins = '';

                    $tables = $this->Table;

                    if ( empty($this->Span_Tables) ) {
                        $key_values = $this->SQL->KeyValues($this->Form_Array);
                    } else {
                        $post_array = array();
                        foreach ($this->Form_Array as $key => $value) {
                            $key = str_replace('__', '.', $key);
                            $post_array[$key] = $value;
                        }
                        $key_values = $this->SQL->KeyValues($post_array);

                        foreach ($this->Span_Tables as $table) {
                            $tables .= ",$table";
                        }
                    }

                    try {
                        $this->SQL->StartTransaction();  // start transaction

                        // ----- get previsous values for log -----
                        $qid = $this->SQL->QuoteValue($id);
                        $old_record_array = $this->SQL->GetRecord($this->Table,'*', "`$this->Table`.`$id_field`=$qid", $this->Span_Joins);
                        $change_id = $old_record_array[$this->Index_Name];

                        $span_where = '';
                        if ($this->Span_Tables) {
                            $span_id = $old_record_array[$this->Span_Tables_Join_Field];
                            foreach ($this->Span_Tables as $table) {
                                $span_where .= " AND `$table`.`$this->Span_Tables_Join_Field`='$span_id'";
                            }
                        }

                        if($this->SQL->UpdateRecord($tables, $key_values, "`$this->Table`.`$id_field`=$qid$span_where", $joins)){
                            $flash = (!empty($this->Form_Array[$this->Flash_Field]))? $this->Form_Array[$this->Flash_Field] : $id;

                            $this->TriggerAfterUpdate($id, $id_field, $tables, $span_where, $joins);
                            if (!$this->Error) {
                                // ----- update log -----
                                $new_record_array = $this->SQL->GetRecord($this->Table,'*', "`$this->Table`.`$id_field`=$qid", $this->Span_Joins);
                                $this->UpdateChangeLog($change_id, 'UPDATE', $old_record_array, $new_record_array);

                                $RESULT .= $this->SuccessfulEditRecord($flash, $id, $id_field);

                            } else {
                                $this->Error .= 'DB Trigger Write Error';
                                throw new Exception('DB Write Error');
                            }

                        } else {
                            $this->Error .= 'DB Write Error';
                            throw new Exception('DB Write Error');
                        }

                        $this->SQL->TransactionCommit();

                    } catch (Exception $e) {
                        $this->Error = 'DB Write Error (Exception Caught)';
                        $this->SQL->Rollback();
                    }
                }
            } // end this->Bypass_Form_Processing) check
        }


        if (!havesubmit($this->Edit_Submit_Name) or $this->Error) {
            $RESULT .= WriteErrorText($this->Error);
            $RESULT .= OutputForm($this->Form_Data_Array_Edit, Post($this->Edit_Submit_Name));
        }
        return $RESULT;
    }


    // ----------- function to inactivate a record ---------------
    public function Inactivate($id, $id_field='')
    {
        if (empty($id_field)) {
            $id_field = $this->Index_Name;
        }

        $qid = $this->SQL->QuoteValue($id);
        $old_record_array = $this->SQL->GetRecord($this->Table,'*', "$id_field=$qid");
        if ($old_record_array['active'] == 0) {
            return 2;
        }

        if($this->SQL->UpdateRecord($this->Table,'active=0',"`$id_field`=$qid")){
            // ----- update log -----
            $new_record_array = $this->SQL->GetRecord($this->Table,'*', "$id_field=$qid");
            $this->UpdateChangeLog($id, 'INACTIVATE', $old_record_array, $new_record_array);
            return 1;

        } else {
            $this->Error .= 'DB Write Error';
            return 0;
        }
    }


    // ----------- function to inactivate a record ---------------
    public function DeleteRecord($id, $id_field='')
    {
        if (empty($id_field)) {
            $id_field = $this->Index_Name;
        }

        $qid = $this->SQL->QuoteValue($id);
        $old_record_array = $this->SQL->GetRecord($this->Table, '*', "$id_field=$qid");

        if ($this->SQL->DeleteRecord($this->Table, "$id_field=$qid")) {
            // ----- update log -----
            $new_record_array = $this->SQL->GetRecord($this->Table,'*', "$id_field=$qid");
            $this->UpdateChangeLog($id, 'DELETED', $old_record_array, '');
            return 1;

        } else {
            $this->Error .= 'DB Delete Error';
            return 0;
        }
    }


    public function GetIdName($id)
    {
        //return str_replace(array(' ', ':', '.', '-'), '_', $id);
        return HexEncodeString($id);
    }

    // ----------- function to save custom searches AJAX ---------------
    public function AjaxCustomSearch($posted_data, $action, $idx)
    {

        $RESULT = '';

        // ---- get name ----
        $search_name = ArrayValue($posted_data, 'CUSTOM_SEARCH_NAME'.$idx);
        //$search_name = str_replace("'", '_', $search_name);

        // ---- check if name exists
        $id_name = $this->Custom_Search_Table . '_id';

        $admin_id = $this->GetUserId();

        if (!$admin_id) {
            return;
        }

        $q_search_name = $this->SQL->QuoteValue($search_name);
        $search_id = $this->SQL->GetValue(array(
            'table' => $this->Custom_Search_Table,
            'key'   => $id_name,
            'where' => "search_name=$q_search_name AND class_name='$this->Class_Name' AND `{$this->Admin_Id_Name}`=$admin_id"
        ));

        if ($action == 'delete') {
            if ($search_id) {
                if (!$this->SQL->UpdateRecord($this->Custom_Search_Table, 'active=0', "`$id_name`=$search_id")) {
                    echo "ERROR: Could Not Delete Custom Search [$search_name]";
                }
            } else {
                echo "ERROR: Custom Search Not Found [$search_name]";
            }
            return;
        }

        // ---- get order ----

        $order = '';
        $order_fields = array();
        $order_field = ArrayValue($posted_data, 'TABLE_ORDER'.$idx);
        list($base, $suffix) = explode(' ', $order_field . ' ');
        if ($order_field) {
            $order_fields[$base] = $suffix;
        }
        $order_field = ArrayValue($posted_data, 'TABLE_2ORDER'.$idx);
        list($base, $suffix) = explode(' ', $order_field . ' ');
        if ($order_field and !array_key_exists($base, $order_fields)) {
            $order_fields[$base] = $suffix;
        }
        $order_field = ArrayValue($posted_data, 'TABLE_3ORDER'.$idx);
        list($base, $suffix) = explode(' ', $order_field . ' ');
        if ($order_field and !array_key_exists($base, $order_fields)) {
            $order_fields[$base] = $suffix;
        }

        if (count($order_fields) >0) {
            foreach($order_fields as $base => $suffix) {
                if (!empty($suffix)) {
                    $suffix = " $suffix";
                }
                $comma = (!empty($order))? ',' : '';
                $order .= $comma . $base . $suffix;
            }
        }


        $name  = $this->GetIdName($search_name);
        $QUERY = "{name:'$name', order:'$order', selections:[\n";

        //-------- display fields --------
        $DisplayFields = '';

        $field_list = array();
        foreach ($posted_data as $key=>$value) {
            if (strpos($key, 'TABLE_SEARCH_OPERATOR') !== false) {
                $field_list[] = strFrom($key, "TABLE_SEARCH_OPERATOR{$idx}_");
            }
        }

        $have_field = false;
        foreach ($field_list as $field) {
            $selector = ArrayValue($posted_data, "TABLE_SEARCH_OPERATOR{$idx}_$field");
            $filter   = ArrayValue($posted_data, "TABLE_SEARCH_VALUE{$idx}_$field");
            $view     = ArrayValue($posted_data, "TABLE_SEARCH_DISPLAY{$idx}_$field");

            if ((($selector != 'All') and ($selector != 1)) or ($view)) {
                $view = ($view)? 1 : 0;
                $filter = addslashes($filter);
                $QUERY .= "{field: '$field', selector: '$selector', filter : '$filter', view : $view},\n";
                $have_field = true;
            }
        }

        if ($have_field) {
            $QUERY = substr($QUERY, 0, -2);
        }
        $QUERY .= "\n]}";


        if ($search_id) {
            $db_result = $this->SQL->UpdateRecord(
                $this->Custom_Search_Table,
                "`search_data`=". $this->SQL->QuoteValue($QUERY),
                "`$id_name`=$search_id"
            );
        } else {
            $keys   = "`{$this->Admin_Id_Name}`,`class_name`,`search_name`,`search_data`,`active`,`created`";

            $values_array = array(
                $admin_id,
                $this->Class_Name,
                $search_name,
                $QUERY,
                1,
                'NOW()'
            );
            $values = $this->SQL->Values($values_array);
            $db_result = $this->SQL->AddRecord($this->Custom_Search_Table, $keys, $values);
        }

        if (!$db_result) {
            $RESULT =  'ERROR: Database Failed to Update!';
        } else {
            $id_name = $this->GetIdName($search_name);
            $RESULT = qqn("<a href=`#` id=`CUSTOM_SEARCH{$idx}_$id_name`
            onclick=`tableCustomSearchSelect($idx, $QUERY); return false;`>$search_name</a>");
        }

        echo $RESULT;
    }



    // ----------- function to display search operators, used to create search selections ---------------
    public function DisplaySearchSelectionOperators($var)
    {
        $idx = $this->Idx;
        $var = $this->GetFieldAlias($var);

        if ($var != 'active') {

            $onchange = "onchange=\"changeTableSearchFilterBox(this.options[this.selectedIndex].value, '$idx', '$var');\"";

            $RESULT = qqn("
            <select class=`table_search_display_operators` name=`TABLE_SEARCH_OPERATOR{$idx}_$var` id=`TABLE_SEARCH_OPERATOR{$idx}_$var` $onchange>");
            foreach ($this->Search_Selection_Operators as $key) {
                $selected = (Post("TABLE_SEARCH_OPERATOR{$idx}_$var") == $key)? ' selected="selected"' : '';
            $RESULT .= "
                <option value=\"$key\"$selected>$key</option>";
            }
            $RESULT .= '
            </select>';
        } else {
            $RESULT = qqn("
            <select class=`table_search_display_operators` name=`TABLE_SEARCH_OPERATOR{$idx}_active`
                id=`TABLE_SEARCH_OPERATOR{$idx}_active`
                onchange=`$('#TABLE_SEARCH_DISPLAY{$idx}_active').attr('checked', this.options[this.selectedIndex].value > 1);
                changeSearchSelectRow('TABLE_SEARCH_DISPLAY{$idx}_active');
            ;`>");
            foreach ($this->Active_Keys as $value=>$key) {
                $selected = (Post("TABLE_SEARCH_OPERATOR{$idx}_active") == $value)? ' selected="selected"' : '';
                $RESULT .= "<option value=\"$value\"$selected>$key</option>\n";
            }
            $RESULT .= '
            </select>';
        }
        return $RESULT;
    }

    // ----------- function to display a search tab ---------------
    public function DisplaySearchTab()
    {
        $idx = $this->Idx;

        $default_fields = explode(',', $this->Default_Fields);
        TrimArray($default_fields);

        //$custom_queries = "var savedQueries = {default : {order : '$this->Default_Sort', selections : [\n";
        $default_query = "{name: '', order : '$this->Default_Sort', selections : [\n";


        foreach ($default_fields as $field) {
            if ($field != '|') {
                $field = $this->GetFieldAlias($field);
                $_POST["TABLE_SEARCH_DISPLAY{$idx}_$field"] = 1;
                $default_query .= "{field: '$field', selector: 'All', filter : '', view : 1},\n";
            }
        }

        $default_query = substr($default_query, 0, -2);
        $default_query .= "\n]}";

        //------------ Get Custom Searches -----------

        $admin_id = $this->GetUserId();
        $search_list = '';

        if ($admin_id) {

            $searches = $this->SQL->GetArrayAll($this->Custom_Search_Table, 'search_name,search_data',
                "class_name='$this->Class_Name' AND `{$this->Admin_Id_Name}`=$admin_id AND `active`=1");

            if ($searches) {
                foreach ($searches as $row) {
                    $search_name   = $row['search_name'];
                    $id_name = $this->GetIdName($search_name);
                    $search_data   = $row['search_data'];
                    $search_list .= qqn("<a href=`#` id=`CUSTOM_SEARCH{$idx}_$id_name`
                        onclick=`tableCustomSearchSelect($idx, $search_data); return false;`>$search_name</a>");
                }
            }
        }

        $dialog_id = $this->GetEqValue('dialog');
        $eq = EncryptQuery("class={$this->Class_Name};idx=$this->Idx;dialog=$dialog_id;parameters={$this->Parameter_String}");

        if ($this->Set_Custom_Search) {
            $custom_search = qqn("
                <div class=`CUSTOM_SEARCHES` id=`CUSTOM_SEARCHES$idx`>$search_list</div>
                <div class=`SEARCH_NAME_BOX`>
                Search Name<br />
                <input type=`text` id=`CUSTOM_SEARCH_NAME$idx` name=`CUSTOM_SEARCH_NAME$idx` size=`12` maxlength=`40` value=`` />
                <br />
                <input type=`button` class=`SEARCH_BUTTON` onclick=`tableCustomSearchSave('$eq', $idx);` value=`Save` />
                <input type=`button` class=`SEARCH_BUTTON` onclick=`tableCustomSearchDelete('$eq', $idx)` value=`Delete` />
                </div>");
        } else {
            $custom_search = '';
        }

        $RESULT = qqn("
            <form name=`TABLE_SEARCH_SELECTION{$idx}` id=`TABLE_SEARCH_SELECTION{$idx}` method=`post` action=`$this->Action_Link`>
            <table class=`TABLE_SEARCH_TAB_WRAPPER` border=`0` cellspacing=`0` cellpadding=`0`>
            <tbody>
            <tr>
            <td>

            <table class=`TABLE_SEARCH_CUSTOM` border=`0` cellspacing=`1` cellpadding=`0`>
            <tbody>
                <tr><th>Selections</th></tr>
                <tr>
                <td>
                <a href=`#` onclick=`searchSelectAll(); return false;`>Display All</a>
                <a href=`#` onclick=`searchClearDisplay(); return false;`>Clear Display</a>
                <a href=`#` onclick=`searchClearAll(); return false;`>Clear All</a>
                <a href=`#` onclick=`tableCustomSearchSelect({$idx}, {$default_query}); return false;`>Default</a>
                {$custom_search}
                </td>
                </tr>
                </tbody>
            </table>
            </td>
            <td>
            <table class=`TABLE_SEARCH_TAB` border=`0` cellspacing=`1` cellpadding=`0`>
            <tbody>
            <tr>
              <th>Field</th><th>Select</th><th>Value</th><th>Display</th><th>Order (A/D)</th>
            </tr>
        ");

        if (!empty($this->Field_Titles)) {
            foreach ($this->Field_Titles as $field => $title) {
                
                
                if ($title == '|') {
                    $style_tr   = "style='background-color:#ccc;'";
                    $style_td   = "style='font-size:14px; font-weight:bold; background-color:#ccc; border-bottom:1px solid #000;'";
                    $RESULT    .= "<tr {$style_tr}><td colspan='5' {$style_td}></br>{$field}</td></tr>";
                    
                } else {
                    
                    $complete_field = $field;
                    $field = $this->GetFieldAlias($field);

                    $select   = $this->DisplaySearchSelectionOperators($field);
                    $value    = TransformContent(Post("TABLE_SEARCH_VALUE{$idx}_$field"),'TS');
                    if ($field == 'active') {
                        $input = '';
                        $span  = ' colspan="2"';
                    } else {
                        $input = "<td><input class=\"SEARCH_FILTER_VALUE\" type=\"text\" id=\"TABLE_SEARCH_VALUE{$idx}_$field\" name=\"TABLE_SEARCH_VALUE{$idx}_$field\" value=\"$value\" size=\"40\" /></td>";
                        $span  = '';
                    }
                    if (Post("TABLE_SEARCH_DISPLAY{$idx}_$field") == 1) {
                        $checked = ' checked="checked"';
                        $row_class = ' class="SEARCH_SELECT_ROW_SELECTED"';
                    } else {
                        $checked = '';
                        $row_class = ' class="SEARCH_SELECT_ROW"';
                    }
                    $checkbox = "<input type=\"checkbox\" class=\"SEARCH_DISPLAY\"
                       id=\"TABLE_SEARCH_DISPLAY{$idx}_$field\"
                       name=\"TABLE_SEARCH_DISPLAY{$idx}_$field\" value=\"1\"$checked
                       onclick=\"changeSearchSelectRow('TABLE_SEARCH_DISPLAY{$idx}_$field');\" />";

                    list($sort1, $sort2, $sort3) = explode(',', $this->Default_Sort . ',,');

                    $checked1   = (((Post("TABLE_ORDER{$idx}") == '') and ($field == $sort1)) or (Post("TABLE_ORDER{$idx}") == $field))? ' checked="checked"' : '';
                    $checked2   = (((Post("TABLE_2ORDER{$idx}") == '') and ($field == $sort2)) or (Post("TABLE_2ORDER{$idx}") == $field))? ' checked="checked"' : '';
                    $checked3   = (((Post("TABLE_3ORDER{$idx}") == '') and ($field == $sort3)) or (Post("TABLE_3ORDER{$idx}") == $field))? ' checked="checked"' : '';
                    
                    $radio      = "<input type=\"radio\" class=\"TABLE_SEARCH_RADIO\" id=\"TABLE_ORDER{$idx}_$field\" name=\"TABLE_ORDER$idx\" value=\"$field\"$checked1 />\n";
                    $radio     .= "<input type=\"radio\" class=\"TABLE_SEARCH_RADIO\" id=\"TABLE_2ORDER{$idx}_$field\" name=\"TABLE_2ORDER$idx\" value=\"$field\"$checked2 />\n";
                    $radio     .= "<input type=\"radio\" class=\"TABLE_SEARCH_RADIO\" id=\"TABLE_3ORDER{$idx}_$field\" name=\"TABLE_3ORDER$idx\" value=\"$field\"$checked3 /><br />\n";

                    $checked1   = ( ((Post("TABLE_ORDER{$idx}") == '') and ("$field DESC" == $sort1)) or (Post("TABLE_ORDER{$idx}") == "$field DESC") )? ' checked="checked"' : '';
                    $checked2   = ( ((Post("TABLE_2ORDER{$idx}") == '') and ("$field DESC" == $sort2)) or (Post("TABLE_2ORDER{$idx}") == "$field DESC") )? ' checked="checked"' : '';
                    $checked3   = ( ((Post("TABLE_3ORDER{$idx}") == '') and ("$field DESC" == $sort3)) or (Post("TABLE_3ORDER{$idx}") == "$field DESC") )? ' checked="checked"' : '';

                    $radio     .= "<input type=\"radio\" class=\"TABLE_SEARCH_RADIO\" id=\"TABLE_ORDER{$idx}_{$field}_DESC\" name=\"TABLE_ORDER$idx\" value=\"$field DESC\"$checked1 />\n";
                    $radio     .= "<input type=\"radio\" class=\"TABLE_SEARCH_RADIO\" id=\"TABLE_2ORDER{$idx}_{$field}_DESC\" name=\"TABLE_2ORDER$idx\" value=\"$field DESC\"$checked2 />\n";
                    $radio     .= "<input type=\"radio\" class=\"TABLE_SEARCH_RADIO\" id=\"TABLE_3ORDER{$idx}_{$field}_DESC\" name=\"TABLE_3ORDER$idx\" value=\"$field DESC\"$checked3 />\n";

                    $RESULT .= "
                    <tr$row_class>
                        <td align=\"right\">$title</td>
                        <td$span>$select</td>
                        $input
                        <td align=\"center\">$checkbox</td>
                        <td align=\"center\" style=\"white-space:nowrap\">$radio</td>
                    </tr>";
                    
                } 
            }
        }

         $RESULT .= '
            </tbody>
            </table>
            </td></tr></tbody></table>
            </form>';

        return $RESULT;
    }


    // ----------- function to display a search tab ---------------
    public function DisplaySearchTabHidden()
    {
        $idx = $this->Idx;
        $default_fields = explode(',', $this->Default_Fields);
        TrimArray($default_fields);

        $RESULT = qqn("<form name=`TABLE_SEARCH_SELECTION{$idx}` id=`TABLE_SEARCH_SELECTION{$idx}` method=`post` action=`$this->Action_Link`>");

        foreach ($default_fields as $field) {
            $field = $this->GetFieldAlias($field);
            $RESULT .= qqn("<input type=`hidden` name=`TABLE_SEARCH_DISPLAY{$idx}_$field` id=`TABLE_SEARCH_DISPLAY{$idx}_$field` value=`1` />");
            $RESULT .= qqn("<input type=`hidden` name=`TABLE_SEARCH_OPERATOR{$idx}_$field` id=`TABLE_SEARCH_OPERATOR{$idx}_$field` value=`All` />");
        }

        $dialog_id = $this->GetEqValue('dialog');
        $eq = EncryptQuery("class={$this->Class_Name};idx=$this->Idx;dialog=$dialog_id;parameters={$this->Parameter_String}");

        list($sort1, $sort2, $sort3) = explode(',', $this->Default_Sort . ',,');
        $RESULT .= "<input type=\"hidden\" name=\"TABLE_ORDER$idx\" id=\"TABLE_ORDER{$idx}\" value=\"$sort1\" />\n";
        $RESULT .= "<input type=\"hidden\" name=\"TABLE_2ORDER$idx\" id=\"TABLE_2ORDER{$idx}\" value=\"$sort2\" />\n";
        $RESULT .= "<input type=\"hidden\" name=\"TABLE_3ORDER$idx\" id=\"TABLE_3ORDER{$idx}\" value=\"$sort3\" />\n";
        $RESULT .= '</form>';

        return $RESULT;
    }

    // ----------- function to display a tab for editing (not currently used) ---------------
    public function DisplayEditViewTab()
    {
        return '<div id="DISPLAY_VIEW_TAB"></div>';
    }


    // ----------- function output a table as a report, not a searchable table ---------------
    public function ViewReportText($keys = '', $conditions = '')
    {
        $idx = $this->Idx;
        $this->SetNoEditLinks();
        $this->Default_Table_Options .= ' id="TABLE_DISPLAY$idx"';
        if(empty($keys)) {
            $keys = $this->SQL->QuoteTables($this->Default_Fields);
        }
        $order = $this->Table . '.' . $this->Index_Name;
        $num_rows  = $this->SQL->Count($this->Table, $conditions, $this->Joins);

        $array = $this->SQL->GetArray($this->Table, $keys . ',`' . $this->Table . '`.`' . $this->Index_Name .'` AS TID', $conditions, $order, 0, $this->Default_List_Size, $this->Joins, false);
        $RESULT = $this->OutputTable($array, $num_rows, 1, $this->Default_List_Size);
        return $RESULT;
    }

    // ----------- function to echo the above ---------------
    public function ViewReport($keys = '', $conditions = '')
    {
        echo $this->ViewReportText($keys, $conditions);
    }

    // ----------- function to export a table to CSV ---------------
    public function ExportCsv($query, $disk_filename='', $XML=false)
    {
        if ($this->Demo) {
            Mtext('Demo Mode', 'Demo Mode &mdash Export Not Operational');  // does not return;
        }

        $save_to_file = !empty($disk_filename);

        $query = str_replace(", `$this->Table`.`$this->Index_Name` AS TID", '', $query);

        $field_values = $this->Field_Values;
        $field_titles = $this->Field_Titles;

        $db_query = $this->SQL->Query('CLASS ExportCSV', $query);

        if (!$db_query) {
            if (!$save_to_file) {
                echo "<h1>Database Query Failed!</h1>";
                //writedbquery();
            }
            $this->Error = 'Database Query Failed';
            return;
        }

        if ($XML) {
            $EX = new Lib_ExcelXml;
        }

        if (!$save_to_file) {
            $filename = $this->Table . date('YmdHis');
            if ($XML) {
                $EX->OutputFileHeaders($filename);
            } else {
                header("Content-type: application/octet-stream");
                header("Content-Disposition: attachment; filename=\"$filename.csv\"");
                header("Content-Type: text/csv, charset=utf-8; encoding=utf-8");
            }
        }

        $need_header = true;
        while ($row = $db_query->fetch()) {

            if ($need_header) {
                //output header

                if ($XML) {
                    $out  = $EX->StartWorkbook();
                    $out .= $EX->AddProperties($this->User_Name);
                    $out .= $EX->AddHeaderStyle($this->Export_Xml_Header_Text_Color, $this->Export_Xml_Header_Cell_Color, 1);
                    $out .= $EX->AddStyles();

                    $out .= $EX->StartWorksheet($this->GetTableTitle());
                    //$out .= $EX->AddAutoWidthColumns(count($row));
                    $out .= $EX->StartRow();
                } else {
                    $out = '';
                }

                foreach ($field_titles as $key => $value) {
                    $newkey = $this->GetFieldAlias($key);

                    if(array_key_exists($newkey, $row)) {
                        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                        if ($XML) {
                            $out .= $EX->AddCell($value, '' , 1);

                        } else {
                            $out .= "\"$value\",";
                        }
                    }
                }

                if ($XML) {
                    $out .= $EX->EndRow();
                } else {
                    $out = substr($out, 0, -1) . "\n";
                }

                if ($save_to_file) {
                    //write to disk file
                    append_file($disk_filename, $out);
                } else {
                    echo $out;
                }
                $need_header = false;
            }

            if ($XML) {
                $out = $EX->StartRow();
            } else {
                $out = '';
            }

            foreach ($field_titles as $key => $value) {
                $newkey = $this->GetFieldAlias($key);

                if (isset($row[$newkey])) {
                    $field_value = $row[$newkey];
                    $outvalue = (empty($field_values[$newkey][$field_value]))? $field_value
                                : $field_values[$newkey][$field_value];
                    $outvalue = html_entity_decode($outvalue, ENT_QUOTES, 'UTF-8');
                    $this->ProcessExportCell($newkey, $outvalue);

                    if ($XML) {
                        $out .= $EX->AddCell($outvalue);
                    } else {
                        $outvalue = mb_ereg_replace('"', '""', $outvalue);
                        $out .= "\"$outvalue\",";
                    }
                } elseif (array_key_exists($newkey, $row)) {
                    // may have a null result
                    if ($XML) {
                        $out .= $EX->AddCell('');
                    } else {
                        $out .= '"",';
                    }
                }
            }

            if ($XML) {
                $out .= $EX->EndRow();
            } else {
                $out = substr($out, 0, -1) . "\n";
            }

            if ($save_to_file) {
                //write to disk file
                append_file($disk_filename, $out);
            } else {
                echo $out;
            }
        }

         if ($XML) {
            $out = $EX->EndWorksheet();
            $out .= $EX->EndWorkbook();
            if (!$save_to_file) {
                echo $out;
            } else {
                append_file($disk_filename, $out);
            }
        }

        if ($save_to_file) {
            if (file_exists($disk_filename)) {
                chmod($disk_filename, 0666);
            }
        }
    }

    // ----------- function to output a table (returns a string) ---------------
    public function ListTableText()
    {
        $idx = $this->Idx;
        $RESULT = '';

        $link_group_name = 'tab'.$idx.'link';
        $tab_group_name  = 'tab'.$idx;

        $dialog_id = Get('DIALOGID');
        $eq = EncryptQuery("class={$this->Class_Name};idx=$this->Idx;dialog=$dialog_id;parameters={$this->Parameter_String}");

        if (!$this->Use_Selection_Tab) {
            $RESULT .= $this->DisplaySearchTabHidden();
            $RESULT .= $this->OutputTable('START', 0, 1, $this->Default_List_Size);
            addScript("\$(function() { tableSearch('HOME', '$eq', $idx); });");
            return $RESULT;
        }

        $RESULT = qqn("
        <br />
        <a id=`{$link_group_name}1` class=`tablink` href=`#` onclick=`$this->Tab_Search_Table_Function(1,'$tab_group_name'); return false;`>Search</a>
        <a id=`{$link_group_name}2` class=`tabselect`   href=`#` onclick=`$this->Tab_Search_Table_Function(2,'$tab_group_name', '$eq', '$idx'); return false;`>Table</a>
        <div class=`tabspacer`>&nbsp;</div>
        <div class=`tabfolder`>");

        $RESULT .= '<div id="' . $tab_group_name . '1" style="display:none;">';
        $RESULT .= $this->DisplaySearchTab();
        $RESULT .= '</div>';


        $RESULT .=  '
        <div id="'.$tab_group_name.'2" style="display:block;">';
            $RESULT .= $this->OutputTable('START', 0, 1, $this->Default_List_Size);
            addScript("\$(function() { tableSearch('HOME', '$eq', $idx); });");
        $RESULT .=  '
        </div>
        </div>';
        return $RESULT;
    }

    // ----------- function to echo a table ---------------
    public function ListTable()
    {
        echo $this->ListTableText();
    }


    // ----------- function to process the row/page selection for table pagination ---------------
    public function RowSelect($num_rows, $start_row, $row_count)
    {
        $idx        = $this->Idx;
        $did        = $this->Dialog_Id;
        $did2       = $this->GetEqValue('dialog');
        $dialog_id  = Get('DIALOGID');
        $eq         = EncryptQuery("class={$this->Class_Name};idx=$this->Idx;dialog=$dialog_id;parameters={$this->Parameter_String}");

        $RESULT = qqn("<div class=`rowselect`>
            <input type=`hidden` id=`NUMBER_ROWS$idx` name=`NUMBER_ROWS$idx` value=`$num_rows` />");
        
        //$RESULT .= qqn("({$did}) ({$did2}) ({$dialog_id})</br>");
        
        if ($start_row > $num_rows) $start_row = 1;
        if (($num_rows > $row_count) or ($start_row > 1)){
            $RESULT .= qqn("
            <p>Found: " . number_format($num_rows) . " records</p>
            <p>Start Row: <input type=`text` size=`6` maxlength=`10` id=`TABLE_STARTROW$idx` name=`TABLE_STARTROW$idx` value=`$start_row` />&nbsp;&nbsp;
            Rows per Page: <input type=`text` size=`6` maxlength=`10` id=`TABLE_ROWS$idx` name=`TABLE_ROWS$idx` value=`$row_count` />&nbsp;&nbsp;
            <input class=`SEARCH_BUTTON` type=`button` value=`Show` onclick=`tableSearch('SHOW','$eq','$idx');` />&nbsp;");
            if ($start_row > 1) {
                $RESULT .= qq("<input class=`SEARCH_BUTTON` type=`button` value=`&lt;&lt;` onclick=`tableSearch('HOME','$eq','$idx');` />&nbsp;");
            }
            if ($start_row > 1) {
                $RESULT .= qq("<input class=`SEARCH_BUTTON` type=`button` value=`&nbsp;<&nbsp;` onclick=`tableSearch('PREVIOUSPAGE','$eq','$idx');` />&nbsp;");
            }
            if ($num_rows >= $start_row+$row_count) {
                $RESULT .= qq("<input class=`SEARCH_BUTTON` type=`button` value=`&nbsp;&gt;&nbsp;` onclick=`tableSearch('NEXTPAGE','$eq','$idx');` />&nbsp;");
            }
            if ($num_rows >= $start_row+$row_count) {
                $RESULT .= qqn("<input class=`SEARCH_BUTTON` type=`button` value=`&gt;&gt;` onclick=`tableSearch('END','$eq','$idx');` />");
            }

            if ($num_rows < 1001) {
                $RESULT .= qqn("<input class=`SEARCH_BUTTON` type=`button` value=`All` onclick=`$('#TABLE_STARTROW$idx').val(1); $('#TABLE_ROWS$idx').val(1000); tableSearch('SHOW','$eq','$idx');` />");
            }

            $RESULT .= '</p></div>';
        } else {
            $RESULT .= qqn("
              <input type=`hidden` id=`TABLE_STARTROW$idx` name=`TABLE_STARTROW$idx` value=`$start_row` />
              <input type=`hidden` id=`TABLE_ROWS$idx` name=`TABLE_ROWS$idx` value=`$row_count` />
              </div>");
        }
        return $RESULT;
    }


    // ----------- The functions below are extended to process a table cell before it is output, can change style and values  ---------------
    // ----------- Process a table cell before it is exported  ---------------
    public function ProcessExportCell($field, &$value)
    {

        // extend this function to possibly modify the field display value
        return;
    }

    // ----------- Process a record table cell before it is output when viewing a record  ---------------
    public function ProcessRecordCell($field, &$value, &$td_options)
    {

        // extend this function to possibly modify the field display value, or change the class or options in the cell <TD>
        return;
    }

    // ----------- Process a record table cell before it is output when viewing a table  ---------------
    public function ProcessTableCell($field, &$value, &$td_options, $id='')
    {
        //for extension use--> parent::ProcessTableCell($field, $value, $td_options, $id);
        if (($field == 'active') and ($value == 'No')) $td_options = 'style="background-color:#f00; color:#fff;"';
        // extend this function to possibly modify the field display value, or change the class or options in the cell <TD>
        return;
    }


    public function OutputTableRow($search_array)
    {
        if ($search_array) {
            $row = $search_array[0];

            $header_fields = array_keys($row);
            array_pop($header_fields);  // remove TID

            $RESULT = '<td align="right"></td>';

            foreach ($header_fields as $newkey) {
                if (isset($row[$newkey])) {
                    $field = $row[$newkey];
                    $outvalue = (empty($this->Field_Values[$newkey][$field]))?
                        $field : $this->Field_Values[$newkey][$field];
                    $td_options = '';
                    $this->ProcessTableCell($newkey, $outvalue, $td_options, $row['TID']);

                    $RESULT .= "<td $td_options>$outvalue</td>";
                } else {
                    // may have a null result
                    $RESULT .= "<td></td>";
                }
            }

            // encrypt the table and value for an encrypted query

            $RESULT .= $this->GetEditLinks($row['TID']);

        }
        return $RESULT;
    }

    public function GetEqValue($var)
    {
        $DATA = GetEncryptQuery('eq');
        return ArrayValue($DATA, $var);
    }

    public function GetEditLinks($row_tid)
    {
        static $dialog_id;

        if (empty($dialog_id)) {
            $dialog_id = $this->GetEqValue('dialog');
        }

        
        if ($this->Edit_Links) {
            $eq             = EncryptQuery("class={$this->Class_Name};id=$row_tid;idx=$this->Idx;dialog=$dialog_id;parameters={$this->Parameter_String}");
            $edit_links     = str_replace('@IDX@', $this->Idx, $this->Edit_Links);
            $title          = $this->GetTableTitle();
            return str_replace(array('@VALUE@', '@EQ@', '@TITLE@', '@DIALOGID@'), array($row_tid, $eq, $title, $dialog_id), $edit_links);
        } else {
            return '';
        }
    }


    // ----------- Output the export block in a table.  Extend this to change or remove this section ---------------
    public function GetExportBlock()
    {
        $idx = $this->Idx;
        $var = "EXPORT__{$this->Class_Name}__{$this->Idx}";
        $query_link = EncryptStringHex($var, 'dbquery');

        $query = strTo($this->SQL->Db_Last_Query, 'LIMIT');
        $query = str_replace(';', '\;', $query);
        $query = str_replace('&', '\&', $query);           
        
        $eq_query = EncryptQuery("class={$this->Class_Name};query=$query;");        
    
        return qqn("
        <div class=`EXPORT_SELECTION`>
            <form action=`$this->Export_Link?idx=$idx` method=`post` target=`_blank` name=`exportform$idx`>
            <input type=`hidden` name=`EXPORT_EQ$idx` value=`$eq_query` />
            <input type=`hidden` name=`EXPORT_TYPE$idx` id=`EXPORT_TYPE$idx` value=`csv` />
            Export<br />
            <a class=`EXPORT_BUTTON`
              title=`Comma Separated Values` href=`#` 
              onclick=`$('#EXPORT_TYPE$idx').val('csv'); document.exportform$idx.submit(); return false;`>CSV</a>
            <a class=`EXPORT_BUTTON`
              title=`Excel XML Format` href=`#`
              onclick=`$('#EXPORT_TYPE$idx').val('xml'); document.exportform$idx.submit(); return false;`>Excel</a>
            </form>
        </div>");
    }
    
    public function GetTableHeading($colcount)
    {
        $export         = ($this->Show_Export)? $this->GetExportBlock() : '';
        $filter_title   = ($this->TableHeading_ShowTitle) ? 'Search Results : ' . $this->GetTableTitle() : '';
        $RESULT = '
            <tr class="TABLE_TITLE">
                <td colspan="'. $colcount. '">
                ' . $export . $filter_title . '
                <div id="TABLE_FILTER_DIV">
                Filter <input id="TABLE_FILTER" type="text" value="" cols="20" onkeyup="runFilter();" />
                </div>
                </td>
            </tr>';
        return $RESULT;
    }

    // ----------- Output a table ("rows_only" just gives the body, so it can be used in AJAX) ---------------
    public function OutputTable($search_array, $num_rows, $start_row, $row_count, $rows_only=false, $primary_sort_order='', $primary_sort_direction='')
    {
        $dialog_id = $this->GetEqValue('dialog');
        if (empty($dialog_id)) {
            $dialog_id = '0';
        }
        $eq = EncryptQuery("class={$this->Class_Name};idx=$this->Idx;dialog=$dialog_id;parameters={$this->Parameter_String}");
        $add_link = str_replace(array('@EQ@', '@TITLE@'), array($eq, $this->GetTableTitle()), $this->Add_Link);

        $idx = $this->Idx;
        $table_options = $this->Default_Table_Options . " class=\"TABLE_DISPLAY\" id=\"TABLE_DISPLAY$idx\"";

        if (empty($search_array)) {
            return "<div class=\"TABLE_DISPLAY\">$add_link</div>";
        } elseif ($search_array == 'NO_FIELDS') {
            $RESULT  = "\n\n<table $table_options>\n<tbody>\n";
            $RESULT  .= "<tr><td><h3>No Fields Defined</h3></td><tr>\n";
            $RESULT  .= "</tbody>\n</table>\n";
            return $RESULT;
        } elseif ($search_array == 'START') {
            $RESULT  = "\n\n<table $table_options>\n<tbody>\n";
            $RESULT  .= '<tr><td style="text-align:center; padding:1em;">Processing . . .<br /><br /><img src="/wo/images/upload.gif" alt="processing" /></td></tr>';
            $RESULT  .= "\n</tbody>\n</table>\n";
            return $RESULT;
        }

        //-------- have a search array --------

        if (!empty($search_array[0])) {
            $header_fields = array_keys($search_array[0]);
            array_pop($header_fields);  // remove TID
        } else {
            $header_fields = array();
        }

        $edit_links = str_replace('@IDX@', $idx, $this->Edit_Links);

        if ($rows_only) {
            $RESULT = '';
        } else {
            $RESULT  = "\n\n<table $table_options>\n<tbody>\n";
        }

        $colcount = count($header_fields) + $this->Edit_Links_Count + 1;

        $RESULT .= $this->GetTableHeading($colcount);

        $RESULT .= "<tr><td class=\"row_select\" colspan=\"$colcount\">" . $this->RowSelect($num_rows, $start_row, $row_count) ."</td></tr>\n";

        $RESULT .= "<tr class=\"TABLE_HEADER\"><th>No.</th>";

        foreach ($header_fields as $field) {
            $newkey = $this->GetFieldTitleFromAlias($field);
            $title  = ArrayValue($this->Field_Titles, $newkey);

            if ($title) {
                if ($primary_sort_order == $field) {
                    $arrow = ($primary_sort_direction)? '<span>&nbsp;&darr;</span>' : '<span>&nbsp;&uarr;</span>';
                } else {
                    $arrow = '';
                }
                $RESULT .= "<th class=\"HEADER_SORT_CELL\"><a href=\"#\" class=\"HEADER_SORT\" onclick=\"setColumnSort($idx, '$eq' ,'$field'); return false;\">$title$arrow</a></th>";
            }
        }

        if ($edit_links) $RESULT .= '<th colspan="' . $this->Edit_Links_Count . '">'. $add_link .'</th>';  // add column for edit links
        $RESULT .= "</tr>\n";
        $even = false;
        $count = $start_row-1;
        if ($search_array) {
            foreach ($search_array as $row) {
                $count++;
                $class = ($even)? 'even' : 'odd';
                $even = !$even;
                $RESULT .= "<tr id=\"TABLE_ROW_ID{$idx}_{$row['TID']}\" class=\"$class\"><td align=\"right\">$count.</td>";

                foreach ($header_fields as $newkey) {
                    if (isset($row[$newkey])) {
                        $field = $row[$newkey];
                        $outvalue = (empty($this->Field_Values[$newkey][$field]))?
                            $field : $this->Field_Values[$newkey][$field];
                        $td_options = '';
                        $this->ProcessTableCell($newkey, $outvalue, $td_options, $row['TID']);

                        $RESULT .= "<td $td_options>$outvalue</td>";
                    } else {
                        // may have a null result
                        $RESULT .= "<td></td>";
                    }
                }

                $RESULT .= $this->GetEditLinks($row['TID']);
                $RESULT .= "</tr>\n";
            }
        }

        if (!$rows_only) {
            $RESULT .= "</tbody>\n</table>\n";
        }

        return $RESULT;
    }

    // ----------- function to show a query in an Ajax table  ---------------
    public function DisplayQueryFromAjaxTable()
    {
        echo $this->Db_Query_Display;
    }

    public function AddJoins($table)
    {
    //echo "Add Joins: $table<br />";
        if ($table and $this->Join_Array) {
            if (empty($this->Use_Join_Array)) {
                $this->Use_Join_Array = array();
            }
            if (!empty($this->Join_Array[$table]) and empty($this->Use_Join_Array[$table])) {
                $join = $this->Join_Array[$table];
                $this->AddJoinsFromWhere($join, $table);  // recursive
                $this->Use_Join_Array[$table] = $this->Join_Array[$table];
            }
        }
    }


    public function AddJoinsFromWhere($where, $exclude_table='')
    {
        // requires tables be in a `tablename`. format
        if ($where and $this->Join_Array) {
            $tables = TextBetweenArray('`', '`', $where);
            if ($tables) {
                foreach($tables as $table) {
                    if (($exclude_table != $table) and ($this->Table != $table))
                    $this->AddJoins($table);
                }
            }
        }
    }

    public function GetJoinList()
    {
        if ($this->Join_Array) {
            if (!empty($this->Use_Join_Array)) {
                $joins = array_values($this->Use_Join_Array);
                return implode("\n", $joins);
            } else {
                return '';
            }
        } else {
            return $this->Joins;
        }
    }



    // ----------- function to display a table using AJAX ---------------
    public function AjaxTableDisplay($posted_data, $action, $idx, $row_id='')
    {
        // returns a table based upon the search result


        $operators = $this->Search_Selection_Operators;

        $this->Idx = $idx;

        $WHERE = $this->Default_Where;

        $SINGLE_ROW = ($action == 'update_row');

        if ($SINGLE_ROW) {
            $row_id = intOnly($row_id);
            if (!$row_id) {
                echo 'Error: No ID';
                return;
            }
            $row_condition = "$this->Index_Name=$row_id";
            $WHERE .= ($WHERE)? " AND $row_condition" : $row_condition;
        }

        $this->AddJoinsFromWhere($WHERE);

        //search operator    : TABLE_SEARCH_OPERATOR_$field
        //search value input : TABLE_SEARCH_VALUE_$field
        //display checkbox   : TABLE_DISPLAY_$field

        //-------- display fields --------
        $DisplayFields = '';
        $active_only = $this->Use_Active;

        $field_list = array();
        $field_aliases = array();
        foreach ($posted_data as $key=>$value) {
            $display = strFrom($key, "TABLE_SEARCH_DISPLAY{$idx}_");
            if ($display and $value) {
                $field_name = $this->GetFieldTitleFromAlias($display);
                if ($field_name) {
                    if (strIn($field_name, ' AS ')) {
                        $field_aliases[] = $display;
                    }
                    // $field = $this->GetFieldTitleFromAlias($display);
                    // $field_list[] = $field;
                    $field_list[] = $field_name;
                    $this->AddJoinsFromWhere($this->SQL->QuoteKey($field_name));
                }
            }
        }

        $DisplayFields = $this->SQL->Keys($field_list, false);

        if (empty($DisplayFields)) {
            echo '<tr><td><h3 class="center">No Fields Selected!</h3></td></tr>';
            return;
        }

        //-------- create Where clause --------

        foreach ($posted_data as $key=>$operator) {
            $field = strFrom($key, "TABLE_SEARCH_OPERATOR{$idx}_");

            if ($field and ($operator != 'All')) {
                $filter = ArrayValue($posted_data, "TABLE_SEARCH_VALUE{$idx}_" . $field);

                $add_where = '';

                if ($field == 'active') {
                    if ($operator == 1) {
                        $add_where = " `{$this->Table}`.`active`=1";
                    } elseif ($operator == 2) {
                        $add_where = " `{$this->Table}`.`active`=0";
                    }
                } else {

                    $field_var = $this->GetFieldAlias($field);
                    
                    $field = $this->GetFieldTitleFromAlias($field);
                    $pos = strrpos($field, ' AS');
                    if ($pos !== false) {
                        $field = substr($field, 0, $pos);
                    }

                    $field = $this->SQL->QuoteKey($field);
                    $this->AddJoinsFromWhere($field);

                    if ($operator == 'Not =') {
                        $operator= '!=';
                    }

                    $have_filter = ($filter or ($filter == '0'));

                    if ($have_filter) {
                        if (!empty($this->Field_Values[$field_var])) {
                            $reverse_field_values = array_flip($this->Field_Values[$field_var]);
                            $new_filter = ArrayValue($reverse_field_values, $filter);
                            if (!empty($new_filter)) {
                                $filter = $new_filter;
                            }
                        }
                    }

                    if ($operator == 'List (x,y)') {
                        if ($have_filter) {
                            //$filter = str_replace("'", "\'", $filter);
                            //$filter = str_replace(',', "','", $filter);
                            $filter_array = explode(',', $filter);
                            TrimArray($filter_array);
                            $filter = $this->SQL->Values($filter_array);
                            $add_where = " $field IN ($filter)";
                        }
                    } elseif ($operator == 'Between (x|y)') {
                        if ($have_filter and (strpos($filter, '|') !== false)) {
                            list($x, $y) = explode('|', $filter);
                            $x = $this->SQL->QuoteValue($x);
                            $y = $this->SQL->QuoteValue($y);
                            if ($x and $y ) {
                                $add_where = " ($field >= $x) AND ($field <= $y)";
                            }
                        }
                    } elseif ($operator == 'Includes') {
                        if ($have_filter) {
                            $filter = $this->SQL->QuoteValue("%$filter%");
                            $add_where = " $field LIKE $filter";
                        }
                    } elseif ($operator == 'Begins With') {
                        if ($have_filter) {
                            $filter = $this->SQL->QuoteValue("$filter%");
                            $add_where = " $field LIKE $filter";
                        }
                    } elseif ($operator == 'Does Not Include') {
                        if ($have_filter) {
                            $filter = $this->SQL->QuoteValue("%$filter%");
                            $add_where = " $field NOT LIKE $filter";
                        }
                    } elseif ($operator == '=') {
                        if ($have_filter) {
                            $filter = $this->SQL->QuoteValue($filter);
                            $add_where = " $field=$filter";
                        } else {
                             $add_where = " ($field='' OR $field IS NULL)";
                        }
                    } elseif ($operator == '!=') {
                        if ($have_filter) {
                            $filter = $this->SQL->QuoteValue($filter);
                            $add_where = " $field!=$filter";
                        } else {
                             $add_where = " ($field!='' AND $field IS NOT NULL)";
                        }
                    } else {
                        $filter = $this->SQL->QuoteValue($filter);
                        $add_where = " $field $operator $filter";
                    }

                }

                if ($add_where) {
                    $WHERE .= ($WHERE)? " AND $add_where" : $add_where;
                }
            }
        }

        $order_fields = array();
        $order_field = ArrayValue($posted_data, 'TABLE_ORDER'.$idx);


        list($base, $suffix) = explode(' ', $order_field . ' ');
        if ($order_field) {
            $order_fields[$base]    = $suffix;
            $primary_sort_order     = $base;
            $primary_sort_direction = $suffix;
        } else {
            $primary_sort_order     = '';
            $primary_sort_direction = '';
        }

        $order_field = ArrayValue($posted_data, 'TABLE_2ORDER'.$idx);
        list($base, $suffix) = explode(' ', $order_field . ' ');
        if ($order_field and !array_key_exists($base, $order_fields)) {
            $order_fields[$base] = $suffix;
        }

        $order_field = ArrayValue($posted_data, 'TABLE_3ORDER'.$idx);
        list($base, $suffix) = explode(' ', $order_field . ' ');
        if ($order_field and !array_key_exists($base, $order_fields)) {
            $order_fields[$base] = $suffix;
        }

        $order = '';
        if (count($order_fields) >0) {
            foreach($order_fields as $base => $suffix) {
                if (!empty($suffix)) {
                    $suffix = " $suffix";
                }
                //$alias = strFromLast($this->GetFieldTitleFromAlias($base), ' AS ');
                //$var = ($alias)? $alias : $this->GetFieldTitleFromAlias($base);

                $order_field = $this->SQL->QuoteKey($this->GetFieldTitleFromAlias($base));
                $alias = strFromLast($order_field, ' AS ');
                if (in_array($base, $field_aliases)) {
                    $order_field = $alias;
                } else {
                    $order_field = str_replace(' AS ' . $alias, '', $order_field);
                }
                //$order_field = $this->SQL->QuoteKey($var);
                $comma = (!empty($order))? ',' : '';
                $order .= $comma . $order_field . $suffix;
                $this->AddJoinsFromWhere($order_field);
            }
        }

        $count_joins = ($WHERE == " `{$this->Table}`.`active`=1")? '' : $this->GetJoinList();
        $num_rows  = $this->SQL->Count($this->Table, $WHERE, $count_joins);  // must recalculate rows because number may change based upon new query

        $start_row = max(TransformContent(ArrayValue($posted_data, 'TABLE_STARTROW'.$idx),'QST'),1);
        $row_count = TransformContent(ArrayValue($posted_data, 'TABLE_ROWS'.$idx),'QST');
        if (empty($row_count)) {
            $row_count = $this->Default_List_Size;
        }
        $row_count = max($row_count,1);


        if ($start_row > $num_rows) $start_row = 1;

        if ($action == 'HOME')             $start_row = 1;
        elseif ($action == 'PREVIOUSPAGE') $start_row = max($start_row - $row_count,1);
        elseif ($action == 'NEXTPAGE')     $start_row = min($start_row + $row_count, $num_rows - $row_count + 1);
        elseif ($action == 'END')          $start_row = max($num_rows - $row_count + 1, 1);

        //$num_rows = '';

        if ($SINGLE_ROW) {
            $start_row = 1;
            $row_count = 1;
        }

        $query_info = array(
            'table'      => $this->Table,
            'keys'       => $DisplayFields . ',`' . $this->Table . '`.`' . $this->Index_Name . '` AS TID',
            'where'      => $WHERE,
            'order'      => $order,
            'start_list' => $start_row - 1,
            'list_size'  => $row_count,
            'joins'      => $this->GetJoinList(),
            'get_count'  => false
        );

        $table_array = $this->SQL->GetArray($query_info);

        if ($table_array) {
            if ($SINGLE_ROW) {
                echo $this->OutputTableRow($table_array);
                return;
            } else {
                echo $this->OutputTable($table_array, $num_rows, $start_row, $row_count, 1, $primary_sort_order, $primary_sort_direction);
                if ($this->Show_Table_Post_Process) {
                    $header_fields  = array_keys($table_array[0]);
                    $colcount       = count($header_fields) + $this->Edit_Links_Count;
                    $content        = $this->TablePostProcess($table_array);
                    $subarea        = "<tr><td colspan='{$colcount}'>{$content}</td></tr>";
                    echo $subarea;
                }
                $count = count($table_array[0]) + $this->Edit_Links_Count + 1;
            }
        } else {
            $dialog_id = $this->GetEqValue('dialog');
            $eq = EncryptQuery("class={$this->Class_Name};idx=$this->Idx;dialog=$dialog_id;parameters={$this->Parameter_String}");
            $add_link = str_replace(array('@EQ@', '@TITLE@'), array($eq, $this->GetTableTitle()), $this->Add_Link);
            echo '<tr><td><h3 class="center">No Records Found!</h3>
            <div class="TABLE_DISPLAY">' . $add_link . '</div>
            </td></tr>';
            $count = 1;
        }

        $this->Db_Query_Display = "<tr><td colspan=\"$count\">" . $this->SQL->WriteDbQueryText() . '</td></tr>';
    }
    
    public function TablePostProcess($search_array)
    {
        # FUNCTION :: Extendable function to re-process the table after its been output
        
        $output = "";
        return $output;
        
    }
    
    // ----------- function to view a record, returing a string  ---------------
    public function ViewRecordText($id, $field_list='', $id_field='')
    {
        if (empty($id_field)) {
            $id_field = $this->Index_Name;
        }
        $id = $this->SQL->QuoteValue($id);
        $id_field = TransformContent($id_field, 'TQS');
        if (!$field_list) {
            $field_list = $this->Field_Titles;
        }

        $keys = $this->SQL->Keys($field_list);
        $where = "`$this->Table`.`$id_field`=$id";

        if (empty($keys) or empty($where)) return '';

        if (!empty($this->Join_Array)) {
            $joins = implode("\n", array_values(($this->Join_Array)));
        } else {
            $joins = $this->Joins;
        }

        $row = $this->SQL->GetRecord($this->Table, $keys, $where, $joins);

        if ($row) {
            $RESULT = "<table {$this->Default_View_Table_Options}>\n<tbody>\n";

            if (!empty($field_list)) {
                foreach ($field_list as $key => $value) {
                    $newkey = $this->GetFieldAlias($key);

                    if (isset($row[$newkey])) {
                        $field = $row[$newkey];
                        $outvalue = (empty($this->Field_Values[$newkey][$field]))? $field : $this->Field_Values[$newkey][$field];
                        $th_options = $this->Default_Th_Options;
                        $td_options = '';
                        $this->ProcessRecordCell($newkey, $outvalue, $td_options, $id);
                        if (empty($td_options)) {
                            $td_options = $this->Default_Td_Options;
                        }
                        $RESULT .= "<tr><th align=\"right\" $th_options>$value</th><td $td_options>$outvalue</td></tr>\n";
                    }
                }
            } else {
                foreach ($row as $key => $value) {
                    $outvalue = (empty($this->Field_Values[$key][$value]))? $value : $this->Field_Values[$key][$value];

                    $th_options = $this->Default_Th_Options;
                    $td_options = '';
                    $this->ProcessRecordCell($key, $outvalue, $td_options, $id);
                    if (empty($td_options)) {
                        $td_options = $this->Default_Td_Options;
                    }
                    $RESULT .= "<tr><th align=\"right\" $th_options>$key</th><td $td_options>$outvalue</td></tr>\n";
                }
            }

            $RESULT .= "</tbody>\n</table>\n";
        } else {
            $RESULT = '';
        }
        return $RESULT;

    }

    // ----------- function to view a record  ---------------
    public function ViewRecord($id, $field_list='', $field='')
    {
        if (empty($field)) {
            $field = $this->Index_Name;
        }

        echo $this->ViewRecordText($id, $field_list, $field);
    }
}