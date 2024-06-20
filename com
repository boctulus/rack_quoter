#!/usr/bin/env php
<?php

use boctulus\SW\core\Constants;
use boctulus\SW\core\FrontController;
use boctulus\SW\core\libs\Config;
use boctulus\SW\core\libs\Env;
use boctulus\SW\core\libs\Files;
use boctulus\SW\core\libs\Strings;


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


if (php_sapi_name() != "cli"){
	return; 
}

define( 'ABSPATH', realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR);

require_once ABSPATH . '/wp-config.php';
require_once ABSPATH . '/wp-load.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'app.php';

/*
   Parse command line arguments into the $_GET variable <sep16@psu.edu>
*/

parse_str(implode('&', array_slice($argv, 3)), $_GET);

/*
   Procesamiento de env: y cfg:

   Ej:

   php com my_controller my_action env:variable=valor cfg:my_config_variable=3
*/

foreach ($_GET as $var => $val)
{
   $pos = strpos($var, 'env:');
   
   if ($pos === 0){
      $var = substr($var, 4);

      Env::set($var, $val);
   }

   $pos = strpos($var, 'cfg:');
   
   if ($pos === 0){
      $var = substr($var, 4);

      Config::set($var, $val);
   }
}


# Implementacoon de patron Command
#
# https://chatgpt.com/c/b27203dd-bc30-4950-a3c8-8a4e6ecb25d8
# https://chatgpt.com/c/2e4ac7e1-7ac7-4d86-ba9f-f1d61264504b
#

$routing = true;
$args    = array_slice($argv, 1);

if (count($args) > 0){
   $name         = Strings::snakeToCamel(array_shift($args));
   $commandClass = $name . "Command";

   $comm_files   = Files::glob(Constants::COMMANDS_PATH, '*Command.php');

   foreach ($comm_files as $file){
      $_name      = Strings::matchOrFail(Files::convertSlashes($file, '/'), '|/([a-zA-Z0-9_]+)Command.php|');
      
      if ($name != $_name){
         continue;
      }

      require $file;

      if (class_exists($commandClass)){      
         $commandInstance = new $commandClass();
         
         if (method_exists($commandInstance, 'handle')) {
            $commandInstance->handle($args);
            $routing = false;
         } else {
            throw new \Exception("Command without handle");
         }
      }
   }
}
   
   
add_action('wp_loaded', function() use ($routing) {            
	if ($routing){
      FrontController::resolve();
   }  
});


if (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL & ~E_NOTICE ^E_WARNING);
}

 	

