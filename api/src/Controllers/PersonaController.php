<?php

class PersonaController extends CrudController
{
    protected $resourceLabel = 'persona';
    protected $allowedFields = array(
        'comunidad_id', 'numero_control', 'nombre', 'apellido_paterno', 'apellido_materno',
        'fecha_nacimiento', 'telefono', 'email', 'direccion', 'barrio', 'seccion',
        'fecha_alta', 'estatus', 'observaciones',
    );
    protected $filterableFields = array('comunidad_id', 'estatus');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD', 'TESORERO', 'CAPTURISTA');

    private $authService;

    public function __construct()
    {
        $this->repo = new PersonaRepository();
        $this->authService = new AuthService();
    }

    public function create($request)
    {
        $usuario = $this->requireAuth();
        if (!$usuario) {
            return $this->fail('unauthorized', 401);
        }
        if (!$this->hasAnyRole($usuario, $this->writeRoles)) {
            return $this->fail('insufficient permissions', 403);
        }

        $payload = $this->extractPayload($request);

        // Solo SUPERADMIN puede elegir comunidad; el resto siempre crea en la suya.
        $role = $this->authService->roleForUsuario($usuario);
        if (!$role || $role['nombre'] !== 'SUPERADMIN') {
            if (empty($usuario->comunidad_id)) {
                return $this->fail('usuario sin comunidad asignada', 403);
            }
            $payload['comunidad_id'] = (int) $usuario->comunidad_id;
        }

        if (empty($payload) || empty($payload['comunidad_id'])) {
            return $this->fail('comunidad_id is required', 422);
        }

        $id = $this->repo->create($payload);
        if (!$id) {
            return $this->fail('failed to create ' . $this->resourceLabel, 500);
        }

        $row = $this->repo->findById($id);
        return $this->ok(array(
            'item' => $this->camelize($row),
        ), $this->resourceLabel . ' created');
    }
}
