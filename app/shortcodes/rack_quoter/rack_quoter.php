<?php

// SHORTCODE

use boctulus\SW\core\libs\Plugins;

enqueue(function(){
    css_file('third_party/fontawesome5/all.min.css');
    js_file('third_party/fontawesome5/fontawesome-5.js');

    css_file('third_party/bootstrap/3.x/bootstrap.min.css');
    css_file('third_party/bootstrap/3.x/normalize.css');
    js_file('third_party/bootstrap/3.x/bootstrap.min.js');

    // JS printer
    js_file("third_party/printThis/printThis.js");
        
    css_file(__DIR__ . '/assets/css/racks.css'); 
    css_file(__DIR__ . '/assets/css/styles.css');

    js_file('js/url.js');
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
    <?php 

        return get_view(SHORTCODES_PATH . 'rack_quoter/views/racks.php'); 

    ?>
 
    <?php
}


/*
    Shortcode

    Para iniciar abierto usar asi:

    [rack-quoter-modal open=true]
*/
function rack_quoter_modal($args = [])
{   
    $open = $args['open'] ?? false;

    ob_start(); // Inicia el almacenamiento en el búfer de salida    
    ?>
   
    <!-- Modal -->
    <div class="modal fade" id="rack_quoter-modal" tabindex="-1" role="dialog" aria-labelledby="rack_quoterLabel" aria-hidden="true">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog modal-lg vertical-align-center">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>

                        </button>
                         <h2 class="modal-title" id="rack_quoterLabel">Pallet rack quoter</h2>

                    </div>
                    <div class="modal-body">
                        <?php echo rack_quoter(); // Imprime el resultado de rack_quoter() ?>
                    </div>
                    <div class="modal-footer">
                         <!-- -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($open): ?>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery('#rack_quoter-modal').modal('show');
            });
        </script>
    <?php endif; ?>

    <?php

    $output = ob_get_clean(); // Obtiene el contenido del búfer y lo limpia
    return $output; // Retorna el contenido almacenado
}


add_shortcode('rack-quoter', 'rack_quoter');
add_shortcode('rack-quoter-modal', 'rack_quoter_modal');
