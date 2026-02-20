<?php

namespace app\Controllers;

use app\Controller;
use app\Models\Admins;

class AdminController extends Controller
{
    protected function handle(...$parameters)
    {
        // Включаем буферизацию вывода с самого начала
        ob_start();
        
        // checkLogged уже обновляет date_visit
        $admin = Admins::checkLogged(true);
        Admins::auth($admin);
        $this->view->admin = $admin;

        // Вход в админку только с определенных IP
        if (!Admins::isPermittedIP()) {
            ob_end_clean();
            return $this->showErrorPage(403, 'Доступ запрещен: IP-адрес не разрешен');
        }

        $url = array_shift($parameters);
        $page = array_shift($parameters);
        $p = array_shift($parameters);

        if (!empty($url)) {
            // Проверяем доступ к модулю перед загрузкой
            if (!Admins::canAccessModule($url)) {
                ob_end_clean();
                return $this->showErrorPage(403, 'Доступ к модулю запрещен');
            }
            
            $module = ROOT.'/private/modules/'.$url.'.php';

            try {
                if (is_file($module)) { 
                    include $module; 
                } else {
                    ob_end_clean();
                    return $this->showErrorPage(404, 'Модуль не найден');
                }
            } catch (\Exception $e) {
                ob_end_clean();
                error_log('Ошибка загрузки модуля: ' . $e->getMessage());
                return $this->showErrorPage(500, 'Ошибка загрузки модуля', $e->getMessage());
            }
        }

        $this->view->module = ob_get_clean(); // Получаем и очищаем буфер
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