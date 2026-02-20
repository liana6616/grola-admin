<?php

// Включаем строгий режим типизации
declare(strict_types=1);

// Базовые настройки безопасности и окружения
define('ROOT', dirname(__DIR__.'/public_html/'));
define('PUBLIC_DIR', ROOT.'/public/');
define('VIEWS', PUBLIC_DIR . 'views');
define('LOG_DIR', ROOT.'/logs/');
define('ADMIN_LINK', 'visualteam');

// Режим отладки - лучше вынести в конфигурационный файл
$debugMode = true; // В production установить false

// Настройки ошибок в зависимости от режима
if ($debugMode) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT . '/logs/php_errors.log');
} else {
    ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT . '/logs/php_errors.log');
}

// Настройки сессии с улучшенной безопасностью
session_set_cookie_params([
    'lifetime' => 86400, // 24 часа
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Запуск сессии с дополнительными проверками
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Защита от session fixation
    if (empty($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { // 30 минут
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Автозагрузка классов через Composer
if (file_exists(ROOT . '/vendor/autoload.php')) {
    require ROOT . '/vendor/autoload.php';
} else {
    die('Composer autoloader not found. Run "composer install".');
}

// Дополнительная автозагрузка для собственных классов
spl_autoload_register(function ($class) {
    $classFile = ROOT . '/' . str_replace('\\', '/', $class) . '.php';
    
    if (file_exists($classFile)) {
        require_once $classFile;
    } else {
        // Для отладки в режиме разработки
        //if ($debugMode) {
            error_log("Class file not found: {$classFile}".PHP_EOL.PHP_EOL);

            // В production можно логировать, но не прерывать выполнение
          // throw new \Exception("Class {$class} not found");
        //}
    }
});


// --- Middleware обработка города ---
try {
    // Обрабатываем определение города
    \app\Middleware\CityDetector::handle();
    
    // Логируем для отладки
    if ($debugMode && defined('CURRENT_CITY')) {
        //error_log("Current city after detection: " . CURRENT_CITY);
        //error_log("Request URI after city detection: " . ($_SERVER['REQUEST_URI'] ?? '/'));
    }
} catch (\Exception $e) {
    //error_log("City detection error: " . $e->getMessage());
    define('CURRENT_CITY', 'default');
}
// --- КОНЕЦ Middleware обработки ---


// Определяем константы для текущего запроса
define('URI', app\Router::getURI());
define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD'] ?? 'GET');

// Проверка AJAX запроса с учетом различных заголовков
$ajax = false;
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $ajax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
} elseif (!empty($_SERVER['HTTP_ACCEPT'])) {
    // Дополнительная проверка для современных AJAX запросов
    $ajax = strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
}
define('AJAX', $ajax);

// Определяем базовый URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . '://' . $host;
define('BASE_URL', $baseUrl);


// Обработка CORS (если нужно)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Здесь можно настроить разрешенные домены
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

// Обработка предварительных OPTIONS запросов
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

// Установка временной зоны
date_default_timezone_set('Europe/Moscow'); // Настроить под ваш регион


// Функция для безопасного показа ошибки 500
function show500Page($debugMode, $technicalInfo = null) {
    try {
        // Проверяем существование класса и метода
        if (class_exists('\app\Controllers\Errors')) {
            $controller = new \app\Controllers\Errors();
            
            // Передаем техническую информацию
            if ($technicalInfo) {
                $controller->error_details = $technicalInfo;
            }
            
            // Проверяем, существует ли метод и является ли он public
            if (method_exists($controller, 'show500') && is_callable([$controller, 'show500'])) {
                if ($debugMode && $technicalInfo) {
                    $controller->show500($technicalInfo, 'Внутренняя ошибка сервера');
                } else {
                    $controller->show500();
                }
                exit(1);
            }
        }
        
        // Если не удалось использовать контроллер, показываем стандартную страницу
        throw new Exception('Error controller not available');
        
    } catch (Exception $e) {
        // Если контроллер не работает, делаем редирект на главную
        if (!$debugMode) {
            // В production режиме - редирект на главную
            header("Location: /");
            exit;
        } else {
            // В debug режиме показываем информацию
            header("HTTP/1.0 500 Internal Server Error");
            
            // Пытаемся использовать шаблон ошибки напрямую
            $templateFile = ROOT . '/private/views/errors/general.php';
            if (file_exists($templateFile)) {
                try {
                    $view = new \app\View();
                    $view->error_code = 500;
                    $view->error_title = 'Внутренняя ошибка сервера';
                    $view->error_message = $technicalInfo ?: 'Произошла внутренняя ошибка сервера';
                    $view->error_details = $technicalInfo;
                    $view->display($templateFile);
                    exit;
                } catch (Exception $viewException) {
                    // Если шаблон не работает, показываем простой текст
                }
            }
            
            // Запасной вариант - простой текст
            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>500 Internal Server Error</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                    .error-container { max-width: 600px; margin: 50px auto; text-align: center; }
                    .error-code { font-size: 72px; color: #dc3545; margin-bottom: 20px; }
                    .error-message { color: #666; margin-bottom: 30px; }
                    .trace { background: #f1f1f1; padding: 10px; margin-top: 10px; overflow: auto; text-align: left; }
                </style>
            </head>
            <body>
                <div class='error-container'>
                    <div class='error-code'>500</div>
                    <h1>Внутренняя ошибка сервера</h1>
                    <p class='error-message'>" . ($technicalInfo ?: 'Произошла внутренняя ошибка сервера') . "</p>
                </div>
            </body>
            </html>";
        }
        exit(1);
    }
}

// Обработка глобальных ошибок
set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($debugMode) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorTypes = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
    ];
    
    $errorType = $errorTypes[$errno] ?? 'Unknown Error';
    
    $message = sprintf(
        '[%s] %s: %s in %s on line %d',
        date('Y-m-d H:i:s'),
        $errorType,
        $errstr,
        $errfile,
        $errline
    );
    
    // Логируем ошибку
    error_log($message.PHP_EOL.PHP_EOL, 3, LOG_DIR . '/errors.log');
    
    // В режиме отладки показываем ошибку
    if ($debugMode) {
        echo "<div style='background:#f8d7da;color:#721c24;padding:10px;margin:10px;border:1px solid #f5c6cb;border-radius:4px;'>
                <strong>{$errorType}:</strong> {$errstr}<br>
                <small>File: {$errfile} (Line: {$errline})</small>
              </div>";
    }
    
    // Для фатальных ошибок завершаем выполнение
    if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        $technicalInfo = $debugMode ? "{$errorType}: {$errstr} in {$errfile} on line {$errline}" : null;
        show500Page($debugMode, $technicalInfo);
    }
    
    return true;
});

// Обработка исключений
set_exception_handler(function ($exception) use ($debugMode) {
    $message = sprintf(
        '[%s] Exception: %s in %s on line %d',
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    );
    
    error_log($message.PHP_EOL.PHP_EOL, 3, LOG_DIR . '/exceptions.log');
    
    http_response_code(500);
    
    $technicalInfo = $debugMode ? 
        "Exception: {$exception->getMessage()} in {$exception->getFile()} on line {$exception->getLine()}\n" . 
        $exception->getTraceAsString() : 
        null;
    
    show500Page($debugMode, $technicalInfo);
});

// Функция для завершения работы при фатальных ошибках
register_shutdown_function(function () use ($debugMode) {
    $error = error_get_last();
    
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $message = sprintf(
            '[%s] Fatal Error: %s in %s on line %d',
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        error_log($message.PHP_EOL.PHP_EOL, 3, LOG_DIR . '/fatal_errors.log');
        
        http_response_code(500);
        
        $technicalInfo = $debugMode ? 
            "Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}" : 
            null;
        
        show500Page($debugMode, $technicalInfo);
    }
});

// Инициализация и запуск приложения
try {
    $router = new app\Router();
    
    // Запускаем роутер
    $router->run();
    
} catch (Exception $e) {
    // Резервная обработка ошибок на случай, если роутер не справился
    $technicalInfo = $debugMode ? 
        "Application Error: {$e->getMessage()}\n" . $e->getTraceAsString() : 
        null;
    
    show500Page($debugMode, $technicalInfo);
}