<?php

class Usuario extends BaseModel
{
    public $id;
    public $comunidad_id;
    public $persona_id;
    public $rol_id;
    public $email;
    public $password_hash;
    public $activo;
    public $ultimo_acceso;
    public $created_at;
    public $updated_at;

    public function toArray()
    {
        $data = parent::toArray();
        unset($data['password_hash']);
        return $data;
    }
}
