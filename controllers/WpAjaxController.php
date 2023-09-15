<?php

namespace boctulus\SW\controllers;

use boctulus\SW\libs\MutaWP;
use boctulus\SW\core\libs\Url;
use boctulus\SW\core\libs\Users;
use boctulus\SW\core\libs\Logger;
use boctulus\SW\core\libs\Strings;

class WpAjaxController
{
    function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }
    
    function register_routes()
    {
        # /wp-json/entity/action
        register_rest_route( 'entity', '/action', array(
            'methods'            => '{VERB}',
            'callback'           => [ $this, '{callback}' ],
            'permission_callback' => '__return_true',       
        ) );

        // more 
    }
    
   
}
