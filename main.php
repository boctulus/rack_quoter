<?php

use boctulus\SW\core\libs\Templates;

/*
    By boctulus
*/

// Templates::set('astra');


// Shortcodes
require_once __DIR__ . '/shortcodes/rack_quoter_sc.php';


function assets(){
	//css_file('/third_party/bootstrap/5.x/bootstrap.min.css');
    // js_file('/third_party/bootstrap/5.x/bootstrap.bundle.min.js');

	// css_file('/css/styles.css');
    
    // js_file('/third_party/sweetalert2/sweetalert.js');
    // css_file('/third_party/sweetalert2/sweetalert2.min.css');

    // js_file('/js/utilities.js');
    // js_file('/js/notices.js');
    // js_file('/js/storage.js');

    // css_file('/wp-content/plugins/woocommerce/assets/css/menu.css?ver=6.5.1');

    // css_file('/third_party/fontawesome5/all.min.css');
    // js_file('/third_party/fontawesome5/fontawesome-5.js');
    
}

// enqueue('assets');

function admin_assets(){
    // css_file('/css/admin_styles.css');

    // js_file('/third_party/sweetalert2/sweetalert.js');
    // css_file('/third_party/sweetalert2/sweetalert2.min.css');

    // fuentes
    // css_file('https://fonts.googleapis.com/css2?family=Roboto:wght@400&display=swap');
    // css_file('https://fonts.googleapis.com/css2?family=Roboto:wght@500&display=swap');
    // css_file('https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap');

    // Podria condicionarse a ciertas paginas
    // css_file('/css/licence.css');

    // js_file('/js/url.js');
}

// enqueue_admin('admin_assets');


// (new WpAjaxController());



