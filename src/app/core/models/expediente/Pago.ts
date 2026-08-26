export class Pago {
    id!: number;
    comunidadId!: number;
    personaId?: number;
    hogarId?: number;
    fecha!: string;
    montoTotal!: number;
    metodoPago!: string;
    referencia?: string;
    observaciones?: string;
    estatus!: string;
    usuarioRegistroId!: number;
    createdAt?: string;
}
