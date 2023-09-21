<?php

use boctulus\SW\core\libs\Url;
use boctulus\SW\core\libs\Files;
use boctulus\SW\core\libs\Plugins;
use boctulus\SW\core\libs\Strings;

function set_shortcode($tag){
    $tag     = str_replace([
        '[', ']'
    ], '', $tag);
    
    $fn_name = str_replace('-', '_', $tag);
    
    add_shortcode($tag, $fn_name);
}

function get_shortcode($tag){
    return do_shortcode("[$tag]");
}

function shortcode($tag){
    echo get_shortcode($tag);
}

function plugin_path(){
    return realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');
}

function plugin_name(){
    static $plugin_name ;

    if ($plugin_name !== null){
        return $plugin_name;
    }

    $plugin_name = Strings::before(
        Strings::after(__DIR__,  DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR), 
        DIRECTORY_SEPARATOR
    );

    return $plugin_name;
}

/*
    Para ser usado dentro de un shortcode

    Ej:

    <img src="<?= shortcode_asset(__DIR__ . '/images/WES-Logo.png') ?>" />
*/
function shortcode_asset($resource)
{   
    $protocol = is_cli() ? 'http' : httpProtocol();
    
    $resource = Strings::substract($resource, SHORTCODES_PATH);
    $resource = str_replace('\\', '/', $resource);
    $resource = str_replace('/views/', '/assets/', $resource);
    
    $base     = config()['base_url'] ?? '';

    if (Strings::endsWith('/', $base)){
        $base = substr($base, 0, -1); 
    }

    $url  = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? env('APP_URL')) . '/wp-content/plugins/' . Plugins::currentName()  . '/app/shortcodes/';
    $url .= $resource;

    $url = Files::normalize($url, '/');

    return $url;    
}

function assets_url(?string $resource = null){        
    if (Files::isAbsolutePath($resource)){
        $resource =  Strings::substract($resource, APP_PATH);
    }

    $resource = str_replace('\\', '/', $resource);
    
    $url = plugin_url() . '/';    

    $url = $url . (!$resource === null ? '' : $resource);

    $url = Files::normalize($url, '/');

    return $url;
}

function asset(string $resource){
    return assets_url($resource);
}

function css_file(string $src, $dependencies = [], $version = null, $media = 'all'){
	$src    = ltrim($src, '/\\');
	$handle = $src;

    if (Files::isAbsolutePath($src)){
        $src = Strings::substract($src, APP_PATH);
    } else {
        $src = 'assets/' . $src;
    }

	if (!Strings::startsWith('http', $src)){
		$src = asset($src);
	}

    if ($version === null && !Strings::contains('third_party', $src)){
        $version = 'asset-' . Plugins::getVersion();
    }
    
	wp_register_style($handle, $src, $dependencies, $version, $media);
	wp_enqueue_style($handle);
}

function js_file(string $src, bool $in_head = false, $dependencies = [], $version = null){
	$src    = ltrim($src, '/\\');
	$handle = $src;

	if (!Strings::startsWith('http', $src)){
		$src = asset($src);
	}

    if ($version === null && !Strings::contains('third_party', $src)){
        $version = 'asset-' . Plugins::getVersion();
    }

	wp_register_script($handle, $src, $dependencies, $version, !$in_head);
	wp_enqueue_script($handle);
}

// Función para agregar código JavaScript inline en temas y plugins
function js($code, $script_name = 'my_js', $is_plugin = false) {
    if ($is_plugin) {
        // Si es un plugin, utilizamos wp_enqueue_scripts
        add_action('wp_enqueue_scripts', function() use ($code, $script_name) {
            wp_add_inline_script($script_name, $code);
        });
    } else {
        // Si es un tema, utilizamos wp_print_scripts
        add_action('wp_print_scripts', function() use ($code) {
            echo '<script>' . $code . '</script>';
        });
    }
}

// Función para agregar CSS inline en temas y plugins
function css($css_code, $style_id = 'my_css', $is_plugin = false) {
    if ($is_plugin) {
        // Si es un plugin, utilizamos wp_enqueue_scripts
        add_action('wp_enqueue_scripts', function() use ($css_code, $style_id) {
            wp_add_inline_style($style_id, $css_code);
        });
    } else {
        // Si es un tema, utilizamos wp_print_styles para frontoffice
        add_action('wp_print_styles', function() use ($css_code) {
            echo '<style>' . $css_code . '</style>';
        });

        // Utilizamos admin_enqueue_scripts para el panel de administración
        add_action('admin_enqueue_scripts', function() use ($css_code) {
            echo '<style>' . $css_code . '</style>';
        });
    }
}

function enqueue($callback, $priority = 10, $accepted_args =1){
    add_action('wp_enqueue_scripts', $callback, $priority, $accepted_args);
}

function enqueue_admin($callback, $priority = 10, $accepted_args =1){
    add_action('admin_enqueue_scripts', $callback, $priority, $accepted_args);
}

function include_no_render(string $path, ?Array $vars = null){
    if (!empty($vars)){
        extract($vars);
    }      
    
    ob_start();
    include $path;
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

function get_view_src(string $filename){
    $ext = Files::fileExtension($filename);

    if ($ext != 'php' && $ext != 'htm' && $ext != 'html'){
        // asumo es .php
        $filename .= '.php';
    }

    if (Files::isAbsolutePath($filename)){
        $path = $filename;
    } else {    
        $path = plugin_path() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $filename;
    }

    return $path;
}

function get_view(string $view_path, ?Array $vars = null){
    return include_no_render(get_view_src($view_path), $vars);
}

function view(string $view_path, array $vars  = null){
    if (!empty($vars)){
        extract($vars);
    }      

    include get_view_src($view_path);
}


/*
    Antes llamada encodeProp()
*/
function var_encode($name, $value){
    $encoded = base64_encode(is_array($value) ? '--array--' . json_encode($value) : $value);

    return "<input type=\"hidden\" name=\"$name-encoded\" id=\"$name-encoded\" value=\"$encoded\">";
}
