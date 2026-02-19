<?php

namespace app;

class Router
{
    private $routes;

    public function __construct()
    {
        $this->routes = include ROOT . '/config/routes.php';
    }

    public static function getURI(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = trim($uri, '/');
        return $uri ?: '/';
    }

    public function run(): void
    {
        $uri = self::getURI();

        foreach ($this->routes as $uriPattern => $path) {
            $pattern = '~^' . $uriPattern . '$~';
            
            if (preg_match($pattern, $uri)) {
                $internalRoute = preg_replace($pattern, $path, $uri);
                $segments = explode('/', $internalRoute);
                
                $controllerSegment = array_shift($segments);
                if (!$controllerSegment) {
                    throw new \Exception('Controller name is empty');
                }
                
                $controllerName = 'app\Controllers\\' . ucfirst($controllerSegment);
                
                if (!class_exists($controllerName)) {
                    throw new \Exception("Controller $controllerName not found");
                }
                
                $controllerObject = new $controllerName();
                
                if (method_exists($controllerObject, '__invoke')) {
                    $result = call_user_func_array([$controllerObject, '__invoke'], $segments);
                } else {
                    $result = call_user_func_array([$controllerObject, 'index'], $segments);
                }
                
                if ($result !== null) {
                    return;
                }
                break;
            }
        }
        
        $this->show404();
    }
    
    private function show404(): void
    {
        http_response_code(404);
        echo '404 Page Not Found';
        exit;
    }
}