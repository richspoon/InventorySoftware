<?php
// FILE: /Lib/Lib_Password.php

class Lib_Password
{

    public static function MakePassword($syllables=3, $options='')  // $options = 'CNS'
    {
        $symbols = '!@#$%^*()-_+={}[]|/:;,.?~|';
        $use_case    = (strpos($options, 'C') !== false);
        $use_numbers = (strpos($options, 'N') !== false);
        $use_symbols = (strpos($options, 'S') !== false);
        
        $vowels = array ('a', 'o', 'e', 'i', 'y', 'u', 'ou', 'oo');        
        $suffix = array ('dom', 'ity', 'ment', 'sion', 'ness', 'ence', 'er', 'ist', 'tion', 'or');
        $doubles = array('n', 'm', 't', 's');
        $consonants = array (
            'w', 'r', 't', 'p', 's', 'd', 'f', 'g',
            'h', 'j', 'k', 'l', 'z', 'x', 'c', 'v',
            'b', 'n', 'm', 'qu'
        );

        $symmax = 25; //count($symbols)-1;
        $vmax = 7;  //count($vowels)-1;
        $smax = 9;  //count($suffix)-1;
        $cmax = 19; //count($consonants)-1;

        $password = '';

        for ($i=0; $i<$syllables; $i++) {

            $c = $consonants[mt_rand(0, $cmax)];

            if ( ($i>0) and in_array($c, $doubles) and 1 == mt_rand(0, 2)) {
                $c .= $c;
            }
            if ($use_case) {
                $c = ucFirst($c);
            }
            
            $symbol = ($use_symbols and ($i < $syllables - 1))? $symbols[mt_rand(0, $symmax)] : '';
            $number = ($use_numbers and ($i < $syllables - 1))? mt_rand(0,9) : '';
            
            $password .=  $c . $vowels[mt_rand(0, $vmax)] . $number. $symbol;
        }

        $new = $suffix[mt_rand(0, $smax)];
        $new = (in_array($new[0], $vowels)) ? $consonants[mt_rand(0, $cmax)] . $new : $new;
        return $password . $new;
    }


/* example: 
    $PASSWORD_HASH_INFO = array(
        'type' => 'sha256', 
        'user_salt' => "MySa:Lt5\n\e\r", 
        'random_salt_length' => 8
    ); 
 */
    
    public static function GetPasswordHash($str)
    {
        global $PASSWORD_HASH_INFO;
        if (empty($PASSWORD_HASH_INFO)) {
            $type = 'sha256';
            $user_salt = '';
            $random_salt_length = 8;
        } else {
            $type               = $PASSWORD_HASH_INFO['type'];
            $user_salt          = $PASSWORD_HASH_INFO['user_salt'];
            $random_salt_length = $PASSWORD_HASH_INFO['random_salt_length'];
        }
        $random_salt = ($random_salt_length)? substr(md5(uniqid(rand())), 0, $random_salt_length) : '';
        return $random_salt . hash($type, $random_salt . $str . $user_salt);
    }

    public static function CheckPasswordHash($str, $hashed_string)
    {
        global $PASSWORD_HASH_INFO;
        $type               = $PASSWORD_HASH_INFO['type'];
        $user_salt          = $PASSWORD_HASH_INFO['user_salt'];
        $random_salt_length = $PASSWORD_HASH_INFO['random_salt_length'];
        $random_salt        = substr($hashed_string, 0, $random_salt_length);
        return $hashed_string === $random_salt . hash($type, $random_salt . $str . $user_salt);
    }

}