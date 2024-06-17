<?php

namespace boctulus\SW\core\traits;

use boctulus\SW\core\libs\Url;
use boctulus\SW\core\Constants;
use boctulus\SW\core\libs\Strings;

trait PostCategoriesTrait
{  
    static function getCategoryNames($pid){
        return wp_get_post_terms( $pid, static::$cat_metakey, array('fields' => 'names') );
    }

    static function getPostsByCategory(string $by, Array $category_ids, $post_status = null)
    {
        return static::getPostsByTaxonomy(static::$cat_metakey, $by, $category_ids, static::$post_type, $post_status);
    }
    
    /*
        Actualiza categoria
    */
    static function setCategory($pid, $category_name, $cat_slug = null)
    {
        $cat_slug = $cat_slug ?? static::$cat_metakey;

        wp_set_object_terms($pid, $category_name, $cat_slug);
    }

    
    /*
        Sobre-escribe cualquier categoria previa
    */
    static function setCategoriesByNames($pid, array $category_names){
        foreach ($category_names as $cat){
            wp_set_object_terms($pid, $cat, static::$cat_metakey);
        }
    }

    /*
        Agrega nuevas categorias por nombre

        No las asigna a ningun post
    */
    static function addCategoriesByNames($pid, Array $categos){
        $current_categos = static::getCategoryNames($pid);

        if (!empty($categos)){
            $current_categos = array_diff($current_categos, ['Uncategorized']);
        }

        $categos = array_merge($current_categos, $categos);

        static::setCategoriesByNames($pid, $categos);
    }

    static function getCategoryById($id, $output = 'OBJECT'){
        $category = get_term_by('term_id', $id, static::$cat_metakey, $output);

        if ($category === null || $category === false) {
            return null;
        }

        return $category;
    }

    /*
        Ej:

        Products::getCategoryBySlug('stivali-da-uomo-versace', ARRAY_A)
    */
    static function getCategoryBySlug($slug, $output = 'OBJECT')
    {
        $category = get_term_by('slug', $slug, static::$cat_metakey, $output);

        if ($category === null || $category === false) {
            return null;
        }

        return $category;
    }

    static function getCategoryIdBySlug($slug, $output = 'OBJECT')
    {
        $cat_obj = static::getCategoryBySlug($slug, $output);

        if (empty($cat_obj)) {
            return null;
        }

        return $cat_obj->term_id;
    }

    /*
        $only_subcategos  determina si las categorias de primer nivel deben o no incluirse 
    */
    static function getCategories(bool $only_subcategos = false)
    {
        static $ret;

        if ($ret !== null) {
            return $ret;
        }

        $taxonomy = static::$cat_metakey;
        $orderby = 'name';
        $show_count = 1;      // 1 for yes, 0 for no
        $pad_counts = 0;      // 1 for yes, 0 for no
        $hierarchical = 1;      // 1 for yes, 0 for no  
        $title = '';
        $empty = 0;

        $args = array(
            'taxonomy' => $taxonomy,
            'orderby' => $orderby,
            'show_count' => $show_count,
            'pad_counts' => $pad_counts,
            'hierarchical' => $hierarchical,
            'title_li' => $title,
            'hide_empty' => $empty
        );

        $all_categories = get_categories($args);

        if (!$only_subcategos) {
            return $all_categories;
        }

        $ret = [];
        foreach ($all_categories as $cat) {
            if ($cat->category_parent == 0) {
                $category_id = $cat->term_id;
                $link = '<a href="' . get_term_link($cat->slug, static::$cat_metakey) . '">' . $cat->name . '</a>';

                $args2 = array(
                    'taxonomy' => $taxonomy,
                    'child_of' => 0,
                    'parent' => $category_id,
                    'orderby' => $orderby,
                    'show_count' => $show_count,
                    'pad_counts' => $pad_counts,
                    'hierarchical' => $hierarchical,
                    'title' => $title,
                    'hide_empty' => $empty
                );
                $sub_cats = get_categories($args2);

                if ($sub_cats) {
                    foreach ($sub_cats as $sub_category) {
                        $ret[] = $sub_category;
                    }
                }
            }
        }

        return $ret;
    }

    static function getCategoSlugs()
    {
        $categos = static::getCategories();

        $ret = [];
        foreach ($categos as $catego) {
            $ret[] = $catego->slug;
        }

        return $ret;
    }

    static function getCategoryChildren($category_id)
    {
        return get_term_children($category_id, static::$cat_metakey);
    }

    static function getCategoryChildrenBySlug($category_slug)
    {
        $cat = static::getTermBySlug($category_slug);

        if ($cat === null) {
            return null;
        }

        $category_id = $cat->term_id;
        return static::getCategoryChildren($category_id);
    }

    /*
        https://devwl.pl/wordpress-get-all-children-of-a-parent-product-category/
    */
    static function getTopLevelCategories()
    {
        global $wpdb;

        $cat_metakey = static::$cat_metakey;

        $sql = "SELECT t.*,  tt.* FROM {$wpdb->prefix}terms t
            LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_taxonomy_id
            WHERE tt.taxonomy = '$cat_metakey' 
            AND tt.parent = 0
            ORDER BY tt.taxonomy;";

        return $wpdb->get_results($sql);
    }

    static function getAllCategories(bool $only_ids = false)
    {
        global $wpdb;

        $cat_metakey = static::$cat_metakey;

        $sql = "SELECT t.*,  tt.* 
            FROM {$wpdb->prefix}terms t
            LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_taxonomy_id
            WHERE tt.taxonomy = '$cat_metakey' 
            ORDER BY tt.taxonomy;";

        $res = $wpdb->get_results($sql, ARRAY_A);

        if ($only_ids) {
            return array_column($res, 'term_id');
        }

        return $res;
    }

    /*  
        Ej:

        Products::getCategoByDescription("ori:$cat_slug", false);
    */
    static function getCategoByDescription(string $desc, bool $strict = true)
    {
        global $wpdb;

        $w_desc = "tt.description = '$desc'";

        if (!$strict) {
            $w_desc = "tt.description LIKE '%$desc%'";
        }

        $sql = "SELECT t.*,  tt.* 
            FROM {$wpdb->prefix}terms t
            LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_taxonomy_id
            WHERE $w_desc";

        $res = $wpdb->get_results($sql, ARRAY_A);

        return $res;
    }

    /*
        Categories  <- PostID
    
        Retorna algo como:

        [
            {
                "id": 193,
                "name": "Plugins para WordPress",
                "slug": "plugins-para-wordpress"
            },
            {
                "id": 197,
                "name": "SEO",
                "slug": "seo"
            }
        ]
    */
    static function getCategoryAttributes($post)
    {
        $obj = [];

        $category_ids = $post->get_category_ids();

        foreach ($category_ids as $cat_id) {
            $terms = get_term_by('id', $cat_id, static::$cat_metakey);

            $obj[] = [
                'id' => $terms->term_id,
                'name' => $terms->name,
                'slug' => $terms->slug
            ];
        }

        return $obj;
    }

    /*
        Category  <----- CategoryName

        Solo devuelve una aunque el nombre se repita con distinto slug
    */
    static function getCategoryByName($name, string $output = OBJECT)
    {
        $category = get_term_by('name', $name, static::$cat_metakey, $output);

        if ($category === null || $category === false) {
            return null;
        }

        return $category;
    }

    /*
        CategoryId  <- CategoryName
    */
    static function getCategoryIdByName($name)
    {
        $category = get_term_by('name', $name, static::$cat_metakey);

        if ($category === null || $category === false) {
            return null;
        }

        return $category->term_id;
    }

    static function getCategoryNameByID($cat_id)
    {
        if ($term = get_term_by('id', $cat_id, static::$cat_metakey)) {
            return $term->name;
        }

        throw new \InvalidArgumentException("Category ID '$cat_id' not found");
    }
    
    /*
        CategoryName  <- ProductID
    */
    static function getCategoryNameByPostID($cat_id){
        return parent::getCategoryNameByID($cat_id);
    }

    /*
        Para categorias "built-in"

        Ej:

        getPostsByCategoryId(7, 10, ['name' => 'ASC'])
    
    */
    static function getPostsByBuiltInCategoryId($id, $limit = -1, $order_by = null)
    {
        if (!empty($order_by)) {
            $col = array_key_first($order_by);
            $order = $order_by[$col];
        }

        $args = array(
            'category' => $id,
            'posts_per_page' => $limit,
            'orderby' => $col,
            'order' => $order,
            'post_type' => static::$post_type
        );

        return get_posts($args);
    }

    /*  
        En la mayoria dew casos, la categoria se crea con slug y cuando se edita es por id 

        Sin embargo, en teoria se podria pasar el "term_id" en $args, creando la taxonomia con su id <-------------- *
    */
    static function createCatego($name, $slug = null, $description = null, $id_parent = null, $image_url = null, $args = [])
    {
        return static::createTaxonomy(static::$cat_metakey, $name, $slug, $description, $id_parent, $image_url, $args);
    }

    // by slug
    static function updateCatego($name, $slug, $description = null, $id_parent = null, $image_url = null, $args = [])
    {
        return static::updateTaxonomyBySlug(static::$cat_metakey, $slug, $name, $description, $id_parent, $image_url, $args);
    }

    static function createOrUpdateCategoBySlug($slug, $name, $description = null, ?int $id_parent = null, $image_url = null, $args = [])
    {
        return static::createOrUpdateTaxonomyBySlug(static::$cat_metakey, $slug, $name, $description, $id_parent, $image_url, $args);
    }

    static function updateCategoById($term_id, $name, $slug = null, $description = null, $id_parent = null, $image_url = null, $args = [])
    {
        return static::updateTaxonomyById(static::$cat_metakey, $term_id, $name, $slug, $description, $id_parent, $image_url, $args);
    }

    /*
        Crea categoria 

        Con $preserve_inherence si la categoria existe y tenia parent no-nulo pero el nuevo parent es nulo => no la modifca. 
        
        <-- Esto evita resetear el padre con valor nulo
        
        @return category_id

        TO-DO

        Migrar la funcionalidad a createOrUpdateTaxonomyBySlug()
    */
    static function createOrUpdateCategory($name, $slug = null, $description = null, $parent = null, $url = null, $preserve_inherence = true){
        if (!empty($id_parent)){
            return static::createOrUpdateCategoBySlug($slug, $name, $description,  $id_parent);
        }

        if (empty($description) && !empty($url)){
            $description = "<!-- ori:$url -->";
        }

        // objeto de la categoria
        $_cat   = static::getCategoryByName($name, ARRAY_A);

        $_parent = null;
        if (!empty($_cat)){  # Si existe ...
            // padre actual
            $_parent = $_cat['parent'];
        }

        // Solo si la categoria no existe O ... si existe pero no tiene actualmente padre 
        if (empty($_cat) || ($preserve_inherence && !empty($_cat) && empty($_parent))){
            if (!empty($parent)){
                if (is_int($parent)){
                    $id_parent = $parent;
                } else {
                    $id_parent = static::getCategoryIdBySlug($parent);

                    if (empty($id_parent)){
                        $id_parent = static::createCatego("TMP NAME", $parent); 
                    }
                }
            }            

            $cid  = static::createOrUpdateCategoBySlug($slug, $name, $description,  $id_parent ?? null);
        } else {
            $cid  = $_cat['term_id'];
        }

        // Asociar la URL como metadato si no está vacío
        if (!empty($url)) {
            update_term_meta($cid, 'category_url', $url);
        }

        return $cid;
    }

    static function extractUrlFromDescription(string $desc){
        return Strings::match($desc, '|<!-- ori:([^ ]+) -->|', 1);
    }

    static function sortCategories(array $categories): array
    {
        // Convert the array into a dictionary indexed by slug for easy access
        $categoryDict = [];
        foreach ($categories as $category) {
            $categoryDict[$category['slug']] = $category;
        }

        $sorted = [];
        $visited = [];

        // Helper function to perform DFS
        $visit = function ($category) use (&$visit, &$sorted, &$visited, $categoryDict) {
            if (isset($visited[$category['slug']])) {
                return;
            }

            $visited[$category['slug']] = true;

            // Visit the parent category first
            if ($category['parent_slug'] !== null && isset($categoryDict[$category['parent_slug']])) {
                $visit($categoryDict[$category['parent_slug']]);
            }

            // Add the current category to the sorted list
            $sorted[] = $category;
        };

        // Visit each category
        foreach ($categories as $category) {
            $visit($category);
        }

        return $sorted;
    }

    /*
        dd(
            Products::getCategoryUrlBySlug('ferreteria')
        );
    */
    static function getCategoryUrlBySlug($slug) {
        // Obtener la categoría por slug
        $category = get_term_by('slug', $slug, 'product_cat');
    
        if ($category && !is_wp_error($category)) {
            // Obtener el metadato 'category_url'
            $url = get_term_meta($category->term_id, 'category_url', true);
            return $url;
        }
    
        return null;  // O lanzar una excepción si prefieres manejarlo de otra manera
    }
    
    /*
        Devuelve toda las categorias de un post

        Ej:

        $post_cats = [];
        foreach ($pids as $pid){
            $post_cats[$pid] = array_column(Products::getCategoriesByPost($pid), 'slug');
        }
    */
    static function getCategoriesByPost($pid, $output = ARRAY_A){
        $post_cats = [];

        $cat_ids = static::getCategoriesById($pid);

        foreach ($cat_ids as $cid){
            $post_cats[] = static::getCategoryById($cid, $output);
	    }

        return $post_cats;
    }

    /*
        Crea todas las categorias seteando la categoria padre en cada caso. 

        El HOOK puede ser 'wp_loaded' pero no cualquiera ***

        NO funciona con hooks como 'woocommerce_loaded' o 'woocommerce_init'

        Dentro de la descripcion de la categoria deja "<!-- ori:$url -->"
        si se enviara y tambien se guarda como metadato y se puede recuperar con getCategoryUrlBySlug()

        - Debe recibir un Array como:  --al menos con slug y name--

        Ej:
        
        $categories = [           
            [
                'name' => 'Materiales de Construcción',
                'slug' => 'materiales-de-construccion',
                'parent_slug' => 'ferreteria',
                'url' => 'https://example.com/ferreteria/materiales-de-construccion'
            ],

            [
                'name' => 'Eléctricas',
                'slug' => 'electricas',
                'parent_slug' => 'herramientas',
                'url' => 'https://example.com/ferreteria/herramientas/electricas'
            ],

            [
                'name' => 'Ferretería',
                'slug' => 'ferreteria',
                'parent_slug' => null,
                'url' => 'https://example.com/ferreteria'
            ],

            [
                'name' => 'Herramientas',
                'slug' => 'herramientas',
                'parent_slug' => 'ferreteria',
                'url' => 'https://example.com/ferreteria/herramientas'
            ],
            
        ];

        // Call the method to create categories
        try {
            Products::createCategoryTree($categories);

            $cats = Products::getAllCategories(true);

            foreach ($cats as $cat){
                dd(
                    Products::breadcrumb($cat), null, false
                );
            }
                
        } catch (\Exception $e) {
            dd(
                $e->getMessage()
            );
        }  

        Salida:

        / Ferretería > Materiales de Construcción
        / Ferretería > Herramientas
        / Ferretería
        / Eléctricas

    */
    public static function createCategoryTree(array $categories)
    {
        $sortedCategories = static::sortCategories($categories);

        foreach ($sortedCategories as $category) {
            self::createOrUpdateCategory(
                $category['name'],
                $category['slug'],
                $category['description'] ?? '',
                $category['parent_slug'],
                $category['url'] ?? ''          # url original cuando se hace web scraping 
            );
        }
    }

    /*
        Dado el id de una categoria devuelve algo como

        A > A2 > A2-1 > A2-1a

        Ej:

        $cats = Products::getAllCategories(true);

        foreach ($cats as $cat){
            dd(
                Products::breadcrumb($cat), null, false
            );
        }
    */
    static function breadcrumb(int $cat_id)
    {
        $search = $cat_id;

        $path = [];
        $parent_id = null;

        while ($parent_id !== 0) {
            $catego = get_term($cat_id);
            $parent_id = $catego->parent;

            if ($parent_id == 0) {
                break;
            }

            $path[] = get_the_category_by_ID($parent_id);
            $cat_id = $parent_id;
        }

        $path = array_reverse($path);
        $last_sep = empty($path) ? '' : '>';
        $first_sep = '/';

        $breadcrumb = ltrim($first_sep . ' ') . ltrim(implode(' > ', $path) . " $last_sep " . get_the_category_by_ID($search));

        return $breadcrumb;
    }

    /* 
    * Actualiza la imagen asociada a una categoría
    *
    * Ejemplo de uso:
    * 
    * $category_id = 446; // ID de la categoría de productos a actualizar
    * $image = 'http://woo1.lan/wp-content/uploads/2024/04/100420241712751485.jpeg'; // URL o ruta local de la imagen
    * 
    * try {
    *     $result = Products::updateProductImageCategory($category_id, $image);
    *     // Manejar el resultado según sea necesario
    *     dd($result);
    * } catch (Exception $e) {
    *     // Capturar y manejar cualquier excepción ocurrida durante el proceso
    *     dd($e->getMessage());
    * }
    *
    * @param int    $category_id  ID de la categoría de productos a la cual se le asignará la imagen.
    * @param string $image        URL o ruta local de la imagen a asignar. Puede ser una URL completa o una ruta de archivo local.
    *                             Si es una URL, la función intentará cargar la imagen usando 'media_sideload_image'.
    *                             Si es una ruta local, la función intentará subir el archivo primero y luego asignar la imagen.
    * @return bool|int|WP_Error   Devuelve true si la actualización de metadatos de la categoría fue exitosa.
    *                             Devuelve el ID del medio subido si se subió correctamente.
    *                             Devuelve un objeto WP_Error si ocurrió algún error durante el proceso.
    * @throws Exception           Lanza una excepción si la categoría no existe o si ocurre un error crítico durante el proceso.
    */
    static function updateProductImageCategory($category_id, $image){
        if (Url::isURL($image)){
            $media_id = static::uploadImage($image);
        } else {
            if ((strlen($image) < 1024)){
                if (!file_exists($image)) {
                    throw new \Exception('Image file does not exist.');
                }

                $image = file_get_contents($image);
            }

            // Asegura que las funciones de medios de WordPress estén disponibles
            if (!function_exists('media_sideload_image')) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
            }

            // Fatal error: Uncaught Error: Call to undefined function boctulus\SW\core\traits\media_sideload_image()
            $media_id = media_sideload_image($image, 0, '', 'id');

            if (is_wp_error($media_id)) {
                throw new \Exception('Error uploading image: ' . $media_id->get_error_message());
            }
        }     

        $ok = update_term_meta($category_id, 'thumbnail_id', $media_id); 

        if (is_wp_error($ok)) {
            throw new \Exception('Error trying to update category metadata');
        }

        return $ok;
    }

}
