<?php

namespace boctulus\SW\controllers;

use boctulus\SW\core\libs\Files;
use boctulus\SW\core\libs\Logger;
use boctulus\SW\core\libs\Metabox;
use boctulus\SW\core\libs\Strings;
use boctulus\SW\core\libs\LearnDash;

class ImportController
{
    function index()
    {
        dd("Bienvenido al importer");
    }

    function purge_all(){
        LearnDash::purgeAllQuizes();
        dd("Todos los QUIZes fueron purgados!");
    }

    // importa
    function run()
    {
        /*
            CSV

            Se tuvieron que cambiar algunos caracteres de la cabecera
        */

        $quiz_name = 'examen-clase-b';               // <--- debe especificarse
        $filename  = 'BASE DE DATOS PRUEBA ACT.csv'; // <--- debe especificarse

        $quiz_id = LearnDash::getQuestionIDsByQuizName($quiz_name);
        $path    = ETC_PATH . $filename;

        // dd($quiz_id, 'QUIZ ID'); exit;
        $quiz_id = 178; // HARDCODED

        if ($quiz_id === null){
            dd("Por favor cree un Quiz cuyo nombre sea 'Examen Clase B' previamente");
            exit;
        }
        
        $rows = Files::getCSV($path)['rows'];

        foreach ($rows as $key => $row) {
            # Pregunta
            $question = $row['PREGUNTA'];

            if (empty($question)){
                // dd("Skiping empty question");
                continue;
            }

            $question_title   = $question;
            $tip              = $row['PISTA'];
            $explanation      = $row['EXPLICACION'];
            $ext_img_url      = $row['IMAGEN URL'];
            $total_points     = 1; 	

            // Elimino algunos caracteres que no corresponden y normalizo
            $s_right_answers  = strtoupper(trim($row['LETRA_RESPUESTA_CORRECTA']));
            $s_right_answers  = preg_replace('/[^ABCDEF, ]/', '', $s_right_answers);
            
            $right_answers_ay = explode(',', $s_right_answers);


            $answers = [];

            # Respuestas
            foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $letter){
                if (isset($row['RESPUESTA_' . $letter]) && !empty($row['RESPUESTA_' . $letter])){
                    $answers[] = [
                        'text' => $row['RESPUESTA_' . $letter],
                        'is_correct' => (in_array($letter, $right_answers_ay)),
                        'points' => 1 // luego se cambiara en tiempo real
                    ];
                }	
            }
            
            $question_id = LearnDash::createQuestion($quiz_id, $question, $question_title, $tip, $ext_img_url, $answers, $total_points);

            /*
                Agrego explicacion
            */

            Metabox::set($question_id, 'explanation', Strings::convertEncoding($explanation));
            
            // if (!empty(trim($explanation))){
            //     dd("Para question_id = $question_id se esta seteando explicacion: " . Strings::convertEncoding($explanation));
            //     exit;
            // }

            dd($question_id, 'QUESTION ID'); 

            // break;//
        }

        dd('DONE');                                                                                                                                                                                                                                                                                                                                                                      
    }
}
