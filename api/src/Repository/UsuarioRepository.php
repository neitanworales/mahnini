<?php

class UsuarioRepository extends BaseRepository
{
    protected $table = 'usuarios';
    protected $fillable = array('comunidad_id', 'persona_id', 'rol_id', 'email', 'activo');

    public function findModelById($id)
    {
        $row = $this->findById($id);
        return $row ? new Usuario($row) : null;
    }

    public function findByEmail($email)
    {
        $sql = 'SELECT * FROM usuarios WHERE email = ? LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? new Usuario($row) : null;
    }

    public function createWithPassword(array $data)
    {
        $sql = 'INSERT INTO usuarios (comunidad_id, persona_id, rol_id, email, password_hash, activo)
            VALUES (?, ?, ?, ?, ?, 1)';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'iiiss',
            $data['comunidad_id'],
            $data['persona_id'],
            $data['rol_id'],
            $data['email'],
            $data['password_hash']
        );

        $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();

        return $id;
    }

    public function updatePasswordHash($usuarioId, $newHash)
    {
        $sql = 'UPDATE usuarios SET password_hash = ? WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $newHash, $usuarioId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function touchLastAccess($usuarioId)
    {
        $sql = 'UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $usuarioId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}
