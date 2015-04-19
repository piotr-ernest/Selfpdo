<?php

/**
 * Description of ConfigHandler
 *
 * @author rnest
 */
class ConfigHandler
{

    protected static $config = null;
    protected static $configPath = ROOT_PATH . '/config';

    public static function __callStatic($rowName, $arguments)
    {
        $name = str_replace('get', '', $rowName);
        return self::getConfig($name);
    }

    protected static function getConfig($name)
    {
        $path = self::$configPath . '/' . strtolower($name) . '.php';
        return (include $path);
    }
    

}
