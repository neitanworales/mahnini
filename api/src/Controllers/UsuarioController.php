<?php

class UsuarioController extends CrudController
{
    protected $resourceLabel = 'usuario';
    protected $allowedFields = array('comunidad_id', 'persona_id', 'rol_id', 'email', 'activo');
    protected $filterableFields = array('comunidad_id', 'rol_id');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD');

    private $authService;
    private $roles;

    public function __construct()
    {
        $this->repo = new UsuarioRepository();
        $this->authService = new AuthService();
        $this->roles = new RolRepository();
    }

    public function index($request)
    {
        $usuario = $this->requireAuth();
        if (!$usuario) {
            return $this->fail('unauthorized', 401);
        }

        $limit = isset($request['limit']) ? (int) $request['limit'] : 100;
        $offset = isset($request['offset']) ? (int) $request['offset'] : 0;

        $conditions = array();
        foreach ($this->filterableFields as $field) {
            if (isset($request[$field]) && $request[$field] !== '') {
                $conditions[$field] = is_numeric($request[$field]) ? (int) $request[$field] : $request[$field];
            }
        }

        $rows = empty($conditions)
            ? $this->repo->findAll($limit, $offset)
            : $this->repo->findAllBy($conditions, $limit, $offset);

        $rows = $this->ocultarSuperadminsSiNoAplica($usuario, $rows);

        return $this->ok(array(
            'items' => $this->camelize($rows),
        ), $this->resourceLabel . ' list');
    }

    public function detail($request)
    {
        $usuario = $this->requireAuth();
        if (!$usuario) {
            return $this->fail('unauthorized', 401);
        }

        $id = isset($request['id']) ? (int) $request['id'] : 0;
        if ($id <= 0) {
            return $this->fail('id is required', 422);
        }

        $row = $this->repo->findById($id);
        if (!$row || empty($this->ocultarSuperadminsSiNoAplica($usuario, array($row)))) {
            return $this->fail($this->resourceLabel . ' not found', 404);
        }

        return $this->ok(array(
            'item' => $this->camelize($row),
        ), $this->resourceLabel . ' found');
    }

    // Un ADMIN_COMUNIDAD (u otro rol no SUPERADMIN) no debe ver cuentas de SUPERADMIN.
    private function ocultarSuperadminsSiNoAplica($usuario, array $rows)
    {
        $role = $this->authService->roleForUsuario($usuario);
        if ($role && $role['nombre'] === 'SUPERADMIN') {
            return $rows;
        }

        $superadminRol = $this->roles->findByNombre('SUPERADMIN');
        $superadminRolId = $superadminRol ? (int) $superadminRol['id'] : 0;

        return array_values(array_filter($rows, function ($row) use ($superadminRolId) {
            return (int) $row['rol_id'] !== $superadminRolId;
        }));
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
        $email = isset($payload['email']) ? strtolower(trim((string) $payload['email'])) : '';
        $password = isset($request['password']) ? (string) $request['password'] : '';
        $comunidadId = isset($payload['comunidad_id']) ? (int) $payload['comunidad_id'] : 0;
        $rolId = isset($payload['rol_id']) ? (int) $payload['rol_id'] : 0;

        if ($email === '' || $password === '' || $comunidadId <= 0 || $rolId <= 0) {
            return $this->fail('email, password, comunidad_id and rol_id are required', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('email format is invalid', 422);
        }

        if (!$this->isStrongPassword($password)) {
            return $this->fail('password must be at least 12 chars and include uppercase, lowercase, number, special char, and no spaces', 422);
        }

        $createdUser = $this->authService->createUsuario(array(
            'comunidad_id' => $comunidadId,
            'persona_id' => isset($payload['persona_id']) ? $payload['persona_id'] : null,
            'rol_id' => $rolId,
            'email' => $email,
            'password' => $password,
        ));

        if (!$createdUser) {
            return $this->fail('email already exists, or comunidad_id/rol_id/persona_id is invalid', 409);
        }

        return $this->ok(array(
            'item' => $this->camelize($createdUser->toArray()),
        ), 'usuario created');
    }

    public function profile($request)
    {
        $usuario = $this->requireAuth();
        if (!$usuario) {
            return $this->fail('unauthorized', 401);
        }

        return $this->ok(array(
            'user' => $usuario->toArray(),
            'role' => $this->authService->roleForUsuario($usuario),
            'persona' => $this->authService->personaForUsuario($usuario),
        ), 'profile found');
    }

    private function isStrongPassword($password)
    {
        if (!is_string($password) || strlen($password) < 12) {
            return false;
        }

        if (preg_match('/\s/', $password)) {
            return false;
        }

        return preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/\d/', $password)
            && preg_match('/[^A-Za-z0-9]/', $password);
    }
}
