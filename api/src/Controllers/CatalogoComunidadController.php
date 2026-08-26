<?php

/**
 * Base para catalogos propios de una comunidad (barrios, secciones/sector).
 * SUPERADMIN administra el catalogo de cualquier comunidad; ADMIN_COMUNIDAD solo el propio
 * (comunidad_id se fuerza en create y se valida contra el registro existente en update/delete).
 */
abstract class CatalogoComunidadController extends CrudController
{
    protected $authService;

    public function __construct()
    {
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

        if (!$this->esSuperAdmin($usuario)) {
            if (empty($usuario->comunidad_id)) {
                return $this->fail('usuario sin comunidad asignada', 403);
            }
            $payload['comunidad_id'] = (int) $usuario->comunidad_id;
        }

        if (empty($payload['comunidad_id'])) {
            return $this->fail('comunidad_id is required', 422);
        }

        $id = $this->repo->create($payload);
        if (!$id) {
            return $this->fail('failed to create ' . $this->resourceLabel, 500);
        }

        return $this->ok(array(
            'item' => $this->camelize($this->repo->findById($id)),
        ), $this->resourceLabel . ' created');
    }

    public function update($request)
    {
        $usuario = $this->requireAuth();
        if (!$usuario) {
            return $this->fail('unauthorized', 401);
        }
        if (!$this->hasAnyRole($usuario, $this->writeRoles)) {
            return $this->fail('insufficient permissions', 403);
        }

        $id = isset($request['id']) ? (int) $request['id'] : 0;
        $existing = $id > 0 ? $this->repo->findById($id) : null;
        if (!$existing) {
            return $this->fail($this->resourceLabel . ' not found', 404);
        }
        if (!$this->puedeAdministrar($usuario, (int) $existing['comunidad_id'])) {
            return $this->fail('insufficient permissions', 403);
        }

        $payload = $this->extractPayload($request);
        // El catalogo no se puede mover de comunidad.
        unset($payload['comunidad_id']);

        $ok = $this->repo->updateById($id, $payload);
        if (!$ok) {
            return $this->fail('failed to update ' . $this->resourceLabel, 500);
        }

        return $this->ok(array(
            'item' => $this->camelize($this->repo->findById($id)),
        ), $this->resourceLabel . ' updated');
    }

    public function delete($request)
    {
        $usuario = $this->requireAuth();
        if (!$usuario) {
            return $this->fail('unauthorized', 401);
        }
        if (!$this->hasAnyRole($usuario, $this->writeRoles)) {
            return $this->fail('insufficient permissions', 403);
        }

        $id = isset($request['id']) ? (int) $request['id'] : 0;
        $existing = $id > 0 ? $this->repo->findById($id) : null;
        if (!$existing) {
            return $this->fail($this->resourceLabel . ' not found', 404);
        }
        if (!$this->puedeAdministrar($usuario, (int) $existing['comunidad_id'])) {
            return $this->fail('insufficient permissions', 403);
        }

        $ok = $this->repo->deleteById($id);
        if (!$ok) {
            return $this->fail('failed to delete ' . $this->resourceLabel, 500);
        }

        return $this->ok(array('deleted' => true), $this->resourceLabel . ' deleted');
    }

    private function esSuperAdmin($usuario)
    {
        $role = $this->authService->roleForUsuario($usuario);
        return $role && $role['nombre'] === 'SUPERADMIN';
    }

    private function puedeAdministrar($usuario, $comunidadId)
    {
        if ($this->esSuperAdmin($usuario)) {
            return true;
        }

        return !empty($usuario->comunidad_id) && (int) $usuario->comunidad_id === $comunidadId;
    }
}
