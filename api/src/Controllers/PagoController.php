<?php

class PagoController extends CrudController
{
    protected $resourceLabel = 'pago';
    protected $allowedFields = array(
        'comunidad_id', 'persona_id', 'hogar_id', 'fecha', 'monto_total', 'metodo_pago',
        'referencia', 'observaciones', 'estatus', 'usuario_registro_id',
    );
    protected $filterableFields = array('comunidad_id', 'persona_id', 'hogar_id');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD', 'TESORERO');

    public function __construct()
    {
        $this->repo = new PagoRepository();
    }

    /**
     * Registra un pago y su detalle (aplicacion a uno o varios cargos) en una sola
     * operacion, descontando el saldo de cada cargo afectado.
     */
    public function registrar($request)
    {
        $usuario = $this->requireAuth();
        if (!$usuario) {
            return $this->fail('unauthorized', 401);
        }
        if (!$this->hasAnyRole($usuario, $this->writeRoles)) {
            return $this->fail('insufficient permissions', 403);
        }

        $comunidadId = isset($request['comunidad_id']) ? (int) $request['comunidad_id'] : 0;
        $montoTotal = isset($request['monto_total']) ? (float) $request['monto_total'] : 0;
        $detalles = isset($request['detalles']) && is_array($request['detalles']) ? $request['detalles'] : array();

        if ($comunidadId <= 0 || $montoTotal <= 0 || empty($detalles)) {
            return $this->fail('comunidad_id, monto_total and detalles are required', 422);
        }

        $sumaDetalles = 0;
        foreach ($detalles as $detalle) {
            if (!isset($detalle['cargo_id']) || !isset($detalle['monto_aplicado'])) {
                return $this->fail('each detalle requires cargo_id and monto_aplicado', 422);
            }
            $sumaDetalles += (float) $detalle['monto_aplicado'];
        }

        if (abs($sumaDetalles - $montoTotal) > 0.01) {
            return $this->fail('la suma de detalles.monto_aplicado debe ser igual a monto_total', 422);
        }

        $pagoData = array(
            'comunidad_id' => $comunidadId,
            'persona_id' => isset($request['persona_id']) ? (int) $request['persona_id'] : null,
            'hogar_id' => isset($request['hogar_id']) ? (int) $request['hogar_id'] : null,
            'fecha' => isset($request['fecha']) ? $request['fecha'] : date('Y-m-d H:i:s'),
            'monto_total' => $montoTotal,
            'metodo_pago' => isset($request['metodo_pago']) ? $request['metodo_pago'] : 'EFECTIVO',
            'referencia' => isset($request['referencia']) ? $request['referencia'] : null,
            'observaciones' => isset($request['observaciones']) ? $request['observaciones'] : null,
            'usuario_registro_id' => (int) $usuario->id,
        );

        try {
            $pagoId = $this->repo->registrarConDetalle($pagoData, $detalles);
        } catch (Exception $e) {
            return $this->fail('failed to register pago: ' . $e->getMessage(), 500);
        }

        $pago = $this->repo->findById($pagoId);
        return $this->ok(array('item' => $this->camelize($pago)), 'pago registrado');
    }
}
