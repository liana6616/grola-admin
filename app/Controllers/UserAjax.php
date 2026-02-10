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

    /*
    public function formsAdd() {
        try {
            $type = (int)($_POST['type'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $text = trim($_POST['text'] ?? '');
            $url = 'https://' . $_SERVER['SERVER_NAME'] . trim($_POST['url'] ?? '/');

            if (empty($type)) {
                return self::response('Тип формы не указан');
            }
            
            // Проверяем существование типа формы
            $forms_type = Forms_type::findById($type);
            if (!$forms_type) {
                return self::response('Неверный тип формы');
            }
            
            // Валидация по типу формы
            switch ($type) {
                case 1:
                    if (empty($phone) && empty($email)) {
                        return self::response('Укажите телефон или email');
                    }
                    break;
            }

            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return self::response('Введите корректный email');
            }
            
            // Валидация телефона (базовая проверка)
            if (!empty($phone) && !preg_match('/^[\d\s\-\+\(\)]{7,20}$/', $phone)) {
                return self::response('Введите корректный номер телефона');
            }

            // Сохранение формы
            $obj = new Forms();
            $obj->type = $type;
            $obj->name = $name;
            $obj->phone = $phone;
            $obj->email = $email;
            $obj->text = $text;
            $obj->date = time();
            $obj->url = $url;
            $obj->ip = Helpers::get_user_ip();
            $obj->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $save = $obj->save();

            if (!$save) {
                throw new \Exception('Не удалось сохранить форму в базу данных');
            }

            $forms_type_name = $forms_type->name;

            // Подготовка email
            $subject = "Новая заявка «{$forms_type_name}»";
            $message = "
                <strong>Новая заявка «{$forms_type_name}»</strong> от посетителя " . $_SERVER['SERVER_NAME'] . "<br><br>
                <strong>Со страницы:</strong> " . htmlspecialchars($url) . "<br />
                <strong>Дата:</strong> " . date('d.m.Y H:i:s') . "<br />
                <strong>Имя:</strong> " . htmlspecialchars($name) . "<br />
                <strong>Телефон:</strong> " . htmlspecialchars($phone) . "<br />
                <strong>E-mail:</strong> " . htmlspecialchars($email) . "<br />
                <strong>Комментарий:</strong> " . nl2br(htmlspecialchars($text)) . "<br />
                <strong>IP-адрес:</strong> " . $obj->ip . "<br />
            ";

            // Отправка email
            $settings = Settings::findById(1);
            $to = $settings->email_send ?? 'admin@example.com';

            $emailSent = Helpers::mail($to, $subject, $message);

            if (!$emailSent) {
                error_log('Не удалось отправить email уведомление о новой заявке');
            }

            // Логирование успешной отправки
            error_log('Отправлена форма: ' . $forms_type_name . ', IP: ' . $obj->ip);

            return self::response('1');
            
        } catch (\Exception $e) {
            error_log('Ошибка в formsAdd: ' . $e->getMessage());
            return self::response('Произошла ошибка при отправке формы. Попробуйте позже еще раз');
        }
    }
    */
}