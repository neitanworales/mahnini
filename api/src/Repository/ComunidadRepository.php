<?php

class ComunidadRepository extends BaseRepository
{
    protected $table = 'comunidades';
    protected $fillable = array(
        'clave', 'nombre', 'municipio', 'estado', 'direccion', 'telefono',
        'email', 'codigo_postal', 'descripcion', 'activo',
    );
}
