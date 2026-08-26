<?php

class AuditoriaController extends CrudController
{
    protected $resourceLabel = 'auditoria';
    protected $filterableFields = array('comunidad_id', 'usuario_id', 'entidad');
    // Solo lectura: se escribe desde los servicios de negocio, no desde la API.
    protected $writeRoles = array('SUPERADMIN');

    public function __construct()
    {
        $this->repo = new AuditoriaRepository();
    }

    public function create($request)
    {
        return $this->fail('auditoria is read-only', 400);
    }

    public function update($request)
    {
        return $this->fail('auditoria is read-only', 400);
    }

    public function delete($request)
    {
        return $this->fail('auditoria is read-only', 400);
    }
}
