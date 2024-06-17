<?php

/*
    @author  Pablo Bozzolo boctulus@gmail.com

    /c/249764e5-9720-4f8d-8fa4-d88f56ef149c
*/

namespace boctulus\SW\core\libs;

use boctulus\SW\core\libs\Posts;

class TutorLMS 
{
    const EVERY_COURSE = -1;

    static function getCourses(string $post_status = 'publish', $fields = []){
        if (empty($fields)){
            $fields = ['ID', 'post_title','post_date'];
        }

        return table('posts')
        ->where([
            'post_type'   => 'courses'
        ])
        ->when(!empty($post_status), function($o) use ($post_status) {
            $o->where([
                'post_status' => $post_status
            ]);
        })
        ->when(!empty($fields), function($o) use ($fields){
            $o->select($fields);
        }) 
        ->get();
    }

    // OK
    static function courseExists($course_id){
        return (get_post($course_id) != null);
    }

    // OK
    static function getEnrollments($user_id = null, $course_id = null){
        $data = [
            'post_type'   => 'tutor_enrolled'
        ];

        if (!empty($user_id)){
            $data['post_author'] = $user_id;
        }

        if (!empty($course_id)){
            $data['post_parent'] = $course_id;
        }

        return table('posts')
        ->where($data)
        ->select([
            'ID', 'post_title', 'post_parent as course_id', 'post_author as student_id', 'post_date'
        ])
        ->get();
    }

    // OK
    static function isUserEnrolled($user_id, $course_id) {
        return table('posts')->where([
            'post_type'   => 'tutor_enrolled',
            'post_author' => $user_id,
            'post_parent' => $course_id,
            'post_status' => 'completed'
        ])->exists();
    }

    static function isCancelled($user_id, $course_id) {
        return table('posts')->where([
            'post_type'   => 'tutor_enrolled',
            'post_author' => $user_id,
            'post_parent' => $course_id,
            'post_status' => 'cancel'
        ])->exists();
    }

    /*
        OK
    */
    public static function enrollUser($user_id, $course_id, $product_id = null, $order_id = null) 
    {
        // Verificar si el usuario existe
        if (!get_user_by('id', $user_id)) {
            throw new \Exception("El usuario con ID = '$user_id' no existe. No se lo puede enrollar.");
        }

        // Verificar si el curso existe
        if (!static::courseExists($course_id)) {
            throw new \Exception("El curso con ID = '$course_id' no existe. No se lo puede enrollar a user_id = '$user_id'");
        }

        // Verificar si el usuario ya está enrollado en el curso
        if (self::isUserEnrolled($user_id, $course_id)) {
            Logger::log("User ID = '$user_id' ya habia sido enrollado para Course ID = '$course_id");
            return true;
        }
        
        // Enroll user
        $title = __('Course Enrolled', 'tutor') . " &ndash; " . date_i18n(get_option('date_format')) . ' @ ' . date_i18n(get_option('time_format'));

        $data = array(
            'post_type'     => 'tutor_enrolled',
            'post_title'    => $title,
            'post_author'   => $user_id,
            'post_parent'   => $course_id,
            'post_status'   => 'completed'  // approved
        );

        $enroll_data = apply_filters('tutor_enroll_data', $data);
    
        // Insert the post into the database
        $isEnrolled = wp_insert_post($enroll_data);
        update_user_meta($user_id, '_is_tutor_student', time());

        if ($order_id !== null && $product_id !== null){
            update_post_meta( $isEnrolled, '_tutor_enrolled_by_order_id', $order_id );
            update_post_meta( $isEnrolled, '_tutor_enrolled_by_product_id', $product_id );
            update_post_meta( $order_id, '_is_tutor_order_for_course', time() );
            update_post_meta( $order_id, '_tutor_order_for_course_id_'.$course_id, $order_id );
        }	    
        
        return true;
    }

    /*
        Ej:

        TutorLMS::cancelEnrollment(30, 16);

        Nota:

        Si se envia -1 en $course_id elimina todos los enrollments
    */
    public static function cancelEnrollment($user_id, $course_id, bool $permanent = false) 
    {
        $data = [
            'post_type'   => 'tutor_enrolled',
            'post_author' => $user_id            
        ];

        if ($course_id != static::EVERY_COURSE){
            $data['post_parent'] = $course_id;
        }

        $pids = table('posts')
        ->where($data)
        ->pluck('ID');

        if ($pids == null){
            return;
        }

        foreach ($pids as $pid){
            if ($permanent){
                $ok = Posts::deleteByID($pid, true);
            } else {
                $ok = table('posts')
                ->where([
                    'post_type'   => 'tutor_enrolled',
                    'post_author' => $user_id,
                    'post_parent' => $course_id
                ])
                ->update([
                    'post_status' => 'cancel'
                ]);
            }            
        }
    }
    
    public static function getQuiz($id){
        $p = Posts::getPost($id);

        if ($p['post_type'] != 'tutor_quiz'){
            throw new \InvalidArgumentException("Quiz not found");
        }

        return $p;
    }
}


