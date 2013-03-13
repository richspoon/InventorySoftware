<?php
// FILE: /Lib/Lib_Tabs.php

class Lib_Tabs
{
    public $Tab_Spacer              = "<div class=\"tabspacer\">&nbsp;</div>\n";
    public $Tab_Folder_Start        = "<div class=\"tabfolder\">\n";
    public $Tab_Folder_End          = '</div>';
    public $Tab_Group_Name          = 'tab';
    public $Tab_Class_Name          = 'tablink';
    public $Tab_Selected_Class_Name = 'tabselect';
    public $Tab_Name                = '';
    public $Tab_Menu                = '';
    public $Tab_Data                = '';
    public $Tab_Count               = 0;
    public $Tab_Set_Function_Name   = 'setTab';
    public $Tab_Comment_Template    = "\n\n<!-- ================== @@ ================== -->\n\n";


    public function  __construct($tab_name = 'tab', $tab_class='tablink', $tab_selected_class_name='tabselect')
    {
        if ($tab_name != 'tab') {
            $this->Tab_Group_Name = $tab_name;
        }
        if ($tab_class != 'tablink') {
            $this->Tab_Class_Name = $tab_class;
        }
        $this->Tab_Selected_Class_Name = $tab_selected_class_name;
    }

    public function SetTabsetName($NAME)
    {
        $this->Tab_Group_Name  = $NAME;
    }

    public function AddTab($TITLE, $DATA)
    {
        //CREATE THE TAB ID
        if (!$DATA) return;  // abort if no data

        $this->TabCountAdd();
        $count              = $this->Tab_Count;
        $group_name         = $this->Tab_Group_Name;
        $tab_class          = $this->Tab_Class_Name;
        $tab_selected_class = $this->Tab_Selected_Class_Name;
        $set_tab_function   = $this->Tab_Set_Function_Name;

        $tab_link_id = $group_name . 'link'. $count;
        $tab_data_id = $group_name . $count;

        $tab_link_class = ($this->Tab_Count == 1)? $this->Tab_Selected_Class_Name : $this->Tab_Class_Name;
        $display        = ($this->Tab_Count == 1)? 'block' : 'none';

        if ($this->Tab_Count == 1) {
            $this->Tab_Menu .= $this->TabComment('TAB MENU');
        }

        $this->Tab_Menu .=
            "\n<a id=\"$tab_link_id\" class=\"$tab_link_class\" href=\"#\"
                onclick=\"$set_tab_function($count, '$group_name', '$tab_class', '$tab_selected_class'); return false;\">$TITLE</a>\n";

        $this->Tab_Data .= $this->TabComment("TAB: $tab_data_id");
        $this->Tab_Data .=
            "<div id=\"$tab_data_id\" style=\"display:$display;\">\n$DATA\n</div>\n\n";
    }


    public function TabComment($comment)
    {
        return ($this->Tab_Comment_Template)? str_replace('@@', $comment, $this->Tab_Comment_Template) : '';
    }

    private function TabCountAdd()
    {
        $this->Tab_Count++;
    }


    public function OutputTabs()
    {
        echo $this->Tab_Menu;
        echo $this->Tab_Spacer;
        echo $this->Tab_Folder_Start;
        echo $this->Tab_Data;
        echo $this->Tab_Folder_End;
        echo $this->TabComment('END TABS');
    }


}