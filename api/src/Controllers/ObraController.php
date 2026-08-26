<?php

class ObraController extends CrudController
{
    protected $resourceLabel = 'obra';
    protected $allowedFields = array(
        'comunidad_id', 'nombre', 'descripcion', 'anio', 'fecha_inicio', 'fecha_fin',
        'monto_objetivo', 'estatus', 'observaciones',
    );
    protected $filterableFields = array('comunidad_id', 'anio', 'estatus');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD', 'TESORERO');

    public function __construct()
    {
        $this->repo = new ObraRepository();
    }
}
