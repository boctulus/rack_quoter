<?php

/*
    Routes for Router

    Nota: la ruta mas general debe colocarse al final
*/

return [
    // rutas 
    'POST:/mutawp/api/install' => 'boctulus\SW\controllers\AjaxController@install',
    '/mutawp/api/redirection'  => 'boctulus\SW\controllers\AjaxController@redirection'
];
