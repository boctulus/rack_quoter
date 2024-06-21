<?php

namespace boctulus\SW\libs;

use boctulus\SW\core\Router;
use boctulus\SW\core\libs\Users;
use boctulus\SW\core\libs\Config;
use boctulus\SW\core\libs\Logger;
use boctulus\SW\core\libs\Metabox;
use boctulus\SW\core\libs\ProductMetabox;
use boctulus\SW\core\FrontController;

/*
    @author Pablo Bozzolo < boctulus@gmail.com >

    WP lifecycle:

    init
    register_sidebar
    wp_register_sidebar_widget
    wp_default_scripts
    wp_default_styles
    admin_bar_init
    add_admin_bar_menus
    wp_loaded
*/

class Main
{ 
    function __construct()
    {
        add_action('init', [$this, 'init']);
        add_action('wp_loaded', [$this, 'wp_loaded'], 10, 1 ); 
        add_action('wp_footer', [$this, 'wp_footer']);
        add_action('admin_footer', [$this, 'admin_footer']);

        // add_action( 'woocommerce_loaded', [$this, 'woo_loaded'], 10, 1 ); 
        // add_action( 'woocommerce_init', [$this, 'woo_init'], 10, 1 ); 
    }

    function init()
    {       
       
    }

    function wp_loaded(){
        /*
            En este plugin donde se importan productos NO mover a otro hook
        */
        Router::resolve();  
        FrontController::resolve();
    }

    function woo_init()
    {   
       
    }

    function woo_loaded()
    {   
       
    }

    function wp_footer(){

    }

    function admin_footer(){

    }
    

}