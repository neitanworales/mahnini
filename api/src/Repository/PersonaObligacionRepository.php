<?php

class PersonaObligacionRepository extends BaseRepository
{
    protected $table = 'persona_obligaciones';
    protected $fillable = array('persona_id', 'concepto_id', 'tipo_estado', 'activo', 'monto_personalizado', 'observaciones');
}
