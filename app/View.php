<?php

namespace app;

use app\Models\Settings;
use app\Models\Pages;
use app\Models\Seo;
use app\Models\Admins;
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

    public function __construct()
    {
        if (!AJAX) {
            $settings = $this->getCachedSettings();
            $this->settings = $settings;
            
            // Очистка телефонов
            $this->phones = Helpers::clearPhone($settings->phone);
            $this->phones2 = Helpers::clearPhone($settings->phone2);
            
            // SEO данные из таблицы seo по текущему URI
            $seo = Seo::findByUrl(URI);
            $this->edit_seo = Seo::edit($seo);
            $this->seo = $seo;
            
            $this->canonical = Helpers::canonical();
            
            // Меню - используем кэширование
            $this->menus = $this->getCachedMenus();
            
            // Каталог
            $this->catalog = Pages::findById(2);
            
            // Статические страницы
            $this->initStaticPages();
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
     * Получить меню с кэшированием
     */
    protected function getCachedMenus()
    {
        if (self::$cachedMenus === null) {
            self::$cachedMenus = Pages::where('WHERE `show`=1 AND `menu`=1 ORDER BY rate DESC, id ASC');
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
     * Инициализация статических страниц с кэшированием
     */
    protected function initStaticPages()
    {
        $staticPages = [
            'oferta' => 10,
            'politika' => 11,
            'pers' => 12,
            'cookie' => 13
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
            case 'pages':
                self::$cachedPagesArray = null;
                break;
            case 'static':
                self::$cachedStaticPages = [];
                break;
            case 'all':
            default:
                self::$cachedSettings = null;
                self::$cachedMenus = null;
                self::$cachedPagesArray = null;
                self::$cachedStaticPages = [];
                break;
        }
    }
    
    /**
     * Получить кэшированные данные (для отладки)
     */
    public static function getCacheInfo()
    {
        return [
            'settings' => self::$cachedSettings !== null ? 'cached' : 'not cached',
            'menus' => self::$cachedMenus !== null ? 'cached' : 'not cached',
            'pages_array' => self::$cachedPagesArray !== null ? 'cached' : 'not cached',
            'static_pages_count' => count(self::$cachedStaticPages),
        ];
    }
}