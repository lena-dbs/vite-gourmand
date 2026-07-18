<?php

declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
        ];
    }
public function dispatch(): void
{
    $method = $_SERVER['REQUEST_METHOD'];
    $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri    = str_replace('/vite-gourmand', '', $uri);
    $uri    = $uri ?: '/';

    foreach ($this->routes as $route) {
        if ($route['method'] !== $method) continue;

        // Convertit les paramètres :id en regex
        $pattern = preg_replace('#:([a-zA-Z]+)#', '([^/]+)', $route['path']);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            preg_match_all('#:([a-zA-Z]+)#', $route['path'], $paramNames);

            foreach ($paramNames[1] as $index => $name) {
                $_GET[$name] = $matches[$index + 1];
            }

            $controllerClass = $route['controller'];
            $action          = $route['action'];
            $controller      = new $controllerClass();
            $controller->$action();
            return;
        }
    }

    http_response_code(404);
    echo '404 - Page non trouvée';
}

}