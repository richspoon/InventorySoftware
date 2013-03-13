<?php

class Lib_Sanitize
{
    public function  __construct()
    {
        $this->ClassInfo = array(
            'Created By'    => 'RAW',
            'Created'       => '2012-04-20',
            'Updated By'    => '',
            'Updated'       => '',
            'Revision'      => '00.01.00',
            'Description'   => 'Used to sanitize form inputs on a global scale',
        );
        
        /*
        ========== UPDATE LOG ==========
        04-12-2012 - RAW - Created file
        */
    }
    
    public function SanitizeAll($show_array = false) 
    {
        // FUNCTION :: Calls a general sanitization on $_GET, $_POST, and $_SERVER
        
        $this->SanitizeArray($_POST, $show_array);
        $this->SanitizeArray($_GET, $show_array);
        $this->SanitizeArray($_SERVER, $show_array);
    }
    
    public function SanitizeArray(&$ARR, $show_array = false) 
    {
        // FUNCTION :: Sanitize any array of values (SINGLE DIMENSION)
        if (is_array($ARR)) {
            if ($show_array) { echo '<hr>'.ArrayToStr($ARR); };
            $ARR = array_map(array($this, 'filter'), $ARR);             // call filter function
            if ($show_array) { echo '<hr>'.ArrayToStr($ARR); };
        }
    }
    
    public function SanitizeVar(&$VAR, $show=false)
    {
        if ($show) { echo '<hr>'.$VAR; };
        $VAR = $this->filter($VAR);                                     // call filter function
        if ($show) { echo '<hr>'.$VAR; };
    }
    
    public function SanitizePage($show_array = false) 
    {
        // FUNCTION :: Sanitize all $_POST variables by calling the proper function
        global $PAGE;
        
        if ($show_array) { echo '<hr>'.ArrayToStr($PAGE); };
        
        foreach ($PAGE as $k=>$v) {
            switch ($k) {
                case 'query':
                case 'printversionlink':
                case 'ajaxlink':
                case 'pagelink':
                case 'pagelinkquery':
                case 'url':
                    $v = urldecode($v);     // convert chars back to HTML (i.e. '%3C' to  '<');
                break;
                
                default:
                break;
            }
            
            $PAGE[$k] = filter($v);                      // call filter function
        }
        
        
        if ($show_array) { echo '<hr>'.ArrayToStr($PAGE); };
    }

    
    
    
    private function filter($data) 
    {
        // FUNCTION :: Call all the various filtering functions for the sent in variable
        // NOTE --> currently not using strip tags because that still eaves the text between the tags and we don't want that
        
        //$data = htmlspecialchars_decode($data);
        
        $data = trim($this->remove_HTML($data));
        
        //$data - filter_var($data, FILTER_SANITIZE_SPECIAL_CHARS);
        //$data = trim(htmlentities(strip_tags($data)));
        
        /*
        if (get_magic_quotes_gpc())
            $data = stripslashes($data);
     
        $data = mysql_real_escape_string($data);
        */
        return $data;
    }


    private function remove_HTML($s , $keep = '' , $expand = 'script|style|noframes|select|option')
    {
        // FUNCTION :: remove_HTML --> removes all tags defined and removes content between those tags

        
        /**///prep the string
        $s = ' ' . $s;
       
        /**///initialize keep tag logic
        if(strlen($keep) > 0){
            $k = explode('|',$keep);
            for($i=0;$i<count($k);$i++){
                $s = str_replace('<' . $k[$i],'[{(' . $k[$i],$s);
                $s = str_replace('</' . $k[$i],'[{(/' . $k[$i],$s);
            }
        }
       
        //begin removal
        /**///remove comment blocks
        while(stripos($s,'<!--') > 0){
            $pos[1] = stripos($s,'<!--');
            $pos[2] = stripos($s,'-->', $pos[1]);
            $len[1] = $pos[2] - $pos[1] + 3;
            $x = substr($s,$pos[1],$len[1]);
            $s = str_replace($x,'',$s);
        }
       
        /**///remove tags with content between them
        if(strlen($expand) > 0){
            $e = explode('|',$expand);
            for($i=0;$i<count($e);$i++){
                while(stripos($s,'<' . $e[$i]) > 0){
                    $len[1] = strlen('<' . $e[$i]);
                    $pos[1] = stripos($s,'<' . $e[$i]);
                    $pos[2] = stripos($s,$e[$i] . '>', $pos[1] + $len[1]);
                    $len[2] = $pos[2] - $pos[1] + $len[1];
                    $x = substr($s,$pos[1],$len[2]);
                    $s = str_replace($x,'',$s);
                }
            }
        }
       
        /**///remove remaining tags
        while(stripos($s,'<') > 0){
            $pos[1] = stripos($s,'<');
            $pos[2] = stripos($s,'>', $pos[1]);
            $len[1] = $pos[2] - $pos[1] + 1;
            $x = substr($s,$pos[1],$len[1]);
            $s = str_replace($x,'',$s);
        }
       
        /**///finalize keep tag
        if(strlen($keep) > 0){
        for($i=0;$i<count($k);$i++){
            $s = str_replace('[{(' . $k[$i],'<' . $k[$i],$s);
            $s = str_replace('[{(/' . $k[$i],'</' . $k[$i],$s);
        }
        }
       
        return trim($s);
    } 


} // end class