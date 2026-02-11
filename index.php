<?php
declare(strict_types=1);
echo "test1";
// Включаем строгий режим типизации


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

// Обработка входящих данных
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Очистка GET, POST, REQUEST массивов (опционально, может замедлить работу)
if ($debugMode) {
    $_GET = cleanInput($_GET);
    $_POST = cleanInput($_POST);
    $_REQUEST = cleanInput($_REQUEST);
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
        if (!$debugMode) {
            include VIEWS_DIR . '/errors/500.php';
        }
        exit(1);
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
    
    if ($debugMode) {
        echo "<div style='background:#f8d7da;color:#721c24;padding:10px;margin:10px;border:1px solid #f5c6cb;border-radius:4px;'>
                <strong>Exception:</strong> {$exception->getMessage()}<br>
                <small>File: {$exception->getFile()} (Line: {$exception->getLine()})</small>
                <pre style='background:#fff;padding:10px;margin-top:5px;border-radius:3px;overflow:auto;'>{$exception->getTraceAsString()}</pre>
              </div>";
    } else {
        include VIEWS_DIR . '/errors/500.php';
    }
    
    exit(1);
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
        
        if ($debugMode) {
            echo "<div style='background:#f8d7da;color:#721c24;padding:10px;margin:10px;border:1px solid #f5c6cb;border-radius:4px;'>
                    <strong>Fatal Error:</strong> {$error['message']}<br>
                    <small>File: {$error['file']} (Line: {$error['line']})</small>
                  </div>";
        } else {
            include VIEWS_DIR . '/errors/500.php';
        }
    }
});

// Инициализация и запуск приложения
try {
    $router = new app\Router();
    
    // Запускаем роутер
    $router->run();
    
} catch (Exception $e) {
    // Резервная обработка ошибок на случай, если роутер не справился
    http_response_code(500);
    
    if ($debugMode) {
        echo "<div style='background:#f8d7da;color:#721c24;padding:10px;margin:10px;border:1px solid #f5c6cb;border-radius:4px;'>
                <strong>Application Error:</strong> {$e->getMessage()}<br>
                <pre style='background:#fff;padding:10px;margin-top:5px;border-radius:3px;overflow:auto;'>{$e->getTraceAsString()}</pre>
              </div>";
    } else {
        echo '<h1>Application Error</h1>';
        echo '<p>Sorry, something went wrong. Please try again later.</p>';
    }
    
    error_log($e->getMessage() . "\n" . $e->getTraceAsString().PHP_EOL.PHP_EOL, 3, LOG_DIR . '/app_errors.log');
}