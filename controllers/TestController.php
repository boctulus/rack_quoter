<?php

namespace boctulus\SW\controllers;

use boctulus\SW\core\libs\DB;
use boctulus\SW\core\libs\Strings;

class TestController
{
    function index(){
        /*
	        Devolver los IDs de las preguntas que contienen chr(0x96)
            a fin de poder editar manualmente las respuestas
        */

        global $wpdb;
        $wp = $wpdb->prefix;

        DB::getConnection();

        $rows  = DB::select("SELECT * FROM {$wp}learndash_pro_quiz_question WHERE 1");
        $answer_ids = array_column($rows, 'id');

        foreach ($answer_ids as $ix => $a_id){
            $data = $rows[$ix]['answer_data'];
            
            if (Strings::contains(chr(0x96), $data)){
                // dd($a_id, "answer_id en {$wp}learndash_pro_quiz_question que contiene caracter especial");

                /*
                    Ahora, necesitas encontrar el ID de la pregunta que contiene esta respuesta.
                    Puedes buscar en la base de datos a partir de la tabla de puentes que conecta las preguntas con las respuestas.

                    Suponiendo que la tabla de puentes se llame '{$wp}postmeta' y el campo meta_key sea 'question_pro_id' y el campo post_id sea el ID de la respuesta,
                    puedes hacer lo siguiente para encontrar el ID de la pregunta relacionada:

                    */

                global $wpdb;

                $question_id = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT post_id FROM {$wp}postmeta WHERE meta_key = 'question_pro_id' AND meta_value = %d",
                        $a_id
                    )
                );

                if ($question_id) {
                    dd("https://rodrigocamposhernandez.cl/wp-admin/post.php?post=$question_id&action=edit");
                } 
            }
        }


    }

}
