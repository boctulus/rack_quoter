<?php

use boctulus\SW\core\libs\Plugins;

/*
    Deberian solo encolarse en las paginas donde se invoca al shorcode
*/

// enqueue_admin(function(){
//     css_file('...');
//     js_file('...');
// });

enqueue(function(){
    css_file('/third_party/fontawesome5/all.min.css');
    js_file('/third_party/fontawesome5/fontawesome-5.js');

    css_file('third_party/bootstrap/3.x/bootstrap.min.css');
    css_file('third_party/bootstrap/3.x/normalize.css');
        
    css_file('css/styles.css');

    //css_file('/third_party/sweetalert2/sweetalert2.min.css'); 
    //js_file('/third_party/sweetalert2/sweetalert.js'); 
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
    <div class="container">        
        <?php view('racks\racks', [
          // params
        ])  ?>
    </div>

    <?php
}


add_shortcode('rack-quoter', 'rack_quoter');