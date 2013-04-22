<?php
// FILE: /Lib/Singleton.php

// USAGE:  $instance = Lib_Singleton::GetInstance('SomeClass');

class Lib_Singleton
{
    private static $Instances = array();
 
    private function __construct(){}
    
    public static function GetInstance($class=null, $parameter='')
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
    
    public static function GetInstanceOverrideName($class=null, $parameter='', $instance_name='')
    {
        # FUNCTION :: Instantiate singleton version of class - but using a special instance name.
        #             This lets you recall a class again with a name so both instances can co-exist.
        
        if( is_null( $class ) ) {
            trigger_error( 'Missing class information', E_USER_ERROR );
        }
        
        if( !array_key_exists( $instance_name, self::$Instances ) ) {
            if ($parameter) {
                self::$Instances[ $instance_name ] = new $class($parameter);
            } else {
                self::$Instances[ $instance_name ] = new $class;
            }
        }
        
        return self::$Instances[ $instance_name ];
    }
    
    public final function __clone()
    {
        trigger_error( 'Cannot clone instance of Singleton pattern', E_USER_ERROR );
    }
}
