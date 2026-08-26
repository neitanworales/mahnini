<?php

class SeccionController extends CatalogoComunidadController
{
    protected $resourceLabel = 'seccion';
    protected $allowedFields = array('comunidad_id', 'nombre', 'activo');
    protected $filterableFields = array('comunidad_id', 'activo');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD');

    public function __construct()
    {
        parent::__construct();
        $this->repo = new SeccionRepository();
    }
}
