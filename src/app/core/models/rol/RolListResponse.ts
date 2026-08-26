import { DefaultResponse } from "../DefaultResponse";
import { Rol } from "./Rol";

export class RolListResponse extends DefaultResponse {
    data!: {
        items: Rol[];
    };
}
