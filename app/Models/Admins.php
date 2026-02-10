<?php

namespace app\Models;

use app\Db;
use app\Model;
use app\Helpers;
use app\Models\Settings;
use app\Models\Admins_ip;

class Admins extends Model
{
    public const TABLE = 'admins';
    
    // Константы классов администраторов
    public const CLASS_ADMIN = 1;      // Администратор (полные права)
    public const CLASS_MODERATOR = 2;  // Модератор (ограниченные права)

    public static function checkAdminData($login, $password)
    {
        $db = new Db();
        $sql = 'SELECT * FROM ' . self::TABLE . ' WHERE login = :login';

        $admin = $db->query(
            $sql,
            [':login' => $login],
            self::class
        );

        if ($admin) {
            if (password_verify($password, $admin[0]->password)) {
                return $admin[0];
            }
            else {
                //$_SESSION['notice'] = 'Неверный пароль';
            }
        }
        else {
            //$_SESSION['notice'] = 'Логин не зарегистрирован';
        }

        return false;
    }

    public static function auth($admin)
    {
        setcookie("admin", $admin->hash, time() + 7776000, "/", $_SERVER['SERVER_NAME'], "0");  //на 90 дней

        $class = Admins_class::findById($admin->class_id);

        $_SESSION['admin']['id'] = $admin->id;
        $_SESSION['admin']['hash'] = $admin->hash;
        $_SESSION['admin']['login'] = $admin->login;
        $_SESSION['admin']['name'] = $admin->name;
        $_SESSION['admin']['image'] = $admin->image;
        $_SESSION['admin']['class'] = $class->id;
        $_SESSION['admin']['className'] = $class->name;
        $_SESSION['admin']['class_name'] = $class->name;
        $_SESSION['admin']['permissions'] = self::getPermissions($admin->class_id);
    }

    public static function logout()
    {
        unset($_SESSION["admin"]);
        setcookie("admin", "", time() - 7776000, "/", $_SERVER['SERVER_NAME'], "0");
    }

    public static function getLogin()
    {
        return $_SESSION['admin']['login'];
    }

    public static function isGuest()
    {
        if (isset($_SESSION['admin'])) {
            return false;
        }
        return true;
    }

    //Разрешенные IP адреса
    public static function isPermittedIP()
    {
        $ip = Helpers::get_ip();
        if (empty($ip)) return false;

        $items = Admins_ip::findAll();
        foreach ($items as $item) {
            if ($item->name == '*') return true;

            if ($ip == $item->name) return true;
        }

        return false;
    }

    public static function isAdmin()
    {
        if(!isset($_SESSION['admin']))
        {
            if(isset($_COOKIE['admin']))
            {
                $admin = Admins::findByHash($_COOKIE['admin']);
                if(!empty($admin)) {
                    $class = Admins_class::findById($admin->class_id);

                    $_SESSION['admin']['id'] = $admin->id;
                    $_SESSION['admin']['hash'] = $admin->hash;
                    $_SESSION['admin']['login'] = $admin->login;
                    $_SESSION['admin']['name'] = $admin->name;
                    $_SESSION['admin']['image'] = $admin->image;
                    $_SESSION['admin']['class'] = $class->id;
                    $_SESSION['admin']['className'] = $class->name;
                    $_SESSION['admin']['class_name'] = $class->name;
                    $_SESSION['admin']['permissions'] = self::getPermissions($admin->class_id);
                }
            }
        }

        return !self::isGuest();
    }

    public static function checkLogged($redirect = false)
    {
        $hash = $_COOKIE['admin'];
        if (empty($hash)) {
            header("Location: /".ADMIN_LINK);
            exit();
        }

        $admin = Admins::findByHash($hash);
        if(empty($admin)) {
            Admins::logout();
            return;
        }

        if (!self::isAdmin() && $redirect) {
            header("Location: /".ADMIN_LINK);
            exit();
        }

        $admin->date_visit = time();
        $admin->save();

        return $admin;
    }

    public static function edit($url, $seo = '')
    {
        if (self::isAdmin()) {
            return "<div class='adminBlock'>
                <div>
                    <a href='/".ADMIN_LINK."/{$url}' target='_blank'>Редактировать</a>
                    {$seo}
                </div>
            </div>";
        }

        return;
    }

    public static function selectClass()
    {
        $db = new Db();
        $sql = 'SELECT * FROM `'.self::TABLE.'_class` ORDER BY id ASC';

        return $db->query(
            $sql,
            [],
            self::class
        );
    }

    public static function getClass($id)
    {
        $db = new Db();
        $sql = 'SELECT * FROM `'.self::TABLE.'_class` WHERE id='.$id.' LIMIT 1';

        $data = $db->query(
            $sql,
            [],
            self::class
        );

        return $data[0];
    }

    public static function getAdminClass()
    {
        if(isset($_SESSION['admin'])) return $_SESSION['admin']['class'];
        else return '';
    }

    public static function getMenu()
    {
        $adminPanel = (include ROOT . '/config/admin.php');
        return $adminPanel;
    }
    
    /**
     * Получить разрешения для класса администратора
     */
    public static function getPermissions($classId)
    {
        $config = self::getMenu();
        $permissions = $config['permissions'] ?? [];
        
        // Проверяем доступ к каждому действию
        $result = [];
        foreach ($permissions as $action => $allowedClasses) {
            $result[$action] = in_array($classId, $allowedClasses);
        }
        
        return $result;
    }
    
    /**
     * Проверить разрешение на действие
     */
    public static function hasPermission($action)
    {
        if (!isset($_SESSION['admin']['permissions'])) {
            return false;
        }
        
        return $_SESSION['admin']['permissions'][$action] ?? false;
    }
    
    /**
     * Проверить доступ к модулю по классу администратора
     */
    public static function canAccessModule($module)
    {
        $adminClass = $_SESSION['admin']['class'] ?? 0;
        $config = self::getMenu();
        
        // Сначала ищем модуль в корне меню
        if (isset($config['menu'][$module])) {
            $moduleConfig = $config['menu'][$module];
            
            // Если у модуля нет ограничений по классу, разрешаем доступ
            if (!isset($moduleConfig['class'])) {
                return true;
            }
            
            return in_array($adminClass, $moduleConfig['class']);
        }
        
        // Если не нашли в корне, ищем во вложенных пунктах меню
        foreach ($config['menu'] as $parentModule => $parentConfig) {
            if (isset($parentConfig['children']) && isset($parentConfig['children'][$module])) {
                $childConfig = $parentConfig['children'][$module];
                
                // Проверяем доступ к дочернему пункту
                if (!isset($childConfig['class'])) {
                    return true;
                }
                
                return in_array($adminClass, $childConfig['class']);
            }
        }
        
        // Если модуль не найден в конфиге, разрешаем доступ (для обратной совместимости)
        return true;
    }

    /**
     * Получить конфигурацию модуля (с учетом вложенности)
     */
    public static function getModuleConfig($module)
    {
        $config = self::getMenu();
        
        // Ищем в корне
        if (isset($config['menu'][$module])) {
            return $config['menu'][$module];
        }
        
        // Ищем во вложенных
        foreach ($config['menu'] as $parentModule => $parentConfig) {
            if (isset($parentConfig['children']) && isset($parentConfig['children'][$module])) {
                return $parentConfig['children'][$module];
            }
        }
        
        return null;
    }

    /**
     * Получить родительский модуль для дочернего
     */
    public static function getParentModule($childModule)
    {
        $config = self::getMenu();
        
        foreach ($config['menu'] as $parentModule => $parentConfig) {
            if (isset($parentConfig['children']) && isset($parentConfig['children'][$childModule])) {
                return $parentModule;
            }
        }
        
        return null;
    }

    /**
     * Получить полный путь к модулю (родитель -> ребенок)
     */
    public static function getModulePath($module)
    {
        $parent = self::getParentModule($module);
        
        if ($parent) {
            return $parent . '/' . $module;
        }
        
        return $module;
    }

    /**
     * Фильтровать меню по классу администратора (обновленная версия)
     */
    public static function filterMenuByClass($menu, $adminClass)
    {
        $filteredMenu = [];
        
        foreach ($menu as $key => $item) {
            // Проверяем доступ к основному пункту меню
            $hasAccessToParent = !isset($item['class']) || in_array($adminClass, $item['class']);
            
            // Клонируем элемент для фильтрации
            $filteredItem = $item;
            
            // Фильтруем дочерние элементы
            if (isset($item['children'])) {
                $filteredChildren = [];
                
                foreach ($item['children'] as $childKey => $childItem) {
                    // Проверяем доступ к дочернему пункту
                    $hasAccessToChild = !isset($childItem['class']) || in_array($adminClass, $childItem['class']);
                    
                    if ($hasAccessToChild) {
                        $filteredChildren[$childKey] = $childItem;
                    }
                }
                
                // Если есть доступные дочерние элементы, добавляем родительский пункт
                if (!empty($filteredChildren)) {
                    $filteredItem['children'] = $filteredChildren;
                    $filteredMenu[$key] = $filteredItem;
                }
                // Если нет дочерних элементов, но есть доступ к родительскому пункту
                // (например, для модулей, которые не имеют дочерних или дочерние скрыты)
                else if ($hasAccessToParent) {
                    // Удаляем детей, если они были
                    unset($filteredItem['children']);
                    $filteredMenu[$key] = $filteredItem;
                }
            }
            // Если нет дочерних элементов, просто проверяем доступ к родительскому
            else if ($hasAccessToParent) {
                $filteredMenu[$key] = $filteredItem;
            }
        }
        
        return $filteredMenu;
    }
    
    /**
     * Получить все доступные классы администраторов
     */
    public static function getAllClasses()
    {
        $db = new Db();
        $sql = 'SELECT * FROM `'.self::TABLE.'_class` ORDER BY id ASC';
        
        $classes = $db->query($sql, [], self::class);
        
        $result = [];
        foreach ($classes as $class) {
            $result[$class->id] = $class->name;
        }
        
        return $result;
    }
    
    /**
     * Получить текущий класс администратора
     */
    public static function getCurrentClass()
    {
        return $_SESSION['admin']['class'] ?? 0;
    }
    
    /**
     * Получить название текущего класса администратора
     */
    public static function getCurrentClassName()
    {
        return $_SESSION['admin']['class_name'] ?? '';
    }
    
    /**
     * Является ли текущий пользователь администратором (класс 1)
     */
    public static function isSuperAdmin()
    {
        return ($_SESSION['admin']['class'] ?? 0) == self::CLASS_ADMIN;
    }
    
    /**
     * Является ли текущий пользователь модератором (класс 2)
     */
    public static function isModerator()
    {
        return ($_SESSION['admin']['class'] ?? 0) == self::CLASS_MODERATOR;
    }
    
    /**
     * Проверить разрешение на копирование
     */
    public static function canCopy()
    {
        return self::hasPermission('copy');
    }
    
    /**
     * Проверить разрешение на удаление
     */
    public static function canDelete()
    {
        return self::hasPermission('delete');
    }
    
    /**
     * Проверить разрешение на создание
     */
    public static function canCreate()
    {
        return self::hasPermission('create');
    }
    
    /**
     * Проверить разрешение на редактирование
     */
    public static function canEdit()
    {
        return self::hasPermission('edit');
    }

    /**
     * Проверить разрешение на публикацию
     */
    public static function canPublish()
    {
        return self::hasPermission('publish');
    }
}