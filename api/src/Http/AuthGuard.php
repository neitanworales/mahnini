<?php

require_once __DIR__ . '/../Services/AuthService.php';

class AuthGuard
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function protect($request, callable $next)
    {
        $token = $this->extractToken($request);
        if ($token === '') {
            return array(
                'success' => false,
                'code' => 401,
                'message' => 'authentication token is required',
            );
        }

        $user = $this->authService->validateToken($token);
        if (!$user) {
            return array(
                'success' => false,
                'code' => 401,
                'message' => 'invalid or expired token',
            );
        }

        $request['auth_token'] = $token;
        $request['auth_user'] = $user;

        return $next($request);
    }

    public function protectWithRoles($request, $roles, callable $next)
    {
        return $this->protect($request, function ($securedRequest) use ($roles, $next) {
            $userId = isset($securedRequest['auth_user']->id) ? (int) $securedRequest['auth_user']->id : 0;
            if ($userId <= 0) {
                return array(
                    'success' => false,
                    'code' => 401,
                    'message' => 'invalid authenticated user',
                );
            }

            if (!$this->authService->userHasAnyRole($userId, $roles)) {
                return array(
                    'success' => false,
                    'code' => 403,
                    'message' => 'insufficient permissions',
                );
            }

            return $next($securedRequest);
        });
    }

    private function extractToken($request)
    {
        if (isset($request['token']) && trim($request['token']) !== '') {
            return trim($request['token']);
        }

        $header = '';
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $header = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        }

        if ($header !== '' && preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }
}
