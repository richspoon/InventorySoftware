<?php

// FILE: /Lib/Lib_AdminModules.php

/* ================================ NOTES ================================
2009-12-03 :    MVP changed default size to 500;
                Changed is_folder to use IF in Field_Titles, instead of using Field_Values;

========================================================================== */

class Lib_AdminModules extends Lib_BaseClass
{

    public $Add_Edit_Style = '
#select_images {
  overflow : scroll;
  width : 640px;
  height : 260px;
  border : 1px solid #888;
  margin: 10px 0px;
  background-color : #eee;
}

a.image_selection {
  float : left;
  text-decoration : none;
  text-align : center;
  width : 100px;
  height : 80px;
  border : 1px dotted transparent;
}

a.image_selection img {
  width : 60px;
  height : 60px;
}

a.image_selection:hover {
  border : 1px dotted #888;
  background-color : #ccc;
}
';
    public $Image_Dir = '/wo/images/menu_icons';

    public function  __construct()
    {

        parent::__construct();


        $this->ClassInfo = array(
            'Created By'  => 'MVP',
            'Description' => 'Create and manage admin_modules',
            'Created'     => '2009-06-24',
            'Updated'     => '2009-12-03'
        );

        $this->Table  = 'admin_modules';

        $this->Add_Submit_Name  = 'ADMIN_MODULES_SUBMIT_EDIT';
        $this->Edit_Submit_Name = 'ADMIN_MODULES_SUBMIT_EDIT';

        $this->Flash_Field = '';

        $this->Field_Titles = array(
            'admin_modules_id' => 'Id',
            'category' => 'Category',
            "IF(is_folder=1,'Yes','No') AS IS_FOLDER" => 'Folder',
            'filename' => 'Filename',
            'title' => 'Title',
            'image' => 'Image',
            'active' => 'Active',
            'updated' => 'Updated',
            'created' => 'Created'
        );

        $this->Index_Name = 'admin_modules_id';

        $this->Default_Fields = 'category,filename,title';

        $this->Unique_Fields = '';

        $this->Default_Sort = 'title';

        $this->Table_Creation_Query = "";

        //$this->Field_Values['is_folder'] = array(0=> 'No', 1 => 'Yes');

        $this->Default_List_Size = 500;

    } // ================== END CONSTRUCT =================


    public function ProcessRecordCell($field, &$value, &$td_options)  // extended from parent
    {
        if ($field == 'image') {
            $value = $value . '<br /><br /><img src="' . $this->Image_Dir . '/'. $value .'" alt="' . $value . '" height="60" border="0" />';
        }

        return;
    }

    public function ProcessTableCell($field, &$value, &$td_options, $id='')
    {
        parent::ProcessTableCell($field, $value, $td_options, $id);
        if ($field == 'image') {
            $value = $value . '<br /><img src="' . $this->Image_Dir . '/'. $value .'" alt="' . $value . '" height="60" border="0" />';
        }

        return;
    }

    public function SetFormArrays()
    {

        global $ROOT, $FormPrefix;

        $new_modules = $this->FindNewModules();

        $images = GetDirectory("$ROOT$this->Image_Dir", '.gif,.png');

        $categories    = $this->SQL->GetFieldValues($this->Table, 'category', "category != ''");
        $category_list = Form_ArrayToList($categories);

        $module_list = '';
        $module_titles = '';
        foreach ($new_modules as $name=>$title) {
            $valid_name = str_replace('/', '__', $name);
            $valid_name = str_replace('-', '_', $valid_name);
            $module_list   .= "|$name";
            $module_titles .= "$valid_name : '$title',";
        }
        $module_titles = substr($module_titles, 0, -1);

        addStyle($this->Add_Edit_Style);

        $image_list = '';

        foreach ($images as $image) {
            //$image_list .= "|$image::style=\"background-image: url(/office/images/menu/$image);\"";
            $image_list .= '<a class="image_selection" href="#"
                onclick="setImage(\''. $image .'\'); return false;">
               <img src="' . $this->Image_Dir . '/' . $image . '" alt="'.$image.'" /><br />' . $image . "</a>\n";
        }

        if ($new_modules) {
            $this->Form_Data_Array_Add = array(
                "form|$this->Action_Link|post|db_edit_form",
                "selecttext|Category|category|N|40|80||$category_list",
                "checkbox|Folder|is_folder||1|0",
                "select|Filename|filename|Y|
                   onchange=\"var myvalue = this.value.replace('/', '__');
                   var myvalue = myvalue.replace('-', '_');
                   getId('{$FormPrefix}title').value = module_titles[myvalue];
                \"$module_list",
                'js|var module_titles = {' . $module_titles . '}',
                "text|title|title|Y|60|80",
                //'code|<div class="select_image">',
                'info|Image|<div style="height:60px;"><img id="image_picture" src="" alt="" width="60" height="60" /></div>',
                'info|Image Name|<span id="image_name">none</span>',
                "hidden|image|",
                "js|function setImage(imageName) {
                    \$('#FORM_image').val(imageName);
                    \$('#image_name').html(imageName);
                    \$('#image_picture').attr('src','$this->Image_Dir/' + imageName);
                }",
                'code|<div id="select_images">',
                'code|' . $image_list,
                'code|</div>',
                "submit|Add Record|$this->Add_Submit_Name",
                "endform"
            );

        } else {
            $this->Form_Data_Array_Add = array(
                'code|<h2>No New Modules Found!</h2>'
            );
        }

        $this->Form_Data_Array_Edit = array(
            "form|$this->Action_Link|post|db_edit_form",
            "selecttext|Category|category|N|40|80||$category_list",
            'checkbox|Folder|is_folder||1|0',
            'text|Filename|filename|Y|60|80',
            'text|title|title|Y|60|80',

            'info|Image|<div style="height:60px;"><img id="image_picture" src="" alt="" width="60" height="60" /></div>',
            'info|Image Name|<span id="image_name">none</span>',
            "hidden|image|",
            "js|function setImage(imageName) {
                \$('#FORM_image').val(imageName);
                \$('#image_name').html(imageName);
                \$('#image_picture').attr('src','$this->Image_Dir/' + imageName);
            }
            setImage(\$('#FORM_image').val());
            ",

            'code|<div id="select_images">',
            'code|' . $image_list,
            'code|</div>',

            "js|
                var myelem = getId('{$FormPrefix}image');
                myelem.style.background='url(" . $this->Image_Dir . "/'+ myelem.value +')';
                myelem.style.backgroundRepeat = 'no-repeat';
                myelem.style.backgroundPosition = '0px 15px';",
            "checkbox|Active|active||1|0",
            "submit|Update Record|$this->Edit_Submit_Name",
            "endform"
        );


    }

    public function FindNewModules()
    {
        global $SITE_ROOT;

        $RESULT = array();
        $current_modules = $this->SQL->FieldArray($this->Table, 'filename');
        $files = GetDirectory("$SITE_ROOT/content", '.php');
        $files = SubTextBetweenArray('','.php',$files);

        foreach ($files as $file) {
            // check if already module
            if (!in_array($file, $current_modules)) {
                // check if has a def file
                $def_file = "$SITE_ROOT/content/$file.def";
                if (file_exists($def_file)) {
                    // check if has a name
                    $def_title = TextBetween('<name>', '</name>', file_get_contents($def_file));
                    if ($def_title) {
                        // add to result
                        $RESULT[$file] = $def_title;
                    }
                }
            }
        }
        return $RESULT;
    }
}
