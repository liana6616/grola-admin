<?php

namespace app;

abstract class Controller
{
    protected $view;

    public function __construct()
    {
        $this->view = new View();
    }

    protected function access(): bool
    {
        return true;
    }

    public function __invoke(...$params)
    {
        if ($this->access()) {
            $this->handle(...$params);
        }
        else {
            $this->showErrorPage(403, 'Доступ запрещен');
        }

        return true;
    }

    abstract protected function handle(...$params);

    /**
     * Показать страницу ошибки
     * 
     * @param int $code Код ошибки
     * @param string|null $message Сообщение об ошибке
     * @param string|null $title Заголовок ошибки
     */
    protected function showErrorPage($code = 404, $message = null, $title = null)
    {
        try {
            // Создаем экземпляр контроллера ошибок
            $errorController = new \app\Controllers\Errors();
            
            // Передаем техническую информацию, если есть
            if (isset($this->error_details)) {
                $errorController->error_details = $this->error_details;
            }
            
            // Используем метод showError из контроллера Errors
            $errorController->showError($code, $message, $title);
            
        } catch (\Exception $e) {
            // Если не удалось показать страницу ошибки, показываем простой текст
            http_response_code($code);
            
            // Устанавливаем заголовки по умолчанию
            $statusTexts = [
                400 => 'Некорректный запрос',
                401 => 'Требуется авторизация',
                403 => 'Доступ запрещен',
                404 => 'Страница не найдена',
                405 => 'Метод не разрешен',
                500 => 'Внутренняя ошибка сервера',
                503 => 'Сервис временно недоступен',
            ];
            
            $title = $title ?? ($statusTexts[$code] ?? 'Ошибка');
            $message = $message ?? 'Произошла непредвиденная ошибка.';
            
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
        
        // Прерываем выполнение текущего контроллера
        exit;
    }

    /**
     * Перенаправление на другую страницу
     * 
     * @param string $url URL для перенаправления
     * @param int $statusCode HTTP код статуса
     */
    protected function redirect($url, $statusCode = 302)
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }

    /**
     * Проверка AJAX запроса
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Отправка JSON ответа
     */
    protected function sendJson($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Отправка JSON ошибки
     */
    protected function sendJsonError($message, $code = 400, $data = [])
    {
        $this->sendJson([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'data' => $data
            ]
        ], $code);
    }
}