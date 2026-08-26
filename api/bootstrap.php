<?php

error_reporting(E_ALL);
ini_set('display_errors', '0');

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

// Autoloader simple: mapea nombre de clase -> archivo bajo src/, sin depender de composer.
spl_autoload_register(function ($class) {
    static $map = null;

    if ($map === null) {
        $map = array();
        $srcDir = __DIR__ . '/src';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $map[$file->getBasename('.php')] = $file->getPathname();
            }
        }
    }

    if (isset($map[$class])) {
        require_once $map[$class];
    }
});

function request_data()
{
    $data = $_GET;

    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $data = array_merge($data, $decoded);
        }
    } else {
        $data = array_merge($data, $_POST);
    }

    return $data;
}

function get_bearer_token()
{
    $header = '';
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $header = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $header = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    } elseif (function_exists('getallheaders')) {
        // Respaldo cuando el servidor no expone Authorization en $_SERVER (p. ej. Apache sin CGIPassAuth).
        foreach (getallheaders() as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                $header = trim($value);
                break;
            }
        }
    }

    if ($header !== '' && preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
        return trim($matches[1]);
    }

    return '';
}

function send_cors_headers()
{
    $configured = getenv('CORS_ALLOWED_ORIGINS');
    $allowedOrigins = $configured !== false && $configured !== ''
        ? array_map('trim', explode(',', $configured))
        : array('http://localhost:4200');

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
}

// Convierte recursivamente claves snake_case a camelCase (usado fuera de CrudController, ej. AuthService).
function camelize_keys($data)
{
    if (is_array($data)) {
        $isAssoc = array_keys($data) !== range(0, count($data) - 1);
        if ($isAssoc) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[snake_to_camel($key)] = camelize_keys($value);
            }
            return $result;
        }

        $items = array();
        foreach ($data as $value) {
            $items[] = camelize_keys($value);
        }
        return $items;
    }

    return $data;
}

function snake_to_camel($value)
{
    $parts = explode('_', $value);
    $first = array_shift($parts);
    $parts = array_map('ucfirst', $parts);
    return $first . implode('', $parts);
}
