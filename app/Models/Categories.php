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

		// Собираем URL по цепочке родителей
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

		// Разворачиваем массив, чтобы начать с корня
		$parents = array_reverse($parents);
		
		foreach($parents AS $category) {
			$url .= '/'.$category->url;
		}

		return $url;
	}

	/**
	 * Получает категории с отступами для иерархического отображения
	 */
	public static function getHierarchical($excludeId = 0)
	{
		$categories = [];
		
		// Получаем все категории, отсортированные по parent и rate
		$where = "WHERE id != ? ORDER BY parent ASC, rate DESC, id ASC";
		$allCategories = self::where($where, [$excludeId]);
		
		// Создаем массив для построения дерева
		$tree = [];
		foreach ($allCategories as $category) {
			// Используем '0' вместо null для корневых категорий
			$parentKey = $category->parent ?? '0';
			if (!isset($tree[$parentKey])) {
				$tree[$parentKey] = [];
			}
			$tree[$parentKey][] = $category;
		}
		
		// Рекурсивно строим список с отступами
		self::buildHierarchicalList($tree, '0', 0, $categories);
		
		return $categories;
	}

	/**
	 * Рекурсивно строит иерархический список категорий
	 */
	private static function buildHierarchicalList(&$tree, $parentId, $level, &$result)
	{
		$parentKey = $parentId ?? '0';
		
		if (!isset($tree[$parentKey])) {
			return;
		}
		
		foreach ($tree[$parentKey] as $category) {
			// Создаем копию категории с добавленным префиксом для отображения
			$hierarchicalCategory = clone $category;
			$hierarchicalCategory->name = str_repeat('— ', $level) . $category->name;
			
			$result[] = $hierarchicalCategory;
			
			// Рекурсивно обрабатываем дочерние категории
			$childKey = $category->id ?? '0';
			self::buildHierarchicalList($tree, $childKey, $level + 1, $result);
		}
	}
}