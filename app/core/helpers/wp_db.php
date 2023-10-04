<?php

function db_instance(){
    global $wpdb;
    
    return $wpdb;
}

function db_prefix(){
    global $wpdb;

    return $wpdb->prefix;
}

/*
    Es una aproximacion
*/
function db_prefix_replace(string $sql){
    $prefix = db_prefix();

    return str_replace("wp_", $prefix, $sql);
}