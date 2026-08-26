<?php

class RolRepository extends BaseRepository
{
    protected $table = 'roles';
    protected $fillable = array('nombre', 'descripcion');

    public function findByNombre($nombre)
    {
        $sql = 'SELECT * FROM roles WHERE nombre = ? LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }
}
