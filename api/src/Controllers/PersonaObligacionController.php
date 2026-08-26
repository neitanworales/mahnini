<?php

class PersonaObligacionController extends CrudController
{
    protected $resourceLabel = 'persona_obligacion';
    protected $allowedFields = array('persona_id', 'concepto_id', 'tipo_estado', 'activo', 'monto_personalizado', 'observaciones');
    protected $filterableFields = array('persona_id', 'concepto_id');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD', 'TESORERO');

    public function __construct()
    {
        $this->repo = new PersonaObligacionRepository();
    }
}
