import { DefaultResponse } from "../DefaultResponse";
import { Persona } from "../persona/Persona";
import { TarjetaReunion } from "./TarjetaReunion";
import { TarjetaFaena } from "./TarjetaFaena";
import { TarjetaCooperacion } from "./TarjetaCooperacion";
import { TarjetaObra } from "./TarjetaObra";

export class TarjetaComunitariaResponse extends DefaultResponse {
    data!: {
        persona: Persona;
        anio: number;
        reuniones: TarjetaReunion[];
        faenas: TarjetaFaena[];
        cooperacionAnual: TarjetaCooperacion;
        obras: TarjetaObra[];
    };
}
