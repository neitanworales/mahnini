<?php

class ComunidadController extends CrudController
{
    protected $resourceLabel = 'comunidad';
    protected $allowedFields = array(
        'clave', 'nombre', 'municipio', 'estado', 'direccion', 'telefono',
        'email', 'codigo_postal', 'descripcion', 'activo',
    );
    protected $filterableFields = array('activo');
    // create/delete son exclusivos de SUPERADMIN; update() se sobreescribe para permitir
    // tambien a ADMIN_COMUNIDAD editar unicamente los datos de su propia comunidad.
    protected $writeRoles = array('SUPERADMIN');

    private $authService;

    public function __construct()
    {
        $this->repo = new ComunidadRepository();
        $this->authService = new AuthService();
    }

    public function update($request)
    {
        $usuario = $this->requireAuth();
        if (!$usuario) {
            return $this->fail('unauthorized', 401);
        }

        $id = isset($request['id']) ? (int) $request['id'] : 0;
        if ($id <= 0) {
            return $this->fail('id is required', 422);
        }

        $role = $this->authService->roleForUsuario($usuario);
        $esSuperAdmin = $role && $role['nombre'] === 'SUPERADMIN';
        $esAdminDeEsta = $role && $role['nombre'] === 'ADMIN_COMUNIDAD'
            && !empty($usuario->comunidad_id) && (int) $usuario->comunidad_id === $id;

        if (!$esSuperAdmin && !$esAdminDeEsta) {
            return $this->fail('insufficient permissions', 403);
        }

        $payload = $this->extractPayload($request);
        if (empty($payload)) {
            return $this->fail('payload is required', 422);
        }

        $ok = $this->repo->updateById($id, $payload);
        if (!$ok) {
            return $this->fail('failed to update ' . $this->resourceLabel, 500);
        }

        $row = $this->repo->findById($id);
        return $this->ok(array(
            'item' => $this->camelize($row),
        ), $this->resourceLabel . ' updated');
    }
}
