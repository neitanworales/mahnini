<?php

class CargoController extends CrudController
{
    protected $resourceLabel = 'cargo';
    protected $allowedFields = array(
        'comunidad_id', 'persona_id', 'hogar_id', 'concepto_id', 'obra_id', 'anio', 'periodo',
        'descripcion', 'fecha_emision', 'fecha_vencimiento', 'monto', 'saldo', 'estatus',
        'origen_tipo', 'origen_id', 'creado_por',
    );
    protected $filterableFields = array('comunidad_id', 'persona_id', 'hogar_id', 'obra_id', 'estatus', 'anio');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD', 'TESORERO');

    public function __construct()
    {
        $this->repo = new CargoRepository();
    }

    protected function extractPayload($request)
    {
        $payload = parent::extractPayload($request);

        // Un cargo nuevo nace con saldo igual al monto si no se especifica.
        if (isset($payload['monto']) && !isset($payload['saldo'])) {
            $payload['saldo'] = $payload['monto'];
        }

        return $payload;
    }
}
