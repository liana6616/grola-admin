<?php
namespace app\Controllers;

use app\Controller;
use app\Helpers;
use app\Models\Pages;
use app\Models\Admins;
use app\Models\News;
use app\Models\Faq;
use app\Models\Categories;
use app\Models\Catalog;
use app\Models\CatalogParams;
use app\Models\Advantages;
use app\Models\SchemeWork;
use app\Models\WhyChooseUs;
use app\Models\FaqSections;
use app\Models\GalleryWorks;
use app\Models\Settings;
use app\Models\Messengers;
use app\Models\Partners;
use app\Models\KeyIndicators;
use app\Models\Pagination;

class PageController extends Controller {
    protected function handle(...$params) {
        try {
            $view = $this->view;
            $view->params = $params;

            $url = !empty($params[0]) ? $params[0] : '';

            // $cos = Messengers::findById(2);
            // var_dump($cos); // ПОСМОТРИМ, ЧТО ТАМ ВООБЩЕ ЕСТЬ
            // exit;

            $view->messengers = Messengers::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];


            // Главная
            if (empty($url)) return self::main($view);

            // Страницы
            $page = Pages::findByUrl($url);

            if (!empty($page)) {
                $view->page = $page;
                $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
                
                // Устанавливаем SEO данные для страницы
                self::setSeo($view, $page);
                
                switch ($page->id) {
                    case 10: return self::about($view); break; // about
                    case 12: return self::contacts($view); break; 
                    case 14: return self::catalog($view); break;
                    case 16: return self::policy($view); break; 
                    case 18: return self::catalogParams($view); break; 

                    // case 9: return self::faq($view); break; // FAQ
                    default: return $view->show('page.php'); break; // Обычные страницы
                }
            }

            // Страница не найдена - ВЫЗЫВАЕМ МЕТОД РОДИТЕЛЯ
            return parent::showErrorPage(404, 'Страница не найдена');
            
        } catch (\Exception $e) {
            error_log('Ошибка в PageController->handle: ' . $e->getMessage());
            // ВЫЗЫВАЕМ МЕТОД РОДИТЕЛЯ
            return parent::showErrorPage(500, 'Произошла ошибка при загрузке страницы');
        }
    }
    
    /**
     * Устанавливает SEO данные для страницы
     */
    protected static function setSeo($view, $page)
    {
        $settings = $view->settings;
        $sitename = $settings->sitename ?? '';

        // Приоритет 1: SEO из таблицы seo (уже загружено в View)
        $title = $view->seo->title ?? null;
        $description = $view->seo->description ?? null;
        $keywords = $view->seo->keywords ?? null;
        
        // Приоритет 2: SEO-поля из текущей страницы
        if (!$title && !empty($page->title)) {
            $title = $page->title;
        }
        
        if (!$description && !empty($page->description)) {
            $description = $page->description;
        }
        
        if (!$keywords && !empty($page->keywords)) {
            $keywords = $page->keywords;
        }
        
        // Приоритет 3: Название страницы
        if (!$title) {
            $title = !empty($page->name) ? $sitename . ' | ' .$page->name  : $sitename;
        }
        
        if (!$description) {
            $description = !empty($page->name) ? $page->name . ' - ' . $sitename : $sitename;
        }
        
        if (!$keywords) {
            $keywords = $sitename;
        }
        
        // Применяем замены через Helpers::replace
        $view->title = Helpers::replace($title);
        $view->description = Helpers::replace($description);
        $view->keywords = Helpers::replace($keywords);
        
        // Если есть SEO изображение в странице
        if (!empty($page->image)) {
            $view->page_image = $page->image;
        }
    }

    protected static function main($view){
        try {
            $page = Pages::findById(1);
            if (!$page) {
                throw new \Exception('Главная страница не найдена в базе данных');
            }
            
            $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
            $view->page = $page;
            $view->advantages = Advantages::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $view->scheme_work = SchemeWork::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $view->why_choose_us = WhyChooseUs::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $view->partners = Partners::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $view->gallery_works = GalleryWorks::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $view->categories = Categories::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];

            // Устанавливаем SEO для главной страницы
            self::setSeo($view, $page);
            
            return $view->show('main.php');
            
        } catch (\Exception $e) {
            error_log('Ошибка в PageController::main: ' . $e->getMessage());
            
            // Если главная страница недоступна, показываем минимальную версию
            $view->error_message = $e->getMessage();
            return $view->show('errors/main_unavailable.php');
        }
    }
    
    protected static function about($view){
        try {
            $page = Pages::findById(10);
            if (!$page) {
                throw new \Exception('Страница About не найдена');
            }

            $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
            $view->breadCrumbs = Pages::breadCrumbs($page->id);

            $view->scheme_work = SchemeWork::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $view->why_choose_us = WhyChooseUs::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $view->key_indicators = KeyIndicators::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $view->partners = Partners::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $view->gallery_works = GalleryWorks::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];

            // Устанавливаем SEO для страницы FAQ
            self::setSeo($view, $page);

            return $view->show('pages/about.php');

        } catch (\Exception $e) {
            error_log('Ошибка в PageController::about: ' . $e->getMessage());

            // Показываем страницу FAQ с сообщением об ошибке
            $view->error_message = 'Временно недоступно. Приносим извинения за неудобства.';
            return $view->show('pages/about.php');
        }
    }

    protected static function contacts($view){
        try {
            $page = Pages::findById(12);
            if (!$page) {
                throw new \Exception('Страница Contacts не найдена');
            }

            $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
            $view->breadCrumbs = Pages::breadCrumbs($page->id);


            // Устанавливаем SEO для страницы Contacts
            self::setSeo($view, $page);

            return $view->show('pages/contacts.php');

        } catch (\Exception $e) {
            error_log('Ошибка в PageController::contacts: ' . $e->getMessage());

            // Показываем страницу FAQ с сообщением об ошибке
            $view->error_message = 'Временно недоступно. Приносим извинения за неудобства.';
            return $view->show('pages/contacts.php');
        }
    }

    protected static function catalog($view){
        try {
            $page = Pages::findById(14);
            if (!$page) {
                throw new \Exception('Страница catalog не найдена');
            }

            $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
            $view->breadCrumbs = Pages::breadCrumbs($page->id);

            $view->categories = Categories::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
            $view->catalog = Catalog::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];


            // Устанавливаем SEO для страницы catalog
            self::setSeo($view, $page);

            return $view->show('pages/catalog.php');

        } catch (\Exception $e) {
            error_log('Ошибка в PageController::catalog: ' . $e->getMessage());

            // Показываем страницу FAQ с сообщением об ошибке
            $view->error_message = 'Временно недоступно. Приносим извинения за неудобства.';
            return $view->show('pages/catalog.php');
        }
    }

    protected static function catalogParams($view){
        try {
            $page = Pages::findById(18);
            if (!$page) {
                throw new \Exception('Страница catalogParams не найдена');
            }

            $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
            $view->breadCrumbs = Pages::breadCrumbs($page->id);


            // Устанавливаем SEO для страницы catalogCategory
            self::setSeo($view, $page);

            return $view->show('pages/catalogCategory.php');

        } catch (\Exception $e) {
            error_log('Ошибка в PageController::catalogParams: ' . $e->getMessage());

            // Показываем страницу FAQ с сообщением об ошибке
            $view->error_message = 'Временно недоступно. Приносим извинения за неудобства.';
            return $view->show('pages/catalogCategory.php');
        }
    }

    protected static function catalogCard($view){
        try {
            $page = Pages::findById(14);
            if (!$page) {
                throw new \Exception('Страница catalogCard не найдена');
            }

            $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
            $view->breadCrumbs = Pages::breadCrumbs($page->id);

            // Устанавливаем SEO для страницы catalogCard
            self::setSeo($view, $page);

            return $view->show('pages/catalogCard.php');

        } catch (\Exception $e) {
            error_log('Ошибка в PageController::catalogCard: ' . $e->getMessage());

            // Показываем страницу FAQ с сообщением об ошибке
            $view->error_message = 'Временно недоступно. Приносим извинения за неудобства.';
            return $view->show('pages/catalogCard.php');
        }
    }

    // protected static function catalogCategory($view){
    //     try {
    //         $page = Pages::findById(18);
    //         if (!$page) {
    //             throw new \Exception('Страница catalogCategory не найдена');
    //         }

    //         $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
    //         $view->breadCrumbs = Pages::breadCrumbs($page->id);

    //         // Устанавливаем SEO для страницы catalogCategory
    //         self::setSeo($view, $page);

    //         return $view->show('pages/catalogCategory.php');

    //     } catch (\Exception $e) {
    //         error_log('Ошибка в PageController::catalogCategory: ' . $e->getMessage());

    //         // Показываем страницу FAQ с сообщением об ошибке
    //         $view->error_message = 'Временно недоступно. Приносим извинения за неудобства.';
    //         return $view->show('pages/catalogCategory.php');
    //     }
    // }

    protected static function policy($view){
        try {
            $page = Pages::findById(14);
            if (!$page) {
                throw new \Exception('Страница policy не найдена');
            }

            $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
            $view->breadCrumbs = Pages::breadCrumbs($page->id);



            // Устанавливаем SEO для страницы policy
            self::setSeo($view, $page);

            return $view->show('pages/policy.php');

        } catch (\Exception $e) {
            error_log('Ошибка в PageController::policy: ' . $e->getMessage());

            // Показываем страницу FAQ с сообщением об ошибке
            $view->error_message = 'Временно недоступно. Приносим извинения за неудобства.';
            return $view->show('pages/policy.php');
        }
    }

    // protected static function faq($view){
    //     try {
    //         $page = Pages::findById(9);
    //         if (!$page) {
    //             throw new \Exception('Страница FAQ не найдена');
    //         }
            
    //         $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
    //         $view->breadCrumbs = Pages::breadCrumbs($page->id);
            
    //         // Устанавливаем SEO для страницы FAQ
    //         self::setSeo($view, $page);

    //         $view->sections = FaqSections::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];
    //         $view->faq = Faq::where('WHERE `show`=1 ORDER BY rate DESC, id ASC') ?: [];

    //         return $view->show('pages/faq.php');
            
    //     } catch (\Exception $e) {
    //         error_log('Ошибка в PageController::faq: ' . $e->getMessage());
            
    //         // Показываем страницу FAQ с сообщением об ошибке
    //         $view->error_message = 'Временно недоступно. Приносим извинения за неудобства.';
    //         $view->sections = [];
    //         $view->faq = [];
    //         return $view->show('pages/faq.php');
    //     }
    // }

    // protected static function news($view){
    //     try {
    //         // Общая страница новостей
    //         if(empty($view->params[1]) || ($view->params[1] == 'p' && !empty($view->params[2]))) {
    //             $page = Pages::findById(8);
    //             if (!$page) {
    //                 throw new \Exception('Страница новостей не найдена');
    //             }
                
    //             $view->edit = Admins::edit("pages?edit={$page->id}", $view->edit_seo);
    //             $view->breadCrumbs = Pages::breadCrumbs($page->id);
                
    //             // Устанавливаем SEO для страницы новостей
    //             self::setSeo($view, $page);

    //             $itemsPerPage = 10;
    //             $currentPage = 1;

    //             if(!empty($view->params[2])) {
    //                 $currentPage = (int)$view->params[2];
    //                 if ($currentPage < 1) $currentPage = 1;
    //             }

    //             $newsCount = count(News::findWhere('WHERE `show`=1'));
    //             $totalPages = ceil($newsCount / $itemsPerPage);

    //             if ($currentPage > $totalPages && $totalPages > 0) {
    //                 $this->redirect('/' . $page->url);
    //                 return;
    //             }

    //             $offset = ($currentPage - 1) * $itemsPerPage;
    //             $limit = " LIMIT {$offset}, {$itemsPerPage}";
    //             $view->news = News::where('WHERE `show`=1 ORDER BY date DESC, id DESC ' . $limit) ?: [];

    //             if ($newsCount > $itemsPerPage) {
    //                 $view->pagination = [
    //                     'current_page' => $currentPage,
    //                     'total_pages' => $totalPages,
    //                     'total_items' => $newsCount,
    //                     'items_per_page' => $itemsPerPage,
    //                     'has_prev' => $currentPage > 1,
    //                     'has_next' => $currentPage < $totalPages,
    //                     'prev_page' => $currentPage > 1 ? $currentPage - 1 : null,
    //                     'next_page' => $currentPage < $totalPages ? $currentPage + 1 : null
    //                 ];
    //             } 

    //             return $view->show('pages/news.php');
    //         }
            
    //         // Детальная страница новости
    //         if (!empty($view->params[1]) && is_numeric($view->params[1])) {
    //             return self::newsDetail($view);
    //         }
            
    //         // Если URL не распознан - ВЫЗЫВАЕМ МЕТОД РОДИТЕЛЯ
    //         return parent::showErrorPage(404, 'Страница новостей не найдена');
            
    //     } catch (\Exception $e) {
    //         error_log('Ошибка в PageController::news: ' . $e->getMessage());
            
    //         // Показываем страницу новостей с сообщением об ошибке
    //         $view->error_message = 'Новости временно недоступны. Приносим извинения за неудобства.';
    //         $view->news = [];
    //         return $view->show('pages/news.php');
    //     }
    // }
}