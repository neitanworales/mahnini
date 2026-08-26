export class Cargo {
    id!: number;
    comunidadId!: number;
    personaId?: number;
    hogarId?: number;
    conceptoId!: number;
    conceptoNombre?: string;
    obraId?: number;
    anio!: number;
    periodo?: string;
    descripcion?: string;
    fechaEmision!: string;
    fechaVencimiento?: string;
    monto!: number;
    saldo!: number;
    estatus!: string;
    origenTipo?: string;
    origenId?: number;
    creadoPor?: number;
    createdAt?: string;
    updatedAt?: string;
}
