<?php

class ReunionRepository extends BaseRepository
{
    protected $table = 'reuniones';
    protected $fillable = array(
        'comunidad_id', 'nombre', 'descripcion', 'anio', 'fecha', 'hora_inicio', 'hora_fin',
        'lugar', 'genera_cargo_inasistencia', 'monto_inasistencia_default', 'concepto_cargo_id', 'estatus',
    );
}
