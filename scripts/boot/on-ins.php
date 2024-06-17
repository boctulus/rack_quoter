<?php

use boctulus\SW\core\libs\Config;
use boctulus\SW\core\libs\Logger;

/*
    A ejecutarse al instalar
*/

/*
    Seteo opciones la primera vez que se activa el plugin (al instalarse)

    La opciones provienen del config.php

        'options' => [
            'op1' => 'value 1',
            'op2' => 'value 2'
        ],    
*/

$options = Config::get('options');

if (!empty($options)){     
    foreach($options as $opt_name => $value){
        update_option($opt_name, $value);        
    }
}


//require_once __DIR__ . '/db/01_link2product_metadata.php';
//require_once __DIR__ . '/db/05_posts_to_lik2prd.php';

// Creacion de otras tablas
// ...

