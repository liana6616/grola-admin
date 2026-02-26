<?php
namespace app\Controllers;

use app\Controller;
use app\Models\Catalog;
use app\Models\Categories;
use app\Models\Gallery;
use app\Models\Files;
use app\Models\Messengers;

class CatalogCardController extends Controller {
    protected function handle(...$params) {
        $view = $this->view;
        
        // Получаем URL товара
        $productUrl = end($params);
        
        // Ищем товар
        $products = Catalog::where("WHERE url = '{$productUrl}' AND `show`=1 AND is_draft=0 LIMIT 1");
        
        if (empty($products)) {
            return $view->show('errors/404.php');
        }
        
        // Передаём товар в шаблон
        $view->product = $products[0];

        // ПОХОЖИЕ ТОВАРЫ
        $view->similar_products = Catalog::where("WHERE `show`=1 AND is_draft=0 AND category_id = {$view->product->category_id} AND id != {$view->product->id} ORDER BY rate DESC, id ASC") ?: [];
        
        // Получаем категорию и childs для ссылок
        if (!empty($view->product->category_id)) {
            $view->category = Categories::findById($view->product->category_id);
            
            // Получаем родительские категории для правильных ссылок
            $parent_id = $view->category->parent ?? 0;
            $view->childs = Categories::getChilds($parent_id) ?: [];
        }

        $view->messengers = Messengers::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
        
        // ИСПРАВЛЕНО: возвращаем -1 для галереи
        $view->gallery = Gallery::where("WHERE `show`=1 AND `type`='product' AND `ids` = " . ($view->product->id - 1) . " ORDER BY rate DESC, id ASC") ?: [];
        
        $view->file = Files::where("WHERE `show`=1 AND `type`='catalog' ORDER BY rate DESC, id ASC") ?: [];

        return $view->show('pages/catalog-card.php');
    }
}