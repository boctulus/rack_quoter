<?php

use boctulus\SW\core\libs\Strings;
use boctulus\SW\core\libs\Files;
use boctulus\SW\core\Constants;


/*
    Convierte ruta relativa al root del plugin en url

    Ej:

    $url = path2url('etc/some-file.csv');
*/
function path2url($rel_path){
    return Files::normalize(plugin_dir_url(Constants::ROOT_PATH . 'index.php') . $rel_path, Files::LINUX_DIR_SLASH);
}

function plugin_url(){
    return base_url() . '/wp-content/plugins/' . plugin_name();
}

function plugin_assets_url($file = null){
    $url = base_url() . '/wp-content/plugins/' . plugin_name() . '/assets';

    return $url;
}

/*
    Ej:

    wp_enqueue_script( 'main-js', asset_url('/js/main.js') , array( 'jquery' ), '1.0', true );
*/
function asset_url($file){
    $url = base_url() . '/wp-content/plugins/' . plugin_name() . '/assets';

    if (!empty($file)){
        $file = Strings::removeFirstSlash($file);
        $url .= '/' . $file;
    }

    return $url;
}

