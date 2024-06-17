<?php

namespace boctulus\SW\core\traits;

trait ProductCategoriesTrait
{
     // Get Products by Category

    /*  
        Alias de getPostsByCategory()

        Nota: 
        
        Hace AND entre cada categoria en la query
    */
    static function getProductsByCategory(string $by, Array $catego_ids, $post_status = null)
    {
        return static::getPostsByCategory($by, $catego_ids, $post_status);
    }

    static function getProductsByCategoryID(Array $cate_ids, $post_status = null){
        return static::getProductsByCategory('id', $cate_ids, $post_status);  // no seria "ID" ?
    }


    // Get category

    /*
        CategoryName  <- ProductID  

        No mover de aqui
    */
    static function getCategoryNameByProductID($cat_id){
        return parent::getCategoryNameByID($cat_id);
    }

    // Get categories

    /*
        Category IDs  <- SKU
    */
    static function getCategoriesByProductSKU($sku){
        $pid = static::getProductIDBySKU($sku);
        
        return static::getCategoriesByProductID($pid);
    }  

    /*
        Category IDs  <- Product ID
    */    
    static function getCategoriesById($pid){
        return wc_get_product_term_ids($pid, static::$cat_metakey);
    }
}
