<?php

class ReunionParticipanteController extends CrudController
{
    protected $resourceLabel = 'reunion_participante';
    protected $allowedFields = array(
        'reunion_id', 'persona_id', 'estatus', 'monto_inasistencia_personalizado',
        'hora_registro', 'observaciones', 'cargo_generado_id',
    );
    protected $filterableFields = array('reunion_id', 'persona_id', 'estatus');
    protected $writeRoles = array('SUPERADMIN', 'ADMIN_COMUNIDAD', 'TESORERO', 'CAPTURISTA');

    public function __construct()
    {
        $this->repo = new ReunionParticipanteRepository();
    }
}
