<?php

namespace app\Controllers;

use app\Controller;
use app\Models\Admins;

class AdminController extends Controller
{
    protected function handle(...$parameters)
    {
        $admin = Admins::checkLogged(true);
        Admins::auth($admin);
        $this->view->admin = $admin;

        // Вход в админку только с определенных IP
        if (!Admins::isPermittedIP()) {
            return $this->showErrorPage(403, 'Доступ запрещен: IP-адрес не разрешен');
        }

        $url = array_shift($parameters);
        $page = array_shift($parameters);
        $p = array_shift($parameters);

        if (!empty($url)) {
            // Проверяем доступ к модулю перед загрузкой
            if (!Admins::canAccessModule($url)) {
                return $this->showErrorPage(403, 'Доступ к модулю запрещен');
            }
            
            $module = ROOT.'/private/modules/'.$url.'.php';

            try {
                if (is_file($module)) { 
                    ob_start(); 
                    include $module; 
                    $module = ob_get_contents(); 
                    ob_clean(); 
                } else {
                    // Если файл модуля не найден, показываем 404
                    return $this->showErrorPage(404, 'Модуль не найден');
                }
            } catch (\Exception $e) {
                // Обработка ошибок при загрузке модуля
                error_log('Ошибка загрузки модуля: ' . $e->getMessage());
                return $this->showErrorPage(500, 'Ошибка загрузки модуля', $e->getMessage());
            }
        }

        $this->view->module = $module;
        $this->view->display(ROOT . '/private/views/content.php');
    }
    
    protected function access(): bool
    {
        // Базовый доступ - проверка авторизации
        if (Admins::isGuest()) {
            $this->showErrorPage(401, 'Требуется авторизация');
            return false;
        }
        
        return true;
    }
}