<?php
// Подключаем модель Settings
require_once dirname(__DIR__) . '/Models/Settings.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'formsAdd') {
        // Получаем данные из формы
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['mail'] ?? '');
        $text = trim($_POST['question'] ?? '');
        $theme = trim($_POST['theme'] ?? '');
        $article = trim($_POST['article'] ?? '');
        
        // Проверяем email
        if (empty($email)) {
            throw new Exception('Введите email');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Введите корректный email');
        }
        
        // ПОЛУЧАЕМ ПОЧТУ ИЗ АДМИНКИ
        $settings = app\Models\Settings::findById(1);
        $to = $settings->email ; // Запасной вариант
        
        // Тема письма
        $subject = '=?utf-8?B?' . base64_encode('Новая заявка с сайта') . '?=';
        
        // Текст письма
        $message = "
        <html>
            <head><meta charset='utf-8'></head>
            <body>
                <h3>Новая заявка</h3>
                <p><b>Артикул:</b> $article</p>
                <p><b>Имя:</b> $name</p>
                <p><b>Телефон:</b> $phone</p>
                <p><b>Email:</b> $email</p>
                <p><b>Тема:</b> $theme</p>
                <p><b>Сообщение:</b><br>$text</p>
            </body>
        </html>
        ";
        
        // Заголовки
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        $headers .= "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        
        // Отправляем
        $sent = mail($to, $subject, $message, $headers);
        
        if ($sent) {
            echo json_encode([
                'success' => true,
                'message' => 'Спасибо! Ваше сообщение отправлено.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception('Ошибка при отправке письма');
        }
    } 
    else if ($action === 'test') {
        echo json_encode([
            'success' => true,
            'message' => 'Тестовый метод работает!',
            'post_data' => $_POST
        ], JSON_UNESCAPED_UNICODE);
    }
    else {
        throw new Exception('Неизвестное действие');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;