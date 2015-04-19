<?php

if(!defined('ROOT_PATH')){
    define('ROOT_PATH', dirname(__FILE__));
}

require 'lib/functions.php';
require 'lib/Registry.php';
require_once 'lib/ConfigHandler.php';
require_once 'lib/Selfpdo.php';

$config = ConfigHandler::getMain();
Registry::set('config', $config);






