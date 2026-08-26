<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/src/Http/Router.php';

send_cors_headers();

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

// El navegador envia OPTIONS como preflight antes de la petición real; no pasa por el router.
if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = new Router();
require dirname(__DIR__) . '/routes/api.v1.php';

$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$path = parse_url($uri, PHP_URL_PATH);

$path = preg_replace('#^/public#', '', $path);
if ($path === '') {
    $path = '/';
}

$request = request_data();
$response = $router->dispatch($method, $path, $request);

$statusCode = isset($response['code']) ? (int) $response['code'] : 200;
http_response_code($statusCode);
header('Content-Type: application/json; charset=utf-8');

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
