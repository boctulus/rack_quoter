<?php

use boctulus\SW\libs\Main;

use boctulus\SW\core\Constants;
use boctulus\SW\core\libs\Config;

/*
    @author Pablo Bozzolo < boctulus@gmail.com >
*/

// Mostrar errores
if ((php_sapi_name() === 'cli') || (isset($_GET['show_errors']) && $_GET['show_errors'] == 1)){
   ini_set('display_errors', '1');
   ini_set('display_startup_errors', '1');
   error_reporting(E_ALL);
}

// Templates::set('astra');

// Shortcodes
require_once __DIR__ . '/app/shortcodes/rack_quoter/rack_quoter.php';

function assets(){
   // ..    
}

// enqueue('assets');

function admin_assets(){
   // ..
}

// enqueue_admin('admin_assets');

// (new WpAjaxController());


// new Main();

