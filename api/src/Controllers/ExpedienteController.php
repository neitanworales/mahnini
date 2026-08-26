<?php

/**
 * Busqueda y consulta consolidada de una persona: datos, balance de pagos/adeudos
 * y los listados de pagos, adeudos pendientes y cargos exentos (condonados).
 * No es un CrudController: es de solo lectura y con alcance segun comunidad del usuario.
 */
class ExpedienteController extends BaseController
{
    private $personas;
    private $pagos;
    private $cargos;
    private $reunionParticipantes;
    private $faenaParticipantes;
    private $authService;

    public function __construct()
    {
        $this->personas = new PersonaRepository();
        $this->pagos = new PagoRepository();
        $this->cargos = new CargoRepository();
        $this->reunionParticipantes = new ReunionParticipanteRepository();
        $this->faenaParticipantes = new FaenaParticipanteRepository();
        $this->authService = new AuthService();
    }

    public function buscar($request)
    {
        $usuario = $this->requireAuth();
        if (!$usuario) {
            return $this->fail('unauthorized', 401);
        }

        $query = isset($request['query']) ? trim((string) $request['query']) : '';
        if ($query === '') {
            return $this->fail('query is required', 422);
        }

        $comunidadId = $this->comunidadScopeFor($usuario);
        if ($comunidadId === false) {
            return $this->fail('usuario sin comunidad asignada', 403);
        }

        $rows = $this->personas->buscar($query, $comunidadId, 20);

        return $this->ok(array(
            'items' => camelize_keys($rows),
        ), 'busqueda de personas');
    }

    public function detalle($request)
    {
        $usuario = $this->requireAuth();
        if (!$usuario) {
            return $this->fail('unauthorized', 401);
        }

        $personaId = isset($request['persona_id']) ? (int) $request['persona_id'] : 0;
        if ($personaId <= 0) {
            return $this->fail('persona_id is required', 422);
        }

        $persona = $this->personas->findById($personaId);
        if (!$persona) {
            return $this->fail('persona not found', 404);
        }

        $comunidadId = $this->comunidadScopeFor($usuario);
        if ($comunidadId === false) {
            return $this->fail('usuario sin comunidad asignada', 403);
        }
        if ($comunidadId !== null && (int) $persona['comunidad_id'] !== $comunidadId) {
            return $this->fail('insufficient permissions', 403);
        }

        $pagos = $this->pagos->findByPersona($personaId);
        $adeudos = $this->cargos->findByPersona($personaId, array('PENDIENTE', 'PARCIAL', 'VENCIDO'));
        $exentos = $this->cargos->findByPersona($personaId, array('CONDONADO'));

        $totalPagado = 0;
        foreach ($pagos as $pago) {
            if ($pago['estatus'] === 'APLICADO') {
                $totalPagado += (float) $pago['monto_total'];
            }
        }

        $totalAdeudo = 0;
        foreach ($adeudos as $adeudo) {
            $totalAdeudo += (float) $adeudo['saldo'];
        }

        $totalExento = 0;
        foreach ($exentos as $exento) {
            $totalExento += (float) $exento['monto'];
        }

        return $this->ok(array(
            'persona' => camelize_keys($persona),
            'resumen' => array(
                'totalPagado' => round($totalPagado, 2),
                'totalAdeudo' => round($totalAdeudo, 2),
                'totalExento' => round($totalExento, 2),
            ),
            'pagos' => camelize_keys($pagos),
            'adeudos' => camelize_keys($adeudos),
            'exentos' => camelize_keys($exentos),
        ), 'expediente encontrado');
    }

    // null = sin restriccion (SUPERADMIN), int = limitar a esa comunidad, false = usuario invalido.
    private function comunidadScopeFor($usuario)
    {
        $role = $this->authService->roleForUsuario($usuario);
        if ($role && $role['nombre'] === 'SUPERADMIN') {
            return null;
        }

        return !empty($usuario->comunidad_id) ? (int) $usuario->comunidad_id : false;
    }

    /**
     * Tarjeta comunitaria: asistencia a reuniones/faenas de un a\u00f1o, balance de
     * cooperacion anual de ese a\u00f1o, y avance de cooperacion de obra (acumulado).
     */
    public function tarjeta($request)
    {
        $usuario = $this->requireAuth();
        if (!$usuario) {
            return $this->fail('unauthorized', 401);
        }

        $personaId = isset($request['persona_id']) ? (int) $request['persona_id'] : 0;
        if ($personaId <= 0) {
            return $this->fail('persona_id is required', 422);
        }

        $persona = $this->personas->findById($personaId);
        if (!$persona) {
            return $this->fail('persona not found', 404);
        }

        $comunidadId = $this->comunidadScopeFor($usuario);
        if ($comunidadId === false) {
            return $this->fail('usuario sin comunidad asignada', 403);
        }
        if ($comunidadId !== null && (int) $persona['comunidad_id'] !== $comunidadId) {
            return $this->fail('insufficient permissions', 403);
        }

        $anio = isset($request['anio']) && (int) $request['anio'] > 0 ? (int) $request['anio'] : (int) date('Y');

        $reuniones = $this->reunionParticipantes->findByPersonaAnio($personaId, $anio);
        $faenas = $this->faenaParticipantes->findByPersonaAnio($personaId, $anio);
        $cooperacion = $this->cargos->resumenPorTipoConcepto($personaId, $anio, 'COOPERACION_ANUAL');
        $obrasRaw = $this->cargos->resumenObrasPorPersona($personaId);

        $asignadoCoop = (float) $cooperacion['asignado'];
        $pendienteCoop = (float) $cooperacion['pendiente'];

        $obras = array();
        foreach ($obrasRaw as $obra) {
            $asignado = (float) $obra['asignado'];
            $pendiente = (float) $obra['pendiente'];
            $obras[] = array(
                'nombre' => $obra['obra_nombre'],
                'asignado' => round($asignado, 2),
                'pagado' => round($asignado - $pendiente, 2),
                'estado' => $pendiente <= 0 ? 'PAGADO' : ($pendiente < $asignado ? 'PARCIAL' : 'PENDIENTE'),
            );
        }

        return $this->ok(array(
            'persona' => camelize_keys($persona),
            'anio' => $anio,
            'reuniones' => camelize_keys($reuniones),
            'faenas' => camelize_keys($faenas),
            'cooperacionAnual' => array(
                'asignado' => round($asignadoCoop, 2),
                'pagado' => round($asignadoCoop - $pendienteCoop, 2),
                'pendiente' => round($pendienteCoop, 2),
            ),
            'obras' => $obras,
        ), 'tarjeta comunitaria');
    }
}
