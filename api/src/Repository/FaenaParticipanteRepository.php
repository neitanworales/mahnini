<?php

class FaenaParticipanteRepository extends BaseRepository
{
    protected $table = 'faena_participantes';
    protected $fillable = array(
        'faena_id', 'persona_id', 'estatus', 'monto_inasistencia_personalizado',
        'hora_registro', 'observaciones', 'cargo_generado_id',
    );

    /**
     * Asistencia de una persona a las faenas de un año, con el monto de la multa
     * (cargo generado por inasistencia) si aplica, para la tarjeta comunitaria.
     */
    public function findByPersonaAnio($personaId, $anio)
    {
        $sql = 'SELECT faena_participantes.*, faenas.fecha, faenas.nombre AS faena_nombre,
                cargos.monto AS cargo_monto, cargos.saldo AS cargo_saldo
            FROM faena_participantes
            INNER JOIN faenas ON faenas.id = faena_participantes.faena_id
            LEFT JOIN cargos ON cargos.id = faena_participantes.cargo_generado_id
            WHERE faena_participantes.persona_id = ? AND faenas.anio = ?
            ORDER BY faenas.fecha ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $personaId, $anio);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}
