<?php

namespace boctulus\SW\core\traits;

/*
    Crea menus para el Navbar del frontoffice

    Se crean a partir de una post type
*/
trait NavMenuTrait 
{ 
    /*
        Punto de entrada

        Ej:

        Products::createNavMenu('MainTop'); 
    */
    static function createNavMenu($menuName, bool $hide_empty = false){
        // Obtén el ID del menú por su nombre
        $menuObject = wp_get_nav_menu_object($menuName);
        if (!$menuObject) {
            // Crea el menú si no existe
            $menuId = wp_create_nav_menu($menuName);
        } else {
            $menuId = $menuObject->term_id;
        }

        // Obtén todas las categorías de productos
        $categories = get_terms(array(
            'taxonomy'   => static::$cat_metakey,
            'hide_empty' => $hide_empty,
        ));

        // Construye un array con las categorías jerárquicas
        $categoriesHierarchy = static::buildCategoriesHierarchy($categories);

        // Agrega los elementos del menú
        foreach ($categoriesHierarchy as $category) {
            static::addMenuItem($menuId, $category);
        }
    }

    static protected function buildCategoriesHierarchy($categories) {
        $categoriesByParent = array();
        foreach ($categories as $category) {
            $categoriesByParent[$category->parent][] = $category;
        }
        return static::buildCategoriesTree(0, $categoriesByParent);
    }

    static protected function buildCategoriesTree($parentId, $categoriesByParent) {
        if (!isset($categoriesByParent[$parentId])) {
            return array();
        }

        $categoriesTree = array();
        foreach ($categoriesByParent[$parentId] as $category) {
            $category->children = static::buildCategoriesTree($category->term_id, $categoriesByParent);
            $categoriesTree[] = $category;
        }
        return $categoriesTree;
    }

    static protected function addMenuItem($menuId, $category, $parentItemId = 0) {
        $itemData = array(
            'menu-item-title' => $category->name,
            'menu-item-object' => static::$cat_metakey,
            'menu-item-object-id' => $category->term_id,
            'menu-item-type' => 'taxonomy',
            'menu-item-status' => 'publish',
            'menu-item-parent-id' => $parentItemId,
        );

        $itemId = wp_update_nav_menu_item($menuId, 0, $itemData);

        foreach ($category->children as $childCategory) {
            static::addMenuItem($menuId, $childCategory, $itemId);
        }
    }
}