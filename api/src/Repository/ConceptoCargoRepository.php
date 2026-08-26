<?php

class ConceptoCargoRepository extends BaseRepository
{
    protected $table = 'conceptos_cargo';
    protected $fillable = array('comunidad_id', 'nombre', 'descripcion', 'tipo', 'monto_default', 'periodicidad', 'activo');
}
