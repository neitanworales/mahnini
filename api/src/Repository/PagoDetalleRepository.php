<?php

class PagoDetalleRepository extends BaseRepository
{
    protected $table = 'pago_detalle';
    protected $fillable = array('pago_id', 'cargo_id', 'monto_aplicado');
}
