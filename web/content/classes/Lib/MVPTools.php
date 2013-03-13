<?php
class Lib_MVPTools
{
    public $LIB                 = '';
    public $lib                 = '';
    public $DOCUMENT_ROOT       = '';
    public $ROOT                = '';
    public $SCRIPT_FILENAME     = '';
    public $SCRIPT_NAME         = '';
    public $QUERY_STRING        = '';
    public $HTTP_HOST           = '';
    public $REQUEST_URI         = '';
    public $THIS_PAGE_QUERY     = '';
    public $THIS_PAGE           = '';
    public $HTTPS               = '';
    public $SCRIPT_URI          = '';
    public $HTTPS_URI           = '';
    public $HTTP_USER_AGENT     = '';
    public $REFERER             = '';
    public $PHP_SELF            = '';
    public $ENCRYPT_QUERY_KEY   = '';
    public $USE_MB              = false;  // set to true for astr_replace functions
    public $BROWSER             = '';
    public $MailCR              = "\n";
    public $SV                  = '';
    public $DOCTYPE_XHTML       = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">';
    
    
    public function  __construct()
    {
        $this->ClassInfo = array(
            'Created By'    => 'RAW',
            'Created'       => '2012-04-14',
            'Updated By'    => '',
            'Updated'       => '',
            'Revision'      => '00.01.00',
            'Description'   => 'Used to sanitize form inputs on a global scale',
        );
        
        /*
        ========== UPDATE LOG ==========
        04-14-2012 - RAW - Created file - conversion of /lib/MVPTools.php
        
        20061024 : added server variables
        20061121 : added GetDirectory
        20061122 : added qq, printq, printqn, printn
        20061222 : added strFrom, strTo
        20061227 : added write_file
        20070218 : directory sorted in natural case-insensitive order
        20070225 : omit svn from directory search
        20070307 : added PostGetStr
        20070325 : added GetFolders
        20070601 : added Get, Post
        20070616 : added SendHTMLmail
        20071004 : updated FromThisDomain
        20071010 : added NameToTitle
        20080421 : added stripos (for version 4), GetDirCount, directory now has both include and exclude options parameters
        20080615 : added mcrypt and hexencode functions
        20080701 : added astr_replace
        20080918 : added readln and filelinecount
        20090116 : added IntOnly
        20090123 : added RemoveExtension
        20090210 : added $USE_MB, mb_str_replace, mb_str_ireplace, (changed astr_replace to use $USE_MB)
        20090305 : added strFromLast
        20091129 : added FormatAddress
        20100630 : added WriteFileFtp
        20100720 : added GetIdFromVar
        20101218 : added url_exists
        */
        
        
        // ----- Initialize any passed in arguments
        $arguments = func_get_args();
        if(!empty($arguments)) {
            foreach($arguments[0] as $key => $property) {
                if(property_exists($this, $key)) {
                    $this->{$key} = $property;
                }
            }
        }
        
        
        
        
        
        
        
        $this->LIB = dirname(__FILE__);
        $this->lib = $LIB;

        $this->DOCUMENT_ROOT   = $this->Server('DOCUMENT_ROOT');
        $this->ROOT            = $this->DOCUMENT_ROOT;
        $this->SCRIPT_FILENAME = $this->Server('SCRIPT_FILENAME');
        $this->SCRIPT_NAME     = $this->Server('SCRIPT_NAME');
        $this->QUERY_STRING    = $this->Server('QUERY_STRING');
        $this->HTTP_HOST       = $this->Server('HTTP_HOST');
        $this->REQUEST_URI     = $this->Server('REQUEST_URI');
        if (empty($this->REQUEST_URI)) {
            $this->REQUEST_URI = "$SCRIPT_NAME?$QUERY_STRING";
        }
        $this->THIS_PAGE_QUERY = $this->REQUEST_URI;
        $this->THIS_PAGE       = preg_replace('/(;|\?|:).+$/', '', $this->THIS_PAGE_QUERY);
        $this->HTTPS           = $this->Server('HTTPS');
        $this->SCRIPT_URI      = empty($this->HTTPS)? 'http://'.$this->HTTP_HOST.$this->REQUEST_URI : 'https://'.$this->HTTP_HOST.$this->REQUEST_URI;
        $this->HTTPS_URI       = 'https://'.$this->HTTP_HOST.$this->REQUEST_URI;
        $this->HTTP_USER_AGENT = $this->Server('HTTP_USER_AGENT');
        $this->REFERER         = $this->Server('HTTP_REFERER');
        $this->PHP_SELF        = $this->Server('PHP_SELF');


        if (empty($this->ENCRYPT_QUERY_KEY)) {
            // should set this key for security, either before and after loading this file
            $this->ENCRYPT_QUERY_KEY = 'EncryptQu3ryK3y';
        }


        
        if (strpos($HTTP_USER_AGENT, 'MSIE') !== FALSE) $this->BROWSER = 'IE';
        elseif (strpos($HTTP_USER_AGENT, 'Firefox') !== FALSE) $this->BROWSER = 'FF';
        elseif (strpos($HTTP_USER_AGENT, 'Chrome/') !== FALSE) $this->BROWSER = 'CH';
        elseif (strpos($HTTP_USER_AGENT, 'Safari') !== FALSE) $this->BROWSER = 'SF';
        else $this->BROWSER = 'NotIE';

        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set('America/Los_Angeles');
        }




        $this->SV = (strpos(get_cfg_var('arg_separator.input'),';')!==false)? ';' : '&amp;';

        
        // ============CLEAR ANY MAGIC QUOTES============
        if (get_magic_quotes_gpc()) {
            $_GET = array_map('stripslashes', $_GET);
            $_POST = array_map('stripslashes', $_POST);
            //$_COOKIE = array_map('stripslashes', $_COOKIE);
            //$_REQUEST = array_map('stripslashes', $_REQUEST);
        }
        
        
        
        
    }
    
 

    public function PathFromRoot($path)
    {
        global $ROOT;
        return str_replace($ROOT, '', $path);
    }

    public function RootPath($path)
    {
        global $ROOT;
        $path = str_replace('\\', '/', $path);
        if (strpos($path, $ROOT) === false) {
            $s = (substr($path, 0, 1) == '/')? '' : '/';
            return $ROOT . $s . $path;
        }
        return $path;
    }


    

    //-----PRINT DOUBLE QUOTE------
    public function qq($text) {return str_replace('`','"',$text);}
    public function qqn($text) {return str_replace('`','"',$text)."\n";}
    public function printq($text) { print $this->qq($text);}
    public function printqn($text) { print $this->qq($text)."\n";}
    public function printn($text) { print $text."\n";}

    //-----POST-GET------

    public function TransformContent($var, $list)
    {
        //List: S=strip tags, T=trim, Q=remove quotes, H=htmlentities, A=addslashes
        if (empty($list)) return $var;
        $list = strtoupper($list);
        if (strpos($list,'S')!==false) $var = strip_tags($var);
        if (strpos($list,'Q')!==false) $var = str_replace(array('"',"'",'`'),'',$var);
        if (strpos($list,'H')!==false) $var = htmlentities($var);
        if (strpos($list,'T')!==false) $var = trim($var);
        if (strpos($list,'A')!==false) $var = addslashes($var);
        return $var;
    }

    public function SetPost($str, $ModStr='ST')
    {
        $VARS = explode(' ',$str);
        foreach ($VARS as $PV) $GLOBALS[$PV] = isset($_POST[$PV])? TransformContent($_POST[$PV],$ModStr) : '';
    }

    public function SetGet($str, $ModStr='ST')
    {
        $VARS = explode(' ',$str);
        foreach ($VARS as $PV) $GLOBALS[$PV] = isset($_GET[$PV])? TransformContent($_GET[$PV],$ModStr) : '';
    }

    public function SetBoth($str, $ModStr='ST')
    {
        $VARS = explode(' ', $str);
        foreach ($VARS as $PV) $GLOBALS[$PV] = isset($_REQUEST[$PV])? TransformContent($_REQUEST[$PV],$ModStr) : '';
    }

    public function SetPostArray($ArrayName, $str, $ModStr='ST')
    {
        $VARS = explode(' ', $str);
        foreach ($VARS as $PV) {
            $GLOBALS[$ArrayName][$PV] = isset($_POST[$ArrayName][$PV])? TransformContent($_POST[$ArrayName][$PV],$ModStr) : '';
        }
    }


    public function IntOnly($str)
    {
        //----- returns a string with only 0-9 values kept ------
        return preg_replace('/[^0-9]/', '', $str);
    }

    public function RemoveExtension($filename)
    {
        return preg_replace('/\.\w+$/U', '', $filename);
    }

    public function ArrayValue($array, $key)
    {
        return (($key != '') and isset($array) and isset($array[$key]))? $array[$key] : '';
    }

    // example:  parse_str(PostGetStr('NAME PASSWORD','POST'));
    public function PostGetStr($str,$type) {

    // ------------------- DEPRECATED ----------------------

      $VARS = explode(' ',$str);
      $RESULT = '';
      if ($type == 'POST') {
         foreach ($VARS as $PV) if (isset($_POST[$PV])) {
           $RESULT .= "$PV=".trim(strip_tags($_POST[$PV])).'&';} else {$RESULT .= "$PV=&";}
      } elseif ($type == 'GET') {
         foreach ($VARS as $PV) if (isset($_GET[$PV])) {
           $RESULT .= "$PV=".trim(strip_tags($_GET[$PV])).'&';} else {$RESULT .= "$PV=&";}
      } elseif ($type == 'BOTH') {
         foreach ($VARS as $PV) if (isset($_REQUEST[$PV])) {
           $RESULT .= "$PV=".trim(strip_tags($_REQUEST[$PV])).'&';} else {$RESULT .= "$PV=&";}
      }
      $RESULT = substr($RESULT,0,-1);
      return $RESULT;
    }


    public function Post($name) {return (isset($_POST[$name]))? $_POST[$name] : '';}
    public function Get($name) {return (isset($_GET[$name]))? $_GET[$name] : '';}
    public function GetPost($name) {return Request($name);}
    public function Request($name) {return (isset($_REQUEST[$name]))? $_REQUEST[$name] : '';}
    public function Session($name) {return (isset($_SESSION[$name]))? $_SESSION[$name] : '';}
    public function Server($name) {return (isset($_SERVER[$name]))? $_SERVER[$name] : '';}
    public function Cookie($name) {return (isset($_COOKIE[$name]))? $_COOKIE[$name] : '';}



    //-----GET VALID BASE TO CHECK REFERRER------
    public function GetValidBase($mydomain)
    {
        if (substr($_SERVER['HTTP_REFERER'],0,11)=='http://www.') {return "http://www.$mydomain";}
        else {return "http://$mydomain";}
    }

    public function FromThisDomain()
    {
        $ValidBase = Server('HTTP_HOST');
        $Base = strFrom(Server('HTTP_REFERER'),'://');
        $Base = strTo($Base,'/');
        return ($Base == $ValidBase);
    }


    public function CheckEmail($email, $use_quotes=false)
    {
        if (!$use_quotes) {
            if (preg_match('/["\'\\\]/', $email)) return false;
        }
        $at_index       = strrpos($email, '@');
        if ($at_index === false) return false;  // no @

        //----local check----
        $local = substr($email, 0, $at_index);
        if (empty($local) or (strlen($local) > 64)) return false;  // max local length=64

        if (preg_match('/^\..*|.*\.$/', $local)) return false; // cannot start or end with dot
        $local = preg_replace("/\\\./", '-', $local); // remove slash items
        if (substr_count($local, '"') > 2) return false;  // cannot have more than two non-slashed quotes

        $local = preg_replace('/".+"$/', '', $local); // remove double quoted content
        if (!empty($local)) {
            $local = preg_replace("/\\\./", '-', $local); // remove slash items
            if (substr($local,-1) == '\\') return false;
            if (!preg_match('/^([A-Za-z0-9!#\$%&\'*\+\/\=\?\^_`\{|\}~\.-]+)$/', $local)) return false; // filter bad characters
            if (strpos($local, '..') !== false) return false; // cannot have double dots
        }
        //----domain check----
        $domain = substr($email, $at_index + 1);
        if (strlen($domain > 255)) return false;
        if (!preg_match('/([0-9a-z\.-]+)\.([a-z]{2,6})$/', $domain)) return false;
        $domain_labels = explode('.', $domain);
        foreach ($domain_labels as $label) {
            if (empty($label) or (strlen($label) > 63)) return false;
        }
        return true;
    }

    //------------ SEND EMAIL --------------
    public function SendHTMLmail($FromName, $FromEmail, $Recipientlist, $Subject, $Message, $CC='', $BCC='')
    {
        global $MailCR;
        $headers  = "From: $FromName <$FromEmail>$MailCR";
        if ($CC)  $headers  .= "Cc: $CC$MailCR";
        if ($BCC) $headers  .= "Bcc: $BCC$MailCR";
        $headers .= "MIME-Version: 1.0$MailCR";
        $headers .= "Content-type: text/html; charset=utf-8$MailCR";

        $count = 0;
        do {
            $RESULT = mail($Recipientlist, "$Subject", $Message, $headers);
            $count++;
            if (!$RESULT) sleep(1);
        } while (($count < 10) and (!$RESULT));

        return $RESULT;
    }

    //-----------WRITE FILE-----------
    public function write_file($filename, $filetext)
    {
        $filepointer = fopen($filename,"w");
        fwrite($filepointer,$filetext);
        fclose($filepointer);
    }

    public function WriteFileFtp($ftp_server, $ftp_username, $ftp_password, $ftp_file_path,  $content)
    {
        $hostname = "ftp://$ftp_username:$ftp_password@" . $ftp_server . $ftp_file_path;
        $context  = stream_context_create(array('ftp' => array('overwrite' => true)));
        return file_put_contents($hostname, $content, 0, $context);
    }

    public function WriteFileFtpC($ftp_conn, $ftp_file_path, $content, $mode=FTP_BINARY)
    {
        $tmp = tmpfile();
        fwrite($tmp, $content);
        fseek($tmp, 0);
        ftp_fput($ftp_conn, $ftp_file_path, $tmp, $mode);
        fclose($tmp);
    }

    public function append_file($filename, $filetext)
    {
        if (!file_exists($filename)) {
            write_file($filename,$filetext);
        } else {
            $filepointer = fopen($filename,"a");
            fwrite($filepointer,$filetext);
            fclose($filepointer);
        }
    }


    //-----------SUBSTRINGS-----------

    public function strIn($string, $within, $offset=0)
    {
        if (empty($within) or empty($string)) {
            return false;
        } else {
            return (stripos($string, $within, $offset) !== false);
        }
    }

    public function strTo($string, $to) {
        if (empty($to)) {
            return $string;
        }
        $i = strpos($string, $to);
        return ( $i !== false )? substr($string, 0, $i) : $string;
    }

    public function strFrom($string, $from) {
        if (empty($from) or empty($string)) return $string;
        $i = strpos($string, $from);
        return ( $i !== false )? substr($string, $i+strlen($from)) : '';
    }

    public function strFromLast($string, $from) {
        if (empty($from) or empty($string)) return $string;
        $i = strrpos($string, $from);
        return ( $i !== false )? substr($string, $i+strlen($from)) : '';
    }

    public function TruncStr($str,$count) {
      $str = trim($str);
      if (strlen($str) < $count) return $str;
      $str = substr($str,0,$count + 1);
      if (substr($str,-1) != ' ') while((substr($str,-1) != ' ') and (strlen($str)>0)) $str = substr($str,0,-1);
      $str = rtrim($str);
      $pos = strrpos($str, '<');
      if ($pos !== false) $str = substr($str,0,$pos-1);
      return $str.'&hellip;';
    }

    //-----Convert File Names/Variables to Title Words
    public function NameToTitle($string) {
      //separate camelcase and underscores words in title
      $SText = str_replace(array('_', '-'), ' ', $string);
      $text = '';
      for ($i=0; $i < strlen($SText); $i++) {
         $ch = $SText[$i];
         if ((($i > 0) and ($SText[$i-1] > 'Z') and ($ch < 'a'))  or
            (($i > 0) and ($ch < 'a') and ($i<strlen($SText)-1) and ($SText[$i+1] > 'Z'))) $text .= ' ';
         $text .= $ch;
      }
      $text = preg_replace('/\s\s+/', ' ', $text);  //remove white space
      return ucwords(trim($text));
    }

    public function FormatAddress($str, $br = '<br />')
    {
        // requires \n for line breaks, removes orphan commas
        return trim(preg_replace(array('/\r/', '/\B,/', '/\n\s+/', '/\s+\n/', '/\n+/'), array('', '', "\n", "\n", $br), trim($str)));
    }


    //-----GET TEXT BETWEEN STRINGS------
    public function TextBetween($s1,$s2,$s)
    {
        $L=strlen($s1);
        $pos1 = ($L>0)? stripos($s, $s1) : 0;

        if ($pos1 !== false) {
            if ($s2 == '') return substr($s, $pos1 + $L);
            $pos2 = stripos(substr($s, $pos1 + $L), $s2);
            if ($pos2 !== false) return substr($s, $pos1 + $L, $pos2);
        }
        return '';
    }


    //-----GET ARRAY TEXT BETWEEN STRINGS------
    public function TextBetweenArray($s1, $s2, $s)
    {
        $myarray = array();
        $L1=strlen($s1);
        $L2=strlen($s2);

        do {
            $pos1 = stripos($s, $s1);
            if ($pos1 !== false) {
                $pos2 = stripos(substr($s, $pos1 + $L1), $s2);
                if ($pos2!==false) {
                    $myarray[] = substr($s, $pos1 + $L1, $pos2);
                    $s = substr($s, $pos1 + $L1 + $pos2 + $L2);
                }
            }
        } while (($pos1!==false) and ($pos2!==false));

        return $myarray;
    }

    //-----GET SUBTEXT IN ARRAY ITEMS------
    public function SubTextBetweenArray($s1,$s2,$myarray) {
      for ($i=0; $i< count($myarray); $i++)
       {$myarray[$i]=TextBetween($s1,$s2,$myarray[$i]);}
      return $myarray;
    }

    //-----CONVERT E-MAIL STRING TO CHARACTER CODES------
    public function ConvertString($s) {
      $result='';
      for($i=0;$i<strlen($s);$i++) { $result.='&#'.ord(substr($s,$i,1)).';'; }
      return $result;
    }

    //-----ARRAY TO STRING------

    public function AssocArrayToStr($array)
    {
        // deprecated use ArrayToStr($array)
        return ArrayToStr($array);
    }

    public function ArrayToStr($array, $html=true)
    {
        $RESULT = '';
        if (!empty($array) and is_array($array)) {
            if ($html) {
                $RESULT = '<pre style="text-align:left;">' . htmlspecialchars(print_r($array, true)) . '</pre>';
            } else {
                $RESULT = print_r($array, true);
            }
        }
        return $RESULT;
    }



    //-----TRIM ARRAY------
    public function TrimValue(&$value) {$value = trim($value);}
    
    public function TrimArray(&$myarray) {
        if ($myarray) {
            array_walk($myarray, 'TrimValue');
        }
    }

    //-----WRITE MESSAGE------
    public function MText($title,$message) {
        global $DOCTYPE_XHTML;
        print <<<MTL
    $DOCTYPE_XHTML
    <head><title>$title</title></head><body style="background-color:#006;">
    <table align="center" style="background-color:#fff; color:#000; border:2px solid #f00; padding:1em; margin-top:20px;">
    <tbody><tr><td align="center">$message</td></tr></tbody></table>
    </body></html>
MTL;
        exit;
    }

    //-----DISPLAY ALERT------
    public function alert($message)
    {
        echo JavaScriptString("alert('$message');");
    }

    //----HTML OUTPUT----
    public function StyleString($mystring)
    {
        if ($mystring) {
            $mystring = trim($mystring);
            return "<style type=\"text/css\">\n$mystring\n</style>";
        } else {
            return '';
        }
    }

    public function JavaScriptString($mystring)
    {
        if ($mystring) {
            return "<script type=\"text/javascript\">\n/* <![CDATA[ */\n$mystring\n/* ]]> */\n</script>";
        } else  {
            return '';
        }
    }

    //--------DATE FUNCTIONS----------

    public function DateToDashes($d)
    {
        //for 4 or 6 char date YYYYMMDD or YYYYMM
        $d=str_replace('-', '', $d);
        if (strlen($d)>6) return substr($d,0,4).'-'.substr($d,4,2).'-'.substr($d,6,2);
        else return substr($d,0,4).'-'.substr($d,4,2);
    }

    public function DateWODashes($d)
    {
        //for 4 or 6 char date YYYY-MM-DD or YYYY-MM
        return str_replace('-','',$d);
    }


    public function DateToStdHM($d)
    {
        $hv = intval(substr($d,8,2));
        if ($hv > 11) {$ampm='pm'; $hv=$hv-12;} else {$ampm='am';}
        if ($hv==0) {$hv=12;}
        if (strlen($hv)==1) {$hv='0'.$hv;}
        $R=substr($d,4,2).'/'.substr($d,6,2).'/'.substr($d,0,4).' - '.$hv.':'.substr($d,10,2).$ampm;
        return $R;
    }



    //---------CHECK IF ARRAY ITEMS IN STRING----------
    public function ArrayItemsWithinStr($myarray, $str)
    {
        $arraycount = count($myarray);
        if (empty($str) or ($arraycount==0)) return false;
        for ($i=0; $i < $arraycount; $i++) {
            if (stripos($str, $myarray[$i]) !== false) return true;
        }
        return false;
    }

    public function GetDirectory($url, $includestr='', $excludestr='', $subdirectories=true)
    {
        //gets a directory and subdirectory filelist with optional include and exclude string
        $files  = array();
        if (!file_exists($url)) return $files;
        if (!empty($includestr)) $include_strings = explode(',',$includestr);
        if (!empty($excludestr)) $exclude_strings = explode(',',$excludestr);
        $url = (substr($url, -1) == DIRECTORY_SEPARATOR) ? $url : $url . DIRECTORY_SEPARATOR;
        $dir = opendir($url);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                $path_file = $url . $file;
                if (is_dir($path_file)) {
                    if ($subdirectories) {
                        $echeck = (!empty($excludestr))? !ArrayItemsWithinStr($exclude_strings, $file . '/') : true;
                        if ($echeck and (strpos($file, '.svn') === false)) {
                            $sfiles = GetDirectory($path_file, $includestr, $excludestr);
                            foreach ($sfiles as $f) {
                                $files[] = $file . DIRECTORY_SEPARATOR . $f;
                            }
                        }
                    }
                } else {
                    $icheck = (!empty($includestr))? ArrayItemsWithinStr($include_strings, $file) : true;
                    $echeck = (!empty($excludestr))? !ArrayItemsWithinStr($exclude_strings, $file) : true;
                    if ($icheck and $echeck) $files[] = $file;
                }
            }
        }
        closedir($dir);
        $files = str_replace('\\', '/', $files);  // fixes windows directory problems

        $list1 = array();
        $list2 = array();
        foreach ($files as $file){
            if (strpos($file, '/') === false) {
                $list1[] = $file;
            } else {
                $list2[] = $file;
            }
        }
        natcasesort($list1);
        natcasesort($list2);

        $files = Array_Merge($list1, $list2);
        return $files;
    }

    public function GetDirCount($url, $includestr='', $excludestr='', $subdirectories=true)
    {
        //gets Count of directory and subdirectory Count
        if (!empty($includestr)) $include_strings = explode(',',$includestr);
        if (!empty($excludestr)) $exclude_strings = explode(',',$excludestr);

        $count  = 0;
        if (!file_exists($url)) return 0;
        if (!empty($includestr)) $include_strings = explode(',',$includestr);
        if (!empty($excludestr)) $exclude_strings = explode(',',$excludestr);
        $dir = opendir("$url/");
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir("$url/$file")) {
                    if ($subdirectories) {
                        if (!eregi('.svn',$file)) {
                            $scount = GetDirCount("$url/$file",$includestr,$excludestr);
                            $count += $scount;
                        }
                    }
                } else {
                    $icheck = (!empty($includestr))? ArrayItemsWithinStr($include_strings,$file) : true;
                    $echeck = (!empty($excludestr))? !ArrayItemsWithinStr($exclude_strings,$file) : true;
                    if ($icheck and $echeck) $count++;
                }
            }
        }
        closedir($dir);
        return $count;
    }

    public function GetFolders($url)
    {
        //gets a folders only
        $folders = array();
        if (!file_exists($url)) return $folders;
        $dir = opendir("$url/");
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir("$url/$file")) {
                    if (!eregi('.svn',$file)) {
                        $folders[] = $file;
                        $sfolders = GetFolders("$url/$file");
                        foreach ($sfolders as $f) $folders[] = "$file/$f";
                    }
                }
            }
        }
        closedir($dir);
        natcasesort($folders);
        $files = array_values($folders);
        return $folders;
    }


    //--------------- functions for PHP < 5.2 -------------------
    public function json_encode($array) {
        //{"a":1,"b":2,"c":3,"d":4,"e":5}  Note options from 5.3 is not supported
        $RESULT = '';
        foreach($array as $key => $value) {
            $RESULT .= '"' . addslashes($key) . '":"' . addslashes($value) . '",';
        }
        $RESULT = substr($RESULT, 0, -1);
        $RESULT = '{' . $RESULT . '}';
        return $RESULT;
    }




    //--------------- functions for PHP 4 -------------------
      public function str_ireplace($search,$replace,$subject) {
        $token = chr(1);
        $haystack = strtolower($subject);
        $needle = strtolower($search);
        while (($pos=strpos($haystack,$needle))!==FALSE) {
            $subject = substr_replace($subject,$token,$pos,strlen($search));
            $haystack = substr_replace($haystack,$token,$pos,strlen($search));
        }
        $subject = str_replace($token,$replace,$subject);
        return $subject;
      }

      

        public function stripos($haystack, $needle) {
            return strpos($haystack, stristr( $haystack, $needle ));
        }
    
        public function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT) {
            return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
        }
    


    //======================== encryption ===========================
    public function HexEncodeString($str)
    {
        $RESULT = '';
        $strlen = strlen($str);
        for ($i=0; $i < $strlen; $i++) {
            $RESULT .= sprintf("%02x",ord(substr($str,$i,1)));
        }
        return $RESULT;
    }

    public function HexDecodeString($str)
    {
        $RESULT = '';
        $strlen = strlen($str);
        for ($i=0; $i < $strlen; $i+=2) {
            $RESULT .= chr(hexdec(substr($str,$i,2)));
        }
        return $RESULT;
    }

    public function EncryptStringHex($string, $key)
    {   // encrypts string using key and converts to a two-char per byte hex string
        return EncryptString($string, $key, 0, 'hex');
    }


    public function DecryptStringHex($string, $key)
    {
        return DecryptString($string, $key, 0, 'hex');
    }


    public function EncryptString($string, $key, $compress=2, $encode='64')
    {   // encrypts string using key and converts to base 64 encoding

        if (!$string) {
            return '';
        }

        srand(crc32($key));
        if ($compress == 1) {
            $string = gzcompress($string, 9);
        } elseif ($compress == 2) {
            $string = gzdeflate($string, 9);
        }
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CFB, '');  // open module
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        $keystr = substr(sha1($key), 0, $ks);
        mcrypt_generic_init($td, $keystr, $iv);
        $RESULT = mcrypt_generic($td, $string);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td); // close module
        if ($encode == '64') {
            $RESULT = strtr(base64_encode($RESULT),'+/=', '-_~');
        } elseif ($encode == 'hex') {
            $RESULT = HexEncodeString($RESULT);
        }
        return $RESULT;
    }

    public function DecryptString($string, $key, $compress=2, $encode='64')
    {
        if (!$string) {
            return '';
        }

        srand(crc32($key));
         if ($encode == '64') {
            $unencoded_string = base64_decode(strtr($string, '-_~', '+/='));
        } elseif ($encode == 'hex') {
            $unencoded_string = HexDecodeString($string);
        }

        $td  = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CFB, '');  // open module
        $iv  = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $key = substr(sha1($key), 0, mcrypt_enc_get_key_size($td));
        mcrypt_generic_init($td, $key, $iv);

        $RESULT = mdecrypt_generic($td, $unencoded_string);

        set_error_handler('IntOnly');  // dummy error handler

        if ($compress == 1) {
            $RESULT = @gzuncompress($RESULT);
        } elseif ($compress == 2) {
            $RESULT = @gzinflate($RESULT);
        }

        restore_error_handler();

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td); // close module
        return $RESULT;
    }


    public function EncryptQuery($str)
    {
        global $ENCRYPT_QUERY_KEY;
        return EncryptString($str, $ENCRYPT_QUERY_KEY);
    }

    public function GetEncryptQuery($encrypted_query_var, $get_variable_name=true)
    {
        // sets $_GET variables from an encrypted $_GET variable($encrypted_query_var)
        global $ENCRYPT_QUERY_KEY;

        $RESULT = array();

        if ($get_variable_name) {
            $encrypted_query = Get($encrypted_query_var);
        } else {
            $encrypted_query = $encrypted_query_var;
        }
        if ($encrypted_query) {
            $qs = DecryptString($encrypted_query, $ENCRYPT_QUERY_KEY);
            if (!empty($qs)) {
                $qs = str_replace('\&', chr(1) , $qs);
                $qs = str_replace('\;', chr(2) , $qs);
                $qs = str_replace('?','&',$qs);
                $qs = str_replace(';','&',$qs);
                $qlist = explode('&',$qs);
                $qlist = str_replace(chr(1), '&' , $qlist);
                $qlist = str_replace(chr(2), ';' , $qlist);
                foreach ($qlist as $var) {
                    $key   = strTo($var, '=');
                    $value = strFrom($var, '=');
                    $RESULT[$key] = trim($value);
                }
            }
        }
        return $RESULT;
    }



    public function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public function astr_replace($array, $string, $ignore_case=false)
    {
        global $USE_MB;

        $old = array_keys($array);
        $new = array_values($array);
        if ($USE_MB) {
            for ($i=0, $count=count($old); $i<$count; $i++) {
                if($ignore_case) {
                    $string = mb_str_ireplace($old[$i], $new[$i], $string);
                } else {
                    $string = mb_str_replace($old[$i], $new[$i], $string);
                }
            }
            return $string;
        } else {
            if($ignore_case) {
                return str_ireplace($old, $new, $string);
            } else {
                return str_replace($old, $new, $string);
            }
        }
    }

    public function mb_str_replace($old, $new, $string)
    {
        $offset  = 0;
        if (!empty($old) and !empty($string)) {
            while(($pos = mb_strpos($string, $old, $offset)) !== false)
            {
                $offset  = $pos + mb_strlen($new);
                $string  = mb_substr($string, 0, $pos) . $new . mb_substr($string, $pos + mb_strlen($old));
            }
        }
        return $string;
    }

    public function mb_str_ireplace($old, $new, $string)
    {
        $stringM = mb_strtolower($string);
        $oldM    = mb_strtolower($old);
        $offset  = 0;

        while(($pos = mb_strpos($stringM, $oldM, $offset)) !== false)
        {
            $offset  = $pos + mb_strlen($new);
            $string  = mb_substr($string, 0, $pos). $new . mb_substr($string, $pos + mb_strlen($old));
            $stringM = mb_strtolower($string);
        }
        return $string;
    }


    public function readln($filename='', $SetCSV=false)
    {
        // call first with filename and CSV to set up handle, then call with empty varlist
        // call with filename == close to close handle
        static $handle = null;
        static $CSV = false;

        if ($filename) {
            if (($filename=='close') and ($handle)) {
                fclose($handle);
                $handle = null;
                return true;
            } else {
                $handle = @fopen($filename, "r");
                $CSV = $SetCSV;
                return $handle;
            }
        }

        if ($handle) {
            if (!feof($handle)) {
                if ($CSV) {
                    return fgetcsv($handle);
                } else {
                    return fgets($handle);
                }
            } else {
                fclose($handle);
                return false;
            }
        } else return false;
    }

    public function FileLineCount($filename)
    {
        $handle = @fopen($filename, "r");

        $RESULT = 0;
        if ($handle) {
            while (!feof($handle)) {
                fgets($handle);
                $RESULT++;
            }
            fclose($handle);
        }
        return $RESULT;
    }


    //--------SEND XML----------
    public function SendReceiveXML($url, $page, $post_xml)
    {
        $header  = "POST ".$page." HTTP/1.0 \r\n";
        $header .= "MIME-Version: 1.0 \r\n";
        $header .= "Content-type: application/PTI26 \r\n";
        $header .= "Content-length: ".strlen($post_xml)." \r\n";
        $header .= "Content-transfer-encoding: text/xml \r\n";
        $header .= "Request-number: 1 \r\n";
        $header .= "Document-type: Request \r\n";
        $header .= "Interface-Version: Test 1.4 \r\n";
        $header .= "Connection: close \r\n\r\n";
        $header .= $post_xml;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $header);

        $data  = curl_exec($ch);
        $error = curl_errno($ch);
        curl_close($ch);
        if ($error) {
            return "$data\nCURL ERROR($error)";
        }
        return $data;
    }


    /*
     * Params:
     *   $host      - Just the hostname.  No http:// or  /path/to/file.html portions
     *   $path      - The /path/to/file.html part
     *   $data      - The query string, without initial question mark
     *   $port      - the port (80 or 443)
     *   $useragent - If true, 'MSIE' will be sent as the User-Agent (optional)
     */
    public function SendPost($host, $path, $data, $port=80, $useragent=0)
    {
        $fp = @fsockopen($host, $port, $error_number, $error_string);

        if (!$fp) Mtext('Failure', 'fsockopen Failed - could not post: ' . $error_string, 30);  // does not return

        $http = ($port == 80)? 'HTTP' : 'HTTPS';
        fputs($fp, "POST $path $http/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-length: " . strlen($data) . "\r\n");
        if ($useragent) {
            fputs($fp, "User-Agent: MSIE\r\n");
        }
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);

        $buf = '';
        while (!feof($fp)) {
            $buf .= fgets($fp, 128);
        }
        fclose($fp);
        return $buf;
    }

    public function url_exists($url)
    {
       $headers = @get_headers($url);
       return is_array($headers)? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $headers[0]) : false;
    }


    public function GetPassword($size=8, $password_chars='abcdefghjkmnpqrtuvwxyz2346789')
    {
        $RESULT = '';
        $max = strlen($password_chars) - 1;
        for ($i= 0; $i < $size; $i++) {
            $RESULT .= substr($password_chars, rand(0,$max) ,1);
        }
        return $RESULT;
    }

    public function GetIdFromVar($var)
    {
        $RESULT = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $var);
        if (!preg_match('/^[a-zA-Z]/', $RESULT)) {
            $RESULT = 'id_' . $RESULT;
        }
        return $RESULT;
    }



} // end class