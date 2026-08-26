import { DefaultResponse } from "../DefaultResponse";
import { Persona } from "../persona/Persona";
import { Cargo } from "./Cargo";
import { Pago } from "./Pago";
import { ExpedienteResumen } from "./ExpedienteResumen";

export class ExpedienteDetalleResponse extends DefaultResponse {
    data!: {
        persona: Persona;
        resumen: ExpedienteResumen;
        pagos: Pago[];
        adeudos: Cargo[];
        exentos: Cargo[];
    };
}
