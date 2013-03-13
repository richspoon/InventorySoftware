<?php
// creates data stored in an array of associative arrays with keys in $this->Fields
class Lib_DataList
{
    public $Data            = array();
    public $File            = '';
    public $Form_Array      = '';
    public $Form_Data_Array = '';
    public $Error           = '';
    public $Submit_Name     = 'DATALIST_SUBMIT';
    public $Action          = '';
    public $Index_Name      = 'IDX';
    public $Max_Index       = 0;
    public $Close_On_Success= true;

    //public $Fields          = array();
    public $Field_Titles;    // this is an associative array created in extensions that define: field name => title
    public $Unique_Fields   = '';
    public $Dialog_Id       = '';
    public $Idx             = 0;
    public $Action_File     = '';
    public $Action_Link     = '';
    public $Root            = '';
    
    public $Form_Start  = array(
        'code|<div class="formdiv">',
        'form|@LINK@|post|dl_edit_form'
    );
    
    public $Form_End = array(
        'submit|Save Record|DATALIST_SUBMIT',
        'hidden|updated',
        'endform',
        'code|</div>'
    );

    public  $Default_Table_Options     = 'cellspacing="1" cellpadding="0"';
    public  $Default_View_Table_Options= 'cellspacing="1" cellpadding="0" class="VIEW_RECORD_TABLE"';
    public  $Default_Td_Options        = '';
    public  $Default_Th_Options        = '';
    public  $Sorting        = false;

    // idx, link, file, id,
    public $Edit_Links  = array(
        '<a href="#" class="row_view" title="View"
            onclick="return tableDataListViewClick(\'@IDX@\',\'@LINK@\', \'@FILE@\', \'@ID@\');"></a>',
        '<a href="#" class="row_edit" title="Edit"
            onclick="return tableDataListEditClick(\'@IDX@\',\'@LINK@\', \'@FILE@\', \'@ID@\');"></a>',
        '<a href="#" class="row_delete" title="Delete"
            onclick="return tableDataListDeleteClick(\'@IDX@\',\'@LINK@\', \'@ID@\');"></a>'
    );

    public $Add_Link = '<a href="#" class="add_record" title="Add Record"
        onclick="return tableDataListAddClick(\'@LINK@\', \'@FILE@\');">Add</a>';

    public function __construct()
    {
        $this->LoadData();
        $this->Dialog_Id = Get('DIALOGID');
        $this->Action_Link = $_SERVER['REQUEST_URI'];
        $this->Page_Link = preg_replace('/(;|\?|:).+$/', '', $this->Action_Link);
        
        $this->Form_Start = str_replace('@LINK@', $this->Action_Link, $this->Form_Start);

        if (empty($GLOBALS['TABLE_BASE_INDEX_COUNT'])) {
            $GLOBALS['TABLE_BASE_INDEX_COUNT'] = 1;
        } else {
            $GLOBALS['TABLE_BASE_INDEX_COUNT']++;
        }
        $this->Idx = $GLOBALS['TABLE_BASE_INDEX_COUNT'];
        $this->Root = $_SERVER['DOCUMENT_ROOT'];
    }

    public function ProcessPage()
    {
        if (Get('A') == 'ADD') {
            $this->AddRecord();
            return;
        }

        if (Get('A') == 'EDIT') {
            $this->EditRecord(Get('ID'));
            return;
        }

        if (Get('A') == 'VIEW') {
            $this->ViewRecord(Get('ID'));
            return;
        }

        if (Get('A') == 'DELETE') {
            echo $this->DeleteRecord(Get('ID'));
            exit;
        }

        if (Get('A') == 'SORT') {
            list($idx, $sort_data) = explode(':', Post('data'));
            $this->SortData($sort_data);
            $this->Idx = $idx;
            $this->Sorting = true;
            $this->ListTable();
            exit;
        }
        $this->ListTable();
    }

   
    public function SortData($list)
    {
        $order = explode(',', $list);
        $new_array = array();
        foreach ($order as $row_id) {
            $row = $this->GetRow($this->Index_Name, $row_id);
            if ($row) {
                $new_array[] = $row;
            }
        }
        $this->Data = $new_array;
        $this->SaveData();
    }

    public function GetEditLinks($row_idx)
    {
        $RESULT = '<td align="center">' . implode("</td>\n<td align=\"center\">", $this->Edit_Links) . "</td>\n";
        $RESULT = str_replace(
            array('@IDX@','@LINK@', '@FILE@', '@ID@'),
            array($this->Idx, $this->Page_Link, $this->File, $row_idx), $RESULT);
        return $RESULT;
    }

    public function GetRow($field, $field_value)
    {
        foreach ($this->Data as $row) {
            if ($row[$field] == $field_value) {
                return $row;
            }
        }
    }

    public function GetRows($field='', $field_value='')
    {
        if (empty($field)) {
            return $this->Data;
        }

        $RESULT = array();
        foreach ($this->Data as $row) {
            if ($row[$field] == $field_value) {
                $RESULT[] = $row;
            }
        }
        return $RESULT;
    }

    public function GetValue($field, $field_value, $item)
    {
        $row = $this->GetRow($field, $field_value);
        return ArrayValue($row, $item);
    }

    public function GetMaxIndex()
    {
        $max = 0;
        if ($this->Data) {
            foreach ($this->Data as $row) {
                if ($row[$this->Index_Name] > $max) {
                    $max = $row[$this->Index_Name];
                }
            }
        }
        $this->Max_Index = $max;
        return $max;
    }
    
    public function GetFieldValues($field)
    {
        $array = array();
        if ($this->Data) {
            foreach ($this->Data as $row) {
                $array[] = $row[$field];
            }
            $array = array_unique($array);
        }
        return $array;
    }

    public function LoadData()
    {
        if ($this->File) {
            if (file_exists(RootPath($this->File))) {
                $content = file_get_contents(RootPath($this->File));
                if ($content) {
                    if ($this->Data = unserialize($content)) {
                        if (is_array($this->Data)) {
                            return;
                        }
                    }
                }
            }
        }
        $this->Data = array();
    }

    public function SaveData()
    {
        return file_put_contents(RootPath($this->File), serialize($this->Data));
    }

    public function GetTimeStamp()
    {
        return time();
    }

    public function AddDataRecord($record)
    {
        $this->LoadData();
        $record[$this->Index_Name] = $this->GetMaxIndex() + 1;
        $timestamp = $this->GetTimeStamp();
        $record['created'] = $timestamp;
        $record['updated'] = $timestamp;
        $this->Data[] = $record;
        return $this->SaveData();
    }

    public function UpdateRecord($field, $field_value, $record)
    {
        $this->LoadData();
        $record['updated'] = $this->GetTimeStamp();

        foreach ($this->Data as $idx => $row) {
            if ($row[$field] == $field_value) {
                foreach ($record as $key => $value) {
                    $this->Data[$idx][$key] = $value;
                }
                if ($this->SaveData()) {
                    return true;
                } else {
                    break;
                }
            }
        }
        $this->Error = 'File Could Not Be Updated';
        return false;
    }

    public function DeleteRow($field, $field_value)
    {
        $this->LoadData();

        foreach ($this->Data as $idx => $row) {
            if ($row[$field] == $field_value) {
                unset($this->Data[$idx]);
                if ($this->SaveData()) {
                    return true;
                } else {
                    break;
                }
            }
        }
        $this->Error = 'Record could not be deleted';
        return false;
    }

    public function SetFormArray() // extend this function
    {
        return;
    }

    public function SetFormArrayFull()
    {
        $this->SetFormArray();
        if ($this->Form_Start) {
            $this->Form_Data_Array = array_merge($this->Form_Start, $this->Form_Data_Array, $this->Form_End);
        }
    }

    // ----- setup default values --------
    public function SetDefaultValues()
    {
        if (!empty($this->Default_Values)) {
            foreach ($this->Default_Values as $key => $value) {
                Form_PostValue($key, $value);
            }
        }
    }

    // ----------- function for AJAX for Add/Edit ---------------
    public function ProcessAjax()
    {
        exit;  // <<----------- this should exit
    }

    // ----------- function to post process any form values ---------------
    public function PostProcessFormValues($FormArray)
    {
        // extend this function to process values -- simply return the array back
        return $FormArray;
    }

    public function AddRecord()
    {
        echo $this->AddRecordText();
    }



    public function AddRecordText()
    {
        global $AJAX;

        $this->Action = 'ADD';

        if ($AJAX) {
            $this->ProcessAjax();
        }

        $this->SetFormArrayFull();

        $this->Error = '';
        $RESULT = '';

        if (havesubmit($this->Submit_Name)) {

            $this->Form_Array = ProcessFormNT($this->Form_Data_Array, $this->Error);

            $this->Form_Array = $this->PostProcessFormValues($this->Form_Array);

            if (!$this->Error) {
                if ($this->AddDataRecord($this->Form_Array)) {
                    $this->SuccessfulAddRecord();
                    return $RESULT;
                }
            }

        }
        if (!havesubmit($this->Submit_Name) or $this->Error) {
            $RESULT .= WriteErrorText($this->Error);

            if (!$this->Error) {
                $this->SetDefaultValues();
            }
            $RESULT .= OutputForm($this->Form_Data_Array, Post($this->Submit_Name));
        }

        return $RESULT;
    }

    public function GetEqValue($var)
    {
        $DATA = GetEncryptQuery('eq');
        return ArrayValue($DATA, $var);
    }

    public function SuccessfulAddRecord()
    {
        $table = $this->File;
        $close = $this->Close_On_Success? "top.parent.appformClose('appform' + dialogNumber);" : '';
        $table_dialog_id   = Get('D');
        $return = ($table_dialog_id)? "
            if (parent.document.getElementById('appformIframe$table_dialog_id')) {
                parent.document.getElementById('appformIframe$table_dialog_id').contentWindow.location.reload(true);
            }" : '';

        AddScript(
            "top.parent.setTopFlash('Record Added to $table');
            $return
            $close"
        );

        return true;
    }

    public function SuccessfulEditRecord($flash)
    {
        $table = $this->File;
        $table_dialog_id   = Get('D');

        $script = ($table_dialog_id)? "
            if (parent.document.getElementById('appformIframe$table_dialog_id')) {
                parent.document.getElementById('appformIframe$table_dialog_id').contentWindow.location.reload(true);
            }" : '';

        $close = $this->Close_On_Success? "top.parent.appformClose('appform' + dialogNumber);" : '';

        AddScript(
            "$script;
            top.parent.setTopFlash('$table Record [$flash] Updated');
            $close"
        );

        return '';
    }




    // ----------- function to pre-poplulate a form from a record ---------------
    public function PrePopulateFormValues($id, $field='')
    {
        if (empty($field)) {
            $field = $this->Index_Name;
        }
        $this->LoadData();
        $row = $this->GetRow($field, $id);
        if ($row) {
            Form_PostArray($row);
            return true;
        }
        return false;
    }

    // ----------- function edit a record and ouput the code ---------------
    public function EditRecord($id, $id_field='')
    {
        if (empty($id_field)) {
            $id_field = $this->Index_Name;
        }
        echo $this->EditRecordText($id, $id_field);
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

        if (!havesubmit($this->Submit_Name)) {
            // ------- prepopulate fields -------

            if (!$this->PrePopulateFormValues($id, $id_field)) {
                $RESULT .= WriteErrorText('Record Not Found');
                return $RESULT;
            }
        }

        $this->SetFormArrayFull();


        if (HaveSubmit($this->Submit_Name)) {

            $this->Form_Array = ProcessFormNT($this->Form_Data_Array, $this->Error);

            // check updated value
            $this->LoadData();

            if (!empty($this->Form_Array['updated'])) {
                $last_update_value = $this->GetValue($id_field, $id, 'updated');
                if ($last_update_value > $this->Form_Array['updated']) {
                    $this->Error = 'A newer version of this record exists. You will need to reload the record to make edits.';
                }
                unset($this->Form_Array['updated']);
            }

            $this->Form_Array = $this->PostProcessFormValues($this->Form_Array);

            if ($this->Unique_Fields) {
                $unique_fields = explode(',', $this->Unique_Fields);
                foreach ($unique_fields as $field) {
                    if (isset($this->Form_Array[$field])) {
                        if ($this->GetRow($field, $this->Form_Array[$field])) {
                            $this->Error = $this->Form_Array[$field] . ' already exits!';
                            break;
                        }
                    }
                }
            }

            if (!$this->Error) {
                if ($this->UpdateRecord($id_field, $id, $this->Form_Array)) {
                    $this->SuccessfulEditRecord("$id = $id_field");
                }
            }

        }


        if (!havesubmit($this->Submit_Name) or $this->Error) {
            $RESULT .= WriteErrorText($this->Error);
            $RESULT .= OutputForm($this->Form_Data_Array, Post($this->Submit_Name));
        }
        return $RESULT;
    }

    // ----------- function to echo a table ---------------
    public function ListTable()
    {
        echo $this->ListTableText();
    }

    public function ListTableText()
    {
        $idx = $this->Idx;

        $add_link = str_replace(array('@LINK@', '@FILE@'), array($this->Action_Link, $this->File), $this->Add_Link);

        $this->LoadData();

        if ($this->Sorting) {
            $RESULT  = '';
            $END_TABLE = '';
        } else {
            AddScriptOnload("setDataListTableDrag($idx, '$this->Action_Link;A=SORT');");
            $table_options = $this->Default_Table_Options . " class=\"TABLE_DISPLAY\" id=\"TABLE_DISPLAY$idx\"";

            $RESULT  = "\n\n<table $table_options>\n<tbody>\n";
            $END_TABLE = "\n</tbody>\n</table>\n";
        }

        if (empty($this->Data)) {
            return "$RESULT<tr><td><div class=\"TABLE_DISPLAY\">$add_link</div></td></tr>$END_TABLE";
        }

        //-------- have a search array --------

        $header_fields = array_values($this->Field_Titles);

        $edit_links = str_replace('@IDX@', $idx, $this->Edit_Links);

        $colcount = count($header_fields) + count($this->Edit_Links) + 1;

        $RESULT .= '
            <tr class="TABLE_TITLE">
                <td colspan="'. $colcount. '">
                    File : ' . $this->File . '
                <div id="TABLE_FILTER_DIV">
                Filter <input id="TABLE_FILTER" type="text" value="" size="20" onkeyup="runDataListFilter();" />
                </div>
                </td>
            </tr>';


        $RESULT .= "<tr class=\"TABLE_HEADER\"><th>No.</th>";

        foreach ($header_fields as $field) {
            $RESULT .= "<th>$field</th>";
        }

        if ($edit_links) $RESULT .= '<th colspan="' . count($this->Edit_Links) . '">'. $add_link .'</th>';  // add column for edit links
        $RESULT .= "</tr>\n";

        $even = false;
        $count = 0;
        if ($this->Data) {
            foreach ($this->Data as $row) {
                $count++;
                $class = ($even)? 'even' : 'odd';
                $even = !$even;
                $RESULT .= "<tr id=\"TABLE_ROW_ID{$idx}_{$row[$this->Index_Name]}\" class=\"$class\"><td align=\"right\" class=\"dragHandle\">$count.</td>";

                foreach ($this->Field_Titles as $field => $title) {
                    if (isset($row[$field])) {
                        $outvalue = $row[$field];

                        $td_options = '';
                        $this->ProcessTableCell($field, $outvalue, $td_options, $row[$this->Index_Name]);

                        $RESULT .= "<td $td_options>$outvalue</td>";
                    } else {
                        // may have a null result
                        $RESULT .= "<td></td>";
                    }
                }

                $RESULT .= $this->GetEditLinks($row[$this->Index_Name]);
                $RESULT .= "</tr>\n";
            }
        }

        $RESULT .= $END_TABLE;

        return $RESULT;
    }

    // ----------- Process a record table cell before it is output when viewing a record  ---------------
    public function ProcessRecordCell($field, &$value, &$td_options)
    {
        // extend this function to possibly modify the field display value, or change the class or options in the cell <TD>
        if ($field == 'created' || $field == 'updated') {
            $value = date('Y-m-d H:i:s', $value);
        }
        return;
    }

    // ----------- Process a record table cell before it is output when viewing a table  ---------------
    public function ProcessTableCell($field, &$value, &$td_options, $id='')
    {
        // extend this function to possibly modify the field display value, or change the class or options in the cell <TD>
        if ($field == 'created' || $field == 'updated') {
            $value = date('Y-m-d H:i:s', $value);
        }
        return;
    }


    // ----------- function to view a record  ---------------
    public function ViewRecord($id, $field='')
    {
        if (empty($field)) {
            $field = $this->Index_Name;
        }

        echo $this->ViewRecordText($id, $field);
    }


    // ----------- function to view a record, returing a string  ---------------
    public function ViewRecordText($id, $id_field='')
    {
        if (empty($id_field)) {
            $id_field = $this->Index_Name;
        }


        $row = $this->GetRow($id_field, $id);

        if ($row) {
            $RESULT = "<table {$this->Default_View_Table_Options}>\n<tbody>\n";

            foreach ($this->Field_Titles as $field => $title) {
                $outvalue = $row[$field];
                $th_options = $this->Default_Th_Options;
                $td_options = '';
                $this->ProcessRecordCell($field, $outvalue, $td_options, $id);
                if (empty($td_options)) {
                    $td_options = $this->Default_Td_Options;
                }
                $RESULT .= "<tr><th align=\"right\" $th_options>$title</th><td $td_options>$outvalue</td></tr>\n";
            }


            $RESULT .= "</tbody>\n</table>\n";
        } else {
            $RESULT = '';
        }
        return $RESULT;

    }


    // ----------- function to delete a record ---------------
    public function DeleteRecord($id, $id_field='')
    {
        if (empty($id_field)) {
            $id_field = $this->Index_Name;
        }

        if ($this->DeleteRow($id_field, $id)) {
            return 1;

        } else {
            $this->Error .= 'Delete Error';
            return 0;
        }
    }


}