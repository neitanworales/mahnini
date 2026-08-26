<?php

class SeccionRepository extends BaseRepository
{
    protected $table = 'secciones';
    protected $fillable = array('comunidad_id', 'nombre', 'activo');
}
