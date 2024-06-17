<?php

use boctulus\SW\core\Constants;
use boctulus\SW\core\libs\StdOut;
use boctulus\SW\core\libs\System;

if (!function_exists('bg_com')) {
    /*
        Ej:

        bg_com("bzz_import do_process")
    */
    function bg_com(string $command, $output_path = null){
        $php = System::getPHP();
        $dir = Constants::ROOT_PATH;

        $cmd = "$php {$dir}com $command";
        $pid = System::runInBackground($cmd, $output_path);

        return $pid;
    }
}

if (!function_exists('is_cli')) {
    function is_cli(){
        return (php_sapi_name() == 'cli');
    }
}

if (!function_exists('is_unix')) {
    function is_unix(){
        return (DIRECTORY_SEPARATOR === '/');
    }
}

if (!function_exists('long_run')) {
    /*
        Pasar -1 a $max_exec_time si desea que sea ilimitado
    */
    function long_run($max_exec_time = 84000, $max_mem_size = '16384M'){
        System::setMemoryLimit($max_mem_size);
        System::setMaxExecutionTime($max_exec_time);
    }
}

if (!function_exists('nap')) {
    /*
        Tiempo en segundos de sleep

        Acepta valores decimales. Ej: 0.7 o 1.3
    */
    function nap($time, $echo = false){
        if ($echo){
            StdOut::pprint("Taking a nap of $time seconds");
        }

        if (!is_numeric($time)){
            throw new \InvalidArgumentException("Time should be a number");
        }

        $time = ((float) ($time)) * 1000000;

        return usleep($time);	 
    }
}
