<?php

namespace app\Models;

use app\Model;

class Categories extends Model
{
    public const TABLE = 'categories';

    public static function adminBreadCrumbs($id)
    {
        $b = [];

        if(!empty($id)) {
            $bread = '';
            $res = Categories::findById($id);
            
            if (!$res) {
                return $b;
            }
            
            while($res && $res->id) {
                if($bread <> '') $bread .= ",";
                $bread .= $res->id;
                if($res->parent <> 0) {
                    $res = Categories::findById($res->parent);
                } else {
                    $res->id = false;
                }
            }
            $bread = array_reverse(explode(',',$bread));

            foreach($bread AS $item) {
                $item = Categories::findById($item);
                if ($item) {
                    $b[] = array('name' => $item->name_menu, 'id' => $item->id, 'url' => '/'.ADMIN_LINK.'/categories?parent='.$item->id);
                }
            }
        }

        return $b;
    }

    public static function getCategory($url) {
        if (empty($url)) return;

        $where = " AND show = 1 AND name<>''";
        $category = self::findByUrl($url, true, $where);
        if (empty($category)) return;

        if (!empty($category->parent)) {
            $parent_info = self::findById($category->parent, $where);
            $category->parent_info = $parent_info;
        }

        return $category;
    }

    public static function getChilds($id) {
        if (empty($id) && $id <> 0) return;
        $childs = self::findWhere("WHERE parent='{$id}' AND `show` = 1 ORDER BY rate DESC, id ASC");
        return $childs;
    }
    
    /**
     * Получает количество товаров в категории
     */
    public static function getProductsCount($categoryId) {
        $result = Catalog::query(
            "SELECT COUNT(*) as count 
             FROM catalog 
             WHERE `show` = 1 AND `is_draft` = 0 AND category_id = {$categoryId}"
        );
        
        return !empty($result) ? (int)$result[0]->count : 0;
    }

    public static function check($parameters)
    {
        $f = 1;
        $ids = 0;

        $url = array_pop($parameters);
        $item = Categories::findWhere('WHERE url="'.$url.'"');
        if(!empty($item) && !empty($parameters)) {
            foreach($parameters AS $param) {
                $obj = Categories::findWhere('WHERE parent='.$ids.' AND url="'.$param.'"');
                if(!empty($obj)) {
                    $ids = $obj[0]->id;
                }
                else {
                    $f = 0;
                    break;
                }
            }
        }
        else $f = 0;

        return $f;
    }

    public static function getUrl($id)
    {
        $url = '';

        $item = Categories::findById($id);
        
        if (!$item) {
            return '';
        }

        $parents = [];
        $current = $item;
        
        while ($current && $current->id) {
            $parents[] = $current;
            if ($current->parent > 0) {
                $current = Categories::findById($current->parent);
            } else {
                $current = null;
            }
        }

        $parents = array_reverse($parents);
        
        foreach($parents AS $category) {
            $url .= '/'.$category->url;
        }

        return $url;
    }

    public static function getHierarchical($excludeId = 0)
    {
        $categories = [];
        
        $where = "WHERE id != ? ORDER BY parent ASC, rate DESC, id ASC";
        $allCategories = self::where($where, [$excludeId]);
        
        $tree = [];
        foreach ($allCategories as $category) {
            $parentKey = $category->parent ?? '0';
            if (!isset($tree[$parentKey])) {
                $tree[$parentKey] = [];
            }
            $tree[$parentKey][] = $category;
        }
        
        self::buildHierarchicalList($tree, '0', 0, $categories);
        
        return $categories;
    }

    private static function buildHierarchicalList(&$tree, $parentId, $level, &$result)
    {
        $parentKey = $parentId ?? '0';
        
        if (!isset($tree[$parentKey])) {
            return;
        }
        
        foreach ($tree[$parentKey] as $category) {
            $hierarchicalCategory = clone $category;
            $hierarchicalCategory->name = str_repeat('— ', $level) . $category->name;
            
            $result[] = $hierarchicalCategory;
            
            $childKey = $category->id ?? '0';
            self::buildHierarchicalList($tree, $childKey, $level + 1, $result);
        }
    }
}