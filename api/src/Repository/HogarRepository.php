<?php

class HogarRepository extends BaseRepository
{
    protected $table = 'hogares';
    protected $fillable = array('comunidad_id', 'clave', 'direccion', 'responsable_persona_id', 'estatus');
}
