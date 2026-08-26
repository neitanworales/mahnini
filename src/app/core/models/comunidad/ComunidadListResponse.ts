import { DefaultResponse } from "../DefaultResponse";
import { Comunidad } from "./Comunidad";

export class ComunidadListResponse extends DefaultResponse {
    data!: {
        items: Comunidad[];
    };
}
