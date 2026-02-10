<?php
namespace app\Controllers;

use app\Controller;
use app\Models\TelegramMessage;
use app\Services\Telegram;

class TelegramController extends Controller
{
    private $telegram;

    protected function handle(...$params)
    {   
        try {
            if (empty($params[0])) {
                return $this->showErrorPage(400, 'Действие не указано');
            }

            // Инициализируем основной класс Telegram
            $this->telegram = new \app\Telegram();

            $action = strtolower($params[0]);

            switch ($action) {
                // Устанавливаем новый webhook
                case 'setwebhook':
                    $this->setWebhook();
                    break;

                // Получаем информацию о вебхуке
                case 'getwebhook':
                    $this->getWebhookInfo();
                    break;

                // Удаляем текущий webhook
                case 'deletewebhook':
                    $this->deleteWebhook();
                    break;

                // Обрабатываем webhook
                case 'webhook':
                    $this->webhook();
                    break;

                default:
                    return $this->showErrorPage(404, 'Действие не найдено: ' . $action);
            }
            
        } catch (\Exception $e) {
            error_log('Ошибка в TelegramController->handle: ' . $e->getMessage());
            
            // Для вебхука не показываем ошибки пользователю, только логируем
            if (isset($action) && $action === 'webhook') {
                error_log('Ошибка обработки вебхука: ' . $e->getMessage());
                http_response_code(200); // Всегда возвращаем 200 для Telegram
                exit;
            } else {
                return $this->showErrorPage(500, 'Произошла внутренняя ошибка сервера');
            }
        }
    }
    
    protected function access(): bool
    {
        // Проверяем доступ для административных действий
        $action = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($action, '/telegram/setwebhook') !== false || 
            strpos($action, '/telegram/getwebhook') !== false || 
            strpos($action, '/telegram/deletewebhook') !== false) {
            
            // Проверяем права администратора
            if (empty($_SESSION['admin']['id']) || $_SESSION['admin']['id'] !== 1) {
                $this->showErrorPage(403, 'Доступ запрещен');
                return false;
            }
        }
        
        return true;
    }

    // Устанавливаем новый webhook
    protected function setWebhook()
    {
        try {
            if (empty($_SESSION['admin']['id']) || $_SESSION['admin']['id'] !== 1) {
                throw new \Exception('Доступ запрещен');
            }
            
            $webhookUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/telegram/webhook';
            
            // Используем метод setWebhook из класса Telegram
            $result = $this->telegram->setWebhook($webhookUrl);
            
            if ($result === false || (is_string($result) && json_decode($result, true)['ok'] === false)) {
                throw new \Exception('Не удалось установить вебхук');
            }
            
            echo $result;
            
        } catch (\Exception $e) {
            error_log('Ошибка в setWebhook: ' . $e->getMessage());
            echo 'Ошибка: ' . htmlspecialchars($e->getMessage());
        }
    }

    // Получаем информацию о вебхуке
    protected function getWebhookInfo()
    {
        try {
            if (empty($_SESSION['admin']['id']) || $_SESSION['admin']['id'] !== 1) {
                throw new \Exception('Доступ запрещен');
            }
            
            // Используем метод getWebhookInfo из класса Telegram
            $result = $this->telegram->getWebhookInfo();
            
            if ($result === false || (is_string($result) && json_decode($result, true)['ok'] === false)) {
                throw new \Exception('Не удалось получить информацию о вебхуке');
            }
            
            echo $result;
            
        } catch (\Exception $e) {
            error_log('Ошибка в getWebhookInfo: ' . $e->getMessage());
            echo 'Ошибка: ' . htmlspecialchars($e->getMessage());
        }
    }

    // Удаляем текущий webhook
    protected function deleteWebhook()
    {
        try {
            if (empty($_SESSION['admin']['id']) || $_SESSION['admin']['id'] !== 1) {
                throw new \Exception('Доступ запрещен');
            }
            
            // Используем метод deleteWebhook из класса Telegram
            $result = $this->telegram->deleteWebhook();
            
            if ($result === false || (is_string($result) && json_decode($result, true)['ok'] === false)) {
                throw new \Exception('Не удалось удалить вебхук');
            }
            
            echo $result;
            
        } catch (\Exception $e) {
            error_log('Ошибка в deleteWebhook: ' . $e->getMessage());
            echo 'Ошибка: ' . htmlspecialchars($e->getMessage());
        }
    }

    // Обрабатываем webhook
    protected function webhook() {
        try {
            // Получаем данные от Telegram
            $input = file_get_contents('php://input');
            if (empty($input)) {
                throw new \Exception('Пустой запрос от Telegram');
            }
            
            $result = json_decode($input, true);
            if ($result === null) {
                throw new \Exception('Невалидный JSON от Telegram');
            }
            
            // Логируем входящий запрос для отладки
            error_log('Telegram webhook received: ' . json_encode($result));
            
            $cb = $result["callback_query"] ?? null; // Нажатие кнопок клавиатуры
            
            if (!empty($cb)) {
                // Обработка callback_query (нажатие кнопок)
                $this->handleCallbackQuery($cb);
            } else {
                // Обработка обычных сообщений
                $this->handleMessage($result);
            }
            
            // Всегда возвращаем 200 OK для Telegram
            http_response_code(200);
            
        } catch (\Exception $e) {
            error_log('Ошибка в webhook обработке: ' . $e->getMessage());
            
            // Все равно возвращаем 200, чтобы Telegram не пытался отправлять повторно
            http_response_code(200);
        }
    }
    
    /**
     * Обработка callback_query (нажатие кнопок)
     */
    private function handleCallbackQuery($cb)
    {
        try {
            $message_id = $cb['message']['message_id'] ?? null;
            $chat_id = $cb['message']['chat']['id'] ?? null;
            $txt = $cb['message']['text'] ?? '';
            $caption = $cb['message']['caption'] ?? '';
            $text = $cb['data'] ?? '';
            $callback_query_id = $cb['id'] ?? '';
            
            if (empty($chat_id) || empty($text) || empty($callback_query_id)) {
                throw new \Exception('Невалидные данные callback_query');
            }
            
            // Ответ на нажатие кнопки (чтобы убрать "часики" у кнопки)
            // В классе Telegram нет метода answerCallbackQuery, нужно его добавить
            // или использовать другой подход
            
            // Временное решение: отправляем пустой ответ
            $this->telegram->answerCallbackQuery($callback_query_id);
            
            if ($text == '/menu') {
                $keyboard = [
                    [['text' => 'Меню 2', 'callback_data' => '/menu2']]
                ];
                
                $mess = 'Меню';
                $response = $this->telegram->sendMessage($chat_id, $mess, $keyboard, 'inline_keyboard', '');
                
                $res = json_decode($response, true);
                if (isset($res['ok']) && $res['ok'] == true) {
                    TelegramMessage::newMess($chat_id, $res['result']['message_id'], $mess, '0', 1);
                }
            }
            elseif ($text == '/menu2') {
                $keyboard = [
                    [['text' => 'Меню', 'callback_data' => '/menu']]
                ];
                
                $mess = 'Меню 2';
                $response = $this->telegram->sendMessage($chat_id, $mess, $keyboard, 'inline_keyboard', '');
                
                $res = json_decode($response, true);
                if (isset($res['ok']) && $res['ok'] == true) {
                    TelegramMessage::newMess($chat_id, $res['result']['message_id'], $mess, '0', 1);
                }
            }
            
        } catch (\Exception $e) {
            error_log('Ошибка в handleCallbackQuery: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Обработка обычных сообщений
     */
    private function handleMessage($result)
    {
        try {
            $text = $result["message"]["text"] ?? '';
            $chat_id = $result["message"]["chat"]["id"] ?? null;
            $username = $result["message"]["from"]["username"] ?? '';
            
            if (empty($chat_id)) {
                throw new \Exception('Невалидный chat_id в сообщении');
            }
            
            if ($text == "/start") {
                $keyboard = [
                    [['text' => 'Меню', 'callback_data' => '/menu']]
                ];
                
                $mess = "Добро пожаловать!";
                $response = $this->telegram->sendMessage($chat_id, $mess, $keyboard, 'inline_keyboard', '');
                
                $res = json_decode($response, true);
                if (isset($res['ok']) && $res['ok'] == true) {
                    TelegramMessage::newMess($chat_id, $res['result']['message_id'], $mess, '0', 1);
                }
            } else {
                $keyboard = [
                    [['text' => 'Меню', 'callback_data' => '/menu']]
                ];
                
                $mess = "Меню";
                $response = $this->telegram->sendMessage($chat_id, $mess, $keyboard, 'inline_keyboard', '');
                
                $res = json_decode($response, true);
                if (isset($res['ok']) && $res['ok'] == true) {
                    TelegramMessage::newMess($chat_id, $res['result']['message_id'], $mess, '0', 1);
                }
            }
            
        } catch (\Exception $e) {
            error_log('Ошибка в handleMessage: ' . $e->getMessage());
            throw $e;
        }
    }
}