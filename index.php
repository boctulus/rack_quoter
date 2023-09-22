<?php

use boctulus\SW\core\Router;
use boctulus\SW\core\FrontController;
use boctulus\SW\core\libs\Files;

/*
	Plugin Name: Rack quoter
	Description: Shortcode cotizador de racks
	Version: 0.0.1
	Author: Abilo Escalona & others
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! defined( 'CURRENT_PLUGIN_INDEX_FILE' ) ) {
	define( 'CURRENT_PLUGIN_INDEX_FILE', __FILE__ );
}		

require_once __DIR__ . '/app.php';


register_activation_hook( __FILE__, function(){
	$log_dir = __DIR__ . '/logs';
	
	if (is_dir($log_dir)){
		Files::globDelete($log_dir);
	} else {
		Files::mkdir($log_dir);
	}

	include_once __DIR__ . '/on_activation.php';
});

db_errors(false);

require_once __DIR__ . '/main.php';


/**
 * Load text domain for translations (see "boctulus-quote-importer")
 *
 */
add_action( 'plugins_loaded', function() {
	global $l10n;

	$domain = pathinfo(__FILE__, PATHINFO_FILENAME);

	if ( isset( $l10n[ $domain ] ) ) {
		return;
	}

	load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/languages/' );
} );


/*
    Con esto puedo hacer endpoints donde podre acceder a funciones de WooCommerce directa o indirectamente

    Ej:

    get_header()
	get_footer()
*/

$cfg = config();

add_action('wp_loaded', function() use ($cfg) {
    if  (!$cfg['wait_for_wc'] || ($cfg['wait_for_wc'] && defined('WC_ABSPATH') && !is_admin()))
	{
       	/*
			Router
		*/

		$routes = include __DIR__ . '/config/routes.php';
		
		if ($cfg['router'] ?? true){ 
			Router::routes($routes);
			Router::getInstance();
		}

		/*
			Front controller
		*/

		if ($cfg['front_controller'] ?? false){        
			FrontController::resolve();
		} 
    }    
});




if (isset($_GET['credits'])){
    add_action('wp_footer', function(){ 
        ?>
            <div style="
            height: 60px;
            text-align: center; margin: auto;
            width: 100%;
            padding-top: 20px;
            padding-bottom: 20px;
            background-color: #f0ad4e; ">
            <strong>Rack Quoter Plugin</strong> by <b>Pablo Bozzolo</b> < boctulus@gmail.com >
            </div>
        <?php    
    }); 
}



