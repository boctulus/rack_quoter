<?php

// SHORTCODE

use boctulus\SW\core\Constants;
use boctulus\SW\core\libs\Config;
use boctulus\SW\core\libs\Plugins;

class RackQuoter {
    public static function init() {
        Config::set('', include __DIR__ . '/config/config.php');

        add_action('wp_enqueue_scripts', [self::class, 'enqueue_assets']);
        add_action('init', [self::class, 'register_shortcodes']);
    }

    public static function enqueue_assets() {
        $plugin_url = plugin_dir_url(__FILE__);
        
        // Encolar CSS a nivel de plugin
        wp_enqueue_style('rack-quoter-fontawesome', asset('third_party/fontawesome5/all.min.css'), [], '5.15.3');
        wp_enqueue_style('rack-quoter-bootstrap', asset('third_party//bootstrap/3.x/bootstrap.min.css'), [], '3.3.7');
        wp_enqueue_style('rack-quoter-normalize', asset('third_party/bootstrap/3.x/normalize.css'), [], '3.3.7');
 
        // Encolar JS a nivel de plugin
        wp_enqueue_script('rack-quoter-fontawesome', asset('third_party/fontawesome5/fontawesome-5.js'), [], '5.15.3', true);
        wp_enqueue_script('rack-quoter-bootstrap', asset('third_party/bootstrap/3.x/bootstrap.min.js'), ['jquery'], '3.3.7', true);
        wp_enqueue_script('rack-quoter-printthis', asset('third_party/printThis/printThis.js'), ['jquery'], '1.0.0', true);
        wp_enqueue_script('rack-quoter-url', asset('js/url.js'), ['jquery'], 'x1.0', true);
 
        // JS personalizado a nivel del shortcode (interior)
        wp_enqueue_style('rack-quoter-racks', $plugin_url . 'assets/css/racks.css', [], '1.0');
	    wp_enqueue_style('rack-quoter-styles', $plugin_url . 'assets/css/styles.css', [], '1.0');
    }

    public static function register_shortcodes() {
        add_shortcode('rack-quoter', [self::class, 'rack_quoter']);
        add_shortcode('rack-quoter-modal', [self::class, 'rack_quoter_modal']);
    }

    public static function rack_quoter($args = []) {
        $mode = $args['mode'] ?? null;

        /*
            Settings
        */
        $cfg = Config::get();

        // ...

        if (!Plugins::isActive('woocommerce')){
            wp_die("WooCommerce es requerido");
        }

        $debug = false;
        if (isset($_GET['debug']) && in_array($_GET['debug'], ['true', '1'])){
            $debug = true;
        }

        // ...

        ob_start();
        ?>

        <!-- HTML -->
        <?php
        return get_view(Constants::SHORTCODES_PATH . 'rack_quoter/views/racks.php');
    }

    public static function rack_quoter_modal($args = []) {
        $open = $args['open'] ?? false;

        ob_start();
        ?>

        <!-- Modal -->
        <div class="modal fade" id="rack_quoter-modal" tabindex="-1" role="dialog" aria-labelledby="rack_quoterLabel" aria-hidden="true">
            <div class="vertical-alignment-helper">
                <div class="modal-dialog modal-lg vertical-align-center">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h2 class="modal-title" id="rack_quoterLabel">Pallet rack quoter</h2>
                        </div>
                        <div class="modal-body">
                            <?php echo self::rack_quoter(); // Imprime el resultado de rack_quoter() ?>
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
        $output = ob_get_clean();
        return $output;
    }
}


RackQuoter::init();