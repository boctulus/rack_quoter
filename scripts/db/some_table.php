<?php

use boctulus\SW\core\libs\DB;
use boctulus\SW\core\libs\Strings;

global $wpdb;

$table_name      = $wpdb->prefix . Strings::lastSegmentOrFail(Strings::before(__FILE__, '.php'), DIRECTORY_SEPARATOR);
$charset_collate = $wpdb->get_charset_collate();

DB::statement("DROP TABLE IF EXISTS `$table_name`;");

$sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned AUTO_INCREMENT,
        /* 
            ...
        */
        created_at DATETIME DEFAULT NULL,
        updated_at DATETIME DEFAULT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB $charset_collate;";

$ok = DB::statement($sql);

if (is_cli()){
    dd($sql);
}