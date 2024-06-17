<?php

use boctulus\SW\core\libs\DB;
use boctulus\SW\core\interfaces\ICommand;

class HelpCommand implements ICommand 
{
    function handle($args){
        $str = <<<STR
        php com mysql_log on                                  DB::dbLogOn()
        php com mysql_log off                                 DB::dbLogOff()
        php com mysql_log start [-filename=]  que ejecuta ..  DB::dbLogStart()       
        php com mysql_log dump                                DB::dbLogDump() 
        STR;

        dd($str);
    }
    
} 