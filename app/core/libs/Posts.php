<?php declare(strict_types=1);

    namespace boctulus\SW\core\libs;

    /*
        @author boctulus
    */

    if ( ! function_exists( 'wp_crop_image' ) ) {
        include_once ( __DIR__ . '/../../../../../wp-admin/includes/image.php' );
    }

    if ( ! function_exists( 'media_sideload_image' ) ) {
        include_once ( __DIR__ . '/../../../../../wp-admin/includes/media.php' );
    }

    if ( ! function_exists( 'download_url' ) ) {
        include_once ( __DIR__ . '/../../../../../wp-admin/includes/file.php' );
    }

    class Posts 
    {
        static $post_type   = 'post';
        static $cat_metakey = 'category';

        static function create($title, $content, $status = 'publish', $post_type = null){
            $status = $status ?? 'publish';
    
            $data = array(
                'post_title'    => $title,
                'post_content'  => $content,
                'post_status'   => $status,  
                'post_type'     => $post_type
            );
            
            // Insertar el nuevo evento
            $post_id = wp_insert_post($data);
            
            // Verificar si la inserción fue exitosa
            if (is_wp_error($post_id)) {
                Logger::logError("Error al crear CPT de tipo '$post_type'. Detalle: " . $post_id->get_error_message());
            }
    
            return $post_id;
        }

        /*
            Ej:

            $site_url = 'xxx bla bla';

            dd(Posts::exists([
                '_site_url' => $site_url
            ], [
                'category' => 'active'
            ], 'publish', 'wsevent'));

        */
        static function exists($metas = null, $taxonomy = null, $post_status = 'publish', $post_type = null) : bool {
            $args = array(
                'post_type'      => $post_type,
                'post_status'    => $post_status,
                'posts_per_page' => 1,
            );
        
            if ($taxonomy !== null) {
                $args['tax_query'] = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => key($taxonomy),
                        'field'    => 'slug',
                        'terms'    => current($taxonomy),
                    ),
                );
            }
        
            if ($metas !== null && is_array($metas)) {
                $meta_query = array('relation' => 'AND');
        
                foreach ($metas as $key => $value) {
                    $meta_query[] = array(
                        'key'       => $key,
                        'value'     => $value,
                        'compare'   => '=',
                    );
                    $meta_query[] = array(
                        'key'       => $key,
                        'value'     => $value,
                        'compare'   => 'BINARY',
                    );
                }
        
                if (isset($args['meta_query'])) {
                    $args['meta_query']['relation'] = 'AND';
                    $args['meta_query'][] = $meta_query;
                } else {
                    $args['meta_query'] = $meta_query;
                }
            }
        
            $query = new \WP_Query($args);
        
            return $query->have_posts();
        }
    
        static function setTaxonomy($pid, $category_name, $cat_slug){
            wp_set_object_terms($pid, $category_name , $cat_slug);
        }
    
        static function setCategory($pid, $category_name, $cat_slug = null){
            $cat_slug = $cat_slug ?? static::$cat_metakey;
    
            wp_set_object_terms($pid, $category_name , $cat_slug);
        }    

        static function getPostType($post_id) {
            // Obtener el objeto del post usando el ID
            $post = get_post($post_id);
        
            // Comprobar si se encontró el post y obtener el post_type
            if ($post) {
                $post_type = $post->post_type;
                return $post_type;
            } else {
                return false; // Si el post no existe, puedes manejarlo de acuerdo a tus necesidades.
            }
        }

        /*
            Puede que no sea la mejor forma porque se salta el mecanismo de cache
        */
        static function setStatus($pid, $status){
            global $wpdb;

            if ($pid == null){
                throw new \InvalidArgumentException("PID can not be null");
            }

            // Status ('publish', 'pending', 'draft' or 'trash')
            if (!in_array($status, ['publish', 'pending', 'draft', 'trash'])){
                throw new \InvalidArgumentException("Estado '$status' invalido.");
            }

            $sql = "UPDATE `{$wpdb->prefix}posts` SET `post_status` = '$status' WHERE `{$wpdb->prefix}posts`.`ID` = $pid;";
            return $wpdb->get_results($sql);    
        }

        // alias
        static function updateStatus($pid, $status){
            return static::setStatus($pid, $status);
        }

        static function setAsDraft($pid){
            static::setStatus($pid, 'draft');
        }

        static function setAsPublish($pid){
            static::setStatus($pid, 'publish');
        }

        static function trash($pid){
            return static::setStatus($pid, 'trash');
        }

        // 'publish'
        static function restore($pid){
            return static::setStatus($pid, 'publish');
        }

        static function getAttr($key = null){
            return post_custom($key);
        }

        /*
            Ej:
            
            Array
            (
                [_edit_lock] => Array
                    (
                        [0] => 1685355749:1
                    )

                [gdrive_actualizacion] => Array
                    (
                        [0] => 2000-10-10
                    )

            )
        */
        static function getAttrByID($id, $key = null){
            $attrs = get_post_custom($id);

            if (!empty($key)){
                return $attrs[$key] ?? null;
            }

            return $attrs;
        }

        static function addAttr($post_id, $attr_name, $attr_value){
            add_post_meta($post_id, $attr_name, $attr_value, true);
        }

        /*
            Ej de uso:

                $slug = 'aplique-espejo-impermeable-50-cm';
                Posts::getBySlug($slug, 'product')

            Retorna un array de propiedades

            [
                'ID' => 2295,
                'post_author' => '1',
                'post_date' => '2022-06-27 20:23:42',
                'post_date_gmt' => '2022-06-27 20:23:42',
                'post_content' => '',
                'post_title' => 'Aplique espejo impermeable 50 cm',
                'post_excerpt' => '',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_password' => '',
                'post_name' => 'aplique-espejo-impermeable-50-cm',
                'to_ping' => '',
                'pinged' => '',
                'post_modified' => '2022-07-11 17:00:20',
                'post_modified_gmt' => '2022-07-11 17:00:20',
                'post_content_filtered' => '',
                'post_parent' => 0,
                'guid' => 'http://woo1.lan/producto/aplique-espejo-impermeable-50-cm/',
                'menu_order' => 0,
                'post_type' => 'product',
                'post_mime_type' => '',
                'comment_count' => '0',
                'filter' => 'raw',
                'ancestors' =>
                array (
                ),
                'page_template' => '',
                'post_category' =>
                array (
                ),
                'tags_input' =>
                array (
                ),
            ]
        */

        static function getBySlug(string $slug, string $post_type = null, $post_status = 'publish')
        {
            if ($post_type == null){
                $post_type = static::$post_type;
            }

            $args = array(
            'name'        => $slug,
            'post_type'   => $post_type,
            'post_status' => $post_status,
            'numberposts' => 1
            );

            $arr = get_posts($args);

            return (empty($arr)) ? NULL : $arr[0]->to_array();
        }

        /*
            Version basada con consultas SQL

            Ej:

            $pids  = Products::getPosts(null, null, $limit, $offset, [
                '_downloadable' => 'yes'
            ]);

            No alterar el orden de los parametros ya que es usada en mutawp_admin !!!
        */
        static function getPosts($select = '*', $post_type = null, $post_status = null, $limit = null, $offset = null, $attributes = null, $order_by = null){
            global $wpdb;

            if (is_array($select)){
                // podria hacer un enclose con ``
                $select = implode(', ', $select);
            }
        
            if ($post_type == null){
                $post_type = static::$post_type;
            }
        
            $include_post_status = '';
            if ($post_status !== null){
                $include_post_status = "AND post_status = '$post_status'";
            }
        
            $attributes_condition = '';
            if (!empty($attributes) && is_array($attributes)) {
                foreach ($attributes as $key => $value) {
                    $attributes_condition .= "AND ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '$key' AND meta_value = '$value') ";
                }
            }
        
            $limit_clause = '';
            if ($limit !== null){
                $limit_clause = "LIMIT $limit";
            }
        
            $offset_clause = '';
            if ($offset !== null){
                $offset_clause = "OFFSET $offset";
            }

            $order_clause = 'ORDER BY ID ASC'; 
            if ($order_by !== null){
                $order_clause = "ORDER BY $order_by";
            }
        
            $sql = "SELECT $select FROM `{$wpdb->prefix}posts` WHERE ID <> 1 AND post_type = '$post_type' $include_post_status $attributes_condition $order_clause $limit_clause $offset_clause";
        
            $res = $wpdb->get_results($sql, ARRAY_A);   
        
            return $res;
        }

        /*
            Ej:

            Posts::getIDs('sfwd-question', 'publish', 5)
        */
        static function getIDs($post_type = null, $post_status = null, $limit = null, $offset = null, $attributes = null, $order_by = null){
            $res = static::getPosts('ID', $post_type, $post_status, $limit, $offset, $attributes, $order_by);
            return array_column($res, 'ID') ?? null;
        }

        static function getPost($id){
            return get_post($id, ARRAY_A);
        }

        /* 
            Podria haber implementacion mas eficiente con FULLSEARCH

            O usar 

            https://www.advancedcustomfields.com/resources/query-posts-custom-fields/
            https://qirolab.com/posts/example-of-wp-query-to-search-by-post-title-in-wordpress
        */
        static function search($keywords, $attributes = null, $select = '*', bool $include_desc = true, $post_type = null, $post_status = null, $limit = null, $offset = null) {
            global $wpdb;

            $tb = $wpdb->prefix . 'posts'; 

            if (is_array($select)){
                // podria hacer un enclose con ``
                $select   = implode(', ', $select);
            }

            $select_multi = Strings::contains(',', $select);

            if (!is_array($keywords) && Strings::contains('+', $keywords)){
                $keywords = explode('+', $keywords);
            }

            if (!is_array($keywords) && Strings::contains(' ', $keywords)){
                $keywords = explode(' ', $keywords);
            }

            if (!is_array($keywords)){
                $keywords = [ $keywords ];
            }

            $conds = [];
            foreach ($keywords as $ix => $keyword){
                $keyword = '%' . $wpdb->esc_like($keyword) . '%'; 
                $conds[] = "(" . "post_title LIKE '$keyword'" . ($include_desc ? " OR post_excerpt LIKE '$keyword'" : '') . ")";
            }

            $conditions = implode(' AND ', $conds);


            ////////////////////////////////////////////////

            if ($post_type == null){
                $post_type = static::$post_type;
            }
        
            $include_post_status = '';
            if ($post_status !== null){
                $include_post_status = "AND post_status = '$post_status'";
            }
        
            $attributes_condition = '';
            if (!empty($attributes) && is_array($attributes)) {
                foreach ($attributes as $key => $value) {
                    $attributes_condition .= "AND ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '$key' AND meta_value = '$value') ";
                }
            }

            $limit_clause = '';
            if ($limit !== null){
                $limit_clause = "LIMIT $limit";
            }
        
            $offset_clause = '';
            if ($offset !== null){
                $offset_clause = "OFFSET $offset";
            }

            ////////////////////////////////////////////////    

            $sql = "SELECT $select FROM `$tb` 
            WHERE 
            post_type = '$post_type' $include_post_status $attributes_condition
            AND post_status = 'publish' 
            AND ($conditions)
            ORDER BY ID ASC $limit_clause $offset_clause;";  

            $results = $wpdb->get_results($sql, ARRAY_A);

            if (!$select_multi){
                $results = array_column($results, trim($select));
            }

            return $results;
        }

        static function getLastPost($post_type = null, $post_status = null){
            global $wpdb;

            if ($post_type == null){
                $post_type = static::$post_type;
            }

            $include_post_status = '';
            if ($post_status !== null){
                $include_post_status = "AND post_status = '$post_status'";
            }

            $sql = "SELECT ID FROM `{$wpdb->prefix}posts` WHERE post_type = '$post_type' $include_post_status ORDER BY ID DESC LIMIT 1;";
            $res = $wpdb->get_results($sql, ARRAY_A);   

            return $res[0] ?? null;
        }

        static function getLastID($post_type = null, $post_status = null){
            if ($post_type == null){
                $post_type = static::$post_type;
            }

            $post = static::getLastPost($post_type, $post_status);

            if ($post == null){
                return null;
            }

            return (int) $post['ID'] ?? null;
        }

        static function getAll($post_type = null, $status = 'publish', $limit = -1, $order = null){
            global $wpdb;

            if ($post_type == null){
                $post_type = static::$post_type;
            }
            
            $sql = "SELECT SQL_CALC_FOUND_ROWS  * FROM {$wpdb->prefix}posts  WHERE 1=1  AND (({$wpdb->prefix}posts.post_type = '$post_type' AND ({$wpdb->prefix}posts.post_status = '$status')));";
        
            return $wpdb->get_results($sql, ARRAY_A);
        }
        
        static function getOne($post_type = null, $status = 'publish', $limit = -1, $order = null){
            global $wpdb;

            if ($post_type == null){
                $post_type = static::$post_type;
            }
            
            $sql = "SELECT SQL_CALC_FOUND_ROWS  * FROM {$wpdb->prefix}posts WHERE 1=1 AND (({$wpdb->prefix}posts.post_type = '$post_type' AND ({$wpdb->prefix}posts.post_status = '$status')));";
        
            return $wpdb->get_row($sql, ARRAY_A);
        }    

        /*
            Retorna Posts contienen determinado valor en una meta_key

            Uso:

                Posts::getByMeta('_Quiz name', 'examen-clase-b', 'sfwd-question')
                Posts::getByMeta('_Quiz name', 'examen-clase-b', 'sfwd-question', null, 2, null, 'RAND()')
                Posts::getByMeta('_Quiz name', 'examen-clase-b', 'sfwd-question', null, 2, null, 'RAND()', 'ID,post_content');
                etc.       

            Genera query como:

                SELECT p.*, pm.* FROM wp_postmeta pm
                LEFT JOIN wp_posts p ON p.ID = pm.post_id 
                WHERE  
                    pm.meta_key   = '_Quiz name' 
                AND pm.meta_value = 'examen-clase-b'
                AND p.post_type   = 'sfwd-question'
                AND p.post_status = 'publish'            
        
        */
        static function getByMeta($meta_key, $meta_value, $post_type = null, $post_status = null, $limit = null, $offset = null, $order_by = null, $select = '*') {
            global $wpdb;
            
            $meta_key = Strings::toSlug($meta_key);

            if ($select != '*'){
                if (is_array($select)){
                    // podria hacer un enclose con ``
                    $select = implode(', ', $select);
                }
                // bool
                $select_multi = Strings::contains(',', $select);
            } else {
                $select = 'p.*, pm.*';
            }

            if ($post_type == null){
                $post_type = static::$post_type;
            }

            $limit_clause = '';
            if ($limit !== null){
                $limit_clause = "LIMIT $limit";
            }

            $offset_clause = '';
            if ($offset !== null){
                $offset_clause = "OFFSET $offset";
            }

            $order_clause = '';
            if ($order_by !== null){
                $order_clause = "ORDER BY $order_by";
            }

            if (!Strings::startsWith('_', $meta_key)){
                $meta_key = '_' . $meta_key;
            }

            $sql = "SELECT $select FROM {$wpdb->prefix}postmeta pm
            LEFT JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id 
            WHERE  
            pm.meta_key = '%s' 
            AND pm.meta_value='%s'";

            $sql_params = array($meta_key, $meta_value);

            if ($post_type !== null) {
                $sql .= " AND p.post_type = %s";
                $sql_params[] = $post_type;
            }

            if ($post_status !== null) {
                $sql .= " AND p.post_status = %s";
                $sql_params[] = $post_status;
            }

            $sql .= " $order_clause $limit_clause $offset_clause";

            $results = $wpdb->get_results($wpdb->prepare($sql, $sql_params), ARRAY_A);
            
            return $results;
        }

        /*
            Retorna la cantidad de productos contienen determinado valor en una meta_key
        */
        static function countByMeta($meta_key, $meta_value, $post_type = null, $post_status = 'publish' ) {
            global $wpdb;

            if ($post_type == null){
                $post_type = static::$post_type;
            }

            if (!Strings::startsWith('_', $meta_key)){
                $meta_key = '_' . $meta_key;
            }

            /*
                SELECT COUNT(*) FROM wp_postmeta pm
                LEFT JOIN wp_posts p ON p.ID = pm.post_id 
                WHERE p.post_type = 'product' 
                AND pm.meta_key = '_forma_farmaceutica' 
                AND pm.meta_value='crema'
                AND p.post_status = 'publish'
                ;
            */   

            $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta pm
            LEFT JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id 
            WHERE  
            pm.meta_key = '%s' 
            AND pm.meta_value='%s'";

            $sql_params = array($meta_key, $meta_value);

            if ($post_type !== null) {
                $sql .= " AND p.post_type = %s";
                $sql_params[] = $post_type;
            }

            if ($post_status !== null) {
                $sql .= " AND p.post_status = %s";
                $sql_params[] = $post_status;}

            $r = (int) $wpdb->get_var($wpdb->prepare($sql, $post_type, $meta_key, $meta_value, $post_status));
        
            return $r;
        }

        static function deleteMeta($post_id, $meta_key){
            if (!Strings::startsWith('_', $meta_key)){
                $meta_key = '_' . $meta_key;
            }

            delete_post_meta($post_id, $meta_key);
        }

        /*
            Uso. Ej:

            Products::getTaxonomyFromTerm('Crema')

            retorna

            array (
                0 => 'pa_forma_farmaceutica',
                1 => 'pa_dosis',
            )
        */
        static function getTaxonomyFromTerm(string $term_name){
            global $wpdb;

            /*  
                SELECT * FROM wp_terms AS t 
                LEFT JOIN wp_termmeta AS tm ON t.term_id = tm.term_id 
                LEFT JOIN wp_term_taxonomy AS tt ON tt.term_id = t.term_id
                WHERE t.name = 'Crema'
            */

            $sql = "SELECT taxonomy FROM {$wpdb->prefix}terms AS t 
            LEFT JOIN {$wpdb->prefix}termmeta AS tm ON t.term_id = tm.term_id 
            LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_id = t.term_id
            WHERE name = '%s'";

            $r = $wpdb->get_col($wpdb->prepare($sql, $term_name));
        
            return $r;
        }

        static function getTermIdsByTaxonomy(string $taxonomy, $prefix = 'pa_'){
            global $wpdb;

            if ($prefix !== false && !Strings::startsWith($prefix, $taxonomy)){
                $taxonomy = $prefix . $taxonomy;
            }

            $sql = "SELECT term_id FROM `{$wpdb->prefix}term_taxonomy` WHERE `taxonomy` = '$taxonomy';";

            return $wpdb->get_col($sql);
        }

        /*
            Devolucion de array de metas incluidos atributos de productos

            array (
                '_sku' =>
                array (
                    0 => '7800063000770',
                ),
                '_regular_price' =>
                array (
                    0 => '2790',
                ),

                // ...
                
                '_product_attributes' =>
                array (
                    0 => 'a:0:{}',
                ),
                
                // ...

                '_laboratorio' =>
                array (
                    0 => 'Mintlab',
                ),
                '_enfermedades' =>
                array (
                    0 => 'Gripe',
                ),        
            )

            Si $single es true, en vez de devolver un array, se devuelve un solo valor,
            lo cual tiene sentido con $key != ''
        */
        static function getMetasByID($pid, $meta_key = '', bool $single = false){
            if (!empty($meta_key)){
                if (!Strings::startsWith('_', $meta_key)){
                    $meta_key = '_' . $meta_key;
                }
            }

            return get_post_meta($pid, $meta_key, $single);
        }

        /*  
            Get metas "ID" by meta key y value 
        */
        static function getMetaIDs($meta_key, $dato){
            global $wpdb;
        
            // Asegurarse de que la clave de metadatos esté precedida por un guión bajo
            if (!Strings::startsWith('_', $meta_key)){
                $meta_key = '_' . $meta_key;
            }
        
            // Preparar la consulta SQL para buscar el ID de la meta
            $query = $wpdb->prepare(
                "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s",
                $meta_key,
                $dato
            );
        
            // Ejecutar la consulta
            $result = $wpdb->get_results($query);
        
            return $result;
        }    

        static function setMeta($post_id, $meta_key, $dato, bool $sanitize = false){
            $meta_key = ltrim($meta_key, '_');
            $meta_key = '_' . Strings::toSlug($meta_key);

            if ($sanitize){
                $dato = sanitize_text_field($dato);
            }

            update_post_meta( $post_id, $meta_key, $dato); 
        }

        static function getMeta($post_id, $meta_key){
            $meta_key = ltrim($meta_key, '_');
            $meta_key = '_' . Strings::toSlug($meta_key);      

            return get_post_meta($post_id, $meta_key, true);
        }

        /*
            Devuelve si un termino existe para una determinada taxonomia

            Nota: atributos re-utilizables de productos variables son "terms" tambien
        */
        static function termExists($term_name, string $taxonomy){
            if (!Strings::startsWith('pa_', $taxonomy)){
                $taxonomy = 'pa_' . $taxonomy;
            }
            
            return (term_exists($term_name, $taxonomy) !== null);
        }

        
        static function getTermBySlug($slug){
            global $wpdb;

            $sql = "SELECT * from `{$wpdb->prefix}terms` WHERE slug = '$slug'";
            return $wpdb->get_row($sql);
        }

        static function getTermById(int $id){
            // global $wpdb;

            // $sql = "SELECT * from `{$wpdb->prefix}terms` WHERE term_id = '$id'";
            // return $wpdb->get_row($sql);

            return get_term($id);
        }

        /*
            Delete Attribute Term by Name

            Borra los terminos agregados con insertAttTerms() de la tabla 'wp_terms' por taxonomia (pa_forma_farmaceutica, etc)
        */
        static function deleteTermByName(string $term_name, string $taxonomy, $args = []){
            if (!Strings::startsWith('pa_', $taxonomy)){
                $taxonomy = 'pa_' . $taxonomy;
            }

            $term_ids = static::getTermIdsByTaxonomy($taxonomy);

            foreach ($term_ids as $term_id){
                wp_delete_term($term_id, $taxonomy, $args);
            }
        }

        /*
            Categories
        */

        static function deleteCategories($cat_taxonomy = null) {
            $cat_taxonomy = static::$cat_metakey ?? $cat_taxonomy;

            $term_ids     = static::getTermIdsByTaxonomy($cat_taxonomy, false);

            foreach ($term_ids as $term_id) {
                wp_delete_term($term_id, $cat_taxonomy);
            }
        }

        static function createCatego($name, $slug = null, $description = null, $id_parent = null)
        {    
            $category_id = wp_insert_term(
                $name, // the term 
                static::$cat_metakey, // the taxonomy
                array(
                    'description'=> $description,
                    'slug'       => $slug,
                    'parent'     => $id_parent
                )
            );

            return $category_id;
        }

        static function createOrUpdateCatego($name, $slug = null, $description = null, $id_parent = null)
        {
            $id = static::getCategoryIdByName($name);

            if ($id !== null){
                //dd("Updating ... $name");

                $cid = wp_update_term(
                    $id, 
                    static::$cat_metakey, // the taxonomy
                    array(
                        'description'=> $description,
                        'slug' => $slug,
                        'parent' => $id_parent
                    )
                );
            } else {
                //dd("Creating ... $name");

                $cid = wp_insert_term(
                    $name, // the term 
                    static::$cat_metakey, // the taxonomy
                    array(
                        'description'=> $description,
                        'slug'       => $slug,
                        'parent'     => $id_parent
                    )
                );
            }

            // category_id
            return $cid;
        }


        static function getCategoryIdByName($name){
            $category = get_term_by( 'slug', $name, static::$cat_metakey );

            if ($category === null || $category === false){
                return null;
            }

            return $category->term_id;
        }

        /*
            $only_subcategos  determina si las categorias de primer nivel deben o no incluirse 
        */
        static function getCategories(bool $only_subcategos = false){
            static $ret;

            if ($ret !== null){
                return $ret;
            }

            $taxonomy     = static::$cat_metakey;
            $orderby      = 'name';  
            $show_count   = 1;      // 1 for yes, 0 for no
            $pad_counts   = 0;      // 1 for yes, 0 for no
            $hierarchical = 1;      // 1 for yes, 0 for no  
            $title        = '';  
            $empty        = 0;

            $args = array(
                    'taxonomy'     => $taxonomy,
                    'orderby'      => $orderby,
                    'show_count'   => $show_count,
                    'pad_counts'   => $pad_counts,
                    'hierarchical' => $hierarchical,
                    'title_li'     => $title,
                    'hide_empty'   => $empty
            );
            
            $all_categories = get_categories( $args );

            if (!$only_subcategos){
                return $all_categories;    
            }

            $ret = [];
            foreach ($all_categories as $cat) {
                if($cat->category_parent == 0) {
                    $category_id = $cat->term_id;       
                    $link = '<a href="'. get_term_link($cat->slug, static::$cat_metakey) .'">'. $cat->name .'</a>';

                    $args2 = array(
                            'taxonomy'     => $taxonomy,
                            'child_of'     => 0,
                            'parent'       => $category_id,
                            'orderby'      => $orderby,
                            'show_count'   => $show_count,
                            'pad_counts'   => $pad_counts,
                            'hierarchical' => $hierarchical,
                            'title'        => $title,
                            'hide_empty'   => $empty
                    );
                    $sub_cats = get_categories( $args2 );

                    if($sub_cats) {
                        foreach($sub_cats as $sub_category) {
                            $ret[] = $sub_category;
                        }   
                    } 
                }       
            }

            return $ret;
        }

        static function getCategoSlugs(){
            $categos = static::getCategories();
        
            $ret = [];
            foreach ($categos as $catego){
                $ret[] = $catego->slug;
            }
        
            return $ret;
        }    
        
        static function getCategoryChildren($category_id){
            return get_term_children($category_id, static::$cat_metakey);
        }

        static function getCategoryChildrenBySlug($category_slug){
            $cat = static::getTermBySlug($category_slug);

            if ($cat === null){
                return null;
            }

            $category_id = $cat->term_id;
            return static::getCategoryChildren($category_id);
        }

        /*
            https://devwl.pl/wordpress-get-all-children-of-a-parent-product-category/
        */
        static function getTopLevelCategories(){
            global $wpdb;

            $cat_metakey = static::$cat_metakey;

            $sql = "SELECT t.term_id, t.name, t.slug,  tt.taxonomy, tt.parent, tt.count FROM {$wpdb->prefix}terms t
            LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_taxonomy_id
            WHERE tt.taxonomy = '$cat_metakey' 
            AND tt.parent = 0
            ORDER BY tt.taxonomy;";

            return $wpdb->get_results($sql);
        }

        static function getAllCategories(bool $only_ids = false){
            global $wpdb;

            $cat_metakey = static::$cat_metakey;

            $sql = "SELECT t.term_id, t.name, t.slug,  tt.taxonomy, tt.parent, tt.count 
            FROM {$wpdb->prefix}terms t
            LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_taxonomy_id
            WHERE tt.taxonomy = '$cat_metakey' 
            ORDER BY tt.taxonomy;";

            $res = $wpdb->get_results($sql, ARRAY_A);

            if ($only_ids){
                return array_column($res, 'term_id');
            }

            return $res;
        }
    
        /*
            Dado el id de una categoria devuelve algo como

            A > A2 > A2-1 > A2-1a
        */
        static function breadcrumb(int $cat_id){
            $search = $cat_id;

            $path = [];
            $parent_id = null;

            while($parent_id !== 0){
                $catego     = get_term($cat_id);
                $parent_id = $catego->parent;

                if ($parent_id == 0){
                    break;
                }

                $path[] = get_the_category_by_ID($parent_id);            
                $cat_id = $parent_id;
            }

            $path       = array_reverse($path);
            $last_sep   = empty($path) ? '' : '>';
            $first_sep  = '/';
            
            $breadcrumb = ltrim($first_sep . ' '). ltrim(implode(' > ', $path) . " $last_sep " . get_the_category_by_ID($search));
        
            return $breadcrumb;
        }

        static function getCategoryNameByID($cat_id){
            if( $term = get_term_by('id', $cat_id, static::$cat_metakey) ){
                return $term->name;
            }

            throw new \InvalidArgumentException("Category ID '$cat_id' not found");
        }   

        /*
            Images
        */

        static function getImages($pid, $featured_img = false){
            $images = get_attached_media('image', $pid);

            if ($featured_img === false){
                $urls = [];
                foreach ($images as $img){
                    $urls[] = $img->guid;
                }

                return $urls;
            }

            // Obtener la URL de la imagen destacada si está definida
            $featured_img_id = get_post_thumbnail_id($pid);
            if ($featured_img_id) {
                $featured_img_url = wp_get_attachment_image_src($featured_img_id, 'full')[0];
                return $featured_img_url;
            }

            return null; // No se encontró imagen destacada
        }

        static function getAttachmentIdFromSrc($image_src) {
            global $wpdb;

            $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
            $id = $wpdb->get_var($query);
            return $id;    
        }

        /*
            Otra implentación:

            https://wordpress.stackexchange.com/questions/64313/add-image-to-media-library-from-url-in-uploads-directory
        */
        static function uploadImage($imageurl, $title = '', $alt = '', $caption = '')
        {
            if (empty($imageurl)){
                return;
            }

            if (strlen($imageurl) < 10 || !Strings::startsWith('http', $imageurl)){
                throw new \InvalidArgumentException("Image url '$imageurl' is not valid");
            }

            $att_id = static::getAttachmentIdFromSrc($imageurl);
            if ( $att_id !== null){
                return $att_id;
            }

            $size = getimagesize($imageurl)['mime'];
            $f_sz = explode('/', $size);
            $imagetype = end($f_sz);
            $uniq_name = date('dmY').''.(int) microtime(true); 
            $filename = $uniq_name.'.'.$imagetype;

            $uploaddir  = wp_upload_dir();
            $uploadfile = $uploaddir['path'] . '/' . $filename;

            // mejor,
            // Files::file_get_contents_curl($imageurl)
            $contents   = file_get_contents($imageurl);

            if (empty($contents)){
                return;
            }

            $savefile = fopen($uploadfile, 'w');
            $bytes    = fwrite($savefile, $contents);
            fclose($savefile);

            if (empty($bytes)){
                return;
            }

            $wp_filetype = wp_check_filetype(basename($filename), null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title'     => $filename,
                'post_content'   => '',
                'post_status'    => 'inherit',
                'guid'           => $imageurl,
                'title'          => $title,
                'alt'            => $alt,
                'caption'        => $caption
            );

            $att_id = wp_insert_attachment( $attachment, $uploadfile );

            if (empty($att_id)){
                return;
            }

            $imagenew = get_post( $att_id );
            $fullsizepath = get_attached_file( $imagenew->ID );
            $attach_data = wp_generate_attachment_metadata( $att_id, $fullsizepath );
            wp_update_attachment_metadata( $att_id, $attach_data ); 

            return $att_id;
        }

        static function setImagesForPost($pid, Array $image_ids){
            //dd("Updating images for post with PID $pid");
            $image_ids = implode(",", $image_ids);
            update_post_meta($pid, '_product_image_gallery', $image_ids);
        }

        // Setea imagen destacada
        static function setDefaultImage($pid, $image_id){
            update_post_meta( $pid, '_thumbnail_id', $image_id );
        }

        /*
            Borra imagenes de la Galeria de Medios para un determinado post

            Otra implementación:

            https://wpsimplehacks.com/how-to-automatically-delete-woocommerce-images/
        */
        static function deleteGaleryImages($pid)
        {
            // Delete Attachments from Post ID $pid
            $attachments = get_posts(
                array(
                    'post_type'      => 'attachment',
                    'posts_per_page' => -1,
                    'post_status'    => 'any',
                    'post_parent'    => $pid,
                )
            );

            foreach ($attachments as $attachment) {
                wp_delete_attachment($attachment->ID, true);
            }        
        }

        static function deleteAllGaleryImages()
        {
            global $wpdb;

            $wpdb->query("DELETE FROM `{$wpdb->prefix}posts` WHERE `post_type` = \"attachment\";");
            $wpdb->query("DELETE FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = \"_wp_attached_file\";");
            $wpdb->query("DELETE FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` = \"_wp_attachment_metadata\";");
        }       

    }