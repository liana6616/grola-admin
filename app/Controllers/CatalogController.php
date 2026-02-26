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
            
            $current_sort = $_GET['sort'] ?? 'rate';
            $current_order = $_GET['order'] ?? 'DESC';

            if ($current_sort == 'rate') {
                $next_order = ($current_order == 'DESC') ? 'ASC' : 'DESC';
            } else {
                $next_order = 'DESC';
            }

            $subcat_param = isset($_GET['subcat']) ? '&subcat='.$_GET['subcat'] : '';
            
            $view->current_sort = $current_sort;
            $view->current_order = $current_order;
            $view->next_order = $next_order;
            $view->subcat_param = $subcat_param;

            $view->messengers = Messengers::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $category = Categories::getCategory($url);

            if (empty($category) && !empty($url)) {
                return $view->show('errors/404.php');
            }

            if (!empty($category)) {
                $subcategories = Categories::getChilds($category->id);

                $category->products_count = Categories::getProductsCount($category->id);

                foreach ($subcategories as $subcat) {
                    $subcat->products_count = Categories::getProductsCount($subcat->id);
                }

                $view->childs = array_merge([$category], $subcategories);
                $view->selectedSubcat = $selectedSubcat;

                $ids = [(int)$category->id];
                foreach ($subcategories as $c) {
                    $ids[] = (int)$c->id;
                }
                $in = implode(',', array_unique($ids));

                if ($selectedSubcat > 0) {
                    $view->catalog = Catalog::where(
                        "WHERE `show`=1 AND `is_draft`=0 AND `category_id` = {$selectedSubcat}
                        ORDER BY {$current_sort} {$current_order}, id ASC"
                    ) ?: [];
                } else {
                    $view->catalog = Catalog::where(
                        "WHERE `show`=1 AND `is_draft`=0 AND `category_id` IN ({$in})
                        ORDER BY {$current_sort} {$current_order}, id ASC"
                    ) ?: [];
                }
            }
            else {
                $view->is_mainCategories = 1;
                $view->selectedSubcat = 0;
                $view->catalog = Catalog::where(
                    "WHERE `show`=1 and `is_draft`=0 
                    ORDER BY {$current_sort} {$current_order}, id ASC"
                ) ?: [];

                $childs = Categories::getChilds(0);

                foreach ($childs as $cat) {
                    $subcategories = Categories::getChilds($cat->id);

                    $allIds = [$cat->id];
                    foreach ($subcategories as $sub) {
                        $allIds[] = $sub->id;
                    }
                    $idsStr = implode(',', $allIds);

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
                throw new \Exception('�������� catalog �� �������');
            }

            $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
            $view->breadCrumbs = Pages::breadCrumbs($page->id);

            self::setSeo($view, $page);

            return $view->show('pages/catalog.php');

        } catch (\Exception $e) {
            error_log('������ � CatalogController->handle: ' . $e->getMessage());
            return $this->showErrorPage(500, '��������� ������ ��� �������� ��������');
        }
    }
    
    protected static function setSeo($view, $page)
    {
        $settings = $view->settings;
        $sitename = $settings->sitename ?? '';
        
        if (!empty($view->currentCategory)) {
            $category = $view->currentCategory;
            $title = !empty($category->title) ? $category->title : $category->name . ' - ' . $sitename;
            $description = !empty($category->description) ? $category->description : $category->name . ' в нашем каталоге';
            $keywords = !empty($category->keywords) ? $category->keywords : $category->name;
        } 
        elseif (!empty($view->product)) {
            $product = $view->product;
            $title = !empty($product->title) ? $product->title : $product->name . ' купить - ' . $sitename;
            $description = !empty($product->description) ? $product->description : $product->name . ' в наличии';
            $keywords = !empty($product->keywords) ? $product->keywords : $product->name;
        }
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