<?php

use boctulus\SW\core\libs\StdOut;

function is_cli(){
	return (php_sapi_name() == 'cli');
}

function is_unix(){
	return (DIRECTORY_SEPARATOR === '/');
}

function long_exec(){
	// ini_set("memory_limit", $config["memory_limit"] ?? "728M");
	wp_raise_memory_limit();	
	
	ini_set("max_execution_time", $config["max_execution_time"] ?? -1);
}

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