<?php

class Lib_Forms
{
    // ------------- FORM HELPER UNIT ----------------
    // ------------- Michael V. Petrovich ------------------
    /*
        2009-12-28 : Added: 'has_too_few_characters'
        2010-06-25 : Added: Form_SetFormPrefix, Form_GetFormPrefix, Form_GetStateCodeFromName
        2010-06-30 : Added: Form_PostValue
        2010-07-12 : Autocomplete - set so that if value shown is empty, result is empty
        2010-07-16 : Added Website, Form_CheckDomainName
        2010-11-29 : Added 'dollarz' kind
    */

    public $Error                = '';
    public $Output_Table         = '';
    public $Encrypt_Query_Key    = '';

    // ---- line values ----
    public $Title_Template       = '';
    public $Title_Template_Std   = '';
    public $Info_Template        = '';
    public $Info_Template_Std    = '';

    public $Form_Javascript      = '';
    public $Javascript_Functions = array();
    public $Alt_Value            = '';
    public $Select_Start         = '';
    public $Process_Want_Table   = true;
    public $Process_Result_Array = array();


    public $Form_Prefix          = 'FORM_'; // -------- !! this is prepended to each input tagname and id

    public $Form_Show_Posted     = true;   // -------- flag to display posted date-time at the end of output table, used mainly for email
    public $Form_Block_Referrer  = true;   // -------- block referrer requires form to be posted from same domain
    public $Form_Show_Missing    = false;  // -------- show missing in output table
    public $Form_Strip_Quotes    = false;  // -------- this removes all the quotes from inputs
    public $Form_Time_Shift      = 0; // set for server time differences hours -- used to return the posted date-time shifted from the server time
    public $Form_Creditcard_Length = 16;

    public $Html_Table_Options  = '';
    public $Html_Th_Options     = '';
    public $Html_Td_Options     = '';

    public $Form_Var = array(
        'line_delimit'       => '', // '|' . chr(27);
        'id_prefix'          => '',
        'required_text'      => '<span class="formrequired">&bull;</span>',      // -------- When flagged as required, prepends to title
        'title_template'     => "<div class=\"formtitle\">@:</div>\n",    // -------- Template for the title
        'info_template'      => "<div class=\"forminfo\">@</div>\n\n",    // -------- templete for the input field
        'error_template'     => "<div class=\"error\">@</div>\n",       // -------- template for the input field with error
        'posted_cell_style'  => 'white-space:nowrap; text-align:center; font-size:0.76em;',  // ------- how to display the posted date-time

        'default_country'    => 'US',               // -------- the 2-digit default country

        'start_select'       => '-- select --',     // -------- initial default for a SELECT input

        'start_select_value' => 'START_SELECT_VALUE',  // no need change this

        'new_select_text'    => '-- new --',        // -------- used with a combination SELECT and TEXT input
        'new_select_text_value'  => 'NEW_SELECT_TEXT_VALUE',  // no need change this


        'new'                => 'New',
        'submit_click_text'  => 'Processing. . .',  // -------- submit button onclick  changes to this text

        // text used in output of errors or additional input text

        'referrer_error'     => 'Invalid Referrer - Blocked for Security!',
        'illegal_characters' => 'has illegal characters',
        'insecure_password'  => 'is insecure: include upper and lower case letters, numbers, and symbols (!@#$%^*()-_+={}[]|/:;,.?~|)',
        'has_too_few_characters' => 'has too few characters (@ needed)',
        'has_too_many_characters' => 'has too many characters (@ maximum)',
        'is_missing'         => 'is missing',
        'year'               => 'Year',
        'month'              => 'Month',
        'day'                => 'Day',
        'hour'               => 'Hour',
        'minute'             => 'Minute',
        'international_entry' => 'International requires Non-US entry',
        'us_state'           => 'U.S. State',
        'state'              => 'State',
        'canada_province'    => 'Province/Territory',
        'state_province'     => 'State/Province',
        'non_us'             => 'Non-U.S.',

        'has_incorrect_number_count' => 'has incorrect number count', // for credit card
        'is_not_a_valid_number'      => 'is not a valid number',      // for credit card
        'is_not_valid'       =>  'is not valid',  // for email
        'is_missing_numbers'         => 'is missing numbers',  // for phone
        'show_password'              => 'Show Password',
        'phone_delimiter'    => '-',                // -------- used to process phone numbers (xxx-xxx-xxxx)
        'form_date_code'     => 'l, M j, Y, g:ia',  // -------- this is the format for the posted date-time
        'accept_charset'     => 'accept-charset="utf-8"'
    );

    //masks for edit
    public $Mask_Integer    = '^[0-9]+$';                   // -------- only integer values
    public $Mask_Name       = '^[a-zA-Z0-9 \'\-]+$';        // -------- used for names
    public $Mask_UserName   = '^[a-zA-Z0-9]+$';             // -------- used for usernames (no spaces)
    public $Mask_Password   = '^[a-zA-Z0-9:\.\-\!\@\#\$\%\^\&\*_]+$';   // -------- used for passwords
    public $Mask_Email      = '^([0-9a-z]+)([0-9a-z\.-]+)@([0-9a-z\._-]+)\.([a-z]{2,6})';  // -------- email mask (better to use email type)
    public $Mask_Real       = '^[0-9.\-]+$';                // -------- real numbers
    public $Mask_RealC      = '^[0-9\,.\-]+$';              // -------- real numbers, with commas
    public $Mask_ZIP        = '^[0-9\-]+$';                 // -------- ZIP codes
    public $Mask_4int       = '^[0-9]{4}$';                 // -------- 4 digit integer
    public $Mask_2int       = '^[0-9]{2}$';                 // -------- 2 digit integer
    public $Mask_Char       = '^[a-zA-Z]+$';                // -------- letters-only
    public $Mask_2chr       = '^[a-zA-Z]{2}$';              // -------- 2 letters only
    public $Mask_General_Line = '^[a-zA-Z0-9_ \!-\?[:punct:]]+$';              // -------- general text line
    public $Mask_General    = "^[a-zA-Z0-9_ \r\n\t\!-\?[:punct:]]+$";    // -------- general text + CR and tabs
    public $Mask_Words      = '^[[:alnum:][:space:][:punct:]]+$';           // -------- words only
    public $Mask_Dir        = '^[a-zA-Z0-9\/_\.\-]+$';      // -------- directory listings


    public $Form_Months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

    public $Country_Codes = '';  // loaded with Form_LoadCountryCodes() in file: country_codes.dat

    public $Form_State_Char_Codes = array('INT','AL','AK','AZ','AR','CA','CO','CT','DE','DC','FL','GA','HI','IA','ID','IL','IN','KS','KY','LA','MA','MD','ME','MN','MI','MO','MS','MT','NC','ND','NE','NH','NJ','NM','NV','NY','OK','OR','OH','PA','RI','SC','SD','TN','TX','UT','VA','VT','WA','WI','WV','WY');

    public $Form_State_Codes = array(
      'International','AL - Alabama','AK - Alaska','AZ - Arizona','AR - Arkansas',
      'CA - California','CO - Colorado','CT - Connecticut','DE - Delaware','DC - Washington D.C.',
      'FL - Florida','GA - Georgia','HI - Hawaii','IA - Iowa','ID - Idaho','IL - Illinois','IN - Indiana',
      'KS - Kansas','KY - Kentucky','LA - Louisiana',
      'MA - Massachusetts','MD - Maryland','ME - Maine','MN - Minnesota','MI - Michigan','MO - Missouri',
      'MS - Mississippi','MT - Montana','NC - North Carolina','ND - North Dakota','NE - Nebraska','NH - New Hampshire',
      'NJ - New Jersey','NM - New Mexico','NV - Nevada','NY - New York','OH - Ohio','OK - Oklahoma','OR - Oregon',
      'PA - Pennsylvania','RI - Rhode Island','SC - South Carolina','SD - South Dakota','TN - Tennessee','TX - Texas',
      'UT - Utah','VA - Virgina','VT - Vermont','WA - Washington','WI - Wisconsin','WV - West Virginia','WY - Wyoming'
    );

    public $Form_Canada_Provinces = array(
        'Alberta', 'British Columbia', 'Manitoba', 'New Brunswick',
        'Newfoundland and Labrador', 'Northwest Territories', 'Nova Scotia',
        'Nunavut', 'Ontario', 'Prince Edward Island', 'Quebec', 'Saskatchewan', 'Yukon'
    );


    // ============================================== FUNCTIONS ==============================================

    public function __construct()
    {
        global $ENCRYPT_QUERY_KEY;
        $this->Form_Var['line_delimit'] = '|' . chr(27);
        $this->Encrypt_Query_Key = $ENCRYPT_QUERY_KEY;
    }

    public function SetFormPrefix($prefix)
    {
        $this->Form_Prefix = $prefix;
    }

    public function GetFormPrefix()
    {
        return $this->Form_Prefix;
    }

    public function SetShowPosted($value)
    {
        $this->Form_Show_Posted = $value;
    }

    public function GetStateCodeFromName($name)
    {
        foreach ($this->Form_State_Codes as $row) {
            if (substr($row, 5) == $name ) {
                return substr($row, 0, 2);
            }
        }
        return $name;
    }

    public function CheckDomainName($domain)
    {
        $domain = strTo($domain, '/');
        if (strlen($domain > 255)) return false;
        if (!preg_match('/([0-9a-z\.-]+)\.([a-z]{2,6})$/', $domain)) return false;
        $domain_labels = explode('.', $domain);
        foreach ($domain_labels as $label) {
            if (empty($label) or (strlen($label) > 63)) return false;
        }
        return true;
    }


    public function GetCountryNameOrCode($cc)
    {
        $this->LoadCountryCodes();

        if (empty($cc)) return '';

        if (!empty($this->Country_Codes[$cc])) {
            $RESULT = $this->Country_Codes[$cc];
        } else {
            $RESULT = array_search($cc, $this->Country_Codes);
            if (!$RESULT) $RESULT = '';
        }
        return $RESULT;
    }


    public function HaveSubmit($submitname)
    {
        return Post($submitname) == $this->Form_Var['submit_click_text'];
    }

    public function WriteFormError()
    {
        echo $this->WriteErrorText($this->Error);
    }

    public function WriteFormErrorText()
    {
        return $this->WriteErrorText($this->Error);
    }

    public function WriteErrorText($error)
    {
        $RESULT = '';
        if(!empty($error)) {
            list($first, $last) = explode('@', $this->Form_Var['error_template']);
            return $first . $error . $last;
        }
        return $RESULT;
    }

    public function WriteError($error)
    {
        echo $this->WriteErrorText($error);
    }


    public function GetPostItem($str)
    {
        $value = Post($this->Form_Prefix.$str);
        if ($value == intOnly($value)) {
            return $value;
        } elseif (!empty($value)) {
            $value = htmlspecialchars_decode($this->StripQuotes($value));
            return trim(htmlspecialchars($value, ENT_COMPAT, 'UTF-8'));
        } else {
            return '';
        }
    }

    public function WriteFormStart($action, $method, $name='')
    {
        if ($name) {
            $name = " name=\"$name\" id=\"$name\"";
        }
        echo "\n<form action=\"$action\" method=\"$method\"$name>\n";
    }

    public function WriteFormEnd()
    {
        echo "</form>\n";
    }


    public function KeyValuesToVar($FormArray)
    {
        foreach($FormArray as $key => $value) $GLOBALS[$key] = $value;
        return '';
    }

    public function PostVars($titlearray, $valuearray)
    {
        foreach ($titlearray as $key => $value) {
            $_POST[$this->Form_Prefix . $key] = empty($valuearray[$key])? '' : $valuearray[$key];
        }
    }

    public function PostArray($array)
    {
        if ($array) {
            foreach ($array as $key => $value) {
                $_POST[$this->Form_Prefix . $key] = $value;
            }
        }
    }

    public function PostValue($key, $value)
    {
        $_POST[$this->Form_Prefix . $key] = $value;
    }

    public function AjaxToPost($var)
    {
        if(Post($var)) {
            $var_pairs = explode('&', $_POST[$var]);

            foreach ($var_pairs as $field) {
                list($key, $value) = explode('=', $field);
                $key               = urldecode($key);
                $value             = urldecode($value);
                $_POST[$key]       = $value;
            }
        }
    }


    public function AssocArrayToList($array)
    {
        // returns kkk=vvv|kkk=vvv
        $RESULT = '';
        if ($array) {
            foreach ($array as $key=>$value) {
                $RESULT .= "|$key=$value";
            }
            $RESULT = substr($RESULT, 1);
        }
        return $RESULT;
    }

    public function ArrayToList($array)
    {
        // returns vvv|vvv
        $RESULT = '';
        if ($array) {
            $RESULT = implode('|', $array);
        }
        return $RESULT;
    }


    public function GetIdFromVar($var)
    {
        $RESULT = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $var);
        $RESULT = $this->Form_Prefix . $this->Form_Var['id_prefix'] . $RESULT;
        if (!preg_match('/^[a-zA-Z]/', $RESULT)) {
            $RESULT = 'id_' . $RESULT;
        }
        return $RESULT;
    }

    // =======================================================================
    // private functions -- not expected to call these outside form processing
    // =======================================================================


    public function LoadCountryCodes()
    {
        if (empty($this->Country_Codes)) {
            $path = dirname(__FILE__);
            $lines = file(RootPath("/lib/country_codes.dat"));
            foreach ($lines as $line) {
                list($co, $name) = explode("\t", trim($line));
                if ($co) {
                    $this->Country_Codes[$co] = $name;
                }
            }
        }
    }


    public function CheckCreditCardLength($num)
    {
        $len = strlen($num);
        $c1 = substr($num,0,1);
        $c2 = substr($num,0,2);
        $c3 = substr($num,0,3);
        $c4 = substr($num,0,4);
        $c5 = substr($num,0,5);
        $c6 = substr($num,0,6);
        if (($c2 == '34') or ($c2 == '37')) return $len == 15;  // American Express

        if (($c3 == '622') or (($c6 >= '622126') and ($c6 <= '622925'))) return (($len > 15) and ($len < 20)); //China UnionPay

        if (($c3 >= '300') and ($c3 <= '305')) return $len == 14; // Diners Club Carte Blanche

        if ($c2 == '36') return $len == 14; // Diners Club International

        if ($c2 == '55') return $len == 16; // Diners Club US & Canada (or visa)

        if (($c4 == '6011') or (($c5 >= '60112') and ($c5 <= '60114')) or ($c6 == '601174')
            or (($c6 >= '601177') and ($c6 <= '601179'))
            or (($c6 >= '601186') and ($c6 <= '601199'))
            or (($c6 >= '622126') and ($c6 <= '622925'))
            or (($c3 >= '644') and ($c3 <= '644'))
            or ($c2 == '65'))  return $len == 16; //  Discover Card

        if (($c4 >= '3528') and ($c4 <= '3589')) return $len == 16; //JCB

        if (($c4 == '5018') or ($c4 == '5020') or ($c4 == '5038') or ($c4 == '6304')
            or ($c4 == '6759') or ($c4 == '6761'))  return (($len > 11) and ($len < 20)); //Maestro (debit card)

        if (($c2 >= '51') and ($c2 <= '55')) return $len == 16; //    MasterCard

        if (($c4 == '6334') or ($c4 == '6767')) return (($len == 16) or ($len == 18) or ($len == 19)); //Solo (debit card)

        if (($c4 == '4903') or ($c4 == '4905') or ($c4 == '4911') or ($c4 == '4936')
            or ($c6 == '564182') or ($c6 == '633110') or ($c4 == '6333')
            or ($c4 == '6759')) return (($len == 16) or ($len == 18) or ($len == 19)); // Switch (debit card)

        if (($c6 == '417500') or ($c4 == '4917') or ($c4 == '4508') or ($c4 == '4844'))  return $len == 16;  // Visa Electron

        if ($c1 == '4') return $len == 16;  // Visa

        return true;  //since could not check
    }

    public function ValidCreditCardNumber($card_number)
    {
        $card_number = preg_replace('/[^0-9]/', '', $card_number);

        if ( empty($card_number) ) {
            return false;
        }

        $valid_format = preg_match("/^5[1-5][0-9]{14}|"  // mastercard
            . "^4[0-9]{12}([0-9]{3})?|" // visa
            . "^3[47][0-9]{13}|" // american express
            . "^3(0[0-5]|[68][0-9])[0-9]{11}|" //discover
            . "^6011[0-9]{12}|" //diners
            . "^(3[0-9]{4}|2131|1800)[0-9]{11}$/", $card_number); //JC

        if (!$valid_format) {
            return false;
        }

        // Is the number valid?
        $revNumber = strrev($card_number);
        $numSum = 0;

        for ($i = 0; $i < strlen($revNumber); $i++) {

            $current_number = substr($revNumber, $i, 1);

            // Double every second digit
            if ($i % 2 == 1) {
                $current_number *= 2;
            }

            // Add digits of 2-digit numbers together
            if ($current_number > 9) {
                $firstNum = $current_number % 10;
                $secondNum = ($current_number - $firstNum) / 10;
                $current_number = $firstNum + $secondNum;
            }

            $numSum += $current_number;
        }

        // If the total has no remainder it's OK
        $pass_check = ($numSum % 10 == 0);
        return $pass_check;
    }


    public function PhoneConvert($phone)
    {
        $RESULT = $phone;
        if ($RESULT == intOnly($RESULT)) {
            if (strlen($RESULT)>4) $RESULT = substr($RESULT,0,-4). $this->Form_Var['phone_delimiter'].substr($RESULT,-4);
            if (strlen($RESULT)>8) $RESULT = substr($RESULT,0,-8). $this->Form_Var['phone_delimiter'].substr($RESULT,-8);
            if (strlen($RESULT)>12) $RESULT = substr($RESULT,0,-12).$this->Form_Var['phone_delimiter'].substr($RESULT,-12);
        } else {

            $RESULT = preg_replace('/[\(\) ]/', $this->Form_Var['phone_delimiter'], $RESULT);
            $RESULT = preg_replace('/[^0-9\-]/', '', $RESULT);
            $RESULT = preg_replace('/\-+/', $this->Form_Var['phone_delimiter'], $RESULT);
            $RESULT = preg_replace('/(^\-+)|(\-+$)/', '', $RESULT);
        }
        return $RESULT;
    }


    public function StripQuotes($str)
    {
        if ($this->Form_Strip_Quotes) {
            return str_replace(array("'", '"', "`"), '', $str);
        } else {
            return $str;
        }
    }


    public function GetPostItemQuotes($str)
    {
        $value = Post($this->Form_Prefix.$str);
        if ($value == '0') return '0';
        return (empty($value))? '' : addslashes(trim(htmlspecialchars($value, ENT_COMPAT, 'UTF-8')));
    }


    public function GetPostHTML($str)
    {
        if (!empty($_POST[$this->Form_Prefix.$str])) {
            return trim($_POST[$this->Form_Prefix.$str]);
        }
        else return '';
    }


    public function OkRow($value)
    {
        return ($this->Form_Show_Missing or ($value != ''));
    }


    public function RequiredCheck($required, $process)
    {
        // determine if required
        $required = trim($required);

        if (empty($required) or ($required == 'N')) {
            return false;
        } elseif ($required == 'Y') {
            return true;
        } elseif (strpos($required, '=') !== false) {

        /// ------------- note may be better to preprocess FormArray - checkings states and creating a required variable

            if (!$process) return true;

            $checks = explode('::', $required);

            // checks are OR checks for all conditions, any true return true
            foreach ($checks as $rc) {
                $var = strTo($rc, '=');
                $var_value = strFrom($rc, '=');
                $not = (substr($var, -1) == '!');
                if ($not) {
                    $var = substr($var, 0, -1);
                }
                $var_value_check = $this->GetPostItem($var);

                if ($not) {
                    if ($var_value_check != $var_value) return true;
                } else {
                    if ($var_value_check == $var_value) return true;
                }
            }
        }
        return false;
    }

    public function AddError($error)
    {
        $this->Error .= "$error<br />\n";
    }

    public function AddTableRow($title, $value, $cols=2)
    {
        if ($cols == 2) {
            $this->Output_Table .= "<tr><th $this->Html_Th_Options>$title</th>\n<td $this->Html_Td_Options>$value</td></tr>\n";
        } else {
            if (empty($title)) {
                $this->Output_Table .= "<tr><td colspan=\"2\" $this->Html_Td_Options>$value</td></tr>\n";
            } else {
                $this->Output_Table .= "<tr><th colspan=\"2\" $this->Html_Th_Options>$title</th></tr>\n";
            }
        }
    }

    public function CheckMissing($required, $value, $empty, $field_name)
    {
        if ($this->RequiredCheck($required, 1) and ($value == $empty)) {
            $this->AddError("$field_name {$this->Form_Var['is_missing']}");
        }
    }


    public function GetMax($max_field)
    {
        $min_max = explode(',', str_replace(' ', '', $max_field));
        return (count($min_max)>1)? $min_max[1] : $max = $min_max[0];
    }

    public function CheckMinMax($value, $field1, $max_field)
    {
        $min_max = explode(',', str_replace(' ', '', $max_field));

        $RESULT = true;
        if ($value) {
            if (count($min_max)>1) {
                if (strlen($value) < $min_max[0]) {
                    $text = str_replace('@', $min_max[0], $this->Form_Var['has_too_few_characters']);
                    $this->AddError("$this->Field1 $text");
                    $RESULT = false;
                }
                $max = $min_max[1];
            } else {
                $max = $min_max[0];
            }
            if (strlen($value) > $max) {
                $text = str_replace('@', $max, $this->Form_Var['has_too_many_characters']);
                $this->AddError("$this->Field1 $text");
                $RESULT = false;
            }
        }
        return $RESULT;
    }


    public function CheckListValue($value, $fields, $start_index)
    {
        $RESULT = false;
        if ($value) {
            $field_count = count($this->Fields);
            for($i = $start_index; $i < $field_count; $i++) {
                $option = strTo($this->Fields[$i], '=');
                $option = strTo($option, '::');
                if (($option != '') and ($option == $value) ) {
                    return true;
                }
            }
        }
        return $RESULT;
    }

    //===============================================================================
    //                               PROCESS FORM
    //===============================================================================


    public function ProcessFormNT($formdata)
    {
        // process form without a table returned
        return $this->ProcessForm($formdata, false);
    }

    public function SetTableOptions($tableoptions='', $thoptions='', $tdoptions='')
    {
        $this->Html_Table_Options  = $tableoptions;
        $this->Html_Th_Options     = $thoptions;
        $this->Html_Td_Options     = $tdoptions;
    }

    public function InitializeFormArray($formdata)
    {
        if (!is_array($formdata)) {
            //then we have text only, not an array
            $formdata = explode($this->Form_Var['line_delimit'], $formdata);
        }
        $formdata = array_map('trim', $formdata);
        return $formdata;
    }

    public function GetFormLineFields($line, $action=0)
    {
        $this->Fields = explode('|',trim($line));
        $this->Fields = array_map('trim', $this->Fields);

        $this->Kind = strtolower($this->Fields[0]);

        if ($action == 0) {
            if (substr($this->Kind, 0, 1) == '@') {
                $this->Title_Template = '';
                $this->Info_Template  = '';
            } else {
                $this->Title_Template = $this->Title_Template_Std;
                $this->Info_Template  = $this->Info_Template_Std;
            }
            $this->Kind = preg_replace('/[@!]/', '', $this->Kind);
        } else {
            $this->Kind = str_replace('@', '', $this->Kind);
            if (substr($this->Kind, 0, 1) == '!') {
                $this->Kind = '';
            }
        }

        $this->Field1 = (count($this->Fields) > 1) ? $this->Fields[1] : '';
        $this->Field2 = (count($this->Fields) > 2) ? $this->Fields[2] : '';
        $this->Field3 = (count($this->Fields) > 3) ? $this->Fields[3] : '';
        $this->Field4 = (count($this->Fields) > 4) ? $this->Fields[4] : '';
        $this->Field5 = (count($this->Fields) > 5) ? $this->Fields[5] : '';
        $this->Field6 = (count($this->Fields) > 6) ? $this->Fields[6] : '';
        $this->Field7 = (count($this->Fields) > 7) ? $this->Fields[7] : '';
        $this->Field8 = (count($this->Fields) > 8) ? $this->Fields[8] : '';
        $this->Field9 = (count($this->Fields) > 9) ? $this->Fields[9] : '';
        $this->Field10= (count($this->Fields) > 10) ? $this->Fields[10] : '';
        $this->Field11= (count($this->Fields) > 11) ? $this->Fields[11] : '';
        $this->Field12= (count($this->Fields) > 12) ? $this->Fields[12] : '';

        $this->Alt_Value = strip_tags($this->Field1);
    }


    //===========================================================================================
    //                                 PROCESS FUNCTIONS
    //===========================================================================================
    public function ProcessFunctionHeading()
    {
        if ($this->Process_Want_Table) {
            //"hx|text|options"
            if ($this->Kind == 'fieldset') {
                $this->Kind = empty($this->Field4)? 'h3' : $this->Field4;
                $this->Field2 = empty($this->Field5)? $this->Field3 : $this->Field5;
            }
            if (substr($this->Kind, 0, 2)=='hh') {
                $this->Kind = substr($this->Kind, 1);
            }
            $option = (empty($this->Field2))? 'style="margin:0px 3px;"' : $this->Field2;
            $this->AddTableRow('', "<$this->Kind $option>$this->Field1</$this->Kind>\n", 1);
        }
    }

    public function ProcessFunctionCell()
    {
        if ($this->Process_Want_Table) {
            $this->AddTableRow('', $this->Field1, 1);
        }
    }

    public function ProcessFunctionInfo()
    {
        //"info|title|info|hide (=H)"
        if ($this->Process_Want_Table and ($this->Field3 != 'H')) {
            $this->AddTableRow($this->Field1, $this->Field2);
        }
    }

    public function ProcessFunctionTitle()
    {
        if ($this->Process_Want_Table) {
            $this->Output_Table .= "<tr><th $this->Html_Th_Options>$this->Field1</th>";
        }
    }


    public function ProcessFunctionInfostart()
    {
        //"infostart"
        if ($this->Process_Want_Table) {
            $this->Output_Table .= "<td $this->Html_Td_Options>";
        }
    }

    public function ProcessFunctionInfoend()
    {
        //"infostart"
        if ($this->Process_Want_Table) {
            $this->Output_Table .= "</td></tr>\n";
        }
    }


    public function ProcessFunctionEhidden()
    {
        //"ehidden|VARNAME|value|title"
        $value = $this->GetPostItem($this->Field1);

        if ($value == EncryptString(@DecryptString($value, $this->Encrypt_Query_Key), $this->Encrypt_Query_Key)) {
            $value = DecryptString($value, $this->Encrypt_Query_Key);
        }

        if (!empty($this->Field3)) {
            $tvalue = nl2br($value);
            if ($this->OkRow($value) and $this->Process_Want_Table) {
                $this->AddTableRow($this->Field3, $tvalue);
            }
        }
        $this->Process_Result_Array[$this->Field1] = $value;
    }


    public function ProcessFunctionHidden()
    {
        //"hidden|VARNAME|value|mask|title"
        $value = $this->GetPostItem($this->Field1);
        if (!empty($this->Field3) and $value) if (!preg_match("/$this->Field3/", $value)) {
            if (!empty($this->Field4)) {
                $this->AddError("$this->Field4 {$this->Form_Var['illegal_characters']}");
            } else {
                $this->AddError("$this->Field1 {$this->Form_Var['illegal_characters']}");
            }
        }
        if (!empty($this->Field4)) {
            $tvalue = nl2br($value);
            if ($this->OkRow($value) and $this->Process_Want_Table) {
                $this->AddTableRow($this->Field4, $tvalue);
            }
        }
        $this->Process_Result_Array[$this->Field1] = $value;
    }


    public function ProcessFunctionText()
    {
        //"text|title|VARNAME|required|size|min,maxlength|options|mask|hide in table(H)|aftertext",
        $value = (($this->Kind == 'textquote') or ($this->Kind == 'qtext')) ? $this->GetPostItemQuotes($this->Field2)
            : $this->GetPostItem($this->Field2);
        if ($this->Kind == 'lctext') {
            $value=strtolower($value);
        }

        if ($value and ($this->Kind == 'password') and (strpos($this->Field6, 'SECURE') !== false)) {
            if (!preg_match('/[A-Z]/', $value) or
                !preg_match('/[a-z]/', $value) or
                !preg_match('/[0-9]/', $value) or
                !preg_match('/[\@\#\$\%\^\*\(\)_\+\=\{\}\[\]|\/\:\;\,\.\?~|\-]/', $value)
            ) {
                $this->AddError("$this->Field1 {$this->Form_Var['insecure_password']}");
            }
        }

        $this->CheckMissing($this->Field3, $value, '', $this->Field1);
        $this->CheckMinMax($value, $this->Field1, $this->Field5);

        if (!empty($this->Field7) and $value) {
            if (!preg_match("/$this->Field7/", $value)) {
                $this->AddError("$this->Field1 {$this->Form_Var['illegal_characters']}");
            }
        }
        if ($this->OkRow($value) and ($this->Field8 != 'H') and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $value);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionInteger()
    {
        //"integer|title|VARNAME|required|size|min,max, or max|options|hide in table(H)|aftertext",  // no mask needed
        $value = intOnly(Post($this->Form_Prefix.$this->Field2));  // only take integer values

        $this->CheckMinMax($value, $this->Field1, $this->Field5);
        $this->CheckMissing($this->Field3, $value, '', $this->Field1);

        $this->Process_Result_Array[$this->Field2] = $value;
        if ($this->Kind == 'integerc') {
            $value = number_format($value, 0);
        }
        if ($this->OkRow($value) and ($this->Field7 != 'H') and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $value);
        }
    }

    public function ProcessFunctionDollar()
    {
        //"dollar|title|VARNAME|required|size|maxlength|options|aftertext",
        $value = $this->GetPostItem($this->Field2);

        if ($value != '') {
            $value = preg_replace('/[^0-9\.]/', '', $value);
            $decimal = (strpos($value, '.') !== false)? 2 : 0;
            $value_format = number_format($value, $decimal);
        }

        $this->CheckMissing($this->Field3, $value, '', $this->Field1);
        
        if ($this->Kind == 'dollar') {
            $this->CheckMissing($this->Field3, $value, '0', $this->Field1);
        }
        
        $this->CheckMinMax($value, $this->Field1, $this->Field5);

        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, '$' . $value_format);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionAutocomplete()
    {
        //"autocomplete|title|VARNAME|N|40|80|Options|functionname|url|params|Mask",
        $value = $this->GetPostItem($this->Field2);
        $value_shown = TransformContent(Post("AC_$this->Form_Prefix$this->Field2"), 'TS');
        if (empty($value_shown)) {
            $value = '';
        }
        $this->CheckMissing($this->Field3, $value, '', $this->Field1);
        if (!empty($this->Field10) and $value) if (!preg_match("/$this->Field10/", $value)) {
            $this->AddError("$this->Field1 value {$this->Form_Var['illegal_characters']}");
        }
        if ($this->OkRow($value) and $this->Process_Want_Table) $this->AddTableRow($this->Field1, $value_shown);
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionCreditcard()
    {
        //"text|title|VARNAME|required|options"  //no mask needed
        $value = intOnly($this->GetPostItem($this->Field2));
        $this->CheckMissing($this->Field3, $value, '', $this->Field1);
        if ($value) {
            if (!$this->ValidCreditCardNumber($value)) {
                $this->AddError("$this->Field1 {$this->Form_Var['is_not_a_valid_number']}");
            }
            if (!$this->CheckCreditCardLength($value)) {
                $this->AddError("$this->Field1 {$this->Form_Var['has_incorrect_number_count']}");
            }

            $outputvalue = "xxxx-xxxx-xxxx-".substr($value,12,4);
        } else $outputvalue = '';
        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $outputvalue);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionWebsite()
    {
        //website|title|VARNAME|required|size|maxlength|options|aftertext
        $value   = $this->GetPostItem($this->Field2);
        $domain  = preg_replace('/^(http|https):\/\//', '', $value);
        $prefix  = $this->GetPostItem($this->Field2 . '_WEB_PREFIX');

        $validcheck = $this->CheckDomainName($domain);
        if($value and !$validcheck) {
            $this->AddError("$this->Field1 {$this->Form_Var['is_not_valid']}");
        }

        $full_domain = ($domain)? $prefix . $domain : '';
        $this->CheckMissing($this->Field3, $value, '', $this->Field1);
        if ($this->OkRow($value) and $this->Process_Want_Table and $validcheck) {
            $this->AddTableRow($this->Field1, $full_domain);
        }
        $_POST[$this->Form_Prefix.$this->Field2] = $full_domain;  // update post with phone number transform
        $this->Process_Result_Array[$this->Field2] = $full_domain;
    }

    public function ProcessFunctionPhone()
    {
        //"phone|title|VARNAME|required|options",  (mask not needed)
        $value = $this->PhoneConvert($this->GetPostItem($this->Field2));
        if($value and strlen($value)< 12) {
            $this->AddError("$this->Field1 {$this->Form_Var['is_missing_numbers']}");
        }
        $this->CheckMissing($this->Field3, $value, '', $this->Field1);
        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $value);
        }
        $_POST[$this->Form_Prefix.$this->Field2] = $value;  // update post with phone number transform
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionEmail()
    {
        //"email|title|VARNAME|required|size|maxlength|options"
        $value = $this->GetPostItem($this->Field2);
        $this->CheckMissing($this->Field3, $value, '', $this->Field1);
        if ($value) {
            if (!CheckEmail($value)) {
                $this->AddError("$this->Field1 {$this->Form_Var['is_not_valid']}");
            }
        }
        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $value);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionTextarea()
    {
        //"textarea|title|VARNAME|required|cols|rows|options|mask"
        $value = ($this->Kind == 'qtextarea')? $this->GetPostItemQuotes($this->Field2) : $this->GetPostItem($this->Field2);
        $value = $this->GetPostItem($this->Field2);
        $this->CheckMissing($this->Field3, $value, '', $this->Field1);
        if (!empty($this->Field7) and $value) if (!preg_match("/$this->Field7/", $value)) {
            $this->AddError("$this->Field1 {$this->Form_Var['illegal_characters']}");
        }
        $tvalue = nl2br($value);
        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $tvalue);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }


    public function ProcessFunctionHtml()
    {
        //"html|title|VARNAME|required|cols|rows|mask"
        $value = $this->GetPostHTML($this->Field2);
        $this->CheckMissing($this->Field3, $value, '', $this->Field1);
        if (!empty($this->Field7) and $value) if (!preg_match("/$this->Field7/", $value)) {
            $this->AddError("$this->Field1 {$this->Form_Var['illegal_characters']}");
        }
        $tvalue = nl2br($value);
        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $tvalue);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }


    public function ProcessFunctionSelect()
    {
        //"select|title|VAR|required|options|value1=text|value2=text"
        $value = $this->GetPostItem($this->Field2);
        $this->CheckMissing($this->Field3, $value, $this->Form_Var['start_select_value'], $this->Field1);
        if ($value==$this->Form_Var['start_select_value']) {
            $value = '';
        }

        $start_index = ($this->Field5 == 'N')? 6 : 5;
        if (!$this->CheckListValue($value, $this->Fields, $start_index)) $value = '';

        if ($this->OkRow($value) and $this->Process_Want_Table) $this->AddTableRow($this->Field1, $value);
        $this->Process_Result_Array[$this->Field2] = $value;
    }


    public function ProcessFunctionSelecttext()
    {
        //"selecttext|title|VAR|required|size|min,max or max|mask|value1|value2 . . ."
        $value = $this->GetPostItem($this->Field2);
        $value2 = $this->GetPostItem("new_$this->Field2");

        if (($value != $this->Form_Var['start_select_value']) and
            ($value != $this->Form_Var['new_select_text_value']) and
            !$this->CheckListValue($value, $this->Fields, 7)) {
                $value = '';
        }

        $this->CheckMissing($this->Field3, $value, $this->Form_Var['start_select_value'], $this->Field1);

        if ($value==$FORM_VAR['start_select_value']) {
            $value = '';
        }

        if (($this->Field3 == 'Y') and ($value==$this->Form_Var['new_select_text_value']) and ($value2 == '')) {
            $this->AddError("$this->Field1 {$this->Form_Var['is_missing']}");
        }


        if ($value == $this->Form_Var['new_select_text_value']) {
            $value = $value2;

            $this->CheckMinMax($value, $this->Field1, $this->Field5);
        }

        if ($this->OkRow($value) and $this->Process_Want_Table) $this->AddTableRow($this->Field1, $value);
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionSelectcount()
    {
        //"selectcount|title|VAR|required|start|end"
        $value = $this->GetPostItem($this->Field2);
        $this->CheckMissing($this->Field3, $value, $this->Form_Var['start_select_value'], $this->Field1);
        if ($value==$this->Form_Var['start_select_value']) {
            $value = '';
        }
        $value = intOnly($value);
        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $value);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionDatepick()
    {
        //"datepick|title|varname|required|startyear|NOW|function|options|aftertext"
        $value = IntOnly(Post($this->Form_Prefix.$this->Field2));  // only take integer values
        $this->CheckMissing($this->Field3, $value, '', $this->Field1);

        if ($value) {
            $value = substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2);
        }

        $this->Process_Result_Array[$this->Field2] = $value;

        if ($this->OkRow($value) and $this->Process_Want_Table) $this->AddTableRow($this->Field1, $value);
    }


    public function ProcessFunctionDatecc()
    {
        //"datecc|title|varname|required|hide=H"
        if (empty($this->Field3)) $this->Field3 = 'Y';
        $yearvar  = "{$this->Field2}_YEAR";
        $monthvar = "{$this->Field2}_MONTH";

        $value = intOnly($this->GetPostItem($yearvar));
        $this->CheckMissing($this->Field3, $value, '00',"$this->Field1 {$this->Form_Var['year']}");
        $yearvar_result = $value;

        $value = intOnly($this->GetPostItem($monthvar));
        $this->CheckMissing($this->Field3, $value, '00',"$this->Field1 {$this->Form_Var['month']}");

        $monthvar_result = $value;
        if (($yearvar_result==date('y')) and (intval($monthvar_result)< date('n'))) {
            $this->AddError("$this->Field1 {$this->Form_Var['has_passed']}");
        }

        if (!empty($yearvar_result) and !empty($monthvar_result)) {
            $value = "$monthvar_result$yearvar_result";
            $_POST[$this->Form_Prefix.$this->Field2] = $value;
        } else $value ='';
        if ($this->Field4 != 'H') {
            if ($this->OkRow($value) and $this->Process_Want_Table) {
                $this->AddTableRow($this->Field1, $value);
            }
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionDateymd()
    {
        //"dateymd|title|varname|format|required|startyear|NOW"
        //"dateym|title|varname|format|required|startyear|NOW"

        $this->Field3 = strtolower($this->Field3);
        $yearvar  = "{$this->Field2}_YEAR";
        $monthvar = "{$this->Field2}_MONTH";
        $dayvar   = "{$this->Field2}_DAY";

        $yearvar_result = intOnly($this->GetPostItem($yearvar));
        $this->CheckMissing($this->Field4, $yearvar_result, '', "$this->Field1 {$this->Form_Var['year']}");
        $monthvar_result = intOnly($this->GetPostItem($monthvar));
        $this->CheckMissing($this->Field4, $monthvar_result, '', "$this->Field1 {$this->Form_Var['month']}");

        if ($this->Kind == 'dateymd') {
            $dayvar_result = intOnly($this->GetPostItem($dayvar));
            $this->CheckMissing($this->Field4, $dayvar_result, '', "$this->Field1 {$this->Form_Var['day']}");
        }

        if ((($this->Kind == 'dateymd') and !empty($yearvar_result) and !empty($monthvar_result) and !empty($dayvar_result))
          or  (($this->Kind == 'dateym') and !empty($yearvar_result) and !empty($monthvar_result))) {
            $value = str_replace('y', $yearvar_result, $this->Field3);
            $value = str_replace('m', $monthvar_result, $value);
            if ($this->Kind == 'dateymd') $value = str_replace('d', $dayvar_result, $value);
        } else {
            $value ='';
        }

        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $value);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionTime()
    {
        //"time|title|varname|format|required|options"
        // NOTE : format not used

        $this->Field3 = strtolower($this->Field3);
        $hourvar  = "{$this->Field2}_HOUR";
        $minutevar = "{$this->Field2}_MINUTE";

        $value = intOnly($this->GetPostItem($hourvar));
        $this->CheckMissing($this->Field4, $value,
            $this->Form_Var['start_select_value'], "$this->Field1 {$this->Form_Var['hour']}");

        if ($value==$this->Form_Var['start_select_value']) $value = '';
        $hour_result = $value;

        $value = intOnly($this->GetPostItem($minutevar));
        $this->CheckMissing($this->Field4, $value,
            $this->Form_Var['start_select_value'],"$this->Field1 {$this->Form_Var['minute']}");

        if ($value==$this->Form_Var['start_select_value']) $value = '';
        $minute_result = $value;

        if (($hour_result != '') and ($minute_result !='')) {
            $value = "$hour_result:$minute_result";
        } else $value ='';

        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $value);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionDatetime()
    {
        //"datetime|title|varname|required|startyear|NOW"

        $yearvar  = "{$this->Field2}_YEAR";
        $monthvar = "{$this->Field2}_MONTH";
        $dayvar   = "{$this->Field2}_DAY";
        $hourvar   = "{$this->Field2}_HOUR";
        $minutevar = "{$this->Field2}_MINUTE";

        $yearvar_result = intOnly($this->GetPostItem($yearvar));
        $this->CheckMissing($this->Field3, $yearvar_result, '', "$this->Field1 {$this->Form_Var['year']}");

        $monthvar_result = intOnly($this->GetPostItem($monthvar));
        $this->CheckMissing($this->Field3, $monthvar_result, '', "$this->Field1 {$this->Form_Var['month']}");

        $dayvar_result = intOnly($this->GetPostItem($dayvar));
        $this->CheckMissing($this->Field3, $dayvar_result, '', "$this->Field1 {$this->Form_Var['day']}");

        $hourvar_result = intOnly($this->GetPostItem($hourvar));
        $this->CheckMissing($this->Field3, $hourvar_result,
            $this->Form_Var['start_select_value'], "$this->Field1 {$this->Form_Var['hour']}");

        $minutevar_result = intOnly($this->GetPostItem($minutevar));
        $this->CheckMissing($this->Field3, $minutevar_result,
            $this->Form_Var['start_select_value'],"$this->Field1 {$this->Form_Var['minute']}");

        if (!empty($yearvar_result) and !empty($monthvar_result) and !empty($dayvar_result)
            and !empty($hourvar_result) and !empty($minutevar_result)) {
            $value = "$yearvar_result-$monthvar_result-$dayvar_result $hourvar_result:$minutevar_result:00";
        } else {
            $value ='';
        }

        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $value);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }


    public function ProcessFunctionCountry()
    {
        //"country|title|VAR|required"
        $value = $this->GetPostItem($this->Field2);
        $this->CheckMissing($this->Field3, $value, $this->Form_Var['start_select_value'], $this->Field1);
        if ($value==$this->Form_Var['start_select_value']) $value = '';
        $cname = $this->GetCountryNameOrCode($value);
        if (empty($cname)) {
            $value = '';
        }
        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $cname);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionCountrystate()
    {
        //"countrystate|title|VARcountry:VARstate|required|options"
        list($country_var, $state_var) = explode(':', $this->Field2);
        $country_value = $this->GetPostItem($country_var);
        $value = $this->GetPostItem($state_var);
        if (empty($value)) {
            if ($country_value == 'US') {
                $value = $this->GetPostItem('US_STATES_' . $state_var);
            } elseif ($country_value == 'CA') {
                $value = $this->GetPostItem('CANADA_PROVINCES_' . $state_var);
            } elseif ($country_value != $this->Form_Var['start_select_value']) {
                $value = $this->GetPostItem('OTHER_STATES_' . $state_var);
            }
        }
        if ($value == $FORM_VAR['start_select_value']) {
            $value = '';
        }
        if ($country_value == 'US') {
            //---- process US state ----
            $this->CheckMissing($this->Field3, $value, $this->Form_Var['start_select_value'], $this->Form_Var['state']);
            if (!in_array($value, $this->Form_State_Char_Codes)) {
                $value = '';
            }
            if ($this->OkRow($value) and $this->Process_Want_Table) {
                $this->AddTableRow($this->Form_Var['state'], $value);
            }
            $this->Process_Result_Array[$state_var] = $value;

        } elseif ($country_value == 'CA') {
            //---- process Canada Province ----
            $this->CheckMissing($this->Field3, $value,
                $this->Form_Var['start_select_value'], $this->Form_Var['canada_province']);

            if (!in_array($value, $this->Form_Canada_Provinces)) {
                $value = '';
            }

            if ($this->OkRow($value) and $this->Process_Want_Table) {
                $this->AddTableRow($this->Form_Var['canada_province'], $value);
            }
            $this->Process_Result_Array[$state_var] = $value;

        } elseif($country_value != $this->Form_Var['start_select_value']) {
            //---- process Other State/Province ----
            $this->CheckMissing($this->Field3, $value, '', $this->Form_Var['state_province']);
            if (!empty($value) and (!preg_match("/$Mask_General_Line/", $value))) {
                $this->AddError("{$this->Form_Var['state_province']} {$this->Form_Var['illegal_characters']}");
            }
            if ($this->OkRow($value) and $this->Process_Want_Table) {
                $this->AddTableRow($this->Form_Var['state_province'], $value);
            }
            $this->Process_Result_Array[$state_var] = $value;
        }

        //---- process country ----
        $value = $country_value;
        $this->CheckMissing($this->Field3, $value, $this->Form_Var['start_select_value'], $this->Field1);
        if ($value==$this->Form_Var['start_select_value']) {
            $value = '';
        }
        $cname = $this->GetCountryNameOrCode($value);
        if (empty($cname)) {
            $value = '';
        }
        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $cname);
        }
        $this->Process_Result_Array[$country_var] = $value;
    }

    public function ProcessFunctionState()
    {
        //"state|title|VAR|required|US"
        $value = $this->GetPostItem($this->Field2);
        $this->CheckMissing($this->Field3, $value, $this->Form_Var['start_select_value'], $this->Field1);

        if (!in_array($value, $this->Form_State_Char_Codes)) {
            $value = '';
        }
        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $value);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public function ProcessFunctionIntstate()
    {
        //"intstate|title|VAR|required|options|countryid"
        $value = $this->GetPostItem($this->Field2);

        if (!in_array($value, $this->Form_State_Char_Codes)) {
            $value = '';
        }

        $intvalue = $this->GetPostItem("INT_$this->Field2");

        if (($value=='INT') and ($intvalue == '')) {
            $this->CheckMissing($this->Field3, $value, 'INT', "$this->Field1 {$this->Form_Var['international_entry']}");
        }
        if ($value == 'INT') $value = $intvalue;
        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $value);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }


    public function ProcessFunctionRadio()
    {
        //"radio|title|VAR|required|options|value1=text|value2=text"
        $value = $this->GetPostItem($this->Field2);
        for($i=5; $i<count($this->Fields); $i++) {
            $itemvalue = trim(strTo($this->Fields[$i], '='));
            $itemtext  = trim(strFrom($this->Fields[$i], '='));
            if (empty($itemtext)) {
                $itemtext = $itemvalue;
            }

            if ($itemvalue == $value) $avalue = $itemtext;
        }
        if (empty($avalue)) {
            $value = '';
        }
        $this->CheckMissing($this->Field3, $value, '', $this->Field1);

        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $avalue);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }


    public function ProcessFunctionCheckboxlist()
    {
        //"checkboxlist|title|options|value1=text|value2=text"
        for($i=3; $i<count($this->Fields); $i++) {

            $itemvalue = trim(strTo($this->Fields[$i], '='));
            $itemtext  = trim(strFrom($this->Fields[$i], '='));
            if (empty($itemtext)) {
                $itemtext = $itemvalue;
            }

            $itemname = preg_replace('/[^a-zA-Z0-9]/', '_', $itemtext);
            $value = $this->GetPostItem($itemname);
            if (!empty($value) and ($value != $itemvalue)) {
                $this->AddError("$this->Field1 {$this->Form_Var['illegal_characters']}");
            }
            $checked = ($value)? 'Checked' : '';
            if ($this->OkRow($value) and $this->Process_Want_Table) {
                $this->AddTableRow($itemtext, $checked);
            }
            $this->Process_Result_Array[$itemname] = $value;
        }
    }

    public function ProcessFunctionCheckboxlistBar()
    {
        //"checkboxlistbar|title|var|Y|options|value1=text|value2=text"
        $item_list = '';
        $have_checked_item = 0;
        for($i=5; $i<count($this->Fields); $i++) {
            $itemvalue = trim(strTo($this->Fields[$i], '='));
            $itemtext  = trim(strFrom($this->Fields[$i], '='));
            if (empty($itemtext)) {
                $itemtext = $itemvalue;
            }
            $count = $i-5;
            $itemname = "$this->Field2$count";
            $value = $this->GetPostItem($itemname);
            if (!empty($value) and ($value != $itemvalue)) {
                $this->AddError("$this->Field1 {$this->Form_Var['illegal_characters']}");
            }
            $item_result = ($value)? $itemvalue : '';
            if ($item_result) $have_checked_item = 1;
            if ($this->OkRow($value) and $this->Process_Want_Table) {
                $this->AddTableRow($itemtext, $item_result);
            }
            $item_list .= "$item_result|";
        }
        $this->CheckMissing($this->Field3, $item_result, 0, $this->Field1);

        $this->Process_Result_Array[$this->Field2] = substr($item_list,0,-1);
    }

    public function ProcessFunctionCheckboxlistSet()
    {
        //"checkboxlistset|title|var|Y|options|value1=text|value2=text"
        $item_list = '';
        $item_array = array();
        $have_checked_item = 0;
        for($i=5; $i<count($this->Fields); $i++) {
            $itemvalue = trim(strTo($this->Fields[$i], '='));
            $itemtext  = trim(strFrom($this->Fields[$i], '='));
            if (empty($itemtext)) {
                $itemtext = $itemvalue;
            }
            $count = $i-5;
            $itemname = "$this->Field2$count";
            $value = $this->GetPostItem($itemname);
            if (!empty($value) and ($value != $itemvalue)) {
                $this->AddError("$this->Field1 {$this->Form_Var['illegal_characters']}");
            }
            $item_result = ($value)? $itemvalue : '';
            if ($item_result) {
                $have_checked_item = 1;
                $item_array[] = $item_result;
            }
        }

        $item_list = implode(',', $item_array);

        if ($this->OkRow($value) and $this->Process_Want_Table and $have_checked_item) {
            $this->AddTableRow($this->Field1, $item_list);
        }

        $this->CheckMissing($this->Field3, $have_checked_item, 0, $this->Field1);

        $this->Process_Result_Array[$this->Field2] = $item_list;
    }

    public function ProcessFunctionCheckbox()
    {
        //"checkbox|title|varname|options|value|value-null"
        $value = $this->GetPostItem($this->Field2);
        if (!empty($value) and ($value != $this->Field4)) {
            $this->AddError("$this->Field1 {$this->Form_Var['illegal_characters']}");
        }
        if ($value != $this->Field4) $value = $this->Field5;
        if ($this->OkRow($value) and $this->Process_Want_Table) {
            $this->AddTableRow($this->Field1, $value);
        }
        $this->Process_Result_Array[$this->Field2] = $value;
    }

    public $Process_Functions = array(
        'h1'              => 'ProcessFunctionHeading',
        'h2'              => 'ProcessFunctionHeading',
        'h3'              => 'ProcessFunctionHeading',
        'h4'              => 'ProcessFunctionHeading',
        'h5'              => 'ProcessFunctionHeading',
        'h6'              => 'ProcessFunctionHeading',
        'hh1'             => 'ProcessFunctionHeading',
        'hh2'             => 'ProcessFunctionHeading',
        'hh3'             => 'ProcessFunctionHeading',
        'hh4'             => 'ProcessFunctionHeading',
        'hh5'             => 'ProcessFunctionHeading',
        'hh6'             => 'ProcessFunctionHeading',
        'fieldset'        => 'ProcessFunctionHeading',
        'cell'            => 'ProcessFunctionCell',
        'info'            => 'ProcessFunctionInfo',
        'title'           => 'ProcessFunctionTitle',
        'infostart'       => 'ProcessFunctionInfostart',
        'infoend'         => 'ProcessFunctionInfoend',
        'ehiddeninfo'     => 'ProcessFunctionEhidden',
        'ehidden'         => 'ProcessFunctionEhidden',
        'hiddeninfo'      => 'ProcessFunctionHidden',
        'hidden'          => 'ProcessFunctionHidden',
        'text'            => 'ProcessFunctionText',
        'lctext'          => 'ProcessFunctionText',
        'password'        => 'ProcessFunctionText',
        'textquote'       => 'ProcessFunctionText',
        'qtext'           => 'ProcessFunctionText',
        'integer'         => 'ProcessFunctionInteger',
        'integerc'        => 'ProcessFunctionInteger',
        'dollar'          => 'ProcessFunctionDollar',
        'dollarz'         => 'ProcessFunctionDollar',
        'autocomplete'    => 'ProcessFunctionAutocomplete',
        'creditcard'      => 'ProcessFunctionCreditcard',
        'website'         => 'ProcessFunctionWebsite',
        'phone'           => 'ProcessFunctionPhone',
        'email'           => 'ProcessFunctionEmail',
        'textarea'        => 'ProcessFunctionTextarea',
        'qtextarea'       => 'ProcessFunctionTextarea',
        'html'            => 'ProcessFunctionHtml',
        'select'          => 'ProcessFunctionSelect',
        'selecttext'      => 'ProcessFunctionSelecttext',
        'selectcount'     => 'ProcessFunctionSelectcount',
        'datepick'        => 'ProcessFunctionDatepick',
        'datecc'          => 'ProcessFunctionDatecc',
        'dateymd'         => 'ProcessFunctionDateymd',
        'dateym'          => 'ProcessFunctionDateymd',
        'time'            => 'ProcessFunctionTime',
        'datetime'        => 'ProcessFunctionDatetime',
        'country'         => 'ProcessFunctionCountry',
        'countrystate'    => 'ProcessFunctionCountrystate',
        'state'           => 'ProcessFunctionState',
        'intstate'        => 'ProcessFunctionIntstate',
        'radioh'          => 'ProcessFunctionRadio',
        'radio'           => 'ProcessFunctionRadio',
        'checkboxlist'    => 'ProcessFunctionCheckboxlist',
        'checkboxlistbar' => 'ProcessFunctionCheckboxlistBar',
        'checkboxlistbarh'=> 'ProcessFunctionCheckboxlistBar',
        'checkboxlistset' => 'ProcessFunctionCheckboxlistSet',
        'checkboxlistseth'=> 'ProcessFunctionCheckboxlistSet',
        'checkbox'        => 'ProcessFunctionCheckbox',
    );

    public function ProcessForm($formdata, $want_table=true)
    {
        if (empty($formdata)) {
            $this->AddError('NO FORM DATA');
            return '';
        }

        $this->Process_Want_Table = $want_table;

        $formdata = $this->InitializeFormArray($formdata);

        $this->Output_Table = '';

        $this->Process_Result_Array = array();

        if (!FromThisDomain() and $this->this->Form_Block_Referrer) {
            $this->AddError($this->Form_Var['referrer_error']);
            return $this->Process_Result_Array;
        }

        if ($this->Process_Want_Table) {
            $this->Output_Table = "<table $this->Html_Table_Options>\n<tbody>\n";
        }

        foreach($formdata as $line) {

            $this->GetFormLineFields($line);

            $function = empty($this->Process_Functions[$this->Kind])? '' : $this->Process_Functions[$this->Kind];
            if ($function) {
                $this->$function();
            }
        }

        if ($this->Form_Show_Posted) {
            if ($this->Process_Want_Table) {
                $date = date($this->Form_Var['form_date_code'], time()+($this->Form_Time_Shift * 3600));
                $this->AddTableRow("<div style=\"{$this->Form_Var['posted_cell_style']}\">Posted: $date</div>", '', 1);
            }
        }

        if($this->Process_Want_Table) {
            $this->Output_Table .= "</tbody>\n</table>\n";
        }

        return $this->Process_Result_Array;
    }

    //===============================================================================
    //                               OUTPUT FORM
    //===============================================================================

    public function GetTitleCode($title, $title_template)
    {
        if (empty($title_template)) {
            return $title;
        } else {
            if (empty($title)) {
                return str_replace(array('@:', '@'), '', $title_template);
            } else {
                return str_replace('@', $title, $title_template);
            }
        }
    }

    public function GetInfoCode($info, $info_template)
    {
        if (empty($this->Info_Template)) {
            return $info;
        } else {
            return str_replace('@', $info, $info_template);
        }
    }


    public function OutputForm($formdata, $process=0)
    {

        $this->Form_Javascript = '';
        $this->Javascript_Functions = array();

        $newdatecodes = array('yyyy', 'mm', 'dd');
        $olddatecodes = array('y', 'm', 'd');

        $this->Select_Start = "<option value=\"{$this->Form_Var['start_select_value']}\">{$this->Form_Var['start_select']}</option>\n";

        $this->Title_Template_Std = $this->Form_Var['title_template'];
        $this->Info_Template_Std  = $this->Form_Var['info_template'];

        if (empty($formdata)) {
            $error = 'NO FORM DATA';
            return '';
        }

        $formdata = $this->InitializeFormArray($formdata);

        $RESULT = '';
        foreach($formdata as $line) {

            $this->GetFormLineFields($line, 0);

            switch ($this->Kind) {

            case 'titletemplate':
                if ($this->Field1 == 'STD') {
                    $this->Title_Template_std = $this->Form_Var['title_template'];
                } else {
                    if (strpos($this->Field1, '@') === false) {
                        echo '<h3>FORM TITLE TEMPLATE ERROR: NO @</h3>';
                        return;
                    }
                    $this->Title_Template_std = $this->Field1;
                }
                break;


            case 'infotemplate':
                if ($this->Field1 == 'STD') {
                    $this->Info_Template_std = $this->Form_Var['info_template'];
                } else {
                    if (strpos($this->Field1, '@') === false) {
                        echo '<h3>FORM INFO TEMPLATE ERROR: NO @</h3>';
                        return;
                    }
                    $this->Info_Template_std = $this->Field1;
                }
                break;


            case 'code':
            case 'cell':
                //"code|text"
                $RESULT .= "$this->Field1\n";
                break;

            case 'js':
                //"js|code"
                $this->Form_Javascript .= "$this->Field1\n";
                break;

            case 'form':
                //"form|action|method|name"
                $name = ($this->Field3)? " name=\"$this->Field3\"" : '';
                $id   = ($this->Field3)? " id=\"$this->Field3\"" : '';
                $RESULT .= "<form action=\"$this->Field1\" method=\"$this->Field2\" {$this->Form_Var['accept_charset']}$id$name>\n";
                break;


            case 'info':
                //"info|title|info"
                if (!$this->Field2) {
                    $this->Field2 = '&nbsp;';
                }
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $RESULT .= $this->GetInfoCode($this->Field2, $this->Info_Template);
                break;

            case 'title':    // these are experimental
                //"title|title"
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                break;

            case 'infostart':
                //"infostart"
                $RESULT .= strTo($this->Info_Template, '@');
                break;

            case 'infoend':
                //"infostart"
                $RESULT .= strFrom($this->Info_Template, '@');
                break;

            case 'fieldset':
                //"fieldset|title|options_fieldset|options_legend"
                $option1 = ($this->Field2)? " $this->Field2" : '';
                $option2 = ($this->Field3)? " $this->Field3" : '';
                $RESULT .= "<fieldset$option1>\n<legend$option2>$this->Field1</legend>\n";
                break;
            case 'endfieldset':
                //"endfieldset"
                $RESULT .= "</fieldset>\n";
                break;

            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                //"hx|text|options"
                $options = ($this->Field2)? " $this->Field2" : '';
                $RESULT .= "<$this->Kind$options>$this->Field1</$this->Kind>\n";
                break;




            case 'button':
                //"button|title|onclick|options"
                $formitem = "<input type=\"button\" class=\"formsubmit\" value=\"$this->Field1\" onclick=\"$this->Field2\" $this->Field3 />\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'hidden':
            case 'hiddeninfo':
            case 'ehidden':
            case 'ehiddeninfo':
                //"hidden|VARNAME|value|mask|title"
                //"ehidden|VARNAME|value|title"
                if (!$this->Field2) {
                    $this->Field2 = $this->GetPostItem($this->Field1);
                }

                if ($this->Kind == 'ehidden' || $this->Kind == 'ehiddeninfo') {
                    // need to determine if already encrypted
                    if ($this->Field2 != EncryptString(@DecryptString($this->Field2, $this->Encrypt_Query_Key), $this->Encrypt_Query_Key)) {
                        $this->Field2 = EncryptString($this->Field2, $this->Encrypt_Query_Key);
                    }
                }
                if ($this->Kind == 'hiddeninfo' || $this->Kind == 'ehiddeninfo') {
                    $title = ($this->Kind == 'hiddeninfo')? $this->Field4 : $this->Field3;
                    $RESULT .= $this->GetTitleCode($title, $this->Title_Template);
                    $RESULT .= $this->GetInfoCode($this->Field2, $this->Info_Template);
                }

                $id = $this->GetIdFromVar($this->Field1);
                $RESULT .= "<input type=\"hidden\" id=\"$id\" name=\"$this->Form_Prefix$this->Field1\" value=\"$this->Field2\" />\n\n";
                break;

            case 'text':
            case 'lctext':
            case 'textquote':
            case 'qtext':
                //"text|title|VARNAME|required|size|maxlength|options|mask|hide in table(H)|aftertext",
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $value = (($this->Kind == 'textquote') or ($this->Kind == 'qtext')) ? $this->GetPostItemQuotes($this->Field2)
                    : $this->GetPostItem($this->Field2);

                $error = ($process and (($this->RequiredCheck($this->Field3, $process) and !$value) or
                       (!empty($this->Field7) and $value and !preg_match("/$this->Field7/", $value))));

                $class =  ($error)? 'formitemerror' : 'formitem';
                $class2 = TextBetween('class="', '"', $this->Field6);
                if ($class2) {
                    $this->Field6 = trim(str_replace('class="' . $class2 . '"', '', $this->Field6));
                    $class2 = " $class2";
                }
                $options = ($this->Field6)? " $this->Field6" : '';

                $maxlen = ($this->Field5)? ' maxlength="' . $this->GetMax($this->Field5) . '"' : '';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem = "<input type=\"text\" alt=\"$this->Alt_Value\" id=\"$id\" class=\"$class$class2\" name=\"$this->Form_Prefix$this->Field2\" size=\"$this->Field4\"$maxlen value=\"$value\"$options />$this->Field9\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'integer':
            case 'integerc':
                //"integer|title|VARNAME|required|size|maxlength|options|hide in table(H)|aftertext",  // no mask needed

                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $options = ($this->Field6)? " $this->Field6" : '';
                $value = intOnly(Post($this->Form_Prefix.$this->Field2));  // only take integer values
                if ($this->Kind == 'integerc') {
                    $value = number_format($value, 0);
                    $this->Javascript_Functions['formatIntegerObj'] = "
                    public function formatIntegerObj(obj) {
                        var num = obj.value;
                        num = num.replace(/[^0-9]/g, '');
                        var objRegExp  = new RegExp('(-?[0-9]+)([0-9]{3})');
                        while(objRegExp.test(num)) {
                           num = num.replace(objRegExp, '$1,$2');
                        }
                        obj.value = num;
                    }";
                    $options = trim($options . ' onkeyup="formatIntegerObj(this);"');
                }
                $error = ($process and ($this->RequiredCheck($this->Field3, $process) and !$value));
                $class =  ($error)? 'formitemerror' : 'formitem';

                $maxlen = ($this->Field5)? ' maxlength="' . $this->GetMax($this->Field5) . '"' : '';

                $id = $this->GetIdFromVar($this->Field2);
                $formitem = "<input type=\"text\" alt=\"$this->Alt_Value\" id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\" size=\"$this->Field4\"$maxlen value=\"$value\"$options />$this->Field8\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'dollar':
                //"dollar|title|VARNAME|required|size|maxlength|options|aftertext",
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $options = ($this->Field6)? " $this->Field6" : '';
                $value = $this->GetPostItem($this->Field2);
                if ($value != '') {
                    $decimal = (strpos($value, '.') !== false)? 2 : 0;
                    $value = number_format( preg_replace('/[^0-9\.]/', '', $value), $decimal);
                }

                $this->Javascript_Functions['formatRealObj'] = "
    public function formatRealObj(obj) {
        var num = obj.value;
        if (num == '.') num = '0.';
        num = num.replace(/[^0-9\.-]/g, '');
        if (parseFloat(num) != num) {
            num = num.substring(0, num.length-1);
        }
        var parts = num.split('.');
        var base = parts[0];
        var rem  = (parts[1] != null)? '.' + parts[1] : '';
        var objRegExp  = new RegExp('(-?[0-9]+)([0-9]{3})');
        while(objRegExp.test(base)) {
           base = base.replace(objRegExp, '$1,$2');
        }
        obj.value = base + rem;
    }";
                $options = trim($options . ' onkeyup="formatRealObj(this);"');

                $error = ($process and ($this->RequiredCheck($this->Field3, $process) and !$value));

                $class =  ($error)? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem = "$&nbsp;<input type=\"text\" alt=\"$this->Alt_Value\" id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\" size=\"$this->Field4\" maxlength=\"$this->Field5\" value=\"$value\"$options />$this->Field7\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;



            case 'autocomplete':
                //"autocomplete|title|VARNAME|N|40|80|Options|functionname|url|completionfunction|Mask|aftertext",
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $options = ($this->Field6)? " $this->Field6" : '';
                $value  = $this->GetPostItem($this->Field2);
                $value2 = TransformContent(Post("AC_$this->Form_Prefix$this->Field2"), 'TS');
                if ($value and !$value2) {
                    $value2 = $this->GetAutocompleteValue($this->Field8, $value);
                }

                $error = ($process and (($this->RequiredCheck($this->Field3, $process) and !$value) or
                       (!empty($this->Field10) and $value and !preg_match("/$this->Field10/", $value))));

                $class =  ($error)? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem  = "<input type=\"text\" alt=\"$this->Alt_Value\" id=\"AC_$id\" class=\"$class ac_input\" name=\"AC_$this->Form_Prefix$this->Field2\" size=\"$this->Field4\" maxlength=\"$this->Field5\" value=\"$value2\"$options />$this->Field11\n";
                $formitem .= "\n<input type=\"hidden\" id=\"$id\" name=\"$this->Form_Prefix$this->Field2\" value=\"$value\" />\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                $complete_function = ($this->Field9)? $this->Field9 : "''";
                $this->Form_Javascript .= "$this->Field7('AC_$id','$id','$this->Field8',$complete_function);\n";

                break;



            case 'creditcard':
                //"creditcard|title|VARNAME|required|options|aftertext"  //no mask needed
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $options = ($this->Fields)? " $this->Field4" : '';
                $value = intOnly($this->GetPostItem($this->Field2));
                $validcheck = $this->ValidCreditCardNumber($value);
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and (!$value or !$validcheck))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem = "<input type=\"text\" alt=\"$this->Alt_Value\" id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\" size=\"$this->Form_Creditcard_Length\" maxlength=\"$this->Form_Creditcard_Length\" value=\"$value\"$options />$this->Field5\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'website' :
                //website|title|VARNAME|required|size|maxlength|options|aftertext
                $this->Field1  = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $options = ($this->Field6)? " $this->Field6" : '';
                $value   = $this->GetPostItem($this->Field2);
                if (substr($value, 0, 4) == 'http') {
                    $prefix  = strTo($value, '://') . '://';
                } else {
                    $prefix  = $this->GetPostItem($this->Field2 . '_WEB_PREFIX');
                }
                $domain  = preg_replace('/^(http|https):\/\//', '', $value);

                $validcheck = $this->CheckDomainName($domain); //<<<<<<<<<<---------- FIX ----------<<<<<<<<<<
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and (!$value or !$validcheck))? 'formitemerror' : 'formitem';
                $s1 = (($prefix == 'http://') or empty($prefix))? ' selected="selected"' : '';
                $s2 = ($prefix == 'https://')? ' selected="selected"' : '';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem = '<select class="' . $class .'" name="' . $this->Form_Prefix . $this->Field2 . '_WEB_PREFIX' .
                    '"><option' . $s1 .'>http://</option><option' . $s2 .'>https://</option></select>';
                $formitem .= "&nbsp;<input type=\"text\" alt=\"$this->Alt_Value\" id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\" size=\"$this->Field4\" maxlength=\"$this->Field5\" value=\"$domain\"$options />$this->Field7\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
            break;


            case 'phone':
                //"phone|title|VARNAME|required|options|aftertext",  (mask not needed)
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $options = ($this->Field4)? " $this->Field4" : '';
                $value = $this->PhoneConvert($this->GetPostItem($this->Field2));
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ((!$value) or ($value and strlen($value)< 12)))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem = "<input type=\"text\" alt=\"$this->Alt_Value\" id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\" size=\"12\" maxlength=\"20\" value=\"$value\"$options />$this->Field5\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'email':
            case 'lcemail':
                //"email|title|VARNAME|required|size|maxlength|options|aftertext"
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field6)? " $this->Field6" : '';
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $value = $this->GetPostItem($this->Field2);
                if ($this->Field3 == 'Y') {
                    $BadEmail = !CheckEmail($value);
                }
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($BadEmail))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem = "<input type=\"text\" alt=\"$this->Alt_Value\" id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\" size=\"$this->Field4\" maxlength=\"$this->Field5\" value=\"$value\"$options />$this->Field7\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'textarea':
            case 'qtextarea':
                //"textarea|title|VARNAME|required|cols|rows|options",
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field6)? " $this->Field6" : '';
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $value = ($this->Kind == 'qtextarea') ? $this->GetPostItemQuotes($this->Field2) : $this->GetPostItem($this->Field2);
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and (!$value))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem = "<textarea class=\"$class\" id=\"$id\" name=\"$this->Form_Prefix$this->Field2\" cols=\"$this->Field4\" rows=\"$this->Field5\"$options>$value</textarea>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'html':
                //"html|title|VARNAME|required|cols|rows|options",
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field6)? " $this->Field6" : '';
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $value = '';
                if (!empty($_POST[$this->Form_Prefix.$this->Field2])) {
                    $value = trim($_POST[$this->Form_Prefix.$this->Field2]);
                }
                $outtext = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and (!$value))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem = "<textarea class=\"$class\" id=\"$id\" name=\"$this->Form_Prefix$this->Field2\" cols=\"$this->Field4\" rows=\"$this->Field5\"$options>$outtext</textarea>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'datecc':
                //"datecc|title|varname|required|hide"
                if (empty($this->Field3)) $this->Field3='Y';
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);

                $yearvar  = "{$this->Field2}_YEAR";
                $monthvar = "{$this->Field2}_MONTH";
                $value = $this->GetPostItem($this->Field2);

                if ($value) {
                    $yearvalue  = substr($value,2);
                    $monthvalue = substr($value,0,2);
                }

                //----month----
                $monthvalue = ($value)? $monthvalue : $this->GetPostItem($monthvar);
                $yearvalue = ($value)? $yearvalue : $this->GetPostItem($yearvar);

                $class =  ($process and ($monthvalue=='00') and $this->RequiredCheck($this->Field3, $process))? 'formitemerror' : 'formitem';
                if (($yearvalue==date('y')) and (intval($monthvalue)< date('n'))) $class = 'formitemerror';
                $id = $this->GetIdFromVar($monthvar);
                $formitem =  "{$this->Form_Var['month']} <select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$monthvar\">\n";
                $formitem .= "<option value=\"00\">{$this->Form_Var['start_select']}</option>\n";
                for($i=1; $i<=12; $i++) {
                    $selected = ($monthvalue == $i)? ' selected="selected"' : '';
                    $optvalue = sprintf('%02d', $i);
                    $formitem .=  "<option value=\"$optvalue\"$selected>$optvalue - {$this->Form_Months[$i-1]}</option>\n";
                }
                $formitem .=  "</select>\n";

                //----year----
                $startyear = date('Y');
                $endyear   = date('Y', strtotime('+9 year'));

                $class =  (($process) and ($yearvalue=='00') and $this->RequiredCheck($this->Field3, $process))? 'formitemerror' : 'formitem';
                if (($yearvalue==date('y')) and (intval($monthvalue)< date('n'))) $class = 'formitemerror';

                $id = $this->GetIdFromVar($yearvar);
                $formitem .= "{$this->Form_Var['year']} <select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$yearvar\"$options>\n";
                $formitem .= "<option value=\"00\">{$this->Form_Var['start_select']}</option>\n";
                for($i=$startyear; $i<=$endyear; $i++) {
                    $optvalue = substr($i,2);
                    $selected = ($yearvalue == $optvalue)? ' selected="selected"' : '';
                    $formitem .= "<option value=\"$optvalue\"$selected>$i</option>\n";
                }
                $formitem .=  "</select>\n";

                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'datepick':
                //"datepick|title|varname|required|startyear|NOW|function|options|aftertext"

                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $value = $this->GetPostItem($this->Field2);

                $now = date('Y');
                $startyear = ($this->Field4 == 'NOW')? $now : $this->Field4;
                $endyear   = ($this->Field5 == 'NOW')? $now : $this->Field5 + $startyear;
                $startyear = $startyear - $now;
                $endyear   = $endyear - $now;

                $error = ($process and ($this->RequiredCheck($this->Field3, $process) and !$value));
                $class =  ($error)? 'formitemerror datepick' : 'formitem datepick';
                $options = ($this->Field7)? " $this->Field7" : '';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem = "<input type=\"text\"  id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\" size=\"12\" maxlength=\"12\" value=\"$value\"$options />$this->Field8\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                $this->Form_Javascript .= "$this->Field6('$this->Form_Prefix$this->Field2', $startyear, $endyear);\n";
                break;

            case 'dateymd':
            case 'dateym':
            //"dateYMD|title|varname|format|required|startyear|NOW|options|aftertext"
            //"dateYD|title|varname|format|required|startyear|NOW|options|aftertext"

                $this->Field1 = $this->RequiredCheck($this->Field4, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field7)? " $this->Field7" : '';
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);

                $this->Field3 = strtolower($this->Field3);
                $yearvar  = "{$this->Field2}_YEAR";
                $monthvar = "{$this->Field2}_MONTH";
                $dayvar   = "{$this->Field2}_DAY";

                $value = $this->GetPostItem($this->Field2);

                if ($value) {
                    $format = str_replace($olddatecodes, $newdatecodes, $this->Field3);
                    $yearvalue  = ($value)? substr($value,strpos($format, 'yyyy'),4):'';
                    $monthvalue = ($value)? substr($value,strpos($format, 'mm'),2):'';
                    if ($this->Kind == 'dateymd') {
                        $dayvalue   = ($value)? substr($value,strpos($format, 'dd'),2):'';
                    }
                }

                //----year----
                $startyear = ($this->Field5 == 'NOW')? date('Y') : $this->Field5;
                $endyear   = ($this->Field6 == 'NOW')? date('Y') : $this->Field6 + $startyear;

                $id = $this->GetIdFromVar($yearvar);
                if ($endyear == $startyear) {
                    $formitem = "<input type=\"hidden\" id=\"$id\" name=\"$this->Form_Prefix$yearvar\" value=\"$startyear\" />\n";
                    $formitem .= $startyear;
                } else {
                    $yearvalue = ($value)? $yearvalue : intOnly($this->GetPostItem($yearvar));
                    $class =  ($this->RequiredCheck($this->Field4, $process) and ($process) and ($yearvalue==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';

                    $formitem = "{$this->Form_Var['year']}&nbsp;<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$yearvar\"$options>\n";
                    $formitem .= $this->Select_Start;
                    for($i=$startyear; $i<=$endyear; $i++) {
                        $selected = ($yearvalue == $i)? ' selected="selected"' : '';
                        $formitem .= "<option value=\"$i\"$selected>$i</option>\n";
                    }
                    $formitem .=  "</select>";
                }

                //----month----
                $id = $this->GetIdFromVar($monthvar);
                $monthvalue = ($value)? $monthvalue : intOnly($this->GetPostItem($monthvar));
                $class =  ($this->RequiredCheck($this->Field4, $process) and ($process) and ($monthvalue==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $formitem .=  "&nbsp;{$this->Form_Var['month']}&nbsp;<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$monthvar\"$options>\n";
                $formitem .= $this->Select_Start;
                for($i=1; $i<=12; $i++) {
                    $selected = ($monthvalue == $i)? ' selected="selected"' : '';
                    $optvalue = sprintf('%02d', $i);
                    $formitem .=  "<option value=\"$optvalue\"$selected>{$this->Form_Months[$i-1]}</option>\n";
                }
                $formitem .=  "</select>";

                //----day----
                if ($this->Kind == 'dateymd') {
                    $dayvalue = ($value)? $dayvalue : intOnly($this->GetPostItem($dayvar));
                    $value = $this->GetPostItem($dayvar);
                    $class =  ($this->RequiredCheck($this->Field4, $process) and ($process) and ($dayvalue==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                    $id = $this->GetIdFromVar($dayvar);
                    $formitem .=  "&nbsp;{$this->Form_Var['day']}&nbsp;<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$dayvar\"$options>\n";
                    $formitem .= $this->Select_Start;
                    for($i=1; $i<=31; $i++) {
                        $selected = ($dayvalue == $i)? ' selected="selected"' : '';
                        $optvalue = ($i<10)? "0$i" : $i;
                        $formitem .=  "<option value=\"$optvalue\"$selected>$optvalue</option>\n";
                    }
                    $formitem .=  "</select>";
                }
                $formitem .= "$this->Field8\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'time':
                //"time|title|varname|format|required|options"
                $this->Field1 = $this->RequiredCheck($this->Field4, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field5)? " $this->Field5" : '';
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);

                $this->Field3 = strtolower($this->Field3);
                $hourvar  = "{$this->Field2}_HOUR";
                $minutevar = "{$this->Field2}_MINUTE";

                $value = $this->GetPostItem($this->Field2);

                if ($value) {
                    $time_pieces  = explode(':', $value);
                    $hourvalue    = trim($time_pieces[0]);
                    $minutevalue  = trim($time_pieces[1]);  // note could also have seconds
                } else {
                    $hourvalue    = intOnly($this->GetPostItem($hourvar));
                    $minutevalue  = intOnly($this->GetPostItem($minutevar));
                }

                //----hour----

                $class =  ($this->RequiredCheck($this->Field4, $process) and ($process) and ($hourvalue==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($hourvar);
                $formitem  =  "{$this->Form_Var['hour']} <select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$hourvar\"$options>\n";
                $formitem .= $this->Select_Start;
                for($i=0; $i<=23; $i++) {
                    $optvalue = ($i<10)? "0$i" : $i;
                    $selected = ($hourvalue == $optvalue)? ' selected="selected"' : '';
                    if ($i == 0)      $opttitle = '12am';
                    elseif ($i < 12)  $opttitle = $i.'am';
                    elseif ($i == 12) $opttitle = '12pm';
                    else              $opttitle = ($i-12).'pm';
                    $formitem .=  "<option value=\"$optvalue\"$selected>$opttitle</option>";
                }
                $formitem .=  "</select>\n";

                //----minute----
                $class =  ($this->RequiredCheck($this->Field4, $process) and ($process) and ($minutevalue==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($minutevar);
                $formitem .=  " {$this->Form_Var['minute']}&nbsp;<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$minutevar\"$options>\n";
                $formitem .= $this->Select_Start;
                for($i=0; $i<=59; $i++) {
                    $optvalue = ($i<10)? "0$i" : $i;
                    $selected = ($minutevalue == $optvalue)? ' selected="selected"' : '';
                    $opttitle = $optvalue;
                    $style = (!($i % 15))? ' style="background-color:#888; color:#fff;"' : '';
                    $formitem .=  "<option value=\"$optvalue\"$selected$style>$opttitle</option>";
                }
                $formitem .=  "</select>\n";


                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'datetime':
                //"datetime|title|varname|required|startyear|NOW|options"
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field6)? " $this->Field6" : '';
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);

                $yearvar   = "{$this->Field2}_YEAR";
                $monthvar  = "{$this->Field2}_MONTH";
                $dayvar    = "{$this->Field2}_DAY";
                $hourvar   = "{$this->Field2}_HOUR";
                $minutevar = "{$this->Field2}_MINUTE";

                $value = $this->GetPostItem($this->Field2);

                $yearvalue   = ($value)? substr($value, 0, 4) :'';
                $monthvalue  = ($value)? substr($value, 5, 2) :'';
                $dayvalue    = ($value)? substr($value, 8, 2) :'';
                $hourvalue   = ($value)? substr($value, 11, 2) :'';
                $minutevalue = ($value)? substr($value, 14, 2) :'';

                //----year----
                $startyear = ($this->Field4 == 'NOW')? date('Y') : $this->Field4;
                $endyear   = ($this->Field5 == 'NOW')? date('Y') : $this->Field5 + $startyear;

                $id = $this->GetIdFromVar($yearvar);
                if ($endyear == $startyear) {
                    $formitem = "<input type=\"hidden\" id=\"$id\" name=\"$this->Form_Prefix$yearvar\" value=\"$startyear\" />\n";
                    $formitem .= $startyear;
                } else {
                    $yearvalue = ($value)? $yearvalue : intOnly($this->GetPostItem($yearvar));
                    $class =  ($this->RequiredCheck($this->Field4, $process) and ($process) and
                        ($yearvalue==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';

                    $formitem = "{$this->Form_Var['year']}&nbsp;<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$yearvar\"$options>\n";
                    $formitem .= $this->Select_Start;
                    for($i=$startyear; $i<=$endyear; $i++) {
                        $selected = ($yearvalue == $i)? ' selected="selected"' : '';
                        $formitem .= "<option value=\"$i\"$selected>$i</option>\n";
                    }
                    $formitem .=  "</select>";
                }

                //----month----
                $monthvalue = ($value)? $monthvalue : intOnly($this->GetPostItem($monthvar));
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and
                    ($monthvalue==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($monthvar);
                $formitem .=
                "&nbsp;{$this->Form_Var['month']}&nbsp;<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$monthvar\"$options>\n";
                $formitem .= $this->Select_Start;
                for($i=1; $i<=12; $i++) {
                    $selected = ($monthvalue == $i)? ' selected="selected"' : '';
                    $optvalue = sprintf('%02d', $i);
                    $formitem .=  "<option value=\"$optvalue\"$selected>{$this->Form_Months[$i-1]}</option>\n";
                }
                $formitem .=  "</select>";

                //----day----

                $dayvalue = ($value)? $dayvalue : intOnly($this->GetPostItem($dayvar));
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and
                    ($dayvalue==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($dayvar);
                $formitem .=
                    "&nbsp;{$this->Form_Var['day']}&nbsp;<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$dayvar\"$options>\n";
                $formitem .= $this->Select_Start;
                for($i=1; $i<=31; $i++) {
                    $selected = ($dayvalue == $i)? ' selected="selected"' : '';
                    $optvalue = ($i<10)? "0$i" : $i;
                    $formitem .=  "<option value=\"$optvalue\"$selected>$optvalue</option>\n";
                }
                $formitem .=  "</select>";

                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                $formitem = '';
                //$RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                //$RESULT .= '<br /><br />';

                // ---------- end date, start time ---------

                //----hour----

                $hourvalue = ($value)? $hourvalue : intOnly($this->GetPostItem($hourvar));
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($hourvalue==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($hourvar);
                $formitem .=  "{$this->Form_Var['hour']} <select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$hourvar\"$options>\n";
                $formitem .= $this->Select_Start;
                for($i=0; $i<=23; $i++) {
                    $optvalue = ($i<10)? "0$i" : $i;
                    $selected = ($hourvalue == $optvalue)? ' selected="selected"' : '';
                    if ($i == 0)      $opttitle = '12am';
                    elseif ($i < 12)  $opttitle = $i.'am';
                    elseif ($i == 12) $opttitle = '12pm';
                    else              $opttitle = ($i-12).'pm';
                    $formitem .=  "<option value=\"$optvalue\"$selected>$opttitle</option>";
                }
                $formitem .=  "</select>\n";

                //----minute----
                $minutevalue = ($value)? $minutevalue : intOnly($this->GetPostItem($minutevar));

                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($minutevalue==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($minutevar);
                $formitem .=  " {$this->Form_Var['minute']}&nbsp;<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$minutevar\"$options>\n";
                $formitem .= $this->Select_Start;
                for($i=0; $i<=59; $i++) {
                    $optvalue = ($i<10)? "0$i" : $i;
                    $selected = ($minutevalue == $optvalue)? ' selected="selected"' : '';
                    $opttitle = $optvalue;
                    $style = (!($i % 15))? ' style="background-color:#888; color:#fff;"' : '';
                    $formitem .=  "<option value=\"$optvalue\"$selected$style>$opttitle</option>";
                }
                $formitem .=  "</select>\n";

                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'countrystate':
                //"countrystate|title|VARcountry:VARstate|required|options"

                $base = $this->Form_Prefix . $this->Form_Var['id_prefix'];
                $this->Javascript_Functions['formCountryState'] = "
                public function formCountryState(countryVar, stateVar) {
                    var countryElem = document.getElementById('$base' + countryVar);
                    var country_index = countryElem.selectedIndex;
                    var country = '';
                    if (country_index >= 0) {
                        country = countryElem.options[country_index].value;
                    }
                    var usState     = 'none';
                    var canadaState = 'none';
                    var otherState  = 'none';
                    if (country == 'US') usState = '';
                    else if(country == 'CA') canadaState = '';
                    else otherState = '';
                    document.getElementById('{$base}US_STATES_DIV_' + stateVar).style.display        = usState;
                    document.getElementById('{$base}CANADA_PROVINCES_DIV_' + stateVar).style.display = canadaState;
                    document.getElementById('{$base}OTHER_STATES_DIV_' + stateVar).style.display     = otherState;
                }";

                //------- country --------
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field4)? " $this->Field4" : '';

                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);

                list($country_var, $state_var) = explode(':', $this->Field2);
                $country_var_id = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $country_var);
                $state_var_id = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $state_var);

                $raw_state_value = $this->GetPostItem($state_var);

                $this->Form_Javascript .= "formCountryState('$country_var_id', '$state_var_id');\n";

                $value = $this->GetPostItem($country_var);
                $ic = ($value)? $value : $this->Form_Var['default_country'];
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($value==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($country_var);
                $formitem  =  "<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$country_var\" onchange=\"formCountryState('$country_var_id', '$state_var_id')\">\n";
                $formitem .= $this->Select_Start;
                $this->LoadCountryCodes();
                foreach ($this->Country_Codes as $code => $name) {
                    $selected =(($ic == $code) or ($ic == $name))? ' selected="selected"' : '';
                    $formitem .= "<option value=\"$code\"$selected>$name</option>\n";
                }
                $formitem .=  "</select>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);

                //------- us state --------

                $id = $this->GetIdFromVar('US_STATES_DIV_' . $state_var);
                $RESULT .= '<div id="' . $id . '" style="display:block;">' . "\n";
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? $this->Form_Var['required_text'] . $this->Form_Var['state'] : $this->Form_Var['state'];

                $this->Form_State_Codes[0] = $this->Form_Var['start_select'];

                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);

                $value = ($raw_state_value)? $raw_state_value : $this->GetPostItem('US_STATES_' . $state_var);
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($value==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar('US_STATES_' . $state_var);
                $formitem  =  "\n<select id=\"$id\" class=\"$class\" name=\"{$this->Form_Prefix}US_STATES_$state_var\">\n";

                foreach ($this->Form_State_Codes as $code) {
                    $statecode = ($code == $this->Form_Var['start_select']) ? $this->Form_Var['start_select_value'] : substr($code,0,2);
                    $text = ($statecode == $value)? ' selected="selected"': '';
                    $formitem .= "<option$text value=\"$statecode\">$code</option>\n";
                }
                $formitem .=  "</select>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                $RESULT .= "</div>\n";

                //------- Canada Province/Territory --------

                $id = $this->GetIdFromVar('CANADA_PROVINCES_DIV_' . $state_var);
                $RESULT .= '<div id="' . $id . '" style="display:none;">' . "\n";
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? $this->Form_Var['required_text'] . $this->Form_Var['canada_province'] : $this->Form_Var['canada_province'];

                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);

                $value = ($raw_state_value)? $raw_state_value : $this->GetPostItem('CANADA_PROVINCES_' . $state_var);

                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($value==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';

                $id = $this->GetIdFromVar('CANADA_PROVINCES_' . $state_var);
                $formitem  =  "\n<select id=\"$id\" class=\"$class\" name=\"{$this->Form_Prefix}CANADA_PROVINCES_$state_var\">\n";
                $formitem .= $this->Select_Start;
                foreach ($this->Form_Canada_Provinces as $province) {
                    $text = ($province == $value)? ' selected="selected"': '';
                    $formitem .= "<option$text value=\"$province\">$province</option>\n";
                }
                $formitem .=  "</select>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                $RESULT .= "</div>\n";

                 //------- Other State/Province --------

                $id = $this->GetIdFromVar('OTHER_STATES_DIV_' . $state_var);
                $RESULT .= '<div id="' . $id . '" style="display:none;">' . "\n";
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? $this->Form_Var['required_text'] . $this->Form_Var['state_province'] : $this->Form_Var['state_province'];
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $value = ($raw_state_value)? $raw_state_value : $this->GetPostItem('OTHER_STATES_' . $state_var);
                $error = ($process and ($this->RequiredCheck($this->Field3, $process) and !$value));
                $class =  ($error)? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar('OTHER_STATES_' . $state_var);
                $formitem = "<input type=\"text\" alt=\"$state_var\" id=\"$id\" class=\"$class\" name=\"{$this->Form_Prefix}OTHER_STATES_$state_var\" size=\"20\" maxlength=\"60\" value=\"$value\" />\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                $RESULT .= "</div>\n";

                break;

            case 'country':
                //"country|title|VAR|required|options"
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field4)? " $this->Field4" : '';
                if (empty($CountryDivOption)) $CountryDivOption = '';
                $id = $this->GetIdFromVar("countrydiv_$this->Field2");
                $RESULT .= "<div id=\"$id\"$CountryDivOption>";
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $value = $this->GetPostItem($this->Field2);
                $ic = ($value)? $value : $this->Form_Var['default_country'];
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($value==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem  =  "<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\"$options>\n";
                $formitem .= $this->Select_Start;
                $this->LoadCountryCodes();
                foreach ($this->Country_Codes as $code => $name) {
                    $selected =(($ic == $code) or ($ic == $name))? ' selected="selected"' : '';
                    $formitem .= "<option value=\"$code\"$selected>$name</option>\n";
                }
                $formitem .=  "</select>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                $RESULT .= '</div>';
                break;

            case 'state':
                //"state|title|VAR|required|options|US"
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field4)? " $this->Field4" : '';
                $US = ($this->Field5 == 'US');
                if ($US) $this->Form_State_Codes[0] = $this->Form_Var['start_select'];
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $value = $this->GetPostItem($this->Field2);
                $defaultvalue = ($US)? $this->Form_Var['start_select_value'] : 'INT';
                $ic = ($value)? $value : $defaultvalue;
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($value==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem  =  "\n<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\"$options>\n";
                foreach ($this->Form_State_Codes as $code) {
                    $statecode = (
                      ($code == 'International') or
                      ($code == $this->Form_Var['start_select'])
                    ) ? $defaultvalue : substr($code,0,2);
                    $text = ($statecode == $value)? ' selected="selected"': '';
                    $formitem .= "<option$text value=\"$statecode\">$code</option>\n";
                }
                $formitem .=  "</select>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'intstate':
                //"intstate|title|VAR|required|options|countryid"

                $onchange = ($this->Field5 == '')? '' : " onchange=\"stateElem = document.getElementById('{$this->Field2}_span');
             if (stateElem) stateElem.style.display = (this.value=='INT')? '' : 'none';
             countrydivElem = document.getElementById('countrydiv_$this->Field5');
             if (countrydivElem) countrydivElem.style.display = (this.value=='INT')? '' : 'none';
             if (this.value != 'INT') {
                 countryElem = document.getElementById('{$this->Form_Prefix}$this->Field5');
                 if (countryElem) {
                    countryElem.selectedIndex=1;
                    countryElem.value='US';
                  }
                 intState = document.getElementById('INT_$this->Field2');
                 if (intState) intState.value='';
               }
             \"";

                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field4)? " $this->Field4" : '';
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $value = $this->GetPostItem($this->Field2);
                $intvalue = $this->GetPostItem("INT_$this->Field2");
                $ic = ($value)? $value : 'INT';
                $hideInt = ($value and ($value != 'INT') and in_array($value, $this->Form_State_Char_Codes));
                $CountryDivOption = ($hideInt)? ' style="display:none;"' : '';
                $IntStyle = $CountryDivOption;
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($value=='INT') and ($intvalue ==''))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem  =  "<div class=\"withinformtext\" style=\"float:left; width:5.5em;\">{$this->Form_Var['us_state']}:</div><select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\"$options$onchange>\n";

                $have_state = false;
                foreach ($this->Form_State_Codes as $code) {
                    $statecode = (($code == 'International') or ($code == $this->Form_Var['start_select_value'])) ? 'INT' : substr($code,0,2);
                    $text = ($statecode == $value)? ' selected="selected"' : '';
                    if($text and ($statecode != 'INT')) {
                        $have_state = true;
                    }
                    $formitem .= "<option$text value=\"$statecode\">$code</option>\n";
                }
                $formitem .=  "</select>\n";

                if (empty($intvalue) and !$have_state) {
                    $intvalue = ($value != 'INT')? $value : '';
                }
                $id = $this->GetIdFromVar($this->Field2);
                $id2 = $this->GetIdFromVar('INT_' .$this->Field2);
                $formitem .=  "<div id=\"{$id}_span\"$IntStyle><span class=\"withinformtext\">or<br /></span>\n<div class=\"withinformtext\" style=\"float:left; width:5.5em;\">{$this->Form_Var['non_us']}:</div>\n<input type=\"text\" id=\"$id2\" class=\"$class\" name=\"{$this->Form_Prefix}INT_$this->Field2\" size=\"20\" maxlength=\"80\" value=\"$intvalue\"$options /></div>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'radio':
            case 'radioh':
                //"radio|title|VAR|required|options|value1=text|value2=text"
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field4)? " $this->Field4" : '';
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $value = $this->GetPostItem($this->Field2);
                $formitem = '';

                $radio_error =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($value==''));
                $start_span = ($radio_error)? '<span class="formitemerror" style="border:none;">' : '';
                $end_span = ($radio_error)? '</span>' : '';

                for($i=5; $i<count($this->Fields); $i++) {
                    $itemvalue = trim(strTo($this->Fields[$i], '='));
                    $itemtext  = trim(strFrom($this->Fields[$i], '='));
                    if (empty($itemtext)) {
                        $itemtext = $itemvalue;
                    }

                    $checked =($itemvalue == $value)? ' checked="checked"' : '';                    
                    
                    if ($this->Kind == 'radio') {
                        $BRK = ($i<count($this->Fields)-1)? '<br />' : '';
                    } else {
                        $start_span .= '<span style="white-space:nowrap;">';
                        $end_span   .= '</span>';
                        $BRK = ($i<count($this->Fields)-1)? '&nbsp; ' : '';
                    }                    
                    
                    $idsuffix = '_'.($i-4);

                    $formitem .= "$start_span<input type=\"radio\" id=\"$this->Field2$idsuffix\" class=\"radio_formitem\" name=\"$this->Form_Prefix$this->Field2\" value=\"$itemvalue\"$checked$options />&nbsp;$itemtext$end_span$BRK\n";
                }
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'select':
                //"select|title|VAR|required|options|value1=text|value2=text"
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field4)? " $this->Field4" : '';
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);

                $value = $this->GetPostItem($this->Field2);
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($value==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem  =  "<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\"$options>\n";

                $optionstart = 5;
                if ($this->Field5 == 'N')  $optionstart = 6;
                else  $formitem .= $this->Select_Start;

                for($i=$optionstart; $i<count($this->Fields); $i++) {
                    $option = strTo($this->Fields[$i], '::');
                    if ($option !='') {
                        $option_options = strFrom($this->Fields[$i], '::');
                        if ($option_options) {
                            $option_options = " $option_options";
                        }

                        $itemvalue = trim(strTo($option, '='));
                        $itemtext  = trim(strFrom($option, '='));
                        if (empty($itemtext)) {
                            $itemtext = $itemvalue;
                        }

                        $selected =($itemvalue == $value)? ' selected="selected"' : '';
                        $formitem .= "<option value=\"$itemvalue\"$selected$option_options>$itemtext</option>\n";
                    }
                }
                $formitem .=  "</select>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'selectcount':
                //"selectcount|title|VAR|required|start|end|options|mask"
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $options = ($this->Field6)? " $this->Field6" : '';
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $value = $this->GetPostItem($this->Field2);
                $class =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($value==$this->Form_Var['start_select_value']))? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem  =  "<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\"$options>\n";


                if (substr($this->Field4,0,1) == 'N') $this->Field4=substr($this->Field4,1);
                else  $formitem .= $this->Select_Start;

                for($i=$this->Field4; $i<=$this->Field5; $i++) {
                    $selected = ($value == $i)? ' selected="selected"' : '';
                    $formitem .=  "<option value=\"$i\"$selected>$i</option>\n";
                }
                $formitem .=  "</select>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'selecttext':
                //"selecttext|title|VAR|required|size|maxlength|mask|value1|value2 . . ."
                $this->Field1a = $this->Field1;
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);

                $value = $this->GetPostItem($this->Field2);
                $value2 = $this->GetPostItem('new_' . $this->Field2);
                $id2 = $this->GetIdFromVar('new_' . $this->Field2);
                $options = "onchange=\"if (this.value == '{$this->Form_Var['new_select_text_value']}') {
                  document.getElementById('$id2').style.display = '';
                  } else document.getElementById('$id2').style.display = 'none';\"";

                $class =  (
                    $this->RequiredCheck($this->Field3, $process) and ($process) and
                    (($value==$this->Form_Var['start_select_value']) or
                     (($this->Form_Var['new_select_text_value'] == $value) and (empty($value2))))
                    )? 'formitemerror' : 'formitem';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem  =  "<select id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\" $options>\n";

                if ($value==$this->Form_Var['start_select_value']) {
                    $value = '';
                }

                $optionstart = 7;
                if ($this->Field6 == 'N')  $optionstart = 8;
                else  $formitem .= $this->Select_Start;

                $have_option = false;
                for($i=$optionstart; $i<count($this->Fields); $i++) {
                    $itemtext = trim($this->Fields[$i]);
                    if ($itemtext == $value) {
                        $selected = ' selected="selected"';
                        $have_option = true;
                    } else {
                        $selected = '';
                    }
                    $selected =($itemtext == $value)? ' selected="selected"' : '';
                    if (!empty($itemtext)) $formitem .= "<option value=\"$itemtext\"$selected>$itemtext</option>\n";
                }

                if ($value2 and !$have_option) {
                    $selected = ' selected="selected"';
                } else {
                    if (!$have_option and $value) {
                        // must be posted value
                        $selected = ' selected="selected"';
                        $value2 = ($this->Form_Var['new_select_text_value'] != $value)? $value : '';
                    } else {
                        $selected = ($this->Form_Var['new_select_text_value'] == $value)? ' selected="selected"' : '';
                    }
                }

                $style  = ($selected)? 'inline' : 'none';
                $formitem .=  "<option value=\"{$this->Form_Var['new_select_text_value']}\" $selected>{$this->Form_Var['new_select_text']}</option>\n</select>\n";

                $maxlen = ($this->Field5)? ' maxlength="' .  $this->GetMax($this->Field5) . '"' : '';

                $id = $this->GetIdFromVar('new_' . $this->Field2);
                $formitem .=  "<span id=\"$id\" style=\"display:$style;\">&nbsp;{$this->Form_Var['new']} ($this->Field1a): <input class=\"$class\" name=\"{$this->Form_Prefix}new_$this->Field2\" value=\"$value2\" type=\"text\" size=\"$this->Field4\"$maxlen /></span>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'checkboxlist':
              //"checkboxlist|title|options|value1=text|value2=text"
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $options = ($this->Field2)? " $this->Field2" : '';
                $formitem = '';
                for($i=3; $i<count($this->Fields); $i++) {

                    $itemvalue = trim(strTo($this->Fields[$i], '='));
                    $itemtext  = trim(strFrom($this->Fields[$i], '='));
                    if (empty($itemtext)) {
                        $itemtext = $itemvalue;
                    }

                    $itemname = preg_replace('/[^a-zA-Z0-9]/', '_', $itemtext);
                    $value = $this->GetPostItem($itemname);
                    $checked =($value)? ' checked="checked"' : '';
                    $BRK = ($i<count($this->Fields)-1)? '<br />' : '';
                    $id = $this->GetIdFromVar($itemname);
                    $formitem .= "<input type=\"checkbox\" id=\"$id\" class=\"formitem_checkbox\" name=\"$this->Form_Prefix$itemname\" value=\"$itemvalue\"$checked$options /> $itemtext$BRK\n";
                }
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'checkboxlistbar':
            case 'checkboxlistbarh':
                //"checkboxlistbar|title|var|Y|options|value1=text|value2=text"
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $options = ($this->Field4)? " $this->Field4" : '';
                $formitem = '';
                $value = $this->GetPostItem($this->Field2);
                $stripped_value = preg_replace('/|/', '', $value);
                $missing_error =  ($this->RequiredCheck($this->Field3, $process) and ($process) and ($stripped_value==''));
                $start_span = ($missing_error)? '<span class="formitemerror" style="border:none;">' : '';
                $end_span = ($missing_error)? '</span>' : '';

                if (!empty($value)) {
                    $values = explode('|', $value);
                } else {
                    // else get values from post
                    $values = array();
                    for($i=5; $i<count($this->Fields); $i++) {
                        $count = $i-5;
                        $itemname = "$this->Field2$count";
                        $values[$count] = $this->GetPostItem($itemname);
                    }
                }
                for($i=5; $i<count($this->Fields); $i++) {

                    $itemvalue = trim(strTo($this->Fields[$i], '='));
                    $itemtext  = trim(strFrom($this->Fields[$i], '='));
                    if (empty($itemtext)) {
                        $itemtext = $itemvalue;
                    }

                    $count = $i-5;
                    $itemname = "$this->Field2$count";
                    $checked = !empty($values[$count])? ' checked="checked"' : '';
                    $break_type = ($this->Kind == 'checkboxlistbar')? '<br />' : '&nbsp;&nbsp;';
                    $BRK = ($i<count($this->Fields)-1)? $break_type : '';
                    $id = $this->GetIdFromVar($itemname);
                    $formitem .= "<input type=\"checkbox\" id=\"$id\" class=\"formitem_checkbox\" name=\"$this->Form_Prefix$itemname\" value=\"$itemvalue\"$checked$options />&nbsp;$itemtext$BRK\n";
                }
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'checkboxlistset':
            case 'checkboxlistseth':
                //"checkboxlistset|title|var|Y|options|value1=text|value2=text"
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $options = ($this->Field4)? " $this->Field4" : '';
                $formitem = '';
                $value = $this->GetPostItem($this->Field2);

                // $value = preg_replace('/,\s+/', ',', $value); // remove space after commas
                // $value = preg_replace('/,+/', ',', $value);   // remove double commas
                // $value = preg_replace('[(^,)|(, $)]', '', $value);  // remove beginning and end commas

                $values_in = explode(',', $value);
                TrimArray($values_in);
                $missing_error = ($this->RequiredCheck($this->Field3, $process) and ($process) and ($value==''));
                $start_span = ($missing_error)? '<span class="formitemerror" style="border:none;">' : '';
                $end_span   = ($missing_error)? '</span>' : '';

                // else get values from post
                $values = array();
                for($i=5, $c=count($this->Fields); $i < $c; $i++) {
                    $count = $i-5;
                    $itemname = "$this->Field2$count";
                    if ($value) {
                        $var = trim(strTo($this->Fields[$i], '='));
                        $values[$count]  = in_array($var, $values_in);
                    } else {
                        $values[$count] = $this->GetPostItem($itemname);
                    }
                }

                for($i=5, $c=count($this->Fields); $i < $c; $i++) {

                    $itemvalue = trim(strTo($this->Fields[$i], '='));
                    $itemtext  = trim(strFrom($this->Fields[$i], '='));
                    if (empty($itemtext)) {
                        $itemtext = $itemvalue;
                    }

                    $count = $i-5;
                    $itemname = "$this->Field2$count";
                    $checked = !empty($values[$count])? ' checked="checked"' : '';
                    $break_type = ($this->Kind == 'checkboxlistset')? '<br />' : '&nbsp;&nbsp;';
                    $BRK = ($i<count($this->Fields)-1)? $break_type : '';
                    $id = $this->GetIdFromVar($itemname);
                    $formitem .= "<input type=\"checkbox\" id=\"$id\" class=\"formitem_checkbox\" name=\"$this->Form_Prefix$itemname\" value=\"$itemvalue\"$checked$options />&nbsp;$itemtext$BRK\n";
                }
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'checkbox':
                //"checkbox|title|varname|options|value|value-null|aftertext"
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                $options = ($this->Field3)? " $this->Field3" : '';
                $formitem = '';
                $value = $this->GetPostItem($this->Field2);
                $checked = ($value==$this->Field4)? ' checked="checked"' : '';
                $id = $this->GetIdFromVar($this->Field2);
                $formitem .= "<input type=\"checkbox\" id=\"$id\" class=\"formitem_checkbox\" name=\"$this->Form_Prefix$this->Field2\" value=\"$this->Field4\"$checked$options />$this->Field6\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;

            case 'password':
                //"password|title|VARNAME|required|size|maxlength|options|mask|show_text",
                $this->Field1 = $this->RequiredCheck($this->Field3, $process)? "{$this->Form_Var['required_text']}$this->Field1" : $this->Field1;
                $RESULT .= $this->GetTitleCode($this->Field1, $this->Title_Template);
                if (strpos($this->Field6, 'SECURE') !== false) {
                    $this->Field6 = trim(str_replace('SECURE', '', $this->Field6));
                }
                $options = ($this->Field6)? " $this->Field6" : '';
                $value  = $this->GetPostItem($this->Field2);
                $show_text = ($this->Field8)? $this->Field8 : $this->Form_Var['show_password'];
                $class  =  ($this->RequiredCheck($this->Field3, $process) and ($process) and (!$value))? 'formitemerror' : 'formitem';
                $maxlen = ($this->Field5)? ' maxlength="' .  $this->GetMax($this->Field5) . '"' : '';

                $id = $this->GetIdFromVar($this->Field2);
                $this->Form_Javascript .= "var My$this->Field2 = '<input type=\"@@TYPE@@\" id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\" size=\"$this->Field4\" $maxlen value=\"@@VALUE@@\"$options />';\n";

                $formitem = "<span id=\"span_$id\">
        <input type=\"password\" alt=\"$this->Alt_Value\" id=\"$id\" class=\"$class\" name=\"$this->Form_Prefix$this->Field2\" size=\"$this->Field4\"$maxlen value=\"$value\"$options /></span>
            <br /><input type=\"checkbox\" value=\"1\" onclick=\"myElem  = document.getElementById('span_$id');
            myValue = document.getElementById('$id').value;
            var myInput = (this.value== '1')? My$this->Field2.replace('@@TYPE@@', 'text'): My$this->Field2.replace('@@TYPE@@', 'password');
            this.value  = (this.value== '1')? 0 : 1;
            myElem.innerHTML= myInput.replace('@@VALUE@@',myValue);\"/>
            <span class=\"withinformtext\">&nbsp;$show_text</span>\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'submit':
                //"submit|text|VAR|options"
                $class   = strIn($this->Field3, 'class=')? '' : 'class="formsubmit"';
                $onclick = strIn($this->Field3, 'onclick=')? '' : "onclick=\"this.value='{$this->Form_Var['submit_click_text']}';\"";
                $id = $this->GetIdFromVar($this->Field2);
                $formitem = "<input type=\"submit\" $class id=\"$id\"  name=\"$this->Field2\" value=\"$this->Field1\" $this->Field3 $onclick />\n";
                $RESULT .= $this->GetInfoCode($formitem, $this->Info_Template);
                break;


            case 'endform':
                //"endform"
                $RESULT .= "</form>\n";
                break;
            }

        }
        $js_functions = '';
        foreach ($this->Javascript_Functions as $key => $value) {
            $js_functions .= "$value\n";
        }
        $this->Form_Javascript = $js_functions . $this->Form_Javascript;
        if ($this->Form_Javascript) $RESULT .= JavaScriptString($this->Form_Javascript);
        return $RESULT;
    }

    public function GetAutocompleteValue($url, $value)
    {
        global $ROOT;
        $evalue = urlencode($value);
        $url = (strpos($url, '?') !== false)? "$url&v=$evalue" : "$url?v=$evalue";
        $url_file = $ROOT . strTo($url, '?');
        $RESULT = '';
        if (file_exists($url_file)) {
            $FORM_AUTOCOMPLETE_PROCESSING = 1;
            $FORM_AUTOCOMPLETE_PARAMETERS = strFrom($url, '?');
            $HOLD_GET = $_GET;
            include ($url_file);
            $_GET = $HOLD_GET;
            $RESULT = $FORM_AUTOCOMPLETE_RESULT;
        }
        return $RESULT;
    }


}