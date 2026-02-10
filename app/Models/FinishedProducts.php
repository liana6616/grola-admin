<?php

namespace app\Models;

use app\Model;

class FinishedProducts extends Model
{
	public const TABLE = 'finished_products';

	public static function adminBreadCrumbs($id)
	{
		$b = [];

		if(!empty($id)) {
			$bread = '';
			$res = FinishedProducts::findById($id);
			while($res->id) {
				if($bread <> '') $bread .= ",";
				$bread .= $res->id;
				if($res->parent <> 0 ) $res = FinishedProducts::findById($res->parent);
				else $res->id = false;
			}
			$bread = array_reverse(explode(',',$bread));

			foreach($bread AS $item) {
				$item = FinishedProducts::findById($item);
				$b[] = array('name' => $item->name, 'id' => $item->id, 'url' => '/'.ADMIN_LINK.'/finished_products?parent='.$item->id);
			}
		}

		return $b;
	}

	/**
     * Получает готовую продукцию с отступами для иерархического отображения
     */
    public static function getHierarchical($excludeId = 0)
    {
        $products = [];
        
        // Получаем все категории, отсортированные по parent и rate
        $where = "WHERE id != ? ORDER BY parent ASC, rate DESC, id ASC";
        $allProducts = self::where($where, [$excludeId]);
        
        // Создаем массив для построения дерева
        $tree = [];
        foreach ($allProducts as $product) {
            $parentKey = $product->parent ?? '0';
            if (!isset($tree[$parentKey])) {
                $tree[$parentKey] = [];
            }
            $tree[$parentKey][] = $product;
        }
        
        // Рекурсивно строим список с отступами
        self::buildHierarchicalList($tree, '0', 0, $products);
        
        return $products;
    }

    /**
     * Рекурсивно строит иерархический список
     */
    private static function buildHierarchicalList(&$tree, $parentId, $level, &$result)
    {
        $parentKey = $parentId ?? '0';
        
        if (!isset($tree[$parentKey])) {
            return;
        }
        
        foreach ($tree[$parentKey] as $product) {
            // Создаем копию продукта с добавленным префиксом
            $hierarchicalProduct = clone $product;
            $hierarchicalProduct->name = str_repeat('— ', $level) . $product->name;
            
            $result[] = $hierarchicalProduct;
            
            // Рекурсивно обрабатываем дочерние
            $childKey = $product->id ?? '0';
            self::buildHierarchicalList($tree, $childKey, $level + 1, $result);
        }
    }
}