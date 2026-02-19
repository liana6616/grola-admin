<?php
namespace app\Controllers;

use app\Controller;
use app\Models\Catalog;
use app\Models\Categories;

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
        
        // Получаем категорию (для хлебных крошек)
        if (!empty($view->product->category_id)) {
            $view->category = Categories::findById($view->product->category_id);
        }
        
        return $view->show('pages/catalog-card.php');
    }
}