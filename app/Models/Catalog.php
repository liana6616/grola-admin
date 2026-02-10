<?php

namespace app\Models;

use app\Model;
use app\Form;

class Catalog extends Model
{
	public const TABLE = 'catalog';

	/**
	* Получает параметры товара
	*/
	public static function getParams($catalogId)
	{
	  $params = [];
	  
	  // Находим товар
	  $catalog = self::findById($catalogId);
	  if (!$catalog) {
	      return $params;
	  }
	  
	  // Находим категорию товара
	  $category = Categories::findById($catalog->category_id);
	  if (!$category || !$category->template_id) {
	      return $params;
	  }
	  
	  // Получаем группы параметров для шаблона
	  $groups = ParamsGroups::findWhere("WHERE template_id = " . $category->template_id . " ORDER BY rate ASC");
	  
	  foreach ($groups as $group) {
	      // Получаем параметры группы
	      $groupItems = ParamsGroupsItems::findWhere("WHERE group_id = " . $group->id . " AND show = 1 ORDER BY rate ASC");
	      
	      $groupParams = [];
	      foreach ($groupItems as $item) {
	          // Получаем значение параметра для этого товара
	          $paramValue = CatalogParams::findWhere("WHERE catalog_id = " . $catalogId . " AND param_id = " . $item->param_id);
	          
	          $groupParams[] = [
	              'param' => Params::findById($item->param_id),
	              'value' => !empty($paramValue) ? $paramValue[0]->value : '',
	              'type' => $item->type,
	              'directory_id' => $item->directory_id,
	              'filter' => $item->filter
	          ];
	      }
	      
	      if (!empty($groupParams)) {
	          $params[$group->name] = $groupParams;
	      }
	  }
	  
	  return $params;
	}

	/**
	* Сохраняет параметры товара
	*/
	public static function saveParams($catalogId, $params)
	{
	  // Удаляем старые параметры
	  $oldParams = CatalogParams::where("WHERE catalog_id = ?", [$catalogId]);
	  foreach ($oldParams as $param) {
	      $param->delete();
	  }
	  
	  // Сохраняем новые параметры
	  foreach ($params as $paramId => $value) {
	      if (!empty($value)) {
	          $param = new CatalogParams();
	          $param->catalog_id = $catalogId;
	          $param->param_id = $paramId;
	          $param->value = trim($value);
	          $param->edit_date = date('Y-m-d H:i:s');
	          $param->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
	          $param->save();
	      }
	  }
	}

	/**
	* Получает цены по весу для товара
	*/
	public static function getPrices($catalogId)
	{
	  return CatalogPrices::findWhere("WHERE catalog_id = " . $catalogId . " ORDER BY weight ASC");
	}

	/**
	* Получает готовую продукцию для товара
	*/
	public static function getFinishedProducts($catalogId)
	{
	  $result = [];
	  $links = FinishedProductsCatalog::findWhere("WHERE catalog_id = " . $catalogId);
	  
	  foreach ($links as $link) {
	      $product = FinishedProducts::findById($link->product_id);
	      if ($product) {
	          $result[] = $product;
	      }
	  }
	  
	  return $result;
	}

	/**
	* Сохраняет привязку к готовой продукции
	*/
	public static function saveFinishedProducts($catalogId, $productIds)
	{
	  // Удаляем старые связи
	  $oldLinks = FinishedProductsCatalog::where("WHERE catalog_id = ?", [$catalogId]);
	  foreach ($oldLinks as $link) {
	      $link->delete();
	  }
	  
	  // Сохраняем новые связи
	  if (!empty($productIds)) {
	      // Проверяем, пришла ли строка или массив
	      if (is_string($productIds)) {
	          $ids = explode('|', $productIds);
	      } else if (is_array($productIds)) {
	          $ids = $productIds;
	      } else {
	          return;
	      }
	      
	      foreach ($ids as $productId) {
	          if (!empty($productId)) {
	              $link = new FinishedProductsCatalog();
	              $link->catalog_id = $catalogId;
	              $link->product_id = (int)$productId;
	              $link->edit_date = date('Y-m-d H:i:s');
	              $link->edit_admin_id = $_SESSION['admin']['id'] ?? 0;
	              $link->save();
	          }
	      }
	  }
	}

	/**
	* Получает хлебные крошки для админки
	*/
	public static function adminBreadCrumbs($categoryId)
	{
	  $b = [];

	  if(!empty($categoryId)) {
	      $bread = '';
	      $res = Categories::findById($categoryId);
	      
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
	              $b[] = array('name' => $item->name_menu, 'id' => $item->id, 'url' => '/'.ADMIN_LINK.'/catalog?category='.$item->id);
	          }
	      }
	  }

	  return $b;
	}

	/**
	* Проверяет URL при маршрутизации
	*/
	public static function check($parameters)
	{
	  $f = 1;
	  $ids = 0;

	  $url = array_pop($parameters);
	  $item = self::findWhere('WHERE url="'.$url.'"');
	  
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
	  else {
	      $f = 0;
	  }

	  return $f;
	}

	/**
	* Получает полный URL товара
	*/
	public static function getUrl($id)
	{
	  $url = '';

	  $item = self::findById($id);
	  
	  if (!$item) {
	      return '';
	  }

	  // Получаем URL категории
	  $categoryUrl = Categories::getUrl($item->category_id);
	  
	  if (!empty($categoryUrl)) {
	      $url = $categoryUrl . '/' . $item->url;
	  } else {
	      $url = '/' . $item->url;
	  }

	  return $url;
	}


	public static function catalogPriceCard(?int $id = null, ?int $priceIndex = null)
	{
	    $configPath = ROOT . '/config/modules/catalog.php';
	    
	    if (file_exists($configPath)) {
	        $config = require $configPath;
	    } else {
	        $config = [];
	    }

	    if (!empty($id)) {
	        $price = CatalogPrices::findById($id);
	    } else {
	        $price = new CatalogPrices();
	        $price->id = 0;
	        $price->weight = '';
	        $price->price = '';
	        $price->count = '';
	        $price->unit = '';
	    }

	    ob_start();
	    include ROOT . '/private/views/components/catalogPriceCard.php';
	    $html = ob_get_clean();
	    
	    return $html;
	}
}