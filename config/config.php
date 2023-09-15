<?php

return [
    'wait_for_wc'       => true,  // <-- 

    'DateTimeZone'      => 'Asia/Manila',
    'api_key'           => null,

    'app_name'          => env('APP_NAME'),
    'namespace'         => "boctulus\SW", 
    'use_composer'      => false,

    /*
        Intercepta errores
    */
    
    'error_handling'    => true,

    /*
        Puede mostrar detalles como consultas SQL fallidas 

        Ver 'log_sql'
    */

    'debug'             => env('DEBUG'),

    'log_file'          => 'log.txt',
    
    /*
        Loguea cada consulta / statement -al menos las ejecutadas usando Model-

        Solo aplica si 'debug' esta en true
    
    */

    'log_sql'           => true,
    
    /*
        Genera logs por cada error / excepcion
    */

    'log_errors'	    => true,

    /*
        Si se quiere incluir todo el trace del error -suele ser bastante largo-

        Solo aplica con 'log_errors' en true
    */

    'log_stack_trace'  => false,

    'front_controller' => true,
    'router'           => true,

    // dejar en false para no chequear certificado
    'ssl_cert'         => false
];

