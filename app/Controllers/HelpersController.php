<?php

namespace app\Controllers;

use app\Db;
use app\Model;
use app\Controller;
use app\Helpers;
use app\DbTriggerLogger;
use app\Migrator;
use WebPConvert\WebPConvert;

class HelpersController extends Controller
{
    protected function handle(...$parameters)
    {
        try {
            if (!empty($parameters)) {
                $action = array_shift($parameters);
                
                // Проверяем существование метода
                if (!method_exists($this, $action)) {
                    return $this->showErrorPage(404, "Метод '{$action}' не найден");
                }
                
                // Проверяем, является ли метод публичным или защищенным
                $reflectionMethod = new \ReflectionMethod($this, $action);
                if (!$reflectionMethod->isPublic() && !$reflectionMethod->isProtected()) {
                    return $this->showErrorPage(403, "Доступ к методу '{$action}' запрещен");
                }
                
                call_user_func_array([$this, $action], $parameters);
            } else {
                // Если нет параметров, показываем список доступных команд
                $this->showHelp();
            }
            
        } catch (\Exception $e) {
            error_log('Ошибка в HelpersController: ' . $e->getMessage() . ' в файле ' . $e->getFile() . ':' . $e->getLine());
            return $this->showErrorPage(500, 'Произошла внутренняя ошибка', $e->getMessage());
        }
    }
    
    protected function access(): bool
    {
        // Проверяем доступ только из консоли или для админов
        if (php_sapi_name() === 'cli') {
            return true; // Разрешаем доступ из командной строки
        }
        
        // Для веб-доступа проверяем авторизацию админа
         if (class_exists('\app\Models\Admins')) {
             return !\app\Models\Admins::isGuest();
         }
        
        // По умолчанию запрещаем веб-доступ к хелперам
        $this->showErrorPage(403, 'Доступ к утилитам разрешен только из командной строки');
        return false;
    }

    /**
     * Конвертер изображений в webp
     */
    public function webp() 
    {
        try {
            $source = ROOT.'/public/src/img/logo.png';
            $destination = ROOT.'/public/src/img/logo.webp';
            
            if (!file_exists($source)) {
                throw new \Exception("Исходный файл не найден: {$source}");
            }
            
            if (!is_writable(dirname($destination))) {
                throw new \Exception("Нет прав на запись в директорию: " . dirname($destination));
            }
            
            // Проверяем доступность библиотеки WebPConvert
            if (!class_exists('WebPConvert\WebPConvert')) {
                throw new \Exception("Библиотека WebPConvert не установлена");
            }
            
            WebPConvert::convert($source, $destination, []);
            
            echo "Конвертация успешно завершена:\n";
            echo "Исходный файл: {$source}\n";
            echo "Результат: {$destination}\n";
            
            // Показываем размеры файлов
            $originalSize = filesize($source);
            $webpSize = filesize($destination);
            $saving = round((1 - $webpSize / $originalSize) * 100, 2);
            
            echo "Размер оригинала: " . $this->formatBytes($originalSize) . "\n";
            echo "Размер WebP: " . $this->formatBytes($webpSize) . "\n";
            echo "Экономия: {$saving}%\n";
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе webp: ' . $e->getMessage());
            throw new \Exception("Ошибка конвертации в WebP: " . $e->getMessage());
        }
    }

    /**
     * Вспомогательный метод для форматирования размера файлов
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Создание триггеров логирования изменений в базе данных
     */
    public function trigger() 
    {
        try {
            // Создаем экземпляр класса
            $logger = new DbTriggerLogger(Db::PDO());
            
            // Таблицы для исключения (опционально)
            $excludeTables = [
                'telegram_errors', 
                'telegram_message', 
                'telegram_settings',
                'users_class',
                'admins_class'
            ];

            echo "Создание триггеров логирования...\n";
            echo "Исключаемые таблицы: " . implode(', ', $excludeTables) . "\n\n";
            
            // Создаем триггеры для всех таблиц
            $results = $logger->createTriggersForAllTables($excludeTables, true);
            
            // Выводим результаты
            echo "Результаты создания триггеров:\n";
            foreach ($results as $table => $result) {
                $status = $result['success'] ? '✓ Успешно' : '✗ Ошибка';
                echo "{$table}: {$status}\n";
                if (!$result['success'] && !empty($result['error'])) {
                    echo "   Ошибка: {$result['error']}\n";
                }
            }
            
            $successCount = count(array_filter($results, function($r) { return $r['success']; }));
            $totalCount = count($results);
            
            echo "\nИтого: создано {$successCount} из {$totalCount} триггеров\n";
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе trigger: ' . $e->getMessage());
            throw new \Exception("Ошибка создания триггеров: " . $e->getMessage());
        }
    }

    /**
     * Очищаем старые логи
     */
    public function clearLogs() 
    {
        try {
            // Создаем экземпляр класса
            $logger = new DbTriggerLogger(Db::PDO());

            $days = 30; // Кол-во дней хранения логов
            $olderThan = date('Y-m-d', strtotime("-{$days} days"));

            echo "Очистка старых логов...\n";
            echo "Удаляем логи старше: {$olderThan} ({$days} дней)\n\n";
            
            $deletedCount = $logger->cleanupOldLogs($olderThan);
            
            echo "Удалено записей логов: {$deletedCount}\n";
            
        } catch (\Exception $e) {
            error_log('Ошибка в методе clearLogs: ' . $e->getMessage());
            throw new \Exception("Ошибка очистки логов: " . $e->getMessage());
        }
    }
}