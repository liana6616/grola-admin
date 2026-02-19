<?php

namespace app\Controllers;

use app\Controller;
use app\Helpers;
use app\Models\Settings;
use app\Models\Pages;
use app\Models\Users;
use app\Models\Forms;
use app\Models\Forms_type;
use app\View;

class UserAjax extends Controller
{
    protected $errors = [];
    protected $send = true;

    protected function handle(...$params) {
        try {
            // Определяем действие из POST или GET
            if (!empty($_POST) && isset($_POST['action'])) {
                $action = trim($_POST['action']);
            } 
            elseif (!empty($_GET)) {
                $action = array_key_first($_GET);
                if ($action !== null) {
                    $action = trim($action);
                } else {
                    return $this->showErrorPage(404, 'Действие не указано');
                }
            } else {
                return $this->showErrorPage(400, 'Неверный запрос');
            }

            // Проверяем существование метода
            if (!method_exists($this, $action)) {
                return $this->sendJsonError("Метод '{$action}' не найден", 404);
            }

            // Вызываем метод
            call_user_func([$this, $action]);
            
        } catch (\Exception $e) {
            error_log('Ошибка в UserAjax->handle: ' . $e->getMessage());
            return $this->sendJsonError('Произошла внутренняя ошибка сервера', 500);
        }
    }

    protected static function response($html) {
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }

    protected static function responseJson($array) {
        if (empty($array)) {
            $array = ['success' => false, 'message' => 'Пустой ответ'];
        }
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Авторизация (для Админки в том числе!)
    protected function userLogin() {
        try {
            $login = trim($_POST['login'] ?? '');
            $password = trim($_POST['password'] ?? '');
            
            // Валидация входных данных
            if (empty($login) || empty($password)) {
                return self::response('Логин и пароль обязательны для заполнения');
            }
            
            $user = Users::checkUserData($login, $password);

            if (!empty($user)) {
                Users::auth($user);
                
                // Логируем успешный вход
                error_log('Успешная авторизация пользователя: ' . $login . ', IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                
                echo "success";
            }
            else {
                // Логируем неудачную попытку входа
                error_log('Неудачная попытка авторизации: ' . $login . ', IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                
                echo "Неверный логин или пароль!";
            }
            
        } catch (\Exception $e) {
            error_log('Ошибка в userLogin: ' . $e->getMessage());
            echo "Произошла ошибка при авторизации. Попробуйте позже.";
        }
    }

    /**
     * Обработчик отправки формы - УПРОЩЕННАЯ ВЕРСИЯ
     */

    public function formsAdd() {
        // Очищаем буферы
        while (ob_get_level()) ob_end_clean();
        
        // Заголовок JSON
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Получаем данные из формы
            $type = (int)($_POST['type'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['mail'] ?? '');
            $text = trim($_POST['question'] ?? '');
            $url = 'https://' . $_SERVER['SERVER_NAME'] . trim($_POST['url'] ?? '/');
            
            // Проверяем email
            if (empty($email)) {
                echo json_encode(['success' => false, 'message' => 'Введите email']);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Введите корректный email']);
                exit;
            }
            
            // Получаем email получателя из настроек
            $settings = Settings::findById(1);
            $to = $settings->email ?? 'verbovskaya_liana@mail.ru'; // Обратите внимание: email_sends (с s на конце!)
            echo json_encode(['success' => true, 'message' => 'Письмо на: ' . $to]);
exit;
            
            // Формируем тему и сообщение для письма
            $subject = "Новая заявка с сайта";
            $message = "
                <strong>Новая заявка с сайта</strong><br><br>
                <strong>Имя:</strong> " . htmlspecialchars($name) . "<br />
                <strong>Телефон:</strong> " . htmlspecialchars($phone) . "<br />
                <strong>E-mail:</strong> " . htmlspecialchars($email) . "<br />
                <strong>Сообщение:</strong> " . nl2br(htmlspecialchars($text)) . "<br />
                <strong>IP:</strong> " . Helpers::get_user_ip() . "<br />
            ";
            
            // Отправляем письмо через Helpers::mail()
            $emailSent = Helpers::mail($to, $subject, $message);
            
            if ($emailSent) {
                echo json_encode(['success' => true, 'message' => 'Отправлено!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка отправки']);
            }
            exit;
            
        } catch (\Exception $e) {
            error_log('Ошибка в formsAdd: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
            exit;
        }
    }
    
    /**
     * Тестовый метод для проверки работы
     */
    public function test() {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'Тестовый метод работает!',
            'post_data' => $_POST
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}