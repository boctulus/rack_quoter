<?php declare(strict_types=1);

namespace boctulus\SW\core\libs;

use boctulus\SW\core\traits\PostCategoriesTrait;  
use boctulus\SW\core\traits\NavMenuTrait;

/*
    @author boctulus
*/

class Posts
{
    use PostCategoriesTrait;
    use NavMenuTrait;
    
    static $post_type   = 'post';
    static $cat_metakey = 'category';

    // 6-ene-24
    static function getByID(int $term_id)
    {
        return get_term_by('id', $term_id, static::$cat_metakey);
    }

    static function getRandom($qty = 1, bool $ret_pid = false){
        global $wpdb;

        if (empty($qty) || $qty < 0){
            throw new \InvalidArgumentException("Quantity can not be 0 or null or negative");
        }

        $post_type = static::$post_type;
        $sql       = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type IN ('{$post_type}') ORDER BY RAND() LIMIT $qty";

        $res = $wpdb->get_results($sql, ARRAY_A);

        return $ret_pid ? array_column($res, 'ID') : $res;
    }

    /*
        Devuelve la lista de post_type(s)
    */
    static function getPostTypes(){
        $prefix = tb_prefix();

        DB::getConnection();

        return DB::select("SELECT DISTINCT post_type FROM {$prefix}posts;");
    }

    static function create($title, $content, $status = 'publish', $post_type = null)
    {
        $status = $status ?? 'publish';

        $data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => $status,
            'post_type' => $post_type
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
    static function exists($metas = null, $taxonomy = null, $post_status = 'publish', $post_type = null): bool
    {
        $args = array(
            'post_type' => $post_type,
            'post_status' => $post_status,
            'posts_per_page' => 1,
        );

        if ($taxonomy !== null) {
            $args['tax_query'] = array(
                'relation' => 'AND',
                array(
                    'taxonomy' => key($taxonomy),
                    'field' => 'slug',
                    'terms' => current($taxonomy),
                ),
            );
        }

        if ($metas !== null && is_array($metas)) {
            $meta_query = array('relation' => 'AND');

            foreach ($metas as $key => $value) {
                $meta_query[] = array(
                    'key' => $key,
                    'value' => $value,
                    'compare' => '=',
                );
                $meta_query[] = array(
                    'key' => $key,
                    'value' => $value,
                    'compare' => 'BINARY',
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

    static function deleteByID($post_id, bool $permanent = false){
        return wp_delete_post($post_id, $permanent);
    }

    static function deleteByIDOrFail($post_id, bool $permanent = false) {
        if (!is_numeric($post_id) || $post_id <= 0) {
            throw new \InvalidArgumentException("Post ID not found");
        }

        // Attempt to delete the post
        $result = wp_delete_post($post_id, $permanent);

        if (!$result){
            throw new \Exception("Post was unable to be deleted");
        }
    }

    static function getPostType($post_id)
    {
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
    static function setStatus($pid, $status, bool $validate = true)
    {
        global $wpdb;

        if ($pid == null) {
            throw new \InvalidArgumentException("PID can not be null");
        }

        if ($validate && !in_array($status, ['publish', 'pending', 'draft', 'trash', 'private', 'future'])) {
            throw new \InvalidArgumentException("Invalid status '$status'.");
        }

        wp_update_post(array(
            'ID'    =>  $pid,
            'post_status'   =>  $status
        ));

        // Limpiar la caché de WordPress
        clean_post_cache($pid);
    }

    /*
        Cambia el status de un post de forma temporal para poder realizar una accion que requiere cierto post_status
        (tipicamente 'publish')

        Ej:

        $product_urls = [];
        foreach ($product_ids as $product_id) {
            $product_urls[$product_id] = changeStatusTemporarily($product_id, 'publish', 'getFriendlyProductUrl');
        }
    */
    static  function changeStatusTemporarily(int $post_id, $temp_status, callable $cb) {
        global $wpdb;
    
        // Obtener el estado original del producto
        $original_status = $wpdb->get_var($wpdb->prepare(
            "SELECT post_status FROM wp_posts WHERE ID = %d",
            $post_id
        ));
    
        static::setStatus($post_id, $temp_status, true);

        // Hacer algo con el status cambiado de forma temporal
        $result = $cb($post_id);
    
        // Restaurar el estado original del producto
        static::setStatus($post_id, $original_status, true);
    
        return $result;
    }
    

    // alias
    static function updateStatus($pid, $status)
    {
        return static::setStatus($pid, $status);
    }

    static function setAsDraft($pid)
    {
        static::setStatus($pid, 'draft');
    }

    static function setAsPublish($pid)
    {
        static::setStatus($pid, 'publish');
    }

    static function setAsPrivate($pid)
    {
        static::setStatus($pid, 'private');
    }

    static function trash($pid)
    {
        return static::setStatus($pid, 'trash');
    }

    // 'publish'
    static function restore($pid)
    {
        return static::setStatus($pid, 'publish');
    }

    static function getAttr($key = null)
    {
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
    static function getAttrByID($id, $key = null)
    {
        $attrs = get_post_custom($id);

        if (!empty($key)) {
            return $attrs[$key] ?? null;
        }

        return $attrs;
    }

    static function addAttr($post_id, $attr_name, $attr_value)
    {
        add_post_meta($post_id, $attr_name, $attr_value, true);
    }

    /*
        @return int post_id

        Ej:

        $pid = Posts::getBySlug('woocommerce-y-a-vender');
        dd($pid, 'PID');

        $pid = Posts::getBySlug('introduccion-a-php', null, 'publish');
        dd($pid, 'PID');

    */
    static function getBySlug(string $slug, string $post_type = null, $post_status = null)
    {
        // No considero post_type de momento
        if ($post_type == null) {
            $post_type = static::$post_type;
        }

        $include_post_status = '';
        if (!empty($post_status)) {
            $include_post_status = "AND post_status = '$post_status'";
        }

        $table_name = 'posts'; 

        $sql =  "
            SELECT ID
            FROM $table_name
            WHERE post_name = ?
            $include_post_status 
            LIMIT 1
        ";

        DB::getConnection();

        return DB::selectOne($sql, [ $slug ]);       
    }

    /*
        Version basada con consultas SQL

        Ej:

        $pids  = Products::getPosts(null, null, $limit, $offset, [
            '_downloadable' => 'yes'
        ]);

        Ej:

        $posts = Posts::getPosts('*', 'post', 'publish', 10, 0, null, ['post_date' => 'ASC', 'post_title' => 'DESC']);

        No alterar el orden de los parametros ya que es usada en mutawp_admin !!!

        TO-DO

        Dividir la función en partes más pequeñas, por ejemplo:

        Una función para construir la condición de atributos.
        Una función para construir la clausula de order.
        Una función principal que use las anteriores.
    */
    static function getPosts($select = '*', $post_type = null, $post_status = null, $limit = -1, $offset = null, $attributes = null, $order_by = null, bool $include_metadata = false)
    {
        global $wpdb;

        if (is_array($select)) {
            // podria hacer un enclose con ``
            $select = implode(', ', $select);
        }

        if ($post_type === null) {
            $post_type = static::$post_type;
        }

        $include_post_status = '';
        if ($post_status !== null) {
            $include_post_status = "AND post_status = '$post_status'";
        }

        $attributes_condition = '';
        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $attributes_condition .= "AND ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '$key' AND meta_value = '$value') ";
            }
        }

        $limit_clause = '';
        if ($limit !== null) {
            $limit_clause = "LIMIT $limit";
        }

        $offset_clause = '';
        if ($offset !== null) {
            $offset_clause = "OFFSET $offset";
        }

        $order_clause = '';
        if ($order_by !== null && is_array($order_by)) {
            $order_clauses = [];
            foreach ($order_by as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $order_clauses[] = "$field $direction";
            }
            if (!empty($order_clauses)) {
                $order_clause = 'ORDER BY ' . implode(', ', $order_clauses);
            }
        } else {
            $order_clause = 'ORDER BY ID ASC';
        }

        $sql = "SELECT $select 
        FROM `{$wpdb->prefix}posts` 
        WHERE ID <> 1 
        AND post_type = '$post_type' 
        $include_post_status 
        $attributes_condition 
        $order_clause 
        $limit_clause 
        $offset_clause";

        $rows = $wpdb->get_results($sql, ARRAY_A);

        if ($include_metadata) {
            foreach ($rows as $ix => $row) {
                $rows[$ix]['meta'] = Posts::getMeta($row['ID']);
            }
        }

        return $rows;
    }

    /*
        Ej:

        Posts::getIDs('sfwd-question', 'publish', 5)
    */
    static function getIDs($post_type = null, $post_status = null, $limit = null, $offset = null, $attributes = null, $order_by = null)
    {
        $res = static::getPosts('ID', $post_type, $post_status, $limit, $offset, $attributes, $order_by);
        return array_column($res, 'ID') ?? null;
    }

    static function getPost($id)
    {
        return get_post($id, ARRAY_A);
    }

    /* 
        Busqueda por coincidencia exacta.
        
        El operador aplicado es AND

        Podria haber implementacion mas eficiente con FULLSEARCH

        O usar 

        https://www.advancedcustomfields.com/resources/query-posts-custom-fields/
        https://qirolab.com/posts/example-of-wp-query-to-search-by-post-title-in-wordpress

        $pids = Posts::search('%MODELO NO DISPONIBLE EN STOCK%');
    */
    static function search($keywords, $attributes = null, $select = '*', bool $include_desc = true, $post_type = null, $post_status = null, $limit = null, $offset = null, $order_by = null)
    {
        global $wpdb;

        $tb = $wpdb->prefix . 'posts';
        
        $select_multi = false;
        if ($select != '*') {
            if (is_array($select)) {
                $select       = implode(', ', array_map(function($col) {
                    return "`$col`";
                }, $select));

                $select_multi = true;
            } else {
                if (Strings::contains(',', $select)){
                    $select_multi = true;
                }
            }
        } else {
            $select_multi = true;
        }

        if (!is_array($keywords)) {
            $keywords = [$keywords];
        }

        $conds = [];
        foreach ($keywords as $ix => $keyword)
        {
            $conds[] = "(" . "post_title = '$keyword'" 
                      . ($include_desc ? " OR post_excerpt = '$keyword'" : '') 
                      . ($include_desc ? " OR post_content = '$keyword'" : '')
                      . ")";
        }

        $conditions = implode(' AND ', $conds);

        ////////////////////////////////////////////////

        if ($post_type == null) {
            $post_type = static::$post_type;
        }

        $include_post_status = '';
        if ($post_status !== null) {
            $include_post_status = "AND post_status = '$post_status'";
        }

        $attributes_condition = '';
        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $attributes_condition .= "AND ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '$key' AND meta_value = '$value') ";
            }
        }

        $order_clause = '';
        if ($order_by !== null && is_array($order_by)) {
            $order_clauses = [];
            foreach ($order_by as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $order_clauses[] = "$field $direction";
            }
            if (!empty($order_clauses)) {
                $order_clause = 'ORDER BY ' . implode(', ', $order_clauses);
            }
        } else {
            $order_clause = 'ORDER BY ID ASC';
        }

        $limit_clause = '';
        if ($limit !== null) {
            $limit_clause = "LIMIT $limit";
        }

        $offset_clause = '';
        if ($offset !== null) {
            $offset_clause = "OFFSET $offset";
        }

        ////////////////////////////////////////////////    

        $sql = $wpdb->prepare(
            "SELECT $select FROM `$tb` 
            WHERE 
            post_type = %s 
            $include_post_status 
            $attributes_condition
            AND ($conditions)
            $order_clause
            $limit_clause 
            $offset_clause;",
            $post_type
        );
    
        $results = $wpdb->get_results($sql, ARRAY_A);
    
        if (!$select_multi) {
            $results = array_column($results, trim($select, '`'));
        }
    
        return $results;
    }

    /* 
        Busqueda con LIKE

        El operador aplicado es AND

        Podria haber implementacion mas eficiente con FULLSEARCH

        O usar 

        https://www.advancedcustomfields.com/resources/query-posts-custom-fields/
        https://qirolab.com/posts/example-of-wp-query-to-search-by-post-title-in-wordpress

        $pids = Products::searchProduct('%MODELO NO DISPONIBLE EN STOCK%', null, 'ID');
    */
    static function searchByLike($keywords, $attributes = null, $select = '*', bool $include_desc = true, $post_type = null, $post_status = null, $limit = null, $offset = null, $order_by = null)
    {
        global $wpdb;

        $tb = $wpdb->prefix . 'posts';

        $select_multi = false;
        if ($select != '*') {
            if (is_array($select)) {
                $select       = implode(', ', array_map(function($col) {
                    return "`$col`";
                }, $select));

                $select_multi = true;
            } else {
                if (Strings::contains(',', $select)){
                    $select_multi = true;
                }
            }
        } else {
            $select_multi = true;
        }

        if (!is_array($keywords)) {
            $keywords = [$keywords];
        }

        $conds = [];
        foreach ($keywords as $ix => $keyword)
        {
            if (Strings::firstChar($keyword) === '%') {
                if (Strings::lastChar($keyword) === '%') {
                    // Si $keyword comienza y termina con '%', escapamos los caracteres especiales
                    // dentro de $keyword y lo rodeamos con '%'
                    $keyword = '%' . $wpdb->esc_like(trim($keyword, '%')) . '%';
                } else {
                    // Si $keyword solo comienza con '%', escapamos los caracteres especiales
                    // dentro de $keyword y lo precedemos con '%'
                    $keyword = '%' . $wpdb->esc_like(trim($keyword, '%'));
                }
            } else {
                if (Strings::lastChar($keyword) === '%') {
                    // Si $keyword solo termina con '%', escapamos los caracteres especiales
                    // dentro de $keyword y lo seguimos con '%'
                    $keyword = $wpdb->esc_like(trim($keyword, '%')) . '%';
                } else {
                    // Si $keyword no comienza ni termina con '%', simplemente escapamos
                    // los caracteres especiales dentro de $keyword
                    $keyword = $wpdb->esc_like($keyword);
                }
            }

            $conds[] = "(" . "post_title LIKE '$keyword'" 
                      . ($include_desc ? " OR post_excerpt LIKE '$keyword'" : '') 
                      . ($include_desc ? " OR post_content LIKE '$keyword'" : '')
                      . ")";
        }

        $conditions = implode(' AND ', $conds);

        ////////////////////////////////////////////////

        if ($post_type == null) {
            $post_type = static::$post_type;
        }

        $include_post_status = '';
        if ($post_status !== null) {
            $include_post_status = "AND post_status = '$post_status'";
        }

        $attributes_condition = '';
        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $attributes_condition .= "AND ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '$key' AND meta_value = '$value') ";
            }
        }

        $order_clause = '';
        if ($order_by !== null && is_array($order_by)) {
            $order_clauses = [];
            foreach ($order_by as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $order_clauses[] = "$field $direction";
            }
            if (!empty($order_clauses)) {
                $order_clause = 'ORDER BY ' . implode(', ', $order_clauses);
            }
        } else {
            $order_clause = 'ORDER BY ID ASC';
        }

        $limit_clause = '';
        if ($limit !== null) {
            $limit_clause = "LIMIT $limit";
        }

        $offset_clause = '';
        if ($offset !== null) {
            $offset_clause = "OFFSET $offset";
        }

        ////////////////////////////////////////////////    

        $sql = $wpdb->prepare(
            "SELECT $select FROM `$tb` 
            WHERE 
            post_type = %s 
            $include_post_status 
            $attributes_condition
            AND ($conditions)
            $order_clause 
            $limit_clause 
            $offset_clause;",
            $post_type
        );
    
        $results = $wpdb->get_results($sql, ARRAY_A);
    
        if (!$select_multi) {
            $results = array_column($results, trim($select, '`'));
        }
    
        return $results;
    }


    /*
        Retorna el ultimos post

        Ej:
        
        Posts::getLastNPost('*', 'shop_coupon')
    */
    static function getLastPost($select = '*', $post_type = null, $post_status = null, $attributes = null, bool $include_metadata = false)
    {
        return static::getPosts($select, $post_type, $post_status, 1, 0, $attributes, "ID DESC", $include_metadata);
    }

    /*
        Retorna los ultimos N-posts

        Ej:
        
        Posts::getLastNPost('*', 'shop_coupon', null, 2	)
    */
    static function getLastNPost($select = '*', $post_type = null, $post_status = null, int $limit = -1, $attributes = null, bool $include_metadata = false)
    {
        return static::getPosts($select, $post_type, $post_status, $limit, 0, $attributes, "ID DESC", $include_metadata);
    }

    static function getLastID($post_type = null, $post_status = null)
    {
        if ($post_type == null) {
            $post_type = static::$post_type;
        }

        $post = static::getLastPost($post_type, $post_status);

        if ($post == null) {
            return null;
        }

        return (int) $post['ID'] ?? null;
    }

    static function getAll($post_type = null, $status = 'publish', $limit = -1, $order = null)
    {
        global $wpdb;

        if ($post_type == null) {
            $post_type = static::$post_type;
        }

        $sql = "SELECT SQL_CALC_FOUND_ROWS  * FROM {$wpdb->prefix}posts  WHERE 1=1  AND (({$wpdb->prefix}posts.post_type = '$post_type' AND ({$wpdb->prefix}posts.post_status = '$status')));";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    static function getOne($post_type = null, $status = 'publish', $limit = -1, $order = null)
    {
        global $wpdb;

        if ($post_type == null) {
            $post_type = static::$post_type;
        }

        $sql = "SELECT SQL_CALC_FOUND_ROWS  * FROM {$wpdb->prefix}posts WHERE 1=1 AND (({$wpdb->prefix}posts.post_type = '$post_type' AND ({$wpdb->prefix}posts.post_status = '$status')));";

        return $wpdb->get_row($sql, ARRAY_A);
    }

    
    /*
        Nota:
        
        Hace un AND entre cada categoria en la query
    */

    static function getPostsByTaxonomy(string $taxo, string $by, Array $term_ids, $post_type = null, $post_status = null)
    {   
        if (!in_array($by, ['slug', 'id', 'name'])){
            throw new \InvalidArgumentException("Invalid field '$by' for quering");
        }

        if ($post_status != null && !in_array($post_status, ['publish', 'draft', 'pending', 'private', 'trash', 'auto-draft'])){
            throw new \InvalidArgumentException("Invalid post_status '$post_status'");
        }

        // When you have more term_id's seperate them by comma.
        $str_term_ids = implode(',', $term_ids);

        $args = array(
            'post_type' => $post_type ?? static::$post_type,
            'numberposts' => -1,
            'post_status' => $post_status,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxo,
                    'field' => $by,
                    'terms' => $str_term_ids,
                    'operator' => 'IN',
                    )
                 ),
        );

        return get_posts( $args);
    }

    
    static function setTaxonomy($pid, $taxonomy_name, $cat_slug)
    {
        wp_set_object_terms($pid, $taxonomy_name, $cat_slug);
    }
    
    static function getPostsByCategory(string $by, Array $category_ids, $post_status = null)
    {
        return static::getPostsByTaxonomy(static::$cat_metakey, $by, $category_ids, static::$post_type, $post_status);
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
            
        Casi todos los parametros son opcionales:
        
        $pids = Products::getByMeta('original_url', null, null, null, null, null, null, 'meta_value');
    
        Se pueden especificar los campos:

        $pids = Products::getByMeta('original_url', null, null, null, null, null, null, ['guid','meta_value']);
    
    */
    static function getByMeta(string $meta_key, ?string $meta_value = null, ?string $post_type = null, ?string $post_status = null, ?int $limit = null, ?int $offset = null, ?string $order_by = null, $select = '*')
    {
        global $wpdb;

        $select_multi = false;
        if ($select != '*') {
            if (is_array($select)) {
                $select       = implode(', ', array_map(function($col) {
                    return "`$col`";
                }, $select));

                $select_multi = true;
            } else {
                if (Strings::contains(',', $select)){
                    $select_multi = true;
                }
            }
        } else {
            $select = 'p.*, pm.*';
            $select_multi = true;
        }

        if ($post_type == null) {
            $post_type = static::$post_type;
        }

        $limit_clause = '';
        if ($limit !== null) {
            $limit_clause = "LIMIT $limit";
        }

        $offset_clause = '';
        if ($offset !== null) {
            $offset_clause = "OFFSET $offset";
        }

        $order_clause = 'ORDER BY ID ASC';
        if ($order_by !== null) {
            $order_clause = "ORDER BY $order_by";
        }

        $sql = "SELECT $select FROM {$wpdb->prefix}postmeta pm
                LEFT JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id 
                WHERE pm.meta_key = %s";

        $sql_params = array($meta_key);

        if ($meta_value !== null) {
            $sql .= " AND pm.meta_value = %s";
            $sql_params[] = $meta_value;
        }

        if ($post_type !== null) {
            $sql .= " AND p.post_type = %s";
            $sql_params[] = $post_type;
        }

        if ($post_status !== null) {
            $sql .= " AND p.post_status = %s";
            $sql_params[] = $post_status;
        }

        $sql .= " $order_clause $limit_clause $offset_clause";

        // dd($sql_params, $sql);

        $results = $wpdb->get_results($wpdb->prepare($sql, $sql_params), ARRAY_A);

        if (!$select_multi) {
            $results = array_column($results, $select);
        }
    
        return $results;
    }


    static function getPostIDsContainingMeta($meta_key)
    {
        global $wpdb;

        // Preparar la consulta SQL para buscar el ID de la meta
        $query = $wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s", $meta_key
        );

        return array_column($wpdb->get_results($query, ARRAY_A), 'post_id');
    }

    /*
        Retorna post(s) contienen determinado valor en una meta_key

        Es una version simplificada de getByMeta()
    */
    static function getPostsByMeta($meta_key, $meta_value, $post_type = null, $post_status = null)
    {
        global $wpdb;

        if ($post_type == null) {
            $post_type = static::$post_type;
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

        $sql = "SELECT * FROM {$wpdb->prefix}postmeta pm
            LEFT JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id 
            WHERE  
            pm.meta_key = '%s' 
            AND pm.meta_value='%s'";

        $sql_params = [$meta_key, $meta_value];

        if ($post_type !== null) {
            $sql .= " AND p.post_type = '%s'";
            $sql_params[] = $post_type;
        }

        if ($post_status !== null) {
            $sql .= " AND p.post_status = '%s'";
            $sql_params[] = $post_status;
        }

        // dd($sql);

        $r = $wpdb->get_results($wpdb->prepare($sql, ...$sql_params));

        return $r;
    }
    
    /*
        Retorna la cantidad de posts contienen determinado valor en una meta_key
    */
    static function countByMeta($meta_key, $meta_value, $post_type = null, $post_status = 'publish')
    {
        global $wpdb;

        if ($post_type == null) {
            $post_type = static::$post_type;
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
            $sql_params[] = $post_status;
        }

        $r = (int) $wpdb->get_var($wpdb->prepare($sql, ...$sql_params));

        return $r;
    }

    /*
        Obtiene un valores de un meta para todos los productos de un tipo

        Ej:

        Products::getMetaValues('_regular_price')

        Tambien puede usarse getByMeta() con el mismo resultado
    */
    public static function getMetaValues($meta_key, $post_type = null, $post_status = null) {
        global $wpdb;

        $sql = "
            SELECT pm.meta_value
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.post_type = %s
            AND pm.meta_key = %s
        ";

        $params = [$post_type ?? static::$post_type, $meta_key];

        if (!empty($post_status)){
            $sql .= " AND p.post_status = %s";
            $params[] = $post_status;
        }

        $query = $wpdb->prepare($sql, ...$params);

        $meta_values = $wpdb->get_col($query);

        return $meta_values;
    }

    static function deleteMeta($post_id, $meta_key)
    {
       delete_post_meta($post_id, $meta_key);
    }

    /*
        Uso. Ej:

        static::getTaxonomyFromTerm('Crema')
    */
    static function getTaxonomyFromTerm(string $term_name)
    {
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

    /*
        Size (attribute)
        small  (term)
        medium (term)
        large  (term)
    */
    static function getTermIdsByTaxonomy(string $taxonomy)
    {
        global $wpdb;

        $sql = "SELECT term_id FROM `{$wpdb->prefix}term_taxonomy` WHERE `taxonomy` = '$taxonomy';";

        return $wpdb->get_col($sql);
    }

    // static function getMetaByPostID_2($pid, $taxonomy = null){
	// 	global $wpdb;

	// 	$pid = (int) $pid;

    //     if ($taxonomy != null){
    //         $and_taxonomy = "AND taxonomy = '$taxonomy'";
    //     }

	// 	$sql = "SELECT T.*, TT.* FROM {$wpdb->prefix}term_relationships as TR 
	// 	INNER JOIN `{$wpdb->prefix}term_taxonomy` as TT ON TR.term_taxonomy_id = TT.term_id  
	// 	INNER JOIN `{$wpdb->prefix}terms` as T ON  TT.term_taxonomy_id = T.term_id
	// 	WHERE 1=1 $and_taxonomy AND TR.object_id='$pid'";

	// 	return $wpdb->get_results($sql);
	// }

    /*
        Devolucion de array de metas incluidos atributos de posts

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
    static function getMeta($pid, $meta_key = '', bool $single = true)
    {
        return get_post_meta($pid, $meta_key, $single);
    }

    /*  
        Get metas "ID" by meta key y value 
    */
    static function getMetaIDs($meta_key, $dato)
    {
        global $wpdb;
       
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

    static function setMeta($post_id, $meta_key, $dato, bool $sanitize = false)
    {
        if ($sanitize) {
            $dato = sanitize_text_field($dato);
        }

        update_post_meta($post_id, $meta_key, $dato);
    }

    /*
        Devuelve si un termino existe para una determinada taxonomia
    */
    static function termExists($term_name, string $taxonomy)
    {       
        return (term_exists($term_name, $taxonomy) !== null);
    }


    static function getTermBySlug($slug)
    {
        global $wpdb;

        $sql = "SELECT * from `{$wpdb->prefix}terms` WHERE slug = '$slug'";
        return $wpdb->get_row($sql);
    }

    static function getTermById(int $id)
    {
        // global $wpdb;

        // $sql = "SELECT * from `{$wpdb->prefix}terms` WHERE term_id = '$id'";
        // return $wpdb->get_row($sql);

        return get_term($id);
    }

    /*
        Delete Attribute Term by Name

        Borra los terminos agregados con insertAttTerms() de la tabla 'wp_terms' por taxonomia
    */
    static function deleteTermByName(string $taxonomy, $args = [])
    {
        $term_ids = static::getTermIdsByTaxonomy($taxonomy);

        foreach ($term_ids as $term_id) {
            wp_delete_term($term_id, $taxonomy, $args);
        }
    }


    static function getTaxonomyBySlug($slug, $taxo = null)
    {
        $category = get_term_by('slug', $slug, $taxo);

        if ($category === null || $category === false) {
            return null;
        }

        return $category;
    }

    static function getTaxonomyIdBySlug($slug, $taxo = null)
    {
        $cat_obj = static::getTaxonomyBySlug($slug, $taxo);

        if (empty($cat_obj)) {
            return null;
        }

        return $cat_obj->term_id;
    }

    // 6-ene-24
    static protected function getPostsByTaxonomy__($field, $terms, $taxonomy, $limit = -1, $offset = 0, $post_type = null, $include_children = false, $args = [])
    {
        $_args = [
            'tax_query' => [
                [
                    'taxonomy' => $taxonomy,
                    'field' => $field, // 'term_id' o 'slug'
                    'terms' => $terms,
                    'include_children' => $include_children // Remove if you need posts from term child terms
                ],
            ],
            // Rest your arguments
        ];

        if ($post_type !== false) {
            $_args['post_type'] = $post_type ?? static::$post_type;
        }

        if ($limit != null) {
            $_args['posts_per_page'] = $limit;
        }

        if (!empty($offset)) {
            $_args['offset'] = $offset;
        }

        $_args = array_merge($_args, $args);

        $query = new \WP_Query($_args);

        return $query->posts;
    }

    /*
        6-ene-24

        En una clase derivada podria hacer esto:

        static::getPostsByTaxonomyId(273)

        o

        static::getPostsByTaxonomyId(273, null, null "my-taxonomy")
    */
    static function getPostsByTaxonomyId($id, $taxonomy, $limit = -1, $offset = 0, $post_type = null, $include_children = false, $args = [])
    {
        return static::getPostsByTaxonomy__('term_id', $id, $taxonomy, $limit, $offset, $post_type, $include_children, $args);
    }

    // 6-ene-24
    static function getPostsByTaxonomySlug($slug, $taxonomy, $limit = -1, $offset = 0, $post_type = null, $include_children = false, $args = [])
    {
        return static::getPostsByTaxonomy__('slug', $slug, $taxonomy, $limit, $offset, $post_type, $include_children, $args);
    }

    // 6-ene-24        
    static function getPostsByCategoryId($id, $limit = -1, $offset = 0, $post_type = null, $include_children = false, $args = [])
    {
        return static::getPostsByTaxonomy__('term_id', $id, static::$cat_metakey, $limit, $offset, $post_type, $include_children, $args);
    }

    // 6-ene-24
    static function getPostsByCategorySlug($slug, $limit = -1, $offset = 0, $post_type = null, $include_children = false, $args = [])
    {
        return static::getPostsByTaxonomy__('slug', $slug, static::$cat_metakey, $limit, $offset, $post_type, $include_children, $args);
    }

    static function dumpCategories()
    {
        $terms = get_terms([
            'taxonomy' => static::$cat_metakey,
            'hide_empty' => false,
        ]);

        return $terms;
    }

    // 6-ene-24

    /*
        Borra una categoria y puede hacerlo recursivamente <--- algo esta mal con la recursividad

        Ademas puede borrar los posts internos

        Ej:

        static::deleteCategoryById(305, true, true, true)
    */
    static function deleteCategoryById($term_id, bool $recursive = false, bool $include_posts = false, bool $force_post_deletion = true)
    {
        if ($include_posts) {
            $posts = static::getPostsByCategoryId($term_id);

            // delete all posts
            foreach ($posts as $post) {
                wp_delete_post($post->ID, $force_post_deletion);
            }
        }

        if ($recursive) {
            //delete all subcategories
            $args = array('child_of' => $term_id);
            $categories = get_categories($args);

            foreach ($categories as $category) {
                static::deleteCategoryById($category->term_id, $recursive, $include_posts, $force_post_deletion);
            }
        }

        return wp_delete_term($term_id, static::$cat_metakey);
    }

    // 6-ene-24 --ok
    static function deleteCategoryBySlug($slug, bool $recursive = false, bool $include_posts = false, bool $force_post_deletion = true){
        $cat = static::getCategoryBySlug($slug);

        if (empty($cat)){
            return;
        }

        return static:: deleteCategoryById($cat->term_id, $recursive, $include_posts, $force_post_deletion);
    }

    // 6-ene-24 -- ok
    static function deleteAllCategories(bool $include_posts, bool $force_post_deletion = false)
    {
        $cats = static::getAllCategories();

        foreach ($cats as $cat) {
            $term_id = $cat['term_id'];

            static::deleteCategoryById($term_id, false, $include_posts, $force_post_deletion);
        }
    }

    /*
        Borra categorias vacias

        Sin ensayar
    */
    static function deleteEmptyCategoriesV1($min = 1)
    {
        $count = $min - 1;
        $wp_ = tb_prefix();

        // https://stackoverflow.com/a/52413755
        $sql = "DELETE FROM {$wp_}terms WHERE term_id IN (SELECT term_id FROM {$wp_}term_taxonomy WHERE count = $count)";

        DB::statement($sql);
    }

    /*
        Esta funcion *debe ser revisasada* porque una categoria 
        no se puede considerar vacia si tiene sub-categorias !!!! !!!

        Su uso es peligroso

        Ej:

        $cats = static::deleteEmptyCategories(1, [
            'shop', 'uomo', 'donna'
        ]);

        <-- en ese caso cualquier categoria con menos de 1 producto
        excepto 'shop', 'uomo', 'donna' son eliminadas 

        Los posts en esas categorias son asignados a la categoria
        padre
    */
    static function deleteEmptyCategories($min = 1, $keep_cats = [])
    {
        // Convierto todas las categorias en cat_ids
        foreach ($keep_cats as $ix => $keep_cat) {
            if (is_string($keep_cat)) {
                $cat_id = static::getCategoryIdByName($keep_cat);

                if (!empty($cat_id)) {
                    $keep_cats[] = $cat_id;
                }

                $cat_id = static::getCategoryIdBySlug($keep_cat);

                if (!empty($cat_id)) {
                    if (!in_array($cat_id, $keep_cats)) {
                        $keep_cats[] = $cat_id;
                    }
                }

                unset($keep_cats[$ix]);
            }
        }

        $cats = static::getAllCategories();

        $affected = 0;
        foreach ($cats as $cat) {
            if (in_array($cat['term_id'], $keep_cats)) {
                continue;
            }

            //dd($cat['count'], $cat['name']);

            if (intval($cat['count']) < $min) {
                //dd("Borrando {$cat['name']}");

                $ok = static::deleteCategoryById($cat['term_id']);

                if ($ok) {
                    $affected++;
                }
            }
        }

        return $affected;
    }

    /*  
        En la mayoria dew casos, la taxonomia se crea con slug y cuando se edita es por id 

        Sin embargo, en teoria se podria pasar el "term_id" en $args, creando la taxonomia con su id
    */
    static function createTaxonomy($taxo, $name, $slug = null, $description = null, $id_parent = null, $image_url = null, $args = [])
    {
        // dd($id_parent, '$id_parent');

        // Verificar si la taxonomía ya existe
        $existing_term = term_exists($name, $taxo);

        if ($existing_term !== 0 && $existing_term !== null) {
            // La taxonomía ya existe, puedes manejarlo como desees
            return $existing_term['term_id'];
        }
        
        $args = array_merge($args, [
            'description' => $description,
            'slug'        => $slug,
            'parent'      => $id_parent
        ]);
        
        if (empty($args['parent'])){
            unset($args['parent']);
        }

        if (isset($args['parent']) && $args['parent'] !== '' && !is_numeric($args['parent'])){
            throw new \Exception("args.parent is expected to be int. Received: ". var_export($args['parent'], true));
        }

        $cat = wp_insert_term(
            $name, // the term 
            $taxo, // the taxonomy
            $args
        );

        if ($cat instanceof \WP_Error) {
            // dd([
            //     'name' => $name, // the term 
            //     'taxo' => $taxo, // the taxonomy
            //     'args' => $args
            // ], 'GENERA ERROR con wp_insert_term($name, $taxo, $args)');

            throw new \Exception($cat->get_error_message());
        }

        if (!empty($image_url)) {
            $img_id = static::uploadImage($image_url);
        }

        if (isset($img_id) && !is_wp_error($cat)) {
            $cat_id = isset($cat['term_id']) ? $cat['term_id'] : 0;
            update_term_meta($cat_id, 'thumbnail_id', absint($img_id));
        }

        return $cat['term_id'];
    }

    static function updateTaxonomyById($taxo, $term_id, $name, $slug = null, $description = null, $id_parent = null, $image_url = null, $args = [])
    {
        //dd("Updating ... $name");

        $args = array_merge($args, [
            'name' => $name,
            'description' => $description,
            'parent' => $id_parent
        ]);

        if (!empty($slug)){
            $args['slug'] = $slug;
        }

        $cat = wp_update_term(
            $term_id,
            $taxo, // the taxonomy
            $args
        );

        if ($cat instanceof \WP_Error) {
            dd([
                'name' => $name, // the term 
                'taxo' => $taxo, // the taxonomy
                'args' => $args
            ], 'GENERA ERROR');

            throw new \Exception($cat->get_error_message());
        }

        if (!empty($image_url)) {
            $img_id = static::uploadImage($image_url);
        }

        if (isset($img_id) && !is_wp_error($cat)) {
            $cat_id = isset($cat['term_id']) ? $cat['term_id'] : 0;
            update_term_meta($cat_id, 'thumbnail_id', absint($img_id));
        }

        return $cat;
    }

    static function updateTaxonomyBySlug($taxo, $slug, $name, $description = null, $id_parent = null, $image_url = null, $args = [])
    {
        $term_id = static::getTaxonomyIdBySlug($slug, $taxo);

        //dd("Updating ... $name");

        $args = array_merge($args, [
            'name' => $name,
            'description' => $description,
            'slug' => $slug,
            'parent' => $id_parent
        ]);

        $cat = wp_update_term(
            $term_id,
            $taxo, // the taxonomy
            $args
        );

        if ($cat instanceof \WP_Error) {
            dd([
                'term_id' => $term_id, // the term 
                'taxo' => $taxo, // the taxonomy
                'args' => $args
            ], 'GENERA ERROR');

            throw new \Exception($cat->get_error_message());
        }

        if (!empty($image_url)) {
            $img_id = static::uploadImage($image_url);
        }

        if (isset($img_id) && !is_wp_error($cat)) {
            $cat_id = isset($cat['term_id']) ? $cat['term_id'] : 0;
            update_term_meta($cat_id, 'thumbnail_id', absint($img_id));
        }

        return $cat;
    }

    static function createOrUpdateTaxonomyBySlug($taxo, $slug, $name, $description = null, ?int $id_parent = null, $image_url = null, $args = [])
    {
        $id = static::getTaxonomyIdBySlug($slug, $taxo);

        if (empty($id)){
            return static::createTaxonomy($taxo, $name, $slug, $description, $id_parent, $image_url, $args);
        } else {
            return static::updateTaxonomyBySlug($taxo, $slug, $name, $description, $id_parent, $image_url, $args);
        }
    }

    /*
        Images
    */
    static function getImages($pid, $featured_img = false)
    {
        $images = get_attached_media('image', $pid);

        if ($featured_img === false) {
            $urls = [];
            foreach ($images as $img) {
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

    static function getAttachmentIdFromSrc($image_src)
    {
        global $wpdb;

        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
        $id = $wpdb->get_var($query);
        return $id;
    }

    /*
        Otra implentación:

        https://wordpress.stackexchange.com/questions/64313/add-image-to-media-library-from-url-in-uploads-directory
    */
    static function uploadImage($url, $title = '', $alt = '', $caption = '')
    {
        if (!function_exists('media_sideload_image')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        if (!function_exists('wp_crop_image')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';;
        }
        
        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        if (empty($url)){
            return;
        }

        if (strlen($url) < 10 || !Strings::startsWith('http', $url)){
            throw new \InvalidArgumentException("Image url '$url' is not valid");
        }

        $att_id = static::getAttachmentIdFromSrc($url);
        if ( $att_id !== null){
            return $att_id;
        }

        // mejor,
        // Files::file_get_contents_curl($url)
        $img_str = @file_get_contents($url);

        if ($img_str === false){
            Logger::logError("Img no accesible. URL: $url");
            return;
        }

        /*	
            Array
            (
                [0] => 600
                [1] => 600
                [2] => 2
                [3] => width="600" height="600"
                [bits] => 8
                [channels] => 3
                [mime] => image/jpeg
            )
        */

        $img_info = getimagesizefromstring($img_str);
        $mime     = $img_info['mime'] ?? null;

        if (empty($mime)){
            Logger::logError("MIME could not be determinated for $url");
            return false;
        }

        $img_type = Strings::afterIfContains($mime, "image/");

        if (empty($img_type)){
            Logger::logError("Formato incorrecto. El MIME indica que no es una imagen");
            return false;
        }

        $uniq_name = date('dmY').''.(int) microtime(true); 
        $filename = $uniq_name . '.' . $img_type;

        $uploaddir  = wp_upload_dir();
        $uploadfile = $uploaddir['path'] . '/' . $filename;


        $savefile = fopen($uploadfile, 'w');
        $bytes    = fwrite($savefile, $img_str);
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
            'guid'           => $url,
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

    static function setImagesForPost($pid, array $image_ids)
    {
        //dd("Updating images for post with PID $pid");
        $image_ids = implode(",", $image_ids);
        update_post_meta($pid, '_product_image_gallery', $image_ids);
    }

    // Setea imagen destacada
    static function setDefaultImage($pid, $image_id)
    {
        update_post_meta($pid, '_thumbnail_id', $image_id);
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
                'post_type' => 'attachment',
                'posts_per_page' => -1,
                'post_status' => 'any',
                'post_parent' => $pid,
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

    // retunrs author_id
    static function getAuthorID($post)
    {
        if (is_numeric($post)) {
            $post = get_post($post);
        }

        return $post->post_author;
    }

}