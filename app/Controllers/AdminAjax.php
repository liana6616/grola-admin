<?php

namespace app\Controllers;

use app\Controller;
use app\Models\Admins;
use app\Models\Catalog;
use app\Form;
use app\Models\Pages;
use app\View;

class AdminAjax extends Controller
{
    protected function handle(...$parameters)
    {
        try {
            // Определяем действие из POST или GET
            if (!empty($_POST) && isset($_POST['action'])) {
                $action = trim($_POST['action']);
            } elseif (!empty($_GET)) {
                // Для GET запросов берем первое значение
                reset($_GET);
                $action = key($_GET);
            } else {
                // Если нет параметров - 404
                return $this->showErrorPage(404, 'Действие не указано');
            }

            // ДОПОЛНИТЕЛЬНАЯ ПРОВЕРКА: разрешаем доступ к adminLogin без IP-ограничения
            if ($action !== 'adminLogin' && !Admins::isPermittedIP()) {
                return $this->sendJsonError('Доступ запрещен: IP-адрес не разрешен', 403);
            }

            // Проверяем, существует ли метод для действия
            if (!method_exists($this, $action)) {
                return $this->sendJsonError('Действие не найдено', 404);
            }

            // Вызываем метод действия
            call_user_func([$this, $action]);
            
        } catch (\Exception $e) {
            // Логируем ошибку
            error_log('Ошибка в AdminAjax: ' . $e->getMessage());
            
            // Возвращаем JSON ошибку для AJAX
            return $this->sendJsonError(
                'Произошла внутренняя ошибка сервера',
                500,
                ['debug' => $e->getMessage()]
            );
        }
    }

    protected function access(): bool
    {
        try {
            // Если пытаемся войти - разрешаем доступ
            if (isset($_POST['action']) && $_POST['action'] === 'adminLogin') {
                return true;
            }
            
            // Для остальных действий проверяем авторизацию
            if (!Admins::isGuest()) {
                return true;
            }
            
            // Если нет доступа - возвращаем JSON ошибку
            $this->sendJsonError('Требуется авторизация', 401);
            return false;
            
        } catch (\Exception $e) {
            error_log('Ошибка проверки доступа в AdminAjax: ' . $e->getMessage());
            $this->sendJsonError('Ошибка проверки доступа', 500);
            return false;
        }
    }

    protected function adminShow()
    {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $className = $_POST['className'] ?? '';
            $show = (int)($_POST['show'] ?? 0);
            
            // Валидация входных данных
            if (empty($className) || !class_exists($className)) {
                return $this->sendJsonError('Некорректный класс', 400);
            }
            
            if ($id <= 0) {
                return $this->sendJsonError('Некорректный ID', 400);
            }
            
            $object = $className::findById($id);
            if (!$object) {
                return $this->sendJsonError('Объект не найден', 404);
            }
            
            $object->show($show);

            if(!empty($object->original_id)) {
                $original = $className::findById($object->original_id);
                $original->show($show);
            }
            
            $this->sendJson([
                'success' => true,
                'message' => 'Статус обновлен',
                'show' => $show
            ]);
            
        } catch (\Exception $e) {
            error_log('Ошибка в adminShow: ' . $e->getMessage());
            $this->sendJsonError('Ошибка при обновлении статуса', 500);
        }
    }

    protected function adminPreviewDelete()
    {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $className = $_POST['className'] ?? '';
            $path = $_POST['path'] ?? '';
            $field = $_POST['field'] ?? '';
            
            // Валидация
            if (empty($className) || !class_exists($className)) {
                return $this->sendJsonError('Некорректный класс', 400);
            }
            
            if (empty($path) || empty($field)) {
                return $this->sendJsonError('Некорректные параметры', 400);
            }
            
            $object = $className::findById($id);
            if (!$object) {
                return $this->sendJsonError('Объект не найден', 404);
            }
            
            $imagePath = ROOT . $path . '/' . $object->$field;
            
            // Проверяем существование файла
            if (!empty($object->$field) && file_exists($imagePath)) {
                if (!unlink($imagePath)) {
                    return $this->sendJsonError('Не удалось удалить файл', 500);
                }
            }
            
            $object->$field = null;
            $object->save();
            
            $this->sendJson([
                'success' => true,
                'message' => 'Изображение удалено'
            ]);
            
        } catch (\Exception $e) {
            error_log('Ошибка в adminPreviewDelete: ' . $e->getMessage());
            $this->sendJsonError('Ошибка при удалении изображения', 500);
        }
    }

    protected function sortbox()
    {
        try {
            $className = trim($_POST['className'] ?? '');
            $items = $_POST['items'] ?? [];
            
            if (empty($className) || !class_exists($className)) {
                return $this->sendJsonError('Некорректный класс', 400);
            }
            
            if (!is_array($items) || empty($items)) {
                return $this->sendJsonError('Некорректные данные для сортировки', 400);
            }
            
            $i = count($items);
            foreach ($items as $item) {
                $itemId = (int)$item;
                if ($itemId <= 0) continue;
                
                $obj = $className::findById($itemId);
                if ($obj) {
                    $obj->rate = $i;
                    $obj->save();

                    if(!empty($obj->original_id)) {
                        $original = $className::findById($obj->original_id);
                        $original->rate = $i;
                        $original->save();
                    }
                }
                $i--;
            }
            
            $this->sendJson([
                'success' => true,
                'message' => 'Сортировка сохранена',
                'count' => count($items)
            ]);
            
        } catch (\Exception $e) {
            error_log('Ошибка в sortbox: ' . $e->getMessage());
            $this->sendJsonError('Ошибка при сохранении сортировки', 500);
        }
    }

    // Авторизация
    protected function adminLogin() {
        try {
            $login = trim($_POST['login'] ?? '');
            $password = trim($_POST['password'] ?? '');
            
            if (empty($login) || empty($password)) {
                return $this->sendJsonError('Логин и пароль обязательны', 400);
            }
            
            $admin = Admins::checkAdminData($login, $password);
            
            if (!empty($admin)) {
                Admins::auth($admin);
                
                // Логируем успешный вход
                error_log('Успешный вход в админ-панель: ' . $login . ', IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                
                $this->sendJson([
                    'success' => true,
                    'message' => 'success',
                    'redirect' => '/'.ADMIN_LINK.'/settings'
                ]);
            } else {
                // Логируем неудачную попытку
                error_log('Неудачная попытка входа: ' . $login . ', IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                
                $this->sendJsonError('Неверный логин или пароль', 401);
            }
            
        } catch (\Exception $e) {
            error_log('Ошибка в adminLogin: ' . $e->getMessage());
            $this->sendJsonError('Ошибка при авторизации', 500);
        }
    }

    protected function adminLogout()
    {
        try {
            Admins::logout();
            
            $this->sendJson([
                'success' => true,
                'message' => 'Выход выполнен',
                'redirect' => '/'.ADMIN_LINK
            ]);
            
        } catch (\Exception $e) {
            error_log('Ошибка в adminLogout: ' . $e->getMessage());
            $this->sendJsonError('Ошибка при выходе', 500);
        }
    }

    protected function perPageSelect()
    {
        try {
            $class = $_POST['class'] ?? '';
            $itemsPerPage = (int)($_POST['i'] ?? 20);
            
            if (empty($class)) {
                return $this->sendJsonError('Класс не указан', 400);
            }
            
            if ($itemsPerPage <= 0) {
                return $this->sendJsonError('Некорректное количество элементов', 400);
            }
            
            $_SESSION[$class]['per_page'] = $itemsPerPage;
            
            $this->sendJson([
                'success' => true,
                'message' => 'Количество элементов на странице изменено',
                'itemsPerPage' => $itemsPerPage
            ]);
            
        } catch (\Exception $e) {
            error_log('Ошибка в perPageSelect: ' . $e->getMessage());
            $this->sendJsonError('Ошибка при изменении настроек', 500);
        }
    }

    public function dialogLoad()
    {
        // Валидация входных данных
        $fn = filter_input(INPUT_POST, 'class', FILTER_SANITIZE_STRING);
        $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING) ?: '';
        $id = filter_input(INPUT_POST, 'p', FILTER_VALIDATE_INT) ?: 0;
        $i = filter_input(INPUT_POST, 'i', FILTER_VALIDATE_INT) ?: 0;
        $tp = filter_input(INPUT_POST, 'tp', FILTER_SANITIZE_STRING) ?: '';
        
        // Разрешенные классы/функции
        $allowedFunctions = ['sendForm'];
        
        if (!in_array($fn, $allowedFunctions)) {
            http_response_code(400);
            exit('Invalid request');
        }
        
        ob_start();
        
        if ($fn == 'sendForm') {
            // Использовать абсолютный путь
            include ROOT . '/publ/views/modals/sendForm.php';
        }
        
        $html = ob_get_clean();
        
        // Минификация HTML (опционально)
        $html = preg_replace('/\s+/', ' ', trim($html));
        
        echo $html;
        exit;
    }

    // Добавление стоимости по весу в каталоге товаров
    protected function catalogPriceAdd()
    {
        try {
            $index = (int)($_POST['index'] ?? 0);
            
            // Получаем HTML через статический метод Catalog
            $html = Catalog::catalogPriceCard(0, $index);
            
            // Отправляем HTML как JSON
            $this->sendJson([
                'success' => true,
                'html' => $html,
                'index' => $index
            ]);
            
        } catch (\Exception $e) {
            error_log('Ошибка в catalogPriceAdd: ' . $e->getMessage());
            $this->sendJsonError('Ошибка при добавлении цены', 500);
        }
    }
}