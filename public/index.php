<?php
// Front controller — session, config, routes, and a minimal dispatcher
declare(strict_types=1);

// Load config (also starts session)
require_once __DIR__ . '/../app/Config/config.php';

// Load routes (returns an array)
$routes = require __DIR__ . '/../routes/web.php';

// Parse request path and method
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$path = '/' . trim((string)preg_replace('#^'.preg_quote($scriptName).'#', '', $request), '/');
if ($path === '/') $path = '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Find handler
$handler = $routes[$method][$path] ?? $routes['ANY'][$path] ?? null;

if (!$handler) {
    http_response_code(404);
    $base = '/Medicus';
    $assets = $base . '/public';
    echo '<!DOCTYPE html><html lang="sq"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>404 – Medicus</title><link rel="stylesheet" href="' . $assets . '/assets/css/app.css"></head><body><main class="page-container"><div class="form-card" style="max-width:480px;margin:48px auto;text-align:center;"><h1 style="font-size:3rem;color:var(--color-primary);margin:0;">404</h1><p style="color:var(--color-text-muted);margin:16px 0;">Faqja nuk u gjet.</p><a href="' . $base . '/" class="btn btn-primary">Kthehu në kryefaqe</a></div></main></body></html>';
    exit;
}

// Dispatch
if (is_callable($handler)) {
    call_user_func($handler);
    exit;
}

// Support 'ControllerName@method' string handlers
if (is_string($handler) && strpos($handler, '@') !== false) {
    list($controllerName, $action) = explode('@', $handler, 2);
    $controllerClass = "App\\Controllers\\{$controllerName}";
    $controllerFile = __DIR__ . '/../app/Controllers/' . $controllerName . '.php';

    if (!file_exists($controllerFile)) {
        http_response_code(500);
        echo 'Controller file not found';
        exit;
    }
    require_once $controllerFile;

    if (!class_exists($controllerClass)) {
        http_response_code(500);
        echo 'Controller class not found';
        exit;
    }

    // Instantiate controller with legacy $mysqli
    $controller = new $controllerClass($mysqli);
    if (!method_exists($controller, $action)) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    call_user_func([$controller, $action]);
    exit;
}

if (is_array($handler) && count($handler) === 2 && is_string($handler[0]) && is_string($handler[1])) {
    $controllerClass = $handler[0];
    $action = $handler[1];

    // Attempt to include controller file (conventional location: app/Controllers)
    $classPath = str_replace('\\', '/', ltrim($controllerClass, '\\')) . '.php';
    $controllerFile = __DIR__ . '/../' . $classPath;
    if (!file_exists($controllerFile)) {
        // Fallback: try app/Controllers/<ClassName>.php
        $controllerFile = __DIR__ . '/../app/Controllers/' . basename($controllerClass) . '.php';
    }
    if (!file_exists($controllerFile)) {
        http_response_code(500);
        echo 'Controller file not found';
        exit;
    }
    require_once $controllerFile;

    if (!class_exists($controllerClass)) {
        http_response_code(500);
        echo 'Controller class not found';
        exit;
    }

    // Instantiate controller with legacy $mysqli
    $controller = new $controllerClass($mysqli);
    if (!method_exists($controller, $action)) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    // Call action (no parameters supported in this minimal dispatcher)
    call_user_func([$controller, $action]);
    exit;
}

if (is_string($handler)) {
    // treat as path to include
    $file = __DIR__ . '/../' . ltrim($handler, '/');
    if (file_exists($file) && is_file($file)) {
        require $file;
        exit;
    }
}

// If we reach here, route could not be dispatched
http_response_code(500);
echo 'Route handler invalid or failed';
exit;
?>
