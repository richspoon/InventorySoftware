<?php

// file: /Lib/Pdo.php

class Lib_Pdo
{
    public $Pdo;
    public $Db_Query                        = '';
    public $Db_Want_Query                   = false;
    public $Db_Max_Db_Query_Length          = 1000000;
    public $Db_Start_Time                   = 0;
    public $Db_Show_Trace                   = false;
    public $Db_Last_Query                   = '';

    public $Db_Query_Table_Options          = 'align="center" style="background-color:#888; border:1px solid #000; margin-top:10px;"';
    public $Db_Query_Table_Options_Th       = 'style="text-align:center; background-color:#aaa; color:#fff;"';
    public $Db_Query_Table_Options_Td       = 'style="text-align:left; background-color:#ff7; color:#000; font-size:0.8em; padding:1em 1em 0px 0px;"';

    public $Last_Insert_Id                  = 0;
    public $Affected_Rows                   = 0;
    public $Number_Rows                     = 0;
    public $Field_Values                    = '';

    public $Error                           = '';
    public $Database_Type                   = '';
    public $Calling_Classname               = '';
    
    public $Classname;
    
    public function  __construct($type= '', $connection_info = '')
    {
        $this->Db_Start_Time = microtime(true);

        if ($connection_info) {
            if ($type == 'MYSQL') {
                $this->ConnectMySql($connection_info);
            } elseif ($type = 'SQLITE') {
                $this->ConnectSqLite($path);
            } else {
                $this->MText('ERROR', 'DB Connection Type Not Defined!');
            }
            $this->Connect($connection_info);
        }
        
        $this->Classname = get_class($this);
        $this->ClassInfo = array(
            'Created By'    => 'Michael Petrovich',
            'Created Date'  => '2010-10-01',
            'Updated By'    => 'Richard Witherspoon',
            'Updated Date'  => '2013-11-18',
            'Filename'      => $this->Classname,
            'Version'       => '2.1',
            'Description'   => 'PDO library for database connection',
            'Update Log'    => array(
                '2013-05-01_2.0'    => "Minor modifications",
                '2013-11-18_2.1'    => "Modified SetDbQuery() so it will always store the query -- needed to make export work"
            ),
        );
    }

    public function MText($title, $message)
    {
        print <<<MTL
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>$title</title></head><body style="background-color:#006;">
<table align="center" style="background-color:#fff; color:#000; border:2px solid #f00; padding:1em; margin-top:20px;">
<tbody><tr><td align="center">$message</td></tr></table></tbody></body></html>
MTL;
        exit;
    }

    public function Connect($connection_string, $user='', $pass='')
    {
        if ($connection_string) {
            try {
                $this->Pdo = new PDO($connection_string, $user, $pass);
                $this->Pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                if ($this->Database_Type != 'SQLITE') {
                    $this->Pdo->exec('SET CHARACTER SET utf8');
                }
            } catch(PDOException $e) {
                $this->Mtext('DB ERROR', $e->getMessage());
            }
        }
    }

    public function ConnectMySql($DB_INFO)
    {
        $this->Database_Type = 'MYSQL';
        if ($DB_INFO) {
            $connection_string = "mysql:host={$DB_INFO['HOST']};dbname={$DB_INFO['NAME']}";
            $this->Connect($connection_string, $DB_INFO['USER'], $DB_INFO['PASS']);
            //$this->Pdo->setAttribute(PDO::ATTR_DB_NAME, "{$DB_INFO['NAME']}");
        }
    }


    public function ConnectSqLite($path)
    {
        $this->Database_Type = 'SQLITE';
        if ($path) {
            $connection_string = 'sqlite:' . $path;
            $this->Connect($connection_string);
        }
    }

    public function SetWantQuery($value)
    {
        $this->Db_Want_Query = $value;
    }

    public function SetTrace($value)
    {
        $this->Db_Show_Trace = $value;
    }

    public function GetLastQuery()
    {
        return $this->Db_Last_Query;
    }

    public function GetAffectedRows()
    {
        return $this->Affected_Rows;
    }

    public function SetDbQuery($function, $query)
    {
        static $max_reached = false;
        static $trace_count = 0;

        //if (!$function or (!$this->Db_Want_Query && !Session('WANT_DB_QUERIES')) or $max_reached) return;
        if (!$function or $max_reached) return;


        $this->Db_Last_Query = $query;
        $query = str_replace(',', ', ', $query);

        $query = htmlspecialchars(trim($query), ENT_COMPAT, 'UTF-8');

        $span1 = '<span style="color:#f00; font-weight:bold;">';
        $span1g = '<span style="color:#0a0; font-weight:bold;">';
        $span2 = '</span>';

        $query = preg_replace('/\b([a-zA-Z_]+\()/', "$span1g\$1$span2", $query);
        $query = preg_replace('/\b(UPDATE|INSERT|INTO|SET|OFFSET|LEFT|RIGHT|JOIN|AS|WHERE|LIMIT|SELECT|AND|OR|ON|FROM|DELETE|GROUP BY|ORDER BY|START TRANSACTION|COMMIT|ROLLBACK)\b/i', "$span1\$1$span2", $query);
        $query = preg_replace('/\b(SET|LEFT|RIGHT|WHERE|LIMIT|ON|FROM|VALUES|GROUP|ORDER)\b/i', '<br />$1', $query);
        $query = str_replace(')', "$span1g)$span2", $query);
        $query = str_replace('(', "$span1g($span2", $query);

        if (!empty($query)) {
            $query = "<b>$function:</b> $query";

            //-------------- tracing -------------
            $error = $this->Error;

            if ($this->Db_Show_Trace) {
                $trace = debug_backtrace();
                $trace_count++;
                $trace_output = '<ul style="text-align:left;">';
                foreach($trace as $entry){
                    if ($entry['function'] != 'SetDbQuery') {
                        $trace_output .= "<li><b>File:</b> {$entry['file']} (Line: {$entry['line']})<br />\n";
                        $trace_output .= (!empty($entry['class']))? "<b>Class:</b> {$entry['class']}<br />" : '';
                        $trace_output .= "<b>Function:</b> {$entry['function']}<br />\n";
                        $args = ArrayToStr($entry['args']);
                        if ($args) {
                            $trace_output .= "<b>Args:</b> $args\n";
                        }
                        $trace_output .= "</li>\n";
                    }
                }
                $trace_output .= '</ul>';
            } else {
                $trace_output = '';
            }


            if (!empty($error)) {
                $error = nl2br($error);
                //$error = "<br /><div style=\"background-color:#f00;color:#fff;padding:0px 3px;\">$error</div>";
                $error = StrFrom($error, ': ');
                $error = str_replace('Stack trace:', '<br /><b>Stack trace:</b>', $error);
                $span_start = 'syntax to use near <b>';
                $span_end   = '</b> at line ';
                $error = preg_replace('/syntax to use near ((.|\n)+) at line /', "$span_start$1$span_end", $error);
                $error = "<br /><div style=\"border:1px solid #f00; background-color:#f66;color:#000;padding:3px;\">$error</div>";
                if (function_exists('AddError')) {
                    AddError('Database Error - ' . $this->Calling_Classname . ' - ' . $function);
                }
            }

            $RESULT  = '';
            if ("$error$query" != '') {
                $RESULT .= "<li style=\"padding:5px;\">$query$error\n";
            }
            $RESULT .= '<ul>';
            if ($trace_output) {
                $RESULT .= '<li id="dbquerytrace_' . $trace_count .'" style="text-align:left; margin-left:2em; display:none; padding:5px;"><b>TRACE</b>' . $trace_output . "</li>\n";
            }
            $RESULT .= '<li style="padding:5px;"><b>Elapsed Time:</b> ' . number_format(microtime(true) - $this->Db_Start_Time, 3) . "</li>\n";
            $RESULT .= "</ul></li>\n";
            if (strlen($this->Db_Query) + strlen($RESULT) > $this->Db_Max_Db_Query_Length) {
                $max_reached = true;
                $amount = number_format($this->Db_Max_Db_Query_Length);
                $this->Db_Query .= '<h3 style="color:#f00; text-align:center;">. . . Maximum Length of Query Text Reached (' . $amount . ')</h3>';

            } else {
                $this->Db_Query .= $RESULT;
            }
        }
    }

    public function WriteDbQuery()
    {
        echo $this->WriteDbQueryText();
    }

    public function WriteDbQueryText()
    {
        $RESULT = '';
        $id = 'db_query_results' . date('His') . rand(100,999);
        if (!empty($this->Db_Query)) {
            $RESULT .= "\n\n<table id=\"$id\" $this->Db_Query_Table_Options>\n";
            $RESULT .= '<tbody>';
            $RESULT .= '<tr>
            <th ' . $this->Db_Query_Table_Options_Th . '>DB Queries';
            if ($this->Db_Show_Trace) {
                $RESULT .= "
                    <a href=\"#\" style=\"text-decoration:none; font-size:0.8em;\"
                    onclick=\"var i=1;
                        var dbTraceState = document.getElementById('dbquerytrace_1').style.display;
                        var traceDisplay = (dbTraceState == 'none')? '' : 'none';
                        while (document.getElementById('dbquerytrace_'+i)) {
                            document.getElementById('dbquerytrace_'+i).style.display=traceDisplay;  i++;
                        }
                        return false;\"> (Trace)</a>";
            }
            $RESULT .= '</th>
            <th style="width:1em; text-align:center;">
            <a href="#" style="text-decoration:none;"
                onclick="getElementById(\''. $id . '\').style.display=\'none\'; return false;">X</a>
            </th></tr>';
            $RESULT .= "\n<tr><td colspan=\"2\" $this->Db_Query_Table_Options_Td>\n<ol style=\"margin:0px 2em;\">$this->Db_Query</ol>\n</td></tr>\n";
            $RESULT .= '</tbody>';
            $RESULT .= "</table>\n";
        }
        return $RESULT;
    }


    public function Exec($function, $query)
    {
        $this->Error = '';
        try {
            $RESULT = $this->Pdo->exec($query);
            $this->Affected_Rows = $RESULT;
        } catch(PDOException $e) {
            $this->Error = $e->getMessage();
            $RESULT = false;
        }
        $this->SetDbQuery($function, $query);
        return $RESULT;
    }

    public function Prepare($function, $query)
    {
        $this->Error = '';
        try {
            $RESULT = $this->Pdo->prepare($query);
        } catch(PDOException $e) {
            $this->Error = $e->getMessage();
            $RESULT = false;
        }
        $this->SetDbQuery($function, $query);
        return $RESULT;
    }

    public function Query($function, $query, $method = PDO::FETCH_ASSOC)
    {
        $this->Error = '';
        try {
            $RESULT = $this->Pdo->query($query, $method);
        } catch(PDOException $e) {
            $this->Error = $e->getMessage();
            $RESULT = false;
        }
        $this->SetDbQuery($function, $query);
        return $RESULT;
    }
    

    public function CreateSqLiteTable($table, $varlist)
    {
        $query = 'CREATE TABLE ' . $table . '(' . $varlist . ')';
        $this->Exec('CREATE TABLE', $query);
    }


    public function QuoteTables($tables)
    {
        $RESULT = preg_replace('/\b([a-zA-Z_0-9\-]+)\b/', '`$1`', $tables);
        $RESULT = str_replace('``','`', $RESULT);
        $RESULT = str_ireplace(' `AS` ', ' AS ', $RESULT);
        return $RESULT;
    }

    public function QuoteValue($value)
    {
        $value = trim($value);
        if (($value == 'NOW()') or ($value == '0') or (is_numeric($value) and (strlen($value)<12) and (substr($value, 0, 1)!='0')))  return $value;
        elseif ((strpos($value, "'")!==false) and (strpos($value, '"')===false)) return "\"$value\"";
        elseif (strpos($value, "'")===false)  return "'$value'";
        else return $this->Pdo->quote($value);
    }

    public function QuoteValueC($value)
    {
        return $this->QuoteValue($value) . ',';
    }

    public function QuoteKey($key)
    {
        //$key = str_replace('.', '`.`', $key);
        $key = preg_replace('/\b([a-zA-Z_0-9\-]+)\.([a-zA-Z_0-9\-]+)\b/', '`$1`.`$2`', $key);
        $key = preg_replace('/ (AS|as) ([a-zA-Z_0-9\-]+)\b/', '` AS `$2`', $key);
        //$key = str_ireplace(' AS ', '` AS `', $key);
        $key = str_replace('``','`', $key);
        $key = str_replace(')`', ')', $key);
        $key = "`$key`";
        $key = str_replace('``','`', $key);
        $key = preg_replace('/`([a-zA-Z\_]+)\(/', '\1(', $key);
        $key = str_replace(')`',')', $key);
        $key = str_replace('`(','(', $key);
        return $key;
    }



    public function KeyValues($array)
    {
        $RESULT = '';
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                $key = $this->QuoteKey($key);
                $RESULT .= "$key=". $this->QuoteValueC($value);
            }
            $RESULT = substr($RESULT,0,-1);
        }
        return $RESULT;
    }

    // takes keys from an array or associative array and returns a key list.
    public function Keys($array, $assoc=true)
    {
        $RESULT = '';
        if (!empty($array)) {
            if ($assoc) {
                $keys = array_keys($array);
            } else {
                $keys = $array;
            }
            foreach ($keys as $key) {
                $key = $this->QuoteKey($key) . ',';
                $RESULT .= $key;
            }
            $RESULT = substr($RESULT,0,-1);
        }
        return $RESULT;
    }

    public function Values($array)
    {   // this function works on standard and associative arrays
        $RESULT = '';
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                $RESULT .= $this->QuoteValueC($value);
            }
            $RESULT = substr($RESULT,0,-1);
        }
        return $RESULT;
    }


    public function OutputTable($search_array, $field_titles='', $table_setup='', $edit_title='', $edit_links='', $id='')
    {
        if (empty($search_array) or  !is_array($search_array)) return '';

        $RESULT = "<table $table_setup>\n";
        $RESULT .= "<tbody>\n<tr><th>No.</th>";
        $wantedit = (!empty($id) and !empty($edit_links));
        if (empty($field_titles)) {
            $field_titles = array();
            foreach ($search_array[0] as $key=>$value) {
                $field_titles[$key] = NameToTitle($key);
            }
        }

        foreach ($field_titles as $key => $value) {
            if (array_key_exists($key, $search_array[0])) {
                $RESULT .= "<th>{$value}</th>";
            }
        }
        if ($wantedit) $RESULT .= "<th>$edit_title</th>";

        $RESULT .= "</tr>\n";
        $evenodd = 2;
        $count = 0;
        foreach ($search_array as $row) {
            $count++;
            $evenodd = 3 - $evenodd;
            $class = ($evenodd == 1) ? 'odd' : 'even';
            $RESULT .= "<tr class=\"$class\"><td align=\"right\">$count.</td>";
            foreach ($field_titles as $key => $value) {
                if (isset($row[$key])) {
                    $field = $row[$key];
                    if (!empty($this->Field_Values)) {
                        $outvalue = (empty($this->Field_Values[$key][$field]))? $field : $this->Field_Values[$key][$field];
                    } else {
                        $outvalue = $field;
                    }
                    $RESULT .= "<td>$outvalue</td>";
                } else {
                    $RESULT .= "<td></td>";  // null value
                }
            }
            if ($wantedit) $RESULT .= '<td>'. str_replace('@@ID@@', $row[$id], $edit_links) .'</td>';
            $RESULT .= "</tr>\n";
        }
        $RESULT .= "</tbody></table>\n";
        return $RESULT;
    }


    public function GetLastInsertId()
    {
        return $this->Last_Insert_Id;
    }

    public function GetLastNumberRows()
    {
        return $this->Number_Rows;
    }

    public function IsUnique($table, $key='', $value='', $exclude='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (empty($key) or empty($value)) {
            return false;
        }
        $excludestr = (!empty($exclude))? " AND $exclude" :'';

        $table = $this->QuoteTables($table);
        $key   = $this->QuoteKey($key);
        $value = $this->QuoteValue($value);

        // cannot compare number to string 1000='1000a' so use lower
        $query = "SELECT $key FROM $table WHERE LOWER($key)=LOWER($value)$excludestr LIMIT 1";

        $query_result = $this->Query('IsUnique', $query);

        if ($query_result) {
            $result = $query_result->fetchColumn();
            $query_result->closeCursor();
            return empty($result);
        } else {
            return true;
        }
    }

    public function GetUniqueID($table, $field) {
        $UID = md5(uniqid(rand(), true));
        while (!$this->IsUnique($table, $field, $UID)) {
            $UID = md5(uniqid(rand(), true));
        }
        return $UID;
    }


    public function GetNextValue($table, $key)
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (empty($key)){
            return '';
        }
        $table = $this->QuoteTables($table);
        $key = $this->QuoteKey($key);
        $query    = "SELECT MAX($key) + 1 AS maxkey FROM $table";
        $query_result = $this->Query('GetNextValue', $query, PDO::FETCH_NUM);

        if ($query_result) {
            $RESULT = $query_result->fetchColumn();
            $query_result->closeCursor();
            return $RESULT;
        } else {
            return 0;
        }
    }

    public function GetNextDate($table, $key, $format, $inc)
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (empty($key)) {
            return '';
        }

        $table = $this->QuoteTables($table);

        $query    = "SELECT MAX($key) AS maxkey FROM $table";
        $query_result = $this->Query('GetNextDate', $query, PDO::FETCH_NUM);

        if ($query_result) {
            $result = $query_result->fetchColumn();
            $query_result->closeCursor();
            $maxdate  = DateToDashes($result);

            if (strlen($maxdate)<8) {
                $maxdate .= '-01';
            }
            return date($format, strtotime("$maxdate +1 $inc"));
        } else {
            return '';
        }
    }


    public function GetFieldValues($table, $key='', $where='', $case='', $joins='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        $RESULT = array();
        $case = strtoupper($case);

        if (!empty($where)) $where = "WHERE $where";

        if (empty($key)) {
            return $RESULT;
        }

        $table = $this->QuoteTables($table);

        $query    = "SELECT $key FROM $table $joins $where GROUP BY $key ORDER BY $key";
        $query_result = $this->Query('GetFieldValues', $query, PDO::FETCH_NUM);


        if (!$query_result) {
            return $RESULT;
        }

        while ($row = $query_result->fetch()) {
            $value = $row[0];
            if (!empty($value)) {
                if ($case == 'U') $value = strtoupper($value);
                elseif ($case == 'L') $value = strtolower($value);
                $RESULT[] = $value;
            }
        }
        $query_result->closeCursor();

        return $RESULT;
    }

    public function GetFieldValuesLC($table, $key='', $where='', $joins='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);
        return $this->GetFieldValues($table, $key, $where, 'L', $joins);
    }

    public function ViewRecord($table, $title_array='', $table_setup='', $keys='', $where='', $joins='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (empty($keys) or empty($where)) return '';

        $row = $this->GetRecord($table, $keys, $where, $joins);

        if (!empty($row)) {
            $RESULT = "<table $table_setup>\n";

            if (!empty($title_array)) {
                foreach ($title_array as $key => $value) {
                    $newkey = strFrom($key,'AS ');
                    if (empty($newkey)) $newkey = strFrom($key,'.');
                    if (empty($newkey)) $newkey = $key;
                    if (isset($row[$newkey])) {
                        $outvalue = (empty($this->Field_Values[$key][$value]))? $row[$newkey] : $this->Field_Values[$key][$row[$newkey]];
                        $RESULT .= "<tr><th align=\"right\">$value</th><td>$outvalue</td></tr>\n";
                    }
                }
            } else {
                foreach ($row as $key => $value) {
                    $outvalue = (empty($this->Field_Values[$key][$value]))? $value : $this->Field_Values[$key][$value];
                    $RESULT .= "<tr><th align=\"right\">$key</th><td>$outvalue</td></tr>\n";
                }
            }

            $RESULT .= "</table>\n";
        } else {
            $RESULT = '';
        }
        return $RESULT;
    }

    public function ViewRecordArray($record, $title_array, $table_setup='')
    {
        global $FieldValues;

        if (!empty($record)) {
            $RESULT = "<table $table_setup>\n";
            foreach ($record as $key => $value) {
                $outkey = (empty($title_array[$key]))? $key : $title_array[$key];
                $outvalue = (empty($this->Field_Values[$key][$value]))? $value : $this->Field_Values[$key][$value];
                $RESULT .= "<tr><th align=\"right\">$outkey</th><td>$outvalue</td></tr>\n";
            }
            $RESULT .= "</table>\n";
        } else {
            $RESULT = '';
        }
        return $RESULT;
    }


    public function GetRecord($table, $keys='', $where='', $joins='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (empty($keys) or empty($where)) {
            return '';
        }

        $table = $this->QuoteTables($table);

        $query = "SELECT $keys FROM $table $joins WHERE $where LIMIT 1";
        $query_result = $this->Query('GetRecord', $query);

        $RESULT = array();
        if ($query_result) {
            $RESULT = $query_result->fetch();
            $query_result->closeCursor();
        } else {
            $RESULT = 0;
        }
        return $RESULT;
    }

    public function UpdateRecord($table, $key_values='', $where='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (empty($key_values) or empty($where)) {
            return false;
        }
        $table = $this->QuoteTables($table);

        $query = "UPDATE $table SET $key_values WHERE $where";

        $RESULT = $this->Exec('UpdateRecord', $query);

        if ($RESULT !== false) {
            $this->Last_Insert_Id = $this->Pdo->lastInsertId();
            return 1;
        } else {
            $this->Last_Insert_Id = 0;
            return 0;
        }
    }

    public function DeleteRecord($table, $where='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (empty($where)) {
            return false;
        }

        $table = $this->QuoteTables($table);
        $query = "DELETE FROM $table WHERE $where";

        return $this->Exec('DeleteRecord', $query);
    }


    public function AddRecord($table, $keys='', $values='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (empty($keys) or empty($values)) {
            return false;
        }

        $table = $this->QuoteTables($table);
        $query = "INSERT INTO $table ($keys) VALUES ($values)";

        $RESULT = $this->Exec('AddRecord', $query);

        if ($RESULT) {
            $this->Last_Insert_Id = $this->Pdo->lastInsertId();
            return $RESULT;
        } else {
            $this->Last_Insert_Id = 0;
        }
        return 0;

    }


    public function GetArray($table, $keys='', $where='', $order='', $start_list='', $list_size='', $joins='', $get_count=true)
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        // joins need to be provide in full SQL
        if ($keys == '') {
            return '';
        }

        $start_list = intOnly($start_list);
        $list_size  = intOnly($list_size);

        if (empty($order) or ($order == 'none')) {
            $order = '';
        } else {
            $order = "ORDER BY $order";
        }

        if (!empty($where)) {
            $where = "WHERE $where";
        }

        $table = $this->QuoteTables($table);

        // --------- get row count ---------
        if ($get_count) {
            $query = "SELECT COUNT(*) FROM $table $joins $where";
            $query_result = $this->Query('GetArray Count', $query, PDO::FETCH_NUM);

            if ($query_result) {
                $this->Number_Rows = $query_result->fetchColumn();
                $query_result->closeCursor();
                $query_result = null;
                if ($start_list < 0) $start_list = $this->Number_Rows + $start_list;  // gets last rows (-100, would get last 100 rows)
            } else {
                $this->Number_Rows = 0;
            }
        } else {
            $this->Number_Rows = 0;
        }


        if ((empty($start_list)) and (empty($list_size))) {
            $LIMIT = '';
        } else {
            $LIMIT = "LIMIT $start_list, $list_size";
        }

        $query = "SELECT $keys FROM $table $joins $where $order $LIMIT";
        $query_result  = $this->Query('GetArray', $query);

        $RESULT = array();
        if ($query_result) {
            $RESULT = $query_result->fetchAll();
            $query_result->closeCursor();
        }
        return $RESULT;
    }


    public function GetArrayAll($table, $keys='*', $where='', $order='', $joins='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if ($keys == '') {
            return '';
        }

        $order = (empty($order) or ($order == 'none'))? '' : "ORDER BY $order";

        if (!empty($where)) {
            $where = "WHERE $where";
        }

        $table = $this->QuoteTables($table);

        $query = "SELECT $keys FROM $table $joins $where $order";
        $query_result = $this->Query('GetArrayAll', $query);

        $RESULT = array();
        if ($query_result) {
            $RESULT = $query_result->fetchAll();
            $query_result->closeCursor();
        }
        return $RESULT;
    }

    public function GetArrayAssoc($table, $reference_key='', $keys='*', $where='', $order='', $joins='', $limit='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (!empty($order)) $order = "ORDER BY $order";

        if (!empty($where)) {
            $where = "WHERE $where";
        }

        if (!empty($limit)) {
            $limit = " LIMIT $limit";
        }

        $table = $this->QuoteTables($table);

        $quote_ref = $this->QuoteKey($reference_key);

        $query = "SELECT $keys, $quote_ref AS 'THE_REFERENCE_KEY' FROM $table $joins $where $order $limit";

        $query_result = $this->Query("GetArrayAssoc ($reference_key)", $query);

        $RESULT = array();
        if ($query_result) {
            while ($row = $query_result->fetch()) {
                if (!empty($row['THE_REFERENCE_KEY']) or ($row['THE_REFERENCE_KEY'] == 0)) {
                    $id = $row['THE_REFERENCE_KEY'];
                    unset($row['THE_REFERENCE_KEY']);
                    $RESULT[$id] = $row;
                }
            }
            $query_result->closeCursor();
        }
        return $RESULT;
    }


    // -------------------- function to return an Associative Array from two fields in a Table ---------------------------
    public function GetAssocArray($table, $key='', $value='', $where='', $key_case='', $joins='', $order='', $limit='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        $RESULT = array();
        $key_case = strtoupper($key_case);

        if (($key == '') or ($value == '')) {
            return $RESULT;
        }

        if (!empty($where)) {
            $where = "WHERE $where";
        }

        if (!empty($limit)) {
            $limit = " LIMIT $limit";
        }

        $key   = $this->QuoteKey($key);
        $value = $this->QuoteKey($value);
        if (empty($order)) $order = $key;

        $table = $this->QuoteTables($table);

        $query = "SELECT DISTINCT($key), $value FROM $table $joins $where ORDER BY $order$limit";

        $query_result = $this->Query("GetAssocArray ($key)", $query, PDO::FETCH_NUM);

        $RESULT = array();
        if ($query_result) {
            while ($row = $query_result->fetch()) {
                $idx = $row[0];
                if (!empty($idx) or ($idx == 0)) {
                    if ($key_case == 'U') $idx = strtoupper($idx);
                    elseif ($key_case == 'L') $idx = strtolower($idx);
                    $RESULT[$idx] = $row[1];
                }
            }
            $query_result->closeCursor();
        }
        return $RESULT;
    }


    public function Count($table, $where='', $joins='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (!empty($where)) $where = " WHERE $where";

        $table = $this->QuoteTables($table);

        $query = "SELECT COUNT(*) FROM $table $joins $where";

        $query_result = $this->Query('Count', $query, PDO::FETCH_NUM);

        if ($query_result) {
            $RESULT = $query_result->fetchColumn();
            $query_result->closeCursor();
        } else {
            $RESULT = 0;
        }
        return $RESULT;
    }


    public function GetFreq($table, $key='', $order=2, $order_direction='', $where='', $joins='', $limit='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if ($key == '') {
            return '';
        }

        if (!empty($limit)) {
            $limit = " LIMIT $limit";
        }

        $table = $this->QuoteTables($table);
        $key   = $this->QuoteKey(strTo($key, ' AS '));

        //$order = ($order == 1)? "ORDER BY $key " : 'ORDER BY THE_COUNT ';
        $order = ($order == 1)? "ORDER BY THE_KEY " : 'ORDER BY THE_COUNT ';
        $order .= $order_direction;
        if (!empty($where)) {
            $where = "WHERE $where";
        }


        $query = "SELECT $key AS THE_KEY, COUNT(*) AS THE_COUNT FROM $table $joins $where GROUP BY THE_KEY $order$limit";

        $query_result = $this->Query("GetFreq", $query, PDO::FETCH_NUM);

        $RESULT = array();
        if ($query_result) {
            while ($row = $query_result->fetch()) {
                $RESULT[$row[0]] = $row[1];
            }
            $query_result->closeCursor();
        }
        return $RESULT;
    }

    public function GetMax($table, $key='', $group='', $where='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if ($group == '') {
            return '';
        }

        if (!empty($where)) {
            $where = "WHERE $where";
        }

        $table    = $this->QuoteTables($table);
        $group    = $this->QuoteKey($group);
        $key      = $this->QuoteKey($key);

        $query = "SELECT $group, MAX($key) FROM $table $where GROUP BY $group";
        $query_result = $this->Query("GetMax", $query, PDO::FETCH_NUM);

        $RESULT = array();
        if ($query_result) {
            while ($row = $query_result->fetch()) {
                $RESULT[$row[0]] = $row[1];
            }
            $query_result->closeCursor();
        }
        return $RESULT;
    }

    public function MaxValue($table, $key='', $where='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (!empty($where)) {
            $where = "WHERE $where";
        }

        $table = $this->QuoteTables($table);
        $key = $this->QuoteKey($key);

        $query = "SELECT MAX($key) FROM $table $where";

        $query_result = $this->Query('MaxValue', $query);

        if ($query_result) {
            $RESULT = $query_result->fetchColumn();
            $query_result->closeCursor();
        } else {
            $RESULT = '';
        }
        return $RESULT;
    }


    public function FieldArray($table, $key='', $where='', $order='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if ($key == '') {
            return '';
        }

        if (!empty($order)) $order = "ORDER BY $order";
        if (!empty($where)) $where = "WHERE $where";

        $table = $this->QuoteTables($table);
        $qkey   = $this->QuoteKey($key);
        $query = "SELECT $qkey FROM $table $where $order";

        $query_result = $this->Query('FieldArray', $query, PDO::FETCH_NUM);

        $RESULT = array();
        if ($query_result) {
            while ($item = $query_result->fetchColumn()) {
                $RESULT[] = $item;
            }
            $query_result->closeCursor();
        }
        return $RESULT;
    }

    public function GetValue($table, $key='', $where='', $joins='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (empty($key) or empty($where)) {
            return '';
        }

        $table = $this->QuoteTables($table);
        $key   = $this->QuoteKey($key);

        $query = "SELECT $key FROM $table $joins WHERE $where LIMIT 1";
        $query_result = $this->Query('GetValue', $query, PDO::FETCH_NUM);

        if ($query_result) {
            $RESULT = $query_result->fetchColumn();
            $query_result->closeCursor();
        } else {
            $RESULT = '';
        }
        return $RESULT;
    }


    public function IncValue($table, $key='', $where='')
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (empty($key) or empty($where)) {
            return '';
        }

        $table = $this->QuoteTables($table);
        $key   = $this->QuoteKey($key);
        $query = "UPDATE $table SET $key=$key+1 WHERE $where LIMIT 1";

        $RESULT = $this->Exec('IncValue', $query);

        if ($RESULT !== false) {
            $this->Last_Insert_Id = $this->Pdo->lastInsertId();
            return 1;
        } else {
            $this->Last_Insert_Id = 0;
            return 0;
        }
    }

    public function AppendValue($table, $key='',  $value='', $where='', $delimiter='', $limit=1)
    {
        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        if (empty($key) or empty($where)) {
            return '';
        }

        $table = $this->QuoteTables($table);
        $key   = $this->QuoteKey($key);
        $value = $this->QuoteValue($value);

        if (!empty($limit)) {
            $limit = " LIMIT $limit";
        }

        if ($delimiter) {
            $delimiter = $this->QuoteValue($delimiter);
            $query = "UPDATE $table SET $key=CONCAT($key, IF($key != '' AND $key IS NOT NULL, $delimiter, ''), $value) WHERE $where $limit";
        } else {
            $query = "UPDATE $table SET $key=CONCAT($key, $value) WHERE $where $limit";
        }

        $RESULT = $this->Exec('AppendValue', $query);

        if ($RESULT !== false) {
            $this->Last_Insert_Id = $this->Pdo->lastInsertId();
            return 1;
        } else {
            $this->Last_Insert_Id = 0;
            return 0;
        }
    }



    public function GetTables($function_name = 'GetTables')
    {
        if ($this->Database_Type == 'SQLITE') {
            $query = "SELECT name FROM sqlite_master WHERE type='table'";
        } else {
            $query = 'SHOW TABLES';
        }

        $query_result = $this->Query($function_name, $query, PDO::FETCH_NUM);

        $RESULT = array();
        if ($query_result) {
            while ($row = $query_result->fetch()) {
                $RESULT[] = $row[0];
            }
            $query_result->closeCursor();
        }
        NatCaseSort($RESULT);
        return $RESULT;
    }
    
    

    public function TableExists($table)
    {
        $array = $this->GetTables("TableExists ($table)");
        return in_array($table, $array);
    }


    public function TableFieldInfo($table)
    {
        $table = $this->QuoteTables($table);

        if ($this->Database_Type == 'SQLITE') {
            $query = "PRAGMA table_info($table)";
        } else {
            $query = "SHOW COLUMNS FROM $table";
        }


        $query_result = $this->Query('TableFieldInfo', $query);
        $RESULT = array();
        if ($query_result) {
            while ($row = $query_result->fetch()) {
                $type        = ArrayValue($row, 'Type');
                if (empty($type)) {
                    $type    = ArrayValue($row, 'type');
                }
                $typeonly    = strTo($type,'(');
                $size        = TextBetween('(',')', $type);
                $row['Kind'] = $typeonly;
                $row['Size'] = $size;
                $RESULT[]    = $row;
            }
            $query_result->closeCursor();
        }
        return $RESULT;
    }

    public function TableFieldNames($table)
    {
        $RESULT = array();
        $tableinfo = $this->TableFieldInfo($table);
        if (count($tableinfo)>0) {
            foreach ($tableinfo as $row) {
                $RESULT[]=$row['Field'];
            }
        }
        return $RESULT;
    }

    public function TableFieldTitleNames($table)
    {
        $RESULT = array();
        $tableinfo = $this->TableFieldInfo($table);
        if (count($tableinfo)>0) {
            foreach ($tableinfo as $row) {
                $value = NameToTitle($row['Field']);
                $RESULT[$row['Field']]= $value;
            }
        }
        return $RESULT;
    }

    public function GetEnumArrays($table) {
        $RESULT = array();
        $table_info = $this->TableFieldInfo($table);
        foreach ($table_info as $row) {
            if ($row['Kind'] == 'enum') {
                $size = $row['Size'];
                $RESULT[$row['Field']] = TextBetweenArray("'","'", $row['Size']);
            }
        }
        return $RESULT;
    }

    public function CopyRow($table, $exclude_list='', $change_list='', $where='')
    {
        // $exculude_list is a comma delimited list
        // $change_list is a '|' delimited list  >> "var1=My Value|var2=My Other Value"  --- do not use quotes for values
        // $change_list can assign values from another field using '=='  >> "var1==myId"

        if (is_array($table)) extract($table, EXTR_OVERWRITE);

        $row = $this->GetRecord($table, '*', $where);
        if ($row) {
            $changes  = explode('|', $change_list);
            TrimArray($changes);
            foreach ($changes as $change) {
                $var   = trim(strTo($change,'='));
                $value = trim(strFrom($change, '='));
                if (substr($value, 0, 1) == '=') {
                    $field = trim(strFrom($value,'='));
                    $value = $row[$field];
                }
                $row[$var] = $value;
            }

            $excludes = explode(',', $exclude_list);
            TrimArray($excludes);
            foreach ($excludes as $field) unset($row[$field]);

            $this->AddRecord($table, $this->Keys($row), $this->Values($row));
            return $this->Last_Insert_Id;
        } else {
            return false;
        }
    }


    // function db_Query($QUERY)
    // {
        // $RESULT = @mysqli_query($DB_CONNECTION, $QUERY);
        // SetDbQuery('db_Query', $QUERY);
        // return $RESULT;
    // }

    // function db_FetchAssoc($db_query)
    // {
        // return mysqli_fetch_assoc($db_query);
    // }

    // function db_FetchRow($db_query)
    // {
        // return mysqli_fetch_row($db_query);
    // }

    public function QueryToArray($QUERY)
    {
        $RESULT = array();
        $query_result = $this->Query('QueryToArray', $QUERY);

        $RESULT = array();
        if ($query_result) {
            $RESULT = $query_result->fetchAll();
        }
        return $RESULT;
    }


    public function GetCustomRecord($QUERY)
    {
        $query_result = $this->Query('GetCustomRecord', $QUERY);

        $RESULT = array();
        if ($query_result) {
            $RESULT = $query_result->fetch();
            $query_result->closeCursor();
        }
        return $RESULT;
    }

    public function StartTransaction()
    {
        $this->SetDbQuery('StartTransaction', "-------- START TRANSACTION --------");
        return $this->Pdo->beginTransaction();
    }

    public function TransactionCommit()
    {
        $this->SetDbQuery('db_TransactionCommit', "-------- COMMIT --------");
        return $this->Pdo->commit();
    }

    public function Rollback()
    {
        $this->SetDbQuery('db_Rollback', "-------- ROLLBACK --------");
        return $this->Pdo->rollBack();
    }

    public function Error()
    {
        return $this->Error;
    }

    public function db_Close()
    {
        $this->Pdo = null;
    }

    
    public function EchoQuery($CLASSNAME='', $FUNCTIONNAME='', $FORCE_ON=false)
    {
        if ($this->Db_Want_Query || $FORCE_ON || Session('WANT_DB_QUERIES')) 
        { 
            //$server_info = $this->Pdo->getAttribute(constant("PDO::ATTR_DB_NAME"));
            
            $last_query     = $this->Db_Last_Query;
            $server_info    = $this->GetDatabase();
            
            
            echo "
            <br />
            <div style='border:1px solid #ddd;'>
                <div style='padding:5px; background-color:#e3e3e3;'>
                    <div><b>QUERY</b> :: {$CLASSNAME} :: {$FUNCTIONNAME}() </div>
                    <div>[<b>Database:</b> {$server_info}]</div>
                </div>
                <div style='padding:5px;'>{$last_query}</div>
            </div>
            <br />";
        }
    }
    
    public function GetDatabase($function_name = 'GetDatabase')
    {
        if ($this->Database_Type == 'SQLITE') {
            $query = "SELECT DATABASE()"; //"SELECT name FROM sqlite_master WHERE type='table'";
        } else {
            $query = 'SELECT DATABASE()';
        }

        $query_result = $this->Query($function_name, $query, PDO::FETCH_NUM);

        $RESULT = '';
        if ($query_result) {
            $row = $query_result->fetch();
            $RESULT = $row[0];
            $query_result->closeCursor();
        }
        
        return $RESULT;
    }
    
    
    /* ============== RAW ADDED 2012-11-29 ============== */
    
    /*
    public function GetCreateTable($table_name = '')
    {
        $function_name  = 'GetCreateTable';
        $query          = 'SHOW CREATE TABLE ' . $table_name;
        $query_result   = $this->Query($function_name, $query, PDO::FETCH_NUM);

        $RESULT = array();
        if ($query_result) {
            $RESULT = $query_result[1];
            
            /*
            while ($row = $query_result->fetch()) {
                $RESULT[] = $row[0];
            }
            
            $query_result->closeCursor();
        }
        //NatCaseSort($RESULT);
        return $RESULT;
    }
    */
}