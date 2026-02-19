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

            $url  = $params[0] ?? null;
            $url1 = $params[1] ?? null;
            $url2 = $params[2] ?? null;

            if (!empty($params[3])) return $view->show('errors/404.php');
            if (!empty($url1)) $url = $url1;
            if (!empty($url2)) $url = $url2;

            $selectedSubcat = isset($_GET['subcat']) ? (int)$_GET['subcat'] : 0;

            $view->messengers = Messengers::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $category = Categories::getCategory($url);
            
            if (empty($category) && !empty($url)) {
                return $view->show('errors/404.php');
            }

            if (!empty($category)) {
                // Получаем подкатегории
                $subcategories = Categories::getChilds($category->id);
                
                // Добавляем количество для родительской категории
                $category->products_count = Categories::getProductsCount($category->id);
                
                // Добавляем количество для каждой подкатегории
                foreach ($subcategories as $subcat) {
                    $subcat->products_count = Categories::getProductsCount($subcat->id);
                }
                
                // Формируем массив для отображения
                $view->childs = $subcategories;
                $view->selectedSubcat = $selectedSubcat;
                
                // Собираем все ID категорий для фильтрации товаров
                $ids = [(int)$category->id];
                foreach ($subcategories as $c) {
                    $ids[] = (int)$c->id;
                }
                $in = implode(',', array_unique($ids));

                // Получаем товары в зависимости от выбранной подкатегории
                if ($selectedSubcat > 0) {
                    $view->catalog = Catalog::where(
                        "WHERE `show`=1 AND `is_draft`=0 AND `category_id` = {$selectedSubcat}
                        ORDER BY rate DESC, id ASC"
                    ) ?: [];
                } else {
                    $view->catalog = Catalog::where(
                        "WHERE `show`=1 AND `is_draft`=0 AND `category_id` IN ({$in})
                        ORDER BY rate DESC, id ASC"
                    ) ?: [];
                }
            }
            else {
                $view->is_mainCategories = 1;
                $view->selectedSubcat = 0;
                $view->catalog = Catalog::where('WHERE `show`=1 and `is_draft`=0 ORDER BY rate DESC, id ASC') ?: [];
                
                $childs = Categories::getChilds(0);
                
                foreach ($childs as $cat) {
    // Получаем все подкатегории для этой категории
    $subcategories = Categories::getChilds($cat->id);
    
    // Собираем все ID (родитель + подкатегории)
    $allIds = [$cat->id];
    foreach ($subcategories as $sub) {
        $allIds[] = $sub->id;
    }
    $idsStr = implode(',', $allIds);
    
    // Считаем товары во всех этих категориях
    $result = Catalog::query(
        "SELECT COUNT(*) as count 
        FROM catalog 
        WHERE `show` = 1 AND `is_draft` = 0 AND category_id IN ({$idsStr})"
    );
    
    $cat->products_count = !empty($result) ? (int)$result[0]->count : 0;
}
                
                $view->childs = $childs;
            }
            
            $page = Pages::findById(14);
            $view->page = $page;
            
            if (!$page) {
                throw new \Exception('Страница catalog не найдена');
            }

            $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
            $view->breadCrumbs = Pages::breadCrumbs($page->id);

            // self::setSeo($view, $page);

            return $view->show('pages/catalog.php');

        } catch (\Exception $e) {
            error_log('Ошибка в CatalogController->handle: ' . $e->getMessage());
            return $this->showErrorPage(500, 'Произошла ошибка при загрузке страницы');
        }
    }

    protected static function setSeo($view, $page)
    {
        $settings = $view->settings;
        $sitename = $settings->sitename ?? '';

        $title = $view->seo->title ?? null;
        $description = $view->seo->description ?? null;
        $keywords = $view->seo->keywords ?? null;

        if (!$title && !empty($page->title)) {
            $title = $page->title;
        }

        if (!$description && !empty($page->description)) {
            $description = $page->description;
        }

        if (!$keywords && !empty($page->keywords)) {
            $keywords = $page->keywords;
        }

        if (!$title) {
            $title = !empty($page->name) ? $sitename . ' | ' .$page->name  : $sitename;
        }

        if (!$description) {
            $description = !empty($page->name) ? $page->name . ' - ' . $sitename : $sitename;
        }

        if (!$keywords) {
            $keywords = $sitename;
        }

        $view->title = Helpers::replace($title);
        $view->description = Helpers::replace($description);
        $view->keywords = Helpers::replace($keywords);

        if (!empty($page->image)) {
            $view->page_image = $page->image;
        }
    }
}