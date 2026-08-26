<?php

class AuthController extends BaseController
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login($request)
    {
        $email = isset($request['email']) ? strtolower(trim((string) $request['email'])) : '';
        $password = isset($request['password']) ? $request['password'] : '';

        if ($email === '' || $password === '') {
            return $this->fail('email and password are required', 422);
        }

        $result = $this->authService->login($email, $password);
        if (!$result) {
            return $this->fail('invalid credentials', 401);
        }

        return $this->ok($result, 'login successful');
    }

    public function register($request)
    {
        $email = isset($request['email']) ? strtolower(trim((string) $request['email'])) : '';
        $password = isset($request['password']) ? (string) $request['password'] : '';
        $comunidadId = isset($request['comunidad_id']) ? (int) $request['comunidad_id'] : 0;

        if ($email === '' || $password === '' || $comunidadId <= 0) {
            return $this->fail('email, password and comunidad_id are required', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('email format is invalid', 422);
        }

        if (!$this->isStrongPassword($password)) {
            return $this->fail('password must be at least 12 chars and include uppercase, lowercase, number, special char, and no spaces', 422);
        }

        $createdUser = $this->authService->register(array(
            'comunidad_id' => $comunidadId,
            'persona_id' => isset($request['persona_id']) ? $request['persona_id'] : null,
            'email' => $email,
            'password' => $password,
        ));

        if (!$createdUser) {
            return $this->fail('email already exists or user could not be created', 409);
        }

        return $this->ok(array(
            'user' => $createdUser->toArray(),
        ), 'user registered successfully');
    }

    public function validate($request)
    {
        $token = isset($request['token']) ? trim($request['token']) : '';
        if ($token === '') {
            return $this->fail('token is required', 422);
        }

        $usuario = $this->authService->validateToken($token);
        if (!$usuario) {
            return $this->fail('invalid or expired token', 401);
        }

        return $this->ok(array(
            'user' => $usuario->toArray(),
            'role' => $this->authService->roleForUsuario($usuario),
        ), 'token valid');
    }

    public function logout($request)
    {
        $token = isset($request['token']) ? trim($request['token']) : '';
        if ($token === '') {
            return $this->fail('token is required', 422);
        }

        $this->authService->logout($token);
        return $this->ok(array(), 'logout successful');
    }

    public function forgotPassword($request)
    {
        $email = isset($request['email']) ? strtolower(trim((string) $request['email'])) : '';
        if ($email === '') {
            return $this->fail('email is required', 422);
        }

        $this->authService->requestPasswordReset($email);

        return $this->ok(
            array(),
            'Si la cuenta existe, se generaron las instrucciones para restablecer la contraseña'
        );
    }

    public function validateResetToken($request)
    {
        $token = isset($request['token']) ? trim($request['token']) : '';
        if ($token === '') {
            return $this->fail('token is required', 422);
        }

        $tokenRow = $this->authService->validatePasswordResetToken($token);
        if (!$tokenRow) {
            return $this->fail('invalid or expired reset token', 401);
        }

        return $this->ok(array(
            'valid' => true,
            'expires_at' => isset($tokenRow['expires_at']) ? $tokenRow['expires_at'] : null,
        ), 'reset token valid');
    }

    public function resetPassword($request)
    {
        $token = isset($request['token']) ? trim($request['token']) : '';
        $newPassword = isset($request['new_password']) ? $request['new_password'] : '';

        if ($token === '' || $newPassword === '') {
            return $this->fail('token and new_password are required', 422);
        }

        if (!$this->isStrongPassword($newPassword)) {
            return $this->fail('new_password must be at least 12 chars and include uppercase, lowercase, number, special char, and no spaces', 422);
        }

        $ok = $this->authService->resetPasswordByToken($token, $newPassword);
        if (!$ok) {
            return $this->fail('invalid or expired reset token', 401);
        }

        return $this->ok(array(), 'password updated successfully');
    }

    private function isStrongPassword($password)
    {
        if (!is_string($password) || strlen($password) < 12) {
            return false;
        }

        if (preg_match('/\s/', $password)) {
            return false;
        }

        return preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password)
            && preg_match('/[^A-Za-z0-9]/', $password);
    }
}
