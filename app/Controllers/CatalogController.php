<?php
namespace app\Controllers;

use app\Controller;
use app\Helpers;
use app\Models\Pages;
use app\Models\Admins;
use app\Models\Categories;
use app\Models\Catalog;
use app\Models\Messengers;

class CatalogController extends Controller {
    protected function handle(...$params) {
        try {
            $view = $this->view;
            $view->params = $params;

            // Очищаем параметры от пустых значений
            $segments = array_filter($params, function($seg) {
                return !is_null($seg) && $seg !== '';
            });
            $segments = array_values($segments);
            
            // Получаем GET-параметр subcat (для обратной совместимости)
            $selectedSubcat = isset($_GET['subcat']) ? (int)$_GET['subcat'] : 0;
            $view->selectedSubcat = $selectedSubcat;
            
            // --- Определяем текущую категорию по сегментам URL ---
            $currentCategory = null;
            $remainingSegments = $segments;
            
            // Ищем категорию, собирая URL по частям
            // Например: ['door', 'interior'] -> ищем категорию с url 'door/interior'
            if (!empty($segments)) {
                $searchUrl = implode('/', $segments);
                $currentCategory = Categories::getCategory($searchUrl);
                
                // Если не нашли по полному пути, пробуем искать поэтапно (для поддержки старых ссылок)
                if (!$currentCategory) {
                    foreach ($segments as $index => $segment) {
                        $partialUrl = implode('/', array_slice($segments, 0, $index + 1));
                        $cat = Categories::getCategory($partialUrl);
                        if ($cat) {
                            $currentCategory = $cat;
                            $remainingSegments = array_slice($segments, $index + 1);
                        } else {
                            break;
                        }
                    }
                } else {
                    // Нашли по полному пути, значит остатка нет
                    $remainingSegments = [];
                }
            }
            
            $view->currentCategory = $currentCategory;
            
            // --- Если есть остаток сегментов после категории, пробуем найти товар ---
            if (!empty($remainingSegments) && $currentCategory) {
                // Последний сегмент - возможный URL товара
                $productUrl = $remainingSegments[0];
                
                // Ищем товар в текущей категории
                $product = Catalog::findWhere(
                    "WHERE url = '{$productUrl}' AND category_id = {$currentCategory->id} AND `show` = 1 AND `is_draft` = 0"
                );
                
                if (!empty($product) && isset($product[0])) {
                    // Нашли товар - показываем его карточку
                    $view->product = $product[0];
                    
                    // Получаем хлебные крошки для категории
                    $view->breadCrumbs = $this->getBreadCrumbsForCategory($currentCategory->id);
                    
                    // Добавляем в крошки текущий товар
                    // $view->breadCrumbs[] = [
                    //     'name' => $view->product->name,
                    //     'url' => ''
                    // ];
                    
                    // Получаем дополнительные данные для товара
                    $view->messengers = Messengers::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
                    
                    return $view->show('pages/product.php'); // Создайте этот шаблон
                }
            }
            
            // --- Если товар не найден, показываем страницу категории ---
            
            // Основная страница каталога (без категории)
            if (empty($currentCategory)) {
                $view->is_mainCategories = 1;
                $view->catalog = Catalog::where('WHERE `show`=1 AND `is_draft`=0 ORDER BY rate DESC, id ASC') ?: [];
                
                // Получаем корневые категории
                $childs = Categories::getChilds(0);
                
                // Добавляем количество товаров для каждой категории
                foreach ($childs as $cat) {
                    $cat->products_count = Categories::getProductsCount($cat->id);
                }
                
                $view->childs = $childs;
                
                // Хлебные крошки для главной страницы каталога
                $page = Pages::findById(14);
                $view->breadCrumbs = Pages::breadCrumbs($page->id);
                
            } else {
                // Страница конкретной категории
                
                // Получаем подкатегории
                $subcategories = Categories::getChilds($currentCategory->id);
                
                // Добавляем количество товаров для подкатегорий
                foreach ($subcategories as $subcat) {
                    $subcat->products_count = Categories::getProductsCount($subcat->id);
                }
                
                $view->childs = $subcategories;
                
                // Собираем все ID категорий для фильтрации товаров
                $ids = [(int)$currentCategory->id];
                foreach ($subcategories as $c) {
                    $ids[] = (int)$c->id;
                }
                $in = implode(',', array_unique($ids));
                
                // Получаем товары с учетом фильтра по подкатегории
                if ($selectedSubcat > 0) {
                    $view->catalog = Catalog::where(
                        "WHERE `show`=1 AND `is_draft`=0 AND category_id = {$selectedSubcat}
                        ORDER BY rate DESC, id ASC"
                    ) ?: [];
                } else {
                    $view->catalog = Catalog::where(
                        "WHERE `show`=1 AND `is_draft`=0 AND category_id IN ({$in})
                        ORDER BY rate DESC, id ASC"
                    ) ?: [];
                }
                
                // Получаем хлебные крошки для текущей категории
                $view->breadCrumbs = $this->getBreadCrumbsForCategory($currentCategory->id);
            }
            
            // Общие данные для страниц каталога
            $page = Pages::findById(14);
            if (!$page) {
                throw new \Exception('Страница catalog не найдена');
            }
            
            $view->page = $page;
            $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
            $view->messengers = Messengers::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            
            // Устанавливаем SEO
            self::setSeo($view, $page);
            
            return $view->show('pages/catalog.php');
            
        } catch (\Exception $e) {
            error_log('Ошибка в CatalogController->handle: ' . $e->getMessage());
            return $this->showErrorPage(500, 'Произошла ошибка при загрузке страницы');
        }
    }
    
    /**
     * Получает хлебные крошки для категории
     */
    private function getBreadCrumbsForCategory($categoryId)
    {
        $breadCrumbs = [];
        
        // Начинаем с главной страницы каталога
        $page = Pages::findById(14);
        $breadCrumbs[] = [
            'name' => $page->name,
            'url' => '/catalog'
        ];
        
        // Используем существующий метод getUrl для получения пути
        $fullUrl = Categories::getUrl($categoryId);
        $urlParts = explode('/', trim($fullUrl, '/'));
        
        $currentPath = '';
        foreach ($urlParts as $part) {
            $currentPath .= '/' . $part;
            $category = Categories::getCategory($part);
            if ($category) {
                $breadCrumbs[] = [
                    'name' => $category->name,
                    'url' => '/catalog' . $currentPath
                ];
            }
        }
        
        return $breadCrumbs;
    }
    
    protected static function setSeo($view, $page)
    {
        $settings = $view->settings;
        $sitename = $settings->sitename ?? '';
        
        // Если есть текущая категория, используем её данные для SEO
        if (!empty($view->currentCategory)) {
            $category = $view->currentCategory;
            $title = !empty($category->title) ? $category->title : $category->name . ' - ' . $sitename;
            $description = !empty($category->description) ? $category->description : $category->name . ' в нашем каталоге';
            $keywords = !empty($category->keywords) ? $category->keywords : $category->name;
        } 
        // Если есть товар, используем его данные
        elseif (!empty($view->product)) {
            $product = $view->product;
            $title = !empty($product->title) ? $product->title : $product->name . ' купить - ' . $sitename;
            $description = !empty($product->description) ? $product->description : $product->name . ' в наличии';
            $keywords = !empty($product->keywords) ? $product->keywords : $product->name;
        }
        // Иначе используем данные страницы
        else {
            $title = $view->seo->title ?? $page->title ?? $sitename;
            $description = $view->seo->description ?? $page->description ?? $sitename;
            $keywords = $view->seo->keywords ?? $page->keywords ?? $sitename;
        }
        
        $view->title = Helpers::replace($title);
        $view->description = Helpers::replace($description);
        $view->keywords = Helpers::replace($keywords);
        
        if (!empty($page->image)) {
            $view->page_image = $page->image;
        }
    }
}