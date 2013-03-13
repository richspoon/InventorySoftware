<?php
// FILE: /Lib/Singleton.php

// USAGE:  $instance = Lib_Singleton::GetInstance('SomeClass');

class Lib_Singleton
{
    private static $Instances = array();
 
    private function __construct(){}
     
    public static function GetInstance( $class = null, $parameter = '' )
    {
        if( is_null( $class ) ) {
            trigger_error( 'Missing class information', E_USER_ERROR );
        }
        
        if( !array_key_exists( $class, self::$Instances ) ) {
            if ($parameter) {
                self::$Instances[ $class ] = new $class($parameter);
            } else {
                self::$Instances[ $class ] = new $class;
            }
        }
        
        return self::$Instances[ $class ];
    }
    
    public final function __clone()
    {
        trigger_error( 'Cannot clone instance of Singleton pattern', E_USER_ERROR );
    }
}
