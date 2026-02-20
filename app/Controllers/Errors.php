<?php

namespace app\Controllers;

use app\Controller;

class Errors extends Controller
{
    /**
     * Детали ошибки для технической информации (логи, отладка)
     * @var string|null
     */
    public ?string $error_details = null; // <--- Добавьте это объявление

    protected function handle(...$parameters)
    {
        // Получаем код ошибки из параметров или из URL
        $errorCode = array_shift($parameters) ?? 404;
        
        // Проверяем, есть ли конкретный метод для этого кода ошибки
        if (method_exists($this, 'show' . $errorCode)) {
            $methodName = 'show' . $errorCode;
            $this->$methodName();
        } else {
            // Или используем общий метод
            $this->showError($errorCode);
        }
        
        // Прерываем выполнение после показа ошибки
        exit;
    }

    /**
     * Показать страницу ошибки
     * 
     * @param int $code Код ошибки
     * @param string|null $message Сообщение об ошибке
     * @param string|null $title Заголовок ошибки
     */
    public function showError($code = 404, $message = null, $title = null)
    {
        $this->showErrorPage($code, $message, $title);
    }

    /**
     * Показать страницу ошибки
     * 
     * @param int $code Код ошибки
     * @param string|null $message Сообщение об ошибке
     * @param string|null $title Заголовок ошибки
     */
    public function showErrorPage($code = 404, $message = null, $title = null)
    {
        try {
            // Устанавливаем заголовок по умолчанию
            if ($title === null) {
                $statusTexts = [
                    400 => 'Некорректный запрос',
                    401 => 'Требуется авторизация',
                    403 => 'Доступ запрещен',
                    404 => 'Страница не найдена',
                    405 => 'Метод не разрешен',
                    500 => 'Внутренняя ошибка сервера',
                    503 => 'Сервис временно недоступен',
                ];
                
                $title = $statusTexts[$code] ?? 'Ошибка';
            }

            // Устанавливаем сообщение по умолчанию
            if ($message === null) {
                $defaultMessages = [
                    400 => 'Ваш запрос содержит синтаксическую ошибку.',
                    401 => 'Для доступа к этой странице необходимо авторизоваться.',
                    403 => 'У вас нет прав для доступа к этой странице.',
                    404 => 'Запрошенная страница не существует или была удалена.',
                    405 => 'Используемый метод запроса не поддерживается.',
                    500 => 'На сервере произошла внутренняя ошибка.',
                    503 => 'Сервис временно недоступен. Пожалуйста, попробуйте позже.',
                ];
                
                $message = $defaultMessages[$code] ?? 'Произошла непредвиденная ошибка.';
            }
            
            // Устанавливаем HTTP код ответа
            http_response_code($code);
            
            // Если это AJAX запрос, отправляем JSON
            if ($this->isAjax()) {
                $this->sendJsonError($message ?: $title, $code);
            }
            
            // Передаем переменные в шаблон (унифицированные названия)
            $this->view->error_code = $code;
            $this->view->error_title = $title;
            $this->view->error_message = $message;
            
            // Добавляем решение для некоторых ошибок
            $solutions = [
                400 => 'Проверьте правильность введенных данных и попробуйте снова.',
                401 => 'Войдите в систему или обратитесь к администратору.',
                403 => 'Если вы считаете, что это ошибка, обратитесь к администратору.',
                404 => 'Проверьте правильность URL или перейдите на главную страницу.',
                500 => 'Попробуйте обновить страницу или зайдите позже.',
            ];
            
            if (isset($solutions[$code])) {
                $this->view->error_solution = $solutions[$code];
            }
            
            // Если есть детали ошибки (только для админов)
            if (isset($this->error_details)) {
                $this->view->error_details = $this->error_details;
            }
            
            // Отображаем общий шаблон ошибки
            $templateFile = ROOT . '/private/views/errors.php';
            if (!file_exists($templateFile)) {
                throw new \Exception('Error template not found');
            }
            
            $this->view->display($templateFile);
            
        } catch (\Exception $e) {
            // Если не удалось показать страницу ошибки, показываем простой текст
            http_response_code($code);
            
            // Логируем ошибку
            error_log('Error in showErrorPage: ' . $e->getMessage());
            
            // Показываем простую страницу ошибки
            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>{$code} - {$title}</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                    .error-container { max-width: 600px; margin: 50px auto; text-align: center; }
                    .error-code { font-size: 72px; color: #dc3545; margin-bottom: 20px; }
                    .error-title { font-size: 24px; margin-bottom: 20px; }
                    .error-message { color: #666; margin-bottom: 30px; }
                    .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
                </style>
            </head>
            <body>
                <div class='error-container'>
                    <div class='error-code'>{$code}</div>
                    <h1 class='error-title'>" . htmlspecialchars($title) . "</h1>
                    <p class='error-message'>" . htmlspecialchars($message) . "</p>
                    <a href='/' class='btn'>На главную</a>
                </div>
            </body>
            </html>";
        }
        
        // Прерываем выполнение
        exit;
    }

    /**
     * Показать ошибку 404 (для обратной совместимости)
     * 
     * @param string|null $message Сообщение об ошибке
     * @param string|null $title Заголовок ошибки
     */
    public function show404($message = null, $title = null)
    {
        $this->showErrorPage(404, $message, $title);
    }

    /**
     * Показать ошибку 403 (для обратной совместимости)
     * 
     * @param string|null $message Сообщение об ошибке
     * @param string|null $title Заголовок ошибки
     */
    public function show403($message = null, $title = null)
    {
        $this->showErrorPage(403, $message, $title);
    }

    /**
     * Показать ошибку 500 (для обратной совместимости)
     * 
     * @param string|null $message Сообщение об ошибке
     * @param string|null $title Заголовок ошибки
     */
    public function show500($message = null, $title = null)
    {
        // Для 500 ошибки можно добавить техническую информацию
        if ($message === null && isset($this->technical_info)) {
            $message = $this->technical_info;
        }
        
        $this->showErrorPage(500, $message, $title);
    }

    /**
     * Показать ошибку 400 (для обратной совместимости)
     * 
     * @param string|null $message Сообщение об ошибке
     * @param string|null $title Заголовок ошибки
     */
    public function show400($message = null, $title = null)
    {
        $this->showErrorPage(400, $message, $title);
    }

    /**
     * Показать ошибку 401 (для обратной совместимости)
     * 
     * @param string|null $message Сообщение об ошибке
     * @param string|null $title Заголовок ошибки
     */
    public function show401($message = null, $title = null)
    {
        $this->showErrorPage(401, $message, $title);
    }
}