<?php
class Lib_TinyMce
{
    public $Style_Sheet = '/office/css/site.css';

    public $External_Link_Item = '';
    //'external_link_list_url : "office_filelinks.php",';

    public $Tiny_Mce_File = '/jslib/tiny_mce/tiny_mce.js';
    public $Tiny_Mce_Path = '/jslib/tiny_mce';
    public $Image_Link_Directory = '';
    
    public $Image_Types = '.jpg,.png,.gif,.bmp,.swf';
    
    public $Js_Code = array();
    
    public function  __construct()
    {
    
        $this->Js_Code[0] = '
tinyMCE.init({
    mode: "exact",
    elements: "@@ELEMENTS@@",
    theme : "simple"
});';

        $this->Js_Code[1] = '
tinyMCE.init({
    mode: "exact",
    elements: "@@ELEMENTS@@",
    button_tile_map : true,
    theme : "advanced",
    plugins : "style,advhr,advimage,advlink,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,nonbreaking",
    theme_advanced_buttons1_add_before : "preview,fullscreen",
    //theme_advanced_buttons1_add : "fontselect,fontsizeselect",
    theme_advanced_buttons1_add : "fontsizeselect",
    theme_advanced_buttons2_add : "separator,zoom,separator,forecolor,backcolor",
    theme_advanced_buttons2_add_before: "@@CUT_COPY_PASTE@@pastetext,pasteword,separator,search,replace,separator",
    theme_advanced_buttons3_add: "nonbreaking",
    //theme_advanced_buttons3_add_before : "tablecontrols,style,separator",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_path_location : "bottom",
    extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
    theme_advanced_resizing : true,
    theme_advanced_resize_horizontal : true,
    apply_source_formatting : true,
    relative_urls : false,
    convert_urls : false,
    content_css : "@@STYLE_SHEET@@",
    popups_css : "@@TINY_MCE_PATH@@/themes/advanced/css/editor_popup.css",
    @@EXTERNAL_LINK_ITEM@@
    external_image_list_url : "@@IMAGE_LINK_DIR@@",
    gecko_spellcheck : true
});';

        $this->Js_Code[2] = '
tinyMCE.init({
    mode: "exact",
    elements: "@@ELEMENTS@@",
    button_tile_map : true,
    theme : "advanced",
    plugins : "style,table,advhr,advimage,advlink,insertdatetime,preview,searchreplace,print,contextmenu,paste,directionality,fullscreen,nonbreaking",
    theme_advanced_buttons1_add_before : "preview,fullscreen",
    theme_advanced_buttons1_add : "fontselect,fontsizeselect",
    theme_advanced_buttons2_add : "separator,zoom,separator,forecolor,backcolor",
    theme_advanced_buttons2_add_before: "@@CUT_COPY_PASTE@@pastetext,pasteword,separator,search,replace,separator",
    theme_advanced_buttons3_add: "nonbreaking",
    theme_advanced_buttons3_add_before : "tablecontrols,style,separator",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_path_location : "bottom",
    extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
    theme_advanced_resizing : true,
    theme_advanced_resize_horizontal : true,
    apply_source_formatting : true,
    relative_urls : false,
    convert_urls : false,
    content_css : "@@STYLE_SHEET@@",
    popups_css : "@@TINY_MCE_PATH@@/themes/advanced/css/editor_popup.css",
    @@EXTERNAL_LINK_ITEM@@
    external_image_list_url : "@@IMAGE_LINK_DIR@@",
    gecko_spellcheck : true
});';

    }


    public function AddTinyMce($elements, $template=2)
    {
        $BROWSER = (strpos(Server('HTTP_USER_AGENT'), 'MSIE') !== FALSE)? 'IE' : '';
        addScriptInclude($this->Tiny_Mce_File);

        $elements = str_replace(' ', '', $elements);
        $elements = 'FORM_' . str_replace(',', ',FORM_', $elements);

        $cut_copy_paste = ($BROWSER=='IE')? 'cut,copy,paste,' : '';

        $swap = array(
            '@@ELEMENTS@@'           => $elements,
            '@@CUT_COPY_PASTE@@'     => $cut_copy_paste,
            '@@STYLE_SHEET@@'        => $this->Style_Sheet,
            '@@TINY_MCE_PATH@@'      => $this->Tiny_Mce_Path,
            '@@EXTERNAL_LINK_ITEM@@' => $this->External_Link_Item,
            '@@IMAGE_LINK_DIR@@'     => $this->Image_Link_Directory
        );

        $text = $this->Js_Code[$template];        

        $text = astr_replace($swap, $text);
        addScript($text);
    }
    
    public function OutputImageLinks($dir)
    {

        $files = GetDirectory(Server('DOCUMENT_ROOT') . '/' . $dir, $this->Image_Types);

        $RESULT = 'var tinyMCEImageList = new Array(';
        
        $count=0;
        foreach ($files as $fi) {
            if ($count) {
                $RESULT .= ',';
            }
            $count++;
            $RESULT .= "['$fi', '/$dir/$fi']";
        }
        $RESULT .= ');';
        
        echo $RESULT;
    }

}