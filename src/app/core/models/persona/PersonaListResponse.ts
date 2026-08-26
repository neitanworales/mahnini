import { DefaultResponse } from "../DefaultResponse";
import { Persona } from "./Persona";

export class PersonaListResponse extends DefaultResponse {
    data!: {
        items: Persona[];
    };
}
