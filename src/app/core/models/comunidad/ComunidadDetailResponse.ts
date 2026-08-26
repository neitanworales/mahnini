import { DefaultResponse } from "../DefaultResponse";
import { Comunidad } from "./Comunidad";

export class ComunidadDetailResponse extends DefaultResponse {
    data!: {
        item: Comunidad;
    };
}
