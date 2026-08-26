<?php

class CargoRepository extends BaseRepository
{
    protected $table = 'cargos';
    protected $fillable = array(
        'comunidad_id', 'persona_id', 'hogar_id', 'concepto_id', 'obra_id', 'anio', 'periodo',
        'descripcion', 'fecha_emision', 'fecha_vencimiento', 'monto', 'saldo', 'estatus',
        'origen_tipo', 'origen_id', 'creado_por',
    );

    public function aplicarPago($cargoId, $montoAplicado)
    {
        $sql = "UPDATE cargos
            SET saldo = GREATEST(saldo - ?, 0),
                estatus = CASE
                    WHEN saldo - ? <= 0 THEN 'PAGADO'
                    WHEN saldo - ? < monto THEN 'PARCIAL'
                    ELSE estatus
                END
            WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('dddi', $montoAplicado, $montoAplicado, $montoAplicado, $cargoId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    /**
     * Cargos de una persona, opcionalmente filtrados por estatus (ej. adeudos o exentos).
     */
    public function findByPersona($personaId, array $estatuses = array())
    {
        $sql = 'SELECT cargos.*, conceptos_cargo.nombre AS concepto_nombre
            FROM cargos
            INNER JOIN conceptos_cargo ON conceptos_cargo.id = cargos.concepto_id
            WHERE cargos.persona_id = ?';
        $types = 'i';
        $params = array($personaId);

        if (!empty($estatuses)) {
            $placeholders = implode(',', array_fill(0, count($estatuses), '?'));
            $sql .= " AND cargos.estatus IN ($placeholders)";
            foreach ($estatuses as $estatus) {
                $types .= 's';
                $params[] = $estatus;
            }
        }

        $sql .= ' ORDER BY cargos.fecha_emision DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /**
     * Asignado/pendiente de una persona para un tipo de concepto (ej. COOPERACION_ANUAL) en un a\u00f1o.
     */
    public function resumenPorTipoConcepto($personaId, $anio, $tipoConcepto)
    {
        $sql = 'SELECT COALESCE(SUM(cargos.monto), 0) AS asignado, COALESCE(SUM(cargos.saldo), 0) AS pendiente
            FROM cargos
            INNER JOIN conceptos_cargo ON conceptos_cargo.id = cargos.concepto_id
            WHERE cargos.persona_id = ? AND cargos.anio = ? AND conceptos_cargo.tipo = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iis', $personaId, $anio, $tipoConcepto);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: array('asignado' => 0, 'pendiente' => 0);
    }

    /**
     * Asignado/pendiente por obra (cooperacion de obra) de una persona, acumulado sin importar el a\u00f1o.
     */
    public function resumenObrasPorPersona($personaId)
    {
        $sql = 'SELECT obras.id AS obra_id, obras.nombre AS obra_nombre,
                COALESCE(SUM(cargos.monto), 0) AS asignado,
                COALESCE(SUM(cargos.saldo), 0) AS pendiente
            FROM cargos
            INNER JOIN obras ON obras.id = cargos.obra_id
            WHERE cargos.persona_id = ? AND cargos.obra_id IS NOT NULL
            GROUP BY obras.id, obras.nombre
            ORDER BY obras.nombre ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $personaId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}
