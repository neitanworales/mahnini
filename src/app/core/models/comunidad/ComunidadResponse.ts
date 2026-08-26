import { DefaultResponse } from "../DefaultResponse";
import { Comunidad } from "./Comunidad";

export class ComunidadResponse extends DefaultResponse {
    data!: {
        item: Comunidad;
    };
}
