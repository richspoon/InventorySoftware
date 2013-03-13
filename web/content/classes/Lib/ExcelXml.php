<?php
//file: Lib/Lib_ExcelXml.php

class Lib_ExcelXml
{
    private $Styles = '';

    private $Excel_Header = '<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">';


    public function AddProperties($author)
    {
        $date = str_replace(array('@', '#'), array('T', 'Z'), date('Y-m-d@H:i:s#'));
        return '
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <LastAuthor>' . $author . '</LastAuthor>
  <Created>' . $date  . '</Created>
  <LastSaved>' . $date  . '</LastSaved>
  <Version>12.00</Version>
 </DocumentProperties>';
    }

    public function AddHeaderStyle($font_color, $cell_color, $number)
    {
        $style = '<Font ss:Color="' . $font_color . '" ss:Bold="1"/>
     <Interior ss:Color="' . $cell_color . '" ss:Pattern="Solid"/>';
        $this->AddStyle($style, $number);
    }
    
    public function AddAutoWidthColumns($count)
    {
        $RESULT = '';
        for ($i=0; $i < $count; $i++) {
            $RESULT .= "\n  <Column ss:AutoFitWidth=\"1\"/>";
        }
        return $RESULT;
    }


    public function AddStyle($style, $number)
    {
        $this->Styles .= "<Style ss:ID=\"s$number\">
    $style
  </Style>";
    }

    public function AddStyles()
    {
        return "
 <Styles>
    {$this->Styles}
 </Styles>";
    }

    public function OutputFileHeaders($filename)
    {
        header('Content-Type: application/vnd.ms-excel; charset="UTF-8"');
        header('Content-Disposition: inline; filename="' . $filename . '.xml"');
    }

    public function StartWorkbook()
    {
        return $this->Excel_Header;
    }


    public function EndWorkbook()
    {
        return '
</Workbook>';
    }


    public function StartWorksheet($name='Sheet1')
    {
        return '
<Worksheet ss:Name="' . $name . '">
<Table>';
    }

    public function EndWorksheet()
    {
        return '
</Table>
</Worksheet>';
    }

    public function StartRow()
    {
        return '
  <Row>';
    }

    public function EndRow()
    {
        return '
  </Row>';
    }

    public function AddCell($value, $type='String', $style_id='')
    {
        // types = String
        if (empty($type)) {
            $type = 'String';
        }
        $style_id = ereg_replace('[^0-9]', '', $style_id);
        $style =  ($style_id)? " ss:StyleID=\"s$style_id\"" : '';
        return "
    <Cell$style>
     <Data ss:Type=\"$type\">$value</Data>
    </Cell>";
    }

}
