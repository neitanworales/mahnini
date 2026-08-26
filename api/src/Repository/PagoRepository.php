<?php

class PagoRepository extends BaseRepository
{
    protected $table = 'pagos';
    protected $fillable = array(
        'comunidad_id', 'persona_id', 'hogar_id', 'fecha', 'monto_total', 'metodo_pago',
        'referencia', 'observaciones', 'estatus', 'usuario_registro_id',
    );

    private $cargos;

    public function __construct()
    {
        parent::__construct();
        $this->cargos = new CargoRepository();
    }

    /**
     * Pagos aplicados a una persona, del más reciente al más antiguo.
     */
    public function findByPersona($personaId)
    {
        $sql = 'SELECT * FROM pagos WHERE persona_id = ? ORDER BY fecha DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $personaId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /**
     * Crea un pago junto con el detalle de cargos que cubre, y descuenta el saldo
     * de cada cargo dentro de una sola transaccion.
     */
    public function registrarConDetalle(array $pagoData, array $detalles)
    {
        $this->db->begin_transaction();

        try {
            $pagoId = $this->insertRow($pagoData, $this->fillable);
            if (!$pagoId) {
                throw new RuntimeException('no se pudo crear el pago');
            }

            $detalleSql = 'INSERT INTO pago_detalle (pago_id, cargo_id, monto_aplicado) VALUES (?, ?, ?)';
            $detalleStmt = $this->db->prepare($detalleSql);

            foreach ($detalles as $detalle) {
                $cargoId = (int) $detalle['cargo_id'];
                $monto = (float) $detalle['monto_aplicado'];

                $detalleStmt->bind_param('iid', $pagoId, $cargoId, $monto);
                $detalleStmt->execute();

                $this->cargos->aplicarPago($cargoId, $monto);
            }

            $detalleStmt->close();
            $this->db->commit();

            return $pagoId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
