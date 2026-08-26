<?php

class FaenaController extends CrudController
{
    protected $resourceLabel = 'faena';
    protected $allowedFields = array(
        'comunidad_id', 'nombre', 'descripcion', 'anio', 'fecha', 'hora_inicio', 'hora_fin',
        'lugar', 'genera_cargo_inasistencia', 'monto_inasistencia_default', 'concepto_cargo_id', 'estatus',
    );
    protected $filterableFields = array('comunidad_id', 'anio', 'estatus');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD', 'TESORERO', 'CAPTURISTA');

    public function __construct()
    {
        $this->repo = new FaenaRepository();
    }
}
