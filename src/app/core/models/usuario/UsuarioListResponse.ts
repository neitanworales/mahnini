import { DefaultResponse } from "../DefaultResponse";
import { Usuario } from "./Usuario";

export class UsuarioListResponse extends DefaultResponse {
    data!: {
        items: Usuario[];
    };
}
