<?php
namespace app\Middleware;

use app\Models\City;

class CityDetector
{
    private static ?string $defaultCityCode = null;
    
    public static function handle(): void
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Удаляем query string и лишние слеши
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = trim($uri, '/');
        
        // Получаем код города по умолчанию
        self::$defaultCityCode = self::getDefaultCityCode();
        
        // Если URI пустой
        if (empty($uri)) {
            define('CURRENT_CITY', 'default');
            return;
        }
        
        $segments = explode('/', $uri);
        $firstSegment = $segments[0];
        
        // Проверяем, является ли первый сегмент валидным городом
        if (self::isValidCity($firstSegment)) {
            $cityCode = $firstSegment;
            
            // Проверяем, не является ли это городом по умолчанию
            if (self::isDefaultCity($cityCode)) {
                // Город по умолчанию - делаем редирект на версию без префикса
                self::redirectToDefault($uri, $segments);
                return;
            }
            
            // Устанавливаем город
            define('CURRENT_CITY', $cityCode);
            
            // Убираем город из URI для дальнейшей маршрутизации
            array_shift($segments);
            $newUri = '/' . implode('/', $segments);
            $_SERVER['REQUEST_URI'] = $newUri ?: '/';
            
            error_log("CityDetector: установлен город '{$cityCode}', новый URI = '{$newUri}'");
        } else {
            // Устанавливаем город по умолчанию
            define('CURRENT_CITY', 'default');
        }
    }
    
    /**
     * Делает редирект с URL с городом по умолчанию на версию без города
     */
    private static function redirectToDefault(string $uri, array $segments): void
    {
        // Убираем город из сегментов
        array_shift($segments);
        $newPath = '/' . implode('/', $segments) ?: '/';
        
        // Сохраняем query string если есть
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        if ($queryString) {
            $newPath .= '?' . $queryString;
        }
        
        // Делаем 301 редирект (постоянный)
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $newPath);
        exit;
    }
    
    /**
     * Проверяет, является ли город городом по умолчанию
     */
    private static function isDefaultCity(string $cityCode): bool
    {
        $defaultCode = self::getDefaultCityCode();
        return $cityCode === $defaultCode;
    }
    
    /**
     * Получает код города по умолчанию
     */
    private static function getDefaultCityCode(): string
    {
        try {
            $defaultCity = City::getDefaultCity();
            return $defaultCity ? $defaultCity->code : 'default';
        } catch (\Exception $e) {
            error_log("CityDetector: ошибка получения города по умолчанию: " . $e->getMessage());
            return 'default';
        }
    }
    
    /**
     * Публичный метод для проверки валидности города
     */
    public static function isValidCity(string $code): bool
    {
        // Список зарезервированных маршрутов
        $reservedRoutes = [
            'visualteam', 'migrator', 'telegram', 
            'helpers', 'user', 'ajax', 'api'
        ];
        
        if (in_array($code, $reservedRoutes, true)) {
            return false;
        }
        
        // Проверяем в БД
        try {
            $city = City::findByCode($code);
            return $city !== null && $city->show == 1;
        } catch (\Exception $e) {
            error_log("CityDetector: ошибка проверки города '{$code}': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Генерирует URL с учетом текущего города
     * Автоматически убирает город по умолчанию из URL
     */
    public static function generateUrl(string $path = ''): string
    {
        $currentCity = defined('CURRENT_CITY') ? CURRENT_CITY : 'default';
        $defaultCity = self::getDefaultCityCode();
        
        // Если текущий город город по умолчанию или undefined - не добавляем префикс
        if ($currentCity === 'default' || $currentCity === $defaultCity) {
            return '/' . ltrim($path, '/');
        }
        
        return '/' . $currentCity . '/' . ltrim($path, '/');
    }
    
    /**
     * Генерирует URL для конкретного города
     * Если город - город по умолчанию, не добавляет префикс
     */
    public static function generateUrlForCity(string $path = '', ?string $cityCode = null): string
    {
        if (!$cityCode) {
            return self::generateUrl($path);
        }
        
        $defaultCity = self::getDefaultCityCode();
        
        // Если указанный город - город по умолчанию, не добавляем префикс
        if ($cityCode === $defaultCity || $cityCode === 'default') {
            return '/' . ltrim($path, '/');
        }
        
        return '/' . $cityCode . '/' . ltrim($path, '/');
    }
    
    /**
     * Возвращает текущий город
     */
    public static function getCurrentCity(): ?string
    {
        return defined('CURRENT_CITY') ? CURRENT_CITY : null;
    }
    
    /**
     * Проверяет, является ли текущий город городом по умолчанию
     */
    public static function isCurrentCityDefault(): bool
    {
        $currentCity = self::getCurrentCity();
        $defaultCity = self::getDefaultCityCode();
        
        return !$currentCity || $currentCity === 'default' || $currentCity === $defaultCity;
    }
    
    /**
     * Получает город по умолчанию
     */
    public static function getDefaultCity(): ?array
    {
        try {
            $city = City::getDefaultCity();
            return $city ? $city->toArray() : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}