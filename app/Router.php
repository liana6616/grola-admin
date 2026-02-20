<?php

namespace app;

class Router
{
    private array $routes;

    public function __construct()
    {
        $this->routes = (include ROOT . '/config/routes.php');
    }

    public static function getURI(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Удаляем query string и лишние слеши
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = trim($uri, '/');
        
        return $uri ?: '/';
    }

    public function run(): void
    {
        $uri = self::getURI();

        foreach ($this->routes as $uriPattern => $path) {
            // Добавляем начало и конец строки для точного соответствия
            $pattern = '~^' . $uriPattern . '$~';
            
            if (preg_match($pattern, $uri)) {
                $internalRoute = preg_replace($pattern, $path, $uri);
                
                // Разбиваем на сегменты
                $segments = explode('/', $internalRoute);
                
                // Извлекаем имя контроллера
                $controllerSegment = array_shift($segments);
                if (!$controllerSegment) {
                    throw new \Exception('Controller name is empty');
                }
                
                $controllerName = 'app\Controllers\\' . ucfirst($controllerSegment);
                
                // Проверяем существование класса
                if (!class_exists($controllerName)) {
                    throw new \Exception("Controller $controllerName not found");
                }
                
                // Создаем экземпляр контроллера
                $controllerObject = new $controllerName();
                
                // Вызываем метод контроллера с параметрами
                if (method_exists($controllerObject, '__invoke')) {
                    $result = call_user_func_array([$controllerObject, '__invoke'], $segments);
                } else {
                    // Или предполагаем, что контроллер имеет метод index
                    $result = call_user_func_array([$controllerObject, 'index'], $segments);
                }
                
                if ($result !== null) {
                    return;
                }
                
                // Если контроллер ничего не вернул, выходим
                break;
            }
        }
        
        // Если ни один маршрут не подошел, можно показать 404
        $this->show404();
    }
    
    private function show404(): void
    {
        http_response_code(404);
        echo '404 Page Not Found';
        exit;
    }
}