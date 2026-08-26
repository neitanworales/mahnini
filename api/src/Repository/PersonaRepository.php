<?php

class PersonaRepository extends BaseRepository
{
    protected $table = 'personas';
    protected $fillable = array(
        'comunidad_id', 'numero_control', 'nombre', 'apellido_paterno', 'apellido_materno',
        'fecha_nacimiento', 'telefono', 'email', 'direccion', 'barrio', 'seccion',
        'fecha_alta', 'estatus', 'observaciones',
    );

    /**
     * Busca personas por coincidencia parcial en varios campos a la vez.
     * $comunidadId en null busca en todas las comunidades (uso exclusivo de SUPERADMIN).
     */
    public function buscar($query, $comunidadId = null, $limit = 20)
    {
        $like = '%' . $query . '%';
        $sql = 'SELECT * FROM personas
            WHERE (numero_control LIKE ? OR nombre LIKE ? OR apellido_paterno LIKE ?
                OR apellido_materno LIKE ? OR barrio LIKE ? OR seccion LIKE ? OR direccion LIKE ?)';
        $types = 'sssssss';
        $params = array($like, $like, $like, $like, $like, $like, $like);

        if ($comunidadId !== null) {
            $sql .= ' AND comunidad_id = ?';
            $types .= 'i';
            $params[] = $comunidadId;
        }

        $sql .= ' ORDER BY nombre LIMIT ?';
        $types .= 'i';
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}
