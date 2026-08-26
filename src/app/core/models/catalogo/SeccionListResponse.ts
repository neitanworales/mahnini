import { DefaultResponse } from "../DefaultResponse";
import { Seccion } from "./Seccion";

export class SeccionListResponse extends DefaultResponse {
    data!: {
        items: Seccion[];
    };
}
