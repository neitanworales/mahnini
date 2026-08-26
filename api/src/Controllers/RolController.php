<?php

class RolController extends CrudController
{
    protected $resourceLabel = 'rol';
    protected $allowedFields = array('nombre', 'descripcion');
    protected $writeRoles = array('SUPERADMIN');

    public function __construct()
    {
        $this->repo = new RolRepository();
    }
}
