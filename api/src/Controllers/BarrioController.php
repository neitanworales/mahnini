<?php

class BarrioController extends CatalogoComunidadController
{
    protected $resourceLabel = 'barrio';
    protected $allowedFields = array('comunidad_id', 'nombre', 'activo');
    protected $filterableFields = array('comunidad_id', 'activo');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD');

    public function __construct()
    {
        parent::__construct();
        $this->repo = new BarrioRepository();
    }
}
