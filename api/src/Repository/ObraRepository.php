<?php

class ObraRepository extends BaseRepository
{
    protected $table = 'obras';
    protected $fillable = array(
        'comunidad_id', 'nombre', 'descripcion', 'anio', 'fecha_inicio', 'fecha_fin',
        'monto_objetivo', 'estatus', 'observaciones',
    );
}
