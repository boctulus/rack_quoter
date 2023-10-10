<?php

namespace boctulus\SW\controllers;

use boctulus\SW\core\libs\Files;
use boctulus\SW\core\libs\Logger;
use boctulus\SW\core\libs\StdOut;
use boctulus\SW\core\libs\Imaginator;
use boctulus\SW\core\libs\Messurements as M;

/*
    API generadora de imagenes
*/
class DrawingController
{
    function __construct()
    {
        _cors(); // helper

        if (isset($_GET['debug'])){
            Imaginator::disable();
        } else {
            StdOut::hideResponse();
        }
    }

    function debug(){
        dd($_GET);

        Imaginator::disable();
        StdOut::showResponse();

        $this->render_rack_array();
    }

    function calc_pallets()
    {
        global $upright_height, $upright_depth, $beam_length, $beam_levels, $l_feets, $w_feets, $aisle, $len;
        global $w, $w_acc, $row_count, $boxes_per_row, $bl, $bl_with_margins;

        try {
            // Step 1
            $upright_height = (int) $_GET['height']; // inches

            $upright_depth  = (int) $_GET['depth'];   // inches     
            $beam_length    = (int) $_GET['beam_length'];   // inches * 

            $beam_levels    = (int) $_GET['beam_levels'] ?? 2;

            // Step 3
            $l_feets        = (int) $_GET['length'];  // feet <-- length **
            $w_feets        = (int) $_GET['width'];;  // feet

            // Step 4
            $aisle          = (int) $_GET['aisle']; // inches
                    
            /*
                Calculo
            */

            $len                  = M::toInches($l_feets);  // inches
            $w                    = M::toInches($w_feets);  // inches

            // StdOut::pprint($l - $upright_depth, "Max");

            // Calculo    

            $w_acc = $upright_depth;

            // StdOut::pprint($w_acc, 'w acc');

            // 42 + 60 + 2*42 + 60 + 2*42 + 60 + 2*42 + 60 + 42

            $row_count = 1;
            while ($row_count<999999 && $w_acc < $w - $upright_depth - $aisle) {
                $w_acc += $aisle + ($upright_depth * 2);
                $row_count += 1;

                // // StdOut::pprint("+= $aisle + ($upright_depth * 2)");
                // // StdOut::pprint($w_acc, 'w acc');
                // // StdOut::pprint($row_count, 'row count');
            }
        
            if ($w_acc < $w && $w_acc + $aisle + $upright_depth < $w){
                $w_acc += $aisle + $upright_depth;
                $row_count++;
            }

            StdOut::pprint(M::toFeetAndInches($w_acc), 'w acc');
            // StdOut::pprint("$row_count : row count");

            //  StdOut::pprint($h_feets, 'h');
            //  StdOut::pprint($aisle, 'aisle');
            //  StdOut::pprint$boxes_per_row, 'boxes per row'

            $boxes_per_row  = floor($len / $beam_length);

            $bl              = ($beam_length * $boxes_per_row);
            $bl_with_margins = (int) ($bl * 1.038);  // <------------- factor de correccion

            if ($bl_with_margins > $len){
                $boxes_per_row--;
            }

            $pallets = ($row_count -1) * $boxes_per_row * 12;

            switch ($beam_levels){
                case 3:
                    $pallets = round( $pallets * 4/3);
                    break;
                case 4:
                    $pallets = round( $pallets * 5/3);
                    break;
                case 5:
                    $pallets = round( $pallets * 2);
                    break;
                case 6:
                    $pallets = round( $pallets * 7/3);
                    break;
            }   

            $data = [
               'pallets' => $pallets
            ];

            return response()
            ->sendJson($data);

        } catch (\Exception $ex){
            response()->error($ex->getMessage(), $ex->getCode());
            Logger::logError($ex->getMessage());
        }             
    }

    function render_rack_array()
    {
        /*
            Seria mejor que fueran propiedades estaticas para evitar re-calcular
        */

        global $upright_height, $upright_depth, $beam_length, $beam_levels, $l_feets, $w_feets, $aisle, $len;
        global $w, $w_acc, $row_count, $boxes_per_row, $bl, $bl_with_margins;

        /*
            A veces puede quedar un poco "pasado" de ancho quedando el ultimo pasillo con alguna pulgada menos

            /img/test?design=multiple-rows&condition=new&height=96&depth=42&beam_length=96&beam_levels=2&length=50&width=200&aisle=132&usesupport=false&usewiredeck=false
        */

        $this->calc_pallets();

        if (isset($_GET['inv_color']) && $_GET['inv_color'] == 1 || $_GET['inv_color'] === 'true'){
            $color_inv = true;
        } else {
            $color_inv = env('INV_COLOR', "0");
            $color_inv = ($color_inv == '1' || $color_inv == 'true');
        }

        // Definir dimensiones y colores
        $ancho = 800; // $_GET['img_w']
        $alto  = 600; // antes 1280

        $colors = [ 
            'white' => [255,255,255],
            'black' => [0,0,0],
            'steelblue' => [70,130,180]
        ];

        $font_1 = ASSETS_PATH . 'fonts/Swiss 721 Light BT.ttf';
        $font_2 = ASSETS_PATH . 'fonts/Swiss721BT-Light.otf';

        if ($row_count > 5){
            $alto *= intval($row_count/4); 
        }

        if ($boxes_per_row > 22){
            $ancho *= intval($boxes_per_row/22);
        }

        $margin_r  = max(intval($ancho * 0.1), 150);

        //////////////////////////////////

        // Crear una nueva imagen
        $im = new Imaginator($ancho, $alto);

        if ($color_inv){
            $im->invertColors();
        }
       
        // Create some colors
        foreach ($colors as $color_name => $color_value){
            $im->createColor($color_name, ...$color_value);
        }

        // Definir color de fondo
        $im->setBackgroundColor('white');

        // Defino color defecto de pincel
        #$im->setForegroundColor('steelblue');
        
        /*
            Defino formas personalizadas
        */

        $im->setShape('row', function($cells_per_row, $x1, $y1, $w, $h, $color = null, bool $filled = false, $x_sp) use($im) {
            if ($color == null){
                $color = $im->getForegroundColor();
            }

            foreach (range(0, $cells_per_row-1) as $c ){
                $x1 += $x_sp + $w;
                $im->rectangle($x1, $y1, $w, $h, $color, $filled);       
            }
        });


        /*
            Ej:

            $im->multipleRow(2, $boxes_per_row, $x, $y + 200, $w, $h, null, true, 2, 2);
        */
        $im->setShape('multipleRow', function($n, $cells_per_row, $x1, $y1, $w, $h, $color=null, bool $filled=false, $x_sp=0, $y_sp=0) use($im) {
            foreach (range(0,$n-1) as $i){
                $im->row($cells_per_row, $x1, $y1 + (($h + $y_sp) * $i), $w, $h, $color, $filled, $x_sp);
            }
        });        


        $x = 1000;
        $y = 50;
        $w = 30;
        $h = 20;
   
        $interline     = ($alto - 150)/ ($row_count);  
        $x             = ($ancho - $margin_r) - ($w * $boxes_per_row);   

        /*
            Vertical lines
        */

        $x_end = ($ancho - $margin_r) + $w;
        $x_ini = $x_end - ($boxes_per_row * $w);
        $x_med = intval(($x_end + $x_ini) * 0.5);

        // duplas considerando que la primera linea y la ultima formarian otra
        $duos  = ($row_count-1);

        // Altura de todo el arreglo
        $y_dif = ($duos * $h) + ($interline * ($row_count-1)) - 1   - ($row_count -2) * $h -$h;

        // Ancho total especificado (convertido a pixels) por el usuario como parametro
        $y_usr = intval($y_dif * $w_feets / M::toFeet($w_acc));

        // Middle line
        $im->line($x_med, $y, 0, $y_dif, null, true);

        // Line at the right
        $im->line($x_end + 20, $y, 0, $y_usr);
   
        /*
            Rows
        */

        $im->multipleRow(1, $boxes_per_row, $x, $y, $w, $h);
        $w_acc = $w;

        for ($i=1; $i<$row_count; $i++){
            $multi = ($i == $row_count-1) ? 1 : 2;
            $im->multipleRow($multi, $boxes_per_row, $x, $y + $interline * $i -$h, $w, $h);

            $w_acc += M::toInches($aisle) + ($w * $multi); 
        }

        /*
            Texts
        */

        // Numero que aparece al centro y totaliza
        $im->text($x_med - 12, $y - 6, M::toFeetAndInches($bl_with_margins),   null, $font_2, 15);

        // Numero que aparece abajo de la primera celda
        $im->text($x + $w + 2 - 5 * strlen((string) $beam_length), $y + $w + 12, "$beam_length''",   null, $font_2, 15); 

        // Numero que aparece a la izquierda de la primera celda
        $im->text($x - 2, $y + $h  -3, "$upright_depth''"              , null, $font_2, 15);

        // Numero que aparece apaisado del lado derecho
        $im->text($x_end + 45, $y + floor($y_usr / 2), $w_feets . "'",   null, $font_2, 15, 90);

        // Leyendas de los pasillos (aisle)
        $lbl = M::toFeetAndInches($aisle);
        for ($i=0; $i<$row_count -1; $i++){
            $im->text($x_med - 20 - strlen($lbl) * 6, $y + $interline * ($i+0.5) +  0.5 * $h , $lbl, null, $font_2, 15);
        }
        
        // ...

        $im->render();                      
    }
}
