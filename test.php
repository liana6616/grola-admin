<?php
// Включаем показ всех ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Глубокая проверка системы</h2>";

// Подключаем ядро сайта
require_once 'index.php';

echo "<h3>1. Загруженные контроллеры после подключения index.php:</h3>";
$loaded_classes = get_declared_classes();
$controllers = array_filter($loaded_classes, function($class) {
    return strpos($class, 'app\\Controllers') === 0;
});
echo "<pre>";
print_r(array_values($controllers));
echo "</pre>";

// Проверяем наличие метода catalogCategory в классе PageController
echo "<h3>2. Проверка методов PageController:</h3>";
if (class_exists('app\\Controllers\\PageController')) {
    $methods = get_class_methods('app\\Controllers\\PageController');
    echo "Методы PageController:<br>";
    echo "<pre>";
    print_r($methods);
    echo "</pre>";
    
    // Ищем конкретно catalogCategory
    if (in_array('catalogCategory', $methods)) {
        echo "<span style='color:red'>❌ МЕТОД catalogCategory СУЩЕСТВУЕТ!</span><br>";
    } else {
        echo "<span style='color:green'>✓ Метод catalogCategory НЕ найден</span><br>";
    }
} else {
    echo "❌ Класс PageController не загружен!<br>";
}

// Проверяем CatalogCardController
echo "<h3>3. Проверка CatalogCardController:</h3>";
if (class_exists('app\\Controllers\\CatalogCardController')) {
    echo "✓ Класс CatalogCardController загружен<br>";
    $methods = get_class_methods('app\\Controllers\\CatalogCardController');
    echo "Методы:<br>";
    echo "<pre>";
    print_r($methods);
    echo "</pre>";
} else {
    echo "❌ Класс CatalogCardController не загружен!<br>";
}

// Проверяем родительский класс Controller
echo "<h3>4. Проверка родительского класса Controller:</h3>";
if (class_exists('app\\Controller')) {
    $methods = get_class_methods('app\\Controller');
    echo "Методы Controller:<br>";
    echo "<pre>";
    print_r($methods);
    echo "</pre>";
}

// Проверяем наличие конфликтующих файлов
echo "<h3>5. Поиск других файлов с catalogCategory:</h3>";
$all_php_files = glob(__DIR__ . '/app/**/*.php');
$found = false;
foreach ($all_php_files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'catalogCategory') !== false) {
        echo "Найден в: " . str_replace(__DIR__, '', $file) . "<br>";
        $found = true;
    }
}
if (!$found) {
    echo "Больше нигде не найден<br>";
}

// Проверяем логи ошибок
echo "<h3>6. Последние ошибки из лога:</h3>";
$log_file = ini_get('error_log');
if ($log_file && file_exists($log_file)) {
    $lines = file($log_file);
    $last_lines = array_slice($lines, -20);
    echo "<pre>";
    foreach ($last_lines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "Лог файл не найден или не указан<br>";
}

// Проверка памяти и кеша
echo "<h3>7. Информация о кеше:</h3>";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status && $status['opcache_enabled']) {
        echo "Opcache включен. Очистка...<br>";
        opcache_reset();
        echo "Opcache очищен!<br>";
    } else {
        echo "Opcache не включен<br>";
    }
} else {
    echo "Opcache не доступен<br>";
}

echo "<h2>Проверка завершена!</h2>";
?>