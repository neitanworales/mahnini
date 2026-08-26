<?php

class AuthService
{
    private const DEFAULT_ROLE_NAME = 'CONSULTA';

    private $usuarios;
    private $tokens;
    private $roles;
    private $personas;

    public function __construct()
    {
        $this->usuarios = new UsuarioRepository();
        $this->tokens = new AuthTokenRepository();
        $this->roles = new RolRepository();
        $this->personas = new PersonaRepository();
    }

    public function login($email, $plainPassword)
    {
        $usuario = $this->usuarios->findByEmail($email);
        if (!$usuario || !$usuario->activo) {
            return null;
        }

        if (!$this->verifyAndUpgradePassword($usuario, $plainPassword)) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
        $this->tokens->createToken((int) $usuario->id, $token, $expiresAt);
        $this->usuarios->touchLastAccess((int) $usuario->id);

        return array(
            'token' => $token,
            'expires_at' => $expiresAt,
            'user' => $usuario->toArray(),
            'role' => $this->roleForUsuario($usuario),
            'persona' => $this->personaForUsuario($usuario),
        );
    }

    public function validateToken($token)
    {
        $tokenRow = $this->tokens->findActiveToken($token);
        if (!$tokenRow) {
            return null;
        }

        return $this->usuarios->findModelById((int) $tokenRow['usuario_id']);
    }

    public function logout($token)
    {
        return $this->tokens->revokeByToken($token);
    }

    public function register($payload)
    {
        $email = strtolower(trim(isset($payload['email']) ? (string) $payload['email'] : ''));

        if ($email === '') {
            return null;
        }

        if ($this->usuarios->findByEmail($email)) {
            return null;
        }

        $comunidadId = isset($payload['comunidad_id']) ? (int) $payload['comunidad_id'] : 0;
        if ($comunidadId <= 0) {
            return null;
        }

        $rol = $this->roles->findByNombre(self::DEFAULT_ROLE_NAME);
        if (!$rol) {
            return null;
        }

        $usuarioId = (int) $this->usuarios->createWithPassword(array(
            'comunidad_id' => $comunidadId,
            'persona_id' => isset($payload['persona_id']) && $payload['persona_id'] ? (int) $payload['persona_id'] : null,
            'rol_id' => (int) $rol['id'],
            'email' => $email,
            'password_hash' => $this->hashPassword((string) $payload['password']),
        ));

        return $usuarioId <= 0 ? null : $this->usuarios->findModelById($usuarioId);
    }

    // Alta administrativa: a diferencia de register(), permite elegir rol y persona explicitamente.
    public function createUsuario($payload)
    {
        $email = strtolower(trim(isset($payload['email']) ? (string) $payload['email'] : ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        if ($this->usuarios->findByEmail($email)) {
            return null;
        }

        $comunidadId = isset($payload['comunidad_id']) ? (int) $payload['comunidad_id'] : 0;
        if ($comunidadId <= 0) {
            return null;
        }

        $rolId = isset($payload['rol_id']) ? (int) $payload['rol_id'] : 0;
        if ($rolId <= 0 || !$this->roles->findById($rolId)) {
            return null;
        }

        $personaId = null;
        if (!empty($payload['persona_id'])) {
            $personaId = (int) $payload['persona_id'];
            if (!$this->personas->findById($personaId)) {
                return null;
            }
        }

        $password = isset($payload['password']) ? (string) $payload['password'] : '';
        if ($password === '') {
            return null;
        }

        $usuarioId = (int) $this->usuarios->createWithPassword(array(
            'comunidad_id' => $comunidadId,
            'persona_id' => $personaId,
            'rol_id' => $rolId,
            'email' => $email,
            'password_hash' => $this->hashPassword($password),
        ));

        return $usuarioId > 0 ? $this->usuarios->findModelById($usuarioId) : null;
    }

    public function requestPasswordReset($email)
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $usuario = $this->usuarios->findByEmail($email);

        // Evita enumeracion de cuentas: siempre responde con la misma forma.
        if ($usuario) {
            $token = bin2hex(random_bytes(32));
            $this->tokens->createPasswordResetToken((int) $usuario->id, $token, $expiresAt);
        }

        return array(
            'expires_at' => $expiresAt,
        );
    }

    public function resetPasswordByToken($token, $newPassword)
    {
        $tokenRow = $this->tokens->findActivePasswordResetToken($token);
        if (!$tokenRow) {
            return false;
        }

        $updated = $this->updatePassword((int) $tokenRow['usuario_id'], $newPassword);
        if (!$updated) {
            return false;
        }

        $this->tokens->consumePasswordResetToken($token);
        return true;
    }

    public function validatePasswordResetToken($token)
    {
        return $this->tokens->findActivePasswordResetToken($token);
    }

    public function hashPassword($plainPassword)
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function updatePassword($usuarioId, $newPassword)
    {
        $hash = $this->hashPassword($newPassword);
        return $this->usuarios->updatePasswordHash((int) $usuarioId, $hash);
    }

    public function roleForUsuario($usuario)
    {
        if (!$usuario || !isset($usuario->rol_id)) {
            return null;
        }

        $rol = $this->roles->findById((int) $usuario->rol_id);
        return $rol ? array('id' => (int) $rol['id'], 'nombre' => strtoupper($rol['nombre'])) : null;
    }

    public function personaForUsuario($usuario)
    {
        if (!$usuario || empty($usuario->persona_id)) {
            return null;
        }

        $persona = $this->personas->findById((int) $usuario->persona_id);
        return $persona ? camelize_keys($persona) : null;
    }

    public function userHasAnyRole($usuarioId, $allowedRoles)
    {
        $usuario = $this->usuarios->findModelById((int) $usuarioId);
        $role = $this->roleForUsuario($usuario);
        if (!$role) {
            return false;
        }

        if ($role['nombre'] === 'SUPERADMIN') {
            return true;
        }

        $allowed = array_map('strtoupper', (array) $allowedRoles);
        return in_array($role['nombre'], $allowed, true);
    }

    private function verifyAndUpgradePassword($usuario, $plainPassword)
    {
        $storedHash = isset($usuario->password_hash) ? $usuario->password_hash : null;
        if (!$storedHash) {
            return false;
        }

        if (!password_verify($plainPassword, $storedHash)) {
            return false;
        }

        if (password_needs_rehash($storedHash, PASSWORD_BCRYPT, array('cost' => 12))) {
            $this->usuarios->updatePasswordHash((int) $usuario->id, $this->hashPassword($plainPassword));
        }

        return true;
    }
}
