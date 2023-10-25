<?php declare(strict_types=1);

namespace boctulus\SW\core\libs;

/*
	El plugin Multivendor setea el "vendor" asignando un author_id al post correspondiente al "vendor"

	'post_author' => '3',

	Donde el "vendor slug" es el `user_nicename` de la tabla `wp_users`

	Para distinguir el plugin de vendor activado verificar con class_exists("WCFMmp_Product")

*/

class WCFM
{
    static function getCurrentVendor($post_id){
        global $wpdb;

        $sql = "SELECT `user_nicename` FROM `{$wpdb->prefix}users` WHERE `id` = $post_id;";
        $vendor_slug  = $wpdb->get_var($sql);

        return $vendor_slug;
    }

    //  ok
    static function updateVendor($vendor_slug, $pid){
        global $wpdb;
        
        $vendor_slug = sanitize_user($vendor_slug);

        $sql = "SELECT id FROM `{$wpdb->prefix}users` WHERE `user_nicename` = '$vendor_slug';";
        $user_id  = $wpdb->get_var($sql);

        if (empty($user_id)){
            return false;
        }

        $arg = array(
            'ID' => $pid,
            'post_author' => $user_id,
        );

        return wp_update_post( $arg );
    }

    static function isActive()
    {
        //return class_exists("WCFMmp_Product");
        
        include_once (ABSPATH.'wp-admin/includes/plugin.php');
        return is_plugin_active('wc-multivendor-marketplace/wc-multivendor-marketplace.php');
    }

}