import { DefaultResponse } from "../DefaultResponse";
import { Persona } from "../persona/Persona";

export class ExpedienteBusquedaResponse extends DefaultResponse {
    data!: {
        items: Persona[];
    };
}
