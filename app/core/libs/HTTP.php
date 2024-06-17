<?php

/*
    @author  Pablo Bozzolo boctulus@gmail.com
*/

namespace boctulus\SW\core\libs;

class HTTP
{
    static function cors(
        string $crossOrigin = '*',
        bool   $allowCredentials = true,
        array  $allowedHeaders = [
            'Origin',
            'Content-Type',
            'X-Auth-Token',
            'AccountKey',
            'X-Requested-With',
            'Authorization',
            'Accept',
            'Client-Security-Token',
            'Host',
            'Date',
            'Cookie',
            'Cookie2',
        ],
        array $allowedMethods = ['POST', 'OPTIONS']
    ) {
        
        header("Access-Control-Allow-Origin: $crossOrigin");
        header('Access-Control-Allow-Credentials: ' . ($allowCredentials ? 'True' : 'False'));
        header('Access-Control-Allow-Headers: ' . implode(', ', $allowedHeaders));
        header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
    }
}
