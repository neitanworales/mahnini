<?php

class HogarPersonaController extends CrudController
{
    protected $resourceLabel = 'hogar_persona';
    protected $allowedFields = array('hogar_id', 'persona_id', 'parentesco', 'es_responsable');
    protected $filterableFields = array('hogar_id', 'persona_id');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD', 'CAPTURISTA');

    public function __construct()
    {
        $this->repo = new HogarPersonaRepository();
    }
}
