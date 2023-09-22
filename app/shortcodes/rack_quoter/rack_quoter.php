<?php

use boctulus\SW\core\libs\Plugins;

enqueue(function(){
    css_file('third_party/fontawesome5/all.min.css');
    js_file('third_party/fontawesome5/fontawesome-5.js');

    css_file('third_party/bootstrap/3.x/bootstrap.min.css');
    css_file('third_party/bootstrap/3.x/normalize.css');
        
    css_file(__DIR__ . '/assets/css/racks.css'); // <-- no estan cargando
    css_file(__DIR__ . '/assets/css/styles.css');
});

// shortcode
function rack_quoter($args = [])
{   
    $mode = $args['mode'] ?? null;

    /*
        Settings
    */

    $cfg = config(); 
    // ...

    if (!Plugins::isActive('woocommerce')){
        wp_die("WoooCommerce es requerido");
    }

    $debug = false;
    if (isset($_GET['debug']) && in_array($_GET['debug'], ['true', '1'])){
        $debug = true;
    } 

    // ...

    ?>    
    
    <!-- HTML --> 
    <?php view(SHORTCODES_PATH . 'rack_quoter/views/racks.php'); ?>
 
    <?php
}


add_shortcode('rack-quoter', 'rack_quoter');