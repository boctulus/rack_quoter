<?php

use boctulus\SW\core\libs\Env;


return [
    'is_enabled'   => Env::get('ENABLED', true),
    
    'app_name'     => Env::get('APP_NAME'),
    'namespace'    => "boctulus\SW",  
    'use_composer' => false, // 

    ////////////////////////////////////////////////////////////////////////////////

    'options' => [
    //     'op1' => 'value 1',
    //     'op2' => 'value 2'
    ],
    
	// "field_separator" => ";",

	"memory_limit" => "1024M",
	"max_execution_time" => 600,
	// "upload_max_filesize" => "50M",
	// "post_max_size" => "50M",

    //
    // No editar desde aqui
    //

    'tmp_dir' => sys_get_temp_dir(),

    /*
        Intercepta errores
    */
    
    'error_handling'    => true,

    /*
        Puede mostrar detalles como consultas SQL fallidas 

        Ver 'log_sql'
    */

    'debug'             => Env::get('DEBUG'),

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

    'paginator' => [
		'max_limit' => 50,
		'default_limit' => 10,
		'position' => 'TOP',
		'params'   => [
			'pageSize' => 'size',
			'page'	   => 'page_num' // redefinido para WordPress
		],
		'formatter' => function ($row_count, $count, $current_page, $page_count, $page_size, $nextUrl){
			return [
				"last_page" => $page_count,
				'paginator' => [
					"total"       => $row_count, 
					"count"       => $count,
					"currentPage" => $current_page,
					"totalPages"  => $page_count,
					"pageSize"    => $page_size,
					"nextUrl"	  => $nextUrl
				],
			];
		},
	],

    'front_controller' => true,
    'router'           => true,
];

