<?php
/**
 * Simple Router
 */
class Router {
    
    private $routes = [];
    
    public function get($route, $callback) {
        $this->routes['GET'][$route] = $callback;
    }
    
    public function post($route, $callback) {
        $this->routes['POST'][$route] = $callback;
    }
    
    public function dispatch() {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $url = str_replace('/food_delivery', '', $url);
        $url = trim($url, '/');
        $url = $url ?: 'home';
        
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Map URLs to controllers
        $routeMap = [
            '' => ['AuthController', 'login'],
            'login' => ['AuthController', 'login'],
            'logout' => ['AuthController', 'logout'],
            'register' => ['AuthController', 'register'],
            'home' => ['HomeController', 'index'],
            'product' => ['ProductController', 'show'],
            'order' => ['OrderController', 'index'],
            'profile' => ['ProfileController', 'index'],
            'profile/edit' => ['ProfileController', 'update'],
            'cart' => ['CartController', 'index'],
            'cart/add' => ['CartController', 'add'],
            'cart/remove' => ['CartController', 'remove'],
        ];
        
        if (isset($routeMap[$url])) {
            $controllerName = 'App\\Controllers\\' . $routeMap[$url][0];
            $action = $routeMap[$url][1];
            
            require_once __DIR__ . '/../app/controllers/' . $routeMap[$url][0] . '.php';
            
            $controller = new $controllerName();
            $controller->$action();
        } else {
            // 404
            http_response_code(404);
            echo "404 - Page not found";
        }
    }
}
