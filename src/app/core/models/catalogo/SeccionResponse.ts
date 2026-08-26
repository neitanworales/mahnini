import { DefaultResponse } from "../DefaultResponse";
import { Seccion } from "./Seccion";

export class SeccionResponse extends DefaultResponse {
    data!: {
        item: Seccion;
    };
}
