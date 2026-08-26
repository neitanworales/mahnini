<?php

class FaenaRepository extends BaseRepository
{
    protected $table = 'faenas';
    protected $fillable = array(
        'comunidad_id', 'nombre', 'descripcion', 'anio', 'fecha', 'hora_inicio', 'hora_fin',
        'lugar', 'genera_cargo_inasistencia', 'monto_inasistencia_default', 'concepto_cargo_id', 'estatus',
    );
}
