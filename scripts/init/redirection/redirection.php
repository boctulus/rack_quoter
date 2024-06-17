<?php

function handleRedirects() {
    $files = [
        '301' => __DIR__ . DIRECTORY_SEPARATOR . '301.txt',
        '302' => __DIR__ . DIRECTORY_SEPARATOR . '302.txt'
    ];

    $url_base = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";

    // Obtener la URL solicitada
    $requestedUrl = $_SERVER['REQUEST_URI'];

    // Procesar cada tipo de redirección
    foreach ($files as $statusCode => $filename) {
        if (file_exists($filename)) {
            $redirects = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($redirects as $redirect) {
                $redirect = trim($redirect);

                if (empty($redirect)){
                    continue;
                }

                // Ignorar líneas que comienzan con ;
                if (strpos($redirect, ';') === 0) {
                    continue;
                }
                
                list($oldUrl, $newUrl) = explode(' ', $redirect, 2);

                // Comparar la URL solicitada con la URL antigua
                if ($requestedUrl === $oldUrl || $url_base . $requestedUrl === $oldUrl) {
                    // Realizar la redirección
                    header("Location: $newUrl", true, (int)$statusCode);
                    exit();
                }
            }
        }
    }
}

// Llamar a la función de redirección
handleRedirects();
