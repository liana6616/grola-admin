<?php

namespace app\Controllers;

use app\Controller;

class Errors extends Controller
{
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

    public function showError($code = 404, $message = null, $title = null)
    {
        // Устанавливаем HTTP заголовок
        $this->setHttpHeader($code);
        
        $this->view->error_code = $code;
        $this->view->error_title = $title ?: $this->getStatusText($code);
        $this->view->error_message = $message;
        $this->view->display(ROOT . '/private/views/errors/general.php');
    }

    // Или для конкретных ошибок
    public function show404()
    {
        $this->setHttpHeader(404);
        $this->view->display(ROOT . '/private/views/errors/404.php');
    }

    public function show403($adminInfo = null)
    {
        $this->setHttpHeader(403);
        $this->view->admin_info = $adminInfo;
        $this->view->display(ROOT . '/private/views/errors/403.php');
    }

    public function show500($technicalInfo = null)
    {
        $this->setHttpHeader(500);
        $this->view->technical_info = $technicalInfo;
        $this->view->display(ROOT . '/private/views/errors/500.php');
    }

    public function show401()
    {
        $this->setHttpHeader(401);
        $this->view->display(ROOT . '/private/views/errors/401.php');
    }
    
    /**
     * Устанавливает HTTP заголовок для ошибки
     */
    private function setHttpHeader($code)
    {
        $statusTexts = [
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
        ];
        
        $text = $statusTexts[$code] ?? 'Error';
        header("HTTP/1.0 {$code} {$text}");
    }
    
    /**
     * Возвращает текст статуса по коду
     */
    private function getStatusText($code)
    {
        $statusTexts = [
            401 => 'Требуется авторизация',
            403 => 'Доступ запрещен',
            404 => 'Страница не найдена',
            500 => 'Внутренняя ошибка сервера',
        ];
        
        return $statusTexts[$code] ?? 'Ошибка';
    }
}