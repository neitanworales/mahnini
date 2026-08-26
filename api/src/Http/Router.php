<?php

class Router
{
    private $routes = array();

    public function add($method, $path, callable $handler)
    {
        $method = strtoupper($method);
        if (!isset($this->routes[$method])) {
            $this->routes[$method] = array();
        }

        $this->routes[$method][$path] = $handler;
    }

    public function dispatch($method, $path, $request)
    {
        $method = strtoupper($method);

        if (!isset($this->routes[$method])) {
            return array('success' => false, 'code' => 405, 'message' => 'Method not allowed');
        }

        if (!isset($this->routes[$method][$path])) {
            return array('success' => false, 'code' => 404, 'message' => 'Route not found');
        }

        return call_user_func($this->routes[$method][$path], $request);
    }
}
