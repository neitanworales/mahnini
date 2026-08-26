<?php

class BarrioRepository extends BaseRepository
{
    protected $table = 'barrios';
    protected $fillable = array('comunidad_id', 'nombre', 'activo');
}
