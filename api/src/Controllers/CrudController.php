<?php

abstract class CrudController extends BaseController
{
    protected $repo;
    protected $allowedFields = array();
    protected $resourceLabel = 'resource';
    // Campos por los que se puede filtrar el listado (?campo=valor), ej: comunidad_id, persona_id.
    protected $filterableFields = array();
    // Roles permitidos para crear/editar/eliminar. Vacio = cualquier usuario autenticado.
    protected $writeRoles = array();

    public function index($request)
    {
        if (!$this->requireAuth()) {
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

        return $this->ok(array(
            'items' => $this->camelize($rows),
        ), $this->resourceLabel . ' list');
    }

    public function detail($request)
    {
        if (!$this->requireAuth()) {
            return $this->fail('unauthorized', 401);
        }

        $id = isset($request['id']) ? (int) $request['id'] : 0;
        if ($id <= 0) {
            return $this->fail('id is required', 422);
        }

        $row = $this->repo->findById($id);
        if (!$row) {
            return $this->fail($this->resourceLabel . ' not found', 404);
        }

        return $this->ok(array(
            'item' => $this->camelize($row),
        ), $this->resourceLabel . ' found');
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
        if (empty($payload)) {
            return $this->fail('payload is required', 422);
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
        if ($id <= 0) {
            return $this->fail('id is required', 422);
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
        if ($id <= 0) {
            return $this->fail('id is required', 422);
        }

        $ok = $this->repo->deleteById($id);
        if (!$ok) {
            return $this->fail('failed to delete ' . $this->resourceLabel, 500);
        }

        return $this->ok(array('deleted' => true), $this->resourceLabel . ' deleted');
    }

    protected function extractPayload($request)
    {
        $data = $request;
        unset($data['id'], $data['limit'], $data['offset']);

        $normalized = array();
        foreach ($data as $key => $value) {
            $normalized[$this->camelToSnake($key)] = $value;
        }

        if (!empty($this->allowedFields)) {
            $filtered = array();
            foreach ($this->allowedFields as $field) {
                if (array_key_exists($field, $normalized)) {
                    $filtered[$field] = $normalized[$field];
                }
            }
            return $filtered;
        }

        return $normalized;
    }

    protected function camelize($data)
    {
        if (is_array($data)) {
            if ($this->isAssoc($data)) {
                $result = array();
                foreach ($data as $key => $value) {
                    $result[$this->snakeToCamel($key)] = $this->camelize($value);
                }
                return $result;
            }

            $items = array();
            foreach ($data as $value) {
                $items[] = $this->camelize($value);
            }
            return $items;
        }

        return $data;
    }

    private function isAssoc(array $array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function camelToSnake($value)
    {
        $value = preg_replace('/[A-Z]/', '_$0', $value);
        return strtolower($value);
    }

    private function snakeToCamel($value)
    {
        $parts = explode('_', $value);
        $first = array_shift($parts);
        $parts = array_map('ucfirst', $parts);
        return $first . implode('', $parts);
    }
}
