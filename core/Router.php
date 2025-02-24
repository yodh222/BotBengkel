<?php
// core/Router.php

class Router
{
    protected $routes = [];

    public function __construct()
    {
        // Muat definisi route dari file routes.php
        $this->routes = require __DIR__ . '/../routes.php';
    }

    public function direct($uri)
    {
        $uri = trim(parse_url($uri, PHP_URL_PATH), '/');

        // Cek exact match terlebih dahulu
        if (isset($this->routes[$uri])) {
            $route = $this->routes[$uri];
            $this->handleRoute($route, []);
            return;
        }

        // Jika tidak ada exact match, cek route dinamis
        foreach ($this->routes as $routePattern => $action) {
            if (strpos($routePattern, '{') !== false) {
                $pattern = preg_replace('/\{[^\/]+\}/', '([^/]+)', $routePattern);
                $pattern = '#^' . trim($pattern, '/') . '$#';

                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches);
                    $this->handleRoute($action, $matches);
                    return;
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    protected function handleRoute($route, $parameters)
    {
        // Jika route merupakan callable, panggil function tersebut dengan parameter
        if (is_callable($route)) {
            call_user_func_array($route, $parameters);
        }
        // Jika route berupa string dengan format "Controller@method"
        elseif (strpos($route, '@') !== false) {
            list($controller, $method) = explode('@', $route);
            $this->callAction($controller, $method, $parameters);
        }
        // Jika route bukan callable dan tidak mengandung '@', anggap sebagai nama view
        else {
            $this->renderView($route);
        }
    }

    protected function callAction($controller, $method, $parameters = [])
    {
        $controllerFile = __DIR__ . '/../app/controllers/' . $controller . '.php';
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
        } else {
            die("Controller {$controller} tidak ditemukan.");
        }
        if (!class_exists($controller)) {
            die("Class {$controller} tidak ditemukan.");
        }
        $controllerObject = new $controller();
        if (!method_exists($controllerObject, $method)) {
            die("Method {$method} tidak ditemukan di controller {$controller}.");
        }
        call_user_func_array([$controllerObject, $method], $parameters);
    }

    protected function renderView($view)
    {
        $viewFile = __DIR__ . '/../app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View file {$view} tidak ditemukan.");
        }
    }
}
