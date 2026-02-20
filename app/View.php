<?php

namespace app;

use app\Models\Settings;
use app\Models\Pages;
use app\Models\Seo;
use app\Models\Admins;
use app\Models\Messengers;
use app\Models\Categories;
use app\Models\FinishedProducts;
use app\Helpers;

class View
{
    protected $data = [];
    protected $params = [];
    
    // Кэшированные данные для всех экземпляров
    protected static $cachedSettings = null;
    protected static $cachedMenus = null;
    protected static $cachedStaticPages = [];
    protected static $cachedPagesArray = null;
    protected static $cachedMessengers = null;
    protected static $cachedMessengers2 = null;
    protected static $cachedCategories = null; // Добавлено для категорий
    protected static $cachedFinishedProducts = null; // Добавлено для готовой продукции
    protected static $cachedCategoriesTree = null; // Добавлено для дерева категорий
    protected static $cachedFinishedProductsTree = null;

    public function __construct()
    {
        if (!AJAX) {
            $settings = $this->getCachedSettings();
            $this->settings = $settings;

            $address = array_filter([
                $settings->postcode ?? null,
                $settings->city ?? null,
                $settings->address ?? null
            ]);

            $this->address = !empty($address) ? join(', <br>', $address) : 'Адрес не указан';
            
            // Очистка телефонов
            $this->phones = Helpers::clearPhone($settings->phone);
            $this->phones2 = Helpers::clearPhone($settings->phone2);
            $this->phones3 = Helpers::clearPhone($settings->phone3);
            
            // SEO данные из таблицы seo по текущему URI
            $seo = Seo::findByUrl(URI);
            $this->edit_seo = Seo::edit($seo);
            $this->seo = $seo;
            
            $this->canonical = Helpers::canonical();
            
            // Меню - используем кэширование
            $this->menus = $this->getCachedMenus();

            $this->messengers = $this->getCachedMessengers();
            $this->messengers2 = $this->getCachedMessengers2();
                        
            // Статические страницы
            $this->initStaticPages();
            
            // Категории товаров - добавляем кэшированные данные
            $this->categories = $this->getCachedCategories();
            $this->categoriesTree = $this->getCachedCategoriesTree();
            
            // Готовая продукция - добавляем кэшированные данные
            $this->finishedProducts = $this->getCachedFinishedProducts();
            $this->finishedProductsTree = $this->getCachedFinishedProductsTree();
        }
    }

    /**
     * Получить настройки с кэшированием
     */
    protected function getCachedSettings()
    {
        if (self::$cachedSettings === null) {
            self::$cachedSettings = Settings::findById(1);
        }
        return self::$cachedSettings;
    }

    /**
     * Получить мессенджеры с кэшированием
     */
    protected function getCachedMessengers()
    {
        if (self::$cachedMessengers === null) {
            self::$cachedMessengers = Messengers::where('WHERE `show`=1 AND `header`=1 AND image<>"" ORDER BY rate DESC, id ASC');
        }
        return self::$cachedMessengers;
    }
    
    protected function getCachedMessengers2()
    {
        if (self::$cachedMessengers2 === null) {
            self::$cachedMessengers2 = Messengers::where('WHERE `show`=1 AND `footer`=1 AND image<>"" ORDER BY rate DESC, id ASC');
        }
        return self::$cachedMessengers2;
    }

    /**
     * Получить меню с кэшированием
     */
    protected function getCachedMenus()
    {
        if (self::$cachedMenus === null) {
            self::$cachedMenus = Pages::where('WHERE `show`=1 AND `is_draft`=0 AND `menu`=1 ORDER BY rate DESC, id ASC');
        }
        return self::$cachedMenus;
    }

    /**
     * Получить массив страниц с кэшированием
     */
    protected function getCachedPageArray()
    {
        if (self::$cachedPagesArray === null) {
            self::$cachedPagesArray = Pages::getArray();
        }
        return self::$cachedPagesArray;
    }

    /**
     * Получить категории с кэшированием
     */
    protected function getCachedCategories()
    {
        if (self::$cachedCategories === null) {
            self::$cachedCategories = Categories::where('WHERE `show`=1 ORDER BY rate DESC, id ASC');
        }
        return self::$cachedCategories;
    }

    /**
     * Получить дерево категорий с кэшированием
     */
    protected function getCachedCategoriesTree()
    {
        if (self::$cachedCategoriesTree === null) {
            self::$cachedCategoriesTree = $this->buildCategoriesTree();
        }
        return self::$cachedCategoriesTree;
    }

    /**
     * Получить готовую продукцию с кэшированием
     */
    protected function getCachedFinishedProducts()
    {
        if (self::$cachedFinishedProducts === null) {
            self::$cachedFinishedProducts = FinishedProducts::where('WHERE `show`=1 ORDER BY rate DESC, id ASC');
        }
        return self::$cachedFinishedProducts;
    }

    /**
     * Построить дерево категорий
     */
    protected function buildCategoriesTree($parentId = null)
    {
        $categories = $this->getCachedCategories();
        $tree = [];
        
        // Группируем категории по parent
        $grouped = [];
        foreach ($categories as $category) {
            $parent = $category->parent ?? 0;
            if (!isset($grouped[$parent])) {
                $grouped[$parent] = [];
            }
            $grouped[$parent][] = $category;
        }
        
        // Рекурсивно строим дерево
        $this->buildTreeRecursive($grouped, $parentId, $tree);
        
        return $tree;
    }

    /**
     * Рекурсивное построение дерева категорий
     */
    protected function buildTreeRecursive(&$grouped, $parentId, &$tree)
    {
        $parentKey = $parentId ?? 0;
        
        if (isset($grouped[$parentKey])) {
            foreach ($grouped[$parentKey] as $category) {
                $node = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'name_menu' => $category->name_menu ?: $category->name,
                    'url' => Categories::getUrl($category->id),
                    'children' => []
                ];
                
                // Рекурсивно добавляем дочерние категории
                $this->buildTreeRecursive($grouped, $category->id, $node['children']);
                
                $tree[] = $node;
            }
        }
    }

    /**
     * Получить плоский список категорий с отступами для select
     */
    public function getCategoriesFlat($parentId = null, $level = 0)
    {
        $categories = $this->getCachedCategories();
        $result = [];
        $prefix = str_repeat('— ', $level);
        
        foreach ($categories as $category) {
            if (($parentId === null && $category->parent === null) || $category->parent == $parentId) {
                $category->display_name = $prefix . $category->name;
                $result[] = $category;
                
                // Рекурсивно добавляем дочерние
                $children = $this->getCategoriesFlat($category->id, $level + 1);
                $result = array_merge($result, $children);
            }
        }
        
        return $result;
    }

    protected function getCachedFinishedProductsTree()
    {
        if (self::$cachedFinishedProductsTree === null) {
            self::$cachedFinishedProductsTree = $this->buildFinishedProductsTree();
        }
        return self::$cachedFinishedProductsTree;
    }

    protected function buildFinishedProductsTree($parentId = null)
    {
        $products = $this->getCachedFinishedProducts();
        $tree = [];
        
        // Группируем по parent
        $grouped = [];
        foreach ($products as $product) {
            $parent = $product->parent ?? 0;
            if (!isset($grouped[$parent])) {
                $grouped[$parent] = [];
            }
            $grouped[$parent][] = $product;
        }
        
        // Рекурсивно строим дерево
        $this->buildFinishedTreeRecursive($grouped, $parentId, $tree);
        
        return $tree;
    }

    protected function buildFinishedTreeRecursive(&$grouped, $parentId, &$tree)
    {
        $parentKey = $parentId ?? 0;
        
        if (isset($grouped[$parentKey])) {
            foreach ($grouped[$parentKey] as $product) {
                $node = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'children' => []
                ];
                
                $this->buildFinishedTreeRecursive($grouped, $product->id, $node['children']);
                
                $tree[] = $node;
            }
        }
    }

    /**
     * Инициализация статических страниц с кэшированием
     */
    protected function initStaticPages()
    {
        $staticPages = [
            'oferta' => 24,
            'politika' => 26,
            'pers' => 30,
            'cookie' => 28
        ];
        
        foreach ($staticPages as $property => $id) {
            if (!isset(self::$cachedStaticPages[$id])) {
                self::$cachedStaticPages[$id] = Pages::findById($id);
            }
            $this->$property = self::$cachedStaticPages[$id];
        }
    }

    // Магические методы для доступа к данным
    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Отображение шаблона
     */
    public function display($template)
    {
        echo $this->render($template);
    }

    /**
     * Отображение шаблона с полным путем
     */
    public function show($template)
    {
        $template = ROOT . '/public/views/' . $template;
        echo $this->render($template);
    }

    /**
     * Рендер шаблона с проверкой существования
     */
    public function render($template)
    {
        if (!file_exists($template)) {
            throw new \Exception("Template {$template} not found");
        }
        
        ob_start();
        include $template;
        return ob_get_clean();
    }

    /**
     * Включение файла с поддержкой разных расширений для Public
     */
    public function include($path, $item = null, $array = [], $extension = 'php')
    {
        $view = $item;
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . "/public/views/{$path}.{$extension}";
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Included file {$fullPath} not found");
        }
        
        include $fullPath;
    }

    public function includePrivate($path, $item = null, $array = [], $extension = 'php')
    {
        $view = $item;
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . "/private/views/{$path}.{$extension}";
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Included file {$fullPath} not found");
        }
        
        include $fullPath;
    }

    /**
     * Включение HTML файла (для обратной совместимости)
     */
    public function includeHtml($path)
    {
        $this->include($path, null, [], 'html');
    }

    public function includePrivateHtml($path)
    {
        $this->includePrivate($path, null, [], 'html');
    }

    /**
     * Получить параметр по имени
     */
    public function getParam($param)
    {
        return $this->params[$param] ?? null;
    }

    /**
     * Получить все параметры
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Статический метод для очистки кэша
     * Полезно при обновлении данных
     */
    public static function clearCache($type = 'all')
    {
        switch ($type) {
            case 'settings':
                self::$cachedSettings = null;
                break;
            case 'menus':
                self::$cachedMenus = null;
                break;
            case 'messengers':
                self::$cachedMessengers = null;
                self::$cachedMessengers2 = null;
                break;
            case 'pages':
                self::$cachedPagesArray = null;
                break;
            case 'static':
                self::$cachedStaticPages = [];
                break;
            case 'categories':
                self::$cachedCategories = null;
                self::$cachedCategoriesTree = null;
                break;
            case 'finished':
                self::$cachedFinishedProducts = null;
                break;
            case 'all':
            default:
                self::$cachedSettings = null;
                self::$cachedMenus = null;
                self::$cachedPagesArray = null;
                self::$cachedStaticPages = [];
                self::$cachedMessengers = null;
                self::$cachedMessengers2 = null;
                self::$cachedCategories = null;
                self::$cachedCategoriesTree = null;
                self::$cachedFinishedProducts = null;
                break;
        }
    }
}