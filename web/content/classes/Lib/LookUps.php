<?php

// FILE: /Lib/Lib_LookUps.php

class Lib_LookUps extends Lib_BaseClass
{
    public function  __construct()
    {
        parent::__construct();

        $this->ClassInfo = array(
            'Created By'  => 'Michael Petrovich',
            'Description' => 'Create and various lookup tables',
            'Created'     => '2009-05-01',
            'Updated'     => '2009-05-01'
        );

        $lookup_table = session('LOOKUP_TABLE');

        if (empty($lookup_table)) {
            echo "<h1>Table Not Defined.</h1>";
            return;
        }

        $this->Default_List_Size = 500;

        $table_info  = $this->SQL->TableFieldInfo($lookup_table);
        $uc_table    = strtoupper($lookup_table);
        $table_title = str_replace(' ','',NameToTitle($lookup_table));

        $this->Table  = $lookup_table;


        $field_titles_array = $this->SQL->TableFieldTitleNames($lookup_table);
        $this->Field_Titles = '';
        foreach($field_titles_array as $var=>$title) {
            $this->Field_Titles[$var] = $title;
        }



        $this->Add_Submit_Name  = $uc_table . '_SUBMIT_EDIT';
        $this->Edit_Submit_Name = $uc_table . '_SUBMIT_EDIT';


        $this->Index_Name = $table_info[0]['Field'];

        $this->Flash_Field = $this->Index_Name;

        $this->Default_Sort  = $this->Index_Name;

        $base_array = array();
        foreach($table_info as $ROW) {
            $kind   = $ROW['Kind'];
            $size   = $ROW['Size'];
            $field  = $ROW['Field'];
            $extra  = $ROW['Extra'];
            $title  = NameToTitle($field);
            $default= $ROW['Default'];

            if (($extra != 'auto_increment') and ($default != 'CURRENT_TIMESTAMP')
                and ($field != 'active') and ($field != 'created') and ($field != 'updated')) {

                $this->Default_Fields .= "$field,";

                if ($kind=='text')  {
                    $base_array[] = "textarea|$title|$field|N|60|5";
                } elseif (($kind =='int') or ($kind =='tinyint'))  {
                    $base_array[] = "integer|$title|$field|N|$size|$size";
                } elseif ($kind=='date')  {
                    $start = date('Y') - 10;
                    $end   = $start + 20;
                    $base_array[] = "dateYMD|$title|$field|Y-M-D|N|$start|$end";
                } elseif ($field=='country')  {
                    $base_array[] = "country|$title|$field|N";
                } else {
                    $colsize = ($size<60)? $size : 60;
                    if ($field != 'active') {
                        $base_array[] ="text|$title|$field|N|$colsize|$size";
                    }
                }
            }
        }

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

        $this->Default_Fields = substr($this->Default_Fields, 0, -1);

    } // -------------- END __construct --------------

}  // -------------- END CLASS --------------