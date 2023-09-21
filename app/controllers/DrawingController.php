<?php

namespace boctulus\SW\controllers;

use boctulus\SW\core\libs\Files;

class DrawingController
{
    function preview(){
        $path = ROOT_PATH . '\app\shortcodes\rack_quoter\assets\img/96x36x96-2.jpg';
        $path = Files::convertSlashes($path);

        $file = file_get_contents($path); 

        // Establecer las cabeceras para indicar que es una imagen JPEG
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . strlen($file));
        
        return $file;
    }

}
