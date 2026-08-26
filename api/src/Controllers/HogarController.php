<?php

class HogarController extends CrudController
{
    protected $resourceLabel = 'hogar';
    protected $allowedFields = array('comunidad_id', 'clave', 'direccion', 'responsable_persona_id', 'estatus');
    protected $filterableFields = array('comunidad_id');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD', 'CAPTURISTA');

    public function __construct()
    {
        $this->repo = new HogarRepository();
    }
}
