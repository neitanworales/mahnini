<?php

class ConceptoCargoController extends CrudController
{
    protected $resourceLabel = 'concepto_cargo';
    protected $allowedFields = array('comunidad_id', 'nombre', 'descripcion', 'tipo', 'monto_default', 'periodicidad', 'activo');
    protected $filterableFields = array('comunidad_id', 'tipo');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD', 'TESORERO');

    public function __construct()
    {
        $this->repo = new ConceptoCargoRepository();
    }
}
