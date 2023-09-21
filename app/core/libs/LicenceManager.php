<?php

/*
    @author Pablo Bozzolo < boctulus@gmail.com >
*/

namespace boctulus\SW\core\libs;

class LicenceManager {
    static protected $endpoint = "https://mutawp.com/mutawp/api/subscription/is_active";
    static protected $option   = "mystore_licence_key";

    static function isValid($licence_key = null, bool $throw = false){
        $licence_key = $licence_key ?? get_option(static::$option);

        if (empty($licence_key)){
            return false;
        }

        $client = ApiClient::instance()
        ->withoutStrictSSL()
        ->setUrl(static::$endpoint)
        ->queryParam('licence_key', $licence_key)
        ->decode()
        ->get();

        // Deberia ser uno de estos
        if ($client->getStatus() == 401 || $client->getStatus() == 403){
            return false;
        }

        /*
            Si se encuentra un HTTP STATUS CODE inesperado
        */
        if ($throw && $client->getStatus() != 200){
            throw new \Exception($client->error());
        }

        $res = $client->data();

        return $res["data"]["is_active"] ?? false;
    }
}