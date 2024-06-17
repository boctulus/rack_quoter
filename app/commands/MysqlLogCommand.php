<?php

use boctulus\SW\core\libs\DB;
use boctulus\SW\core\interfaces\ICommand;

class MysqlLogCommand implements ICommand 
{
    function handle($args){
        $fst = array_shift($args);

        if ($fst == 'on'){
            dd("Iniciando logs ...");
            DB::dbLogOn();
            return;
        }

        if ($fst == 'off'){
            dd("Desactivando logs ...");
            DB::dbLogOff();
            return;
        }

        if ($fst == 'start'){
            dd("Activando logs ...");
            DB::dbLogStart();
            return;
        }

        if ($fst == 'dump'){
            dd("Volcando logs ...");
            DB::dbLogDump();
            return;
        }         
    }    
} 