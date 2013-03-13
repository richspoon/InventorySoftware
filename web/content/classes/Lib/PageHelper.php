<?php

class Lib_PageHelper
{
    public $SITECONFIG      = '';   // must have this defined first
    public $PAGE_ALIAS      = '';
    
    
    
public $DOCUMENT_ROOT   = '';
public $ROOT            = '';
public $SCRIPT_FILENAME = '';
public $SCRIPT_NAME     = '';
public $QUERY_STRING    = '';
public $HTTP_HOST       = '';
public $REQUEST_URI     = '';
public $THIS_PAGE_QUERY = '';
public $THIS_PAGE       = '';
public $HTTPS           = '';
public $SCRIPT_URI      = '';
public $HTTPS_URI       = '';
public $HTTP_USER_AGENT = '';
public $REFERER         = '';
public $PHP_SELF        = '';
    
    public $User_Agent_File = "user_agent_list.dat";
    
    public $PAGE   = array(
        'ERROR'                     => '',
        'MESSAGE'                   => '',
        'FLASH'                     => '',
        'START_TIME'                => '',
        'DATETIME'                  => '',
        'MENU_ID_TEMPLATE'          => 'id="menu_@"',
        'MENU_SELECT_TEMPLATE'      => 'id="pageselected"',
        'SCRIPTINC'                 => '',
        'SCRIPT'                    => '',
        'SCRIPT_ONREADY'            => '',
        'SCRIPT_ONLOAD'             => '',
        'STYLE'                     => '',
        'STYLE_SHEETS'              => '',
        'SCRIPT_ONREADY_TEMPLATE'   => "\n\$(function(){\n@\n});\n",
        'SCRIPT_ONLOAD_TEMPLATE'    => "\nwindow.onload = function(){\n@\n}\n",
        'original_name'             => '',
        'PAGE_CONTENT_ONLY'         => 0
    );
    
    public $SITE_DIR            = '';
    public $PAGE_DIR            = '';
    public $SITE_ROOT           = '';
    public $PAGEDEFAULT         = 'index';
    public $PAGE_SWAP_VARIABLES = array();  // assoc array of additional items to swap
    public $BLOCK_LIST_TERMS    = array('awstats','xmlrpc','chat/','phpmyadmin','phpMyAdmin-','/main','azenv','thisdoesnotexistahaha');
    public $QUERYVAR            = array();
    public $AJAX                = 0;
    
    

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
        04-14-2012 - RAW - Created file - conversion of /lib/page_helper.php
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

        
        
        
        

        $this->DOCUMENT_ROOT    = $_SERVER['DOCUMENT_ROOT'];
        $this->ROOT             = $_SERVER['DOCUMENT_ROOT'];
        $this->SCRIPT_FILENAME  = $_SERVER['SCRIPT_FILENAME'];
        $this->SCRIPT_NAME      = $_SERVER['SCRIPT_NAME'];
        $this->QUERY_STRING     = $_SERVER['QUERY_STRING'];
        $this->HTTP_HOST        = $_SERVER['HTTP_HOST'];
        $this->REQUEST_URI      = $_SERVER['REQUEST_URI'];
        if (empty($this->REQUEST_URI)) {
            $REQUEST_URI = "{$this->SCRIPT_NAME}?{$this->QUERY_STRING}";
        }
        $this->THIS_PAGE_QUERY  = $this->REQUEST_URI;
        $this->THIS_PAGE        = preg_replace('/(;|\?|:).+$/', '', $this->THIS_PAGE_QUERY);
        $this->HTTPS            = $_SERVER['HTTPS'];
        $this->SCRIPT_URI       = empty($this->HTTPS)? 'http://'.$this->HTTP_HOST.$this->REQUEST_URI : 'https://'.$this->HTTP_HOST.$this->REQUEST_URI;
        $this->HTTPS_URI        = 'https://'.$this->HTTP_HOST.$this->REQUEST_URI;
        $this->HTTP_USER_AGENT  = $_SERVER['HTTP_USER_AGENT'];
        $this->REFERER          = $_SERVER['HTTP_REFERER'];
        $this->PHP_SELF         = $_SERVER['PHP_SELF'];
        
        

        
        
        
        
        $this->PAGE['START_TIME']   = microtime(true);
        $this->PAGE['DATETIME']     = date('YmdHis');
        
        if (!$this->SITECONFIG) {
            echo "ERROR --> Class :: Lib_PageHelper --> No SITECONFIG Passed in"; exit();
        }
        
        //------- page helper file ---------
        if (!session_id()) {
            ini_set('url_rewriter.tags', '');
            ini_set('session.use_trans_sid', false);
            session_start();
        }
        
       
        

        $this->SITE_DIR         = $this->SITECONFIG['sitedir'];
        $this->PAGE_DIR         = $this->SITECONFIG['pagedir'];
        
        $this->SITE_ROOT        = $this->ROOT.$this->SITE_DIR;
        $this->PAGEDEFAULT      = 'index';
        
        if (empty($this->SITECONFIG['templatedir']))    $this->SITECONFIG['templatedir'] = $this->SITE_DIR . '/common';
        if (empty($this->SITECONFIG['cssdir']))         $this->SITECONFIG['cssdir'] = $this->SITE_DIR . '/common';
        
    }
    
    
    public function AdminRunning()
    {
        return ArrayValue(ArrayValue($_SESSION, 'SITE_ADMIN'), 'AdminLoginOK') == 'ok';
    }

    public function addSwap($key, $value)
    {
        $this->PAGE_SWAP_VARIABLES[$key] = $value;
    }
    
    public function Page_Helper_Add_Msg($field, $MSG, $template)
    {
        if ($MSG != '') {
            $this->PAGE[$field] .= str_replace('@@', $MSG, $template);
        }
    }

    public function addError($MSG, $template='<p>@@</p>')
    {
        $this->Page_Helper_Add_Msg('ERROR', $MSG, $template);
    }

    public function addMessage($MSG, $template='<p>@@</p>')
    {
        $this->Page_Helper_Add_Msg('MESSAGE', $MSG, $template);
    }

    public function addFlash($MSG, $template='<p>@@</p>')
    {
        $this->Page_Helper_Add_Msg('FLASH', $MSG, $template);
    }

    public function addScript($MSG)
    {
        $this->Page_Helper_Add_Msg('SCRIPT', $MSG, "@@\n\n");
    }

    public function addScriptOnload($MSG)
    {
        $this->Page_Helper_Add_Msg('SCRIPT_ONLOAD', $MSG, "@@\n\n");
    }

    public function addScriptOnReady($MSG)
    {
        $this->Page_Helper_Add_Msg('SCRIPT_ONREADY', $MSG, "@@\n\n");
    }

    public function addScriptInclude($SCRIPTS)
    {
        if (!empty($SCRIPTS)) {
            $scripts_list   = explode(',', $SCRIPTS);
            foreach ($scripts_list as $script) {
                $script = trim($script);
                if ($script) {
                    $this->PAGE['SCRIPTINC'] .= "<script type=\"text/javascript\" src=\"$script\"></script>\n";
                }
            }
        }
    }

    public function addStyle($MSG)
    {
        $this->Page_Helper_Add_Msg('STYLE', $MSG, "@@\n\n");
    }

    public function addStyleSheet($style_sheets)
    {
        if (!empty($style_sheets)) {
            $sheets_array = explode(',', $style_sheets);
            foreach ($sheets_array as $sheet) {
                $sheet = trim($sheet);
                if ($sheet) {
                    $this->PAGE['STYLE_SHEETS'] .= "@import \"$sheet\";\n";
                }
            }
        }
    }
    
    public function IsBot($user_agent = '')
    {
        static $bot_array;

        if (isset($_SESSION['IS_BOT'])) {
            return $_SESSION['IS_BOT'];
        }

        if (empty($user_agent)) {
            $user_agent = Server('HTTP_USER_AGENT');
            $set_session = true;
        } else {
            $set_session = false;
        }

        if (empty($bot_array)) {

            $user_agent_data = file("{$this->LIB}/{$this->User_Agent_File}");
            
            foreach ($user_agent_data as $line) {
                $line = trim($line);
                if ($line) {
                    list($agent_text, $title) = explode('|', $line);
                    if (strpos($title, ' BOT') !== false) {
                        $bot_array[] = $agent_text;
                    }
                }
            }
        }
        $RESULT = ArrayItemsWithinStr($bot_array, $user_agent);
        if ($set_session) {
            $_SESSION['IS_BOT'] = $RESULT;
        }
        return $RESULT;
    }

    public function GetQuery($name)
    {
        return (isset($this->QUERYVAR[$name]))? $this->QUERYVAR[$name] : '';
    }

    public function page_OutputGroup()  // group process (js/css)
    {
        $list       = strFrom($this->REQUEST_URI, 'GROUP/');
        $list       = strTo($list, '?');
        $update     = strFrom($this->REQUEST_URI, '?');
        $files      = explode(';', $list);
        $extension  = '';
        
        echo "<br />GROUP PROCESSING";
        echo "<br />ROOT ---> " . $this->ROOT;
        
        
        // check extensions -- must be all alike and js or css        
        foreach ($files as $file) {
            $ext = strFromLast($file, '.');
            if ($ext == 'js' || $ext =='css') {
                if ($extension == '') {
                    $extension = $ext;
                } elseif ($extension != $ext) {
                    echo 'Extension Mismatch';
                    exit;
                }
            } else {
                echo 'Extension Error';
                exit;
            }
        }
        if (!$extension) {
            echo 'Invalid File Group';
            exit;
        }

        $cache_path         = $this->ROOT . $this->SITECONFIG['cachedir']; // Cache path, this is where the .gz files will be stored
        $expires_offset     = 3600 * 24 * 3650; // Cache for 10 years in browser cache
        $content            = '';
        $encodings          = array();
        $supports_gzip      = false;
        $enc                = '';
        $cache_key          = '';

        // Headers
        if ($extension == 'js') {
            header('Content-type: text/javascript');
        } else {
            header('Content-type: text/css');
        }
        header('Vary: Accept-Encoding');  // Handle proxies
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires_offset) . ' GMT');
        
        // Setup cache info
        $cache_key      = md5($list);
        $cache_file     = $cache_path . '/' . $cache_key . '_' . $extension . '.gz';
        
        // Check if it supports gzip
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            $encodings = explode(',', strtolower(preg_replace("/\s+/", "", $_SERVER['HTTP_ACCEPT_ENCODING'])));
        }
        
        if ((in_array('gzip', $encodings) || in_array('x-gzip', $encodings) ||
            isset($_SERVER['---------------'])) && function_exists('ob_gzhandler') && !ini_get('zlib.output_compression')) {
            $enc = in_array('x-gzip', $encodings) ? "x-gzip" : "gzip";
            $supports_gzip = true;
        }
        
        // Use cached file disk cache
        $get_cache = true;
        if (!empty($update) && file_exists($cache_file)) {
            $cache_date = date('YmdHi', filemtime($cache_file));
            foreach ($files as $file) {
                $file_date = date('YmdHi', filemtime($this->ROOT . '/' . $file));
                if ($file_date > $cache_date) {
                    $get_cache = false;
                    break;
                }
            }
        }

        if ($get_cache && $supports_gzip && file_exists($cache_file)) {
            //$cache_date = date('YmdHi', filemtime($cache_file));
            header("Content-Encoding: " . $enc);
            echo file_get_contents($cache_file);
            exit;
        }

        // Add content
        foreach ($files as $file) {
            $file_content = file_get_contents($this->ROOT . '/' . $file);
            if (empty($file_content)) {
                $file_content = '// -------> ERROR: File Contents Not Found';
            }
            $content .= "/*
    =========================================
      FILE: $file
    =========================================
    */\n" . $file_content . "\n\n";
        }

        // Generate GZIP'd content
        if ($supports_gzip) {
            header('Content-Encoding: ' . $enc);
            $cache_data = gzencode($content, 9, FORCE_GZIP);
            write_file($cache_file, $cache_data);  // Write gz file
            echo $cache_data; // Stream to client

        } else {
            echo $content; // Stream uncompressed content
        }
        exit;
    }

    public function GetPageName($pagename = '')
    {
        if (strpos($this->REQUEST_URI, 'GROUP/') !== false) {
            $this->page_OutputGroup();  // does not return;
        }

        //---- process psudo query string ----

        $querystr = preg_replace('/(^.+(\?|:|;|$))/U', '', $this->REQUEST_URI);

        if (!empty($querystr)) {

            $qlist = explode('&', preg_replace('/;|\?/', '&', $querystr));

            foreach ($qlist as $var) {
                $varinfo                = explode('=',$var);
                $key                    = $varinfo[0];
                $value                  = (count($varinfo)>1)? $varinfo[1] : 1;
                $value                  = urldecode($value);
                $this->QUERYVAR[$key]   = $value;
                $_GET[$key]             = $value;
            }
        }

        if (empty($pagename)) {
            //set pagename up to query itmes
            $pagename = preg_replace('/(;|\?|:).+$/', '', $this->QUERY_STRING);
        }

        // remove beginning slashes
        $pagename = preg_replace('/^\/+/', '', $pagename);

        if (substr($pagename, 0, 5) == 'AJAX/') {
            $this->AJAX = 1;
            $pagename = substr($pagename, 5);
        }

        if (substr($pagename, 0, 12) == 'PAGECONTENT/') {
            $this->PAGE['PAGE_CONTENT_ONLY'] = 1;
            $pagename = substr($pagename, 12);
        }

        if (substr($pagename, 0, 2)=='P-') {
            $print = 1;
            $pagename=substr($pagename, 2);
        } else {
            $print = 0;
        }

        $pagename = RemoveExtension($pagename);
        $pagename = ($pagename == '') ? $this->PAGEDEFAULT : $pagename;
        
        $this->PAGE['dirpage'] = dirname($pagename);
        
        // if ending slash add default
        if (substr($pagename, -1) == '/') {
            $pagename .= $this->PAGEDEFAULT;
        }

        $this->PAGE['original_name'] = $pagename;
        if (!empty($this->PAGE_ALIAS)) {
            if (array_key_exists($pagename, $this->PAGE_ALIAS)) {
                $pagename = $this->PAGE_ALIAS[$pagename];
            }
        }

        $pagename_with_query    = ($querystr)? "$pagename?$querystr" : $pagename;
        $basename               = str_replace('//', '/', $this->HTTP_HOST . dirname($this->PHP_SELF) . '/');
        $basename               = empty($this->HTTPS)? 'http://'.$basename : 'https://'. $basename;

        $this->PAGE['pagename']         = $pagename;
        $this->PAGE['basename']         = $basename;
        $this->PAGE['print']            = $print;
        $this->PAGE['query']            = $querystr;
        $this->PAGE['printversionlink'] = "{$this->SITECONFIG['pagedir']}/P-$pagename_with_query";
        $this->PAGE['ajaxlink']         = "{$this->SITECONFIG['pagedir']}/AJAX/$pagename_with_query";
        $this->PAGE['pagelink']         = "{$this->SITECONFIG['pagedir']}/$pagename";
        $this->PAGE['pagelinkquery']    = "{$this->SITECONFIG['pagedir']}/$pagename_with_query";
        $this->PAGE['url']              = urlencode($this->HTTP_HOST)."{$this->SITECONFIG['pagedir']}/$pagename";
        $this->PAGE['id']               = str_replace('/','_',$pagename);
        $this->PAGE['menuid']           = str_replace('@', $this->PAGE['id'], $this->PAGE['MENU_ID_TEMPLATE']);
    }

    public function Page_SetPageName($pagename)
    {
        $pagename_with_query            = ($this->PAGE['query'])? $pagename . '?' . $this->PAGE['query'] : $pagename;
        $this->PAGE['pagename']         = $pagename;
        $this->PAGE['printversionlink'] = "{$this->SITECONFIG['pagedir']}/P-$pagename_with_query";
        $this->PAGE['ajaxlink']         = "{$this->SITECONFIG['pagedir']}/AJAX/$pagename_with_query";
        $this->PAGE['pagelink']         = "{$this->SITECONFIG['pagedir']}/$pagename";
        $this->PAGE['pagelinkquery']    = "{$this->SITECONFIG['pagedir']}/$pagename_with_query";
        $this->PAGE['url']              = urlencode($this->HTTP_HOST)."{$this->SITECONFIG['pagedir']}/$pagename";
        $this->PAGE['id']               = str_replace('/','_',$pagename);
        $this->PAGE['menuid']           = str_replace('@', $this->PAGE['id'], $this->PAGE['MENU_ID_TEMPLATE']);
    }
    
    public function GetPageFileNames($redirect='/')
    {
        $this->PAGE['titlefilename']   = $this->ROOT.$this->SITECONFIG['contentdir']."/{$this->PAGE['pagename']}{$this->SITECONFIG['titlestr']}";
        $this->PAGE['contentfilename'] = $this->ROOT.$this->SITECONFIG['contentdir']."/{$this->PAGE['pagename']}{$this->SITECONFIG['contentstr']}";
        
        if (!file_exists($this->PAGE['titlefilename']) && !$this->AJAX) {
            
            echo "REDIRECTING TO DEFAULT PAGE"; exit();
            
            // ----- add to missing log -----
            $missingfile    = $this->ROOT.$this->SITECONFIG['logdir'].'/missingpage-'.date('Y-m').'.dat';
            $date           = date('Y-m-d:H:i:s');
            $ADDR           = Server('REMOTE_ADDR');
            $HTTP_REFERER   = Server('HTTP_REFERER');
            $USER_AGENT     = Server('HTTP_USER_AGENT');
            $line           = "$date|$this->QUERY_STRING|$HTTP_REFERER|$ADDR|$USER_AGENT\n";
            append_file($missingfile, $line);
            // ----- redirect -----
            header("Location: $redirect");
            exit;
        }

        $t                          = filemtime($this->PAGE['titlefilename']);
        $c                          = filemtime($this->PAGE['contentfilename']);
        $this->PAGE['updated']      = date("m\/d\/Y", max($t,$c));
        $this->PAGE['modified']     = gmdate('D, d M Y H:i:s', max($t,$c)) . ' GMT';
    }
    
    public function GetTitleVariables() 
    {
        $PAGE_TITLE_CONTENT         = file_get_contents($this->PAGE['titlefilename']);
        $this->PAGE['name']         = TextBetween('<name>','</name>', $PAGE_TITLE_CONTENT);
        $this->PAGE['title']        = TextBetween('<title>','</title>', $PAGE_TITLE_CONTENT);
        $this->PAGE['description']  = TextBetween('<description>','</description>', $PAGE_TITLE_CONTENT);
        $this->PAGE['keywords']     = TextBetween('<keywords>','</keywords>', $PAGE_TITLE_CONTENT);
        $this->PAGE['banner']       = TextBetween('<banner>','</banner>', $PAGE_TITLE_CONTENT);
        $style                      = TextBetween('<style>','</style>', $PAGE_TITLE_CONTENT);
        $style_sheets               = TextBetween('<stylesheet>','</stylesheet>', $PAGE_TITLE_CONTENT);
        $script                     = TextBetween('<script>','</script>', $PAGE_TITLE_CONTENT);
        $include_script             = TextBetween('<scriptinclude>','</scriptinclude>', $PAGE_TITLE_CONTENT);
        $php                        = TextBetween('<php>','</php>', $PAGE_TITLE_CONTENT);
        
        if ($php) {
            eval($php);
        }
        
        AddStyle($style);
        AddStyleSheet($style_sheets);
        AddScript($script);
        AddScriptInclude($include_script);
        
        $body                   = TextBetween('<body>','</body>', $PAGE_TITLE_CONTENT);
        $body                   = ($body) ? ' ' . $body : $body;
        $this->PAGE['body']     = $body;
        
        $this->PAGE['template'] = TextBetween('<template>','</template>', $PAGE_TITLE_CONTENT);
        
        $robots                 = TextBetween('<robots>','</robots>', $PAGE_TITLE_CONTENT);
        $robots                 = (!empty($robots)) ? "<meta name=\"robots\" content=\"$robots\" />" : '';
        $this->PAGE['robots']   = $robots;
    }
    
    public function BlockedIPCheck()
    {
        if (AdminRunning()) {
            return;
        }

        $SITE_TRACKING = str_replace('/', '_', "SITE_TRACKING{$this->SITE_DIR}");

        if (!isset($_SESSION[$SITE_TRACKING])) {
            $_SESSION[$SITE_TRACKING] = array();
        }

        $pageok = !ArrayItemsWithinStr($this->BLOCK_LIST_TERMS, $this->PAGE['pagename']);
        $tracking_array = $_SESSION[$SITE_TRACKING];

        if ($pageok and ArrayValue($tracking_array, 'BLOCK_CHECK') == 'ok') {
            return;
        }

        $blockfile = $this->ROOT . $this->SITECONFIG['logdir'] . '/block-' . date('Y-m-d') . '.dat';

        $block = false;
        $ADDR  = Server('REMOTE_ADDR');

        if (!$pageok or ArrayValue($tracking_array, 'BLOCK_CHECK') == 'block') {
            $block = true;
        } else {
            $blocklist = file_exists($blockfile)? file_get_contents($blockfile) : '';
            if (strpos($blocklist, $ADDR) !== false) {
               $block = true;
            }
        }

        if ($block) {
            $line = "$ADDR|{$_SERVER['QUERY_STRING']}|" . date('H:i:s') . "\n";
            append_file($blockfile, $line);
            $_SESSION[$SITE_TRACKING]['BLOCK_CHECK'] = 'block';
            header("Location: $ADDR/{$this->PAGE['pagename']}");
        } else {
            $_SESSION[$SITE_TRACKING]['BLOCK_CHECK'] = 'ok';
        }
    }

    public function WriteTrackingLogDb()
    {
        $titlefilename  = $this->ROOT . $this->SITECONFIG['contentdir'] . "/{$this->PAGE['pagename']}{$this->SITECONFIG['titlestr']}";
        $SITE_TRACKING  = str_replace('/', '_', "SITE_TRACKING{$this->SITE_DIR}");
        
        if (!AdminRunning() and file_exists($titlefilename) and empty($_SESSION[$SITE_TRACKING]['PAGE'][$this->PAGE['pagename']])) {
            include "$this->LIB/class.SiteLogs.php";
            $SL = new SiteLogs;
            $SL->AddToSiteLog();
        }
    }

    public function WriteTrackingLog()
    {
        if (AdminRunning()) {
            return;
        }

        $SITE_TRACKING = str_replace('/', '_', "SITE_TRACKING{$this->SITE_DIR}");

        if (!isset($_SESSION[$SITE_TRACKING])) {
            $_SESSION[$SITE_TRACKING] = array();
        }

        $pagename = $this->PAGE['pagename'];

        if (empty($_SESSION[$SITE_TRACKING]['PAGE'][$pagename])) {
            $_SESSION[$SITE_TRACKING]['PAGE'][$pagename] = 1;
            if (empty($_SESSION[$SITE_TRACKING]['START_TIME'])) {
                $_SESSION[$SITE_TRACKING]['START_TIME'] = time();
            } else {
                $elapsedtime = time() - $_SESSION[$SITE_TRACKING]['START_TIME'];
            }
            
            $tid        = $_SESSION[$SITE_TRACKING]['START_TIME'] . substr(session_id(),-4);
            $logfile    = $this->ROOT . $this->SITECONFIG['logdir'] . '/log-' . date('Y-m').'.dat';
            
            if (empty($_SESSION[$SITE_TRACKING]['REFERRER'])) {
                $_SESSION[$SITE_TRACKING]['REFERRER'] = 1;
                $ADDR           = Server('REMOTE_ADDR');
                $HTTP_REFERER   = Server('HTTP_REFERER');
                $USER_AGENT     = Server('HTTP_USER_AGENT');
                $line           = "$tid|REF|{$HTTP_REFERER}[$ADDR][$USER_AGENT]\n$tid|0|{$this->PAGE['pagename']}\n";
            } else {
                $line           = "$tid|$elapsedtime|$pagename\n";
            }
            append_file($logfile, $line);
        }
    }
    
    public function SwapStdMarkUp()
    {
        $ERROR   = (empty($this->PAGE['ERROR']))?   '' : "<div id=\"error\">{$this->PAGE['ERROR']}</div>";
        $MESSAGE = (empty($this->PAGE['MESSAGE']))? '' : "<div id=\"message\">{$this->PAGE['MESSAGE']}</div>";
        $FLASH   = (empty($this->PAGE['FLASH']))?   '' : "<div id=\"flash\">{$this->PAGE['FLASH']}</div>";

        if ($this->PAGE['PAGE_CONTENT_ONLY']) {
            $this->PAGE_STREAM = '@@ERROR@@@@MESSAGE@@@@FLASH@@@@CONTENT@@';
        }

        if ($this->PAGE['SCRIPT_ONREADY']) {
            $this->PAGE['SCRIPT'] .= str_replace('@', rtrim($this->PAGE['SCRIPT_ONREADY']), $this->PAGE['SCRIPT_ONREADY_TEMPLATE']);
        }
        if ($this->PAGE['SCRIPT_ONLOAD']) {
            $this->PAGE['SCRIPT'] .= str_replace('@', rtrim($this->PAGE['SCRIPT_ONLOAD']), $this->PAGE['SCRIPT_ONLOAD_TEMPLATE']);
        }
        $this->PAGE['SCRIPT'] = JavaScriptString($this->PAGE['SCRIPT']);
        $this->PAGE['STYLE']  = StyleString($this->PAGE['STYLE_SHEETS'] . $this->PAGE['STYLE']);

        $new_end_body = isset($this->TESTVAR)? "$TESTVAR\n</body>" : '</body>';
        
        /*
        if (function_exists('customerrortext')) {
            AddSwap('@@PHPERROR@@', CustomErrorText());
        }
        */
        
        # CLASS VERSION
        
        if (isset($OBJ_ERROR)) {
            if (method_exists('Lib_CustomError', 'CustomErrorText')) {
                global $OBJ_ERROR;
                $this->AddSwap('@@PHPERROR@@', $OBJ_ERROR->CustomErrorText());
            }
        } else {
            $this->AddSwap('@@PHPERROR@@', 'NO ERROR FUNCTION DEFINED');
        }

        $swap_array = array(
            '@@TITLE@@'                 => $this->PAGE['title'],
            '@@DESCRIPTION@@'           => $this->PAGE['description'],
            '@@KEYWORDS@@'              => $this->PAGE['keywords'],
            '<!-- @@STYLE@@ -->'        => $this->PAGE['STYLE'],
            '<!-- @@SCRIPT@@ -->'       => $this->PAGE['SCRIPT'],
            '<!-- @@SCRIPTINCLUDE@@ -->'=> $this->PAGE['SCRIPTINC'],
            ' title="@@BODY@@"'         => $this->PAGE['body'],
            '<!-- @@ROBOTS@@ -->'       => $this->PAGE['robots'],
            '@@CONTENT@@'               => $this->PAGE_CONTENT,
            '@@ERROR@@'                 => $ERROR,
            '@@MESSAGE@@'               => $MESSAGE,
            '@@FLASH@@'                 => $FLASH,
            '@@BASENAME@@'              => $this->PAGE['basename'],
            '@@UPDATED@@'               => $this->PAGE['updated'],
            '@@PRINTVERSIONLINK@@'      => $this->PAGE['printversionlink'],
            '@@DIR@@'                   => $this->PAGE['dirpage'],
            '@@PAGEURL@@'               => $this->PAGE['url'],
            '@@PAGEID@@'                => $this->PAGE['id'],
            '--PAGEID--'                => $this->PAGE['id'],
            '@@COMPANYNAME@@'           => $this->SITECONFIG['companyname'],
            '@@BANNER@@'                => $this->PAGE['banner'],
            '</body>'                   => $new_end_body
        );
        
        $last_swap = array (
            $this->PAGE['menuid']       => $this->PAGE['MENU_SELECT_TEMPLATE'],
            '@@SITEDIR@@'               => $this->SITECONFIG['sitedir'],
            '@@PAGEDIR@@'               => $this->SITECONFIG['pagedir'],
            '@@PAGELINK@@'              => $this->PAGE['pagelink'],
            '@@AJAXLINK@@'              => $this->PAGE['ajaxlink'],
            '@@PAGELINKQUERY@@'         => $this->PAGE['pagelinkquery'],
            '@@PAGENAME@@'              => $this->PAGE['pagename'],
            '@@DATETIME@@'              => $this->PAGE['DATETIME'],
            '@@TIME@@'                  => number_format(microtime(true) - $this->PAGE['START_TIME'], 3)
        );

        $swap_array = array_merge($swap_array, $this->PAGE_SWAP_VARIABLES, $last_swap);

        $this->PAGE_STREAM = astr_replace($swap_array, $this->PAGE_STREAM);
    }



} // end class