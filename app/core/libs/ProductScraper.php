<?php

namespace boctulus\SW\core\libs;

use boctulus\SW\core\Constants;
use boctulus\SW\core\libs\ApiClient;

/*
    Scraper base preparado para:

    ✅ Lista de categorias de productos                 getCategoList
    ✅ Paginador de categorias                          getPaginator
    ✅ Pagina de categoria                              getCategoryPageURL, getCategoryPage
    ✅ Links de productos dentro de pagina de cat.      getProductLinks
    ✅ Pagina de producto                               getProduct

    Flujo:

    getCategoList -> getPaginator -> getCategoryPageURL -> getProductLink -> getProduct
*/
abstract class ProductScraper
{
    protected static $baseUrl;
    protected static $exp_time = 3600 * 24 * 30;

    
    static function setup($baseUrl = null, $exp_time = null){

        if (!empty($baseUrl)){
            static::$baseUrl  = $baseUrl;
        }
        
        if (!empty($exp_time)){
            static::$exp_time = $exp_time;
        }

        System::setMaxExecutionTime(-1);
        System::setMemoryLimit(99999999);
    }

    static function setExpTime($exp_time) {
        self::$exp_time = $exp_time;
    }

    /*  
        @param string $slug slug o url
        @param int|null $exp_time tiempo de duracion de la cache
    */
    static function getHTML($url, $exp_time = null)
    {
        $cli = (new ApiClient($url))
        ->withoutStrictSSL()
        ->setHeaders([
            'User-Agent' => 'PostmanRuntime/7.34.0',
        ])
        ->redirect()
        //->debug() //
        // ->ignoreStatusCodes([404])
        ->setRetries(10)
        ->cache($exp_time != null ? $exp_time : static::$exp_time);

        $cli->setMethod('GET');
        $cli->send();

        $err = $cli->error();
        if ($err){
            
            // OpenSSL SSL_connect: Connection was reset in connection to www.azulejosmadridonline.es:443
            if (Strings::startsWith("OpenSSL SSL_connect: Connection was reset in connection to ", $err)){
                static::$baseUrl = str_replace('https://', 'http://', static::$baseUrl);

                return static::getHTML($url, $exp_time);
            }            

            throw new \Exception("HTTP ERROR: ". $err);
        }

        $res = $cli->data(); // html

        return $res;
    }

    static function getHTMLfromSlug($slug, $exp_time = null){
        $url = rtrim(static::$baseUrl, '/') . '/' . ltrim($slug, '/');
        return static::getHTML($url, $exp_time);
    }

    public static function __getVariantAttrs($html, $ucfirst = false){
        throw new \Exception('Method '. __METHOD__ .' is not implemented.');
    }

    abstract public static function getProduct(string $url);

    public static function getProductBasicAttr(string $html)
    {
        throw new \Exception('Method '. __METHOD__ .' is not implemented.');
    }
    

    // Si es $html = null, entonces hace uso interno de otras funciones quizas partiendo de una lista filtrada
    abstract public static function getCategoList(?string $html = null);

    /*
        Obtiene paginador de pagina de productos (u otras)
    */
    abstract public static function getPaginator($html);

    /*
        Obtiene URL de pagina de categoria paginada
    */
    abstract public static function getCategoryPageURL(string $category_url, int $page, $page_size = null);

    /*
        Obtiene de pagina de categoria paginada
    */
    public static function getCategoryPage(string $category_url, int $page, $page_size = null)
    {
        return static::getHTML(static::getCategoryPageURL($category_url, $page, $page_size));
    }

    /*
        Devuelve descripcion e imagenes de cada categoria
    */
    public static function getCatego(string $html)
    {
        throw new \Exception('Method '. __METHOD__ .' is not implemented.');
    }

    public static function getBrandList(string $html)
    {
        throw new \Exception('Method '. __METHOD__ .' is not implemented.');
    }

    abstract public static function getProductLinks(string $html);

}

