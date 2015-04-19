<?php

/**
 * Description of AppGlobals
 *
 * @author rnest
 */
class Registry
{

    private static $instance = null;
    private $storage = array();

    private function __construct()
    {
        
    }

    public function __clone()
    {
        
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new Registry();
        }
        return self::$instance;
    }

    public function getStorage()
    {
        return $this->storage;
    }
    
    private function register($i, $v){
        $this->storage[$i] = $v;
    }
    
    private function getRegisteredIndex($i)
    {
        return $this->storage[$i];
    }

    public static function set($index, $value)
    {
        $registry = self::getInstance();
        $registry->register($index, $value);
    }

    public static function get($index)
    {
        $registry = self::getInstance();
        return $registry->getRegisteredIndex($index);
    }

}
