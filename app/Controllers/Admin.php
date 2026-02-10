<?php

namespace app\Controllers;

use app\Controller;
use app\Models\Admins;

class Admin extends Controller
{
    protected function handle(...$parameters)
    {
        // Вход в админку только с определенных IP
        if (!Admins::isPermittedIP()) {
            return $this->showErrorPage(403, 'Доступ к админ-панели разрешен только с определенных IP-адресов');
        }

        // Если админ уже авторизован - редирект в админку
        if (Admins::isAdmin()) {
            $this->redirect('/'.ADMIN_LINK.'/settings');
        }

        $this->view->display(ROOT . '/private/views/login.php');
    }

    protected function access(): bool
    {
        if (isset($_POST['submit'])) {
            try {
                $login = trim($_POST['login'] ?? '');
                $password = trim($_POST['password'] ?? '');

                // Проверяем заполнение полей
                if (empty($login) || empty($password)) {
                    $this->view->error = 'Логин и пароль обязательны для заполнения';
                    return true;
                }

                $admin = Admins::checkAdminData($login, $password);
                
                if (!$admin) {
                    $this->view->error = 'Неверный логин или пароль';
                    // Логируем попытку неудачного входа
                    error_log('Неудачная попытка входа в админ-панель с логином: ' . $login . ', IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                    return true;
                }

                // Авторизация
                Admins::auth($admin);

                // Редирект после успешной авторизации
                $this->redirect('/'.ADMIN_LINK.'/settings');
                
            } catch (\Exception $e) {
                // Обработка неожиданных ошибок
                error_log('Ошибка при авторизации: ' . $e->getMessage());
                return $this->showErrorPage(500, 'Произошла ошибка при авторизации');
            }
        }

        return true;
    }
}