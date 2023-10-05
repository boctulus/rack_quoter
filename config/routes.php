<?php

/*
    Routes for Router

    Nota: la ruta mas general debe colocarse al final
*/

return [
    // rutas 
    'GET:/api/drawing/preview'        => 'boctulus\SW\controllers\DrawingController@render_rack_array',
    'GET:/api/drawing/debug'          => 'boctulus\SW\controllers\DrawingController@debug',
    'GET:/api/drawing/calc/pallets '  => 'boctulus\SW\controllers\DrawingController@calc_pallets',
    
    #'/api/redirection'  => 'boctulus\SW\controllers\AjaxController@redirection'
];
