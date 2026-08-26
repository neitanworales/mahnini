<?php

class HogarPersonaRepository extends BaseRepository
{
    protected $table = 'hogar_personas';
    protected $fillable = array('hogar_id', 'persona_id', 'parentesco', 'es_responsable');
}
