<?php

class BaseController
{
    protected function ok($data = array(), $message = 'ok')
    {
        return array(
            'success' => true,
            'message' => $message,
            'data' => $data,
        );
    }

    protected function fail($message, $code = 400, $details = array())
    {
        return array(
            'success' => false,
            'message' => $message,
            'code' => $code,
            'details' => $details,
        );
    }

    protected function requireAuth()
    {
        if (!function_exists('get_bearer_token')) {
            return null;
        }

        $token = get_bearer_token();
        if ($token === '') {
            return null;
        }

        $auth = new AuthService();
        return $auth->validateToken($token);
    }

    protected function hasAnyRole($usuario, array $allowedRoles)
    {
        if (!$usuario || empty($allowedRoles)) {
            return empty($allowedRoles);
        }

        $auth = new AuthService();
        return $auth->userHasAnyRole((int) $usuario->id, $allowedRoles);
    }
}
