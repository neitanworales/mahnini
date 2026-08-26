<?php

class ReunionParticipanteRepository extends BaseRepository
{
    protected $table = 'reunion_participantes';
    protected $fillable = array(
        'reunion_id', 'persona_id', 'estatus', 'monto_inasistencia_personalizado',
        'hora_registro', 'observaciones', 'cargo_generado_id',
    );

    /**
     * Asistencia de una persona a las reuniones de un año (para la tarjeta comunitaria).
     */
    public function findByPersonaAnio($personaId, $anio)
    {
        $sql = 'SELECT reunion_participantes.*, reuniones.fecha, reuniones.nombre AS reunion_nombre
            FROM reunion_participantes
            INNER JOIN reuniones ON reuniones.id = reunion_participantes.reunion_id
            WHERE reunion_participantes.persona_id = ? AND reuniones.anio = ?
            ORDER BY reuniones.fecha ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $personaId, $anio);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}
