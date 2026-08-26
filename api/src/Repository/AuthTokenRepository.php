<?php

class AuthTokenRepository extends BaseRepository
{
    protected $table = 'auth_tokens';

    public function createToken($usuarioId, $token, $expiresAt, $tokenType = 'AUTH')
    {
        $sql = 'INSERT INTO auth_tokens (usuario_id, token, token_type, expires_at) VALUES (?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('isss', $usuarioId, $token, $tokenType, $expiresAt);
        $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();

        return $id;
    }

    public function findActiveToken($token, $tokenType = 'AUTH')
    {
        $sql = 'SELECT * FROM auth_tokens
            WHERE token = ? AND token_type = ? AND revoked_at IS NULL AND (expires_at IS NULL OR expires_at > NOW())
            LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $token, $tokenType);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    public function revokeByToken($token, $tokenType = 'AUTH')
    {
        $sql = 'UPDATE auth_tokens SET revoked_at = NOW() WHERE token = ? AND token_type = ? AND revoked_at IS NULL';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $token, $tokenType);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function createPasswordResetToken($usuarioId, $token, $expiresAt)
    {
        return $this->createToken($usuarioId, $token, $expiresAt, 'PASSWORD_RESET');
    }

    public function findActivePasswordResetToken($token)
    {
        return $this->findActiveToken($token, 'PASSWORD_RESET');
    }

    public function consumePasswordResetToken($token)
    {
        return $this->revokeByToken($token, 'PASSWORD_RESET');
    }
}
