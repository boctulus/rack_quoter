<?php declare(strict_types = 1);

use boctulus\SW\core\libs\Debug;
use boctulus\SW\core\libs\Files;
use boctulus\SW\core\libs\Logger;
use boctulus\SW\core\libs\StdOut;
use boctulus\SW\core\libs\Strings;
use boctulus\SW\core\libs\VarDump;
use boctulus\SW\core\Constants;

if (!function_exists('show_debug_trace')){
    function show_debug_trace(bool $status = true){
        VarDump::showTrace($status);
    }
}

if (!function_exists('hide_debug_trace')){
    function hide_debug_trace(){
        VarDump::hideTrace();
    }
}

if (!function_exists('show_debug_response')){
    function show_debug_response(bool $status = true){
        VarDump::showResponse($status);
    }
}

if (!function_exists('hide_debug_response')){
    function hide_debug_response(){
        VarDump::hideResponse();
    }
}

if (!function_exists('_dd')){
    function _dd($val = null, $msg = null, bool $additional_carriage_return = true){
        // foo();
	    return VarDump::dd($val, $msg, $additional_carriage_return);
    }
}

if (!function_exists('dd')){
    function dd($val = null, $msg = null, bool $additional_carriage_return = true){
        return _dd($val, $msg, $additional_carriage_return);
    }
}

if (!function_exists('foo')){
    function foo(){
        throw new \Exception("FOO");
    }
}

if (!function_exists('here')){
    function here(){
        _dd('HERE !');
    }
}

if (!function_exists('debug')){
    function debug($val, $msg = null) 
    {
        dd($val, $msg);
        
        if (!empty($msg)){
            Logger::log($msg. ': '. var_export($val, true));
        } else {
            Logger::log(var_export($val, true));
        }   
    }
}

if (!function_exists('console_log')){
    function console_log($val, $msg = null, bool $only_admin = false){
        if ($only_admin && !is_admin()){
            return;
        }

        if (!is_cli()){
            ?>
            <script>
                console.log('<?= $msg ?>', '<?= $val ?>');     
            </script>
            <?php
        }
    }
}

if (!function_exists('get_log')){
    function get_log(string $file, bool $reverse = true){
        if (!file_exists(Constants::LOGS_PATH . $file)){
            return '--x--';
        }

        $content = file_get_contents(Constants::LOGS_PATH . $file);

        if (empty($content)){
            return '--x--';
        }

        if ($reverse){
            $lines   = Strings::lines($content);
            $lines   = array_reverse($lines);
        
            $content = implode(PHP_EOL, $lines);
        }

        return nl2br($content);
    }
}
