<?php

class PagoDetalleController extends CrudController
{
    protected $resourceLabel = 'pago_detalle';
    protected $allowedFields = array('pago_id', 'cargo_id', 'monto_aplicado');
    protected $filterableFields = array('pago_id', 'cargo_id');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD', 'TESORERO');

    public function __construct()
    {
        $this->repo = new PagoDetalleRepository();
    }
}
